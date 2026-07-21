<?php

namespace App\Http\Controllers;

use App\Models\EmployeeWorkingShift;
use App\Models\WorkingShift;
use App\Models\SchoolUnit;
use App\Services\SchoolUnitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmployeeWorkingShiftController extends Controller
{
    protected SchoolUnitService $unitService;

    public function __construct(SchoolUnitService $unitService)
    {
        $this->unitService = $unitService;
    }

    /**
     * Display a listing of shift assignments.
     */
    public function index(Request $request)
    {
        $units = SchoolUnit::where('is_active', true)->orderBy('name')->get();
        $shifts = WorkingShift::orderBy('name')->get();
        
        $selectedUnitId = $request->query('unit_id');
        
        // Fetch all assignments
        $query = EmployeeWorkingShift::with(['schoolUnit', 'workingShift']);
        if ($selectedUnitId) {
            $query->where('school_unit_id', $selectedUnitId);
        }
        $assignments = $query->orderBy('start_date', 'desc')->get();

        // Get employees from the service to map names
        $employees = $this->unitService->getSdEmployees();
        $employeeMap = collect($employees)->keyBy(function ($item) {
            return $item['unit_id'] . '-' . $item['id'];
        })->toArray();

        foreach ($assignments as &$assignment) {
            $key = $assignment->school_unit_id . '-' . $assignment->employee_id;
            if (isset($employeeMap[$key])) {
                $assignment->employee_name = $employeeMap[$key]['name'];
                $assignment->employee_nip = $employeeMap[$key]['nuptk_nip_nik'] ?? '-';
            } else {
                $assignment->employee_name = 'Pegawai #' . $assignment->employee_id;
                $assignment->employee_nip = '-';
            }
        }

        $groupedAssignments = $assignments->groupBy(function ($item) {
            return $item->workingShift ? $item->workingShift->name . ' (' . $item->workingShift->code . ')' : 'Tanpa Shift';
        });

        return view('employee-working-shifts.index', compact('groupedAssignments', 'units', 'shifts', 'selectedUnitId'));
    }

    /**
     * Show the form for assigning new shifts (Bulk).
     */
    public function create()
    {
        $units = SchoolUnit::where('is_active', true)->orderBy('name')->get();
        $shifts = WorkingShift::orderBy('name')->get();
        return view('employee-working-shifts.create', compact('units', 'shifts'));
    }

    /**
     * Fetch employees of a specific school unit via AJAX.
     */
    public function getEmployeesByUnit($unitId)
    {
        $unit = SchoolUnit::find($unitId);
        if (!$unit) {
            return response()->json([]);
        }

        try {
            $response = Http::withHeaders([
                'X-API-TOKEN' => $unit->api_token,
                'Accept' => 'application/json',
            ])->timeout(5)->get(rtrim($unit->api_url, '/') . '/employees');

            if ($response->successful()) {
                return response()->json($response->json('data') ?? []);
            }
        } catch (\Exception $e) {
            Log::error("Failed to fetch employees for unit {$unitId}: " . $e->getMessage());
        }

        return response()->json([]);
    }

    /**
     * Store newly created assignments in storage (Bulk).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_unit_id' => 'required|exists:school_units,id',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'integer',
            'working_shift_id' => 'required|exists:working_shifts,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        foreach ($validated['employee_ids'] as $employeeId) {
            // If there's an active shift assignment overlap, close it
            EmployeeWorkingShift::where('school_unit_id', $validated['school_unit_id'])
                ->where('employee_id', $employeeId)
                ->whereNull('end_date')
                ->update(['end_date' => date('Y-m-d', strtotime($validated['start_date'] . ' -1 day'))]);

            EmployeeWorkingShift::create([
                'school_unit_id' => $validated['school_unit_id'],
                'employee_id' => $employeeId,
                'working_shift_id' => $validated['working_shift_id'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? null,
            ]);
        }

        // Sync to the specific unit ONCE for all updated assignments
        $this->syncSchedulesToUnit($validated['school_unit_id']);

        return redirect()->route('employee-working-shifts.index')
            ->with('success', 'Jadwal shift pegawai berhasil ditugaskan dan disinkronkan secara massal.');
    }

    /**
     * Remove the specified assignment from storage.
     */
    public function destroy($id)
    {
        $assignment = EmployeeWorkingShift::findOrFail($id);
        $unitId = $assignment->school_unit_id;
        $assignment->delete();

        // Sync to unit
        $this->syncSchedulesToUnit($unitId);

        return redirect()->route('employee-working-shifts.index')
            ->with('success', 'Jadwal shift pegawai berhasil dihapus.');
    }

    /**
     * Sync schedules of a unit.
     */
    private function syncSchedulesToUnit($unitId)
    {
        $unit = SchoolUnit::find($unitId);
        if (!$unit) {
            return;
        }

        // Fetch employees of this unit to map NIP/NIK
        $employees = [];
        try {
            $resp = Http::withHeaders([
                'X-API-TOKEN' => $unit->api_token,
                'Accept' => 'application/json',
            ])->get(rtrim($unit->api_url, '/') . '/employees');
            if ($resp->successful()) {
                $employees = collect($resp->json('data') ?? [])->keyBy('id')->toArray();
            }
        } catch (\Exception $e) {
            Log::error("Failed to get unit employees for syncing schedules: " . $e->getMessage());
        }

        $assignments = EmployeeWorkingShift::with('workingShift')
            ->where('school_unit_id', $unitId)
            ->get();

        $payload = [];
        foreach ($assignments as $a) {
            $nip = isset($employees[$a->employee_id]) ? ($employees[$a->employee_id]['nuptk_nip_nik'] ?? null) : null;
            $payload[] = [
                'employee_id' => $a->employee_id,
                'nuptk_nip_nik' => $nip,
                'working_shift_code' => $a->workingShift->code,
                'start_date' => $a->start_date->format('Y-m-d'),
                'end_date' => $a->end_date ? $a->end_date->format('Y-m-d') : null,
            ];
        }

        try {
            Http::withHeaders([
                'X-API-TOKEN' => $unit->api_token,
                'Accept' => 'application/json',
            ])->post(rtrim($unit->api_url, '/') . '/sync/schedules', [
                'schedules' => $payload
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to sync schedules to unit {$unit->name}: " . $e->getMessage());
        }
    }
}

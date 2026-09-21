<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Models\HolidayAdjustment;
use App\Models\HolidayReward;
use App\Models\SchoolUnit;
use App\Services\SchoolUnitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HolidayController extends Controller
{
    protected SchoolUnitService $schoolUnitService;

    public function __construct(SchoolUnitService $schoolUnitService)
    {
        $this->schoolUnitService = $schoolUnitService;
    }

    /**
     * Display a listing of holidays, adjustments, and holiday rewards.
     */
    public function index()
    {
        $groupedHolidays = $this->getGroupedHolidays();
        $units = SchoolUnit::where('is_active', true)->orderBy('name')->get();
        $unitsMap = $units->keyBy('id');
        $adjustments = HolidayAdjustment::with(['holiday', 'schoolUnit'])->orderBy('original_date', 'desc')->get();
        $rawEmployees = $this->schoolUnitService->getAllEmployees();

        // Index raw employees for fast lookup
        $employeesMap = [];
        foreach ($rawEmployees as $emp) {
            $compositeKey = ($emp['unit_id'] ?? '') . '_' . ($emp['id'] ?? '');
            $employeesMap[$compositeKey] = $emp;
            $employeesMap[(string)($emp['id'] ?? '')] = $emp;
        }

        $holidayRewards = HolidayReward::orderBy('start_date', 'desc')->get()->map(function ($hr) use ($unitsMap, $employeesMap) {
            $unitIds = $hr->school_unit_ids ?? [];
            $unitNames = [];
            foreach ($unitIds as $uId) {
                if (isset($unitsMap[$uId])) {
                    $unitNames[] = $unitsMap[$uId]->name;
                }
            }

            $empIds = $hr->employee_ids ?? [];
            $empNames = [];
            $empDetails = [];
            if (!empty($empIds)) {
                foreach ($empIds as $eKey) {
                    if (isset($employeesMap[$eKey])) {
                        $emp = $employeesMap[$eKey];
                        $empNames[] = $emp['name'] . ' (' . ($emp['unit_name'] ?? '') . ')';
                        $empDetails[] = [
                            'id' => $emp['id'] ?? '',
                            'name' => $emp['name'] ?? '',
                            'unit_name' => $emp['unit_name'] ?? '',
                            'position' => $emp['position'] ?? $emp['subject_position'] ?? '-',
                            'nik' => $emp['nuptk_nip_nik'] ?? $emp['nik'] ?? '',
                        ];
                    } else {
                        $empNames[] = 'ID #' . $eKey;
                        $empDetails[] = [
                            'id' => $eKey,
                            'name' => 'Pegawai #' . $eKey,
                            'unit_name' => '-',
                            'position' => '-',
                            'nik' => '',
                        ];
                    }
                }
            }

            $hr->unit_names_list = $unitNames;
            $hr->employee_names_list = $empNames;
            $hr->employee_details_list = $empDetails;
            $hr->is_all_employees = empty($empIds);
            return $hr;
        });

        $positions = collect($rawEmployees)->map(function ($emp) {
            return $emp['position'] ?? $emp['subject_position'] ?? null;
        })->filter()->unique()->sort()->values();

        return view('holidays.index', compact('groupedHolidays', 'units', 'adjustments', 'holidayRewards', 'rawEmployees', 'positions'));
    }

    /**
     * Store a newly created holiday in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'applies_to' => 'required|in:global,custom',
            'school_unit_ids' => 'required_if:applies_to,custom|array',
            'school_unit_ids.*' => 'exists:school_units,id',
        ]);

        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $endDate = \Carbon\Carbon::parse($validated['end_date']);
        $isGlobal = $validated['applies_to'] === 'global';
        $schoolUnitIds = $request->input('school_unit_ids', []);

        $currentDate = $startDate->copy();
        $createdCount = 0;

        while ($currentDate->lte($endDate)) {
            $dateStr = $currentDate->format('Y-m-d');

            // Find or create holiday for this day
            $holiday = Holiday::where('original_date', $dateStr)->first();
            if (!$holiday) {
                $holiday = Holiday::create([
                    'name' => $validated['name'],
                    'original_date' => $dateStr,
                    'is_global' => $isGlobal,
                ]);
            } else {
                $holiday->update([
                    'name' => $validated['name'],
                    'is_global' => $isGlobal,
                ]);
                // Clear any existing adjustments for this holiday
                $holiday->adjustments()->delete();
            }

            // Create adjustments if scope is specific units
            if (!$isGlobal && !empty($schoolUnitIds)) {
                foreach ($schoolUnitIds as $unitId) {
                    HolidayAdjustment::create([
                        'holiday_id' => $holiday->id,
                        'original_date' => $dateStr,
                        'adjusted_date' => $dateStr,
                        'school_unit_id' => $unitId,
                        'reason' => $validated['name'],
                    ]);
                }
            }

            $currentDate->addDay();
            $createdCount++;
        }

        // Auto sync
        $this->syncHolidaysToUnits();

        return redirect()->route('holidays.index')
            ->with('success', $createdCount . ' hari libur berhasil disimpan.');
    }

    /**
     * Store a holiday adjustment (reschedule).
     */
    public function storeAdjustment(Request $request)
    {
        $validated = $request->validate([
            'holiday_id' => 'required|exists:holidays,id',
            'adjusted_date' => 'required|date',
            'school_unit_ids' => 'nullable|array',
            'school_unit_ids.*' => 'exists:school_units,id',
            'reason' => 'nullable|string',
        ]);

        $holiday = Holiday::findOrFail($validated['holiday_id']);
        $schoolUnitIds = $request->input('school_unit_ids');

        if (empty($schoolUnitIds)) {
            HolidayAdjustment::create([
                'holiday_id' => $holiday->id,
                'original_date' => $holiday->original_date,
                'adjusted_date' => $validated['adjusted_date'],
                'school_unit_id' => null,
                'reason' => $validated['reason'] ?? null,
            ]);
        } else {
            foreach ($schoolUnitIds as $unitId) {
                HolidayAdjustment::create([
                    'holiday_id' => $holiday->id,
                    'original_date' => $holiday->original_date,
                    'adjusted_date' => $validated['adjusted_date'],
                    'school_unit_id' => $unitId,
                    'reason' => $validated['reason'] ?? null,
                ]);
            }
        }

        // Auto sync
        $this->syncHolidaysToUnits();

        return redirect()->route('holidays.index')
            ->with('success', 'Penyesuaian hari libur berhasil dibuat.');
    }

    /**
     * Update the specified holiday in storage.
     */
    public function update(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'applies_to' => 'required|in:global,custom',
            'school_unit_ids' => 'required_if:applies_to,custom|array',
            'school_unit_ids.*' => 'exists:school_units,id',
            'ids' => 'required|array',
            'ids.*' => 'exists:holidays,id',
        ]);

        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $endDate = \Carbon\Carbon::parse($validated['end_date']);
        $isGlobal = $validated['applies_to'] === 'global';
        $schoolUnitIds = $request->input('school_unit_ids', []);
        $oldIds = $request->input('ids', []);

        // Validate that dates are not taken by other holidays (excluding the ones we are editing)
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dateStr = $currentDate->format('Y-m-d');
            $existing = Holiday::where('original_date', $dateStr)
                ->whereNotIn('id', $oldIds)
                ->first();
            if ($existing) {
                return redirect()->back()->withErrors(['start_date' => "Tanggal {$dateStr} sudah terdaftar sebagai hari libur lain ({$existing->name})."]);
            }
            $currentDate->addDay();
        }

        // Delete the old holidays in the group
        Holiday::whereIn('id', $oldIds)->delete();

        // Create the new holiday range
        $currentDate = $startDate->copy();
        $createdCount = 0;

        while ($currentDate->lte($endDate)) {
            $dateStr = $currentDate->format('Y-m-d');

            $holiday = Holiday::create([
                'name' => $validated['name'],
                'original_date' => $dateStr,
                'is_global' => $isGlobal,
            ]);

            // Create adjustments if custom
            if (!$isGlobal && !empty($schoolUnitIds)) {
                foreach ($schoolUnitIds as $unitId) {
                    HolidayAdjustment::create([
                        'holiday_id' => $holiday->id,
                        'original_date' => $dateStr,
                        'adjusted_date' => $dateStr,
                        'school_unit_id' => $unitId,
                        'reason' => $validated['name'],
                    ]);
                }
            }

            $currentDate->addDay();
            $createdCount++;
        }

        // Auto sync
        $this->syncHolidaysToUnits();

        return redirect()->back()
            ->with('success', 'Hari libur berhasil diperbarui.');
    }

    /**
     * Remove the specified holiday.
     */
    public function destroy(Holiday $holiday)
    {
        $ids = request('ids', [$holiday->id]);
        Holiday::whereIn('id', $ids)->delete();

        // Auto sync
        $this->syncHolidaysToUnits();

        return redirect()->back()
            ->with('success', 'Hari libur berhasil dihapus.');
    }

    /**
     * Remove adjustment.
     */
    public function destroyAdjustment($id)
    {
        $adj = HolidayAdjustment::findOrFail($id);
        $adj->delete();

        // Auto sync
        $this->syncHolidaysToUnits();

        return redirect()->back()
            ->with('success', 'Penyesuaian hari libur berhasil dihapus.');
    }

    /**
     * Store a newly created Holiday Reward.
     */
    public function storeReward(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'school_unit_ids' => 'required|array|min:1',
            'school_unit_ids.*' => 'exists:school_units,id',
            'target_type' => 'required|in:all,specific',
            'employee_ids' => 'nullable|array',
            'notes' => 'nullable|string|max:500',
        ]);

        $employeeIds = null;
        if ($validated['target_type'] === 'specific' && !empty($request->input('employee_ids'))) {
            $employeeIds = array_values(array_unique($request->input('employee_ids')));
        }

        HolidayReward::create([
            'name' => $validated['name'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'school_unit_ids' => array_values(array_map('intval', $validated['school_unit_ids'])),
            'employee_ids' => $employeeIds,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('holidays.index', ['tab' => 'reward'])
            ->with('success', 'Reward Hari Libur berhasil ditambahkan. Pegawai yang berhak akan menerima bonus kehadiran penuh pada tanggal tersebut.');
    }

    /**
     * Update the specified Holiday Reward.
     */
    public function updateReward(Request $request, $id)
    {
        $reward = HolidayReward::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'school_unit_ids' => 'required|array|min:1',
            'school_unit_ids.*' => 'exists:school_units,id',
            'target_type' => 'required|in:all,specific',
            'employee_ids' => 'nullable|array',
            'notes' => 'nullable|string|max:500',
        ]);

        $employeeIds = null;
        if ($validated['target_type'] === 'specific' && !empty($request->input('employee_ids'))) {
            $employeeIds = array_values(array_unique($request->input('employee_ids')));
        }

        $reward->update([
            'name' => $validated['name'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'school_unit_ids' => array_values(array_map('intval', $validated['school_unit_ids'])),
            'employee_ids' => $employeeIds,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('holidays.index', ['tab' => 'reward'])
            ->with('success', 'Reward Hari Libur berhasil diperbarui.');
    }

    /**
     * Remove the specified Holiday Reward.
     */
    public function destroyReward($id)
    {
        $reward = HolidayReward::findOrFail($id);
        $reward->delete();

        return redirect()->route('holidays.index', ['tab' => 'reward'])
            ->with('success', 'Reward Hari Libur berhasil dihapus.');
    }

    /**
     * Trigger manual synchronization.
     */
    public function triggerSync()
    {
        $this->syncHolidaysToUnits();
        return redirect()->back()
            ->with('success', 'Sinkronisasi data libur selesai.');
    }

    /**
     * Sync holidays to active units.
     */
    private function syncHolidaysToUnits()
    {
        $units = SchoolUnit::where('is_active', true)->get();
        $holidays = Holiday::with('adjustments')->get()->map(function ($h) {
            return [
                'name' => $h->name,
                'original_date' => $h->original_date->format('Y-m-d'),
                'is_global' => $h->is_global,
                'adjustments' => $h->adjustments->map(function ($adj) {
                    return [
                        'original_date' => $adj->original_date->format('Y-m-d'),
                        'adjusted_date' => $adj->adjusted_date->format('Y-m-d'),
                        'school_unit_id' => $adj->school_unit_id,
                        'reason' => $adj->reason,
                    ];
                })->toArray()
            ];
        })->toArray();

        foreach ($units as $unit) {
            try {
                Http::timeout(3)->withHeaders([
                    'X-API-TOKEN' => $unit->api_token,
                    'Accept' => 'application/json',
                ])->post(rtrim($unit->api_url, '/') . '/sync/holidays', [
                    'holidays' => $holidays
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to sync holidays to unit {$unit->name}: " . $e->getMessage());
            }
        }
    }

    /**
     * Helper to group consecutive holidays with the same name.
     */
    private function getGroupedHolidays()
    {
        $holidays = Holiday::with('adjustments.schoolUnit')->orderBy('original_date', 'asc')->get();
        $grouped = [];

        $byName = $holidays->groupBy('name');

        foreach ($byName as $name => $items) {
            $items = $items->sortBy('original_date')->values();
            
            if ($items->isEmpty()) continue;
            
            $currentGroup = [];
            $prevDate = null;
            
            foreach ($items as $item) {
                $currentDate = $item->original_date;
                
                if ($prevDate === null) {
                    $currentGroup[] = $item;
                } else {
                    $diff = abs($currentDate->diffInDays($prevDate));
                    if ($diff == 1) {
                        $currentGroup[] = $item;
                    } else {
                        $grouped[] = $this->buildGroupRow($name, $currentGroup);
                        $currentGroup = [$item];
                    }
                }
                $prevDate = $currentDate;
            }
            
            if (!empty($currentGroup)) {
                $grouped[] = $this->buildGroupRow($name, $currentGroup);
            }
        }

        usort($grouped, function ($a, $b) {
            return strcmp($b['start_date']->format('Y-m-d'), $a['start_date']->format('Y-m-d'));
        });

        return $grouped;
    }

    private function buildGroupRow($name, $items)
    {
        $startDate = $items[0]->original_date;
        $endDate = end($items)->original_date;
        
        $isGlobal = true;
        $adjustments = collect();
        $ids = [];
        $itemsData = [];
        $adjustmentsData = [];
        
        foreach ($items as $item) {
            $ids[] = $item->id;
            $itemsData[] = [
                'id' => $item->id,
                'date_formatted' => $item->original_date->format('d M Y'),
            ];
            if (!$item->is_global) {
                $isGlobal = false;
            }
            foreach ($item->adjustments as $adj) {
                $adjustments->push($adj);
                $adjustmentsData[] = [
                    'id' => $adj->id,
                    'school_unit_name' => $adj->schoolUnit ? $adj->schoolUnit->name : 'Semua Unit',
                    'original_date_formatted' => $adj->original_date->format('d M Y'),
                    'adjusted_date_formatted' => $adj->adjusted_date->format('d M Y'),
                    'reason' => $adj->reason ?? 'Tidak ada alasan',
                    'destroy_url' => route('holidays.destroy-adjustment', $adj->id),
                ];
            }
        }
        
        return [
            'name' => $name,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_global' => $isGlobal,
            'ids' => $ids,
            'items_data' => $itemsData,
            'adjustments_data' => $adjustmentsData,
            'adjustments' => $adjustments,
        ];
    }
}

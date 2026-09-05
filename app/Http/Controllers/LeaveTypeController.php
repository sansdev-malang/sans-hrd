<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use App\Models\SchoolUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LeaveTypeController extends Controller
{
    /**
     * Display a listing of leave types.
     */
    public function index(Request $request)
    {
        $query = LeaveType::orderBy('id', 'asc');

        if ($request->filled('unit')) {
            $unit = $request->input('unit');
            if ($unit === 'all') {
                $query->where('target_unit', 'all');
            } else {
                $query->where(function ($q) use ($unit) {
                    $q->where('target_unit', 'all')
                      ->orWhere('target_unit', $unit)
                      ->orWhere('target_unit', 'like', "%{$unit}%");
                });
            }
        }

        if ($request->filled('status_code')) {
            $query->where('status_code', $request->input('status_code'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $leaveTypes = $query->get();
        $schoolUnits = SchoolUnit::where('is_active', true)->orderBy('name')->get();

        // Statistics
        $totalCount = LeaveType::count();
        $globalCount = LeaveType::where('target_unit', 'all')->count();
        $bonusCount = LeaveType::where('gets_presence_bonus', true)->count();
        $autoApproveCount = LeaveType::where('requires_approval', false)->count();

        return view('leave_types.index', compact(
            'leaveTypes',
            'schoolUnits',
            'totalCount',
            'globalCount',
            'bonusCount',
            'autoApproveCount'
        ));
    }

    /**
     * Store a newly created leave type and push to school units.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status_code' => 'required|in:S,I,C,H',
            'target_unit' => 'required|string',
            'requires_attendance' => 'required|boolean',
            'requires_approval' => 'required|boolean',
            'gets_presence_bonus' => 'required|boolean',
        ]);

        $baseCode = Str::slug($validated['name']);
        $code = $baseCode;
        $counter = 1;
        while (LeaveType::where('code', $code)->exists()) {
            $code = $baseCode . '-' . $counter++;
        }
        $validated['code'] = $code;

        $leaveType = LeaveType::create($validated);

        // Push to units
        $syncResult = $this->pushLeaveTypeToUnits($leaveType, 'save');

        return redirect()->route('leave-types.index')
            ->with('success', "Tipe izin '{$leaveType->name}' berhasil ditambahkan dan disinkronkan ke unit sekolah ({$syncResult['success_count']} unit berhasil).");
    }

    /**
     * Update the specified leave type and sync to school units.
     */
    public function update(Request $request, $id)
    {
        $leaveType = LeaveType::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status_code' => 'required|in:S,I,C,H',
            'target_unit' => 'required|string',
            'requires_attendance' => 'required|boolean',
            'requires_approval' => 'required|boolean',
            'gets_presence_bonus' => 'required|boolean',
        ]);

        $leaveType->update($validated);

        // Push to units
        $syncResult = $this->pushLeaveTypeToUnits($leaveType, 'save');

        return redirect()->route('leave-types.index')
            ->with('success', "Tipe izin '{$leaveType->name}' berhasil diperbarui dan disinkronkan ke unit sekolah ({$syncResult['success_count']} unit berhasil).");
    }

    /**
     * Remove the specified leave type and sync deletion to units.
     */
    public function destroy($id)
    {
        $leaveType = LeaveType::findOrFail($id);
        $name = $leaveType->name;

        // Push delete to units
        $syncResult = $this->pushLeaveTypeToUnits($leaveType, 'delete');

        $leaveType->delete();

        return redirect()->route('leave-types.index')
            ->with('success', "Tipe izin '{$name}' berhasil dihapus dari HRD dan disinkronkan ke unit sekolah.");
    }

    /**
     * Pull and import all leave types from active school units.
     */
    public function pullFromUnits()
    {
        $units = SchoolUnit::where('is_active', true)->get();
        $importedCount = 0;
        $unitDataMap = [];

        foreach ($units as $unit) {
            if (!$unit->api_url || !$unit->api_token) continue;

            try {
                $response = Http::timeout(4)->withHeaders([
                    'X-API-TOKEN' => $unit->api_token,
                    'Accept' => 'application/json',
                ])->get(rtrim($unit->api_url, '/') . '/leave-types');

                if ($response->successful()) {
                    $types = $response->json();
                    if (isset($types['data'])) {
                        $types = $types['data'];
                    }

                    foreach ($types as $t) {
                        $code = Str::slug($t['name']);
                        if (!isset($unitDataMap[$code])) {
                            $unitDataMap[$code] = [
                                'name' => $t['name'],
                                'code' => $code,
                                'status_code' => $t['status_code'] ?? 'I',
                                'requires_attendance' => $t['requires_attendance'] ?? true,
                                'requires_approval' => $t['requires_approval'] ?? true,
                                'gets_presence_bonus' => $t['gets_presence_bonus'] ?? false,
                                'units' => [],
                            ];
                        }
                        $unitDataMap[$code]['units'][] = strtolower($unit->name);
                    }
                }
            } catch (\Exception $e) {
                Log::error("Failed pulling leave types from {$unit->name}: " . $e->getMessage());
            }
        }

        $allUnitNames = $units->map(fn($u) => strtolower($u->name))->toArray();

        foreach ($unitDataMap as $code => $data) {
            $isAll = count(array_intersect($allUnitNames, $data['units'])) >= (count($allUnitNames) - 1);
            $targetUnit = $isAll ? 'all' : implode(',', $data['units']);

            LeaveType::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $data['name'],
                    'status_code' => $data['status_code'],
                    'target_unit' => $targetUnit,
                    'requires_attendance' => $data['requires_attendance'],
                    'requires_approval' => $data['requires_approval'],
                    'gets_presence_bonus' => $data['gets_presence_bonus'],
                ]
            );
            $importedCount++;
        }

        // Clean up any old duplicate non-standard slugs
        LeaveType::whereNotIn('code', array_keys($unitDataMap))->delete();

        return redirect()->route('leave-types.index')
            ->with('success', "Berhasil menarik dan menyinkronkan {$importedCount} tipe izin dari unit sekolah.");
    }

    /**
     * Push all leave types to respective school units.
     */
    public function pushAllToUnits()
    {
        $leaveTypes = LeaveType::all();
        $totalSuccess = 0;

        foreach ($leaveTypes as $type) {
            $res = $this->pushLeaveTypeToUnits($type, 'save');
            $totalSuccess += $res['success_count'];
        }

        return redirect()->route('leave-types.index')
            ->with('success', "Berhasil mendorong seluruh tipe izin ke seluruh unit sekolah terkait.");
    }

    /**
     * Internal helper to broadcast leave type payload to target units.
     */
    protected function pushLeaveTypeToUnits(LeaveType $leaveType, string $action = 'save'): array
    {
        $units = SchoolUnit::where('is_active', true)->get();
        $successCount = 0;
        $failedCount = 0;

        foreach ($units as $unit) {
            if (!$unit->api_url || !$unit->api_token) continue;

            // Check if unit is in scope
            $unitCode = strtolower($unit->name);
            $target = strtolower($leaveType->target_unit);

            $inScope = ($target === 'all' || $target === $unitCode || str_contains($target, $unitCode));

            if (!$inScope && $action === 'save') {
                // If not in scope, ensure it's removed if it previously existed
                continue;
            }

            try {
                $response = Http::timeout(4)->withHeaders([
                    'X-API-TOKEN' => $unit->api_token,
                    'Accept' => 'application/json',
                ])->post(rtrim($unit->api_url, '/') . '/sync/leave-types', [
                    'action' => $action,
                    'code' => $leaveType->code,
                    'name' => $leaveType->name,
                    'status_code' => $leaveType->status_code,
                    'requires_attendance' => $leaveType->requires_attendance,
                    'requires_approval' => $leaveType->requires_approval,
                    'gets_presence_bonus' => $leaveType->gets_presence_bonus,
                ]);

                if ($response->successful()) {
                    $successCount++;
                } else {
                    $failedCount++;
                    Log::warning("Failed pushing leave type {$leaveType->name} to {$unit->name}: " . $response->body());
                }
            } catch (\Exception $e) {
                $failedCount++;
                Log::error("Exception pushing leave type {$leaveType->name} to {$unit->name}: " . $e->getMessage());
            }
        }

        return [
            'success_count' => $successCount,
            'failed_count' => $failedCount,
        ];
    }
}

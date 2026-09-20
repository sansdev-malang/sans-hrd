<?php

namespace App\Http\Controllers;

use App\Models\SchoolUnit;
use App\Services\SchoolUnitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PicketScheduleController extends Controller
{
    protected SchoolUnitService $service;

    public function __construct(SchoolUnitService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of picket schedules and swaps from all school units (Read-Only Matrix Board).
     */
    public function index(Request $request)
    {
        $unitId = $request->query('unit_id');
        $search = $request->query('search');
        $activeTab = $request->query('tab', 'schedules');

        $activeUnits = SchoolUnit::where('is_active', true)->orderBy('id')->get();

        // Default to first active unit (SD / SMP / PAUD) so it always displays one focused unit board
        if ((empty($unitId) || !$activeUnits->contains('id', $unitId)) && $activeUnits->isNotEmpty()) {
            $unitId = (string) $activeUnits->first()->id;
        }

        // Fetch synced picket assignments from all units
        $picketData = $this->service->getAllPicketAssignments();
        $rawSchedules = collect($picketData['schedules'] ?? []);
        $rawSwaps = collect($picketData['swaps'] ?? []);

        // Filter Swaps by selected unit
        $filteredSwaps = $rawSwaps;
        if (!empty($unitId)) {
            $filteredSwaps = $filteredSwaps->filter(function ($item) use ($unitId) {
                return (string) ($item['school_unit_id'] ?? '') === (string) $unitId;
            });
        }
        if (!empty($search)) {
            $keyword = strtolower(trim($search));
            $filteredSwaps = $filteredSwaps->filter(function ($item) use ($keyword) {
                $reqName = strtolower($item['requester_name'] ?? '');
                $targetName = strtolower($item['target_employee_name'] ?? '');
                $unit = strtolower($item['unit_name'] ?? '');
                return str_contains($reqName, $keyword) || str_contains($targetName, $keyword) || str_contains($unit, $keyword);
            });
        }

        // Sort swaps by date descending
        $swaps = $filteredSwaps->sortByDesc(function ($item) {
            return $item['target_date'] ?? $item['requested_date'] ?? '';
        })->values();

        // Group schedules by Unit and Area into Matrix Boards (identical to Unit layout)
        $unitBoards = [];
        $selectedUnitSchedules = collect();

        foreach ($activeUnits as $unit) {
            if (!empty($unitId) && (string) $unit->id !== (string) $unitId) {
                continue;
            }

            $unitSchedules = $rawSchedules->filter(function ($item) use ($unit) {
                return (string) ($item['school_unit_id'] ?? '') === (string) $unit->id;
            });
            $selectedUnitSchedules = $unitSchedules;

            $areasMap = [];
            foreach ($unitSchedules as $sched) {
                $areaKey = $sched['picket_area_id'] ?? $sched['picket_area_name'];
                if (!isset($areasMap[$areaKey])) {
                    $areasMap[$areaKey] = [
                        'id' => $sched['picket_area_id'] ?? null,
                        'name' => $sched['picket_area_name'] ?? 'Area Piket',
                        'duty_hours' => $sched['duty_hours'] ?? ($sched['start_time'] ? substr($sched['start_time'], 0, 5) . ' - ' . substr($sched['end_time'] ?? '07:00:00', 0, 5) : '06:30 - 07:00'),
                        'jobs' => $sched['picket_area_jobs'] ?? null,
                        'days' => [
                            1 => [],
                            2 => [],
                            3 => [],
                            4 => [],
                            5 => [],
                            6 => [],
                        ],
                    ];
                }
                $dayNum = (int) ($sched['day_of_week'] ?? 1);
                if (isset($areasMap[$areaKey]['days'][$dayNum])) {
                    $areasMap[$areaKey]['days'][$dayNum][] = $sched;
                }
            }

            $unitBoards[$unit->id] = [
                'unit' => $unit,
                'areas' => array_values($areasMap),
                'total_schedules' => $unitSchedules->count(),
            ];
        }

        // Summary statistics for currently selected unit
        $stats = [
            'total_assignments' => $selectedUnitSchedules->count(),
            'total_teachers' => $selectedUnitSchedules->pluck('employee_id')->unique()->count(),
            'total_areas' => $selectedUnitSchedules->pluck('picket_area_name')->unique()->count(),
            'total_swaps' => $filteredSwaps->count(),
        ];

        $days = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];

        return view('picket-schedules.index', compact(
            'activeUnits',
            'unitBoards',
            'swaps',
            'stats',
            'unitId',
            'search',
            'activeTab',
            'days'
        ));
    }

    /**
     * Force sync / refresh picket assignments cache from all units.
     */
    public function sync(Request $request)
    {
        // Flush all cached picket data
        Cache::forget('hrd_picket_assignments_all_all');

        // Trigger immediate fetch to warm up cache
        $this->service->getAllPicketAssignments();

        return redirect()->route('picket-schedules.index')
            ->with('success', 'Data jadwal piket dan tukar piket berhasil disinkronkan dari seluruh unit sekolah!');
    }
}

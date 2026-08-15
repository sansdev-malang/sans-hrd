<?php

namespace App\Http\Controllers;

use App\Models\EmployeeWorkingShift;
use App\Models\WorkingShift;
use App\Models\BonusSchema;
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
        $bonusSchemas = BonusSchema::orderBy('name')->get();
        
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

        $batches = [];
        $rosterBatches = [];

        foreach ($assignments as $assignment) {
            if ($assignment->roster_name === null) {
                // Permanent & Temporary Shifts (Standard Batch Assignments)
                $key = 'perm|' . $assignment->school_unit_id . '|' . $assignment->working_shift_id . '|' . $assignment->start_date->format('Y-m-d') . '|' . ($assignment->end_date ? $assignment->end_date->format('Y-m-d') : 'null');
                
                if (!isset($batches[$key])) {
                    $batches[$key] = [
                        'type' => 'permanent',
                        'school_unit_id' => $assignment->school_unit_id,
                        'working_shift_id' => $assignment->working_shift_id,
                        'start_date' => $assignment->start_date,
                        'end_date' => $assignment->end_date,
                        'unit_name' => $assignment->schoolUnit->name ?? 'Unknown',
                        'shift_name' => $assignment->workingShift->name ?? 'Unknown',
                        'shift_code' => $assignment->workingShift->code ?? '-',
                        
                        'employees' => [],
                        'sort_date' => $assignment->start_date->format('Y-m-d')
                    ];
                }

                $empKey = $assignment->school_unit_id . '-' . $assignment->employee_id;
                $batches[$key]['employees'][] = [
                    'id' => $assignment->employee_id,
                    'name' => $employeeMap[$empKey]['name'] ?? 'Pegawai #' . $assignment->employee_id,
                    'nip' => $employeeMap[$empKey]['nuptk_nip_nik'] ?? '-',
                    'photo' => $employeeMap[$empKey]['photo'] ?? null,
                    'unit_url' => $employeeMap[$empKey]['unit_url'] ?? null,
                    'position' => $employeeMap[$empKey]['position'] ?? $employeeMap[$empKey]['subject_position'] ?? null,
                ];
            } else {
                // Roster Shifts
                $month = $assignment->start_date->format('m');
                $year = $assignment->start_date->format('Y');
                $rosterNameKey = $assignment->roster_name ?: 'Roster Shift Bulanan';
                $key = 'roster|' . $assignment->school_unit_id . '|' . $year . '|' . $month . '|' . $rosterNameKey;

                if (!isset($rosterBatches[$key])) {
                    $rosterBatches[$key] = [
                        'type' => 'roster',
                        'school_unit_id' => $assignment->school_unit_id,
                        'month' => $month,
                        'year' => $year,
                        'unit_name' => $assignment->schoolUnit->name ?? 'Unknown',
                        'roster_name' => $assignment->roster_name,
                        'employees_map' => [],
                        'sort_date' => $year . '-' . $month . '-31'
                    ];
                }
                if ($assignment->roster_name) {
                    $rosterBatches[$key]['roster_name'] = $assignment->roster_name;
                }
                
                $empKey = $assignment->school_unit_id . '-' . $assignment->employee_id;
                if (!isset($rosterBatches[$key]['employees_map'][$assignment->employee_id])) {
                    $rosterBatches[$key]['employees_map'][$assignment->employee_id] = [
                        'id' => $assignment->employee_id,
                        'name' => $employeeMap[$empKey]['name'] ?? 'Pegawai #' . $assignment->employee_id,
                        'nip' => $employeeMap[$empKey]['nuptk_nip_nik'] ?? '-',
                        'photo' => $employeeMap[$empKey]['photo'] ?? null,
                        'unit_url' => $employeeMap[$empKey]['unit_url'] ?? null,
                        'position' => $employeeMap[$empKey]['position'] ?? $employeeMap[$empKey]['subject_position'] ?? null,
                    ];
                }
            }
        }

        // Convert roster employees map to array
        foreach ($rosterBatches as &$rBatch) {
            $rBatch['employees'] = array_values($rBatch['employees_map']);
            unset($rBatch['employees_map']);
        }

        $allBatches = array_merge(array_values($batches), array_values($rosterBatches));
        
        // Search Filter (Locally added filter support)
        $searchQuery = $request->query('search');
        if (!empty($searchQuery)) {
            $allBatches = array_filter($allBatches, function($batch) use ($searchQuery) {
                $searchQueryLower = strtolower($searchQuery);
                if (str_contains(strtolower($batch['unit_name']), $searchQueryLower)) {
                    return true;
                }
                if (isset($batch['roster_name']) && str_contains(strtolower($batch['roster_name']), $searchQueryLower)) {
                    return true;
                }
                if (isset($batch['shift_name']) && str_contains(strtolower($batch['shift_name']), $searchQueryLower)) {
                    return true;
                }
                if (isset($batch['shift_code']) && str_contains(strtolower($batch['shift_code']), $searchQueryLower)) {
                    return true;
                }
                foreach ($batch['employees'] as $emp) {
                    if (str_contains(strtolower($emp['name']), $searchQueryLower)) {
                        return true;
                    }
                    if (isset($emp['nip']) && str_contains(strtolower($emp['nip']), $searchQueryLower)) {
                        return true;
                    }
                }
                return false;
            });
        }
        
        // Sort by Unit Name ASC, then Type (roster > permanent), then sort_date DESC
        usort($allBatches, function($a, $b) {
            $unitCompare = strcmp($a['unit_name'], $b['unit_name']);
            if ($unitCompare !== 0) {
                return $unitCompare;
            }
            
            if ($a['type'] !== $b['type']) {
                return $a['type'] === 'roster' ? -1 : 1;
            }

            return strcmp($b['sort_date'], $a['sort_date']);
        });

        // Paginate the array manually in PHP
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $perPageQuery = $request->query('per_page', '50');
        
        if ($perPageQuery === 'all') {
            $perPage = 1000000;
        } else {
            $perPage = in_array((int)$perPageQuery, [10, 25, 50, 100, 500]) ? (int)$perPageQuery : 50;
        }
        
        $itemCollection = collect($allBatches);
        $currentPageItems = $itemCollection->slice(($currentPage - 1) * $perPage, $perPage)->all();
        
        $paginatedBatches = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageItems,
            $itemCollection->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Fetch neglected employees (employees without active shift schedules today)
        $todayStr = date('Y-m-d');
        $firstDayOfMonth = date('Y-m-01');
        $lastDayOfMonth = date('Y-m-t');

        $activeAssignments = EmployeeWorkingShift::where('start_date', '<=', $todayStr)
            ->where(function($q) use ($todayStr) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', $todayStr);
            })
            ->get()
            ->groupBy(function($item) {
                return $item->school_unit_id . '-' . $item->employee_id;
            });

        // Also query employees on roster this month (if on roster, today might be "OFF" shift so no record exists for today, but they are scheduled)
        $rosterAssignments = EmployeeWorkingShift::whereNotNull('roster_name')
            ->where(function($q) use ($firstDayOfMonth, $lastDayOfMonth) {
                $q->whereBetween('start_date', [$firstDayOfMonth, $lastDayOfMonth])
                  ->orWhereBetween('end_date', [$firstDayOfMonth, $lastDayOfMonth]);
            })
            ->get()
            ->groupBy(function($item) {
                return $item->school_unit_id . '-' . $item->employee_id;
            });

        $neglectedEmployees = [];
        foreach ($employees as $emp) {
            $key = $emp['unit_id'] . '-' . $emp['id'];
            $hasActiveToday = isset($activeAssignments[$key]);
            $hasRosterThisMonth = isset($rosterAssignments[$key]);

            if (!$hasActiveToday && !$hasRosterThisMonth) {
                $neglectedEmployees[] = [
                    'id' => $emp['id'],
                    'name' => $emp['name'],
                    'nip' => $emp['nuptk_nip_nik'] ?? '-',
                    'unit_id' => $emp['unit_id'],
                    'unit_name' => $emp['unit_name'] ?? 'Unknown',
                    'position' => $emp['position'] ?? $emp['subject_position'] ?? '-',
                ];
            }
        }

        if ($selectedUnitId) {
            $neglectedEmployees = array_filter($neglectedEmployees, function($emp) use ($selectedUnitId) {
                return $emp['unit_id'] == $selectedUnitId;
            });
            $neglectedEmployees = array_values($neglectedEmployees);
        }

        return view('employee-working-shifts.index', [
            'batches' => $paginatedBatches,
            'units' => $units,
            'shifts' => $shifts,
            'bonusSchemas' => $bonusSchemas,
            'selectedUnitId' => $selectedUnitId,
            'perPage' => $perPageQuery,
            'neglectedEmployees' => $neglectedEmployees
        ]);
    }

    public function editBatch(Request $request)
    {
        return redirect()->route('employee-working-shifts.index');
    }

    public function updateBatch(Request $request)
    {
        $validated = $request->validate([
            'old_school_unit_id' => 'required',
            'old_working_shift_id' => 'required',
            'old_start_date' => 'required',
            'old_end_date' => 'nullable',
            
            'school_unit_id' => 'required|exists:school_units,id',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'integer',
            'working_shift_id' => 'required|exists:working_shifts,id',
            'bonus_schema_id' => 'nullable|exists:bonus_schemas,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $oldEnd = $validated['old_end_date'] === 'null' ? null : $validated['old_end_date'];

        EmployeeWorkingShift::where('school_unit_id', $validated['old_school_unit_id'])
            ->where('working_shift_id', $validated['old_working_shift_id'])
            ->where('start_date', $validated['old_start_date'])
            ->when($oldEnd !== null, function($q) use ($oldEnd) {
                return $q->where('end_date', $oldEnd);
            }, function($q) {
                return $q->whereNull('end_date');
            })->delete();

        foreach ($validated['employee_ids'] as $employeeId) {
            EmployeeWorkingShift::create([
                'school_unit_id' => $validated['school_unit_id'],
                'employee_id' => $employeeId,
                'working_shift_id' => $validated['working_shift_id'],
                'bonus_schema_id' => $validated['bonus_schema_id'] ?? null,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? null,
            ]);
        }

        $this->syncSchedulesToUnit($validated['old_school_unit_id']);
        if ($validated['old_school_unit_id'] != $validated['school_unit_id']) {
            $this->syncSchedulesToUnit($validated['school_unit_id']);
        }

        return redirect()->route('employee-working-shifts.index')->with('success', 'Batch jadwal shift berhasil diperbarui.');
    }

    public function destroyBatch(Request $request)
    {
        $unit_id = $request->input('unit_id');
        $shift_id = $request->input('shift_id');
        $start = $request->input('start_date');
        $end = $request->input('end_date');
        $end = $end === 'null' ? null : $end;

        EmployeeWorkingShift::where('school_unit_id', $unit_id)
            ->where('working_shift_id', $shift_id)
            ->where('start_date', $start)
            ->when($end !== null, function($q) use ($end) {
                return $q->where('end_date', $end);
            }, function($q) {
                return $q->whereNull('end_date');
            })->delete();

        $this->syncSchedulesToUnit($unit_id);

        return redirect()->route('employee-working-shifts.index')->with('success', 'Batch jadwal shift berhasil dihapus.');
    }

    public function destroyRoster(Request $request)
    {
        $unit_id = $request->input('unit_id');
        $month = $request->input('month');
        $year = $request->input('year');
        $rosterName = $request->input('roster_name');

        $firstDay = \Carbon\Carbon::create($year, $month, 1)->startOfDay();
        $lastDay = $firstDay->copy()->endOfMonth();

        $query = EmployeeWorkingShift::where('school_unit_id', $unit_id)
            ->whereNotNull('end_date')
            ->where(function($query) use ($firstDay, $lastDay) {
                $query->whereBetween('start_date', [$firstDay, $lastDay])
                      ->orWhereBetween('end_date', [$firstDay, $lastDay])
                      ->orWhere(function($q) use ($firstDay, $lastDay) {
                          $q->where('start_date', '<', $firstDay)
                            ->where('end_date', '>', $lastDay);
                      });
            });

        if (!empty($rosterName)) {
            $query->where('roster_name', $rosterName);
        } else {
            $query->where(function($q) {
                $q->whereNull('roster_name')->orWhere('roster_name', '');
            });
        }

        $query->delete();

        $this->syncSchedulesToUnit($unit_id);

        return redirect()->route('employee-working-shifts.index')->with('success', 'Roster shift bulanan berhasil dihapus.');
    }

    /**
     * Show the form for assigning new shifts (Bulk).
     */
    public function create()
    {
        $units = SchoolUnit::where('is_active', true)->orderBy('name')->get();
        $shifts = WorkingShift::where('is_shift', false)->orderBy('name')->get();
        $bonusSchemas = BonusSchema::all(); return view('employee-working-shifts.create', compact('units', 'shifts', 'bonusSchemas'));
    }

    /**
     * Fetch employees of a specific school unit via AJAX.
     */
    public function getEmployeesByUnit(Request $request, $unitId)
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
                $employees = $response->json('data') ?? [];
                
                $month = $request->query('month');
                $year = $request->query('year');
                
                if ($month && $year) {
                    $firstDay = \Carbon\Carbon::create($year, $month, 1)->startOfDay();
                    $lastDay = $firstDay->copy()->endOfMonth();
                    $empIds = collect($employees)->pluck('id')->toArray();
                    
                    $rosters = EmployeeWorkingShift::whereIn('employee_id', $empIds)
                        ->whereNotNull('roster_name')
                        ->where(function($query) use ($firstDay, $lastDay) {
                            $query->whereBetween('start_date', [$firstDay, $lastDay])
                                  ->orWhereBetween('end_date', [$firstDay, $lastDay])
                                  ->orWhere(function($q) use ($firstDay, $lastDay) {
                                      $q->where('start_date', '<', $firstDay)
                                        ->where(function($q2) use ($lastDay) {
                                            $q2->whereNull('end_date')->orWhere('end_date', '>=', $lastDay);
                                        });
                                  });
                        })
                        ->get()
                        ->groupBy('employee_id');
                        
                    foreach ($employees as &$emp) {
                        $empId = $emp['id'];
                        $emp['active_roster_name'] = isset($rosters[$empId]) ? $rosters[$empId]->first()->roster_name : null;
                    }
                }
                
                return response()->json($employees);
            }
        } catch (\Exception $e) {
            Log::error("Failed to fetch employees for unit {$unitId}: " . $e->getMessage());
        }

        return response()->json([]);
    }

    /**
     * Get employee IDs assigned to a specific roster.
     */
    public function getRosterEmployees(Request $request)
    {
        $unitId = $request->query('unit_id');
        $month = $request->query('month');
        $year = $request->query('year');
        $rosterName = $request->query('roster_name');

        if (!$unitId || !$month || !$year || !$rosterName) {
            return response()->json([]);
        }

        $firstDay = \Carbon\Carbon::create($year, $month, 1)->startOfDay();
        $lastDay = $firstDay->copy()->endOfMonth();

        $employeeIds = EmployeeWorkingShift::where('school_unit_id', $unitId)
            ->where('roster_name', $rosterName)
            ->where(function($query) use ($firstDay, $lastDay) {
                $query->whereBetween('start_date', [$firstDay, $lastDay])
                      ->orWhereBetween('end_date', [$firstDay, $lastDay])
                      ->orWhere(function($q) use ($firstDay, $lastDay) {
                          $q->where('start_date', '<', $firstDay)
                            ->where(function($q2) use ($lastDay) {
                                $q2->whereNull('end_date')->orWhere('end_date', '>=', $lastDay);
                            });
                      });
            })
            ->pluck('employee_id')
            ->unique()
            ->values()
            ->toArray();

        return response()->json($employeeIds);
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
            'bonus_schema_id' => 'nullable|exists:bonus_schemas,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        foreach ($validated['employee_ids'] as $employeeId) {
            // Find active permanent shift assignment overlap (if any)
            $activePermanent = EmployeeWorkingShift::where('school_unit_id', $validated['school_unit_id'])
                ->where('employee_id', $employeeId)
                ->whereNull('end_date')
                ->first();

            if ($activePermanent) {
                $parentStart = $activePermanent->start_date->format('Y-m-d');
                $hMinus1 = date('Y-m-d', strtotime($validated['start_date'] . ' -1 day'));
                
                if ($hMinus1 >= $parentStart) {
                    // Close the old permanent shift at H-1 of the new shift
                    $activePermanent->update(['end_date' => $hMinus1]);
                    
                    // Resume the old permanent shift starting from H+1 of the new shift's end_date
                    if (!empty($validated['end_date'])) {
                        EmployeeWorkingShift::create([
                            'school_unit_id' => $validated['school_unit_id'],
                            'employee_id' => $employeeId,
                            'working_shift_id' => $activePermanent->working_shift_id,
                            'bonus_schema_id' => $activePermanent->bonus_schema_id,
                            'start_date' => date('Y-m-d', strtotime($validated['end_date'] . ' +1 day')),
                            'end_date' => null,
                        ]);
                    }
                } else {
                    // If the new shift starts before or at the start of the old permanent shift,
                    // we just delay the start of the old permanent shift until H+1 of the new temporary shift
                    if (!empty($validated['end_date'])) {
                        $activePermanent->update([
                            'start_date' => date('Y-m-d', strtotime($validated['end_date'] . ' +1 day'))
                      ]);
                    } else {
                        // If the new shift is permanent and starts before the old one, the new one completely replaces the old one
                        $activePermanent->delete();
                    }
                }
            }

            // Create the new shift assignment
            EmployeeWorkingShift::create([
                'school_unit_id' => $validated['school_unit_id'],
                'employee_id' => $employeeId,
                'working_shift_id' => $validated['working_shift_id'],
                'bonus_schema_id' => $validated['bonus_schema_id'] ?? null,
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

    public function detailRoster(Request $request)
    {
        $units = SchoolUnit::where('is_active', true)->orderBy('name')->get();
        $selectedUnitId = $request->query('unit_id');
        $year = $request->query('year');
        $month = $request->query('month');
        $rosterNameParam = $request->query('roster_name');

        if (!$selectedUnitId || !$year || !$month) {
            return redirect()->route('employee-working-shifts.index')->with('error', 'Parameter tidak lengkap.');
        }

        $shifts = WorkingShift::orderBy('name')->get();
        
        $colors = ['bg-indigo-100 text-indigo-700', 'bg-emerald-100 text-emerald-700', 'bg-amber-100 text-amber-700', 'bg-sky-100 text-sky-700', 'bg-purple-100 text-purple-700'];
        $hexBg = ['#e0e7ff', '#d1fae5', '#fef3c7', '#e0f2fe', '#f3e8ff'];
        $hexText = ['#4338ca', '#047857', '#b45309', '#0369a1', '#7e22ce'];
        foreach ($shifts as $index => $shift) {
            $shift->color = 'shift-color-' . $shift->id;
            $shift->hex_bg = $hexBg[$index % count($hexBg)];
            $shift->hex_text = $hexText[$index % count($hexText)];
        }
        $bonusSchemas = \App\Models\BonusSchema::where('is_active', true)->orderBy('name')->get();
        $employees = [];
        $rosterData = [];
        $daysInMonth = \Carbon\Carbon::create($year, $month, 1)->daysInMonth;

        $unitService = app(\App\Services\SchoolUnitService::class);
        $allEmployees = $unitService->getSdEmployees();
        $rawEmployees = array_filter($allEmployees, function($emp) use ($selectedUnitId) {
            return $emp['unit_id'] == $selectedUnitId;
        });

        $firstDay = \Carbon\Carbon::create($year, $month, 1)->startOfDay();
        $lastDay = $firstDay->copy()->endOfMonth();

        $assignmentsQuery = \App\Models\EmployeeWorkingShift::where('school_unit_id', $selectedUnitId)
            ->whereNotNull('roster_name')
            ->where(function($query) use ($firstDay, $lastDay) {
                $query->whereBetween('start_date', [$firstDay, $lastDay])
                      ->orWhereBetween('end_date', [$firstDay, $lastDay])
                      ->orWhere(function($q) use ($firstDay, $lastDay) {
                          $q->where('start_date', '<', $firstDay)
                            ->where(function($q2) use ($lastDay) {
                                $q2->where('end_date', '>', $lastDay)->orWhereNull('end_date');
                            });
                      });
            });
            
        if ($rosterNameParam) {
            $assignmentsQuery->where('roster_name', $rosterNameParam);
        }
        
        $assignments = $assignmentsQuery->get();

        $rosterName = $assignments->firstWhere('roster_name', '!=', null)->roster_name ?? ($rosterNameParam ?: 'Roster Shift Bulanan');
        
        $assignedEmployeeIds = $assignments->pluck('employee_id')->unique()->toArray();
        
        // Filter employees to ONLY those who have assignments in this month
        $employees = array_filter($rawEmployees, function($emp) use ($assignedEmployeeIds) {
            return in_array($emp['id'], $assignedEmployeeIds);
        });
        $employees = array_values($employees);

        foreach ($employees as $emp) {
            $empId = $emp['id'];
            $rosterData[$empId] = [
                'bonus_schema_id' => null,
                'days' => array_fill(1, $daysInMonth, null)
            ];
        }

        foreach ($assignments as $assignment) {
            $empId = $assignment->employee_id;
            if (!isset($rosterData[$empId])) continue;

            if (!$rosterData[$empId]['bonus_schema_id']) {
                $rosterData[$empId]['bonus_schema_id'] = $assignment->bonus_schema_id;
            }

            $start = \Carbon\Carbon::parse($assignment->start_date);
            $end = $assignment->end_date ? \Carbon\Carbon::parse($assignment->end_date) : $lastDay;
            
            if ($start < $firstDay) $start = $firstDay->copy();
            if ($end > $lastDay) $end = $lastDay->copy();

            for ($d = $start->day; $d <= $end->day; $d++) {
                $rosterData[$empId]['days'][$d] = $assignment->working_shift_id;
            }
        }

        return view('employee-working-shifts.detail-roster', compact('units', 'selectedUnitId', 'year', 'month', 'shifts', 'bonusSchemas', 'employees', 'rosterData', 'daysInMonth', 'rosterName'));
    }

    /**
     * Show the roster grid view.
     */
    public function roster(Request $request)
    {
        $units = \App\Models\SchoolUnit::where('is_active', true)->orderBy('name')->get();
        $selectedUnitId = $request->query('unit_id', $units->first()->id ?? null);
        $year = $request->query('year', date('Y'));
        $month = $request->query('month', date('m'));
        $rosterNameParam = $request->query('roster_name');
        $empIdsParam = $request->query('emp_ids', []);
        
        $selectedShiftIds = $request->query('shift_ids');
        $allShifts = \App\Models\WorkingShift::orderBy('name')->get();
        $colors = ['bg-indigo-100 text-indigo-700', 'bg-emerald-100 text-emerald-700', 'bg-amber-100 text-amber-700', 'bg-sky-100 text-sky-700', 'bg-purple-100 text-purple-700'];
        $hexBg = ['#e0e7ff', '#d1fae5', '#fef3c7', '#e0f2fe', '#f3e8ff'];
        $hexText = ['#4338ca', '#047857', '#b45309', '#0369a1', '#7e22ce'];
        foreach ($allShifts as $index => $shift) {
            $shift->color = 'shift-color-' . $shift->id;
            $shift->hex_bg = $hexBg[$index % count($hexBg)] ?? '#e0e7ff';
            $shift->hex_text = $hexText[$index % count($hexText)] ?? '#4338ca';
        }
        
        $bonusSchemas = \App\Models\BonusSchema::all();

        $employees = [];
        $rosterData = [];

        if ($selectedUnitId) {
            $unit = \App\Models\SchoolUnit::find($selectedUnitId);
            if ($unit) {
                try {
                    $resp = \Illuminate\Support\Facades\Http::withHeaders([
                        'X-API-TOKEN' => $unit->api_token,
                        'Accept' => 'application/json',
                    ])->timeout(5)->get(rtrim($unit->api_url, '/') . '/employees');
                    
                    if ($resp->successful()) {
                        $rawEmployees = $resp->json('data') ?? [];
                        
                        // Get all employees who have a permanent schedule (end_date IS NULL)
                        $permanentEmployees = \App\Models\EmployeeWorkingShift::where('school_unit_id', $selectedUnitId)
                            ->whereNull('end_date')
                            ->pluck('employee_id')
                            ->toArray();

                        // Filter out permanent employees
                        $employees = array_filter($rawEmployees, function($emp) use ($permanentEmployees) {
                            return !in_array($emp['id'], $permanentEmployees);
                        });
                        
                        // Re-index array for blade
                        $employees = array_values($employees);

                        if (!empty($empIdsParam)) {
                            // Check for conflicts: employee is assigned to a DIFFERENT roster name in this month
                            $firstDay = \Carbon\Carbon::create($year, $month, 1)->startOfDay();
                            $lastDay = $firstDay->copy()->endOfMonth();
                            
                            $conflictingAssignments = \App\Models\EmployeeWorkingShift::whereIn('employee_id', $empIdsParam)
                                ->whereNotNull('roster_name')
                                ->where('roster_name', '!=', $rosterNameParam)
                                ->where(function($query) use ($firstDay, $lastDay) {
                                    $query->whereBetween('start_date', [$firstDay, $lastDay])
                                          ->orWhereBetween('end_date', [$firstDay, $lastDay])
                                          ->orWhere(function($q) use ($firstDay, $lastDay) {
                                              $q->where('start_date', '<', $firstDay)
                                                ->where(function($q2) use ($lastDay) {
                                                    $q2->whereNull('end_date')->orWhere('end_date', '>=', $lastDay);
                                                });
                                          });
                                })
                                ->get();
                                
                            if ($conflictingAssignments->count() > 0) {
                                $conflict = $conflictingAssignments->first();
                                $conflictEmpId = $conflict->employee_id;
                                $conflictRosterName = $conflict->roster_name;
                                
                                $conflictEmpName = 'Pegawai';
                                foreach ($employees as $emp) {
                                    if ($emp['id'] == $conflictEmpId) {
                                        $conflictEmpName = $emp['name'];
                                        break;
                                    }
                                }
                                
                                return redirect()->route('employee-working-shifts.index')
                                    ->with('error', "Gagal membuat roster baru: {$conflictEmpName} sudah terdaftar pada Roster \"{$conflictRosterName}\" di bulan ini. Silakan edit roster tersebut atau keluarkan pegawai dari roster aktif tersebut terlebih dahulu.");
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to fetch employees for roster: " . $e->getMessage());
                }

                // Fetch assignments overlapping this month
                $firstDay = \Carbon\Carbon::create($year, $month, 1)->startOfDay();
                $lastDay = $firstDay->copy()->endOfMonth();
                $daysInMonth = $lastDay->day;

                $assignmentsQuery = \App\Models\EmployeeWorkingShift::where('school_unit_id', $selectedUnitId)
                    ->where(function($query) use ($firstDay, $lastDay) {
                        $query->whereBetween('start_date', [$firstDay, $lastDay])
                              ->orWhereBetween('end_date', [$firstDay, $lastDay])
                              ->orWhere(function($q) use ($firstDay, $lastDay) {
                                  $q->where('start_date', '<', $firstDay)
                                    ->where(function($q2) use ($lastDay) {
                                        $q2->whereNull('end_date')
                                           ->orWhere('end_date', '>=', $lastDay);
                                    });
                              });
                    });
                    
                if ($rosterNameParam) {
                    $assignmentsQuery->where('roster_name', $rosterNameParam);
                }
                
                $assignments = $assignmentsQuery->get();
                
                $assignedEmployeeIds = $assignments->pluck('employee_id')->unique()->toArray();
                $rosterName = $assignments->firstWhere('roster_name', '!=', null)->roster_name ?? ($rosterNameParam ?: 'Roster Shift Bulanan');



                // Build roster array
                foreach ($employees as $emp) {
                    $empId = $emp['id'];
                    $rosterData[$empId] = [
                        'bonus_schema_id' => null,
                        'days' => array_fill(1, $daysInMonth, null)
                    ];
                }

                foreach ($assignments as $assignment) {
                    $empId = $assignment->employee_id;
                    if (!isset($rosterData[$empId])) continue;

                    // Set default bonus schema from the first assignment found
                    if (!$rosterData[$empId]['bonus_schema_id']) {
                        $rosterData[$empId]['bonus_schema_id'] = $assignment->bonus_schema_id;
                    }

                    $start = \Carbon\Carbon::parse($assignment->start_date);
                    $end = $assignment->end_date ? \Carbon\Carbon::parse($assignment->end_date) : $lastDay;
                    
                    // Constrain to current month bounds
                    if ($start < $firstDay) $start = $firstDay->copy();
                    if ($end > $lastDay) $end = $lastDay->copy();

                    for ($d = $start->day; $d <= $end->day; $d++) {
                        $rosterData[$empId]['days'][$d] = $assignment->working_shift_id;
                    }
                }
                
                if ($selectedShiftIds === null) {
                    $selectedShiftIds = $assignments->whereNotNull('end_date')->pluck('working_shift_id')->filter()->unique()->toArray();
                }
            }
        }

        $selectedShiftIds = $selectedShiftIds ?? [];
        if (is_array($selectedShiftIds) && count($selectedShiftIds) > 0) {
            $shifts = $allShifts->whereIn('id', $selectedShiftIds)->values();
        } else {
            $shifts = $allShifts;
        }

        $daysInMonth = \Carbon\Carbon::create($year, $month, 1)->daysInMonth;
        
        $oldRosterName = $rosterNameParam; // To know which roster to update

        return view('employee-working-shifts.roster', compact('units', 'selectedUnitId', 'year', 'month', 'shifts', 'allShifts', 'selectedShiftIds', 'bonusSchemas', 'employees', 'rosterData', 'daysInMonth', 'rosterName', 'oldRosterName', 'assignedEmployeeIds', 'empIdsParam'));
    }

    /**
     * Save the roster grid.
     */
    public function updateRoster(Request $request)
    {
        $validated = $request->validate([
            'school_unit_id' => 'required|exists:school_units,id',
            'year' => 'required|numeric',
            'month' => 'required|numeric|min:1|max:12',
            'roster' => 'array',
            'roster_name' => 'required|string|max:255',
            'old_roster_name' => 'nullable|string|max:255',
        ]);

        $unitId = $validated['school_unit_id'];
        $year = $validated['year'];
        $month = $validated['month'];
        $rosterInput = $validated['roster'] ?? [];
        $rosterName = $validated['roster_name'];
        $oldRosterName = $validated['old_roster_name'] ?? $rosterName;

        $firstDay = \Carbon\Carbon::create($year, $month, 1)->startOfDay();
        $lastDay = $firstDay->copy()->endOfMonth();

        // Check for conflicts: employee is assigned to a DIFFERENT roster name in this month
        $submittedEmpIds = array_keys($rosterInput);
        if (!empty($submittedEmpIds)) {
            $conflictingQuery = \App\Models\EmployeeWorkingShift::whereIn('employee_id', $submittedEmpIds)
                ->whereNotNull('roster_name')
                ->where('roster_name', '!=', $rosterName);

            if ($oldRosterName) {
                $conflictingQuery->where('roster_name', '!=', $oldRosterName);
            }

            $conflictingAssignments = $conflictingQuery->where(function($query) use ($firstDay, $lastDay) {
                    $query->whereBetween('start_date', [$firstDay, $lastDay])
                          ->orWhereBetween('end_date', [$firstDay, $lastDay])
                          ->orWhere(function($q) use ($firstDay, $lastDay) {
                              $q->where('start_date', '<', $firstDay)
                                ->where(function($q2) use ($lastDay) {
                                    $q2->whereNull('end_date')->orWhere('end_date', '>=', $lastDay);
                                });
                          });
                })
                ->get();
                
            if ($conflictingAssignments->count() > 0) {
                $conflict = $conflictingAssignments->first();
                $conflictEmpId = $conflict->employee_id;
                $conflictRosterName = $conflict->roster_name;
                
                $conflictEmpName = 'Pegawai';
                $unit = \App\Models\SchoolUnit::find($unitId);
                if ($unit) {
                    try {
                        $resp = \Illuminate\Support\Facades\Http::withHeaders([
                            'X-API-TOKEN' => $unit->api_token,
                            'Accept' => 'application/json',
                        ])->timeout(5)->get(rtrim($unit->api_url, '/') . '/employees');
                        if ($resp->successful()) {
                            $rawEmployees = $resp->json('data') ?? [];
                            foreach ($rawEmployees as $emp) {
                                if ($emp['id'] == $conflictEmpId) {
                                    $conflictEmpName = $emp['name'];
                                    break;
                                }
                            }
                        }
                    } catch (\Exception $e) {}
                }
                
                return back()->with('error', "Gagal menyimpan roster: {$conflictEmpName} sudah terdaftar pada Roster \"{$conflictRosterName}\" di bulan ini. Silakan keluarkan pegawai dari roster aktif tersebut terlebih dahulu.");
            }
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            if ($oldRosterName) {
                $existingRosterEmployeeIds = \App\Models\EmployeeWorkingShift::where('school_unit_id', $unitId)
                    ->where('roster_name', $oldRosterName)
                    ->where(function($query) use ($firstDay, $lastDay) {
                        $query->whereBetween('start_date', [$firstDay, $lastDay])
                              ->orWhereBetween('end_date', [$firstDay, $lastDay])
                              ->orWhere(function($q) use ($firstDay, $lastDay) {
                                  $q->where('start_date', '<', $firstDay)
                                    ->where(function($q2) use ($lastDay) {
                                        $q2->whereNull('end_date')->orWhere('end_date', '>=', $lastDay);
                                    });
                              });
                    })
                    ->pluck('employee_id')
                    ->unique()
                    ->toArray();

                $submittedEmployeeIds = array_keys($rosterInput);
                $removedEmployeeIds = array_diff($existingRosterEmployeeIds, $submittedEmployeeIds);

                if (!empty($removedEmployeeIds)) {
                    \App\Models\EmployeeWorkingShift::whereIn('employee_id', $removedEmployeeIds)
                        ->where('school_unit_id', $unitId)
                        ->where('roster_name', $oldRosterName)
                        ->where(function($query) use ($firstDay, $lastDay) {
                            $query->whereBetween('start_date', [$firstDay, $lastDay])
                                  ->orWhereBetween('end_date', [$firstDay, $lastDay])
                                  ->orWhere(function($q) use ($firstDay, $lastDay) {
                                      $q->where('start_date', '<', $firstDay)
                                        ->where(function($q2) use ($lastDay) {
                                            $q2->whereNull('end_date')->orWhere('end_date', '>=', $lastDay);
                                        });
                                  });
                        })
                        ->delete();
                }
            }

            foreach ($rosterInput as $empId => $data) {
                $bonusSchemaId = $data['bonus_schema_id'] ?? null;
                $days = $data['days'] ?? [];

                // 1. Splitting logic for existing shifts
                $overlappingShiftsQuery = \App\Models\EmployeeWorkingShift::where('employee_id', $empId)
                    ->where('school_unit_id', $unitId)
                    ->where(function($query) use ($firstDay, $lastDay) {
                        $query->whereBetween('start_date', [$firstDay, $lastDay])
                              ->orWhereBetween('end_date', [$firstDay, $lastDay])
                              ->orWhere(function($q) use ($firstDay, $lastDay) {
                                  $q->where('start_date', '<', $firstDay)
                                    ->where(function($q2) use ($lastDay) {
                                        $q2->where('end_date', '>', $lastDay)->orWhereNull('end_date');
                                    });
                              });
                    });
                    
                if ($oldRosterName) {
                    $overlappingShiftsQuery->where('roster_name', $oldRosterName);
                }
                
                $overlappingShifts = $overlappingShiftsQuery->get();

                foreach ($overlappingShifts as $shift) {
                    $shiftStartDateStr = $shift->start_date ? $shift->start_date->format('Y-m-d') : null;
                    $shiftEndDateStr = $shift->end_date ? $shift->end_date->format('Y-m-d') : null;
                    $firstDayStr = $firstDay->format('Y-m-d');
                    $lastDayStr = $lastDay->format('Y-m-d');

                    if ($shiftStartDateStr < $firstDayStr && ($shiftEndDateStr > $lastDayStr || is_null($shiftEndDateStr))) {
                        // Encompasses whole month -> Split into two
                        $newShift = $shift->replicate();
                        $newShift->start_date = $lastDay->copy()->addDay()->format('Y-m-d');
                        $newShift->save();
                        
                        $shift->end_date = $firstDay->copy()->subDay()->format('Y-m-d');
                        $shift->save();
                    } elseif ($shiftStartDateStr < $firstDayStr) {
                        // Starts before, ends within -> Trim end
                        $shift->end_date = $firstDay->copy()->subDay()->format('Y-m-d');
                        $shift->save();
                    } elseif ($shiftEndDateStr > $lastDayStr || is_null($shiftEndDateStr)) {
                        // Starts within, ends after -> Trim start
                        $shift->start_date = $lastDay->copy()->addDay()->format('Y-m-d');
                        $shift->save();
                    } else {
                        // Entirely within -> Delete
                        $shift->delete();
                    }
                }

                // 2. Merging logic for new shifts
                $currentShiftId = null;
                $rangeStart = null;
                $rangeEnd = null;

                for ($d = 1; $d <= $lastDay->day; $d++) {
                    $shiftId = $days[$d] ?? null;
                    
                    if ($shiftId != $currentShiftId) {
                        // Save previous range
                        if ($currentShiftId !== null && $currentShiftId !== '' && $currentShiftId !== 'OFF') {
                            \App\Models\EmployeeWorkingShift::create([
                                'employee_id' => $empId,
                                'school_unit_id' => $unitId,
                                'working_shift_id' => $currentShiftId,
                                'bonus_schema_id' => $bonusSchemaId,
                                'start_date' => $firstDay->copy()->day($rangeStart)->format('Y-m-d'),
                                'end_date' => $firstDay->copy()->day($rangeEnd)->format('Y-m-d'),
                                'roster_name' => $rosterName,
                            ]);
                        }
                        
                        // Start new range
                        $currentShiftId = $shiftId;
                        $rangeStart = $d;
                        $rangeEnd = $d;
                    } else {
                        $rangeEnd = $d;
                    }
                }

                // Save the last range if exists
                if ($currentShiftId !== null && $currentShiftId !== '' && $currentShiftId !== 'OFF') {
                    // Check if they want to make this ongoing (we'll assume no for now, unless explicitly requested, 
                    // but usually rosters are strictly bound to the month unless it's the last day and we want it to flow.
                    // Actually, setting end_date to last day of month is safest for a monthly roster.)
                    \App\Models\EmployeeWorkingShift::create([
                        'employee_id' => $empId,
                        'school_unit_id' => $unitId,
                        'working_shift_id' => $currentShiftId,
                        'bonus_schema_id' => $bonusSchemaId,
                        'start_date' => $firstDay->copy()->day($rangeStart)->format('Y-m-d'),
                        'end_date' => $firstDay->copy()->day($rangeEnd)->format('Y-m-d'),
                        'roster_name' => $rosterName,
                    ]);
                }
            }

            \Illuminate\Support\Facades\DB::commit();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Failed to update roster: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menyimpan Roster Shift.');
        }

        $this->syncSchedulesToUnit($unitId);

        return redirect()->route('employee-working-shifts.index')->with('success', 'Roster Shift Bulanan berhasil disimpan dan disinkronkan.');
    }

    public function exportRoster(Request $request)
    {
        $unitId = $request->query('unit_id');
        $month = (int)$request->query('month');
        $year = (int)$request->query('year');
        $rosterNameParam = $request->query('roster_name');
        $type = $request->query('type', 'pdf');
        $notes = $request->query('notes', '');
        
        if (!$unitId || !$month || !$year) {
            return redirect()->back()->with('error', 'Parameter tidak lengkap untuk ekspor.');
        }
        
        $unit = SchoolUnit::findOrFail($unitId);
        $shifts = WorkingShift::with('details')->orderBy('name')->get();
        
        $colors = ['bg-indigo-100 text-indigo-700', 'bg-emerald-100 text-emerald-700', 'bg-amber-100 text-amber-700', 'bg-sky-100 text-sky-700', 'bg-purple-100 text-purple-700'];
        $hexBg = ['#e0e7ff', '#d1fae5', '#fef3c7', '#e0f2fe', '#f3e8ff'];
        $hexText = ['#4338ca', '#047857', '#b45309', '#0369a1', '#7e22ce'];
        foreach ($shifts as $index => $shift) {
            $shift->color = $colors[$index % count($colors)];
            $shift->hex_bg = $hexBg[$index % count($hexBg)];
            $shift->hex_text = $hexText[$index % count($hexText)];
        }
        $daysInMonth = \Carbon\Carbon::create($year, $month)->daysInMonth;
        
        $allEmployees = $this->unitService->getSdEmployees();
        $rawEmployees = array_filter($allEmployees, function($emp) use ($unitId) {
            return $emp['unit_id'] == $unitId;
        });
        
        
        $firstDay = \Carbon\Carbon::create($year, $month, 1)->startOfDay();
        $lastDay = \Carbon\Carbon::create($year, $month, $daysInMonth)->endOfDay();
        
        $assignmentsQuery = EmployeeWorkingShift::with('workingShift')
            ->where('school_unit_id', $unitId)
            ->whereNotNull('roster_name')
            ->where(function($query) use ($firstDay, $lastDay) {
                $query->whereBetween('start_date', [$firstDay, $lastDay])
                      ->orWhere(function($q) use ($firstDay, $lastDay) {
                          $q->where('start_date', '<', $firstDay)
                            ->where(function($q2) use ($lastDay) {
                                $q2->whereNull('end_date')
                                   ->orWhere('end_date', '>=', $lastDay);
                            });
                      });
            });
            
        if ($rosterNameParam) {
            $assignmentsQuery->where('roster_name', $rosterNameParam);
        }
        
        $assignments = $assignmentsQuery->get();

        $assignedEmployeeIds = $assignments->pluck('employee_id')->unique()->toArray();
        $rosterName = $assignments->firstWhere('roster_name', '!=', null)->roster_name ?? ($rosterNameParam ?: 'Roster Shift Bulanan');
        
        // Match the same logic as detailRoster, only include employees with assignments
        $employees = array_filter($rawEmployees, function($emp) use ($assignedEmployeeIds) {
            return in_array($emp['id'], $assignedEmployeeIds);
        });
        $employees = array_values($employees);
            
        $rosterData = [];
        foreach ($assignments as $assignment) {
            $empId = $assignment->employee_id;
            if (!isset($rosterData[$empId])) {
                $rosterData[$empId] = [
                    'bonus_schema_id' => $assignment->bonus_schema_id,
                    'days' => []
                ];
            }
            
            $start = \Carbon\Carbon::parse($assignment->start_date)->max($firstDay);
            $end = $assignment->end_date ? \Carbon\Carbon::parse($assignment->end_date)->min($lastDay) : $lastDay;
            
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $rosterData[$empId]['days'][$d->day] = $assignment->working_shift_id;
            }
        }
        
        $bonusSchemas = \App\Models\BonusSchema::where('is_active', true)->orderBy('name')->get();
        
        $data = compact('unit', 'year', 'month', 'shifts', 'employees', 'rosterData', 'daysInMonth', 'notes', 'rosterName');
        
        if ($type === 'excel') {
            return $this->generateExcel($data);
        }
        
        ini_set('memory_limit', '512M');
        set_time_limit(300);
        // PDF Export
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('employee-working-shifts.export-roster-pdf', $data);
        $pdf->setPaper('a4', 'landscape');
        
        $safeRosterName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $rosterName);
        return $pdf->download("{$safeRosterName}_{$unit->name}_{$month}_{$year}.pdf");
    }

    private function generateExcel($data)
    {
        extract($data);
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Title
        $monthName = \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y');
        $lastColHeader = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(2 + $daysInMonth);
        $sheet->setCellValue('A1', 'JADWAL ' . strtoupper($rosterName) . ' ' . strtoupper($monthName));
        $sheet->mergeCells("A1:{$lastColHeader}1");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        // Header
        $sheet->setCellValue('A4', 'No');
        $sheet->mergeCells('A4:A5');
        $sheet->setCellValue('B4', 'Nama Pegawai');
        $sheet->mergeCells('B4:B5');
        
        $col = 'C';
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $timestamp = mktime(0,0,0,$month,$d,$year);
            $dayName = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'][date('w', $timestamp)];
            $sheet->setCellValue($col . '4', $dayName);
            $sheet->setCellValue($col . '5', $d);
            
            // Set fixed width for dates so they don't stretch
            $sheet->getColumnDimension($col)->setWidth(5.7);
            
            $col++;
        }
        
        // Freeze Panes
        $sheet->freezePane('C6');
        
        // Apply Header Style
        $headerStyle = [
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFE2EFDA'],
            ],
        ];
        $sheet->getStyle("A4:$lastColHeader" . "5")->applyFromArray($headerStyle);
        
        // Extract Used Shifts
        $usedShiftIds = [];
        foreach ($rosterData as $rd) {
            foreach ($rd['days'] as $sid) {
                if ($sid) $usedShiftIds[$sid] = true;
            }
        }
        
        // Data Rows
        $row = 6;
        foreach ($employees as $index => $emp) {
            $empId = $emp['id'];
            $rowData = $rosterData[$empId] ?? null;
            
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $emp['name']);
            
            $colIdx = 3;
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $shiftId = $rowData['days'][$d] ?? '';
                $shiftCode = '';
                $bgHex = 'FFFFFFFF';
                $textHex = 'FF000000';
                
                $timestamp = mktime(0,0,0,$month,$d,$year);
                $isWeekend = (date('D', $timestamp) == 'Sun');
                
                if ($shiftId) {
                    $shift = collect($shifts)->firstWhere('id', $shiftId);
                    if ($shift) {
                        $shiftCode = $shift->short_code ?: strtoupper(last(explode('_', $shift->code)));
                        $bgHex = str_replace('#', 'FF', $shift->hex_bg);
                        $textHex = str_replace('#', 'FF', $shift->hex_text);
                    }
                }
                
                if (!$shiftCode) {
                    $shiftCode = 'OFF';
                    $bgHex = 'FFE2E8F0'; // slate-200
                    $textHex = 'FF334155'; // slate-700
                }
                
                if ($isWeekend && $shiftCode === 'OFF') {
                    $bgHex = 'FFFEE2E2'; // rose-50
                    $textHex = 'FFE11D48'; // rose-600
                }
                
                $sheet->setCellValue($colLetter . $row, $shiftCode);
                $sheet->getStyle($colLetter . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($bgHex);
                $sheet->getStyle($colLetter . $row)->getFont()->getColor()->setARGB($textHex);
                $sheet->getStyle($colLetter . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIdx++;
            }
            $row++;
        }
        
        // Apply Data Borders
        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle("A6:$lastColHeader" . ($row - 1))->applyFromArray($dataStyle);
        
        // Notes and Legend
        $row = $row + 2;
        $sheet->setCellValue('B' . $row, 'Keterangan Shift:');
        $sheet->getStyle('B' . $row)->getFont()->setBold(true);
        
        $legendRow = $row + 1;
        $sheet->setCellValue('B' . $legendRow, 'Kode');
        $sheet->setCellValue('C' . $legendRow, 'Nama Shift');
        $sheet->mergeCells("C{$legendRow}:E{$legendRow}");
        
        $daysOfWeek = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
        $cIdx = 6; // F
        foreach ($daysOfWeek as $dayStr) {
            $colL1 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx);
            $colL2 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx + 1); // Merge 2 columns
            $sheet->setCellValue($colL1 . $legendRow, $dayStr);
            $sheet->mergeCells("{$colL1}{$legendRow}:{$colL2}{$legendRow}");
            $cIdx += 2;
        }
        
        $lastLegendCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx - 1);
        $sheet->getStyle("B$legendRow:$lastLegendCol$legendRow")->getFont()->setBold(true);
        $sheet->getStyle("B$legendRow:$lastLegendCol$legendRow")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("B$legendRow:$lastLegendCol$legendRow")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        // Position Catatan dynamically based on where Legend ends
        $noteStartIdx = $cIdx + 1; 
        $noteStartCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($noteStartIdx);
        $noteEndCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($noteStartIdx + 4);
        
        if (!empty($notes)) {
            $sheet->setCellValue($noteStartCol . $row, 'Catatan:');
            $sheet->getStyle($noteStartCol . $row)->getFont()->setBold(true);
            $sheet->setCellValue($noteStartCol . ($row + 1), $notes);
            $sheet->mergeCells("{$noteStartCol}" . ($row + 1) . ":{$noteEndCol}" . ($row + 4));
            $sheet->getStyle("{$noteStartCol}" . ($row + 1) . ":{$noteEndCol}" . ($row + 4))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8FAFC');
            $sheet->getStyle("{$noteStartCol}" . ($row + 1) . ":{$noteEndCol}" . ($row + 4))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $sheet->getStyle("{$noteStartCol}" . ($row + 1))->getAlignment()->setWrapText(true)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
        }
        
        $legendRow++;
        foreach ($shifts as $shift) {
            if (!isset($usedShiftIds[$shift->id])) continue;
            
            $bgHex = str_replace('#', 'FF', $shift->hex_bg);
            $textHex = str_replace('#', 'FF', $shift->hex_text);
            $code = $shift->short_code ?: strtoupper(last(explode('_', $shift->code)));
            
            $sheet->setCellValue('B' . $legendRow, $code);
            $sheet->getStyle('B' . $legendRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($bgHex);
            $sheet->getStyle('B' . $legendRow)->getFont()->getColor()->setARGB($textHex);
            $sheet->getStyle('B' . $legendRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            $sheet->setCellValue('C' . $legendRow, $shift->name);
            $sheet->mergeCells("C{$legendRow}:E{$legendRow}");
            $sheet->getStyle("C{$legendRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            $cIdx = 6;
            for ($i = 1; $i <= 7; $i++) {
                $detail = $shift->details->firstWhere('day_of_week', $i);
                $timeStr = $detail && !$detail->is_off ? substr($detail->start_time, 0, 5) . '-' . substr($detail->end_time, 0, 5) : 'Libur';
                
                $colL1 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx);
                $colL2 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx + 1);
                
                $sheet->setCellValue($colL1 . $legendRow, $timeStr);
                $sheet->mergeCells("{$colL1}{$legendRow}:{$colL2}{$legendRow}");
                $sheet->getStyle($colL1 . $legendRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                
                $cIdx += 2;
            }
            $sheet->getStyle("B$legendRow:$lastLegendCol$legendRow")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            
            $legendRow++;
        }
        
        // HRD Signature
        // Calculate Signature position dynamically based on last date column or notes
        $sigRow = $legendRow + 2;
        $sigStartColIdx = max((2 + $daysInMonth) - 4, $noteStartIdx);
        $sigStartCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($sigStartColIdx);
        $sigEndCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($sigStartColIdx + 4);
        
        $sheet->setCellValue($sigStartCol . $sigRow, 'Mengetahui,');
        $sheet->mergeCells("{$sigStartCol}{$sigRow}:{$sigEndCol}{$sigRow}");
        $sheet->getStyle($sigStartCol . $sigRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        $sheet->setCellValue($sigStartCol . ($sigRow + 1), 'HRD');
        $sheet->mergeCells("{$sigStartCol}" . ($sigRow + 1) . ":{$sigEndCol}" . ($sigRow + 1));
        $sheet->getStyle("{$sigStartCol}" . ($sigRow + 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        $sheet->setCellValue($sigStartCol . ($sigRow + 5), '_______________________');
        $sheet->mergeCells("{$sigStartCol}" . ($sigRow + 5) . ":{$sigEndCol}" . ($sigRow + 5));
        $sheet->getStyle("{$sigStartCol}" . ($sigRow + 5))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        // Auto size columns for Name and Kode only
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $safeRosterName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $rosterName);
        $fileName = "{$safeRosterName}_Excel_{$month}_{$year}.xlsx";
        if ($unit) {
            $fileName = "{$safeRosterName}_{$unit->name}_{$month}_{$year}.xlsx";
        }
        $tempFile = tempnam(sys_get_temp_dir(), 'roster_excel');
        $writer->save($tempFile);
        
        return response()->download($tempFile, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
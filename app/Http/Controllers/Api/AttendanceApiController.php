<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Services\SchoolUnitService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Holiday;
use App\Models\HolidayAdjustment;
use App\Models\EmployeeWorkingShift;
use App\Models\WorkingShift;
use App\Models\WorkingShiftDetail;
use App\Models\LeaveRequest;
use App\Models\BonusSchema;
use App\Models\BonusTier;

class AttendanceApiController extends Controller
{
    protected SchoolUnitService $service;

    public function __construct(SchoolUnitService $service)
    {
        $this->service = $service;
    }

        public function matrixReport(Request $request)
    {
        $month = $request->query('month', date('Y-m'));
        $unitId = $request->query('unit_id');

        $startDateParam = $request->query('start_date');
        $endDateParam = $request->query('end_date');

        if ($startDateParam && $endDateParam) {
            $startDate = Carbon::parse($startDateParam)->startOfDay();
            $endDate = Carbon::parse($endDateParam)->endOfDay();
        } else {
            $cutoffDate = (int) \App\Models\Setting::get('payroll_cutoff_date', 26);
            $monthCarbon = Carbon::createFromFormat('Y-m', $month);
            
            $endDate = $monthCarbon->copy()->setDay($cutoffDate)->endOfDay();
            $startDate = $monthCarbon->copy()->subMonth()->setDay($cutoffDate + 1)->startOfDay();
        }

        $rawEmployees = collect($this->service->getSdEmployees());
        if (!empty($unitId)) {
            $rawEmployees = $rawEmployees->filter(function($e) use ($unitId) {
                $eUnit = strtolower($e['unit_name'] ?? '');
                return str_contains($eUnit, strtolower($unitId)) || (string)($e['unit_id'] ?? '') === (string)$unitId;
            });
        }
        $employeesCollection = $rawEmployees->values();

        $holidays = \App\Models\Holiday::with('adjustments')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('original_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                  ->orWhereHas('adjustments', function ($q2) use ($startDate, $endDate) {
                      $q2->whereBetween('adjusted_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
                  });
            })->get();
        $holidayDates = $holidays->pluck('original_date')->toArray();

        $leavesData = \App\Models\LeaveRequest::where(function($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate]);
        })->get();

        $leaves = [];
        foreach ($leavesData as $l) {
            $key = $l->school_unit_id . '_' . $l->employee_id;
            $leaves[$key][] = $l;
        }

        foreach ($leaves as $key => $empLeaves) {
            usort($leaves[$key], function($a, $b) {
                $statusOrder = ['Approved' => 1, 'Pending' => 2, 'Rejected' => 3];
                $orderA = $statusOrder[$a->status] ?? 4;
                $orderB = $statusOrder[$b->status] ?? 4;
                return $orderA <=> $orderB;
            });
        }

        $shiftsData = \App\Models\EmployeeWorkingShift::with('workingShift.details')
            ->where(function($q) use ($startDate, $endDate) {
                $q->where('start_date', '<=', $endDate->format('Y-m-d'))
                  ->where(function($sq) use ($startDate) {
                      $sq->whereNull('end_date')
                         ->orWhere('end_date', '>=', $startDate->format('Y-m-d'));
                  });
            })->get();

        $assignedShifts = [];
        foreach ($shiftsData as $s) {
            $key = $s->school_unit_id . '_' . $s->employee_id;
            $assignedShifts[$key][] = $s;
        }
        foreach ($assignedShifts as $key => &$shifts) {
            usort($shifts, function($a, $b) {
                return ($b->roster_name === null ? 0 : 1) <=> ($a->roster_name === null ? 0 : 1);
            });
        }

        // Fetch logs up to next day noon to catch night shift clock outs
        $logsData = \App\Models\AttendanceLog::whereBetween('timestamp', [
            $startDate->format('Y-m-d 00:00:00'),
            $endDate->copy()->addDay()->format('Y-m-d 12:00:00')
        ])->get();

        $attendanceLogs = [];
        foreach ($logsData as $log) {
            $attendanceLogs[(string)$log->uid][] = $log->timestamp;
        }
        foreach ($attendanceLogs as &$ulogs) {
            sort($ulogs);
        }

        $reports = [];
        $lastDay = clone $endDate;
        if ($endDate > now()) {
            $lastDay = now()->endOfDay();
        }

        foreach ($employeesCollection as $emp) {
            $uid = $emp['zkteco_uid'] ?? null;
            $empId = $emp['id'] ?? null;
            $unit = $emp['unit_id'] ?? null;

            if (!$uid || !$empId) continue;
            $userLogs = $attendanceLogs[(string)$uid] ?? [];

            $dailyDetails = [];
            $currentDate = $startDate->copy();
            
            while ($currentDate <= $lastDay) {
                $dateStr = $currentDate->format('Y-m-d');
                $dayOfWeek = $currentDate->dayOfWeek; // 0 (Sun) to 6 (Sat)

                if (in_array($dateStr, $holidayDates)) {
                    $dailyDetails[$dateStr] = ['status' => 'Libur'];
                    $currentDate->addDay();
                    continue;
                }

                $isOnLeave = false;
                $leaveCode = 'I';
                $originalType = 'Izin';
                $hasRequiresAttendanceLeave = false;
                $activeLeave = null;
                $leaveKey = $unit . '_' . $empId;
                if (isset($leaves[$leaveKey])) {
                    foreach ($leaves[$leaveKey] as $leave) {
                        $leaveStart = substr($leave->start_date, 0, 10);
                        $leaveEnd = substr($leave->end_date, 0, 10);
                        if ($dateStr >= $leaveStart && $dateStr <= $leaveEnd) {
                            $isOnLeave = true;
                            $leaveCode = $leave->status_code ?? 'I';
                            $originalType = $leave->type_name;
                            $hasRequiresAttendanceLeave = (bool) $leave->requires_attendance;
                            $activeLeave = $leave;
                            break;
                        }
                    }
                }
                $hasShiftToday = false;
                $isOffShift = false;
                $shiftStartTime = null;
                $shiftEndTime = null;
                $shiftKey = $unit . '_' . $empId;
                
                $isShiftWorker = false;
                $checkInLog = null;
                $checkOutLog = null;

                if (isset($assignedShifts[$shiftKey])) {
                    foreach ($assignedShifts[$shiftKey] as $assignment) {
                        if ($assignment->workingShift->is_shift) {
                            $isShiftWorker = true;
                        }
                        
                        $assignStartDate = substr($assignment->start_date, 0, 10);
                        $assignEndDate = $assignment->end_date ? substr($assignment->end_date, 0, 10) : null;
                        if ($dateStr >= $assignStartDate && (!$assignEndDate || $dateStr <= $assignEndDate)) {
                            $detail = $assignment->workingShift->details->where('day_of_week', $dayOfWeek)->first();
                            if ($detail) {
                                if ($detail->is_off) {
                                    $isOffShift = true;
                                } else {
                                    $hasShiftToday = true;
                                    $shiftStartTime = $detail->start_time;
                                    $shiftEndTime = $detail->end_time;
                                }
                            }
                            break;
                        }
                    }
                }
                
                if ($isShiftWorker && !$hasShiftToday && !$isOffShift) {
                    $isOffShift = true;
                }

                if ($hasShiftToday) {
                    $isNightShift = $shiftStartTime > $shiftEndTime;
                    $expectedIn = \Carbon\Carbon::parse($dateStr . ' ' . $shiftStartTime);
                    $expectedOut = \Carbon\Carbon::parse($dateStr . ' ' . $shiftEndTime);
                    if ($isNightShift) {
                        $expectedOut->addDay();
                    }

                    $inStart = $expectedIn->copy()->subHours(6);
                    $inEnd = $expectedIn->copy()->addHours(6);
                    $outStart = $expectedOut->copy()->subHours(6);
                    $outEnd = $expectedOut->copy()->addHours(6);

                    $checkInLog = null;
                    $checkOutLog = null;

                    foreach ($userLogs as $tsStr) {
                        $ts = \Carbon\Carbon::parse($tsStr);
                        if ($ts->between($inStart, $inEnd)) {
                            if (!$checkInLog || $ts < \Carbon\Carbon::parse($checkInLog)) {
                                $checkInLog = $tsStr;
                            }
                        }
                        if ($ts->between($outStart, $outEnd)) {
                            if (!$checkOutLog || $ts > \Carbon\Carbon::parse($checkOutLog)) {
                                $checkOutLog = $tsStr;
                            }
                        }
                    }

                    if ($checkInLog || $checkOutLog) {
                        $isLate = false;
                        if ($checkInLog) {
                            if (\Carbon\Carbon::parse($checkInLog)->second(0) > $expectedIn->second(0)) {
                                $isLate = true;
                            }
                        }

                        if ($checkInLog && $checkOutLog && $checkInLog === $checkOutLog) {
                            $diffIn = \Carbon\Carbon::parse($checkInLog)->diffInMinutes($expectedIn);
                            $diffOut = \Carbon\Carbon::parse($checkOutLog)->diffInMinutes($expectedOut);
                            if ($diffIn < $diffOut) {
                                $checkOutLog = null;
                            } else {
                                $checkInLog = null;
                            }
                        }

                        if ($checkInLog && $checkOutLog && $checkInLog !== $checkOutLog) {
                            if (\Carbon\Carbon::parse($checkInLog)->diffInHours(\Carbon\Carbon::parse($checkOutLog)) < 2) {
                                $diffIn = \Carbon\Carbon::parse($checkInLog)->diffInMinutes($expectedIn);
                                $diffOut = \Carbon\Carbon::parse($checkOutLog)->diffInMinutes($expectedOut);
                                if ($diffIn < $diffOut) {
                                    $checkOutLog = null;
                                } else {
                                    $checkInLog = null;
                                }
                            }
                        }

                        $dailyDetails[$dateStr] = [
                            'status' => 'Hadir',
                            'check_in' => $checkInLog ? substr($checkInLog, 11, 5) : null,
                            'check_out' => $checkOutLog ? substr($checkOutLog, 11, 5) : null,
                            'is_late' => $isLate
                        ];
                    } else {
                        // Determine if it is actually Alfa or if the shift hasn't started yet
                        $now = \Carbon\Carbon::now('Asia/Jakarta');
                        $shiftStartDateTime = \Carbon\Carbon::parse($dateStr . ' ' . $shiftStartTime, 'Asia/Jakarta');
                        
                        if ($now->lessThan($shiftStartDateTime)) {
                            $dailyDetails[$dateStr] = ['status' => 'Pending'];
                        } else {
                            $dailyDetails[$dateStr] = ['status' => 'Alfa'];
                        }
                    }
                } elseif ($isOffShift) {
                    $dailyDetails[$dateStr] = ['status' => 'Off'];
                } else {
                    if ($dayOfWeek == 0) { // Sunday
                        $dailyDetails[$dateStr] = ['status' => 'Libur'];
                    }
                }

                if ($isOnLeave && $activeLeave) {
                    // Opsi A: Jangan timpa status jika hari ini adalah hari libur terjadwal (Sunday tanpa shift atau off-shift)
                    $isRestDay = $isOffShift || ($dayOfWeek == 0 && !$hasShiftToday);

                    if (!$isRestDay && ($activeLeave->status === 'Approved' || $activeLeave->status === 'Pending')) {
                        $isLateForLeave = false;
                        if ($activeLeave->status === 'Approved') {
                            if ($hasRequiresAttendanceLeave && ($activeLeave->gets_presence_bonus || $activeLeave->status_code === 'H')) {
                                $isLateForLeave = $isLate ?? false;
                            }
                        } else {
                            $isLateForLeave = $isLate ?? false;
                        }

                        $dailyDetails[$dateStr] = [
                            'status' => 'Cuti/Izin',
                            'is_pending' => ($activeLeave->status === 'Pending'),
                            'leave_code' => $leaveCode,
                            'leave_type' => $originalType,
                            'check_in' => $checkInLog ? substr($checkInLog, 11, 5) : null,
                            'check_out' => $checkOutLog ? substr($checkOutLog, 11, 5) : null,
                            'is_late' => $isLateForLeave,
                        ];
                    } elseif ($activeLeave->status === 'Rejected') {
                        $dailyDetails[$dateStr]['rejected_leave'] = [
                            'leave_code' => $leaveCode,
                            'leave_type' => $originalType,
                        ];
                    }
                }

                $currentDate->addDay();
            }

            $reports[] = [
                'employee' => $emp,
                'daily_details' => $dailyDetails,
            ];
        }

        return response()->json([
            'success' => true,
            'month' => $month,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'data' => $reports
        ]);
    }

    public function bonusReport(Request $request)
    {
        $month = $request->query('month', date('Y-m'));
        $unitId = $request->query('unit_id'); // Optional unit filter

        // Fetch cutoff date from settings, default to 26
        $cutoffDate = (int) \App\Models\Setting::get('payroll_cutoff_date', 26);
        
        $monthCarbon = Carbon::createFromFormat('Y-m', $month);
        
        $endDate = $monthCarbon->copy()->setDay($cutoffDate)->endOfDay();
        $startDate = $monthCarbon->copy()->subMonth()->setDay($cutoffDate + 1)->startOfDay();

        $startDateReq = $startDate->format('Y-m-d');
        $endDateReq = $endDate->format('Y-m-d');

        // 2. Fetch All Bonus Schemas for dynamic assignment
        $allSchemas = BonusSchema::with('tiers')->get()->keyBy('id');
        $defaultSchema = $allSchemas->where('is_active', true)->first();

        // 3. Fetch Employees (Filter by unit if needed)
        $rawEmployees = $this->service->getSdEmployees();
        $employeesCollection = collect($rawEmployees);

        if (!empty($unitId)) {
            $employeesCollection = $employeesCollection->filter(function ($emp) use ($unitId) {
                $empUnit = strtolower($emp['unit_name'] ?? '');
                return str_contains($empUnit, strtolower($unitId)) || (string)($emp['unit_id'] ?? '') === (string)$unitId;
            });
        }

        // 4. Pre-fetch related data
        $uids = $employeesCollection->pluck('zkteco_uid')->filter()->toArray();
        $employeeIds = $employeesCollection->pluck('id')->filter()->toArray();

        // Pre-fetch Attendance Logs for this month
        $attendanceLogs = AttendanceLog::whereIn('uid', $uids)
            ->whereBetween('timestamp', [$startDate->format('Y-m-d 00:00:00'), $endDate->format('Y-m-d 23:59:59')])
            ->get()
            ->groupBy(function($log) {
                return $log->uid . '_' . Carbon::parse($log->timestamp)->format('Y-m-d');
            });

        // Pre-fetch Assigned Shifts
        $assignedShifts = EmployeeWorkingShift::with(['workingShift.details'])
            ->whereIn('employee_id', $employeeIds)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate->format('Y-m-d'))
                      ->where(function($q) use ($startDate) {
                          $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', $startDate->format('Y-m-d'));
                      });
            })
            ->get()
            ->groupBy(function($shift) {
                return $shift->school_unit_id . '_' . $shift->employee_id;
            });

        // Pre-fetch Holidays
        $holidays = \App\Models\Holiday::with('adjustments')
            ->whereBetween('original_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();
        $holidayDates = $holidays->pluck('original_date')->toArray();

        // Pre-fetch Leave Requests (Approved)
        $leaves = \App\Models\LeaveRequest::whereIn('employee_id', $employeeIds)
            ->where('status', 'Approved')
            ->where(function($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate->format('Y-m-d'))
                      ->where('end_date', '>=', $startDate->format('Y-m-d'));
            })
            ->get()
            ->groupBy(function($leave) {
                return $leave->school_unit_id . '_' . $leave->employee_id;
            });

        // 5. Calculate Attendance Report
        $reports = [];

        foreach ($employeesCollection as $emp) {
            $uid = $emp['zkteco_uid'] ?? null;
            $empId = $emp['id'] ?? null;
            $unit = $emp['unit_id'] ?? null;

            if (!$uid || !$empId) continue;

            $totalPresent = 0;
            $totalLateMinutes = 0;
            $totalAbsent = 0;
            $totalBonusNominal = 0;
            $dailyDetails = [];

            // Loop through each day of the month
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                $dateStr = $currentDate->format('Y-m-d');
                $dayOfWeek = $currentDate->dayOfWeek; // 0 (Sun) - 6 (Sat)

                $shiftStartTime = null;
                $shiftEndTime = null;
                $shiftName = null;

                // Skip Holidays
                if (in_array($dateStr, $holidayDates)) {
                    $currentDate->addDay();
                    continue;
                }

                // Skip Leaves (except Dinas, those getting presence bonus, or those requiring attendance)
                $isOnLeave = false;
                $leaveType = null;
                $getsBonus = false;
                $hasRequiresAttendanceLeave = false;
                $activeLeave = null;
                $leaveKey = $unit . '_' . $empId;
                if (isset($leaves[$leaveKey])) {
                    foreach ($leaves[$leaveKey] as $leave) {
                        $leaveStart = substr($leave->start_date, 0, 10);
                        $leaveEnd = substr($leave->end_date, 0, 10);
                        if ($dateStr >= $leaveStart && $dateStr <= $leaveEnd) {
                            $isOnLeave = true;
                            $leaveType = $leave->type_name;
                            $getsBonus = $leave->gets_presence_bonus || ($leaveType === 'Dinas') || ($leave->status_code === 'H');
                            $hasRequiresAttendanceLeave = (bool) $leave->requires_attendance;
                            $activeLeave = $leave;
                            break;
                        }
                    }
                }
                if ($isOnLeave && !$getsBonus && !$hasRequiresAttendanceLeave) {
                    $currentDate->addDay();
                    continue;
                }

                // Check Shift Assignment
                $hasShiftToday = false;
                $shiftStartTime = null;
                $shiftEndTime = null;
                $shiftName = null;
                $currentAssignment = null;
                $shiftKey = $unit . '_' . $empId;

                if (isset($assignedShifts[$shiftKey])) {
                    foreach ($assignedShifts[$shiftKey] as $assignment) {
                        $assignStartDate = substr($assignment->start_date, 0, 10);
                        $assignEndDate = $assignment->end_date ? substr($assignment->end_date, 0, 10) : null;
                        if ($dateStr >= $assignStartDate && (!$assignEndDate || $dateStr <= $assignEndDate)) {
                            $detail = $assignment->workingShift->details->where('day_of_week', $dayOfWeek)->first();
                            if ($detail) {
                                $shiftName = $assignment->workingShift->name;
                                $currentAssignment = $assignment;
                                if (!$detail->is_off) {
                                    $hasShiftToday = true;
                                    $shiftStartTime = $detail->start_time;
                                    $shiftEndTime = $detail->end_time;
                                }
                            }
                            break;
                        }
                    }
                }

                if ($hasShiftToday) {
                    $dailyLateMinutes = 0;
                    $dailyStatus = 'Absent';
                    $dailyCheckIn = null;
                    $dailyBonus = 0;
                    $dailyTierLevel = null;

                    $logKey = $uid . '_' . $dateStr;
                    $isDinas = ($isOnLeave && $getsBonus && !$hasRequiresAttendanceLeave);

                    if (isset($attendanceLogs[$logKey]) || $isDinas) {
                        $dailyStatus = $isDinas ? ($leaveType ?: 'Dinas') : 'Present';
                        $totalPresent++;
                        
                        if (isset($attendanceLogs[$logKey]) && !$isDinas) {
                            $firstCheckIn = collect($attendanceLogs[$logKey])->sortBy('timestamp')->first();
                            $checkInCarbon = Carbon::parse($firstCheckIn->timestamp);
                            $expectedStart = Carbon::parse($dateStr . ' ' . $shiftStartTime);
                            $dailyCheckIn = $checkInCarbon->format('H:i:s');
    
                            $isForgiven = $hasRequiresAttendanceLeave && $activeLeave && (!$activeLeave->gets_presence_bonus && $activeLeave->status_code !== 'H');

                            if ($isForgiven) {
                                $dailyLateMinutes = 0;
                            } else {
                                if ($checkInCarbon->copy()->second(0) > $expectedStart->copy()->second(0)) {
                                    $diff = (int) $expectedStart->diffInMinutes($checkInCarbon);
                                    $dailyLateMinutes = $diff;
                                    $totalLateMinutes += $diff;
                                }
                            }
                        } else {
                            $dailyLateMinutes = 0;
                            $dailyCheckIn = 'DINAS';
                        }

                        $currentSchema = ($currentAssignment && $currentAssignment->bonus_schema_id) 
                                         ? ($allSchemas->get($currentAssignment->bonus_schema_id) ?? $defaultSchema)
                                         : $defaultSchema;

                        if ($currentSchema && $currentSchema->tiers->count() > 0) {
                            $qualifyingTiers = $currentSchema->tiers->filter(function($tier) use ($dailyLateMinutes) {
                                return $dailyLateMinutes <= $tier->max_late_minutes;
                            })->sortByDesc('nominal');

                            if ($qualifyingTiers->count() > 0) {
                                $bestTier = $qualifyingTiers->first();
                                $dailyBonus = $bestTier->nominal;
                                $dailyTierLevel = $bestTier->tier_level;
                            }
                        }

                        $totalBonusNominal += $dailyBonus;
                    } else {
                        $now = \Carbon\Carbon::now('Asia/Jakarta');
                        $shiftStartDateTime = \Carbon\Carbon::parse($dateStr . ' ' . $shiftStartTime, 'Asia/Jakarta');
                        
                        if ($now->lessThan($shiftStartDateTime)) {
                            $dailyStatus = 'Pending';
                        } else {
                            $totalAbsent++;
                        }
                    }

                    $dailyDetails[$dateStr] = [
                        'date' => $dateStr,
                        'shift_name' => $shiftName,
                        'shift_start' => $shiftStartTime,
                        'shift_end' => $shiftEndTime,
                        'check_in' => $dailyCheckIn,
                        'late_minutes' => $dailyLateMinutes,
                        'status' => $dailyStatus,
                        'bonus_nominal' => $dailyBonus,
                    ];
                } else {
                    $dailyDetails[$dateStr] = [
                        'date' => $dateStr,
                        'shift_name' => $shiftName,
                        'shift_start' => null,
                        'shift_end' => null,
                        'check_in' => null,
                        'late_minutes' => 0,
                        'status' => 'Off',
                        'bonus_nominal' => 0,
                    ];
                }

                $currentDate->addDay();
            }

            $activeShiftsData = [];
            $employeeShifts = $assignedShifts[$shiftKey] ?? collect();
            foreach ($employeeShifts as $assignment) {
                $ws = $assignment->workingShift;
                if ($ws && !isset($activeShiftsData[$ws->id])) {
                    $details = [];
                    foreach ($ws->details->sortBy('day_of_week') as $dt) {
                        $details[] = [
                            'day_of_week' => $dt->day_of_week,
                            'is_off' => (bool)$dt->is_off,
                            'start_time' => $dt->start_time ? substr($dt->start_time, 0, 5) : null,
                            'end_time' => $dt->end_time ? substr($dt->end_time, 0, 5) : null,
                        ];
                    }
                    $activeShiftsData[$ws->id] = [
                        'name' => $ws->name,
                        'description' => $ws->description,
                        'details' => $details,
                    ];
                }
            }
            $activeShiftsData = array_values($activeShiftsData);

            $reports[] = [
                'employee' => $emp,
                'total_present' => $totalPresent,
                'total_late_minutes' => $totalLateMinutes,
                'total_absent' => $totalAbsent,
                'bonus_nominal' => $totalBonusNominal,
                'active_shifts' => $activeShiftsData,
                'daily_details' => $dailyDetails,
            ];
        }

        return response()->json([
            'success' => true,
            'month' => $month,
            'start_date' => $startDateReq,
            'end_date' => $endDateReq,
            'cutoff_date' => $cutoffDate,
            'data' => $reports
        ]);
    }

    public function index(Request $request)
    {
        $date = $request->query('date', Carbon::today()->toDateString());
        $unit = $request->query('unit'); // unit string, e.g., 'smp'

        // Get employees from HRD's unified API
        $rawEmployees = $this->service->getSdEmployees();
        $employees = [];

        foreach ($rawEmployees as $emp) {
            $empUnit = strtolower($emp['unit_name'] ?? '');
            if ($unit && !str_contains($empUnit, strtolower($unit))) {
                continue;
            }
            // Key by UID for fast matching
            if (!empty($emp['zkteco_uid'])) {
                $uidStr = (string)$emp['zkteco_uid'];
                $employees[$uidStr] = $emp;
            }
        }

        if (empty($employees)) {
            return response()->json(['success' => true, 'date' => $date, 'data' => []]);
        }

        $uids = array_keys($employees);

        // Fetch logs for the date
        $logs = AttendanceLog::whereDate('timestamp', $date)
            ->whereIn('uid', $uids)
            ->get();

        $punchRecords = [];
        foreach ($logs as $log) {
            $uid = (string)$log->uid;
            $ts = Carbon::parse($log->timestamp);
            if (!isset($punchRecords[$uid])) {
                $punchRecords[$uid] = [
                    'min' => $ts->timestamp,
                    'max' => $ts->timestamp,
                    'min_formatted' => $ts->format('H:i:s'),
                    'max_formatted' => clone $ts, // We will format later if valid
                ];
            } else {
                if ($ts->timestamp < $punchRecords[$uid]['min']) {
                    $punchRecords[$uid]['min'] = $ts->timestamp;
                    $punchRecords[$uid]['min_formatted'] = $ts->format('H:i:s');
                }
                if ($ts->timestamp > $punchRecords[$uid]['max']) {
                    $punchRecords[$uid]['max'] = $ts->timestamp;
                    $punchRecords[$uid]['max_formatted'] = clone $ts;
                }
            }
        }

        $results = [];

        foreach ($employees as $uid => $emp) {
            $clockIn = null;
            $clockOut = null;
            
            if (isset($punchRecords[$uid])) {
                $clockIn = $punchRecords[$uid]['min_formatted'];
                
                $minTs = $punchRecords[$uid]['min'];
                $maxTs = $punchRecords[$uid]['max'];
                
                // Anti double-tap logic: if max and min differ by more than 60 minutes (3600 seconds), it's a valid clock out.
                if (($maxTs - $minTs) >= 3600) {
                    $clockOut = $punchRecords[$uid]['max_formatted']->format('H:i:s');
                }
            }

            // Calculate Status & Bonus using centralized HRD logic
            $calc = $this->calculateAttendanceLogic($emp['id'], $date, $clockIn, $clockOut);

            $results[] = [
                'employee' => $emp,
                'date' => $date,
                'clock_in' => $clockIn,
                'clock_out' => $clockOut,
                'status' => $calc['status'],
                'calculated_bonus' => $calc['calculated_bonus'],
                'notes' => null
            ];
        }

        // Sort by name
        usort($results, function ($a, $b) {
            return strcmp($a['employee']['name'] ?? '', $b['employee']['name'] ?? '');
        });

        $shifts = \App\Models\WorkingShift::select('id', 'name')->get();
        return response()->json([
            'success' => true,
            'date' => $date,
            'data' => $results,
            'shifts' => $shifts,
        ]);
    }

    private function calculateAttendanceLogic($employeeId, $date, $clockIn, $clockOut)
    {
        $carbonDate = Carbon::parse($date);
        $dayOfWeek = $carbonDate->dayOfWeek; // 0=Sunday, 1=Monday, ..., 6=Saturday

        // 1. Check Holiday Adjustment or global holiday
        $isHoliday = false;
        $adjustment = HolidayAdjustment::where('adjusted_date', $date)->first();

        if ($adjustment) {
            $isHoliday = true;
        } else {
            $holiday = Holiday::where('original_date', $date)->first();
            if ($holiday) {
                $wasRescheduled = HolidayAdjustment::where('holiday_id', $holiday->id)
                    ->where('original_date', $date)
                    ->exists();
                if (!$wasRescheduled) {
                    $isHoliday = true;
                }
            }
        }

        // 2. Find Assigned Shift or Default
        $activeShiftAssigned = EmployeeWorkingShift::where('employee_id', $employeeId)
            ->where('start_date', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', $date);
            })
            ->orderByRaw('CASE WHEN roster_name IS NOT NULL THEN 1 ELSE 0 END DESC')
            ->first();

        $shift = null;
        if ($activeShiftAssigned) {
            $shift = WorkingShift::find($activeShiftAssigned->working_shift_id);
        }

        if (!$shift) {
            $shift = WorkingShift::where('code', 'default')->first();
        }

        $shiftDetail = null;
        if ($shift) {
            $shiftDetail = WorkingShiftDetail::where('working_shift_id', $shift->id)
                ->where('day_of_week', $dayOfWeek)
                ->first();
        }

        $isOffDay = ($shiftDetail && $shiftDetail->is_off) || $isHoliday;

        // 3. Check Approved Leave Requests
        $leave = LeaveRequest::where('employee_id', $employeeId)
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->where('status', 'Approved')
            ->first();

        $hasRequiresAttendanceLeave = $leave && $leave->requires_attendance;

        // 4. Calculate Status and Bonus
        $status = null;
        $calculatedBonus = 0.00;

        if ($leave && !$hasRequiresAttendanceLeave) {
            $status = ($leave->status_code === 'S' || $leave->type_name === 'Sakit') ? 'Sick' : 'Leave';
            if ($leave->type_name === 'Dinas' || $leave->status_code === 'H' || $leave->gets_presence_bonus) {
                $currentSchemaId = ($activeShiftAssigned && $activeShiftAssigned->bonus_schema_id) 
                                    ? $activeShiftAssigned->bonus_schema_id 
                                    : (BonusSchema::where('is_active', true)->first()->id ?? null);
                
                if ($currentSchemaId) {
                    $maxTier = BonusTier::where('bonus_schema_id', $currentSchemaId)
                        ->orderBy('nominal', 'desc')
                        ->first();
                    if ($maxTier) {
                        $calculatedBonus = $maxTier->nominal;
                    }
                }
            }
        } elseif ($isOffDay) {
            if (!$clockIn) {
                $status = 'Off';
            } else {
                $status = 'Present';
                $currentSchemaId = ($activeShiftAssigned && $activeShiftAssigned->bonus_schema_id) 
                                    ? $activeShiftAssigned->bonus_schema_id 
                                    : (BonusSchema::where('is_active', true)->first()->id ?? null);
                
                if ($currentSchemaId) {
                    $maxTier = BonusTier::where('bonus_schema_id', $currentSchemaId)
                        ->orderBy('nominal', 'desc')
                        ->first();
                    if ($maxTier) {
                        $calculatedBonus = $maxTier->nominal;
                    }
                }
            }
        } else {
            // Work day
            if (!$clockIn) {
                $status = 'Absent';
            } else {
                $status = 'Present';
                $lateMinutes = 0;

                if ($shiftDetail && $shiftDetail->start_time) {
                    $shiftStart = Carbon::parse($date . ' ' . $shiftDetail->start_time);
                    $actualIn = Carbon::parse($date . ' ' . $clockIn);

                    if ($actualIn->gt($shiftStart)) {
                        $isForgiven = $hasRequiresAttendanceLeave && $leave && (!$leave->gets_presence_bonus && $leave->status_code !== 'H');
                        if ($isForgiven) {
                            $lateMinutes = 0;
                        } else {
                            $lateMinutes = $actualIn->diffInMinutes($shiftStart);
                            $status = 'Late';
                        }
                    }
                }

                $activeSchema = BonusSchema::where('is_active', true)->first();
                if ($activeSchema) {
                    $matchingTier = BonusTier::where('bonus_schema_id', $activeSchema->id)
                        ->where('max_late_minutes', '>=', $lateMinutes)
                        ->orderBy('nominal', 'desc')
                        ->first();

                    if ($matchingTier) {
                        $calculatedBonus = $matchingTier->nominal;
                    }
                }
            }
        }

        return [
            'status' => $status ?? 'Present',
            'calculated_bonus' => $calculatedBonus,
            'shift_id' => $shift ? $shift->id : null,
            'shift_name' => $shift ? $shift->name : 'Unknown',
        ];
    }
}

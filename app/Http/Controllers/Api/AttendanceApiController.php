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
use App\Models\SchoolUnit;
use App\Models\Setting;
use App\Models\HolidayReward;

class AttendanceApiController extends Controller
{
    protected SchoolUnitService $service;

    public function __construct(SchoolUnitService $service)
    {
        $this->service = $service;
    }

        public function matrixReport(Request $request)
    {
        $cutoffDate = (int) \App\Models\Setting::get('payroll_cutoff_date', 25);
        $month = $request->query('month');
        if (empty($month)) {
            $today = now();
            $month = $today->day > $cutoffDate ? $today->copy()->startOfMonth()->addMonth()->format('Y-m') : $today->format('Y-m');
        }
        $unitId = $request->query('unit_id');

        $startDateParam = $request->query('start_date');
        $endDateParam = $request->query('end_date');

        if ($startDateParam && $endDateParam) {
            $startDate = Carbon::parse($startDateParam)->startOfDay();
            $endDate = Carbon::parse($endDateParam)->endOfDay();
        } else {
            $monthCarbon = Carbon::parse($month . '-01');
            
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

        $holidays = \App\Models\Holiday::all();
        $holidayAdjustments = \App\Models\HolidayAdjustment::all();
        $schoolUnitsList = SchoolUnit::all();
        $unitHolidays = [];
        $unitHolidays[''] = [];
        foreach ($holidays as $h) {
            if ($h->is_global) {
                $unitHolidays[''][$h->original_date->format('Y-m-d')] = true;
            }
        }
        foreach ($holidayAdjustments as $adj) {
            if (is_null($adj->school_unit_id)) {
                $origStr = $adj->original_date->format('Y-m-d');
                $adjStr = $adj->adjusted_date->format('Y-m-d');
                if (isset($unitHolidays[''][$origStr])) {
                    unset($unitHolidays[''][$origStr]);
                }
                $unitHolidays[''][$adjStr] = true;
            }
        }
        foreach ($schoolUnitsList as $unitModel) {
            $uId = $unitModel->id;
            $unitHolidays[$uId] = [];
            foreach ($holidays as $h) {
                if ($h->is_global) {
                    $unitHolidays[$uId][$h->original_date->format('Y-m-d')] = true;
                }
            }
            foreach ($holidayAdjustments as $adj) {
                if (is_null($adj->school_unit_id) || $adj->school_unit_id == $uId) {
                    $origStr = $adj->original_date->format('Y-m-d');
                    $adjStr = $adj->adjusted_date->format('Y-m-d');
                    if (isset($unitHolidays[$uId][$origStr])) {
                        unset($unitHolidays[$uId][$origStr]);
                    }
                    $unitHolidays[$uId][$adjStr] = true;
                }
            }
        }

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
                $scoreA = ($a->roster_name !== null && $a->roster_name !== '') ? 3 : ($a->end_date !== null ? 2 : 1);
                $scoreB = ($b->roster_name !== null && $b->roster_name !== '') ? 3 : ($b->end_date !== null ? 2 : 1);
                
                if ($scoreA !== $scoreB) {
                    return $scoreB <=> $scoreA; // Descending
                }
                
                $dateA = $a->start_date instanceof \Carbon\Carbon ? $a->start_date->format('Y-m-d') : substr($a->start_date, 0, 10);
                $dateB = $b->start_date instanceof \Carbon\Carbon ? $b->start_date->format('Y-m-d') : substr($b->start_date, 0, 10);
                if ($dateA !== $dateB) {
                    return strcmp($dateB, $dateA); // Descending
                }
                
                return $b->id <=> $a->id; // Descending
            });
        }

        $holidayRewards = \App\Models\HolidayReward::where('end_date', '>=', $startDate->format('Y-m-d'))
            ->where('start_date', '<=', $endDate->format('Y-m-d'))
            ->get();

        // Pre-fetch Picket Schedules & Swaps
        $startDateReq = $startDate->format('Y-m-d');
        $endDateReq = $endDate->format('Y-m-d');
        $picketData = $this->getPicketDataIndexed($startDateReq, $endDateReq);
        $picketSchedulesMap = $picketData['schedulesMap'];
        $picketSwapOverrides = $picketData['swapOverrides'];

        $cutoffDate = (int) Setting::get('payroll_cutoff_date', 25);
        $defaultPicketEffectiveDate = Carbon::parse('2026-10-01')->subMonth()->setDay($cutoffDate + 1)->format('Y-m-d');
        $globalPicketEffectiveDate = Setting::get('picket_bonus_effective_date', $defaultPicketEffectiveDate);

        $schoolUnitsList = SchoolUnit::all();
        $unitPicketEffectiveDates = [];
        foreach ($schoolUnitsList as $su) {
            $unitPicketEffectiveDates[$su->id] = Setting::get('picket_effective_date_' . $su->id, $globalPicketEffectiveDate);
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

        // Pre-fetch Bonus Schemas
        $allBonusSchemas = BonusSchema::with('tiers')->get()->keyBy('id');
        $activeSchema = BonusSchema::with('tiers')
            ->where('is_active', true)
            ->where(function($q) {
                $q->where('calculation_mode', 'late_tolerance')
                  ->orWhereNull('calculation_mode');
            })
            ->first()
            ?: BonusSchema::with('tiers')->where('is_active', true)->first();

        $picketBonusSchemaId = Setting::get('picket_bonus_schema_id');
        $picketBonusSchema = $picketBonusSchemaId ? $allBonusSchemas->get($picketBonusSchemaId) : null;
        if (!$picketBonusSchema) {
            $picketBonusSchema = $allBonusSchemas->first(function($s) {
                return $s->is_active && ($s->calculation_mode === 'early_arrival' || str_contains(strtolower($s->name), 'piket'));
            });
        }

        $reports = [];
        $lastDay = clone $endDate;

        foreach ($employeesCollection as $emp) {
            $uid = $emp['zkteco_uid'] ?? null;
            $empId = $emp['id'] ?? null;
            $unit = $emp['unit_id'] ?? null;

            if (!$uid || !$empId) continue;

            $totalBonusNominal = 0;
            $isShiftWorker = false;
            $shiftKey = $unit . '_' . $empId;
            if (isset($assignedShifts[$shiftKey])) {
                foreach ($assignedShifts[$shiftKey] as $assignment) {
                    if ($assignment->workingShift->is_shift) {
                        $isShiftWorker = true;
                        break;
                    }
                }
            }
            $userLogs = $attendanceLogs[(string)$uid] ?? [];

            $dailyDetails = [];
            $currentDate = $startDate->copy();
            
            while ($currentDate <= $lastDay) {
                $dateStr = $currentDate->format('Y-m-d');
                $dayOfWeek = $currentDate->dayOfWeek; // 0 (Sun) to 6 (Sat)

                // Reset daily calculation variables
                $isLate = false;
                $isRedLate = false;
                $lateMinutes = 0;
                $earlyMinutes = 0;
                $shiftName = null;

                // Check Picket Duty for this employee on this date
                $isPicketToday = false;
                $picketAreaName = null;
                $picketStartTime = '06:30:00';

                // Picket integration takes effect starting from unit-specific effective date
                $unitEffectiveDate = $unitPicketEffectiveDates[$unit] ?? $globalPicketEffectiveDate;
                if ($dateStr >= $unitEffectiveDate) {
                    $swapKey = $unit . '_' . $empId . '_' . $dateStr;
                    if (isset($picketSwapOverrides[$swapKey])) {
                        if ($picketSwapOverrides[$swapKey]['action'] === 'assigned') {
                            $isPicketToday = true;
                            $picketAreaName = $picketSwapOverrides[$swapKey]['picket_area_name'] ?? 'Tukar Piket';
                            $picketStartTime = $picketSwapOverrides[$swapKey]['start_time'] ?? '06:30:00';
                        }
                    } else {
                        $routineKey = $unit . '_' . $empId . '_' . $dayOfWeek;
                        if (isset($picketSchedulesMap[$routineKey])) {
                            $schedList = isset($picketSchedulesMap[$routineKey]['day_of_week']) 
                                ? [$picketSchedulesMap[$routineKey]] 
                                : $picketSchedulesMap[$routineKey];
                            foreach ($schedList as $sched) {
                                $schedStart = !empty($sched['start_date']) ? substr($sched['start_date'], 0, 10) : null;
                                $schedEnd = !empty($sched['end_date']) ? substr($sched['end_date'], 0, 10) : null;
                                if ((!$schedStart || $dateStr >= $schedStart) && (!$schedEnd || $dateStr <= $schedEnd)) {
                                    $isPicketToday = true;
                                    $picketAreaName = $sched['picket_area_name'] ?? 'Area Piket';
                                    $picketStartTime = $sched['start_time'] ?? '06:30:00';
                                    break;
                                }
                            }
                        }
                    }
                }

                // Check Reward Holiday for this employee on this date
                $isRewardHoliday = false;
                $rewardHolidayName = null;
                foreach ($holidayRewards as $hr) {
                    if ($hr->appliesToDate($dateStr) && $hr->appliesToEmployee($unit, $empId)) {
                        $isRewardHoliday = true;
                        $rewardHolidayName = $hr->name;
                        break;
                    }
                }

                $empUnitKey = ($unit && isset($unitHolidays[$unit])) ? $unit : '';
                $isHoliday = ($unitHolidays[$empUnitKey][$dateStr] ?? false) && !$isShiftWorker;
                if ($isHoliday && !$isRewardHoliday && !$isPicketToday) {
                    $dailyDetails[$dateStr] = [
                        'status' => 'Libur',
                        'is_picket' => false,
                        'picket_area' => null,
                        'picket_start' => null,
                    ];
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

                $activeAssignmentOnDate = null;
                if (isset($assignedShifts[$shiftKey])) {
                    foreach ($assignedShifts[$shiftKey] as $assignment) {
                        if ($assignment->workingShift->is_shift) {
                            $isShiftWorker = true;
                        }
                        
                        $assignStartDate = substr($assignment->start_date, 0, 10);
                        $assignEndDate = $assignment->end_date ? substr($assignment->end_date, 0, 10) : null;
                        if ($dateStr >= $assignStartDate && (!$assignEndDate || $dateStr <= $assignEndDate)) {
                            $activeAssignmentOnDate = $assignment;
                            $shiftName = $assignment->workingShift->name ?? 'Reguler';
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

                // If teacher is on picket duty, baseline shift arrival time becomes picket start time (06:30:00)
                if ($isPicketToday) {
                    $hasShiftToday = true;
                    $isOffShift = false;
                    $shiftStartTime = $picketStartTime;
                    $shiftEndTime = $shiftEndTime ?: '15:00:00';
                    $shiftName = 'Piket: ' . ($picketAreaName ?: 'Area Piket');
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
                        $isRedLate = false;
                        $lateMinutes = 0;
                        $earlyMinutes = 0;

                        if ($checkInLog) {
                            $empPosition = strtolower($emp['position'] ?? $emp['subject_position'] ?? '');
                            $isShiftExempt = $isShiftWorker 
                                || str_contains($empPosition, 'keamanan') 
                                || str_contains($empPosition, 'satpam') 
                                || str_contains($empPosition, 'security') 
                                || str_contains($empPosition, 'mart') 
                                || str_contains($empPosition, 'toko') 
                                || str_contains($empPosition, 'salehmart');

                            $checkInCarbon = \Carbon\Carbon::parse($checkInLog);

                            if ($checkInCarbon->second(0) > $expectedIn->second(0)) {
                                $isLate = true;
                                $lateMinutes = (int) abs($checkInCarbon->diffInMinutes($expectedIn));
                            } else {
                                $earlyMinutes = (int) abs($expectedIn->diffInMinutes($checkInCarbon));
                            }

                            // Only non-roster/non-shift staff who arrive after 07:25 (or 25 mins after picket start) get is_red_late
                            $redLateThreshold = $isPicketToday ? $expectedIn->copy()->addMinutes(25) : \Carbon\Carbon::parse($dateStr . ' 07:25:00');
                            if (!$isShiftExempt && $checkInCarbon->second(0) > $redLateThreshold->second(0)) {
                                $isRedLate = true;
                            }
                        }

                        if ($checkInLog && $checkOutLog && $checkInLog === $checkOutLog) {
                            $diffIn = abs(\Carbon\Carbon::parse($checkInLog)->diffInMinutes($expectedIn));
                            $diffOut = abs(\Carbon\Carbon::parse($checkOutLog)->diffInMinutes($expectedOut));
                            if ($diffIn < $diffOut) {
                                $checkOutLog = null;
                            } else {
                                $checkInLog = null;
                            }
                        }

                        if ($checkInLog && $checkOutLog && $checkInLog !== $checkOutLog) {
                            if (\Carbon\Carbon::parse($checkInLog)->diffInHours(\Carbon\Carbon::parse($checkOutLog)) < 2) {
                                $diffIn = abs(\Carbon\Carbon::parse($checkInLog)->diffInMinutes($expectedIn));
                                $diffOut = abs(\Carbon\Carbon::parse($checkOutLog)->diffInMinutes($expectedOut));
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
                            'is_late' => $isLate,
                            'is_red_late' => $isRedLate,
                            'late_minutes' => $lateMinutes,
                            'early_minutes' => $earlyMinutes,
                            'shift_name' => $shiftName ?? ($isShiftWorker ? 'Shift' : 'Reguler'),
                            'shift_start' => $shiftStartTime,
                            'shift_end' => $shiftEndTime,
                            'is_reward' => $isRewardHoliday,
                            'reward_name' => $rewardHolidayName,
                        ];
                    } else {
                        if ($isRewardHoliday) {
                            $dailyDetails[$dateStr] = [
                                'status' => 'Reward Libur',
                                'reward_name' => $rewardHolidayName,
                                'is_reward' => true,
                                'shift_name' => $shiftName ?? ($isShiftWorker ? 'Shift' : 'Reguler'),
                                'shift_start' => $shiftStartTime,
                                'shift_end' => $shiftEndTime,
                            ];
                        } else {
                            // Determine if it is actually Alfa or if the shift hasn't started yet
                            $now = \Carbon\Carbon::now('Asia/Jakarta');
                            $shiftStartDateTime = \Carbon\Carbon::parse($dateStr . ' ' . $shiftStartTime, 'Asia/Jakarta');
                            
                            if ($now->lessThan($shiftStartDateTime)) {
                                $dailyDetails[$dateStr] = [
                                    'status' => 'Pending',
                                    'shift_name' => $shiftName ?? ($isShiftWorker ? 'Shift' : 'Reguler'),
                                    'shift_start' => $shiftStartTime,
                                    'shift_end' => $shiftEndTime,
                                ];
                            } else {
                                $dailyDetails[$dateStr] = [
                                    'status' => 'Alfa',
                                    'shift_name' => $shiftName ?? ($isShiftWorker ? 'Shift' : 'Reguler'),
                                    'shift_start' => $shiftStartTime,
                                    'shift_end' => $shiftEndTime,
                                ];
                            }
                        }
                    }
                } elseif ($isRewardHoliday) {
                    $dailyDetails[$dateStr] = [
                        'status' => 'Reward Libur',
                        'reward_name' => $rewardHolidayName,
                        'is_reward' => true,
                        'shift_name' => $shiftName ?? ($isShiftWorker ? 'Shift' : 'Reguler'),
                        'shift_start' => $shiftStartTime,
                        'shift_end' => $shiftEndTime,
                    ];
                } elseif ($isOffShift) {
                    $dailyDetails[$dateStr] = [
                        'status' => 'Off',
                        'shift_name' => $shiftName ?? 'Shift Kerja',
                    ];
                } else {
                    if ($dayOfWeek == 0) { // Sunday
                        $dailyDetails[$dateStr] = [
                            'status' => 'Libur',
                            'shift_name' => 'Akhir Pekan',
                        ];
                    }
                }

                if ($isOnLeave && $activeLeave) {
                    // Opsi A: Jangan timpa status jika hari ini adalah hari libur terjadwal (Sunday tanpa shift atau off-shift)
                    $isRestDay = $isOffShift || ($dayOfWeek == 0 && !$hasShiftToday);

                    if (!$isRestDay && ($activeLeave->status === 'Approved' || $activeLeave->status === 'Pending')) {
                        $isKedinasanLeave = str_contains(strtolower($originalType), 'dinas') || str_contains(strtolower($originalType), 'kedinasan');
                        $isLateForLeave = false;
                        if (!$isKedinasanLeave && !empty($checkInLog)) {
                            if ($activeLeave->status === 'Approved') {
                                if ($hasRequiresAttendanceLeave && ($activeLeave->gets_presence_bonus || $activeLeave->status_code === 'H')) {
                                    $isLateForLeave = $isLate;
                                }
                            } else {
                                $isLateForLeave = $isLate;
                            }
                        }

                        $dailyDetails[$dateStr] = [
                            'status' => 'Cuti/Izin',
                            'is_pending' => ($activeLeave->status === 'Pending'),
                            'leave_code' => $leaveCode,
                            'leave_type' => $originalType,
                            'check_in' => $checkInLog ? substr($checkInLog, 11, 5) : null,
                            'check_out' => $checkOutLog ? substr($checkOutLog, 11, 5) : null,
                            'is_late' => $isLateForLeave,
                            'is_red_late' => ($isLateForLeave && $isRedLate),
                            'late_minutes' => $isLateForLeave ? $lateMinutes : 0,
                            'early_minutes' => !empty($checkInLog) ? $earlyMinutes : 0,
                            'shift_name' => $shiftName ?? ($isShiftWorker ? 'Shift' : 'Reguler'),
                            'shift_start' => $shiftStartTime,
                            'shift_end' => $shiftEndTime,
                        ];
                    } elseif ($activeLeave->status === 'Rejected') {
                        $dailyDetails[$dateStr]['rejected_leave'] = [
                            'leave_code' => $leaveCode,
                            'leave_type' => $originalType,
                        ];
                    }
                }

                // Resolve Active Bonus Schema
                $currentSchema = null;
                if ($isPicketToday && $picketBonusSchema) {
                    $currentSchema = $picketBonusSchema;
                } else {
                    if (isset($activeAssignmentOnDate) && $activeAssignmentOnDate && $activeAssignmentOnDate->bonus_schema_id && $allBonusSchemas) {
                        $currentSchema = $allBonusSchemas->get($activeAssignmentOnDate->bonus_schema_id);
                    }
                    if (!$currentSchema && !empty($emp['unit_name']) && $allBonusSchemas) {
                        $currentSchema = $allBonusSchemas->first(function($s) use ($emp) {
                            return strtolower(trim($s->name)) === strtolower(trim($emp['unit_name']))
                                || str_contains(strtolower($emp['unit_name']), strtolower($s->name))
                                || str_contains(strtolower($s->name), strtolower($emp['unit_name']));
                        });
                    }
                    if (!$currentSchema) {
                        $currentSchema = $activeSchema;
                    }
                }

                $dailyBonus = 0;
                $dailyTierLevel = null;
                $dailySchemaName = $currentSchema ? $currentSchema->name : null;

                $isKedinasan = false;
                if ($isOnLeave && $activeLeave) {
                    $ltLower = strtolower($originalType ?? '');
                    if (str_contains($ltLower, 'dinas') || str_contains($ltLower, 'kedinasan')) {
                        $isKedinasan = true;
                    }
                }
                $getsBonus = $activeLeave ? ($activeLeave->gets_presence_bonus || ($originalType === 'Dinas') || ($activeLeave->status_code === 'H')) : false;
                $isDinas = ($isOnLeave && $getsBonus && (!$hasRequiresAttendanceLeave || $isKedinasan));

                $isEligibleForBonus = (!empty($checkInLog) || !empty($checkOutLog)) || $isDinas || $isRewardHoliday;

                if ($isEligibleForBonus && $currentSchema && $currentSchema->tiers && $currentSchema->tiers->count() > 0) {
                    if ($isRewardHoliday || $isDinas || $isKedinasan) {
                        $bestTier = $currentSchema->tiers->sortByDesc('nominal')->first();
                        if ($bestTier) {
                            $dailyBonus = $bestTier->nominal;
                            $dailyTierLevel = $bestTier->tier_level;
                        }
                    } elseif (($currentSchema->calculation_mode ?? 'early_arrival') === 'early_arrival') {
                        if ($lateMinutes > 0) {
                            $baseTier = $currentSchema->tiers->first(function($t) {
                                return ($t->min_early_minutes ?? 0) == 0;
                            });
                            if ($baseTier && $baseTier->nominal > 0) {
                                $dailyBonus = $baseTier->nominal;
                                $dailyTierLevel = $baseTier->tier_level;
                            } else {
                                $dailyBonus = 0;
                                $dailyTierLevel = null;
                            }
                        } else {
                            $qualifyingTiers = $currentSchema->tiers->filter(function($tier) use ($earlyMinutes) {
                                return ($tier->min_early_minutes ?? 0) <= $earlyMinutes;
                            })->sortByDesc('nominal');

                            if ($qualifyingTiers->count() > 0) {
                                $bestTier = $qualifyingTiers->first();
                                $dailyBonus = $bestTier->nominal;
                                $dailyTierLevel = $bestTier->tier_level;
                            }
                        }
                    } else {
                        // late_tolerance
                        $qualifyingTiers = $currentSchema->tiers->filter(function($tier) use ($lateMinutes) {
                            return $lateMinutes <= $tier->max_late_minutes;
                        })->sortByDesc('nominal');

                        if ($qualifyingTiers->count() > 0) {
                            $bestTier = $qualifyingTiers->first();
                            $dailyBonus = $bestTier->nominal;
                            $dailyTierLevel = $bestTier->tier_level;
                        }
                    }
                }

                if (isset($dailyDetails[$dateStr])) {
                    $dailyDetails[$dateStr]['is_picket'] = $isPicketToday;
                    $dailyDetails[$dateStr]['picket_area'] = $picketAreaName;
                    $dailyDetails[$dateStr]['picket_start'] = $isPicketToday ? $picketStartTime : null;
                    $dailyDetails[$dateStr]['bonus_nominal'] = (int) $dailyBonus;
                    $dailyDetails[$dateStr]['tier_level'] = $dailyTierLevel;
                    $dailyDetails[$dateStr]['schema_name'] = $dailySchemaName;
                    $totalBonusNominal += (int) $dailyBonus;
                }

                $currentDate->addDay();
            }

            $reports[] = [
                'employee' => $emp,
                'daily_details' => $dailyDetails,
                'total_bonus_nominal' => $totalBonusNominal,
            ];
        }

        return response()->json([
            'success' => true,
            'month' => $month,
            'cutoff_date' => $cutoffDate,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'data' => $reports
        ]);
    }

    public function bonusReport(Request $request)
    {
        $cutoffDate = (int) \App\Models\Setting::get('payroll_cutoff_date', 25);
        $month = $request->query('month');
        if (empty($month)) {
            $today = now();
            $month = $today->day > $cutoffDate ? $today->copy()->startOfMonth()->addMonth()->format('Y-m') : $today->format('Y-m');
        }
        $unitId = $request->query('unit_id') ?? $request->query('school_unit_id'); // Optional unit filter
        
        $monthCarbon = Carbon::parse($month . '-01');
        
        $endDate = $monthCarbon->copy()->setDay($cutoffDate)->endOfDay();
        $startDate = $monthCarbon->copy()->subMonth()->setDay($cutoffDate + 1)->startOfDay();

        $startDateReq = $startDate->format('Y-m-d');
        $endDateReq = $endDate->format('Y-m-d');

        // 2. Fetch All Bonus Schemas for dynamic assignment
        $allSchemas = BonusSchema::with('tiers')->get()->keyBy('id');
        $defaultSchema = $allSchemas->where('is_active', true)->where('calculation_mode', 'late_tolerance')->first()
            ?: $allSchemas->where('is_active', true)->first();

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

        // Pre-fetch Attendance Logs spanning -1 day 12:00 to +1 day 12:00 for cross-day / night shifts
        $logsData = AttendanceLog::whereIn('uid', $uids)
            ->whereBetween('timestamp', [
                $startDate->copy()->subDay()->format('Y-m-d 12:00:00'),
                $endDate->copy()->addDay()->format('Y-m-d 12:00:00')
            ])
            ->get(['uid', 'timestamp']);

        $attendanceLogs = [];
        foreach ($logsData as $log) {
            $tsStr = $log->timestamp instanceof Carbon ? $log->timestamp->format('Y-m-d H:i:s') : (string) $log->timestamp;
            $attendanceLogs[(string)$log->uid][] = $tsStr;
        }
        foreach ($attendanceLogs as &$uLogs) {
            sort($uLogs);
        }
        unset($uLogs);

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
            })
            ->map(function($group) {
                return $group->sort(function($a, $b) {
                    $scoreA = ($a->roster_name !== null && $a->roster_name !== '') ? 3 : ($a->end_date !== null ? 2 : 1);
                    $scoreB = ($b->roster_name !== null && $b->roster_name !== '') ? 3 : ($b->end_date !== null ? 2 : 1);
                    if ($scoreA !== $scoreB) {
                        return $scoreB <=> $scoreA; // Descending
                    }
                    $dateA = $a->start_date instanceof \Carbon\Carbon ? $a->start_date->format('Y-m-d') : substr($a->start_date, 0, 10);
                    $dateB = $b->start_date instanceof \Carbon\Carbon ? $b->start_date->format('Y-m-d') : substr($b->start_date, 0, 10);
                    if ($dateA !== $dateB) {
                        return strcmp($dateB, $dateA); // Descending
                    }
                    return $b->id <=> $a->id; // Descending
                });
            });

        // Pre-fetch Holidays
        $holidays = \App\Models\Holiday::all();
        $holidayAdjustments = \App\Models\HolidayAdjustment::all();
        $schoolUnitsList = SchoolUnit::all();
        $unitHolidays = [];
        $unitHolidays[''] = [];
        foreach ($holidays as $h) {
            if ($h->is_global) {
                $unitHolidays[''][$h->original_date->format('Y-m-d')] = true;
            }
        }
        foreach ($holidayAdjustments as $adj) {
            if (is_null($adj->school_unit_id)) {
                $origStr = $adj->original_date->format('Y-m-d');
                $adjStr = $adj->adjusted_date->format('Y-m-d');
                if (isset($unitHolidays[''][$origStr])) {
                    unset($unitHolidays[''][$origStr]);
                }
                $unitHolidays[''][$adjStr] = true;
            }
        }
        foreach ($schoolUnitsList as $unitModel) {
            $uId = $unitModel->id;
            $unitHolidays[$uId] = [];
            foreach ($holidays as $h) {
                if ($h->is_global) {
                    $unitHolidays[$uId][$h->original_date->format('Y-m-d')] = true;
                }
            }
            foreach ($holidayAdjustments as $adj) {
                if (is_null($adj->school_unit_id) || $adj->school_unit_id == $uId) {
                    $origStr = $adj->original_date->format('Y-m-d');
                    $adjStr = $adj->adjusted_date->format('Y-m-d');
                    if (isset($unitHolidays[$uId][$origStr])) {
                        unset($unitHolidays[$uId][$origStr]);
                    }
                    $unitHolidays[$uId][$adjStr] = true;
                }
            }
        }

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

        // Pre-fetch Holiday Rewards
        $holidayRewards = \App\Models\HolidayReward::where('end_date', '>=', $startDate->format('Y-m-d'))
            ->where('start_date', '<=', $endDate->format('Y-m-d'))
            ->get();

        // Pre-fetch Picket Schedules & Swaps
        $picketData = $this->getPicketDataIndexed($startDateReq, $endDateReq);
        $picketSchedulesMap = $picketData['schedulesMap'];
        $picketSwapOverrides = $picketData['swapOverrides'];
        $defaultPicketEffectiveDate = Carbon::parse('2026-10-01')->subMonth()->setDay($cutoffDate + 1)->format('Y-m-d');
        $globalPicketEffectiveDate = \App\Models\Setting::get('picket_bonus_effective_date', $defaultPicketEffectiveDate);

        $unitPicketEffectiveDates = [];
        foreach ($schoolUnitsList as $su) {
            $unitPicketEffectiveDates[$su->id] = \App\Models\Setting::get('picket_effective_date_' . $su->id, $globalPicketEffectiveDate);
        }

        $picketBonusSchemaId = \App\Models\Setting::get('picket_bonus_schema_id');
        $picketBonusSchema = $picketBonusSchemaId ? $allSchemas->get($picketBonusSchemaId) : null;
        if (!$picketBonusSchema) {
            $picketBonusSchema = $allSchemas->first(function($s) {
                return $s->is_active && ($s->calculation_mode === 'early_arrival' || str_contains(strtolower($s->name), 'piket'));
            });
        }

        // 5. Calculate Attendance Report
        $reports = [];

        foreach ($employeesCollection as $emp) {
            $uid = $emp['zkteco_uid'] ?? null;
            $empId = $emp['id'] ?? null;
            $unit = $emp['unit_id'] ?? null;

            if (!$uid || !$empId) continue;

            $userLogs = $attendanceLogs[(string)$uid] ?? [];

            $isShiftWorker = false;
            $shiftKey = $unit . '_' . $empId;
            if (isset($assignedShifts[$shiftKey])) {
                foreach ($assignedShifts[$shiftKey] as $assignment) {
                    if ($assignment->workingShift->is_shift) {
                        $isShiftWorker = true;
                        break;
                    }
                }
            }

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

                // Check Reward Holiday for this employee on this date
                $isRewardHoliday = false;
                $rewardHolidayName = null;
                foreach ($holidayRewards as $hr) {
                    if ($hr->appliesToDate($dateStr) && $hr->appliesToEmployee($unit, $empId)) {
                        $isRewardHoliday = true;
                        $rewardHolidayName = $hr->name;
                        break;
                    }
                }

                $empUnitKey = ($unit && isset($unitHolidays[$unit])) ? $unit : '';
                $isHoliday = ($unitHolidays[$empUnitKey][$dateStr] ?? false) && !$isShiftWorker;
                if ($isHoliday && !$isRewardHoliday) {
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
                if ($isOnLeave && !$getsBonus && !$hasRequiresAttendanceLeave && !$isRewardHoliday) {
                    $currentDate->addDay();
                    continue;
                }

                // Check Shift Assignment
                $hasShiftToday = false;
                $shiftStartTime = null;
                $shiftEndTime = null;
                $shiftName = null;
                $shiftCode = null;
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
                                $shiftCode = $assignment->workingShift->short_code ?: strtoupper(last(explode('_', $assignment->workingShift->code)));
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

                // Check Picket Duty for this employee on this date
                $isPicketToday = false;
                $picketAreaName = null;
                $picketStartTime = '06:30:00';

                // Picket integration takes effect starting from unit-specific effective date
                $unitEffectiveDate = $unitPicketEffectiveDates[$unit] ?? $globalPicketEffectiveDate;
                if ($dateStr >= $unitEffectiveDate) {
                    $swapKey = $unit . '_' . $empId . '_' . $dateStr;
                    if (isset($picketSwapOverrides[$swapKey])) {
                        if ($picketSwapOverrides[$swapKey]['action'] === 'assigned') {
                            $isPicketToday = true;
                            $picketAreaName = $picketSwapOverrides[$swapKey]['picket_area_name'] ?? 'Tukar Piket';
                            $picketStartTime = $picketSwapOverrides[$swapKey]['start_time'] ?? '06:30:00';
                        }
                    } else {
                        $routineKey = $unit . '_' . $empId . '_' . $dayOfWeek;
                        if (isset($picketSchedulesMap[$routineKey])) {
                            $schedList = isset($picketSchedulesMap[$routineKey]['day_of_week']) 
                                ? [$picketSchedulesMap[$routineKey]] 
                                : $picketSchedulesMap[$routineKey];
                            foreach ($schedList as $sched) {
                                $schedStart = !empty($sched['start_date']) ? substr($sched['start_date'], 0, 10) : null;
                                $schedEnd = !empty($sched['end_date']) ? substr($sched['end_date'], 0, 10) : null;
                                if ((!$schedStart || $dateStr >= $schedStart) && (!$schedEnd || $dateStr <= $schedEnd)) {
                                    $isPicketToday = true;
                                    $picketAreaName = $sched['picket_area_name'] ?? 'Area Piket';
                                    $picketStartTime = $sched['start_time'] ?? '06:30:00';
                                    break;
                                }
                            }
                        }
                    }
                }

                if ($hasShiftToday || $isRewardHoliday) {
                    $shiftStartTime = $shiftStartTime ?: '07:00:00';
                    $shiftEndTime = $shiftEndTime ?: '15:00:00';
                    $shiftName = $shiftName ?: 'Shift Reguler';
                    if ($isPicketToday) {
                        $shiftStartTime = $picketStartTime;
                    }

                    // Shift windows (handling cross-day night shifts)
                    $isNightShift = $shiftStartTime > $shiftEndTime;
                    $expectedIn = Carbon::parse($dateStr . ' ' . $shiftStartTime);
                    $expectedOut = Carbon::parse($dateStr . ' ' . $shiftEndTime);
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
                        $ts = Carbon::parse($tsStr);
                        if ($ts->between($inStart, $inEnd)) {
                            if (!$checkInLog || $ts < Carbon::parse($checkInLog)) {
                                $checkInLog = $tsStr;
                            }
                        }
                        if ($ts->between($outStart, $outEnd)) {
                            if (!$checkOutLog || $ts > Carbon::parse($checkOutLog)) {
                                $checkOutLog = $tsStr;
                            }
                        }
                    }

                    // Disambiguation
                    if ($checkInLog && $checkOutLog && $checkInLog === $checkOutLog) {
                        $diffIn = abs(Carbon::parse($checkInLog)->diffInMinutes($expectedIn));
                        $diffOut = abs(Carbon::parse($checkOutLog)->diffInMinutes($expectedOut));
                        if ($diffIn < $diffOut) {
                            $checkOutLog = null;
                        } else {
                            $checkInLog = null;
                        }
                    }

                    if ($checkInLog && $checkOutLog && $checkInLog !== $checkOutLog) {
                        if (Carbon::parse($checkInLog)->diffInHours(Carbon::parse($checkOutLog)) < 2) {
                            $diffIn = abs(Carbon::parse($checkInLog)->diffInMinutes($expectedIn));
                            $diffOut = abs(Carbon::parse($checkOutLog)->diffInMinutes($expectedOut));
                            if ($diffIn < $diffOut) {
                                $checkOutLog = null;
                            } else {
                                $checkInLog = null;
                            }
                        }
                    }

                    $currentSchema = null;
                    if ($isPicketToday && $picketBonusSchema) {
                        $currentSchema = $picketBonusSchema;
                    } else {
                        if ($currentAssignment && $currentAssignment->bonus_schema_id) {
                            $currentSchema = $allSchemas->get($currentAssignment->bonus_schema_id);
                        }
                        if (!$currentSchema && !empty($emp['unit_name'])) {
                            $currentSchema = $allSchemas->first(function($s) use ($emp) {
                                return strtolower(trim($s->name)) === strtolower(trim($emp['unit_name']))
                                    || str_contains(strtolower($emp['unit_name']), strtolower($s->name))
                                    || str_contains(strtolower($s->name), strtolower($emp['unit_name']));
                            });
                        }
                        if (!$currentSchema) {
                            $currentSchema = $defaultSchema;
                        }
                    }

                    $dailyLateMinutes = 0;
                    $earlyMinutes = 0;
                    $dailyStatus = 'Absent';
                    $dailyCheckIn = null;
                    $dailyCheckOut = null;
                    $dailyBonus = 0;
                    $dailyTierLevel = null;

                    $isKedinasan = false;
                    if ($isOnLeave && $activeLeave) {
                        $ltLower = strtolower($leaveType ?? $activeLeave->type ?? $activeLeave->type_name ?? '');
                        if (str_contains($ltLower, 'dinas') || str_contains($ltLower, 'kedinasan')) {
                            $isKedinasan = true;
                        }
                    }
                    $isDinas = ($isOnLeave && $getsBonus && (!$hasRequiresAttendanceLeave || $isKedinasan));

                    if ($checkInLog || $checkOutLog || $isDinas || $isRewardHoliday) {
                        if ($isRewardHoliday) {
                            $dailyStatus = 'Reward Libur';
                            $dailyCheckIn = 'REWARD';
                            $dailyCheckOut = 'REWARD';
                        } elseif ($isKedinasan || $isDinas) {
                            $dailyStatus = ($leaveType ?: 'Dinas');
                            $dailyCheckIn = $checkInLog ? substr($checkInLog, 11, 5) : 'DINAS';
                            $dailyCheckOut = $checkOutLog ? substr($checkOutLog, 11, 5) : ($checkInLog ? '-' : 'DINAS');
                        } else {
                            $dailyStatus = 'Present';
                            $dailyCheckIn = $checkInLog ? substr($checkInLog, 11, 5) : 'Tidak Hadir';
                            $dailyCheckOut = $checkOutLog ? substr($checkOutLog, 11, 5) : '-';
                        }
                        $totalPresent++;

                        // Calculate Early / Late
                        if ($checkInLog && !$isDinas && !$isRewardHoliday && !$isKedinasan) {
                            $checkInCarbon = Carbon::parse($checkInLog);
                            $isForgiven = $hasRequiresAttendanceLeave && $activeLeave && (!$activeLeave->gets_presence_bonus && $activeLeave->status_code !== 'H');

                            if ($isForgiven) {
                                $dailyLateMinutes = 0;
                                $earlyMinutes = 0;
                            } else {
                                $checkInMin = $checkInCarbon->copy()->second(0);
                                $expectedMin = $expectedIn->copy()->second(0);

                                if ($checkInMin->gt($expectedMin)) {
                                    $diff = (int) $expectedMin->diffInMinutes($checkInMin);
                                    $dailyLateMinutes = $diff;
                                    $totalLateMinutes += $diff;
                                    $earlyMinutes = 0;
                                } else {
                                    $earlyMinutes = (int) $checkInMin->diffInMinutes($expectedMin);
                                    $dailyLateMinutes = 0;
                                }
                            }
                        }

                        // Calculate Daily Bonus
                        $schemaMode = $currentSchema ? ($currentSchema->calculation_mode ?? 'early_arrival') : 'early_arrival';

                        if ($currentSchema && $currentSchema->tiers->count() > 0) {
                            if ($isRewardHoliday || $isDinas || $isKedinasan) {
                                $bestTier = $currentSchema->tiers->sortByDesc('nominal')->first();
                                if ($bestTier) {
                                    $dailyBonus = $bestTier->nominal;
                                    $dailyTierLevel = $bestTier->tier_level;
                                }
                            } elseif ($checkInLog) {
                                if ($schemaMode === 'early_arrival') {
                                    if ($dailyLateMinutes > 0) {
                                        $baseTier = $currentSchema->tiers->first(function($t) {
                                            return ($t->min_early_minutes ?? 0) == 0;
                                        });
                                        if ($baseTier && $baseTier->nominal > 0) {
                                            $dailyBonus = $baseTier->nominal;
                                            $dailyTierLevel = $baseTier->tier_level;
                                        }
                                    } else {
                                        $qualifyingTiers = $currentSchema->tiers->filter(function($tier) use ($earlyMinutes) {
                                            return ($tier->min_early_minutes ?? 0) <= $earlyMinutes;
                                        })->sortByDesc('nominal');

                                        if ($qualifyingTiers->count() > 0) {
                                            $bestTier = $qualifyingTiers->first();
                                            $dailyBonus = $bestTier->nominal;
                                            $dailyTierLevel = $bestTier->tier_level;
                                        }
                                    }
                                } else {
                                    $qualifyingTiers = $currentSchema->tiers->filter(function($tier) use ($dailyLateMinutes) {
                                        return $dailyLateMinutes <= $tier->max_late_minutes;
                                    })->sortByDesc('nominal');

                                    if ($qualifyingTiers->count() > 0) {
                                        $bestTier = $qualifyingTiers->first();
                                        $dailyBonus = $bestTier->nominal;
                                        $dailyTierLevel = $bestTier->tier_level;
                                    }
                                }
                            }
                        }

                        $totalBonusNominal += $dailyBonus;
                    } else {
                        $now = Carbon::now('Asia/Jakarta');
                        $shiftStartDateTime = Carbon::parse($dateStr . ' ' . $shiftStartTime, 'Asia/Jakarta');
                        
                        if ($now->lessThan($shiftStartDateTime)) {
                            $dailyStatus = 'Pending';
                        } else {
                            $dailyStatus = 'Absent';
                            $totalAbsent++;
                        }
                    }

                    $dailyDetails[$dateStr] = [
                        'date' => $dateStr,
                        'shift_name' => $shiftName,
                        'shift_code' => $shiftCode,
                        'short_code' => $shiftCode,
                        'shift_start' => substr($shiftStartTime, 0, 5),
                        'shift_end' => substr($shiftEndTime, 0, 5),
                        'check_in' => $dailyCheckIn,
                        'check_out' => $dailyCheckOut,
                        'late_minutes' => $dailyLateMinutes,
                        'early_minutes' => $earlyMinutes,
                        'status' => $dailyStatus,
                        'bonus_nominal' => $dailyBonus,
                        'tier_level' => $dailyTierLevel,
                        'calculation_mode' => $currentSchema ? ($currentSchema->calculation_mode ?? 'early_arrival') : 'early_arrival',
                        'schema_name' => $currentSchema ? $currentSchema->name : null,
                        'is_picket' => $isPicketToday,
                        'picket_area' => $picketAreaName,
                        'picket_start' => $isPicketToday ? substr($picketStartTime, 0, 5) : null,
                        'is_reward_holiday' => $isRewardHoliday,
                        'reward_name' => $rewardHolidayName,
                    ];
                } else {
                    $dailyDetails[$dateStr] = [
                        'date' => $dateStr,
                        'shift_name' => $shiftName,
                        'shift_code' => $shiftCode,
                        'short_code' => $shiftCode,
                        'shift_start' => null,
                        'shift_end' => null,
                        'check_in' => null,
                        'check_out' => null,
                        'late_minutes' => 0,
                        'early_minutes' => 0,
                        'status' => 'Off',
                        'bonus_nominal' => 0,
                        'tier_level' => null,
                        'calculation_mode' => null,
                        'schema_name' => null,
                        'is_picket' => false,
                        'picket_area' => null,
                        'picket_start' => null,
                        'is_reward_holiday' => false,
                        'reward_name' => null,
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
                        'id' => $ws->id,
                        'name' => $ws->name,
                        'code' => $ws->code,
                        'short_code' => $ws->short_code ?: strtoupper(last(explode('_', $ws->code))),
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

    public function percentageReport(Request $request)
    {
        $cutoffDate = (int) Setting::get('payroll_cutoff_date', 25);
        $month = $request->query('month');
        $startDateParam = $request->query('start_date');
        $endDateParam = $request->query('end_date');
        $unitParam = $request->query('unit_id') ?? $request->query('school_unit_id');

        if ($startDateParam && $endDateParam) {
            $startDate = Carbon::parse($startDateParam)->startOfDay();
            $endDate = Carbon::parse($endDateParam)->endOfDay();
        } else {
            if (empty($month)) {
                $today = now();
                $month = $today->day > $cutoffDate ? $today->copy()->startOfMonth()->addMonth()->format('Y-m') : $today->format('Y-m');
            }
            $monthCarbon = Carbon::parse($month . '-01');
            $endDate = $monthCarbon->copy()->setDay($cutoffDate)->endOfDay();
            $startDate = $monthCarbon->copy()->subMonth()->setDay($cutoffDate + 1)->startOfDay();
        }

        $startDateReq = $startDate->format('Y-m-d');
        $endDateReq = $endDate->format('Y-m-d');

        // Fetch employees (strictly exclude Tukang from attendance percentage calculations)
        $rawEmployees = $this->service->getAllEmployees();
        $employeesCollection = collect($rawEmployees)
            ->filter(function ($emp) {
                $pos = strtolower(trim((string)($emp['position'] ?? $emp['subject_position'] ?? '')));
                $type = strtolower(trim((string)($emp['employee_type']['name'] ?? $emp['employee_type']['code'] ?? (is_string($emp['employee_type'] ?? null) ? $emp['employee_type'] : ''))));
                $code = strtolower(trim((string)($emp['employee_type']['code'] ?? '')));
                $name = strtolower(trim((string)($emp['name'] ?? '')));

                if (str_contains($pos, 'tukang') || str_contains($type, 'tukang') || $type === 'tkg' || $code === 'tkg' || str_contains($name, 'tukang')) {
                    return false;
                }
                return true;
            });

        if (!empty($unitParam)) {
            $employeesCollection = $employeesCollection->filter(function ($emp) use ($unitParam) {
                $empUnitName = strtolower($emp['unit_name'] ?? '');
                $empUnitId = (string)($emp['unit_id'] ?? '');
                return str_contains($empUnitName, strtolower($unitParam)) || $empUnitId === (string)$unitParam;
            });
        }

        $allEmployeesCollection = $employeesCollection
            ->sort(function ($a, $b) {
                return strcmp($a['name'] ?? '', $b['name'] ?? '');
            })
            ->values();

        $uids = $allEmployeesCollection->pluck('zkteco_uid')->filter()->toArray();
        $employeeIds = $allEmployeesCollection->pluck('id')->filter()->toArray();

        // Pre-fetch Attendance Logs
        $attendanceLogs = AttendanceLog::whereIn('uid', $uids)
            ->whereBetween('timestamp', [$startDate->format('Y-m-d 00:00:00'), $endDate->format('Y-m-d 23:59:59')])
            ->orderBy('timestamp', 'asc')
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
            ->map(function ($assignment) {
                $assignment->formatted_start = $assignment->start_date instanceof Carbon ? $assignment->start_date->format('Y-m-d') : substr($assignment->start_date, 0, 10);
                $assignment->formatted_end = $assignment->end_date ? ($assignment->end_date instanceof Carbon ? $assignment->end_date->format('Y-m-d') : substr($assignment->end_date, 0, 10)) : null;
                return $assignment;
            })
            ->groupBy(function($shift) {
                return $shift->school_unit_id . '_' . $shift->employee_id;
            })
            ->map(function($group) {
                return $group->sort(function($a, $b) {
                    $scoreA = ($a->roster_name !== null && $a->roster_name !== '') ? 3 : ($a->end_date !== null ? 2 : 1);
                    $scoreB = ($b->roster_name !== null && $b->roster_name !== '') ? 3 : ($b->end_date !== null ? 2 : 1);
                    if ($scoreA !== $scoreB) {
                        return $scoreB <=> $scoreA;
                    }
                    $dateA = $a->formatted_start;
                    $dateB = $b->formatted_start;
                    if ($dateA !== $dateB) {
                        return strcmp($dateB, $dateA);
                    }
                    return $b->id <=> $a->id;
                });
            });

        // Pre-fetch Holidays, Adjustments, Holiday Rewards
        $holidays = Holiday::all();
        $holidayAdjustments = HolidayAdjustment::all();
        $holidayRewards = HolidayReward::where('end_date', '>=', $startDate->format('Y-m-d'))
            ->where('start_date', '<=', $endDate->format('Y-m-d'))
            ->get();

        $schoolUnitsList = SchoolUnit::all();
        $unitHolidays = [];
        $unitHolidays[''] = [];
        foreach ($holidays as $h) {
            if ($h->is_global) {
                $unitHolidays[''][$h->original_date->format('Y-m-d')] = true;
            }
        }
        foreach ($holidayAdjustments as $adj) {
            if (is_null($adj->school_unit_id)) {
                $origStr = $adj->original_date->format('Y-m-d');
                $adjStr = $adj->adjusted_date->format('Y-m-d');
                if (isset($unitHolidays[''][$origStr])) {
                    unset($unitHolidays[''][$origStr]);
                }
                $unitHolidays[''][$adjStr] = true;
            }
        }

        foreach ($schoolUnitsList as $unitModel) {
            $uId = $unitModel->id;
            $unitHolidays[$uId] = [];
            foreach ($holidays as $h) {
                if ($h->is_global) {
                    $unitHolidays[$uId][$h->original_date->format('Y-m-d')] = true;
                }
            }
            foreach ($holidayAdjustments as $adj) {
                if (is_null($adj->school_unit_id) || $adj->school_unit_id == $uId) {
                    $origStr = $adj->original_date->format('Y-m-d');
                    $adjStr = $adj->adjusted_date->format('Y-m-d');
                    if (isset($unitHolidays[$uId][$origStr])) {
                        unset($unitHolidays[$uId][$origStr]);
                    }
                    $unitHolidays[$uId][$adjStr] = true;
                }
            }
        }

        // Pre-fetch Leave Requests
        $leaves = LeaveRequest::whereIn('employee_id', $employeeIds)
            ->where('status', 'Approved')
            ->where(function($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate->format('Y-m-d'))
                      ->where('end_date', '>=', $startDate->format('Y-m-d'));
            })
            ->get()
            ->map(function ($leave) {
                $leave->formatted_start = $leave->start_date instanceof Carbon ? $leave->start_date->format('Y-m-d') : substr($leave->start_date, 0, 10);
                $leave->formatted_end = $leave->end_date instanceof Carbon ? $leave->end_date->format('Y-m-d') : substr($leave->end_date, 0, 10);
                return $leave;
            })
            ->groupBy(function($leave) {
                return $leave->school_unit_id . '_' . $leave->employee_id;
            });

        // Pre-fetch Picket Schedules & Swaps
        $picketData = $this->getPicketDataIndexed($startDateReq, $endDateReq);
        $defaultPicketEffectiveDate = Carbon::parse('2026-10-01')->subMonth()->setDay($cutoffDate + 1)->format('Y-m-d');
        $globalPicketEffectiveDate = Setting::get('picket_bonus_effective_date', $defaultPicketEffectiveDate);

        $unitPicketEffectiveDates = [];
        foreach ($schoolUnitsList as $su) {
            $unitPicketEffectiveDates[$su->id] = Setting::get('picket_effective_date_' . $su->id, $globalPicketEffectiveDate);
        }

        $reports = [];
        $totalAllWorkDays = 0;
        $totalAllPresent = 0;
        $totalAllAbsent = 0;
        $totalAllSakit = 0;
        $totalAllIzin = 0;
        $totalAllCuti = 0;
        $totalLateEvents = 0;
        $totalLateMinutesAll = 0;
        $totalWaspadaStaff = 0;
        $totalKritisStaff = 0;

        foreach ($allEmployeesCollection as $emp) {
            $uid = $emp['zkteco_uid'] ?? null;
            $empId = $emp['id'] ?? null;
            $unit = $emp['unit_id'] ?? null;

            if (!$uid || !$empId) continue;

            $isShiftWorker = false;
            $shiftKey = $unit . '_' . $empId;
            if (isset($assignedShifts[$shiftKey])) {
                foreach ($assignedShifts[$shiftKey] as $assignment) {
                    if ($assignment->workingShift && $assignment->workingShift->is_shift) {
                        $isShiftWorker = true;
                        break;
                    }
                }
            }

            $totalWorkDays = 0;
            $totalPresent = 0;
            $actualScanCount = 0;
            $dinasCount = 0;
            $rewardCount = 0;
            $totalSakit = 0;
            $totalIzin = 0;
            $totalCuti = 0;
            $totalAbsent = 0;
            $totalLateMinutes = 0;
            $lateCount = 0;
            $waspadaLateCount = 0;
            $kritisLateCount = 0;

            $sakitDates = [];
            $izinDates = [];
            $cutiDates = [];
            $absentDates = [];
            $scanDates = [];
            $dinasDates = [];
            $rewardDates = [];
            $lateDates = [];
            $waspadaDates = [];
            $kritisDates = [];
            $dayDetails = [];

            $lastDay = $endDate->greaterThan(Carbon::now()) ? Carbon::now()->endOfDay() : $endDate;
            $currentDate = $startDate->copy();

            while ($currentDate <= $lastDay) {
                $dateStr = $currentDate->format('Y-m-d');
                $formattedDate = $currentDate->translatedFormat('l, d M Y');
                $dayOfWeek = $currentDate->dayOfWeek; // 0 (Sun) - 6 (Sat)
                $dayOfWeek1to6 = $dayOfWeek === 0 ? 7 : $dayOfWeek;

                // Check Reward Holiday
                $isRewardHoliday = false;
                $rewardHolidayName = null;
                foreach ($holidayRewards as $hr) {
                    if ($hr->appliesToDate($dateStr) && $hr->appliesToEmployee($unit, $empId)) {
                        $isRewardHoliday = true;
                        $rewardHolidayName = $hr->name;
                        break;
                    }
                }

                // Check Official Holiday
                $empUnitKey = ($unit && isset($unitHolidays[$unit])) ? $unit : '';
                $isHoliday = ($unitHolidays[$empUnitKey][$dateStr] ?? false) && !$isShiftWorker;
                if ($isHoliday && !$isRewardHoliday) {
                    $dayDetails[] = [
                        'date' => $formattedDate,
                        'date_str' => $dateStr,
                        'status' => 'Libur',
                        'label' => 'Hari Libur Resmi',
                        'detail' => 'Libur Nasional / Yayasan',
                        'color' => 'slate'
                    ];
                    $currentDate->addDay();
                    continue;
                }

                // Check Shift
                $activeShift = null;
                $shiftStartTime = null;
                $shiftEndTime = null;
                if (isset($assignedShifts[$shiftKey])) {
                    foreach ($assignedShifts[$shiftKey] as $assignment) {
                        $start = $assignment->formatted_start;
                        $end = $assignment->formatted_end;
                        if ($dateStr >= $start && (!$end || $dateStr <= $end)) {
                            $activeShift = $assignment->workingShift;
                            break;
                        }
                    }
                }

                $shiftName = $activeShift ? $activeShift->name : 'Non-Shift';
                $isDayOff = false;

                if ($activeShift && $activeShift->details) {
                    $dayDetail = $activeShift->details->firstWhere('day_of_week', $dayOfWeek);
                    if ($dayDetail) {
                        if ($dayDetail->is_off) {
                            $isDayOff = true;
                        } else {
                            $shiftStartTime = $dayDetail->start_time;
                            $shiftEndTime = $dayDetail->end_time;
                        }
                    } else {
                        $isDayOff = true;
                    }
                } elseif ($currentDate->isSunday()) {
                    $isDayOff = true;
                }

                if ($isShiftWorker && !$activeShift) {
                    $isDayOff = true;
                }

                // Check Picket Duty (Overrides start time to 06:30)
                $isPicketToday = false;
                $picketAreaName = null;
                $unitEffectiveDate = $unitPicketEffectiveDates[$unit] ?? $globalPicketEffectiveDate;
                if (!empty($unit) && !empty($empId) && $dateStr >= $unitEffectiveDate) {
                    $overrideKey = $unit . '_' . $empId . '_' . $dateStr;
                    if (isset($picketData['swapOverrides'][$overrideKey])) {
                        $ov = $picketData['swapOverrides'][$overrideKey];
                        if ($ov['action'] === 'assigned') {
                            $isPicketToday = true;
                            $picketAreaName = $ov['picket_area_name'] ?? 'Tukar Piket';
                        }
                    } else {
                        $schedKey = $unit . '_' . $empId . '_' . $dayOfWeek1to6;
                        if (isset($picketData['schedulesMap'][$schedKey])) {
                            $schedList = isset($picketData['schedulesMap'][$schedKey]['day_of_week']) 
                                ? [$picketData['schedulesMap'][$schedKey]] 
                                : $picketData['schedulesMap'][$schedKey];
                            foreach ($schedList as $sched) {
                                $schedStart = !empty($sched['start_date']) ? substr($sched['start_date'], 0, 10) : null;
                                $schedEnd = !empty($sched['end_date']) ? substr($sched['end_date'], 0, 10) : null;
                                if ((!$schedStart || $dateStr >= $schedStart) && (!$schedEnd || $dateStr <= $schedEnd)) {
                                    $isPicketToday = true;
                                    $picketAreaName = $sched['picket_area_name'] ?? 'Area Piket';
                                    break;
                                }
                            }
                        }
                    }
                }

                if ($isPicketToday) {
                    $shiftStartTime = '06:30:00';
                    $shiftName = 'Piket: ' . ($picketAreaName ?: 'Area Piket');
                    $isDayOff = false; // Picket day is an active working day
                }

                $shiftScheduleText = $shiftStartTime ? substr($shiftStartTime, 0, 5) . ($shiftEndTime ? ' - ' . substr($shiftEndTime, 0, 5) : '') : null;
                $logKey = $uid . '_' . $dateStr;
                $hasScan = isset($attendanceLogs[$logKey]);

                // Reward Holiday
                if ($isRewardHoliday) {
                    $totalWorkDays++;
                    $totalPresent++;
                    $rewardCount++;
                    $rewardDates[] = $currentDate->translatedFormat('d M');

                    $inTime = 'REWARD';
                    $outTime = '-';
                    $scanDetail = $rewardHolidayName ? "Reward Libur: {$rewardHolidayName}" : 'Reward Hari Libur';

                    if ($hasScan) {
                        $actualScanCount++;
                        $scanDates[] = $currentDate->translatedFormat('d M');
                        $dayLogs = $attendanceLogs[$logKey]->sortBy('timestamp')->values();
                        $firstLog = $dayLogs->first();
                        $lastLog = $dayLogs->last();
                        $inTime = Carbon::parse($firstLog->timestamp)->format('H:i');
                        if ($dayLogs->count() > 1 && $firstLog->timestamp !== $lastLog->timestamp) {
                            $outTime = Carbon::parse($lastLog->timestamp)->format('H:i');
                        }
                        $scanDetail .= " (Scan: {$inTime}" . ($outTime !== '-' ? " - {$outTime}" : "") . ")";
                    }

                    $dayDetails[] = [
                        'date' => $formattedDate,
                        'date_str' => $dateStr,
                        'status' => 'Reward Libur',
                        'label' => 'Reward Libur',
                        'detail' => $scanDetail,
                        'shift_name' => $shiftName,
                        'shift_schedule' => $shiftScheduleText,
                        'in_time' => $inTime,
                        'out_time' => $outTime,
                        'late_minutes' => 0,
                        'late_category' => null,
                        'is_picket' => $isPicketToday,
                        'picket_area' => $picketAreaName,
                        'notes' => $rewardHolidayName ? "Reward Libur: {$rewardHolidayName}" : 'Hak Reward Hari Libur (Hadir Penuh)',
                        'color' => 'indigo'
                    ];

                    $currentDate->addDay();
                    continue;
                }

                // Check Leave Requests
                $activeLeave = null;
                if (isset($leaves[$shiftKey])) {
                    foreach ($leaves[$shiftKey] as $leave) {
                        if ($dateStr >= $leave->formatted_start && $dateStr <= $leave->formatted_end) {
                            $activeLeave = $leave;
                            break;
                        }
                    }
                }

                if (!$isDayOff) {
                    $totalWorkDays++;

                    if ($hasScan) {
                        $totalPresent++;
                        $actualScanCount++;
                        $scanDates[] = $currentDate->translatedFormat('d M');

                        $dayLogs = $attendanceLogs[$logKey]->sortBy('timestamp')->values();
                        $firstLog = $dayLogs->first();
                        $lastLog = $dayLogs->last();
                        $firstLogCarbon = Carbon::parse($firstLog->timestamp);
                        $firstScan = $firstLogCarbon->format('H:i');

                        // Calculate late minutes
                        $dailyLateMinutes = 0;
                        $dailyLateCategory = null;
                        if ($shiftStartTime) {
                            $expectedStart = Carbon::parse($dateStr . ' ' . $shiftStartTime);
                            if ($firstLogCarbon->copy()->second(0) > $expectedStart->copy()->second(0)) {
                                $isKedinasanLeave = $activeLeave && $activeLeave->status === 'Approved' && (
                                    str_contains(strtolower($activeLeave->type_name ?? $activeLeave->type ?? ''), 'dinas') || 
                                    str_contains(strtolower($activeLeave->type_name ?? $activeLeave->type ?? ''), 'kedinasan')
                                );

                                if (!$isKedinasanLeave) {
                                    $dailyLateMinutes = (int) abs($expectedStart->diffInMinutes($firstLogCarbon));
                                    $totalLateMinutes += $dailyLateMinutes;
                                    $lateCount++;
                                    $lateDates[] = $currentDate->translatedFormat('d M');

                                    $empPosition = strtolower($emp['position'] ?? $emp['subject_position'] ?? '');
                                    $isShiftExempt = $isShiftWorker 
                                        || str_contains($empPosition, 'keamanan') 
                                        || str_contains($empPosition, 'satpam') 
                                        || str_contains($empPosition, 'security') 
                                        || str_contains($empPosition, 'mart') 
                                        || str_contains($empPosition, 'toko') 
                                        || str_contains($empPosition, 'salehmart');

                                    $redLateThreshold = $isPicketToday
                                        ? Carbon::parse($dateStr . ' 06:55:00')->second(0)
                                        : Carbon::parse($dateStr . ' 07:25:00')->second(0);

                                    $isRedLate = false;
                                    if (!$isShiftExempt && $firstLogCarbon > $redLateThreshold) {
                                        if (!($activeLeave && $activeLeave->status === 'Approved')) {
                                            $isRedLate = true;
                                        }
                                    }

                                    if ($isRedLate) {
                                        $dailyLateCategory = 'kritis';
                                        $kritisLateCount++;
                                        $kritisDates[] = $currentDate->translatedFormat('d M');
                                    } else {
                                        $dailyLateCategory = 'waspada';
                                        $waspadaLateCount++;
                                        $waspadaDates[] = $currentDate->translatedFormat('d M');
                                    }
                                }
                            }
                        }

                        $inTime = $firstScan;
                        $outTime = '-';

                        if ($dayLogs->count() > 1 && $firstLog->timestamp !== $lastLog->timestamp) {
                            $outTime = Carbon::parse($lastLog->timestamp)->format('H:i');
                            $scanDetail = "Masuk: {$inTime} • Pulang: {$outTime}";
                        } else {
                            if ($firstLogCarbon->hour < 12) {
                                $inTime = $firstScan;
                                $outTime = '-';
                                $scanDetail = "Masuk: {$inTime} • Pulang: -";
                            } else {
                                $inTime = '-';
                                $outTime = $firstScan;
                                $scanDetail = "Masuk: - • Pulang: {$outTime}";
                            }
                        }

                        if ($dailyLateMinutes > 0) {
                            $lateLabel = $dailyLateCategory === 'kritis' ? 'Mengkhawatirkan' : 'Terlambat';
                            $scanDetail .= " • {$lateLabel}: {$dailyLateMinutes} mnt";
                        }

                        $dayDetails[] = [
                            'date' => $formattedDate,
                            'date_str' => $dateStr,
                            'status' => 'Hadir',
                            'label' => $dailyLateCategory === 'kritis' ? 'Mengkhawatirkan' : ($dailyLateCategory === 'waspada' ? 'Terlambat' : 'Hadir'),
                            'detail' => $scanDetail,
                            'shift_name' => $shiftName,
                            'shift_schedule' => $shiftScheduleText,
                            'in_time' => $inTime,
                            'out_time' => $outTime,
                            'late_minutes' => $dailyLateMinutes,
                            'late_category' => $dailyLateCategory,
                            'is_picket' => $isPicketToday,
                            'picket_area' => $picketAreaName,
                            'notes' => null,
                            'color' => $dailyLateCategory === 'kritis' ? 'rose' : ($dailyLateCategory === 'waspada' ? 'amber' : 'emerald')
                        ];
                    } elseif ($activeLeave) {
                        $statusCode = $activeLeave->status_code;
                        $leaveReason = $activeLeave->notes ?? $activeLeave->reason ?? 'Izin disetujui';
                        $getsBonus = $activeLeave->gets_presence_bonus || ($statusCode === 'H') || ($activeLeave->type_name === 'Dinas');

                        if (!$statusCode) {
                            if ($activeLeave->type_name === 'Sakit') $statusCode = 'S';
                            elseif ($activeLeave->type_name === 'Cuti') $statusCode = 'C';
                            elseif ($activeLeave->type_name === 'Dinas') $statusCode = 'H';
                            else $statusCode = 'I';
                        }

                        if ($statusCode === 'S') {
                            $totalSakit++;
                            $sakitDates[] = $currentDate->translatedFormat('d M');
                            $dayDetails[] = [
                                'date' => $formattedDate,
                                'date_str' => $dateStr,
                                'status' => 'Sakit',
                                'label' => 'Sakit',
                                'detail' => $leaveReason,
                                'shift_name' => $shiftName,
                                'shift_schedule' => $shiftScheduleText,
                                'in_time' => null,
                                'out_time' => null,
                                'late_minutes' => 0,
                                'late_category' => null,
                                'is_picket' => $isPicketToday,
                                'picket_area' => $picketAreaName,
                                'notes' => $leaveReason ?: 'Surat Dokter / Izin Sakit',
                                'color' => 'amber'
                            ];
                        } elseif ($statusCode === 'C') {
                            $totalCuti++;
                            $cutiDates[] = $currentDate->translatedFormat('d M');
                            $dayDetails[] = [
                                'date' => $formattedDate,
                                'date_str' => $dateStr,
                                'status' => 'Cuti',
                                'label' => 'Cuti',
                                'detail' => $leaveReason,
                                'shift_name' => $shiftName,
                                'shift_schedule' => $shiftScheduleText,
                                'in_time' => null,
                                'out_time' => null,
                                'late_minutes' => 0,
                                'late_category' => null,
                                'is_picket' => $isPicketToday,
                                'picket_area' => $picketAreaName,
                                'notes' => $leaveReason ?: 'Cuti Tahunan / Resmi',
                                'color' => 'blue'
                            ];
                        } elseif ($statusCode === 'H' || $getsBonus) {
                            $totalPresent++;
                            $dinasCount++;
                            $dinasDates[] = $currentDate->translatedFormat('d M');
                            $dayDetails[] = [
                                'date' => $formattedDate,
                                'date_str' => $dateStr,
                                'status' => 'Dinas',
                                'label' => 'Dinas',
                                'detail' => $leaveReason,
                                'shift_name' => $shiftName,
                                'shift_schedule' => $shiftScheduleText,
                                'in_time' => null,
                                'out_time' => null,
                                'late_minutes' => 0,
                                'late_category' => null,
                                'is_picket' => $isPicketToday,
                                'picket_area' => $picketAreaName,
                                'notes' => $leaveReason ?: 'Dinas / Tugas Luar',
                                'color' => 'indigo'
                            ];
                        } else {
                            $totalIzin++;
                            $izinDates[] = $currentDate->translatedFormat('d M');
                            $dayDetails[] = [
                                'date' => $formattedDate,
                                'date_str' => $dateStr,
                                'status' => 'Izin',
                                'label' => 'Izin',
                                'detail' => $leaveReason,
                                'shift_name' => $shiftName,
                                'shift_schedule' => $shiftScheduleText,
                                'in_time' => null,
                                'out_time' => null,
                                'late_minutes' => 0,
                                'late_category' => null,
                                'is_picket' => $isPicketToday,
                                'picket_area' => $picketAreaName,
                                'notes' => $leaveReason ?: 'Izin Terverifikasi',
                                'color' => 'amber'
                            ];
                        }
                    } else {
                        $totalAbsent++;
                        $absentDates[] = $currentDate->translatedFormat('d M');
                        $dayDetails[] = [
                            'date' => $formattedDate,
                            'date_str' => $dateStr,
                            'status' => 'Alpa',
                            'label' => 'Alpa',
                            'detail' => "Tidak ada scan pada jadwal $shiftName" . ($shiftScheduleText ? " ($shiftScheduleText)" : ''),
                            'shift_name' => $shiftName,
                            'shift_schedule' => $shiftScheduleText,
                            'in_time' => null,
                            'out_time' => null,
                            'late_minutes' => 0,
                            'late_category' => null,
                            'is_picket' => $isPicketToday,
                            'picket_area' => $picketAreaName,
                            'notes' => 'Tidak ada rekaman absensi',
                            'color' => 'rose'
                        ];
                    }
                } else {
                    $dayDetails[] = [
                        'date' => $formattedDate,
                        'date_str' => $dateStr,
                        'status' => 'Libur',
                        'label' => 'Libur',
                        'detail' => $isHoliday ? 'Hari Libur Resmi' : "Jadwal Libur ($shiftName)",
                        'shift_name' => $shiftName,
                        'shift_schedule' => $shiftScheduleText,
                        'in_time' => null,
                        'out_time' => null,
                        'late_minutes' => 0,
                        'late_category' => null,
                        'is_picket' => $isPicketToday,
                        'picket_area' => $picketAreaName,
                        'notes' => $isHoliday ? 'Hari Libur Resmi' : 'Jadwal Libur',
                        'color' => 'slate'
                    ];
                }

                $currentDate->addDay();
            }

            // Formula: Hadir / (Hadir + Alpa) * 100%
            $totalActiveWorkDays = $totalPresent + $totalAbsent;
            $percentage = $totalActiveWorkDays > 0 ? round(($totalPresent / $totalActiveWorkDays) * 100, 1) : 100;

            $totalAllWorkDays += $totalWorkDays;
            $totalAllPresent += $totalPresent;
            $totalAllAbsent += $totalAbsent;
            $totalAllSakit += $totalSakit;
            $totalAllIzin += $totalIzin;
            $totalAllCuti += $totalCuti;
            $totalLateMinutesAll += $totalLateMinutes;
            $totalLateEvents += $lateCount;
            if ($waspadaLateCount > 0) $totalWaspadaStaff++;
            if ($kritisLateCount > 0) $totalKritisStaff++;

            $reports[] = [
                'employee' => $emp,
                'total_work_days' => $totalWorkDays,
                'total_present' => $totalPresent,
                'actual_scan_count' => $actualScanCount,
                'scan_dates' => $scanDates,
                'dinas_count' => $dinasCount,
                'dinas_dates' => $dinasDates,
                'reward_count' => $rewardCount,
                'reward_dates' => $rewardDates,
                'total_sakit' => $totalSakit,
                'sakit_dates' => $sakitDates,
                'total_izin' => $totalIzin,
                'izin_dates' => $izinDates,
                'total_cuti' => $totalCuti,
                'cuti_dates' => $cutiDates,
                'total_absent' => $totalAbsent,
                'absent_dates' => $absentDates,
                'total_late_minutes' => $totalLateMinutes,
                'late_count' => $lateCount,
                'late_dates' => $lateDates,
                'waspada_late_count' => $waspadaLateCount,
                'waspada_dates' => $waspadaDates,
                'kritis_late_count' => $kritisLateCount,
                'kritis_dates' => $kritisDates,
                'percentage' => $percentage,
                'day_details' => $dayDetails,
            ];
        }

        $activeWorkDaysAll = $totalAllPresent + $totalAllAbsent;
        $unitAverage = $activeWorkDaysAll > 0 ? round(($totalAllPresent / $activeWorkDaysAll) * 100, 1) : 0;

        $positions = collect($reports)
            ->map(fn($r) => $r['employee']['position'] ?? $r['employee']['subject_position'] ?? 'Staf')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        $countGreen = collect($reports)->filter(fn($r) => $r['percentage'] >= 95)->count();
        $countYellow = collect($reports)->filter(fn($r) => $r['percentage'] >= 90 && $r['percentage'] < 95)->count();
        $countRed = collect($reports)->filter(fn($r) => $r['percentage'] < 90)->count();

        return response()->json([
            'success' => true,
            'month' => $month,
            'start_date' => $startDateReq,
            'end_date' => $endDateReq,
            'cutoff_date' => $cutoffDate,
            'unit_id' => $unitParam,
            'summary' => [
                'total_employees' => count($reports),
                'average_percentage' => $unitAverage,
                'unit_average_percentage' => $unitAverage,
                'count_green' => $countGreen,
                'count_yellow' => $countYellow,
                'count_red' => $countRed,
                'total_work_days' => $totalAllWorkDays,
                'total_present' => $totalAllPresent,
                'total_absent' => $totalAllAbsent,
                'total_sakit' => $totalAllSakit,
                'total_izin' => $totalAllIzin,
                'total_cuti' => $totalAllCuti,
                'waspada_staff_count' => $totalWaspadaStaff,
                'kritis_staff_count' => $totalKritisStaff,
                'total_late_events' => $totalLateEvents,
                'total_late_count' => $totalLateEvents,
                'total_late_minutes' => $totalLateMinutesAll,
            ],
            'positions' => $positions,
            'reports' => $reports,
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
            $calc = $this->calculateAttendanceLogic($emp['id'], $emp['unit_id'] ?? null, $date, $clockIn, $clockOut);

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

    private function calculateAttendanceLogic($employeeId, $unitId, $date, $clockIn, $clockOut)
    {
        $carbonDate = Carbon::parse($date);
        $dayOfWeek = $carbonDate->dayOfWeek; // 0=Sunday, 1=Monday, ..., 6=Saturday

        // 1. Check Holiday Adjustment or global holiday
        $isHoliday = false;
        $adjustment = HolidayAdjustment::where('adjusted_date', $date)
            ->where(function($q) use ($unitId) {
                $q->whereNull('school_unit_id')
                  ->orWhere('school_unit_id', $unitId);
            })->first();

        if ($adjustment) {
            $isHoliday = true;
        } else {
            $holiday = Holiday::where('original_date', $date)
                ->where('is_global', true)
                ->first();
            if ($holiday) {
                $wasRescheduled = HolidayAdjustment::where('holiday_id', $holiday->id)
                    ->where('original_date', $date)
                    ->where(function($q) use ($unitId) {
                        $q->whereNull('school_unit_id')
                          ->orWhere('school_unit_id', $unitId);
                    })->exists();
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
            ->orderByRaw('CASE 
                WHEN roster_name IS NOT NULL AND roster_name != "" THEN 3 
                WHEN end_date IS NOT NULL THEN 2 
                ELSE 1 
             END DESC')
            ->orderBy('start_date', 'desc')
            ->orderBy('id', 'desc')
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

        $isShiftWorker = $shift && $shift->is_shift;
        $isOffDay = ($shiftDetail && $shiftDetail->is_off) || ($isHoliday && !$isShiftWorker);

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

        $isKedinasan = false;
        if ($leave) {
            $ltLower = strtolower($leave->type_name ?? $leave->type ?? '');
            if (str_contains($ltLower, 'dinas') || str_contains($ltLower, 'kedinasan')) {
                $isKedinasan = true;
            }
        }

        if ($leave && (!$hasRequiresAttendanceLeave || ($isKedinasan && !$clockIn))) {
            $status = ($leave->status_code === 'S' || $leave->type_name === 'Sakit') ? 'Sick' : ($isKedinasan ? 'Dinas' : 'Leave');
            if ($leave->type_name === 'Dinas' || $leave->status_code === 'H' || $leave->gets_presence_bonus || $isKedinasan) {
                $currentSchemaId = ($activeShiftAssigned && $activeShiftAssigned->bonus_schema_id) 
                                    ? $activeShiftAssigned->bonus_schema_id 
                                    : (BonusSchema::where('is_active', true)->where('calculation_mode', 'late_tolerance')->first()->id ?? BonusSchema::where('is_active', true)->first()->id ?? null);
                
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
                                    : (BonusSchema::where('is_active', true)->where('calculation_mode', 'late_tolerance')->first()->id ?? BonusSchema::where('is_active', true)->first()->id ?? null);
                
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
                        if ($isForgiven || $isKedinasan) {
                            $lateMinutes = 0;
                        } else {
                            $lateMinutes = (int) abs($actualIn->diffInMinutes($shiftStart));
                            $status = 'Late';
                        }
                    }
                }

                $activeSchemaId = ($activeShiftAssigned && $activeShiftAssigned->bonus_schema_id)
                                    ? $activeShiftAssigned->bonus_schema_id
                                    : (BonusSchema::where('is_active', true)->where('calculation_mode', 'late_tolerance')->first()->id ?? BonusSchema::where('is_active', true)->first()->id ?? null);
                
                if ($activeSchemaId) {
                    if ($isKedinasan) {
                        $matchingTier = BonusTier::where('bonus_schema_id', $activeSchemaId)
                            ->orderBy('nominal', 'desc')
                            ->first();
                    } else {
                        $matchingTier = BonusTier::where('bonus_schema_id', $activeSchemaId)
                            ->where('max_late_minutes', '>=', $lateMinutes)
                            ->orderBy('nominal', 'desc')
                            ->first();
                    }

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

    /**
     * Fetch and index picket schedules and swaps across all school units.
     */
    private function getPicketDataIndexed(string $startDateReq, string $endDateReq): array
    {
        $picketData = $this->service->getAllPicketAssignments($startDateReq, $endDateReq);
        $schedules = $picketData['schedules'] ?? [];
        $swaps = $picketData['swaps'] ?? [];

        $schedulesMap = [];
        foreach ($schedules as $ps) {
            $pKey = ($ps['school_unit_id'] ?? '') . '_' . ($ps['employee_id'] ?? '') . '_' . ($ps['day_of_week'] ?? '');
            $schedulesMap[$pKey][] = $ps;
        }

        $swapOverrides = [];
        foreach ($swaps as $sw) {
            $swUnit = $sw['school_unit_id'] ?? '';
            $reqId = $sw['requester_id'] ?? null;
            $targetId = $sw['target_employee_id'] ?? null;
            $reqDate = $sw['requested_date'] ?? null;
            $targetDate = $sw['target_date'] ?? null;

            if ($reqDate) {
                if ($reqId) {
                    $swapOverrides[$swUnit . '_' . $reqId . '_' . $reqDate] = ['action' => 'released'];
                }
                if ($targetId) {
                    $swapOverrides[$swUnit . '_' . $targetId . '_' . $reqDate] = [
                        'action' => 'assigned',
                        'start_time' => '06:30:00',
                        'picket_area_name' => 'Tukar Piket',
                    ];
                }
            }

            if ($targetDate) {
                if ($targetId) {
                    $swapOverrides[$swUnit . '_' . $targetId . '_' . $targetDate] = ['action' => 'released'];
                }
                if ($reqId) {
                    $swapOverrides[$swUnit . '_' . $reqId . '_' . $targetDate] = [
                        'action' => 'assigned',
                        'start_time' => '06:30:00',
                        'picket_area_name' => 'Tukar Piket',
                    ];
                }
            }
        }

        return [
            'schedulesMap' => $schedulesMap,
            'swapOverrides' => $swapOverrides,
        ];
    }
}

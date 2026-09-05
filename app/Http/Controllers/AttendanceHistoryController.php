<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\EmployeeWorkingShift;
use App\Models\Setting;
use App\Models\SchoolUnit;
use App\Models\Holiday;
use App\Models\HolidayAdjustment;
use App\Models\LeaveRequest;
use App\Services\SchoolUnitService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AttendanceHistoryController extends Controller
{
    protected SchoolUnitService $service;

    public function __construct(SchoolUnitService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $startDateReq = $request->query('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDateReq = $request->query('end_date', Carbon::now()->format('Y-m-d'));

        $startDate = Carbon::parse($startDateReq)->startOfDay();
        $endDate = Carbon::parse($endDateReq)->endOfDay();

        // Enforce maximum 31 days range to prevent memory exhaustion
        $diffDays = $startDate->diffInDays($endDate);
        if ($diffDays > 31) {
            $endDate = $startDate->copy()->addDays(30)->endOfDay();
            $endDateReq = $endDate->format('Y-m-d');
            session()->now('warning', 'Rentang tanggal dibatasi maksimal 31 hari untuk menjaga kestabilan performa.');
        }

        $search = $request->query('search');
        $unitId = $request->query('unit_id');
        $position = $request->query('position');
        $selectedStatus = $request->query('status'); // Hadir, Terlambat, Alfa, Sakit, Izin, Cuti, Dinas

        // Get unique positions from raw employee data using the renamed service method
        $rawEmployees = collect($this->service->getAllEmployees());
        
        $unitPositions = [];
        $unitPositions[''] = $rawEmployees
            ->map(fn($emp) => $emp['position'] ?? $emp['subject_position'] ?? 'Staf')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        foreach ($rawEmployees->groupBy('unit_id') as $uId => $emps) {
            $unitPositions[$uId] = $emps
                ->map(fn($emp) => $emp['position'] ?? $emp['subject_position'] ?? 'Staf')
                ->filter()
                ->unique()
                ->sort()
                ->values()
                ->toArray();
        }

        $positions = !empty($unitId) ? ($unitPositions[$unitId] ?? []) : $unitPositions[''];

        // Filter employees
        if (!empty($unitId)) {
            $rawEmployees = $rawEmployees->filter(fn($emp) => ($emp['unit_id'] ?? '') == $unitId);
        }
        if (!empty($position)) {
            $rawEmployees = $rawEmployees->filter(function ($emp) use ($position) {
                $pos = $emp['position'] ?? $emp['subject_position'] ?? 'Staf';
                return $pos === $position;
            });
        }
        if (!empty($search)) {
            $rawEmployees = $rawEmployees->filter(function ($emp) use ($search) {
                return str_contains(strtolower($emp['name'] ?? ''), strtolower($search))
                    || str_contains(strtolower($emp['nuptk_nip_nik'] ?? ''), strtolower($search));
            });
        }

        $employeesCollection = $rawEmployees->values();
        $uids = $employeesCollection->pluck('zkteco_uid')->filter()->toArray();
        $employeeIds = $employeesCollection->pluck('id')->filter()->toArray();

        // 1. Fetch holidays
        $holidays = Holiday::all();
        $holidayAdjustments = HolidayAdjustment::all();
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

        // 2. Fetch approved and pending leaves
        $leavesData = LeaveRequest::whereIn('employee_id', $employeeIds)
            ->whereIn('status', ['Approved', 'Pending'])
            ->where(function($q) use ($startDate, $endDate) {
                $q->where('start_date', '<=', $endDate->format('Y-m-d'))
                  ->where('end_date', '>=', $startDate->format('Y-m-d'));
            })
            ->get()
            ->groupBy(function($leave) {
                return ($leave->school_unit_id ?: '') . '_' . $leave->employee_id;
            })
            ->map(function($group) {
                return $group->sort(function($a, $b) {
                    $statusOrder = ['Approved' => 1, 'Pending' => 2, 'Rejected' => 3];
                    $orderA = $statusOrder[$a->status] ?? 4;
                    $orderB = $statusOrder[$b->status] ?? 4;
                    if ($orderA !== $orderB) {
                        return $orderA <=> $orderB;
                    }
                    return $b->id <=> $a->id;
                });
            });

        $shiftsMonthStart = $startDate->copy()->startOfMonth()->format('Y-m-d');
        $shiftsMonthEnd = $endDate->copy()->endOfMonth()->format('Y-m-d');

        $shiftsData = EmployeeWorkingShift::with(['workingShift.details'])
            ->whereIn('employee_id', $employeeIds)
            ->where(function ($query) use ($shiftsMonthStart, $shiftsMonthEnd) {
                $query->where('start_date', '<=', $shiftsMonthEnd)
                      ->where(function($q) use ($shiftsMonthStart) {
                          $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', $shiftsMonthStart);
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
                        return $scoreB <=> $scoreA;
                    }
                    $dateA = $a->start_date instanceof \Carbon\Carbon ? $a->start_date->format('Y-m-d') : substr($a->start_date, 0, 10);
                    $dateB = $b->start_date instanceof \Carbon\Carbon ? $b->start_date->format('Y-m-d') : substr($b->start_date, 0, 10);
                    if ($dateA !== $dateB) {
                        return strcmp($dateB, $dateA);
                    }
                    return $b->id <=> $a->id;
                });
            });

        // 4. Fetch attendance logs
        $logsData = AttendanceLog::whereIn('uid', $uids)
            ->whereBetween('timestamp', [
                $startDate->format('Y-m-d 00:00:00'),
                $endDate->copy()->addDay()->format('Y-m-d 12:00:00')
            ])
            ->get()
            ->groupBy('uid');

        // Build flat history list
        $historyList = [];

        foreach ($employeesCollection as $emp) {
            $uid = $emp['zkteco_uid'] ?? null;
            $empId = $emp['id'] ?? null;
            $unit = $emp['unit_id'] ?? null;

            if (!$uid || !$empId) continue;

            $userLogs = $logsData->get((string)$uid) ? $logsData->get((string)$uid)->pluck('timestamp')->sort()->values()->toArray() : [];
            $consumedLogs = [];

            $currentDate = $startDate->copy();
            $lastDay = $endDate->greaterThan(Carbon::now()) ? Carbon::now()->endOfDay() : $endDate;

            while ($currentDate <= $lastDay) {
                $dateStr = $currentDate->format('Y-m-d');
                $dayOfWeek = $currentDate->dayOfWeekIso;

                $empUnitKey = ($unit && isset($unitHolidays[$unit])) ? $unit : '';
                $isHoliday = $unitHolidays[$empUnitKey][$dateStr] ?? false;
                
                $isOnLeave = false;
                $getsBonus = false;
                $statusCode = null;
                $leaveReason = '';
                $leaveApprovalStatus = null;
                $leaveKey = $unit . '_' . $empId;
                $fallbackLeaveKey = '_' . $empId;
                $empLeaves = $leavesData->get($leaveKey) ?? $leavesData->get($fallbackLeaveKey) ?? [];
                foreach ($empLeaves as $leave) {
                    $leaveStart = $leave->start_date instanceof \Carbon\Carbon ? $leave->start_date->format('Y-m-d') : substr((string)$leave->start_date, 0, 10);
                    $leaveEnd = $leave->end_date instanceof \Carbon\Carbon ? $leave->end_date->format('Y-m-d') : substr((string)$leave->end_date, 0, 10);
                    if ($dateStr >= $leaveStart && $dateStr <= $leaveEnd) {
                        $isOnLeave = true;
                        $leaveApprovalStatus = $leave->status;
                        $getsBonus = $leave->gets_presence_bonus || ($leave->status_code === 'H') || ($leave->type_name === 'Dinas');
                        $statusCode = $leave->status_code;
                        $leaveReason = !empty($leave->reason) && $leave->reason !== '-' 
                            ? $leave->reason 
                            : (!empty($leave->notes) && $leave->notes !== '-' 
                                ? $leave->notes 
                                : (!empty($leave->type) ? $leave->type : ($leave->type_name ?? 'Izin')));
                        if (!$statusCode) {
                            if ($leave->type_name === 'Sakit' || str_contains(strtolower($leave->type ?? ''), 'sakit')) $statusCode = 'S';
                            elseif ($leave->type_name === 'Cuti' || str_contains(strtolower($leave->type ?? ''), 'cuti')) $statusCode = 'C';
                            elseif ($leave->type_name === 'Dinas' || str_contains(strtolower($leave->type ?? ''), 'dinas') || str_contains(strtolower($leave->type ?? ''), 'kedinasan')) $statusCode = 'H';
                            else $statusCode = 'I';
                        }
                        break;
                    }
                }

                $hasShiftToday = false;
                $isOffShift = false;
                $shiftStartTime = null;
                $shiftEndTime = null;
                $shiftName = '-';
                $shiftKey = $unit . '_' . $empId;
                $isShiftWorker = false;
                $matchedRosterAssignment = null;

                // Resolve Assigned Shift
                $matchedAssignment = null;
                if (isset($shiftsData[$shiftKey])) {
                    foreach ($shiftsData[$shiftKey] as $assignment) {
                        if ($assignment->workingShift->is_shift) {
                            $isShiftWorker = true;
                        }
                        $assignStartDate = substr($assignment->start_date, 0, 10);
                        $assignEndDate = $assignment->end_date ? substr($assignment->end_date, 0, 10) : null;
                        if ($dateStr >= $assignStartDate && (!$assignEndDate || $dateStr <= $assignEndDate)) {
                            $matchedAssignment = $assignment->workingShift;
                            break;
                        }
                    }
                }

                $activeShift = $matchedAssignment;

                if ($activeShift) {
                    $shiftName = $activeShift->name;
                    $detail = $activeShift->details->where('day_of_week', $dayOfWeek)->first();
                    if ($detail) {
                        if ($detail->is_off) {
                            $isOffShift = true;
                            $shiftStartTime = $detail->start_time;
                            $shiftEndTime = $detail->end_time;
                        } else {
                            $hasShiftToday = true;
                            $shiftStartTime = $detail->start_time;
                            $shiftEndTime = $detail->end_time;
                        }
                    }
                } else {
                    // Check if they are on a roster in this same month
                    $monthStr = substr($dateStr, 0, 7);
                    $matchedRosterAssignment = null;
                    if (isset($shiftsData[$shiftKey])) {
                        foreach ($shiftsData[$shiftKey] as $assignment) {
                            $assignStartMonth = substr($assignment->start_date instanceof \Carbon\Carbon ? $assignment->start_date->format('Y-m-d') : $assignment->start_date, 0, 7);
                            if ($assignment->roster_name && $assignStartMonth === $monthStr) {
                                $matchedRosterAssignment = $assignment;
                                break;
                            }
                        }
                    }
                    if ($matchedRosterAssignment) {
                        $shiftName = $matchedRosterAssignment->roster_name;
                        $isOffShift = true;
                    }
                }

                if ($isShiftWorker && !$hasShiftToday && !$isOffShift) {
                    $isOffShift = true;
                }

                // Check-in and Check-out Log retrieval using unconsumed logs
                $availableLogs = array_diff($userLogs, $consumedLogs);

                // Consume orphan night shift checkout: if we have an early morning scan on this day (before 10:00),
                // and there is an unconsumed scan from yesterday evening (after 17:00), consume both of them!
                $yesterdayDateStr = $currentDate->copy()->subDay()->format('Y-m-d');
                $yesterdayNightScan = null;
                $todayMorningScan = null;
                
                foreach ($availableLogs as $tsStr) {
                    if (substr($tsStr, 0, 10) === $yesterdayDateStr) {
                        $timePart = substr($tsStr, 11, 8);
                        if ($timePart >= '17:00:00' && $timePart <= '23:59:59') {
                            if (!$yesterdayNightScan || $tsStr < $yesterdayNightScan) {
                                $yesterdayNightScan = $tsStr;
                            }
                        }
                    }
                    if (substr($tsStr, 0, 10) === $dateStr) {
                        $timePart = substr($tsStr, 11, 8);
                        if ($timePart >= '04:00:00' && $timePart <= '10:00:00') {
                            if (!$todayMorningScan || $tsStr < $todayMorningScan) {
                                $todayMorningScan = $tsStr;
                            }
                        }
                    }
                }
                
                if ($yesterdayNightScan && $todayMorningScan) {
                    $consumedLogs[] = $yesterdayNightScan;
                    $consumedLogs[] = $todayMorningScan;
                    $availableLogs = array_diff($userLogs, $consumedLogs);
                }

                $checkInLog = null;
                $checkOutLog = null;

                if ($shiftStartTime && $shiftEndTime && !$isOffShift) {
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

                    foreach ($availableLogs as $tsStr) {
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

                    if ($checkInLog && $checkOutLog && $checkInLog === $checkOutLog) {
                        $diffIn = Carbon::parse($checkInLog)->diffInMinutes($expectedIn);
                        $diffOut = Carbon::parse($checkOutLog)->diffInMinutes($expectedOut);
                        if ($diffIn < $diffOut) {
                            $checkOutLog = null;
                        } else {
                            $checkInLog = null;
                        }
                    }

                    if ($checkInLog && $checkOutLog && $checkInLog !== $checkOutLog) {
                        if (Carbon::parse($checkInLog)->diffInHours(Carbon::parse($checkOutLog)) < 2) {
                            $diffIn = Carbon::parse($checkInLog)->diffInMinutes($expectedIn);
                            $diffOut = Carbon::parse($checkOutLog)->diffInMinutes($expectedOut);
                            if ($diffIn < $diffOut) {
                                $checkOutLog = null;
                            } else {
                                $checkInLog = null;
                            }
                        }
                    }
                } else {
                    // Rest day (Off / Holiday / Sunday)
                    
                    // 1. Try to find a night shift starting on this rest day (check-in after 17:00, check-out next morning before 10:00)
                    $nightCheckIn = null;
                    $nightCheckOut = null;
                    
                    foreach ($availableLogs as $tsStr) {
                        if (substr($tsStr, 0, 10) === $dateStr) {
                            $timePart = substr($tsStr, 11, 8);
                            if ($timePart >= '17:00:00' && $timePart <= '23:59:59') {
                                if (!$nightCheckIn || $tsStr < $nightCheckIn) {
                                    $nightCheckIn = $tsStr;
                                }
                            }
                        }
                    }
                    
                    if ($nightCheckIn) {
                        $nextDateStr = $currentDate->copy()->addDay()->format('Y-m-d');
                        foreach ($availableLogs as $tsStr) {
                            if (substr($tsStr, 0, 10) === $nextDateStr) {
                                $timePart = substr($tsStr, 11, 8);
                                if ($timePart >= '04:00:00' && $timePart <= '10:00:00') {
                                    if (!$nightCheckOut || $tsStr < $nightCheckOut) {
                                        $nightCheckOut = $tsStr;
                                    }
                                }
                            }
                        }
                    }
                    
                    if ($nightCheckIn) {
                        $checkInLog = $nightCheckIn;
                        $checkOutLog = $nightCheckOut;
                    } else {
                        // 2. Normal day shift on rest day (all scans on this day)
                        $dayScans = [];
                        foreach ($availableLogs as $tsStr) {
                            if (substr($tsStr, 0, 10) === $dateStr) {
                                $dayScans[] = $tsStr;
                            }
                        }
                        if (count($dayScans) > 0) {
                            sort($dayScans);
                            $checkInLog = $dayScans[0];
                            if (count($dayScans) > 1) {
                                $checkOutLog = $dayScans[count($dayScans) - 1];
                            }
                        }
                    }
                }

                if ($checkInLog) {
                    $consumedLogs[] = $checkInLog;
                }
                if ($checkOutLog && $checkOutLog !== $checkInLog) {
                    $consumedLogs[] = $checkOutLog;
                }

                $record = [
                    'employee_name' => $emp['name'] ?? '',
                    'employee_nip' => $emp['nuptk_nip_nik'] ?? '',
                    'zkteco_uid' => $uid,
                    'unit_name' => $emp['unit_name'] ?? 'Staf Yayasan',
                    'position' => $emp['position'] ?? $emp['subject_position'] ?? 'Staf',
                    'date' => $currentDate->copy(),
                    'date_formatted' => $currentDate->translatedFormat('l, d M Y'),
                    'shift_name' => $shiftName,
                    'shift_start' => $shiftStartTime ? substr($shiftStartTime, 0, 5) : null,
                    'shift_end' => $shiftEndTime ? substr($shiftEndTime, 0, 5) : null,
                    'check_in' => $checkInLog ? substr($checkInLog, 11, 5) : null,
                    'check_out' => $checkOutLog ? (substr($checkOutLog, 11, 5) . (substr($checkOutLog, 0, 10) !== $dateStr ? ' (Besok)' : '')) : null,
                    'status' => 'Off',
                    'late_minutes' => 0,
                    'notes' => '',
                    'leave_status' => $isOnLeave ? $leaveApprovalStatus : null,
                ];

                // Status determination
                if ($isHoliday && !$isShiftWorker) {
                    if ($checkInLog) {
                        $record['status'] = 'Tepat waktu';
                        $record['notes'] = 'Masuk di Hari Libur';
                    } else {
                        $record['status'] = 'Libur';
                        $record['notes'] = 'Libur Nasional / Yayasan';
                    }
                } elseif ($activeShift) {
                    if ($hasShiftToday) {
                        if ($checkInLog || $checkOutLog) {
                            $isLate = false;
                            $isRedLate = false;
                            $lateMinutes = 0;
                            if ($checkInLog && $shiftStartTime) {
                                $checkInTime = Carbon::parse($checkInLog)->second(0);
                                $expectedInTime = Carbon::parse($dateStr . ' ' . $shiftStartTime)->second(0);
                                if ($checkInTime > $expectedInTime) {
                                    $isLate = true;
                                    $lateMinutes = (int) abs($checkInTime->diffInMinutes($expectedInTime));
                                }

                                $empPosition = strtolower($emp['position'] ?? $emp['subject_position'] ?? '');
                                $isShiftExempt = $isShiftWorker 
                                    || $matchedRosterAssignment
                                    || str_contains($empPosition, 'keamanan') 
                                    || str_contains($empPosition, 'satpam') 
                                    || str_contains($empPosition, 'security') 
                                    || str_contains($empPosition, 'mart') 
                                    || str_contains($empPosition, 'toko') 
                                    || str_contains($empPosition, 'salehmart');

                                if (!$isShiftExempt && $checkInTime > Carbon::parse($dateStr . ' 07:25:00')->second(0)) {
                                    if (!($isOnLeave && $leaveApprovalStatus === 'Approved')) {
                                        $isRedLate = true;
                                    }
                                }
                            }
                            $record['late_minutes'] = $lateMinutes;
                            if ($isRedLate) {
                                $record['status'] = 'Mengkhawatirkan';
                            } elseif ($isLate) {
                                $record['status'] = 'Terlambat';
                            } else {
                                $record['status'] = 'Tepat waktu';
                            }
                            if ($isOnLeave) {
                                $record['notes'] = $leaveReason;
                            }
                        } elseif ($isOnLeave) {
                            if ($statusCode === 'S') {
                                $record['status'] = 'Sakit';
                            } elseif ($statusCode === 'C') {
                                $record['status'] = 'Cuti';
                            } elseif ($statusCode === 'H' || $getsBonus) {
                                $record['status'] = 'Dinas';
                            } else {
                                $record['status'] = 'Izin';
                            }
                            $record['notes'] = $leaveReason;
                        } else {
                            $now = Carbon::now('Asia/Jakarta');
                            $shiftStartDateTime = Carbon::parse($dateStr . ' ' . $shiftStartTime, 'Asia/Jakarta');
                            if ($now->lessThan($shiftStartDateTime)) {
                                $record['status'] = 'Pending';
                            } else {
                                $record['status'] = 'Alfa';
                            }
                        }
                    } elseif ($isOffShift) {
                        if ($checkInLog) {
                            $record['status'] = 'Tepat waktu';
                            $record['notes'] = ($isShiftWorker || $matchedRosterAssignment) ? 'Masuk Kerja' : 'Masuk di Hari Libur Pekan';
                        } else {
                            if ($isOnLeave) {
                                if ($statusCode === 'S') {
                                    $record['status'] = 'Sakit';
                                } elseif ($statusCode === 'C') {
                                    $record['status'] = 'Cuti';
                                } elseif ($statusCode === 'H' || $getsBonus) {
                                    $record['status'] = 'Dinas';
                                } else {
                                    $record['status'] = 'Izin';
                                }
                                $record['notes'] = $leaveReason;
                            } else {
                                $record['status'] = 'Off';
                                $record['notes'] = 'Jadwal Libur';
                            }
                        }
                    } else {
                        if ($checkInLog) {
                            $record['status'] = 'Tepat waktu';
                            $record['notes'] = 'Masuk Kerja';
                        } else {
                            if ($isOnLeave) {
                                if ($statusCode === 'S') {
                                    $record['status'] = 'Sakit';
                                } elseif ($statusCode === 'C') {
                                    $record['status'] = 'Cuti';
                                } elseif ($statusCode === 'H' || $getsBonus) {
                                    $record['status'] = 'Dinas';
                                } else {
                                    $record['status'] = 'Izin';
                                }
                                $record['notes'] = $leaveReason;
                            } elseif ($dayOfWeek == 0) {
                                $record['status'] = 'Libur';
                                $record['notes'] = 'Hari Minggu (Non-Shift)';
                            } else {
                                $record['status'] = 'Off';
                            }
                        }
                    }
                } elseif ($isOffShift) {
                    if ($checkInLog) {
                        $record['status'] = 'Tepat waktu';
                        $record['notes'] = ($isShiftWorker || $matchedRosterAssignment) ? 'Masuk Kerja' : 'Masuk di Hari Libur Pekan';
                    } else {
                        if ($isOnLeave) {
                            if ($statusCode === 'S') {
                                $record['status'] = 'Sakit';
                            } elseif ($statusCode === 'C') {
                                $record['status'] = 'Cuti';
                            } elseif ($statusCode === 'H' || $getsBonus) {
                                $record['status'] = 'Dinas';
                            } else {
                                $record['status'] = 'Izin';
                            }
                            $record['notes'] = $leaveReason;
                        } else {
                            $record['status'] = 'Off';
                            $record['notes'] = 'Jadwal Libur';
                        }
                    }
                } else {
                    // No shift/schedule assigned at all
                    if ($checkInLog) {
                        $record['status'] = 'Tepat waktu';
                        $record['notes'] = 'Masuk Kerja (Tanpa Jadwal)';
                    } else {
                        if ($isOnLeave) {
                            if ($statusCode === 'S') {
                                $record['status'] = 'Sakit';
                            } elseif ($statusCode === 'C') {
                                $record['status'] = 'Cuti';
                            } elseif ($statusCode === 'H' || $getsBonus) {
                                $record['status'] = 'Dinas';
                            } else {
                                $record['status'] = 'Izin';
                            }
                            $record['notes'] = $leaveReason;
                        } else {
                            $record['status'] = '-';
                            $record['notes'] = '-';
                        }
                    }
                }

                // Override if leave is approved/pending on working days
                if ($isOnLeave && ($record['status'] === 'Alfa' || $record['status'] === 'Pending')) {
                    if ($statusCode === 'S') {
                        $record['status'] = 'Sakit';
                    } elseif ($statusCode === 'C') {
                        $record['status'] = 'Cuti';
                    } elseif ($statusCode === 'H' || $getsBonus) {
                        $record['status'] = 'Dinas';
                    } else {
                        $record['status'] = 'Izin';
                    }
                    $record['notes'] = $leaveReason;
                }

                // Skip empty Libur/Off/- records if there is no actual attendance log and not on leave
                if (in_array($record['status'], ['Libur', 'Off', '-']) && !$checkInLog && !$checkOutLog && !$isOnLeave) {
                    $currentDate->addDay();
                    continue;
                }

                $historyList[] = $record;
                $currentDate->addDay();
            }
        }

        // Sort descending date, then ascending unit name, then ascending name
        usort($historyList, function($a, $b) {
            $dateA = $a['date']->format('Y-m-d');
            $dateB = $b['date']->format('Y-m-d');
            if ($dateA !== $dateB) {
                return strcmp($dateB, $dateA);
            }
            $unitCompare = strcmp($a['unit_name'] ?? '', $b['unit_name'] ?? '');
            if ($unitCompare !== 0) {
                return $unitCompare;
            }
            return strcmp($a['employee_name'], $b['employee_name']);
        });

        // Filter status
        if (!empty($selectedStatus)) {
            $historyList = array_filter($historyList, function ($item) use ($selectedStatus) {
                if (strtolower($selectedStatus) === 'tepat waktu' || strtolower($selectedStatus) === 'hadir') {
                    return in_array(strtolower($item['status']), ['tepat waktu', 'hadir']);
                }
                return strtolower($item['status']) === strtolower($selectedStatus);
            });
        }

        // Paginator
        $total = count($historyList);
        $perPageInput = $request->query('per_page', 50);
        $page = (int) $request->query('page', 1);

        if ($perPageInput === 'all') {
            $perPage = $total > 0 ? $total : 50;
            $paginatedItems = $historyList;
        } else {
            $perPage = (int) $perPageInput;
            if ($perPage <= 0) {
                $perPage = 50;
            }
            $paginatedItems = array_slice($historyList, ($page - 1) * $perPage, $perPage);
        }

        $paginatedHistory = new LengthAwarePaginator(
            $paginatedItems,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $schoolUnits = SchoolUnit::where('is_active', true)->orderBy('name')->get();

        return view('attendance-history.index', compact(
            'paginatedHistory',
            'schoolUnits',
            'startDateReq',
            'endDateReq',
            'positions',
            'selectedStatus',
            'unitPositions'
        ));
    }

    public function export(Request $request)
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 300);

        $startDateReq = $request->query('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDateReq = $request->query('end_date', Carbon::now()->format('Y-m-d'));

        $startDate = Carbon::parse($startDateReq)->startOfDay();
        $endDate = Carbon::parse($endDateReq)->endOfDay();

        $search = $request->query('search');
        $unitId = $request->query('unit_id');
        $position = $request->query('position');
        $selectedStatus = $request->query('status');

        // Fetch employees list using the renamed service method
        $rawEmployees = collect($this->service->getAllEmployees());
        if (!empty($unitId)) {
            $rawEmployees = $rawEmployees->filter(fn($emp) => ($emp['unit_id'] ?? '') == $unitId);
        }
        if (!empty($position)) {
            $rawEmployees = $rawEmployees->filter(function ($emp) use ($position) {
                $pos = $emp['position'] ?? $emp['subject_position'] ?? 'Staf';
                return $pos === $position;
            });
        }
        if (!empty($search)) {
            $rawEmployees = $rawEmployees->filter(function ($emp) use ($search) {
                return str_contains(strtolower($emp['name'] ?? ''), strtolower($search))
                    || str_contains(strtolower($emp['nuptk_nip_nik'] ?? ''), strtolower($search));
            });
        }
        $employeesCollection = $rawEmployees->values();
        $uids = $employeesCollection->pluck('zkteco_uid')->filter()->toArray();
        $employeeIds = $employeesCollection->pluck('id')->filter()->toArray();

        $format = $request->query('format', 'excel');

        // 1. Fetch holidays
        $holidays = Holiday::all();
        $holidayAdjustments = HolidayAdjustment::all();
        $schoolUnitsList = SchoolUnit::where('is_active', true)->get();
        $unitHolidays = [];
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

        // 2. Fetch approved and pending leaves
        $leavesData = LeaveRequest::whereIn('employee_id', $employeeIds)
            ->whereIn('status', ['Approved', 'Pending'])
            ->where(function($q) use ($startDate, $endDate) {
                $q->where('start_date', '<=', $endDate->format('Y-m-d'))
                  ->where('end_date', '>=', $startDate->format('Y-m-d'));
            })
            ->get()
            ->groupBy(function($leave) {
                return ($leave->school_unit_id ?: '') . '_' . $leave->employee_id;
            })
            ->map(function($group) {
                return $group->sort(function($a, $b) {
                    $statusOrder = ['Approved' => 1, 'Pending' => 2, 'Rejected' => 3];
                    $orderA = $statusOrder[$a->status] ?? 4;
                    $orderB = $statusOrder[$b->status] ?? 4;
                    if ($orderA !== $orderB) {
                        return $orderA <=> $orderB;
                    }
                    return $b->id <=> $a->id;
                });
            });

        $shiftsMonthStart = $startDate->copy()->startOfMonth()->format('Y-m-d');
        $shiftsMonthEnd = $endDate->copy()->endOfMonth()->format('Y-m-d');

        $shiftsData = EmployeeWorkingShift::with(['workingShift.details'])
            ->whereIn('employee_id', $employeeIds)
            ->where(function ($query) use ($shiftsMonthStart, $shiftsMonthEnd) {
                $query->where('start_date', '<=', $shiftsMonthEnd)
                      ->where(function($q) use ($shiftsMonthStart) {
                          $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', $shiftsMonthStart);
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
                        return $scoreB <=> $scoreA;
                    }
                    $dateA = $a->start_date instanceof \Carbon\Carbon ? $a->start_date->format('Y-m-d') : substr($a->start_date, 0, 10);
                    $dateB = $b->start_date instanceof \Carbon\Carbon ? $b->start_date->format('Y-m-d') : substr($b->start_date, 0, 10);
                    if ($dateA !== $dateB) {
                        return strcmp($dateB, $dateA);
                    }
                    return $b->id <=> $a->id;
                });
            });

        // 4. Fetch attendance logs
        $logsData = AttendanceLog::whereIn('uid', $uids)
            ->whereBetween('timestamp', [
                $startDate->format('Y-m-d 00:00:00'),
                $endDate->copy()->addDay()->format('Y-m-d 12:00:00')
            ])
            ->get()
            ->groupBy('uid');

        // Build list
        $historyList = [];

        foreach ($employeesCollection as $emp) {
            $uid = $emp['zkteco_uid'] ?? null;
            $empId = $emp['id'] ?? null;
            $unit = $emp['unit_id'] ?? null;

            if (!$uid || !$empId) continue;

            $userLogs = $logsData->get((string)$uid) ? $logsData->get((string)$uid)->pluck('timestamp')->sort()->values()->toArray() : [];
            $consumedLogs = [];

            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                $dateStr = $currentDate->format('Y-m-d');
                $dayOfWeek = $currentDate->dayOfWeekIso;

                $empUnitKey = ($unit && isset($unitHolidays[$unit])) ? $unit : '';
                $isHoliday = $unitHolidays[$empUnitKey][$dateStr] ?? false;
                
                $isOnLeave = false;
                $getsBonus = false;
                $statusCode = null;
                $leaveReason = '';
                $leaveApprovalStatus = null;
                $leaveKey = $unit . '_' . $empId;
                $fallbackLeaveKey = '_' . $empId;
                $empLeaves = $leavesData->get($leaveKey) ?? $leavesData->get($fallbackLeaveKey) ?? [];
                foreach ($empLeaves as $leave) {
                    $leaveStart = $leave->start_date instanceof \Carbon\Carbon ? $leave->start_date->format('Y-m-d') : substr((string)$leave->start_date, 0, 10);
                    $leaveEnd = $leave->end_date instanceof \Carbon\Carbon ? $leave->end_date->format('Y-m-d') : substr((string)$leave->end_date, 0, 10);
                    if ($dateStr >= $leaveStart && $dateStr <= $leaveEnd) {
                        $isOnLeave = true;
                        $leaveApprovalStatus = $leave->status;
                        $getsBonus = $leave->gets_presence_bonus || ($leave->status_code === 'H') || ($leave->type_name === 'Dinas');
                        $statusCode = $leave->status_code;
                        $leaveReason = !empty($leave->reason) && $leave->reason !== '-' 
                            ? $leave->reason 
                            : (!empty($leave->notes) && $leave->notes !== '-' 
                                ? $leave->notes 
                                : (!empty($leave->type) ? $leave->type : ($leave->type_name ?? 'Izin')));
                        if (!$statusCode) {
                            if ($leave->type_name === 'Sakit' || str_contains(strtolower($leave->type ?? ''), 'sakit')) $statusCode = 'S';
                            elseif ($leave->type_name === 'Cuti' || str_contains(strtolower($leave->type ?? ''), 'cuti')) $statusCode = 'C';
                            elseif ($leave->type_name === 'Dinas' || str_contains(strtolower($leave->type ?? ''), 'dinas') || str_contains(strtolower($leave->type ?? ''), 'kedinasan')) $statusCode = 'H';
                            else $statusCode = 'I';
                        }
                        break;
                    }
                }

                $hasShiftToday = false;
                $isOffShift = false;
                $shiftStartTime = null;
                $shiftEndTime = null;
                $shiftName = '-';
                $shiftKey = $unit . '_' . $empId;
                $isShiftWorker = false;
                $matchedRosterAssignment = null;

                // Resolve Assigned Shift
                $matchedAssignment = null;
                if (isset($shiftsData[$shiftKey])) {
                    foreach ($shiftsData[$shiftKey] as $assignment) {
                        if ($assignment->workingShift->is_shift) {
                            $isShiftWorker = true;
                        }
                        $assignStartDate = substr($assignment->start_date, 0, 10);
                        $assignEndDate = $assignment->end_date ? substr($assignment->end_date, 0, 10) : null;
                        if ($dateStr >= $assignStartDate && (!$assignEndDate || $dateStr <= $assignEndDate)) {
                            $matchedAssignment = $assignment->workingShift;
                            break;
                        }
                    }
                }

                $activeShift = $matchedAssignment;

                if ($activeShift) {
                    $shiftName = $activeShift->name;
                    $detail = $activeShift->details->where('day_of_week', $dayOfWeek)->first();
                    if ($detail) {
                        if ($detail->is_off) {
                            $isOffShift = true;
                            $shiftStartTime = $detail->start_time;
                            $shiftEndTime = $detail->end_time;
                        } else {
                            $hasShiftToday = true;
                            $shiftStartTime = $detail->start_time;
                            $shiftEndTime = $detail->end_time;
                        }
                    }
                } else {
                    // Check if they are on a roster in this same month
                    $monthStr = substr($dateStr, 0, 7);
                    $matchedRosterAssignment = null;
                    if (isset($shiftsData[$shiftKey])) {
                        foreach ($shiftsData[$shiftKey] as $assignment) {
                            $assignStartMonth = substr($assignment->start_date instanceof \Carbon\Carbon ? $assignment->start_date->format('Y-m-d') : $assignment->start_date, 0, 7);
                            if ($assignment->roster_name && $assignStartMonth === $monthStr) {
                                $matchedRosterAssignment = $assignment;
                                break;
                            }
                        }
                    }
                    if ($matchedRosterAssignment) {
                        $shiftName = $matchedRosterAssignment->roster_name;
                        $isOffShift = true;
                    }
                }

                if ($isShiftWorker && !$hasShiftToday && !$isOffShift) {
                    $isOffShift = true;
                }

                // Check-in and Check-out Log retrieval using unconsumed logs
                $availableLogs = array_diff($userLogs, $consumedLogs);

                // Consume orphan night shift checkout: if we have an early morning scan on this day (before 10:00),
                // and there is an unconsumed scan from yesterday evening (after 17:00), consume both of them!
                $yesterdayDateStr = $currentDate->copy()->subDay()->format('Y-m-d');
                $yesterdayNightScan = null;
                $todayMorningScan = null;
                
                foreach ($availableLogs as $tsStr) {
                    if (substr($tsStr, 0, 10) === $yesterdayDateStr) {
                        $timePart = substr($tsStr, 11, 8);
                        if ($timePart >= '17:00:00' && $timePart <= '23:59:59') {
                            if (!$yesterdayNightScan || $tsStr < $yesterdayNightScan) {
                                $yesterdayNightScan = $tsStr;
                            }
                        }
                    }
                    if (substr($tsStr, 0, 10) === $dateStr) {
                        $timePart = substr($tsStr, 11, 8);
                        if ($timePart >= '04:00:00' && $timePart <= '10:00:00') {
                            if (!$todayMorningScan || $tsStr < $todayMorningScan) {
                                $todayMorningScan = $tsStr;
                            }
                        }
                    }
                }
                
                if ($yesterdayNightScan && $todayMorningScan) {
                    $consumedLogs[] = $yesterdayNightScan;
                    $consumedLogs[] = $todayMorningScan;
                    $availableLogs = array_diff($userLogs, $consumedLogs);
                }

                $checkInLog = null;
                $checkOutLog = null;

                if ($shiftStartTime && $shiftEndTime && !$isOffShift) {
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

                    foreach ($availableLogs as $tsStr) {
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

                    if ($checkInLog && $checkOutLog && $checkInLog === $checkOutLog) {
                        $diffIn = Carbon::parse($checkInLog)->diffInMinutes($expectedIn);
                        $diffOut = Carbon::parse($checkOutLog)->diffInMinutes($expectedOut);
                        if ($diffIn < $diffOut) {
                            $checkOutLog = null;
                        } else {
                            $checkInLog = null;
                        }
                    }

                    if ($checkInLog && $checkOutLog && $checkInLog !== $checkOutLog) {
                        if (Carbon::parse($checkInLog)->diffInHours(Carbon::parse($checkOutLog)) < 2) {
                            $diffIn = Carbon::parse($checkInLog)->diffInMinutes($expectedIn);
                            $diffOut = Carbon::parse($checkOutLog)->diffInMinutes($expectedOut);
                            if ($diffIn < $diffOut) {
                                $checkOutLog = null;
                            } else {
                                $checkInLog = null;
                            }
                        }
                    }
                } else {
                    // Rest day (Off / Holiday / Sunday)
                    
                    // 1. Try to find a night shift starting on this rest day (check-in after 17:00, check-out next morning before 10:00)
                    $nightCheckIn = null;
                    $nightCheckOut = null;
                    
                    foreach ($availableLogs as $tsStr) {
                        if (substr($tsStr, 0, 10) === $dateStr) {
                            $timePart = substr($tsStr, 11, 8);
                            if ($timePart >= '17:00:00' && $timePart <= '23:59:59') {
                                if (!$nightCheckIn || $tsStr < $nightCheckIn) {
                                    $nightCheckIn = $tsStr;
                                }
                            }
                        }
                    }
                    
                    if ($nightCheckIn) {
                        $nextDateStr = $currentDate->copy()->addDay()->format('Y-m-d');
                        foreach ($availableLogs as $tsStr) {
                            if (substr($tsStr, 0, 10) === $nextDateStr) {
                                $timePart = substr($tsStr, 11, 8);
                                if ($timePart >= '04:00:00' && $timePart <= '10:00:00') {
                                    if (!$nightCheckOut || $tsStr < $nightCheckOut) {
                                        $nightCheckOut = $tsStr;
                                    }
                                }
                            }
                        }
                    }
                    
                    if ($nightCheckIn) {
                        $checkInLog = $nightCheckIn;
                        $checkOutLog = $nightCheckOut;
                    } else {
                        // 2. Normal day shift on rest day (all scans on this day)
                        $dayScans = [];
                        foreach ($availableLogs as $tsStr) {
                            if (substr($tsStr, 0, 10) === $dateStr) {
                                $dayScans[] = $tsStr;
                            }
                        }
                        if (count($dayScans) > 0) {
                            sort($dayScans);
                            $checkInLog = $dayScans[0];
                            if (count($dayScans) > 1) {
                                $checkOutLog = $dayScans[count($dayScans) - 1];
                            }
                        }
                    }
                }

                if ($checkInLog) {
                    $consumedLogs[] = $checkInLog;
                }
                if ($checkOutLog && $checkOutLog !== $checkInLog) {
                    $consumedLogs[] = $checkOutLog;
                }

                $record = [
                    'employee_name' => $emp['name'] ?? '',
                    'employee_nip' => $emp['nuptk_nip_nik'] ?? '',
                    'zkteco_uid' => $uid,
                    'unit_name' => $emp['unit_name'] ?? 'Staf Yayasan',
                    'position' => $emp['position'] ?? $emp['subject_position'] ?? 'Staf',
                    'date' => $currentDate->copy(),
                    'date_formatted' => $currentDate->translatedFormat('l, d M Y'),
                    'shift_name' => $shiftName,
                    'shift_start' => $shiftStartTime ? substr($shiftStartTime, 0, 5) : null,
                    'shift_end' => $shiftEndTime ? substr($shiftEndTime, 0, 5) : null,
                    'check_in' => $checkInLog ? substr($checkInLog, 11, 5) : null,
                    'check_out' => $checkOutLog ? (substr($checkOutLog, 11, 5) . (substr($checkOutLog, 0, 10) !== $dateStr ? ' (Besok)' : '')) : null,
                    'status' => 'Off',
                    'late_minutes' => 0,
                    'notes' => '',
                    'leave_status' => $isOnLeave ? $leaveApprovalStatus : null,
                ];

                // Status determination
                if ($isHoliday) {
                    if ($checkInLog) {
                        $record['status'] = 'Tepat waktu';
                        $record['notes'] = 'Masuk di Hari Libur';
                    } else {
                        $record['status'] = 'Libur';
                        $record['notes'] = 'Libur Nasional / Yayasan';
                    }
                } elseif ($activeShift) {
                    if ($hasShiftToday) {
                        if ($checkInLog || $checkOutLog) {
                            $isLate = false;
                            $isRedLate = false;
                            $lateMinutes = 0;
                            if ($checkInLog && $shiftStartTime) {
                                $checkInTime = Carbon::parse($checkInLog)->second(0);
                                $expectedInTime = Carbon::parse($dateStr . ' ' . $shiftStartTime)->second(0);
                                if ($checkInTime > $expectedInTime) {
                                    $isLate = true;
                                    $lateMinutes = (int) abs($checkInTime->diffInMinutes($expectedInTime));
                                }

                                $empPosition = strtolower($emp['position'] ?? $emp['subject_position'] ?? '');
                                $isShiftExempt = $isShiftWorker 
                                    || $matchedRosterAssignment
                                    || str_contains($empPosition, 'keamanan') 
                                    || str_contains($empPosition, 'satpam') 
                                    || str_contains($empPosition, 'security') 
                                    || str_contains($empPosition, 'mart') 
                                    || str_contains($empPosition, 'toko') 
                                    || str_contains($empPosition, 'salehmart');

                                if (!$isShiftExempt && $checkInTime > Carbon::parse($dateStr . ' 07:25:00')->second(0)) {
                                    if (!($isOnLeave && $leaveApprovalStatus === 'Approved')) {
                                        $isRedLate = true;
                                    }
                                }
                            }
                            $record['late_minutes'] = $lateMinutes;
                            if ($isRedLate) {
                                $record['status'] = 'Mengkhawatirkan';
                            } elseif ($isLate) {
                                $record['status'] = 'Terlambat';
                            } else {
                                $record['status'] = 'Tepat waktu';
                            }
                            if ($isOnLeave) {
                                $record['notes'] = $leaveReason;
                            }
                        } elseif ($isOnLeave) {
                            if ($statusCode === 'S') {
                                $record['status'] = 'Sakit';
                            } elseif ($statusCode === 'C') {
                                $record['status'] = 'Cuti';
                            } elseif ($statusCode === 'H' || $getsBonus) {
                                $record['status'] = 'Dinas';
                            } else {
                                $record['status'] = 'Izin';
                            }
                            $record['notes'] = $leaveReason;
                        } else {
                            $now = Carbon::now('Asia/Jakarta');
                            $shiftStartDateTime = Carbon::parse($dateStr . ' ' . $shiftStartTime, 'Asia/Jakarta');
                            if ($now->lessThan($shiftStartDateTime)) {
                                $record['status'] = 'Pending';
                            } else {
                                $record['status'] = 'Alfa';
                            }
                        }
                    } elseif ($isOffShift) {
                        if ($checkInLog) {
                            $record['status'] = 'Tepat waktu';
                            $record['notes'] = ($isShiftWorker || $matchedRosterAssignment) ? 'Masuk Kerja' : 'Masuk di Hari Libur Pekan';
                        } else {
                            if ($isOnLeave) {
                                if ($statusCode === 'S') {
                                    $record['status'] = 'Sakit';
                                } elseif ($statusCode === 'C') {
                                    $record['status'] = 'Cuti';
                                } elseif ($statusCode === 'H' || $getsBonus) {
                                    $record['status'] = 'Dinas';
                                } else {
                                    $record['status'] = 'Izin';
                                }
                                $record['notes'] = $leaveReason;
                            } else {
                                $record['status'] = 'Off';
                                $record['notes'] = 'Jadwal Libur';
                            }
                        }
                    } else {
                        if ($checkInLog) {
                            $record['status'] = 'Tepat waktu';
                            $record['notes'] = 'Masuk Kerja';
                        } else {
                            if ($isOnLeave) {
                                if ($statusCode === 'S') {
                                    $record['status'] = 'Sakit';
                                } elseif ($statusCode === 'C') {
                                    $record['status'] = 'Cuti';
                                } elseif ($statusCode === 'H' || $getsBonus) {
                                    $record['status'] = 'Dinas';
                                } else {
                                    $record['status'] = 'Izin';
                                }
                                $record['notes'] = $leaveReason;
                            } elseif ($dayOfWeek == 0) {
                                $record['status'] = 'Libur';
                                $record['notes'] = 'Hari Minggu (Non-Shift)';
                            } else {
                                $record['status'] = 'Off';
                            }
                        }
                    }
                } elseif ($isOffShift) {
                    if ($checkInLog) {
                        $record['status'] = 'Tepat waktu';
                        $record['notes'] = ($isShiftWorker || $matchedRosterAssignment) ? 'Masuk Kerja' : 'Masuk di Hari Libur Pekan';
                    } else {
                        if ($isOnLeave) {
                            if ($statusCode === 'S') {
                                $record['status'] = 'Sakit';
                            } elseif ($statusCode === 'C') {
                                $record['status'] = 'Cuti';
                            } elseif ($statusCode === 'H' || $getsBonus) {
                                $record['status'] = 'Dinas';
                            } else {
                                $record['status'] = 'Izin';
                            }
                            $record['notes'] = $leaveReason;
                        } else {
                            $record['status'] = 'Off';
                            $record['notes'] = 'Jadwal Libur';
                        }
                    }
                } else {
                    // No shift/schedule assigned at all
                    if ($checkInLog) {
                        $record['status'] = 'Tepat waktu';
                        $record['notes'] = 'Masuk Kerja (Tanpa Jadwal)';
                    } else {
                        if ($isOnLeave) {
                            if ($statusCode === 'S') {
                                $record['status'] = 'Sakit';
                            } elseif ($statusCode === 'C') {
                                $record['status'] = 'Cuti';
                            } elseif ($statusCode === 'H' || $getsBonus) {
                                $record['status'] = 'Dinas';
                            } else {
                                $record['status'] = 'Izin';
                            }
                            $record['notes'] = $leaveReason;
                        } else {
                            $record['status'] = '-';
                            $record['notes'] = '-';
                        }
                    }
                }

                // Override if leave is approved/pending on working days
                if ($isOnLeave && ($record['status'] === 'Alfa' || $record['status'] === 'Pending')) {
                    if ($statusCode === 'S') {
                        $record['status'] = 'Sakit';
                    } elseif ($statusCode === 'C') {
                        $record['status'] = 'Cuti';
                    } elseif ($statusCode === 'H' || $getsBonus) {
                        $record['status'] = 'Dinas';
                    } else {
                        $record['status'] = 'Izin';
                    }
                    $record['notes'] = $leaveReason;
                }

                // Skip empty Libur/Off/- records if there is no actual attendance log and not on leave
                if (in_array($record['status'], ['Libur', 'Off', '-']) && !$checkInLog && !$checkOutLog && !$isOnLeave) {
                    $currentDate->addDay();
                    continue;
                }

                $historyList[] = $record;
                $currentDate->addDay();
            }
        }

        // Sort descending date, then ascending unit name, then ascending name
        usort($historyList, function($a, $b) {
            $dateA = $a['date']->format('Y-m-d');
            $dateB = $b['date']->format('Y-m-d');
            if ($dateA !== $dateB) {
                return strcmp($dateB, $dateA);
            }
            $unitCompare = strcmp($a['unit_name'] ?? '', $b['unit_name'] ?? '');
            if ($unitCompare !== 0) {
                return $unitCompare;
            }
            return strcmp($a['employee_name'], $b['employee_name']);
        });

        // Filter status
        if (!empty($selectedStatus)) {
            $historyList = array_filter($historyList, function ($item) use ($selectedStatus) {
                if (strtolower($selectedStatus) === 'tepat waktu' || strtolower($selectedStatus) === 'hadir') {
                    return in_array(strtolower($item['status']), ['tepat waktu', 'hadir']);
                }
                return strtolower($item['status']) === strtolower($selectedStatus);
            });
        }

        \Illuminate\Support\Facades\Log::info("HISTORY LIST COUNT FOR EXPORT: " . count($historyList));
        $format = $request->query('format', 'excel');
        $periodeStr = $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y');

        if ($format === 'pdf') {
            return redirect()->back()->with('error', 'Ekspor PDF untuk menu Riwayat Kehadiran dinonaktifkan sementara demi stabilitas server. Silakan unduh laporan menggunakan format Excel (.xlsx) yang jauh lebih cepat dan andal.');
        }

        // Generate Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Riwayat Kehadiran');

        // Headers (without NIP/NUPTK column)
        $headers = [
            'A1' => 'NO',
            'B1' => 'NAMA PEGAWAI',
            'C1' => 'UNIT',
            'D1' => 'JABATAN',
            'E1' => 'HARI & TANGGAL',
            'F1' => 'SHIFT KERJA',
            'G1' => 'JAM MASUK SHIFT',
            'H1' => 'JAM KELUAR SHIFT',
            'I1' => 'JAM MASUK AKTUAL',
            'J1' => 'JAM KELUAR AKTUAL',
            'K1' => 'STATUS',
            'L1' => 'KETERLAMBATAN (MENIT)',
            'M1' => 'KETERANGAN / CATATAN',
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        // Styling headers
        $headerRange = 'A1:M1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('1E293B');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(26);
        
        $rowIdx = 2;
        $no = 1;
        foreach ($historyList as $item) {
            $sheet->setCellValue('A' . $rowIdx, $no++);
            $sheet->setCellValue('B' . $rowIdx, $item['employee_name']);
            $sheet->setCellValue('C' . $rowIdx, $item['unit_name']);
            $sheet->setCellValue('D' . $rowIdx, $item['position']);
            $sheet->setCellValue('E' . $rowIdx, $item['date_formatted']);
            $sheet->setCellValue('F' . $rowIdx, $item['shift_name']);
            $sheet->setCellValue('G' . $rowIdx, $item['shift_start'] ?? '-');
            $sheet->setCellValue('H' . $rowIdx, $item['shift_end'] ?? '-');
            $sheet->setCellValue('I' . $rowIdx, $item['check_in'] ?? '-');
            $sheet->setCellValue('J' . $rowIdx, $item['check_out'] ?? '-');
            
            // Status text determination
            $statusText = $item['status'] === 'Libur' ? 'OFF' : $item['status'];
            if (!empty($item['leave_status']) && in_array($item['status'], ['Izin', 'Sakit', 'Cuti', 'Dinas', 'Terlambat', 'Mengkhawatirkan', 'Tepat waktu'])) {
                $statusText .= ' (' . ($item['leave_status'] === 'Approved' ? 'Disetujui' : 'Pending') . ')';
            }
            $sheet->setCellValue('K' . $rowIdx, $statusText);
            $sheet->setCellValue('L' . $rowIdx, $item['late_minutes'] > 0 ? $item['late_minutes'] : 0);
            $sheet->setCellValue('M' . $rowIdx, $item['notes'] ?? '');
            
            // Row styling
            $sheet->getRowDimension($rowIdx)->setRowHeight(20);
            $isApprovedLate = ($item['status'] === 'Terlambat' && !empty($item['leave_status']) && $item['leave_status'] === 'Approved');

            // Jam Masuk Color
            if ($item['check_in']) {
                $inColor = match($item['status']) {
                    'Tepat waktu', 'Hadir' => '059669',
                    'Terlambat' => $isApprovedLate ? '0284C7' : 'D97706',
                    'Mengkhawatirkan' => 'E11D48',
                    default => '1E293B',
                };
                $sheet->getStyle('I' . $rowIdx)->getFont()->setBold(true)->getColor()->setRGB($inColor);
            }

            // Status Cell Color & Background
            $statusStyle = match($item['status']) {
                'Tepat waktu', 'Hadir' => ['bg' => 'ECFDF5', 'color' => '047857'],
                'Terlambat' => $isApprovedLate ? ['bg' => 'F0F9FF', 'color' => '0284C7'] : ['bg' => 'FFFBEB', 'color' => 'B45309'],
                'Mengkhawatirkan' => ['bg' => 'FFF1F2', 'color' => 'BE123C'],
                'Alfa' => ['bg' => 'FFF1F2', 'color' => 'BE123C'],
                'Sakit' => ['bg' => 'FEF2F2', 'color' => 'B91C1C'],
                'Izin' => ['bg' => 'FFF7ED', 'color' => 'C2410C'],
                'Cuti' => ['bg' => 'EFF6FF', 'color' => '1D4ED8'],
                'Dinas' => ['bg' => 'EEF2FF', 'color' => '4338CA'],
                'Off' => ['bg' => 'F8FAFC', 'color' => '64748B'],
                'Libur' => ['bg' => 'F3F4F6', 'color' => '4B5563'],
                default => ['bg' => 'FFFFFF', 'color' => '1E293B'],
            };
            $sheet->getStyle('K' . $rowIdx)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB($statusStyle['bg']);
            $sheet->getStyle('K' . $rowIdx)->getFont()->setBold(true)->getColor()->setRGB($statusStyle['color']);

            // Keterlambatan Color
            if ($item['late_minutes'] > 0) {
                $lateColor = $item['status'] === 'Mengkhawatirkan' ? 'E11D48' : ($isApprovedLate ? '0284C7' : 'D97706');
                $sheet->getStyle('L' . $rowIdx)->getFont()->setBold(true)->getColor()->setRGB($lateColor);
            } else {
                $sheet->getStyle('L' . $rowIdx)->getFont()->getColor()->setRGB('94A3B8');
            }

            $rowIdx++;
        }

        if ($rowIdx > 2) {
            // Alignments
            $sheet->getStyle('A2:A' . ($rowIdx - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G2:L' . ($rowIdx - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Borders
            $tableRange = 'A1:M' . ($rowIdx - 1);
            $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');
        }

        // Auto-fit columns
        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'history_export');
        $writer->save($tempFile);

        $response = response()->download($tempFile, "Riwayat_Kehadiran_{$startDateReq}_to_{$endDateReq}.xlsx")->deleteFileAfterSend(true);
        if ($request->filled('download_token')) {
            setcookie('download_token', $request->query('download_token'), time() + 60, '/', '', false, false);
        }
        return $response;
    }
}

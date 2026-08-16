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

        // Get unique positions from raw employee data
        $rawEmployees = collect($this->service->getSdEmployees());
        $positions = $rawEmployees
            ->map(fn($emp) => $emp['position'] ?? $emp['subject_position'] ?? 'Staf')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();

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

        // 2. Fetch approved leaves
        $leavesData = LeaveRequest::whereIn('employee_id', $employeeIds)
            ->where('status', 'Approved')
            ->where(function($q) use ($startDate, $endDate) {
                $q->where('start_date', '<=', $endDate->format('Y-m-d'))
                  ->where('end_date', '>=', $startDate->format('Y-m-d'));
            })
            ->get()
            ->groupBy(function($leave) {
                return $leave->school_unit_id . '_' . $leave->employee_id;
            });

        // 3. Fetch shifts
        $shiftsData = EmployeeWorkingShift::with(['workingShift.details'])
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

        // Fetch default shift
        $defaultShift = \App\Models\WorkingShift::with('details')->where('code', 'default')->first();
        if (!$defaultShift) {
            $defaultShift = \App\Models\WorkingShift::with('details')->first();
        }

        // Build flat history list
        $historyList = [];

        foreach ($employeesCollection as $emp) {
            $uid = $emp['zkteco_uid'] ?? null;
            $empId = $emp['id'] ?? null;
            $unit = $emp['unit_id'] ?? null;

            if (!$uid || !$empId) continue;

            $userLogs = $logsData->get((string)$uid) ? $logsData->get((string)$uid)->pluck('timestamp')->sort()->values()->toArray() : [];

            $currentDate = $startDate->copy();
            $lastDay = $endDate->greaterThan(Carbon::now()) ? Carbon::now()->endOfDay() : $endDate;

            while ($currentDate <= $lastDay) {
                $dateStr = $currentDate->format('Y-m-d');
                $dayOfWeek = $currentDate->dayOfWeek;

                $isHoliday = isset($unitHolidays[$unit][$dateStr]) ?? false;
                
                $isOnLeave = false;
                $getsBonus = false;
                $statusCode = null;
                $leaveReason = '';
                $leaveKey = $unit . '_' . $empId;
                if (isset($leavesData[$leaveKey])) {
                    foreach ($leavesData[$leaveKey] as $leave) {
                        $leaveStart = substr($leave->start_date, 0, 10);
                        $leaveEnd = substr($leave->end_date, 0, 10);
                        if ($dateStr >= $leaveStart && $dateStr <= $leaveEnd) {
                            $isOnLeave = true;
                            $getsBonus = $leave->gets_presence_bonus || ($leave->status_code === 'H') || ($leave->type_name === 'Dinas');
                            $statusCode = $leave->status_code;
                            $leaveReason = $leave->notes ?? $leave->reason ?? 'Izin disetujui';
                            if (!$statusCode) {
                                if ($leave->type_name === 'Sakit') $statusCode = 'S';
                                elseif ($leave->type_name === 'Cuti') $statusCode = 'C';
                                elseif ($leave->type_name === 'Dinas') $statusCode = 'H';
                                else $statusCode = 'I';
                            }
                            break;
                        }
                    }
                }

                $hasShiftToday = false;
                $isOffShift = false;
                $shiftStartTime = null;
                $shiftEndTime = null;
                $shiftName = 'Shift Kerja';
                $shiftKey = $unit . '_' . $empId;
                $isShiftWorker = false;

                // Resolve Assigned or Default Shift
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

                $activeShift = $matchedAssignment ?: $defaultShift;

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
                }

                if ($isShiftWorker && !$hasShiftToday && !$isOffShift) {
                    $isOffShift = true;
                }

                // Check-in and Check-out Log retrieval
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
                    // Rest day (Off / Holiday / Sunday) - search all scans on this day
                    $dayScans = [];
                    foreach ($userLogs as $tsStr) {
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
                    'check_out' => $checkOutLog ? substr($checkOutLog, 11, 5) : null,
                    'status' => 'Off',
                    'late_minutes' => 0,
                    'notes' => '',
                ];

                // Status determination
                if ($isHoliday) {
                    if ($checkInLog) {
                        $record['status'] = 'Hadir';
                        $record['notes'] = 'Masuk di Hari Libur';
                    } else {
                        $record['status'] = 'Libur';
                        $record['notes'] = 'Libur Nasional / Yayasan';
                    }
                } elseif ($hasShiftToday) {
                    if ($checkInLog || $checkOutLog) {
                        $isLate = false;
                        $lateMinutes = 0;
                        if ($checkInLog && $shiftStartTime) {
                            $checkInTime = Carbon::parse($checkInLog)->second(0);
                            $expectedInTime = Carbon::parse($dateStr . ' ' . $shiftStartTime)->second(0);
                            if ($checkInTime > $expectedInTime) {
                                $isLate = true;
                                $lateMinutes = $checkInTime->diffInMinutes($expectedInTime);
                            }
                        }
                        $record['late_minutes'] = $lateMinutes;
                        $record['status'] = $isLate ? 'Terlambat' : 'Hadir';
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
                        $record['status'] = 'Hadir';
                        $record['notes'] = 'Masuk di Hari Libur Pekan';
                    } else {
                        $record['status'] = 'Off';
                        $record['notes'] = 'Jadwal Libur Pekan/Shift';
                    }
                } else {
                    if ($checkInLog) {
                        $record['status'] = 'Hadir';
                        $record['notes'] = 'Masuk Kerja';
                    } else {
                        if ($dayOfWeek == 0) {
                            $record['status'] = 'Libur';
                            $record['notes'] = 'Hari Minggu (Non-Shift)';
                        } else {
                            $record['status'] = 'Off';
                        }
                    }
                }

                // Override if leave is approved on working days
                if ($isOnLeave && $hasShiftToday && ($record['status'] === 'Alfa' || $record['status'] === 'Pending')) {
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

                $historyList[] = $record;
                $currentDate->addDay();
            }
        }

        // Sort descending date, then ascending name
        usort($historyList, function($a, $b) {
            $dateA = $a['date']->format('Y-m-d');
            $dateB = $b['date']->format('Y-m-d');
            if ($dateA !== $dateB) {
                return strcmp($dateB, $dateA);
            }
            return strcmp($a['employee_name'], $b['employee_name']);
        });

        // Filter status
        if (!empty($selectedStatus)) {
            $historyList = array_filter($historyList, function ($item) use ($selectedStatus) {
                return strtolower($item['status']) === strtolower($selectedStatus);
            });
        }

        // Paginator
        $total = count($historyList);
        $perPage = (int) $request->query('per_page', 50);
        $page = (int) $request->query('page', 1);

        $paginatedItems = array_slice($historyList, ($page - 1) * $perPage, $perPage);
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
            'selectedStatus'
        ));
    }

    public function export(Request $request)
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);

        $startDateReq = $request->query('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDateReq = $request->query('end_date', Carbon::now()->format('Y-m-d'));

        $startDate = Carbon::parse($startDateReq)->startOfDay();
        $endDate = Carbon::parse($endDateReq)->endOfDay();

        $search = $request->query('search');
        $unitId = $request->query('unit_id');
        $position = $request->query('position');
        $selectedStatus = $request->query('status');

        // Fetch employees list
        $rawEmployees = collect($this->service->getSdEmployees());
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

        // 2. Fetch approved leaves
        $leavesData = LeaveRequest::whereIn('employee_id', $employeeIds)
            ->where('status', 'Approved')
            ->where(function($q) use ($startDate, $endDate) {
                $q->where('start_date', '<=', $endDate->format('Y-m-d'))
                  ->where('end_date', '>=', $startDate->format('Y-m-d'));
            })
            ->get()
            ->groupBy(function($leave) {
                return $leave->school_unit_id . '_' . $leave->employee_id;
            });

        // 3. Fetch shifts
        $shiftsData = EmployeeWorkingShift::with(['workingShift.details'])
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

        // Fetch default shift
        $defaultShift = \App\Models\WorkingShift::with('details')->where('code', 'default')->first();
        if (!$defaultShift) {
            $defaultShift = \App\Models\WorkingShift::with('details')->first();
        }

        // Build list
        $historyList = [];

        foreach ($employeesCollection as $emp) {
            $uid = $emp['zkteco_uid'] ?? null;
            $empId = $emp['id'] ?? null;
            $unit = $emp['unit_id'] ?? null;

            if (!$uid || !$empId) continue;

            $userLogs = $logsData->get((string)$uid) ? $logsData->get((string)$uid)->pluck('timestamp')->sort()->values()->toArray() : [];

            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                $dateStr = $currentDate->format('Y-m-d');
                $dayOfWeek = $currentDate->dayOfWeek;

                $isHoliday = isset($unitHolidays[$unit][$dateStr]) ?? false;
                
                $isOnLeave = false;
                $getsBonus = false;
                $statusCode = null;
                $leaveReason = '';
                $leaveKey = $unit . '_' . $empId;
                if (isset($leavesData[$leaveKey])) {
                    foreach ($leavesData[$leaveKey] as $leave) {
                        $leaveStart = substr($leave->start_date, 0, 10);
                        $leaveEnd = substr($leave->end_date, 0, 10);
                        if ($dateStr >= $leaveStart && $dateStr <= $leaveEnd) {
                            $isOnLeave = true;
                            $getsBonus = $leave->gets_presence_bonus || ($leave->status_code === 'H') || ($leave->type_name === 'Dinas');
                            $statusCode = $leave->status_code;
                            $leaveReason = $leave->notes ?? $leave->reason ?? 'Izin disetujui';
                            if (!$statusCode) {
                                if ($leave->type_name === 'Sakit') $statusCode = 'S';
                                elseif ($leave->type_name === 'Cuti') $statusCode = 'C';
                                elseif ($leave->type_name === 'Dinas') $statusCode = 'H';
                                else $statusCode = 'I';
                            }
                            break;
                        }
                    }
                }

                $hasShiftToday = false;
                $isOffShift = false;
                $shiftStartTime = null;
                $shiftEndTime = null;
                $shiftName = 'Shift Kerja';
                $shiftKey = $unit . '_' . $empId;
                $isShiftWorker = false;

                // Resolve Assigned or Default Shift
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

                $activeShift = $matchedAssignment ?: $defaultShift;

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
                }

                if ($isShiftWorker && !$hasShiftToday && !$isOffShift) {
                    $isOffShift = true;
                }

                // Check-in and Check-out Log retrieval
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
                    // Rest day (Off / Holiday / Sunday) - search all scans on this day
                    $dayScans = [];
                    foreach ($userLogs as $tsStr) {
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
                    'check_out' => $checkOutLog ? substr($checkOutLog, 11, 5) : null,
                    'status' => 'Off',
                    'late_minutes' => 0,
                    'notes' => '',
                ];

                // Status determination
                if ($isHoliday) {
                    if ($checkInLog) {
                        $record['status'] = 'Hadir';
                        $record['notes'] = 'Masuk di Hari Libur';
                    } else {
                        $record['status'] = 'Libur';
                        $record['notes'] = 'Libur Nasional / Yayasan';
                    }
                } elseif ($hasShiftToday) {
                    if ($checkInLog || $checkOutLog) {
                        $isLate = false;
                        $lateMinutes = 0;
                        if ($checkInLog && $shiftStartTime) {
                            $checkInTime = Carbon::parse($checkInLog)->second(0);
                            $expectedInTime = Carbon::parse($dateStr . ' ' . $shiftStartTime)->second(0);
                            if ($checkInTime > $expectedInTime) {
                                $isLate = true;
                                $lateMinutes = $checkInTime->diffInMinutes($expectedInTime);
                            }
                        }
                        $record['late_minutes'] = $lateMinutes;
                        $record['status'] = $isLate ? 'Terlambat' : 'Hadir';
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
                        $record['status'] = 'Hadir';
                        $record['notes'] = 'Masuk di Hari Libur Pekan';
                    } else {
                        $record['status'] = 'Off';
                        $record['notes'] = 'Jadwal Libur Pekan/Shift';
                    }
                } else {
                    if ($checkInLog) {
                        $record['status'] = 'Hadir';
                        $record['notes'] = 'Masuk Kerja';
                    } else {
                        if ($dayOfWeek == 0) {
                            $record['status'] = 'Libur';
                            $record['notes'] = 'Hari Minggu (Non-Shift)';
                        } else {
                            $record['status'] = 'Off';
                        }
                    }
                }

                // Override if leave is approved on working days
                if ($isOnLeave && $hasShiftToday && ($record['status'] === 'Alfa' || $record['status'] === 'Pending')) {
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

                $historyList[] = $record;
                $currentDate->addDay();
            }
        }

        // Sort descending date, then ascending name
        usort($historyList, function($a, $b) {
            $dateA = $a['date']->format('Y-m-d');
            $dateB = $b['date']->format('Y-m-d');
            if ($dateA !== $dateB) {
                return strcmp($dateB, $dateA);
            }
            return strcmp($a['employee_name'], $b['employee_name']);
        });

        // Filter status
        if (!empty($selectedStatus)) {
            $historyList = array_filter($historyList, function ($item) use ($selectedStatus) {
                return strtolower($item['status']) === strtolower($selectedStatus);
            });
        }

        $format = $request->query('format', 'excel');
        $periodeStr = $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y');

        if ($format === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('attendance-history.export-pdf', compact('historyList', 'periodeStr'))
                ->setPaper('a4', 'portrait');
            $response = $pdf->download("Riwayat_Kehadiran_{$startDateReq}_to_{$endDateReq}.pdf");
            if ($request->filled('download_token')) {
                $response->headers->setCookie(cookie('download_token', $request->query('download_token'), 1, '/', null, false, false));
            }
            return $response;
        }

        // Generate Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Riwayat Kehadiran');

        // Headers
        $sheet->setCellValue('A1', 'NO');
        $sheet->setCellValue('B1', 'NAMA PEGAWAI');
        $sheet->setCellValue('C1', 'NIP/NUPTK');
        $sheet->setCellValue('D1', 'UNIT');
        $sheet->setCellValue('E1', 'JABATAN');
        $sheet->setCellValue('F1', 'HARI & TANGGAL');
        $sheet->setCellValue('G1', 'SHIFT KERJA');
        $sheet->setCellValue('H1', 'JAM MASUK SHIFT');
        $sheet->setCellValue('I1', 'JAM KELUAR SHIFT');
        $sheet->setCellValue('J1', 'JAM MASUK AKTUAL');
        $sheet->setCellValue('K1', 'JAM KELUAR AKTUAL');
        $sheet->setCellValue('L1', 'STATUS');
        $sheet->setCellValue('M1', 'KETERLAMBATAN (MENIT)');
        $sheet->setCellValue('N1', 'KETERANGAN / CATATAN');

        // Styling headers
        $headerRange = 'A1:N1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        $rowIdx = 2;
        $no = 1;
        foreach ($historyList as $item) {
            $sheet->setCellValue('A' . $rowIdx, $no++);
            $sheet->setCellValue('B' . $rowIdx, $item['employee_name']);
            $sheet->setCellValue('C' . $rowIdx, $item['employee_nip'] . ' ');
            $sheet->setCellValue('D' . $rowIdx, $item['unit_name']);
            $sheet->setCellValue('E' . $rowIdx, $item['position']);
            $sheet->setCellValue('F' . $rowIdx, $item['date_formatted']);
            $sheet->setCellValue('G' . $rowIdx, $item['shift_name']);
            $sheet->setCellValue('H' . $rowIdx, $item['shift_start'] ?? '-');
            $sheet->setCellValue('I' . $rowIdx, $item['shift_end'] ?? '-');
            $sheet->setCellValue('J' . $rowIdx, $item['check_in'] ?? '-');
            $sheet->setCellValue('K' . $rowIdx, $item['check_out'] ?? '-');
            $sheet->setCellValue('L' . $rowIdx, $item['status']);
            $sheet->setCellValue('M' . $rowIdx, $item['late_minutes'] > 0 ? $item['late_minutes'] : 0);
            $sheet->setCellValue('N' . $rowIdx, $item['notes'] ?? '');
            
            $rowIdx++;
        }

        // Auto-fit columns
        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'history_export');
        $writer->save($tempFile);

        $response = response()->download($tempFile, "Riwayat_Kehadiran_{$startDateReq}_to_{$endDateReq}.xlsx")->deleteFileAfterSend(true);
        if ($request->filled('download_token')) {
            $response->headers->setCookie(cookie('download_token', $request->query('download_token'), 1, '/', null, false, false));
        }
        return $response;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\EmployeeWorkingShift;
use App\Models\Setting;
use App\Models\SchoolUnit;
use App\Services\SchoolUnitService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendancePercentageReportController extends Controller
{
    protected SchoolUnitService $service;

    public function __construct(SchoolUnitService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $unitId = $request->query('unit_id');
        $startDateReq = $request->query('start_date');
        $endDateReq = $request->query('end_date');
        $month = $request->query('month');

        // Fetch cutoff date from settings, default to 26
        $cutoffDate = (int) Setting::get('payroll_cutoff_date', 26);

        if (!empty($startDateReq) && !empty($endDateReq)) {
            $startDate = Carbon::parse($startDateReq)->startOfDay();
            $endDate = Carbon::parse($endDateReq)->endOfDay();
        } else {
            if (empty($month)) {
                $today = now();
                $month = $today->day > $cutoffDate ? $today->copy()->startOfMonth()->addMonth()->format('Y-m') : $today->format('Y-m');
            }
            $monthCarbon = Carbon::parse($month . '-01');
            $endDate = $monthCarbon->copy()->setDay($cutoffDate)->endOfDay();
            $startDate = $monthCarbon->copy()->subMonth()->setDay($cutoffDate + 1)->startOfDay();

            $startDateReq = $startDate->format('Y-m-d');
            $endDateReq = $endDate->format('Y-m-d');
        }

        // Fetch all active employees (excluding Tukang from attendance percentage reports)
        $rawEmployees = $this->service->getAllEmployees();
        $allEmployeesCollection = collect($rawEmployees)
            ->filter(function ($emp) {
                $pos = strtolower(trim($emp['position'] ?? $emp['subject_position'] ?? ''));
                return $pos !== 'tukang' && !str_contains($pos, 'tukang');
            })
            ->sort(function ($a, $b) {
                $unitCompare = strcmp($a['unit_name'] ?? '', $b['unit_name'] ?? '');
                if ($unitCompare !== 0) {
                    return $unitCompare;
                }
                return strcmp($a['name'] ?? '', $b['name'] ?? '');
            })
            ->values();

        $uids = $allEmployeesCollection->pluck('zkteco_uid')->filter()->toArray();
        $employeeIds = $allEmployeesCollection->pluck('id')->filter()->toArray();

        // Pre-fetch Attendance Logs for this month
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
                $assignment->formatted_start = $assignment->start_date instanceof \Carbon\Carbon ? $assignment->start_date->format('Y-m-d') : substr($assignment->start_date, 0, 10);
                $assignment->formatted_end = $assignment->end_date ? ($assignment->end_date instanceof \Carbon\Carbon ? $assignment->end_date->format('Y-m-d') : substr($assignment->end_date, 0, 10)) : null;
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
                        return $scoreB <=> $scoreA; // Descending
                    }
                    $dateA = $a->formatted_start;
                    $dateB = $b->formatted_start;
                    if ($dateA !== $dateB) {
                        return strcmp($dateB, $dateA); // Descending
                    }
                    return $b->id <=> $a->id; // Descending
                });
            });

        // Pre-fetch Holidays and Adjustments
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
            ->map(function ($leave) {
                $leave->formatted_start = $leave->start_date instanceof \Carbon\Carbon ? $leave->start_date->format('Y-m-d') : substr($leave->start_date, 0, 10);
                $leave->formatted_end = $leave->end_date instanceof \Carbon\Carbon ? $leave->end_date->format('Y-m-d') : substr($leave->end_date, 0, 10);
                return $leave;
            })
            ->groupBy(function($leave) {
                return $leave->school_unit_id . '_' . $leave->employee_id;
            });

        \Illuminate\Support\Facades\Log::info("DEBUG PERCENTAGE REPORT: " . json_encode([
            'employee_ids' => $employeeIds,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'leaves_keys' => array_keys($leaves->toArray()),
        ]));

        // Calculate Report
        $allReports = [];

        foreach ($allEmployeesCollection as $emp) {
            $uid = $emp['zkteco_uid'] ?? null;
            $empId = $emp['id'] ?? null;
            $unit = $emp['unit_id'] ?? null;

            if (!$uid || !$empId) continue;

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

            $totalWorkDays = 0;
            $totalPresent = 0; // Physical scan OR gets_presence_bonus leaves
            $actualScanCount = 0; // Physical scan tap
            $dinasCount = 0; // Dinas/Tugas/Excused presence
            $totalSakit = 0;
            $totalIzin = 0;
            $totalCuti = 0;
            $totalAbsent = 0;
            $totalLateMinutes = 0;
            $lateCount = 0;

            $sakitDates = [];
            $izinDates = [];
            $cutiDates = [];
            $absentDates = [];
            $scanDates = [];
            $dinasDates = [];
            $lateDates = [];
            $dayDetails = [];

            // Loop through each day of the month
            $lastDay = $endDate->greaterThan(Carbon::now()) ? Carbon::now()->endOfDay() : $endDate;
            $currentDate = $startDate->copy();
            while ($currentDate <= $lastDay) {
                $dateStr = $currentDate->format('Y-m-d');
                $formattedDate = $currentDate->translatedFormat('l, d M Y'); // e.g. Senin, 10 Agt 2026
                $dayOfWeek = $currentDate->dayOfWeek; // 1 (Mon) - 7 (Sun)

                // Skip Holidays based on employee's unit
                $empUnitKey = ($unit && isset($unitHolidays[$unit])) ? $unit : '';
                $isHoliday = ($unitHolidays[$empUnitKey][$dateStr] ?? false) && !$isShiftWorker;
                if ($isHoliday) {
                    $dayDetails[] = [
                        'date' => $formattedDate,
                        'status' => 'Libur',
                        'label' => 'Hari Libur Resmi',
                        'detail' => 'Libur Nasional / Yayasan',
                        'color' => 'slate'
                    ];
                    $currentDate->addDay();
                    continue;
                }

                // Check Shift Assignment
                $activeShift = null;
                $shiftKey = $unit . '_' . $empId;
                $shiftStartTime = null;

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
                $shiftEndTime = null;

                if ($activeShift && $activeShift->details) {
                    $dayOfWeek = $currentDate->dayOfWeek; // 0 (Sun) to 6 (Sat)
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

                // If shift worker and has no assigned shift on this date -> day off (roster off)
                if ($isShiftWorker && !$activeShift) {
                    $isDayOff = true;
                }

                // If not shift worker and it is holiday -> day off
                if (!$isShiftWorker && $isHoliday) {
                    $isDayOff = true;
                }

                $shiftScheduleText = $shiftStartTime ? substr($shiftStartTime, 0, 5) . ($shiftEndTime ? ' - ' . substr($shiftEndTime, 0, 5) : '') : null;

                $logKey = $uid . '_' . $dateStr;
                $hasScan = isset($attendanceLogs[$logKey]);

                // Check approved leaves for this date
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

                        // Calculate late minutes if shift start time is set
                        $dailyLateMinutes = 0;
                        if ($shiftStartTime) {
                            $expectedStart = Carbon::parse($dateStr . ' ' . $shiftStartTime);
                            if ($firstLogCarbon->copy()->second(0) > $expectedStart->copy()->second(0)) {
                                $dailyLateMinutes = (int) $expectedStart->diffInMinutes($firstLogCarbon);
                                $totalLateMinutes += $dailyLateMinutes;
                                $lateCount++;
                                $lateDates[] = $currentDate->translatedFormat('d M');
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
                            $scanDetail .= " • Terlambat: {$dailyLateMinutes} mnt";
                        }

                        $dayDetails[] = [
                            'date' => $formattedDate,
                            'status' => 'Hadir',
                            'label' => 'Hadir',
                            'detail' => $scanDetail,
                            'shift_name' => $shiftName,
                            'shift_schedule' => $shiftScheduleText,
                            'in_time' => $inTime,
                            'out_time' => $outTime,
                            'late_minutes' => $dailyLateMinutes,
                            'notes' => null,
                            'color' => 'emerald'
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
                                'status' => 'Sakit',
                                'label' => 'Sakit',
                                'detail' => $leaveReason,
                                'shift_name' => $shiftName,
                                'shift_schedule' => $shiftScheduleText,
                                'in_time' => null,
                                'out_time' => null,
                                'late_minutes' => 0,
                                'notes' => $leaveReason ?: 'Surat Dokter / Izin Sakit',
                                'color' => 'amber'
                            ];
                        } elseif ($statusCode === 'C') {
                            $totalCuti++;
                            $cutiDates[] = $currentDate->translatedFormat('d M');
                            $dayDetails[] = [
                                'date' => $formattedDate,
                                'status' => 'Cuti',
                                'label' => 'Cuti',
                                'detail' => $leaveReason,
                                'shift_name' => $shiftName,
                                'shift_schedule' => $shiftScheduleText,
                                'in_time' => null,
                                'out_time' => null,
                                'late_minutes' => 0,
                                'notes' => $leaveReason ?: 'Cuti Tahunan / Resmi',
                                'color' => 'blue'
                            ];
                        } elseif ($statusCode === 'H' || $getsBonus) {
                            $totalPresent++;
                            $dinasCount++;
                            $dinasDates[] = $currentDate->translatedFormat('d M');
                            $dayDetails[] = [
                                'date' => $formattedDate,
                                'status' => 'Dinas',
                                'label' => 'Dinas',
                                'detail' => $leaveReason,
                                'shift_name' => $shiftName,
                                'shift_schedule' => $shiftScheduleText,
                                'in_time' => null,
                                'out_time' => null,
                                'late_minutes' => 0,
                                'notes' => $leaveReason ?: 'Dinas / Tugas Luar',
                                'color' => 'indigo'
                            ];
                        } else {
                            $totalIzin++;
                            $izinDates[] = $currentDate->translatedFormat('d M');
                            $dayDetails[] = [
                                'date' => $formattedDate,
                                'status' => 'Izin',
                                'label' => 'Izin',
                                'detail' => $leaveReason,
                                'shift_name' => $shiftName,
                                'shift_schedule' => $shiftScheduleText,
                                'in_time' => null,
                                'out_time' => null,
                                'late_minutes' => 0,
                                'notes' => $leaveReason ?: 'Izin Terverifikasi',
                                'color' => 'amber'
                            ];
                        }
                    } else {
                        $totalAbsent++;
                        $absentDates[] = $currentDate->translatedFormat('d M');
                        $dayDetails[] = [
                            'date' => $formattedDate,
                            'status' => 'Alpa',
                            'label' => 'Alpa',
                            'detail' => "Tidak ada scan pada jadwal $shiftName" . ($shiftScheduleText ? " ($shiftScheduleText)" : ''),
                            'shift_name' => $shiftName,
                            'shift_schedule' => $shiftScheduleText,
                            'in_time' => null,
                            'out_time' => null,
                            'late_minutes' => 0,
                            'notes' => 'Tidak ada rekaman absensi',
                            'color' => 'rose'
                        ];
                    }
                } else {
                    $dayDetails[] = [
                        'date' => $formattedDate,
                        'status' => 'Libur',
                        'label' => 'Libur',
                        'detail' => $isHoliday ? 'Hari Libur Resmi' : "Jadwal Libur ($shiftName)",
                        'shift_name' => $shiftName,
                        'shift_schedule' => $shiftScheduleText,
                        'in_time' => null,
                        'out_time' => null,
                        'late_minutes' => 0,
                        'notes' => $isHoliday ? 'Hari Libur Resmi' : 'Jadwal Libur',
                        'color' => 'slate'
                    ];
                }

                $currentDate->addDay();
            }

            // Formula: Hadir / (Hadir + Alpa) * 100%
            $totalActiveWorkDays = $totalPresent + $totalAbsent;
            $percentage = $totalActiveWorkDays > 0 ? round(($totalPresent / $totalActiveWorkDays) * 100, 1) : 100;

            $allReports[] = [
                'employee' => $emp,
                'total_work_days' => $totalWorkDays,
                'total_present' => $totalPresent,
                'actual_scan_count' => $actualScanCount,
                'scan_dates' => $scanDates,
                'dinas_count' => $dinasCount,
                'dinas_dates' => $dinasDates,
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
                'percentage' => $percentage,
                'day_details' => $dayDetails,
            ];
        }

        $schoolUnits = SchoolUnit::where('is_active', true)->get();

        // 1. Calculate average percentages for 5 categories (PAUD, SD Reguler, SMP Reguler, GPK, GPQ)
        $unitStats = [
            'paud' => [
                'name' => 'PAUD',
                'count' => 0,
                'total_present' => 0,
                'total_absent' => 0,
                'average' => 0
            ],
            'sd' => [
                'name' => 'SD (Reguler)',
                'count' => 0,
                'total_present' => 0,
                'total_absent' => 0,
                'average' => 0
            ],
            'smp' => [
                'name' => 'SMP (Reguler)',
                'count' => 0,
                'total_present' => 0,
                'total_absent' => 0,
                'average' => 0
            ],
            'gpk' => [
                'name' => 'GPK (SD-SMP)',
                'count' => 0,
                'total_present' => 0,
                'total_absent' => 0,
                'average' => 0
            ],
            'gpq' => [
                'name' => 'GPQ (SD-SMP)',
                'count' => 0,
                'total_present' => 0,
                'total_absent' => 0,
                'average' => 0
            ],
        ];

        foreach ($allReports as $rep) {
            $pos = strtoupper(trim($rep['employee']['position'] ?? $rep['employee']['subject_position'] ?? ''));
            $uName = strtolower(trim($rep['employee']['unit_name'] ?? ''));

            if ($pos === 'GPK' || str_contains($pos, 'GPK')) {
                $category = 'gpk';
            } elseif ($pos === 'GPQ' || str_contains($pos, 'GPQ')) {
                $category = 'gpq';
            } elseif (str_contains($uName, 'paud')) {
                $category = 'paud';
            } elseif (str_contains($uName, 'smp')) {
                $category = 'smp';
            } else {
                $category = 'sd';
            }

            if (isset($unitStats[$category])) {
                $unitStats[$category]['count']++;
                $unitStats[$category]['total_present'] += $rep['total_present'];
                $unitStats[$category]['total_absent'] += $rep['total_absent'];
            }
        }

        foreach ($unitStats as $k => &$v) {
            $totalActive = $v['total_present'] + $v['total_absent'];
            $v['average'] = $totalActive > 0 ? round(($v['total_present'] / $totalActive) * 100, 1) : 0;
        }
        unset($v);

        // 2. Filter reports by Unit / Category
        $filteredCollection = collect($allReports);

        if ($unitId === 'gpk') {
            $filteredCollection = $filteredCollection->filter(function ($r) {
                $pos = strtoupper(trim($r['employee']['position'] ?? $r['employee']['subject_position'] ?? ''));
                return $pos === 'GPK' || str_contains($pos, 'GPK');
            });
        } elseif ($unitId === 'gpq') {
            $filteredCollection = $filteredCollection->filter(function ($r) {
                $pos = strtoupper(trim($r['employee']['position'] ?? $r['employee']['subject_position'] ?? ''));
                return $pos === 'GPQ' || str_contains($pos, 'GPQ');
            });
        } elseif (!empty($unitId)) {
            $filteredCollection = $filteredCollection->filter(function ($r) use ($unitId) {
                $pos = strtoupper(trim($r['employee']['position'] ?? $r['employee']['subject_position'] ?? ''));
                $isGpkGpq = in_array($pos, ['GPK', 'GPQ']) || str_contains($pos, 'GPK') || str_contains($pos, 'GPQ');
                return ($r['employee']['unit_id'] ?? '') == $unitId && !$isGpkGpq;
            });
        }

        // 3. Extract unique positions from current unit/category scope
        $positions = $filteredCollection
            ->map(fn($r) => $r['employee']['position'] ?? $r['employee']['subject_position'] ?? 'Staf')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        // 4. Multi-select positions filter
        $selectedPositions = $request->query('positions');
        if (empty($selectedPositions) && $request->filled('position')) {
            $selectedPositions = [$request->query('position')];
        }
        if (is_string($selectedPositions)) {
            $selectedPositions = explode(',', $selectedPositions);
        }
        $selectedPositions = array_values(array_filter((array) $selectedPositions));

        if (!empty($selectedPositions)) {
            $filteredCollection = $filteredCollection->filter(function ($r) use ($selectedPositions) {
                $pos = $r['employee']['position'] ?? $r['employee']['subject_position'] ?? 'Staf';
                return in_array($pos, $selectedPositions);
            });
        }

        // 5. Search filter
        $search = $request->query('search');
        if (!empty($search)) {
            $filteredCollection = $filteredCollection->filter(function ($r) use ($search) {
                $emp = $r['employee'];
                return str_contains(strtolower($emp['name'] ?? ''), strtolower($search))
                    || str_contains(strtolower($emp['nuptk_nip_nik'] ?? ''), strtolower($search));
            });
        }

        $reports = $filteredCollection->values()->toArray();

        return view('attendance-percentage-reports.index', compact(
            'reports',
            'schoolUnits',
            'month',
            'unitId',
            'startDateReq',
            'endDateReq',
            'unitStats',
            'positions',
            'selectedPositions'
        ));
    }
}

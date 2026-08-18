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
                $month = date('Y-m');
            }
            $monthCarbon = Carbon::createFromFormat('Y-m', $month);
            $endDate = $monthCarbon->copy()->setDay($cutoffDate)->endOfDay();
            $startDate = $monthCarbon->copy()->subMonth()->setDay($cutoffDate + 1)->startOfDay();

            $startDateReq = $startDate->format('Y-m-d');
            $endDateReq = $endDate->format('Y-m-d');
        }

        // Fetch Employees (Filter by unit if needed)
        $rawEmployees = $this->service->getAllEmployees();
        $employeesCollection = collect($rawEmployees)->sort(function ($a, $b) {
            $unitCompare = strcmp($a['unit_name'] ?? '', $b['unit_name'] ?? '');
            if ($unitCompare !== 0) {
                return $unitCompare;
            }
            return strcmp($a['name'] ?? '', $b['name'] ?? '');
        })->values();

        if (!empty($unitId)) {
            $employeesCollection = $employeesCollection->filter(function ($emp) use ($unitId) {
                return ($emp['unit_id'] ?? '') == $unitId;
            });
        }

        // Extract unique positions for filter (adapts to selected unit)
        $positions = $employeesCollection
            ->map(fn($emp) => $emp['position'] ?? $emp['subject_position'] ?? 'Staf')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        // Apply position filter
        $selectedPosition = $request->query('position');
        if (!empty($selectedPosition)) {
            $employeesCollection = $employeesCollection->filter(function ($emp) use ($selectedPosition) {
                $pos = $emp['position'] ?? $emp['subject_position'] ?? 'Staf';
                return $pos === $selectedPosition;
            });
        }

        // Apply search filter
        $search = $request->query('search');
        if (!empty($search)) {
            $employeesCollection = $employeesCollection->filter(function ($emp) use ($search) {
                return str_contains(strtolower($emp['name'] ?? ''), strtolower($search))
                    || str_contains(strtolower($emp['nuptk_nip_nik'] ?? ''), strtolower($search));
            });
        }

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
        $reports = [];

        foreach ($employeesCollection as $emp) {
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

            $sakitDates = [];
            $izinDates = [];
            $cutiDates = [];
            $absentDates = [];
            $scanDates = [];
            $dinasDates = [];
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

                // Check approved leaves
                $isOnLeave = false;
                $getsBonus = false;
                $statusCode = null;
                $leaveReason = '';
                $leaveKey = $unit . '_' . $empId;
                if (isset($leaves[$leaveKey])) {
                    foreach ($leaves[$leaveKey] as $leave) {
                        $leaveStart = $leave->formatted_start;
                        $leaveEnd = $leave->formatted_end;
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

                // Check Shift Assignment
                $hasShiftToday = false;
                $shiftKey = $unit . '_' . $empId;
                $shiftName = 'Shift Kerja';

                if (isset($assignedShifts[$shiftKey])) {
                    foreach ($assignedShifts[$shiftKey] as $assignment) {
                        $assignStartDate = $assignment->formatted_start;
                        $assignEndDate = $assignment->formatted_end;
                        if ($dateStr >= $assignStartDate && (!$assignEndDate || $dateStr <= $assignEndDate)) {
                            $detail = $assignment->workingShift->details->where('day_of_week', $dayOfWeek)->first();
                            if ($detail && !$detail->is_off) {
                                $hasShiftToday = true;
                                $shiftName = $assignment->workingShift->name;
                            }
                            break;
                        }
                    }
                }

                if ($hasShiftToday) {
                    $totalWorkDays++;
                    $logKey = $uid . '_' . $dateStr;
                    $hasScan = isset($attendanceLogs[$logKey]);

                    if ($hasScan) {
                        $totalPresent++;
                        $actualScanCount++;
                        $scanDates[] = $currentDate->translatedFormat('d M');
                        
                        $firstLog = $attendanceLogs[$logKey]->sortBy('timestamp')->first();
                        $scanTime = Carbon::parse($firstLog->timestamp)->format('H:i');

                        $dayDetails[] = [
                            'date' => $formattedDate,
                            'status' => 'Hadir',
                            'label' => "Hadir (Scan: {$scanTime})",
                            'detail' => "Shift: $shiftName",
                            'color' => 'emerald'
                        ];
                    } elseif ($isOnLeave) {
                        if ($statusCode === 'S') {
                            $totalSakit++;
                            $sakitDates[] = $currentDate->translatedFormat('d M');
                            $dayDetails[] = [
                                'date' => $formattedDate,
                                'status' => 'Sakit',
                                'label' => 'Sakit',
                                'detail' => $leaveReason,
                                'color' => 'red'
                            ];
                        } elseif ($statusCode === 'C') {
                            $totalCuti++;
                            $cutiDates[] = $currentDate->translatedFormat('d M');
                            $dayDetails[] = [
                                'date' => $formattedDate,
                                'status' => 'Cuti',
                                'label' => 'Cuti Tahunan',
                                'detail' => $leaveReason,
                                'color' => 'blue'
                            ];
                        } elseif ($statusCode === 'H' || $getsBonus) {
                            $totalPresent++;
                            $dinasCount++;
                            $dinasDates[] = $currentDate->translatedFormat('d M');
                            $dayDetails[] = [
                                'date' => $formattedDate,
                                'status' => 'Dinas',
                                'label' => 'Dinas / Tugas Luar',
                                'detail' => $leaveReason,
                                'color' => 'indigo'
                            ];
                        } else {
                            $totalIzin++;
                            $izinDates[] = $currentDate->translatedFormat('d M');
                            $dayDetails[] = [
                                'date' => $formattedDate,
                                'status' => 'Izin',
                                'label' => 'Izin Pribadi',
                                'detail' => $leaveReason,
                                'color' => 'amber'
                            ];
                        }
                    } else {
                        $totalAbsent++;
                        $absentDates[] = $currentDate->translatedFormat('d M');
                        $dayDetails[] = [
                            'date' => $formattedDate,
                            'status' => 'Alpa',
                            'label' => 'Alpa / Tanpa Keterangan',
                            'detail' => "Tidak masuk pada jadwal $shiftName",
                            'color' => 'rose'
                        ];
                    }
                } else {
                    $dayDetails[] = [
                        'date' => $formattedDate,
                        'status' => 'Off',
                        'label' => 'Hari Libur Jadwal (Off)',
                        'detail' => "Jadwal Libur Pekan/Shift",
                        'color' => 'slate'
                    ];
                }

                $currentDate->addDay();
            }

            // Formula: Hadir / (Hadir + Alpa) * 100%
            $totalActiveWorkDays = $totalPresent + $totalAbsent;
            $percentage = $totalActiveWorkDays > 0 ? round(($totalPresent / $totalActiveWorkDays) * 100, 1) : 100;

            $reports[] = [
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
                'percentage' => $percentage,
                'day_details' => $dayDetails,
            ];
        }

        $schoolUnits = SchoolUnit::where('is_active', true)->get();

        // Calculate average percentages per unit based on filtered/fetched reports
        $unitStats = [];
        $reportsGrouped = collect($reports)->groupBy(function ($rep) {
            return $rep['employee']['unit_id'] ?? 0;
        });

        foreach ($schoolUnits as $unit) {
            $unitReports = $reportsGrouped->get($unit->id) ?? collect();
            $totalPresentUnit = $unitReports->sum('total_present');
            $totalAbsentUnit = $unitReports->sum('total_absent');
            $totalActiveUnit = $totalPresentUnit + $totalAbsentUnit;
            
            $unitStats[$unit->id] = [
                'name' => $unit->name,
                'average' => $totalActiveUnit > 0 ? round(($totalPresentUnit / $totalActiveUnit) * 100, 1) : 0,
                'count' => $unitReports->count()
            ];
        }

        return view('attendance-percentage-reports.index', compact(
            'reports',
            'schoolUnits',
            'month',
            'unitId',
            'startDateReq',
            'endDateReq',
            'unitStats',
            'positions',
            'selectedPosition'
        ));
    }
}

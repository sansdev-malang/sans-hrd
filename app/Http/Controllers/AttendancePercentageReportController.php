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
        $rawEmployees = $this->service->getSdEmployees();
        $employeesCollection = collect($rawEmployees);

        if (!empty($unitId)) {
            $employeesCollection = $employeesCollection->filter(function ($emp) use ($unitId) {
                return ($emp['unit_id'] ?? '') == $unitId;
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

        // Calculate Report
        $reports = [];

        foreach ($employeesCollection as $emp) {
            $uid = $emp['zkteco_uid'] ?? null;
            $empId = $emp['id'] ?? null;
            $unit = $emp['unit_id'] ?? null;

            if (!$uid || !$empId) continue;

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

            // Loop through each day of the month
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                $dateStr = $currentDate->format('Y-m-d');
                $dayOfWeek = $currentDate->dayOfWeek; // 1 (Mon) - 7 (Sun)

                // Skip Holidays
                if (in_array($dateStr, $holidayDates)) {
                    $currentDate->addDay();
                    continue;
                }

                // Check approved leaves
                $isOnLeave = false;
                $getsBonus = false;
                $statusCode = null;
                $leaveKey = $unit . '_' . $empId;
                if (isset($leaves[$leaveKey])) {
                    foreach ($leaves[$leaveKey] as $leave) {
                        $leaveStart = substr($leave->start_date, 0, 10);
                        $leaveEnd = substr($leave->end_date, 0, 10);
                        if ($dateStr >= $leaveStart && $dateStr <= $leaveEnd) {
                            $isOnLeave = true;
                             $getsBonus = $leave->gets_presence_bonus || ($leave->status_code === 'H') || ($leave->type_name === 'Dinas');
                             $statusCode = $leave->status_code;
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

                if (isset($assignedShifts[$shiftKey])) {
                    foreach ($assignedShifts[$shiftKey] as $assignment) {
                        $assignStartDate = substr($assignment->start_date, 0, 10);
                        $assignEndDate = $assignment->end_date ? substr($assignment->end_date, 0, 10) : null;
                        if ($dateStr >= $assignStartDate && (!$assignEndDate || $dateStr <= $assignEndDate)) {
                            $detail = $assignment->workingShift->details->where('day_of_week', $dayOfWeek)->first();
                            if ($detail && !$detail->is_off) {
                                $hasShiftToday = true;
                            }
                            break;
                        }
                    }
                }

                if ($hasShiftToday) {
                    $totalWorkDays++;
                    $logKey = $uid . '_' . $dateStr;
                    $hasScan = isset($attendanceLogs[$logKey]);
                    $formattedDate = Carbon::parse($dateStr)->format('d M');

                    if ($hasScan) {
                        $totalPresent++;
                        $actualScanCount++;
                    } elseif ($isOnLeave) {
                        if ($statusCode === 'S') {
                            $totalSakit++;
                            $sakitDates[] = $formattedDate;
                        } elseif ($statusCode === 'C') {
                            $totalCuti++;
                            $cutiDates[] = $formattedDate;
                        } elseif ($statusCode === 'H' || $getsBonus) {
                            $totalPresent++;
                            $dinasCount++;
                        } else {
                            $totalIzin++;
                            $izinDates[] = $formattedDate;
                        }
                    } else {
                        $totalAbsent++;
                        $absentDates[] = $formattedDate;
                    }
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
                'dinas_count' => $dinasCount,
                'total_sakit' => $totalSakit,
                'sakit_dates' => $sakitDates,
                'total_izin' => $totalIzin,
                'izin_dates' => $izinDates,
                'total_cuti' => $totalCuti,
                'cuti_dates' => $cutiDates,
                'total_absent' => $totalAbsent,
                'absent_dates' => $absentDates,
                'percentage' => $percentage,
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
            'unitStats'
        ));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\BonusSchema;
use App\Models\EmployeeWorkingShift;
use App\Models\Setting;
use App\Models\SchoolUnit;
use App\Services\SchoolUnitService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceBonusReportController extends Controller
{
    protected SchoolUnitService $service;

    public function __construct(SchoolUnitService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $month = $request->query('month', date('Y-m'));
        $unitId = $request->query('unit_id');

        // Fetch cutoff date from settings, default to 26
        $cutoffDate = (int) Setting::get('payroll_cutoff_date', 26);

        // Determine Start and End dates based on the selected month and cutoff date
        // E.g. month = '2026-07', cutoff = 26
        // endDate = 2026-07-26
        // startDate = 2026-06-27
        
        $monthCarbon = Carbon::createFromFormat('Y-m', $month);
        
        $endDate = $monthCarbon->copy()->setDay($cutoffDate)->endOfDay();
        $startDate = $monthCarbon->copy()->subMonth()->setDay($cutoffDate + 1)->startOfDay();

        $startDateReq = $startDate->format('Y-m-d');
        $endDateReq = $endDate->format('Y-m-d');

        // 2. Fetch Active Global Bonus Schema
        $activeSchema = BonusSchema::with('tiers')
            ->where('is_active', true)
            ->first();

        // 3. Fetch Employees (Filter by unit if needed)
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

        // 4. Pre-fetch related data to avoid N+1 query problem during calculations
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

            // Loop through each day of the month (up to today if in current month)
            $lastDay = $endDate > now() ? now()->endOfDay() : $endDate;
            
            $currentDate = $startDate->copy();
            while ($currentDate <= $lastDay) {
                $dateStr = $currentDate->format('Y-m-d');
                $dayOfWeek = $currentDate->dayOfWeekIso; // 1 (Mon) - 7 (Sun)

                // Skip Holidays
                if (in_array($dateStr, $holidayDates)) {
                    $currentDate->addDay();
                    continue;
                }

                // Skip Leaves
                $isOnLeave = false;
                $leaveKey = $unit . '_' . $empId;
                if (isset($leaves[$leaveKey])) {
                    foreach ($leaves[$leaveKey] as $leave) {
                        $leaveStart = substr($leave->start_date, 0, 10);
                        $leaveEnd = substr($leave->end_date, 0, 10);
                        if ($dateStr >= $leaveStart && $dateStr <= $leaveEnd) {
                            $isOnLeave = true;
                            break;
                        }
                    }
                }
                if ($isOnLeave) {
                    $currentDate->addDay();
                    continue;
                }

                // Check Shift Assignment
                $hasShiftToday = false;
                $shiftStartTime = null;
                $shiftKey = $unit . '_' . $empId;

                if (isset($assignedShifts[$shiftKey])) {
                    foreach ($assignedShifts[$shiftKey] as $assignment) {
                        $assignStartDate = substr($assignment->start_date, 0, 10);
                        $assignEndDate = $assignment->end_date ? substr($assignment->end_date, 0, 10) : null;
                        if ($dateStr >= $assignStartDate && (!$assignEndDate || $dateStr <= $assignEndDate)) {
                            // Find detail for this day of week
                            $detail = $assignment->workingShift->details->where('day_of_week', $dayOfWeek)->first();
                            if ($detail && !$detail->is_off) {
                                $hasShiftToday = true;
                                $shiftStartTime = $detail->start_time;
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

                    // Check Attendance
                    $logKey = $uid . '_' . $dateStr;
                    if (isset($attendanceLogs[$logKey])) {
                        $dailyStatus = 'Present';
                        $totalPresent++;
                        
                        // Calculate Late
                        $firstCheckIn = collect($attendanceLogs[$logKey])->sortBy('timestamp')->first();
                        $checkInCarbon = Carbon::parse($firstCheckIn->timestamp);
                        $expectedStart = Carbon::parse($dateStr . ' ' . $shiftStartTime);
                        $dailyCheckIn = $checkInCarbon->format('H:i:s');

                        if ($checkInCarbon > $expectedStart) {
                            $diff = (int) $expectedStart->diffInMinutes($checkInCarbon);
                            $dailyLateMinutes = $diff;
                            $totalLateMinutes += $diff;
                        }

                        // Calculate Daily Bonus
                        if ($activeSchema && $activeSchema->tiers->count() > 0) {
                            $qualifyingTiers = $activeSchema->tiers->filter(function($tier) use ($dailyLateMinutes) {
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
                        $totalAbsent++;
                    }

                    $dailyDetails[] = [
                        'date' => $dateStr,
                        'shift_start' => $shiftStartTime,
                        'check_in' => $dailyCheckIn,
                        'late_minutes' => $dailyLateMinutes,
                        'status' => $dailyStatus,
                        'bonus_nominal' => $dailyBonus,
                        'tier_level' => $dailyTierLevel
                    ];
                }

                $currentDate->addDay();
            }

            $reports[] = [
                'employee' => $emp,
                'total_present' => $totalPresent,
                'total_late_minutes' => $totalLateMinutes,
                'total_absent' => $totalAbsent,
                'bonus_nominal' => $totalBonusNominal,
                'daily_details' => $dailyDetails,
            ];
        }

        // Convert array to a length-aware paginator for the view
        $perPage = 15;
        $page = $request->query('page', 1);
        $total = count($reports);
        $paginatedReports = new \Illuminate\Pagination\LengthAwarePaginator(
            array_slice($reports, ($page - 1) * $perPage, $perPage),
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $schoolUnits = SchoolUnit::where('is_active', true)->get();

        return view('bonus-reports.index', compact('paginatedReports', 'schoolUnits', 'activeSchema', 'month', 'startDateReq', 'endDateReq', 'cutoffDate'));
    }
}

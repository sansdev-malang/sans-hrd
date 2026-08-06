<?php

namespace App\Http\Controllers;

use App\Services\SchoolUnitService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected SchoolUnitService $schoolService;

    public function __construct(SchoolUnitService $schoolService)
    {
        $this->schoolService = $schoolService;
    }

    /**
     * Display the HRD aggregator dashboard.
     */
    public function index(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());

        // Fetch data from SD unit API
        $sdEmployees = $this->schoolService->getSdEmployees();
        $sdAttendances = $this->schoolService->getSdAttendances($date);

        // Fetch logs from local ZKTeco records for this date
        $logs = \App\Models\AttendanceLog::with('device')->whereDate('timestamp', $date)->get();
        $zktecoLogs = [];
        foreach ($logs as $log) {
            $uid = (string)$log->uid;
            $ts = Carbon::parse($log->timestamp);
            $zktecoLogs[$uid][] = [
                'time' => $ts->format('H:i:s'),
                'device' => $log->device->name ?? 'Mesin Absen',
                'timestamp' => $ts->timestamp,
            ];
        }

        foreach ($zktecoLogs as $uid => &$ulogs) {
            usort($ulogs, fn($a, $b) => $a['timestamp'] <=> $b['timestamp']);
        }

        // Fetch shift assignments for yesterday and today
        $startDate = Carbon::parse($date)->subDay();
        $endDate = Carbon::parse($date);
        
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

        // We can group the data for display
        $employeesCount = count($sdEmployees);
        
        $hadir = 0;
        $izin = 0;
        $sakit = 0;
        $alpa = 0;
        $belumAbsen = 0;

        // Build a mapping of employee_id => API attendance details
        $apiAttMap = [];
        foreach ($sdAttendances as $att) {
            $empId = $att['employee_id'] ?? null;
            $unitId = $att['unit_id'] ?? null;
            if ($empId && $unitId) {
                $apiAttMap["{$unitId}_{$empId}"] = $att;
            }
        }

        $attendanceMap = [];
        
        $todayStr = Carbon::parse($date)->format('Y-m-d');
        $yesterdayStr = Carbon::parse($date)->subDay()->format('Y-m-d');
        $dayOfWeekToday = Carbon::parse($date)->dayOfWeekIso;
        $dayOfWeekYesterday = Carbon::parse($date)->subDay()->dayOfWeekIso;

        foreach ($sdEmployees as $emp) {
            $empId = $emp['id'];
            $unitId = $emp['unit_id'] ?? 0;
            $uniqueKey = "{$unitId}_{$empId}";
            $uid = isset($emp['zkteco_uid']) ? (string)$emp['zkteco_uid'] : null;

            if ($uid && isset($zktecoLogs[$uid])) {
                
                $activeShiftYesterday = null;
                $activeShiftToday = null;

                if (isset($assignedShifts[$uniqueKey])) {
                    foreach ($assignedShifts[$uniqueKey] as $assignment) {
                        $assignStartDate = substr($assignment->start_date, 0, 10);
                        $assignEndDate = $assignment->end_date ? substr($assignment->end_date, 0, 10) : null;
                        
                        if ($todayStr >= $assignStartDate && (!$assignEndDate || $todayStr <= $assignEndDate)) {
                            $activeShiftToday = $assignment->workingShift->details->where('day_of_week', $dayOfWeekToday)->first();
                        }
                        if ($yesterdayStr >= $assignStartDate && (!$assignEndDate || $yesterdayStr <= $assignEndDate)) {
                            $activeShiftYesterday = $assignment->workingShift->details->where('day_of_week', $dayOfWeekYesterday)->first();
                        }
                    }
                }

                $empLogs = $zktecoLogs[$uid];
                $finalClockIn = null;
                $finalClockInDevice = null;
                $finalClockOut = null;
                $finalClockOutDevice = null;

                $isNightShiftYesterday = false;
                $yesterdayExpectedOut = null;
                if ($activeShiftYesterday && !$activeShiftYesterday->is_off) {
                    if ($activeShiftYesterday->start_time > $activeShiftYesterday->end_time) {
                        $isNightShiftYesterday = true;
                        $yesterdayExpectedOut = Carbon::parse($date . ' ' . $activeShiftYesterday->end_time);
                    }
                }

                foreach ($empLogs as $log) {
                    $logTime = Carbon::parse($date . ' ' . $log['time']);
                    
                    if ($isNightShiftYesterday && $logTime->diffInHours($yesterdayExpectedOut) <= 6 && $logTime->format('H') < 14) {
                        $finalClockOut = $log['time'];
                        $finalClockOutDevice = $log['device'];
                    } else {
                        if (!$finalClockIn) {
                            $finalClockIn = $log['time'];
                            $finalClockInDevice = $log['device'];
                        } else {
                            $finalClockOut = $log['time'];
                            $finalClockOutDevice = $log['device'];
                        }
                    }
                }

                $attendanceMap[$uniqueKey] = [
                    'status' => 'Present',
                    'clock_in' => $finalClockIn,
                    'clock_in_device' => $finalClockInDevice,
                    'clock_out' => $finalClockOut,
                    'clock_out_device' => $finalClockOutDevice,
                    'last_activity' => $finalClockOut ?: $finalClockIn,
                ];
                $hadir++;
            } else {
                if (isset($apiAttMap[$uniqueKey])) {
                    $att = $apiAttMap[$uniqueKey];
                    $attendanceMap[$uniqueKey] = $att;
                    $status = $att['status'] ?? '';
                    if ($status === 'Present' || $status === 'Late') {
                        $hadir++;
                    } elseif ($status === 'Leave' || $status === 'Permit') {
                        $izin++;
                    } elseif ($status === 'Sick' || $status === 'Sakit') {
                        $sakit++;
                    } elseif ($status === 'Absent' || $status === 'Alpa') {
                        $alpa++;
                    } else {
                        $alpa++;
                    }
                } else {
                    $alpa++;
                }
            }
        }
        // Fetch pending leave requests for the widget
        $pendingLeaves = \App\Models\LeaveRequest::where('status', 'Pending')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Fetch latest announcements
        $latestAnnouncements = \App\Models\Announcement::with('creator')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'date',
            'sdEmployees',
            'attendanceMap',
            'employeesCount',
            'hadir',
            'izin',
            'sakit',
            'alpa',
            'belumAbsen',
            'pendingLeaves',
            'latestAnnouncements'
        ));
    }
}

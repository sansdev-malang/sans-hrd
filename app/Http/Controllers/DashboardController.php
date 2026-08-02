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
        $logs = \App\Models\AttendanceLog::whereDate('timestamp', $date)->get();
        $zktecoLogs = [];
        foreach ($logs as $log) {
            $uid = (string)$log->uid;
            $ts = Carbon::parse($log->timestamp);
            if (!isset($zktecoLogs[$uid])) {
                $zktecoLogs[$uid] = [
                    'clock_in' => $ts->format('H:i:s'),
                    'clock_out' => clone $ts, // store temporarily for comparison
                    '_min' => $ts->timestamp,
                    '_max' => $ts->timestamp,
                ];
            } else {
                if ($ts->timestamp < $zktecoLogs[$uid]['_min']) {
                    $zktecoLogs[$uid]['_min'] = $ts->timestamp;
                    $zktecoLogs[$uid]['clock_in'] = $ts->format('H:i:s');
                }
                if ($ts->timestamp > $zktecoLogs[$uid]['_max']) {
                    $zktecoLogs[$uid]['_max'] = $ts->timestamp;
                    $zktecoLogs[$uid]['clock_out'] = clone $ts;
                }
            }
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
            if ($empId) {
                $apiAttMap[$empId] = $att;
            }
        }

        $attendanceMap = [];
        
        foreach ($sdEmployees as $emp) {
            $empId = $emp['id'];
            $uid = isset($emp['zkteco_uid']) ? (string)$emp['zkteco_uid'] : null;

            if ($uid && isset($zktecoLogs[$uid])) {
                $clockIn = $zktecoLogs[$uid]['clock_in'];
                $clockOut = null;
                if ($zktecoLogs[$uid]['_max'] > $zktecoLogs[$uid]['_min']) {
                    $clockOut = $zktecoLogs[$uid]['clock_out']->format('H:i:s');
                }

                $attendanceMap[$empId] = [
                    'status' => 'Present',
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                    'last_activity' => $clockOut ?: $clockIn,
                ];
                $hadir++;
            } else {
                if (isset($apiAttMap[$empId])) {
                    $att = $apiAttMap[$empId];
                    $attendanceMap[$empId] = $att;
                    $status = $att['status'] ?? '';
                    if ($status === 'Present') $hadir++;
                    elseif ($status === 'Permit') $izin++;
                    elseif ($status === 'Sick') $sakit++;
                    elseif ($status === 'Absent') $alpa++;
                    else $belumAbsen++;
                } else {
                    $belumAbsen++;
                }
            }
        }

        return view('dashboard', compact(
            'date',
            'sdEmployees',
            'attendanceMap',
            'employeesCount',
            'hadir',
            'izin',
            'sakit',
            'alpa',
            'belumAbsen'
        ));
    }
}

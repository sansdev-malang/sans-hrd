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

        // We can group the data for display
        $employeesCount = count($sdEmployees);
        
        $hadir = 0;
        $izin = 0;
        $sakit = 0;
        $alpa = 0;

        // Build a mapping of employee_id => attendance details
        $attendanceMap = [];
        foreach ($sdAttendances as $att) {
            $empId = $att['employee_id'] ?? null;
            if ($empId) {
                $attendanceMap[$empId] = $att;
            }

            $status = $att['status'] ?? '';
            if ($status === 'Present') $hadir++;
            elseif ($status === 'Permit') $izin++;
            elseif ($status === 'Sick') $sakit++;
            elseif ($status === 'Absent') $alpa++;
        }

        // For employees who have no attendance record yet on this date, we treat them as "Belum Absen"
        $belumAbsen = max(0, $employeesCount - count($sdAttendances));

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

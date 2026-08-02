<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Services\SchoolUnitService;
use Illuminate\Http\Request;

class RawAttendanceLogController extends Controller
{
    protected $service;

    public function __construct(SchoolUnitService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $search = $request->query('search');

        $query = AttendanceLog::with('device');

        if (!empty($search)) {
            $query->where('uid', 'like', "%{$search}%")
                  ->orWhere('local_name', 'like', "%{$search}%")
                  ->orWhereHas('device', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
        }

        $logs = $query->orderBy('timestamp', 'desc')->paginate(50);

        // Fetch employee mapping
        $employees = $this->service->getSdEmployees();
        $employeeMap = [];
        foreach ($employees as $emp) {
            if (!empty($emp['zkteco_uid'])) {
                $employeeMap[(string)$emp['zkteco_uid']] = $emp['name'] ?? null;
            }
        }

        return view('raw-attendance-logs.index', compact('logs', 'search', 'employeeMap'));
    }
}

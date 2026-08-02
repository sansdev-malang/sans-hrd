<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use Illuminate\Http\Request;

class RawAttendanceLogController extends Controller
{
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

        return view('raw-attendance-logs.index', compact('logs', 'search'));
    }
}

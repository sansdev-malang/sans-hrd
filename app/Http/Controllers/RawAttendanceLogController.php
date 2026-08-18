<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\ZktecoDevice;
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
        $unitId = $request->query('unit_id');
        $position = $request->query('position');

        // Fetch employee mapping
        $employees = $this->service->getSdEmployees();

        // Build filtered employee list
        $filteredEmployees = collect($employees);
        if (!empty($unitId)) {
            $filteredEmployees = $filteredEmployees->filter(function($emp) use ($unitId) {
                return (string)($emp['unit_id'] ?? '') === (string)$unitId;
            });
        }
        if (!empty($position)) {
            $filteredEmployees = $filteredEmployees->filter(function($emp) use ($position) {
                $pos = $emp['position'] ?? $emp['subject_position'] ?? '';
                return $pos === $position;
            });
        }
        if (!empty($search)) {
            $filteredEmployees = $filteredEmployees->filter(function($emp) use ($search) {
                $nameMatch = stripos($emp['name'] ?? '', $search) !== false;
                $uidMatch = stripos((string)($emp['zkteco_uid'] ?? ''), $search) !== false;
                return $nameMatch || $uidMatch;
            });
        }

        // Map employees for display
        $employeeMap = [];
        foreach ($employees as $emp) {
            if (!empty($emp['zkteco_uid'])) {
                $employeeMap[(string)$emp['zkteco_uid']] = $emp['name'] ?? null;
            }
        }

        $query = AttendanceLog::with('device');

        // If any filters are applied, constrain query to matching employee uids
        if (!empty($unitId) || !empty($position) || !empty($search)) {
            $uids = $filteredEmployees->pluck('zkteco_uid')->filter()->map(function($uid) {
                return (string)$uid;
            })->unique()->toArray();

            $query->where(function($q) use ($uids, $search) {
                if (!empty($uids)) {
                    $q->whereIn('uid', $uids);
                } else {
                    $q->where('uid', 'INVALID_UID');
                }

                if (!empty($search)) {
                    $q->orWhere('local_name', 'like', "%{$search}%")
                      ->orWhereHas('device', function($qd) use ($search) {
                          $qd->where('name', 'like', "%{$search}%");
                      });
                }
            });
        }

        $logs = $query->orderBy('timestamp', 'desc')->paginate(50);

        $devices = ZktecoDevice::all();
        $schoolUnits = \App\Models\SchoolUnit::where('is_active', true)->get();
        
        $positions = collect($employees)
            ->map(function ($emp) {
                return $emp['position'] ?? $emp['subject_position'] ?? null;
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $unitPositions = [];
        foreach ($schoolUnits as $unit) {
            $unitPositions[$unit->id] = collect($employees)
                ->filter(function($emp) use ($unit) {
                    return (string)($emp['unit_id'] ?? '') === (string)$unit->id;
                })
                ->map(function ($emp) {
                    return $emp['position'] ?? $emp['subject_position'] ?? null;
                })
                ->filter()
                ->unique()
                ->sort()
                ->values()
                ->toArray();
        }

        return view('raw-attendance-logs.index', compact(
            'logs', 'search', 'unitId', 'position', 'employeeMap', 
            'devices', 'employees', 'schoolUnits', 'positions', 'unitPositions'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'zkteco_device_id' => 'required|exists:zkteco_devices,id',
            'uid' => 'required|string|max:255',
            'timestamp' => 'required|date',
            'state' => 'required|integer',
            'type' => 'required|integer',
            'local_name' => 'nullable|string|max:255',
        ]);

        AttendanceLog::create($validated);

        return redirect()->route('raw-attendance-logs.index')->with('success', 'Log mentah mesin berhasil ditambahkan secara manual.');
    }

    public function update(Request $request, $id)
    {
        $log = AttendanceLog::findOrFail($id);

        $validated = $request->validate([
            'zkteco_device_id' => 'required|exists:zkteco_devices,id',
            'uid' => 'required|string|max:255',
            'timestamp' => 'required|date',
            'state' => 'required|integer',
            'type' => 'required|integer',
            'local_name' => 'nullable|string|max:255',
        ]);

        $log->update($validated);

        return redirect()->route('raw-attendance-logs.index')->with('success', 'Log mentah mesin berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $log = AttendanceLog::findOrFail($id);
        $log->delete();

        return redirect()->route('raw-attendance-logs.index')->with('success', 'Log mentah mesin berhasil dihapus.');
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SchoolUnitService;
use App\Models\PerformanceReport;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PkgIntegrationApiController extends Controller
{
    protected SchoolUnitService $schoolService;

    public function __construct(SchoolUnitService $schoolService)
    {
        $this->schoolService = $schoolService;
    }

    /**
     * Verify API Token helper.
     */
    protected function authorizeRequest(Request $request)
    {
        $token = $request->header('X-API-TOKEN');
        $configuredToken = env('PKG_API_TOKEN', 'secret_token_pkg');
        return $token === $configuredToken;
    }

    /**
     * SSO Credentials verification gateway.
     */
    public function verifyCredential(Request $request)
    {
        if (!$this->authorizeRequest($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $email = $request->input('email');
        $password = $request->input('password');

        // Fetch all active employees from SANS HRD
        $employees = $this->schoolService->getSdEmployees();
        $matchingEmployees = collect($employees)->where('email', $email);

        if ($matchingEmployees->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Kredensial salah atau pegawai tidak terdaftar di unit mana pun.'], 404);
        }

        $unitsList = [];
        $authenticatedUser = null;
        $isAuthenticated = false;

        foreach ($matchingEmployees as $emp) {
            $unitId = $emp['unit_id'] ?? null;
            if (!$unitId) continue;

            $unit = \App\Models\SchoolUnit::find($unitId);
            if (!$unit || !$unit->is_active) continue;

            $unitsList[] = [
                'unit_id' => $unit->id,
                'unit_name' => $unit->name,
                'employee_id' => $emp['id'],
                'role' => $emp['position'] ?? 'teacher',
            ];

            if (!$isAuthenticated) {
                try {
                    $response = \Illuminate\Support\Facades\Http::withHeaders([
                        'X-API-TOKEN' => $unit->api_token,
                        'Accept' => 'application/json',
                    ])->timeout(5)->post(rtrim($unit->api_url, '/') . '/auth/verify', [
                        'email' => $email,
                        'password' => $password,
                    ]);

                    if ($response->successful()) {
                        $body = $response->json();
                        if ($body['success'] ?? false) {
                            $isAuthenticated = true;
                            $authenticatedUser = [
                                'id' => $emp['id'],
                                'name' => $emp['name'],
                                'email' => $emp['email'],
                                'unit_id' => $unit->id,
                                'unit_name' => $unit->name,
                                'role' => $body['role'] ?? $emp['position'] ?? 'teacher',
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    // Try next unit if connection fails
                }
            }
        }

        if ($isAuthenticated && $authenticatedUser) {
            return response()->json([
                'success' => true,
                'message' => 'Authenticated successfully',
                'user' => [
                    'id' => $authenticatedUser['id'],
                    'name' => $authenticatedUser['name'],
                    'email' => $authenticatedUser['email'],
                    'unit_id' => $authenticatedUser['unit_id'],
                    'unit_name' => $authenticatedUser['unit_name'],
                    'role' => $authenticatedUser['role'],
                    'units' => $unitsList
                ]
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Kata sandi salah atau verifikasi gagal di semua unit sekolah.'], 401);
    }

    /**
     * Get aggregated employees list.
     */
    public function employees(Request $request)
    {
        if (!$this->authorizeRequest($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $employees = $this->schoolService->getSdEmployees();
        return response()->json([
            'success' => true,
            'data' => $employees
        ]);
    }

    /**
     * Get aggregated attendance summary for a date range.
     */
    public function attendanceSummary(Request $request)
    {
        if (!$this->authorizeRequest($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

        $employees = collect($this->schoolService->getSdEmployees());

        // Prepare holidays
        $holidays = \App\Models\Holiday::with('adjustments')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('original_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                  ->orWhereHas('adjustments', function ($q2) use ($startDate, $endDate) {
                      $q2->whereBetween('adjusted_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
                  });
            })->get();
        $holidayDates = $holidays->pluck('original_date')->toArray();

        // Prepare leaves
        $leavesData = \App\Models\LeaveRequest::where(function($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate]);
        })->where('status', 'approved')->get();

        $leaves = [];
        foreach ($leavesData as $l) {
            $key = $l->school_unit_id . '_' . $l->employee_id;
            $leaves[$key][] = $l;
        }

        // Prepare shifts
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

        // Prepare logs
        $logsData = \App\Models\AttendanceLog::whereBetween('timestamp', [
            $startDate->format('Y-m-d 00:00:00'),
            $endDate->copy()->addDay()->format('Y-m-d 12:00:00')
        ])->get();

        $attendanceLogs = [];
        foreach ($logsData as $log) {
            $attendanceLogs[(string)$log->uid][] = $log->timestamp;
        }

        foreach ($attendanceLogs as &$ulogs) {
            sort($ulogs);
        }

        $summaries = [];

        foreach ($employees as $emp) {
            $uid = $emp['zkteco_uid'] ?? null;
            $empId = $emp['id'] ?? null;
            $unitId = $emp['unit_id'] ?? null;

            if (!$uid || !$empId) continue;

            $totalWorkDays = 0;
            $presentDays = 0;
            $lateMinutesTotal = 0;

            $lastDay = $endDate > now() ? now()->endOfDay() : $endDate;
            $currentDate = $startDate->copy();

            while ($currentDate <= $lastDay) {
                $dateStr = $currentDate->format('Y-m-d');
                $dayOfWeek = $currentDate->dayOfWeek;

                if (in_array($dateStr, $holidayDates)) {
                    $currentDate->addDay();
                    continue;
                }

                $isOnLeave = false;
                $leaveKey = $unitId . '_' . $empId;
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

                $hasShiftToday = false;
                $isOffShift = false;
                $shiftStartTime = null;
                $shiftEndTime = null;
                $shiftKey = $unitId . '_' . $empId;
                $isShiftWorker = false;

                if (isset($assignedShifts[$shiftKey])) {
                    foreach ($assignedShifts[$shiftKey] as $assignment) {
                        if ($assignment->workingShift->is_shift) {
                            $isShiftWorker = true;
                        }
                        $assignStartDate = substr($assignment->start_date, 0, 10);
                        $assignEndDate = $assignment->end_date ? substr($assignment->end_date, 0, 10) : null;
                        if ($dateStr >= $assignStartDate && (!$assignEndDate || $dateStr <= $assignEndDate)) {
                            $detail = $assignment->workingShift->details->where('day_of_week', $dayOfWeek)->first();
                            if ($detail) {
                                if ($detail->is_off) {
                                    $isOffShift = true;
                                } else {
                                    $hasShiftToday = true;
                                    $shiftStartTime = $detail->start_time;
                                    $shiftEndTime = $detail->end_time;
                                }
                            }
                            break;
                        }
                    }
                }

                if ($isShiftWorker && !$hasShiftToday && !$isOffShift) {
                    $isOffShift = true;
                }

                if ($hasShiftToday) {
                    $totalWorkDays++;

                    $isNightShift = $shiftStartTime > $shiftEndTime;
                    $expectedIn = Carbon::parse($dateStr . ' ' . $shiftStartTime);
                    $expectedOut = Carbon::parse($dateStr . ' ' . $shiftEndTime);
                    if ($isNightShift) {
                        $expectedOut->addDay();
                    }

                    $inStart = $expectedIn->copy()->subHours(6);
                    $inEnd = $expectedIn->copy()->addHours(6);

                    $checkInLog = null;
                    if (isset($attendanceLogs[(string)$uid])) {
                        foreach ($attendanceLogs[(string)$uid] as $ts) {
                            $tsCarbon = Carbon::parse($ts);
                            if ($tsCarbon->between($inStart, $inEnd)) {
                                $checkInLog = $ts;
                                break;
                            }
                        }
                    }

                    if ($checkInLog) {
                        $presentDays++;
                        $actualIn = Carbon::parse($checkInLog);
                        if ($actualIn->gt($expectedIn)) {
                            $lateMinutesTotal += $actualIn->diffInMinutes($expectedIn);
                        }
                    }
                }

                $currentDate->addDay();
            }

            $summaries[] = [
                'employee_id' => $empId,
                'unit_id' => $unitId,
                'total_work_days' => $totalWorkDays,
                'present_days' => $presentDays,
                'late_minutes_total' => $lateMinutesTotal,
                'attendance_rate' => $totalWorkDays > 0 ? round(($presentDays / $totalWorkDays) * 100, 2) : 0.00
            ];
        }

        return response()->json([
            'success' => true,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'data' => $summaries
        ]);
    }

    /**
     * Receive and store performance reports from SANS PKG.
     */
    public function receivePerformanceReport(Request $request)
    {
        if (!$this->authorizeRequest($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'academic_year' => 'required|string|max:9',
            'semester' => 'required|in:1,2',
            'employee_id' => 'required|integer',
            'unit_id' => 'required|exists:school_units,id',
            'scores.pedagogik' => 'required|numeric',
            'scores.kepribadian' => 'required|numeric',
            'scores.sosial' => 'required|numeric',
            'scores.profesional' => 'required|numeric',
            'scores.discipline' => 'required|numeric',
            'scores.final' => 'required|numeric',
            'predicate' => 'nullable|string|max:50',
            'recommendations' => 'nullable|string',
        ]);

        $report = PerformanceReport::updateOrCreate(
            [
                'academic_year' => $request->input('academic_year'),
                'semester' => $request->input('semester'),
                'employee_id' => $request->input('employee_id'),
                'unit_id' => $request->input('unit_id'),
            ],
            [
                'score_pedagogik' => $request->input('scores.pedagogik'),
                'score_kepribadian' => $request->input('scores.kepribadian'),
                'score_sosial' => $request->input('scores.sosial'),
                'score_profesional' => $request->input('scores.profesional'),
                'score_discipline' => $request->input('scores.discipline'),
                'final_score' => $request->input('scores.final'),
                'predicate' => $request->input('predicate'),
                'recommendations' => $request->input('recommendations'),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Performance report received and saved successfully.',
            'data' => $report
        ], 200);
    }
}

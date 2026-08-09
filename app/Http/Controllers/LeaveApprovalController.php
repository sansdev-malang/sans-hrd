<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\SchoolUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LeaveApprovalController extends Controller
{
    /**
     * Display a listing of leave requests from all units.
     */
    public function index(Request $request)
    {
        if ($request->has('clear_all')) {
            $recentIds = LeaveRequest::where('created_at', '>=', now()->subDays(3))->pluck('id')->toArray();
            $readIds = session('read_leave_ids_' . auth()->id(), []);
            $newReadIds = array_unique(array_merge($readIds, $recentIds));
            session(['read_leave_ids_' . auth()->id() => $newReadIds]);
            if ($request->expectsJson()) {
                return response()->json(['success' => true]);
            }
            return redirect()->route('leave-approvals.index');
        }

        if ($request->has('read_id')) {
            $readIds = session('read_leave_ids_' . auth()->id(), []);
            $readIds[] = (int) $request->input('read_id');
            session(['read_leave_ids_' . auth()->id() => array_unique($readIds)]);
        }
        // 1. Pull latest leave requests from all active units and sync locally
        $this->pullLeaveRequestsFromUnits();

        // 2. Load from local database with query filters
        $query = LeaveRequest::with('schoolUnit')->orderBy('created_at', 'desc');

        if ($request->filled('unit_id')) {
            $query->where('school_unit_id', $request->input('unit_id'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $leavesCollection = $query->get();

        // 3. Map employee names
        $employees = (new \App\Services\SchoolUnitService)->getSdEmployees();
        $employeeMap = collect($employees)->keyBy(function ($item) {
            return $item['unit_id'] . '-' . $item['id'];
        })->toArray();

        $search = $request->query('search');
        $mappedLeaves = [];

        foreach ($leavesCollection as $leave) {
            $key = $leave->school_unit_id . '-' . $leave->employee_id;
            if (isset($employeeMap[$key])) {
                $leave->employee_name = $employeeMap[$key]['name'];
                $leave->employee_nip = $employeeMap[$key]['nuptk_nip_nik'] ?? '-';
                $leave->employee_photo = $employeeMap[$key]['photo'] ?? null;
                $leave->employee_unit_url = $employeeMap[$key]['unit_url'] ?? null;
                $leave->employee_gender = $employeeMap[$key]['gender'] ?? '-';
                $leave->employee_status = $employeeMap[$key]['employment_status'] ?? '-';
                $leave->employee_position = $employeeMap[$key]['position'] ?? $employeeMap[$key]['subject_position'] ?? '-';
                $leave->employee_email = $employeeMap[$key]['email'] ?? '-';
            } else {
                $leave->employee_name = 'Pegawai #' . $leave->employee_id;
                $leave->employee_nip = '-';
                $leave->employee_photo = null;
                $leave->employee_unit_url = null;
                $leave->employee_gender = '-';
                $leave->employee_status = '-';
                $leave->employee_position = '-';
                $leave->employee_email = '-';
            }

            if ($search) {
                $searchLower = strtolower($search);
                $nameMatch = str_contains(strtolower($leave->employee_name), $searchLower);
                $nipMatch = str_contains(strtolower($leave->employee_nip), $searchLower);
                if (!$nameMatch && !$nipMatch) {
                    continue;
                }
            }

            $mappedLeaves[] = $leave;
        }

        // 4. Paginate
        $total = count($mappedLeaves);
        $perPageReq = $request->query('per_page', 50);
        $perPage = $perPageReq === 'all' ? ($total > 0 ? $total : 1) : (int) $perPageReq;
        $page = (int) $request->query('page', 1);

        $paginatedLeaves = new \Illuminate\Pagination\LengthAwarePaginator(
            array_slice($mappedLeaves, ($page - 1) * $perPage, $perPage),
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $schoolUnits = SchoolUnit::where('is_active', true)->orderBy('name')->get();
        $leaveTypes = LeaveRequest::distinct()->pluck('type')->filter()->sort()->values();

        return view('leave-approvals.index', compact('paginatedLeaves', 'schoolUnits', 'leaveTypes'));
    }

    /**
     * Approve a leave request.
     */
    public function approve($id)
    {
        $leave = LeaveRequest::findOrFail($id);
        $unit = SchoolUnit::findOrFail($leave->school_unit_id);

        try {
            // Send decision to unit API
            $response = Http::withHeaders([
                'X-API-TOKEN' => $unit->api_token,
                'Accept' => 'application/json',
            ])->post(rtrim($unit->api_url, '/') . '/leave-requests/decision', [
                'leave_id' => $leave->remote_leave_id ?? $leave->employee_id, // Fallback to employee_id if remote_leave_id is null
                'status' => 'Approved',
                'notes' => 'Disetujui oleh HRD Pusat.',
            ]);

            if ($response->successful()) {
                $leave->update([
                    'status' => 'Approved',
                    'notes' => 'Disetujui oleh HRD Pusat.',
                ]);

                return redirect()->route('leave-approvals.index')
                    ->with('success', 'Pengajuan izin berhasil disetujui.');
            } else {
                Log::error("Failed response approving leave: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Error approving leave: " . $e->getMessage());
        }

        return redirect()->route('leave-approvals.index')
            ->with('error', 'Gagal memproses persetujuan izin ke unit sekolah.');
    }

    /**
     * Reject a leave request.
     */
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'notes' => 'required|string|max:255',
        ]);

        $leave = LeaveRequest::findOrFail($id);
        $unit = SchoolUnit::findOrFail($leave->school_unit_id);

        try {
            // Send decision to unit API
            $response = Http::withHeaders([
                'X-API-TOKEN' => $unit->api_token,
                'Accept' => 'application/json',
            ])->post(rtrim($unit->api_url, '/') . '/leave-requests/decision', [
                'leave_id' => $leave->remote_leave_id ?? $leave->employee_id, // Fallback
                'status' => 'Rejected',
                'notes' => $validated['notes'],
            ]);

            if ($response->successful()) {
                $leave->update([
                    'status' => 'Rejected',
                    'notes' => $validated['notes'],
                ]);

                return redirect()->route('leave-approvals.index')
                    ->with('success', 'Pengajuan izin berhasil ditolak.');
            }
        } catch (\Exception $e) {
            Log::error("Error rejecting leave: " . $e->getMessage());
        }

        return redirect()->route('leave-approvals.index')
            ->with('error', 'Gagal memproses penolakan izin ke unit sekolah.');
    }

    /**
     * Automatically pull all leave requests from units and save them in local database.
     */
    public function pullLeaveRequestsFromUnits()
    {
        $units = SchoolUnit::where('is_active', true)->get();

        foreach ($units as $unit) {
            try {
                $response = Http::withHeaders([
                    'X-API-TOKEN' => $unit->api_token,
                    'Accept' => 'application/json',
                ])->timeout(5)->get(rtrim($unit->api_url, '/') . '/leave-requests');

                if ($response->successful()) {
                    $remoteLeaves = $response->json() ?? [];

                    foreach ($remoteLeaves as $rL) {
                        // We check by school_unit_id and remote_leave_id
                        LeaveRequest::updateOrCreate(
                            [
                                'school_unit_id' => $unit->id,
                                'remote_leave_id' => $rL['id'],
                            ],
                            [
                                'employee_id' => $rL['employee_id'],
                                'employee_name' => $rL['employee_name'] ?? null,
                                'start_date' => $rL['start_date'],
                                'end_date' => $rL['end_date'],
                                'type' => $rL['type'],
                                'status_code' => $rL['status_code'] ?? null,
                                'gets_presence_bonus' => $rL['gets_presence_bonus'] ?? false,
                                'reason' => $rL['reason'],
                                'status' => $rL['status'],
                                'notes' => $rL['notes'] ?? null,
                                'attachment' => $rL['attachment'] ?? null,
                            ]
                        );
                    }
                }
            } catch (\Exception $e) {
                Log::error("Failed pulling leave requests from unit {$unit->name}: " . $e->getMessage());
            }
        }
    }
}

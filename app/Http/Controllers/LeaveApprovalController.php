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
        $cookieName = 'read_leave_ids_' . auth()->id();
        if ($request->has('clear_all')) {
            $recentIds = LeaveRequest::where('created_at', '>=', now()->subDays(3))->pluck('id')->toArray();
            $readIds = $request->cookie($cookieName);
            $readIds = $readIds ? json_decode($readIds, true) : [];
            if (!is_array($readIds)) {
                $readIds = [];
            }
            $newReadIds = array_unique(array_merge($readIds, $recentIds));
            cookie()->queue($cookieName, json_encode($newReadIds), 60 * 24 * 30); // 30 days
            if ($request->expectsJson()) {
                return response()->json(['success' => true]);
            }
            return redirect()->route('leave-approvals.index');
        }

        if ($request->has('read_id')) {
            $readIds = $request->cookie($cookieName);
            $readIds = $readIds ? json_decode($readIds, true) : [];
            if (!is_array($readIds)) {
                $readIds = [];
            }
            $readIds[] = (int) $request->input('read_id');
            cookie()->queue($cookieName, json_encode(array_unique($readIds)), 60 * 24 * 30); // 30 days
        }
        // 1. Pull latest leave requests from all active units and sync locally
        $this->pullLeaveRequestsFromUnits();

        // 2. Load from local database with query filters
        $query = LeaveRequest::with('schoolUnit')->orderBy('created_at', 'desc');

        if ($request->filled('unit_id')) {
            $query->where('school_unit_id', $request->input('unit_id'));
        }

        if ($request->filled('type')) {
            $type = $request->input('type');
            $codeMap = ['Sakit' => 'S', 'Izin' => 'I', 'Cuti' => 'C', 'Dinas' => 'H'];
            if (isset($codeMap[$type])) {
                $query->where('status_code', $codeMap[$type]);
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $leavesCollection = $query->get();

        // 3. Map employee names
        $employees = (new \App\Services\SchoolUnitService)->getAllEmployees();
        $employeeMap = collect($employees)->keyBy(function ($item) {
            return $item['unit_id'] . '-' . $item['id'];
        })->toArray();

        $search = $request->query('search');
        $mappedLeaves = [];
        $codeToNameMap = ['S' => 'Sakit', 'I' => 'Izin', 'C' => 'Cuti', 'H' => 'Dinas'];

        foreach ($leavesCollection as $leave) {
            $leave->type = $leave->type_name;
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
        $leaveTypes = ['Sakit', 'Izin', 'Cuti', 'Dinas'];

        $pendingCount = LeaveRequest::where('status', 'Pending')->count();
        $processedCount = LeaveRequest::whereIn('status', ['Approved', 'Rejected'])->count();
        $approvedCount = LeaveRequest::where('status', 'Approved')->count();
        $approvalRate = $processedCount > 0 ? round(($approvedCount / $processedCount) * 100) : 0;

        return view('leave-approvals.index', compact(
            'paginatedLeaves',
            'schoolUnits',
            'leaveTypes',
            'pendingCount',
            'processedCount',
            'approvalRate'
        ));
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
                'processed_by' => auth()->user()->name . ' (HRD Pusat)',
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
                'processed_by' => auth()->user()->name . ' (HRD Pusat)',
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
     * Update a leave request decision (Edit status/notes).
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:Pending,Approved,Rejected',
            'notes' => 'nullable|string|max:255',
            'type' => 'required|string',
        ]);

        $leave = LeaveRequest::findOrFail($id);
        $unit = SchoolUnit::findOrFail($leave->school_unit_id);

        try {
            // Send new decision to unit API
            $response = Http::withHeaders([
                'X-API-TOKEN' => $unit->api_token,
                'Accept' => 'application/json',
            ])->post(rtrim($unit->api_url, '/') . '/leave-requests/decision', [
                'leave_id' => $leave->remote_leave_id ?? $leave->employee_id,
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? '',
                'type' => $validated['type'],
                'processed_by' => auth()->user()->name . ' (HRD Pusat)',
            ]);

            if ($response->successful()) {
                $typeLower = strtolower($validated['type']);
                $getsPresenceBonus = false;
                if (str_contains($typeLower, 'sakit')) {
                    $statusCode = 'S';
                } elseif (str_contains($typeLower, 'cuti')) {
                    $statusCode = 'C';
                } elseif (str_contains($typeLower, 'dinas') || str_contains($typeLower, 'kedinasan') || $typeLower === 'h') {
                    $statusCode = 'H';
                    $getsPresenceBonus = true;
                } else {
                    $statusCode = 'I';
                }

                $leave->update([
                    'status' => $validated['status'],
                    'notes' => $validated['notes'],
                    'status_code' => $statusCode,
                    'gets_presence_bonus' => $getsPresenceBonus,
                ]);

                return redirect()->route('leave-approvals.index')
                    ->with('success', 'Keputusan izin berhasil diperbarui.');
            }
        } catch (\Exception $e) {
            Log::error("Error updating leave decision: " . $e->getMessage());
        }

        return redirect()->route('leave-approvals.index')
            ->with('error', 'Gagal memproses pembaruan keputusan izin ke unit sekolah.');
    }

    /**
     * Delete a leave request (Soft Delete and Reject on Remote Unit).
     */
    public function destroy($id)
    {
        $leave = LeaveRequest::findOrFail($id);
        $unit = SchoolUnit::findOrFail($leave->school_unit_id);

        try {
            // Reject on remote unit
            $response = Http::withHeaders([
                'X-API-TOKEN' => $unit->api_token,
                'Accept' => 'application/json',
            ])->post(rtrim($unit->api_url, '/') . '/leave-requests/decision', [
                'leave_id' => $leave->remote_leave_id ?? $leave->employee_id,
                'status' => 'Rejected',
                'notes' => 'Dihapus oleh HRD Pusat.',
            ]);

            if ($response->successful()) {
                // Soft delete locally
                $leave->delete();

                return redirect()->route('leave-approvals.index')
                    ->with('success', 'Pengajuan izin berhasil dihapus.');
            }
        } catch (\Exception $e) {
            Log::error("Error deleting leave: " . $e->getMessage());
        }

        return redirect()->route('leave-approvals.index')
            ->with('error', 'Gagal menyinkronkan penghapusan izin ke unit sekolah.');
    }

    /**
     * Automatically pull all leave requests from units and save them in local database.
     */
    public function pullLeaveRequestsFromUnits()
    {
        $units = SchoolUnit::where('is_active', true)->get();

        if ($units->isEmpty()) {
            return;
        }

        // Fetch leave requests from all units concurrently
        $responses = Http::pool(function (\Illuminate\Http\Client\Pool $pool) use ($units) {
            return $units->map(function ($unit) use ($pool) {
                return $pool->as($unit->id)
                    ->withHeaders([
                        'X-API-TOKEN' => $unit->api_token,
                        'Accept' => 'application/json',
                    ])->timeout(5)->get(rtrim($unit->api_url, '/') . '/leave-requests');
            });
        });

        foreach ($units as $unit) {
            try {
                $response = $responses[$unit->id] ?? null;

                if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
                    $remoteLeaves = $response->json() ?? [];

                    foreach ($remoteLeaves as $rL) {
                        $statusCode = $rL['status_code'] ?? null;
                        $status = $rL['status'];
                        
                        $isNewOrPendingH = false;
                        if ($statusCode === 'H') {
                            // Check if it already exists and is approved in our local DB
                            $exists = LeaveRequest::where('school_unit_id', $unit->id)
                                ->where('remote_leave_id', $rL['id'])
                                ->first();
                            
                            if (!$exists || $exists->status !== 'Approved') {
                                $isNewOrPendingH = true;
                            }
                            $status = 'Approved';
                        }

                        $notes = $rL['notes'] ?? ($statusCode === 'H' ? 'Disetujui otomatis oleh HRD Pusat (Dinas).' : null);
                        $processedBy = $rL['processed_by'] ?? (($statusCode === 'H') ? 'Sistem (Otomatis)' : null);

                        if (empty($processedBy) && $notes) {
                            $lowerNotes = strtolower($notes);
                            if (
                                str_starts_with($lowerNotes, 'disetujui oleh ') ||
                                str_starts_with($lowerNotes, 'ditolak oleh ') ||
                                str_starts_with($lowerNotes, 'disetujui otomatis oleh ')
                            ) {
                                $parts = explode('oleh ', $notes);
                                $processedBy = preg_replace('/[\s.]+$/', '', end($parts));
                            } elseif (preg_match('/\((Keputusan|Ditolak|Disetujui)\s+oleh\s+(.*?)\)/i', $notes, $matches)) {
                                $processedBy = trim($matches[2]);
                            }
                        }

                        if ($notes) {
                            // Clean notes from decision maker signature
                            $notes = preg_replace('/\s*\((Keputusan|Ditolak|Disetujui)\s+oleh.*?\)/i', '', $notes);
                            
                            // If notes is just automatic text like "Disetujui oleh...", make it null
                            $lowerNotes = strtolower(trim($notes));
                            if (
                                str_starts_with($lowerNotes, 'disetujui oleh') || 
                                str_starts_with($lowerNotes, 'ditolak oleh') || 
                                str_starts_with($lowerNotes, 'disetujui otomatis oleh')
                            ) {
                                $notes = null;
                            }
                        }

                        $updateData = [
                            'employee_id' => $rL['employee_id'],
                            'start_date' => $rL['start_date'],
                            'end_date' => $rL['end_date'],
                            'status_code' => $statusCode,
                            'gets_presence_bonus' => $rL['gets_presence_bonus'] ?? false,
                            'requires_attendance' => $rL['requires_attendance'] ?? true,
                            'requires_approval' => $rL['requires_approval'] ?? true,
                            'status' => $status,
                            'notes' => $notes,
                        ];

                        // We check by school_unit_id and remote_leave_id
                        LeaveRequest::updateOrCreate(
                            [
                                'school_unit_id' => $unit->id,
                                'remote_leave_id' => $rL['id'],
                            ],
                            $updateData
                        );

                        if ($isNewOrPendingH) {
                            try {
                                Http::withHeaders([
                                    'X-API-TOKEN' => $unit->api_token,
                                    'Accept' => 'application/json',
                                ])->post(rtrim($unit->api_url, '/') . '/leave-requests/decision', [
                                    'leave_id' => $rL['id'],
                                    'status' => 'Approved',
                                    'notes' => 'Disetujui otomatis oleh HRD Pusat (Dinas).',
                                ]);
                            } catch (\Exception $e) {
                                Log::error("Failed to send auto-approval for H leave to unit {$unit->name}: " . $e->getMessage());
                            }
                        }
                    }
                } else {
                    $statusCode = $response instanceof \Illuminate\Http\Client\Response ? $response->status() : 'Error/Timeout';
                    Log::error("Failed response pulling leave requests from unit {$unit->name}. Status: {$statusCode}");
                }
            } catch (\Exception $e) {
                Log::error("Failed pulling leave requests from unit {$unit->name}: " . $e->getMessage());
            }
        }
    }

    /**
     * Fetch leave types directly from school unit API.
     */
    public function getUnitLeaveTypes($unit_id)
    {
        $unit = SchoolUnit::findOrFail($unit_id);
        
        try {
            $response = Http::withHeaders([
                'X-API-TOKEN' => $unit->api_token,
                'Accept' => 'application/json',
            ])->timeout(5)->get(rtrim($unit->api_url, '/') . '/leave-types');
            
            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (\Exception $e) {
            Log::error("Failed fetching leave types from unit {$unit->name}: " . $e->getMessage());
        }
        
        return response()->json([]);
    }
}

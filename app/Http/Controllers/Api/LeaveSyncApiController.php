<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LeaveSyncApiController extends Controller
{
    /**
     * Store or update a leave request synced from a school unit.
     */
    public function storeOrUpdate(Request $request)
    {
        $validated = $request->validate([
            'school_unit_id' => 'required|integer',
            'remote_leave_id' => 'required|integer',
            'employee_id' => 'required|integer',
            'status_code' => 'required|string|max:10',
            'gets_presence_bonus' => 'required|boolean',
            'requires_attendance' => 'required|boolean',
            'requires_approval' => 'required|boolean',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'status' => 'required|string|max:50',
            'notes' => 'nullable|string',
        ]);

        try {
            $leave = LeaveRequest::updateOrCreate(
                [
                    'school_unit_id' => $validated['school_unit_id'],
                    'remote_leave_id' => $validated['remote_leave_id'],
                ],
                $validated
            );

            Log::info('Leave request synced successfully', [
                'unit_id' => $validated['school_unit_id'],
                'remote_leave_id' => $validated['remote_leave_id'],
                'leave_id' => $leave->id,
                'employee_id' => $validated['employee_id'],
                'status' => $validated['status']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Leave request synced successfully.',
                'data' => $leave
            ]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            Log::error('Leave request sync: Unique constraint violation', [
                'unit_id' => $validated['school_unit_id'] ?? null,
                'remote_leave_id' => $validated['remote_leave_id'] ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Duplicate leave request for this unit.'
            ], 409);
        } catch (\Exception $e) {
            Log::error('Leave request sync failed', [
                'unit_id' => $validated['school_unit_id'] ?? null,
                'remote_leave_id' => $validated['remote_leave_id'] ?? null,
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync leave request. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete a leave request synced from a school unit.
     */
    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'school_unit_id' => 'required|integer',
            'remote_leave_id' => 'required|integer',
        ]);

        try {
            $deleted = LeaveRequest::where('school_unit_id', $validated['school_unit_id'])
                ->where('remote_leave_id', $validated['remote_leave_id'])
                ->delete();

            if ($deleted) {
                Log::info('Leave request deleted successfully', [
                    'unit_id' => $validated['school_unit_id'],
                    'remote_leave_id' => $validated['remote_leave_id'],
                    'rows_deleted' => $deleted
                ]);
            } else {
                Log::warning('Leave request deletion: Record not found', [
                    'unit_id' => $validated['school_unit_id'],
                    'remote_leave_id' => $validated['remote_leave_id']
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => $deleted ? 'Leave request deleted successfully.' : 'Leave request not found or already deleted.'
            ]);
        } catch (\Exception $e) {
            Log::error('Leave request deletion failed', [
                'unit_id' => $validated['school_unit_id'] ?? null,
                'remote_leave_id' => $validated['remote_leave_id'] ?? null,
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete leave request. Please try again.'
            ], 500);
        }
    }
}

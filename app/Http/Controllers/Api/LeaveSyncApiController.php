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

            return response()->json([
                'success' => true,
                'message' => 'Leave request synced successfully.',
                'data' => $leave
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to sync leave request from unit: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync leave request.'
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
                ->delete(); // Hard delete because soft deletes are dropped

            return response()->json([
                'success' => true,
                'message' => $deleted ? 'Leave request deleted successfully.' : 'Leave request not found or already deleted.'
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to delete synced leave request: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete synced leave request.'
            ], 500);
        }
    }
}

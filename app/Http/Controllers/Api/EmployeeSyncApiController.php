<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class EmployeeSyncApiController extends Controller
{
    /**
     * Clear the employee list cache when a mutation occurs on a school unit.
     */
    public function clearCache(Request $request)
    {
        // The request is already validated by 'verify_school_unit_token' middleware
        $unitId = $request->input('school_unit_id') ?? $request->input('unit_id');
        $unit = $request->get('verified_unit');

        try {
            Cache::forget('sd_employees_all');

            Log::info('Employee cache cleared via webhook trigger from school unit', [
                'school_unit_id' => $unitId,
                'school_unit_name' => $unit ? $unit->name : 'Unknown'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Employee cache cleared successfully on central HRD.'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to clear employee cache via webhook', [
                'school_unit_id' => $unitId,
                'exception' => get_class($e),
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to clear employee cache.'
            ], 500);
        }
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifySchoolUnitToken
{
    /**
     * Verify school unit API token for sync endpoints.
     * Required header: X-API-TOKEN
     * Required parameter: school_unit_id or unit_id
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('X-API-TOKEN');
        $unitId = $request->input('school_unit_id') ?? $request->input('unit_id');

        // Validate token and unit_id existence
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Missing X-API-TOKEN header. Please provide valid school unit API token.'
            ], 401);
        }

        if (!$unitId) {
            return response()->json([
                'success' => false,
                'message' => 'Missing school_unit_id or unit_id parameter.'
            ], 400);
        }

        // Verify token against database
        $unit = \App\Models\SchoolUnit::find($unitId);

        if (!$unit) {
            \Illuminate\Support\Facades\Log::warning('API: School unit not found', [
                'unit_id' => $unitId,
                'ip' => $request->ip(),
                'endpoint' => $request->getPathInfo()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'School unit not found.'
            ], 404);
        }

        if (!$unit->is_active) {
            \Illuminate\Support\Facades\Log::warning('API: School unit is inactive', [
                'unit_id' => $unitId,
                'unit_name' => $unit->name,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'School unit is inactive.'
            ], 403);
        }

        if ($unit->api_token !== $token) {
            \Illuminate\Support\Facades\Log::warning('API: Invalid token', [
                'unit_id' => $unitId,
                'unit_name' => $unit->name,
                'ip' => $request->ip(),
                'endpoint' => $request->getPathInfo()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid API token.'
            ], 401);
        }

        // Store unit info in request for later use
        $request->merge(['verified_unit' => $unit]);

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyPkgApiToken
{
    /**
     * Verify PKG (Penilaian Kinerja Guru) integration API token.
     * This middleware protects endpoints used by sans-pkg application.
     *
     * Required header: X-API-TOKEN
     * Expected token: env('PKG_API_TOKEN')
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('X-API-TOKEN');
        $configuredToken = config('services.pkg.api_token');

        if (!$configuredToken) {
            \Illuminate\Support\Facades\Log::error('PKG_API_TOKEN not configured in .env');
            return response()->json([
                'success' => false,
                'message' => 'Server configuration error.'
            ], 500);
        }

        if (!$token) {
            \Illuminate\Support\Facades\Log::warning('API: Missing PKG API token', [
                'ip' => $request->ip(),
                'endpoint' => $request->getPathInfo()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Missing X-API-TOKEN header.'
            ], 401);
        }

        if ($token !== $configuredToken) {
            \Illuminate\Support\Facades\Log::warning('API: Invalid PKG API token', [
                'ip' => $request->ip(),
                'endpoint' => $request->getPathInfo()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid API token.'
            ], 401);
        }

        return $next($request);
    }
}

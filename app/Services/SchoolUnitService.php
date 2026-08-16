<?php

namespace App\Services;

use App\Models\SchoolUnit;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SchoolUnitService
{
    /**
     * Get employees from all active school units.
     */
    public function getSdEmployees(): array
    {
        return \Illuminate\Support\Facades\Cache::remember('sd_employees_all', 86400, function() {
            $activeUnits = SchoolUnit::where('is_active', true)->get();
            $allEmployees = [];

            if ($activeUnits->isEmpty()) {
                return [];
            }

            $responses = Http::pool(function (\Illuminate\Http\Client\Pool $pool) use ($activeUnits) {
                return $activeUnits->map(function ($unit) use ($pool) {
                    return $pool->as($unit->id)
                        ->withHeaders([
                            'X-API-TOKEN' => $unit->api_token,
                            'Accept' => 'application/json',
                        ])->timeout(5)->get(rtrim($unit->api_url, '/') . '/employees');
                });
            });

            foreach ($activeUnits as $unit) {
                $response = $responses[$unit->id] ?? null;
                
                if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
                    $employees = $response->json('data') ?? [];
                    
                    $parsedUrl = parse_url($unit->api_url);
                    $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
                    if (isset($parsedUrl['port'])) {
                        $baseUrl .= ':' . $parsedUrl['port'];
                    }

                    foreach ($employees as &$emp) {
                        $emp['unit_name'] = $unit->name;
                        $emp['unit_id'] = $unit->id;
                        $emp['unit_url'] = $baseUrl;
                    }
                    $allEmployees = array_merge($allEmployees, $employees);
                } else {
                    $status = $response instanceof \Illuminate\Http\Client\Response ? $response->status() : 'Error/Timeout';
                    Log::error("Failed to fetch employees from unit {$unit->name}. Status: {$status}");
                }
            }

            return $allEmployees;
        });
    }

    /**
     * Get attendances from all active school units for a specific date.
     */
    public function getSdAttendances(string $date): array
    {
        $activeUnits = SchoolUnit::where('is_active', true)->get();
        $allAttendances = [];

        if ($activeUnits->isEmpty()) {
            return [];
        }

        $responses = Http::pool(function (\Illuminate\Http\Client\Pool $pool) use ($activeUnits, $date) {
            return $activeUnits->map(function ($unit) use ($pool, $date) {
                return $pool->as($unit->id)
                    ->withHeaders([
                        'X-API-TOKEN' => $unit->api_token,
                        'Accept' => 'application/json',
                    ])->timeout(5)->get(rtrim($unit->api_url, '/') . '/attendances', [
                        'date' => $date
                    ]);
            });
        });

        foreach ($activeUnits as $unit) {
            $response = $responses[$unit->id] ?? null;

            if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
                $attendances = $response->json('data') ?? [];
                foreach ($attendances as &$att) {
                    $att['unit_name'] = $unit->name;
                    $att['unit_id'] = $unit->id;
                }
                $allAttendances = array_merge($allAttendances, $attendances);
            } else {
                $status = $response instanceof \Illuminate\Http\Client\Response ? $response->status() : 'Error/Timeout';
                Log::error("Failed to fetch attendances from unit {$unit->name}. Status: {$status}");
            }
        }

        return $allAttendances;
    }
}

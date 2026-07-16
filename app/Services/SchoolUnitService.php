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
        $activeUnits = SchoolUnit::where('is_active', true)->get();
        $allEmployees = [];

        foreach ($activeUnits as $unit) {
            try {
                $response = Http::withHeaders([
                    'X-API-TOKEN' => $unit->api_token,
                    'Accept' => 'application/json',
                ])->timeout(5)->get(rtrim($unit->api_url, '/') . '/employees');

                if ($response->successful()) {
                    $employees = $response->json('data') ?? [];
                    // We can tag each employee with their unit name
                    foreach ($employees as &$emp) {
                        $emp['unit_name'] = $unit->name;
                        $emp['unit_id'] = $unit->id;
                    }
                    $allEmployees = array_merge($allEmployees, $employees);
                } else {
                    Log::error("Failed to fetch employees from unit {$unit->name}. Status: " . $response->status());
                }
            } catch (\Exception $e) {
                Log::error("Exception fetching employees from unit {$unit->name}: " . $e->getMessage());
            }
        }

        return $allEmployees;
    }

    /**
     * Get attendances from all active school units for a specific date.
     */
    public function getSdAttendances(string $date): array
    {
        $activeUnits = SchoolUnit::where('is_active', true)->get();
        $allAttendances = [];

        foreach ($activeUnits as $unit) {
            try {
                $response = Http::withHeaders([
                    'X-API-TOKEN' => $unit->api_token,
                    'Accept' => 'application/json',
                ])->timeout(5)->get(rtrim($unit->api_url, '/') . '/attendances', [
                    'date' => $date
                ]);

                if ($response->successful()) {
                    $attendances = $response->json('data') ?? [];
                    foreach ($attendances as &$att) {
                        $att['unit_name'] = $unit->name;
                        $att['unit_id'] = $unit->id;
                    }
                    $allAttendances = array_merge($allAttendances, $attendances);
                } else {
                    Log::error("Failed to fetch attendances from unit {$unit->name}. Status: " . $response->status());
                }
            } catch (\Exception $e) {
                Log::error("Exception fetching attendances from unit {$unit->name}: " . $e->getMessage());
            }
        }

        return $allAttendances;
    }
}

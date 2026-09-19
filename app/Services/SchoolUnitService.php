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
    public function getAllEmployees(): array
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

                    // Filter only active employees (Active / Aktif)
                    $employees = array_values(array_filter($employees, function ($emp) {
                        if (!isset($emp['status']) || $emp['status'] === null || $emp['status'] === '') {
                            return true;
                        }
                        $status = strtolower(trim((string)$emp['status']));
                        return in_array($status, ['active', 'aktif', '1', 'true']);
                    }));

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
     * Deprecated: Use getAllEmployees() instead.
     */
    public function getSdEmployees(): array
    {
        return $this->getAllEmployees();
    }

    /**
     * Get attendances from all active school units for a specific date.
     */
    public function getAllAttendances(string $date): array
    {
        $cacheKey = 'sd_attendances_' . $date;
        $isToday = ($date === date('Y-m-d'));
        $cacheTime = $isToday ? 60 : 86400; // 1 minute for today, 24 hours for past dates

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, $cacheTime, function() use ($date) {
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
        });
    }

    /**
     * Deprecated: Use getAllAttendances() instead.
     */
    public function getSdAttendances(string $date): array
    {
        return $this->getAllAttendances($date);
    }

    /**
     * Get picket assignments (schedules & approved swaps) from all active school units.
     */
    public function getAllPicketAssignments(?string $startDate = null, ?string $endDate = null): array
    {
        $cacheKey = 'hrd_picket_assignments_' . ($startDate ?: 'all') . '_' . ($endDate ?: 'all');
        
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function() use ($startDate, $endDate) {
            $activeUnits = SchoolUnit::where('is_active', true)->get();
            $picketData = [
                'schedules' => [],
                'swaps' => [],
            ];

            if ($activeUnits->isEmpty()) {
                return $picketData;
            }

            $responses = Http::pool(function (\Illuminate\Http\Client\Pool $pool) use ($activeUnits, $startDate, $endDate) {
                return $activeUnits->map(function ($unit) use ($pool, $startDate, $endDate) {
                    $params = [];
                    if ($startDate && $endDate) {
                        $params['start_date'] = $startDate;
                        $params['end_date'] = $endDate;
                    }
                    return $pool->as($unit->id)
                        ->withHeaders([
                            'X-API-TOKEN' => $unit->api_token,
                            'Accept' => 'application/json',
                        ])->timeout(5)->get(rtrim($unit->api_url, '/') . '/picket-assignments', $params);
                });
            });

            foreach ($activeUnits as $unit) {
                $response = $responses[$unit->id] ?? null;

                if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
                    $unitData = $response->json('data') ?? [];
                    
                    $unitSchedules = $unitData['schedules'] ?? [];
                    foreach ($unitSchedules as &$sched) {
                        $sched['school_unit_id'] = $unit->id;
                        $sched['unit_name'] = $unit->name;
                    }
                    $picketData['schedules'] = array_merge($picketData['schedules'], $unitSchedules);

                    $unitSwaps = $unitData['swaps'] ?? [];
                    foreach ($unitSwaps as &$sw) {
                        $sw['school_unit_id'] = $unit->id;
                        $sw['unit_name'] = $unit->name;
                    }
                    $picketData['swaps'] = array_merge($picketData['swaps'], $unitSwaps);
                } else {
                    $status = $response instanceof \Illuminate\Http\Client\Response ? $response->status() : 'Error/Timeout';
                    Log::warning("Failed to fetch picket assignments from unit {$unit->name}. Status: {$status}");
                }
            }

            return $picketData;
        });
    }
}

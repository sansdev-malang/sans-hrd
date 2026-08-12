<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'remote_leave_id',
        'employee_id',
        'school_unit_id',
        'status_code',
        'gets_presence_bonus',
        'start_date',
        'end_date',
        'status',
        'notes',
        'requires_attendance',
        'requires_approval',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'gets_presence_bonus' => 'boolean',
        'requires_attendance' => 'boolean',
        'requires_approval' => 'boolean',
    ];

    public function schoolUnit()
    {
        return $this->belongsTo(SchoolUnit::class);
    }

    public function getTypeNameAttribute()
    {
        static $unitTypes = [];

        if (!isset($unitTypes[$this->school_unit_id])) {
            $unitTypes[$this->school_unit_id] = [];
            $unit = $this->schoolUnit ?? SchoolUnit::find($this->school_unit_id);
            if ($unit) {
                try {
                    $response = \Illuminate\Support\Facades\Http::timeout(3)->withHeaders([
                        'X-API-TOKEN' => $unit->api_token,
                        'Accept' => 'application/json',
                    ])->get(rtrim($unit->api_url, '/') . '/leave-requests');
                    if ($response->successful()) {
                        foreach ($response->json() as $rL) {
                            $unitTypes[$this->school_unit_id][$rL['id']] = $rL['type'] ?? null;
                        }
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to fetch leave types for unit {$unit->name}: " . $e->getMessage());
                }
            }
        }

        $codeToNameMap = ['S' => 'Sakit', 'I' => 'Izin', 'C' => 'Cuti', 'H' => 'Dinas'];
        return $unitTypes[$this->school_unit_id][$this->remote_leave_id] ?? ($codeToNameMap[$this->status_code] ?? 'Izin');
    }
}

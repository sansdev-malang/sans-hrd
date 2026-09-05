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
        'type',
        'reason',
        'attachment',
        'gets_presence_bonus',
        'start_date',
        'end_date',
        'status',
        'notes',
        'requires_attendance',
        'requires_approval',
        'created_at',
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
        if (!empty($this->attributes['type'])) {
            return $this->attributes['type'];
        }
        $codeToNameMap = ['S' => 'Sakit', 'I' => 'Izin', 'C' => 'Cuti', 'H' => 'Dinas'];
        return $codeToNameMap[$this->status_code] ?? 'Izin';
    }

    public function getReasonAttribute()
    {
        return $this->attributes['reason'] ?? '-';
    }

    public function getAttachmentAttribute()
    {
        $attachment = $this->attributes['attachment'] ?? null;
        if (!$attachment) {
            return null;
        }

        // If it's already a full URL
        if (str_starts_with($attachment, 'http://') || str_starts_with($attachment, 'https://')) {
            return $attachment;
        }

        // Extract relative path if it contains '/storage/'
        $storagePos = strpos($attachment, '/storage/');
        if ($storagePos !== false) {
            $attachment = substr($attachment, $storagePos + strlen('/storage/'));
        }

        // Build URL from SchoolUnit
        $unit = $this->schoolUnit ?? SchoolUnit::find($this->school_unit_id);
        if ($unit && $unit->api_url) {
            $baseUrl = rtrim($unit->api_url, '/');
            $apiPos = strpos($baseUrl, '/api/');
            if ($apiPos !== false) {
                $baseUrl = substr($baseUrl, 0, $apiPos);
            }
            return $baseUrl . '/storage/' . ltrim($attachment, '/');
        }

        return $attachment;
    }
}

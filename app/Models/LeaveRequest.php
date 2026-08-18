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

    public function getRemoteData()
    {
        static $remoteLeaves = [];

        if (!isset($remoteLeaves[$this->school_unit_id])) {
            $remoteLeaves[$this->school_unit_id] = [];
            $unit = $this->schoolUnit ?? SchoolUnit::find($this->school_unit_id);
            if ($unit) {
                try {
                    $response = \Illuminate\Support\Facades\Http::timeout(3)->withHeaders([
                        'X-API-TOKEN' => $unit->api_token,
                        'Accept' => 'application/json',
                    ])->get(rtrim($unit->api_url, '/') . '/leave-requests');
                    if ($response->successful()) {
                        foreach ($response->json() as $rL) {
                            $remoteLeaves[$this->school_unit_id][$rL['id']] = $rL;
                        }
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to fetch remote leaves for unit {$unit->name}: " . $e->getMessage());
                }
            }
        }

        return $remoteLeaves[$this->school_unit_id][$this->remote_leave_id] ?? null;
    }

    public function getTypeNameAttribute()
    {
        $remote = $this->getRemoteData();
        if ($remote && isset($remote['type'])) {
            return $remote['type'];
        }
        $codeToNameMap = ['S' => 'Sakit', 'I' => 'Izin', 'C' => 'Cuti', 'H' => 'Dinas'];
        return $codeToNameMap[$this->status_code] ?? 'Izin';
    }

    public function getReasonAttribute()
    {
        $remote = $this->getRemoteData();
        return $remote['reason'] ?? '-';
    }

    public function getAttachmentAttribute()
    {
        $remote = $this->getRemoteData();
        if ($remote && isset($remote['attachment']) && !empty($remote['attachment'])) {
            $attachment = $remote['attachment'];
            
            // Extract relative path if it contains '/storage/'
            $storagePos = strpos($attachment, '/storage/');
            if ($storagePos !== false) {
                $attachment = substr($attachment, $storagePos + strlen('/storage/'));
            } else {
                $parsed = parse_url($attachment);
                if (isset($parsed['scheme']) && isset($parsed['host'])) {
                    $path = $parsed['path'] ?? '';
                    if (str_starts_with($path, '/storage/')) {
                        $attachment = substr($path, strlen('/storage/'));
                    } else {
                        return $remote['attachment'];
                    }
                }
            }
            
            // Build absolute URL using the SchoolUnit's api_url from Central HRD database
            $unit = $this->schoolUnit ?? SchoolUnit::find($this->school_unit_id);
            if ($unit && $unit->api_url) {
                $baseUrl = rtrim($unit->api_url, '/');
                $apiPos = strpos($baseUrl, '/api/');
                if ($apiPos !== false) {
                    $baseUrl = substr($baseUrl, 0, $apiPos);
                }
                
                $localUrl = $baseUrl . '/storage/' . $attachment;
                
                // If the URL is a local domain (.test)
                if (str_contains($localUrl, '.test')) {
                    // Try to check if the file actually exists on local using a fast cURL HEAD request
                    $ch = curl_init($localUrl);
                    curl_setopt($ch, CURLOPT_NOBODY, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT_MS, 400); // 400ms timeout
                    curl_exec($ch);
                    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    
                    if ($statusCode === 200) {
                        return $localUrl;
                    }
                    
                    // Fallback to production domains if local file is missing
                    return str_replace(
                        ['http://sans-sd.test', 'https://sans-sd.test', 'http://sans-smp.test', 'https://sans-smp.test', 'http://sans-paud.test', 'https://sans-paud.test'],
                        ['https://sd.sans.sch.id', 'https://sd.sans.sch.id', 'https://smp.sans.sch.id', 'https://smp.sans.sch.id', 'https://paud.sans.sch.id', 'https://paud.sans.sch.id'],
                        $localUrl
                    );
                }
                
                return $localUrl;
            }
            
            return $remote['attachment'];
        }
        return null;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    protected $fillable = [
        'zkteco_device_id',
        'uid',
        'timestamp',
        'state',
        'type',
        'local_name',
    ];

    public function device()
    {
        return $this->belongsTo(ZktecoDevice::class, 'zkteco_device_id');
    }
}

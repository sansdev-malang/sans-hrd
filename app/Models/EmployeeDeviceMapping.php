<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeDeviceMapping extends Model
{
    protected $fillable = ['zkteco_uid', 'zkteco_device_id'];

    public function device()
    {
        return $this->belongsTo(ZktecoDevice::class, 'zkteco_device_id');
    }
}

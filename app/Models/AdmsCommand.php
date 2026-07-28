<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdmsCommand extends Model
{
    protected $fillable = [
        'zkteco_device_id',
        'command_string',
        'status',
    ];

    public function device()
    {
        return $this->belongsTo(ZktecoDevice::class, 'zkteco_device_id');
    }
}

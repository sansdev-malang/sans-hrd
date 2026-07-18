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
        'type',
        'start_date',
        'end_date',
        'reason',
        'status',
        'notes',
        'attachment',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function schoolUnit()
    {
        return $this->belongsTo(SchoolUnit::class);
    }
}

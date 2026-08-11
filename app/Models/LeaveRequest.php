<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'remote_leave_id',
        'employee_id',
        'employee_name',
        'school_unit_id',
        'type',
        'status_code',
        'gets_presence_bonus',
        'start_date',
        'end_date',
        'reason',
        'status',
        'notes',
        'attachment',
        'processed_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'gets_presence_bonus' => 'boolean',
    ];

    public function schoolUnit()
    {
        return $this->belongsTo(SchoolUnit::class);
    }
}

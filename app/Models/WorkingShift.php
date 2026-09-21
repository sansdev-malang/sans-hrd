<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkingShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'short_code',
        'is_shift',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_shift' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to only include active shifts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function details()
    {
        return $this->hasMany(WorkingShiftDetail::class);
    }

    public function employeeShifts()
    {
        return $this->hasMany(EmployeeWorkingShift::class);
    }
}

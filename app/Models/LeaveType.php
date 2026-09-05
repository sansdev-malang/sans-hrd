<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'status_code',
        'target_unit',
        'requires_attendance',
        'requires_approval',
        'gets_presence_bonus',
    ];

    protected $casts = [
        'requires_attendance' => 'boolean',
        'requires_approval' => 'boolean',
        'gets_presence_bonus' => 'boolean',
    ];

    /**
     * Check if this leave type applies to a given school unit.
     */
    public function appliesToUnit($unit)
    {
        if ($this->target_unit === 'all' || empty($this->target_unit)) {
            return true;
        }

        $unitCode = is_object($unit) ? strtolower($unit->code ?? $unit->name) : strtolower($unit);
        $target = strtolower($this->target_unit);

        if ($target === $unitCode) {
            return true;
        }

        $targets = array_map('trim', explode(',', $target));
        return in_array($unitCode, $targets);
    }
}

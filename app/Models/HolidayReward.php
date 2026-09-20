<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HolidayReward extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'school_unit_ids',
        'employee_ids',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'school_unit_ids' => 'array',
        'employee_ids' => 'array',
    ];

    /**
     * Check if this reward holiday applies to a given date.
     */
    public function appliesToDate(string|Carbon $date): bool
    {
        $dateStr = $date instanceof Carbon ? $date->format('Y-m-d') : substr($date, 0, 10);
        $startStr = $this->start_date instanceof Carbon ? $this->start_date->format('Y-m-d') : substr($this->start_date, 0, 10);
        $endStr = $this->end_date instanceof Carbon ? $this->end_date->format('Y-m-d') : substr($this->end_date, 0, 10);

        return $dateStr >= $startStr && $dateStr <= $endStr;
    }

    /**
     * Check if this reward holiday applies to a specific employee in a unit.
     */
    public function appliesToEmployee(int|string|null $unitId, int|string|null $empId): bool
    {
        $unitIds = $this->school_unit_ids ?? [];
        if (!empty($unitIds)) {
            $unitIdsStr = array_map('strval', $unitIds);
            if (!in_array((string) $unitId, $unitIdsStr, true)) {
                return false;
            }
        }

        $empIds = $this->employee_ids ?? [];
        // If employee list is empty, applies to ALL employees in the selected units
        if (empty($empIds)) {
            return true;
        }

        $compositeKey = $unitId . '_' . $empId;
        $empIdsStr = array_map('strval', $empIds);

        return in_array((string) $compositeKey, $empIdsStr, true)
            || in_array((string) $empId, $empIdsStr, true);
    }
}

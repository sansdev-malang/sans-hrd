<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year',
        'semester',
        'employee_id',
        'unit_id',
        'score_pedagogik',
        'score_kepribadian',
        'score_sosial',
        'score_profesional',
        'score_discipline',
        'final_score',
        'predicate',
        'recommendations',
    ];

    /**
     * Get the school unit associated with the performance report.
     */
    public function schoolUnit()
    {
        return $this->belongsTo(SchoolUnit::class, 'unit_id');
    }
}

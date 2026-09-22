<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payslip extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'school_unit_id',
        'period',
        'file_path',
        'original_filename',
        'attachment_path',
        'original_attachment_name',
    ];

    public function schoolUnit()
    {
        return $this->belongsTo(SchoolUnit::class);
    }
}

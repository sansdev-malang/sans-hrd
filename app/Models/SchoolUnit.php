<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'api_url',
        'api_token',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}

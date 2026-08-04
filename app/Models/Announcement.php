<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'content',
        'category',
        'publish_date',
        'expiry_date',
        'is_active',
        'attachment',
        'target_units',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'publish_date' => 'datetime',
        'expiry_date' => 'datetime',
        'target_units' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

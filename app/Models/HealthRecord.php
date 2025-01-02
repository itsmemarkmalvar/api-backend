<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HealthRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'baby_id',
        'record_date',
        'category',
        'title',
        'description',
        'severity',
        'treatment',
        'notes',
        'is_ongoing',
        'resolved_at',
        'type'
    ];

    protected $casts = [
        'record_date' => 'datetime',
        'resolved_at' => 'datetime',
        'is_ongoing' => 'boolean'
    ];

    public function baby()
    {
        return $this->belongsTo(Baby::class);
    }
} 
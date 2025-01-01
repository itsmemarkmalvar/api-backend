<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedingLog extends Model
{
    protected $fillable = [
        'baby_id',
        'type',
        'start_time',
        'duration',
        'amount',
        'breast_side',
        'food_type',
        'notes'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'duration' => 'integer',
        'amount' => 'decimal:2'
    ];

    public function baby(): BelongsTo
    {
        return $this->belongsTo(Baby::class);
    }
} 
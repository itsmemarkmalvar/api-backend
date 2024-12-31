<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{
    protected $fillable = [
        'baby_id',
        'category',
        'title',
        'completed',
        'completed_at',
        'notes'
    ];

    protected $casts = [
        'completed' => 'boolean',
        'completed_at' => 'datetime'
    ];

    public function baby()
    {
        return $this->belongsTo(Baby::class);
    }
} 
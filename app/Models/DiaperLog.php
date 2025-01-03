<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class DiaperLog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'baby_id',
        'type',
        'time',
        'notes',
        'color',
        'consistency',
        'rash_noticed',
        'rash_description'
    ];

    protected $casts = [
        'time' => 'datetime',
        'rash_noticed' => 'boolean',
    ];

    protected $appends = ['formatted_time'];

    public function baby()
    {
        return $this->belongsTo(Baby::class);
    }

    public function getFormattedTimeAttribute()
    {
        return Carbon::parse($this->time)->format('Y-m-d H:i:s');
    }
} 
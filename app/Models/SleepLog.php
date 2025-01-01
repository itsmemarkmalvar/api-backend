<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SleepLog extends Model
{
    protected $fillable = [
        'baby_id',
        'start_time',
        'end_time',
        'duration_minutes',
        'quality',
        'location',
        'is_nap',
        'notes'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'duration_minutes' => 'integer',
        'is_nap' => 'boolean'
    ];

    // Relationships
    public function baby()
    {
        return $this->belongsTo(Baby::class);
    }

    // Calculate duration when end_time is set
    public function setEndTimeAttribute($value)
    {
        $this->attributes['end_time'] = $value;
        if ($value && $this->start_time) {
            $start = Carbon::parse($this->start_time);
            $end = Carbon::parse($value);
            $this->attributes['duration_minutes'] = $end->diffInMinutes($start);
        }
    }

    // Scopes for common queries
    public function scopeForBaby($query, $babyId)
    {
        return $query->where('baby_id', $babyId);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_time', [$startDate, $endDate]);
    }

    public function scopeNapsOnly($query)
    {
        return $query->where('is_nap', true);
    }

    public function scopeNightSleepOnly($query)
    {
        return $query->where('is_nap', false);
    }
} 
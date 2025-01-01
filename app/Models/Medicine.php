<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medicine extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'baby_id',
        'name',
        'instructions',
        'side_effects',
        'form',
        'strength',
        'start_date',
        'end_date',
        'is_active',
        'notes'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean'
    ];

    public function baby()
    {
        return $this->belongsTo(Baby::class);
    }

    public function schedules()
    {
        return $this->hasMany(MedicineSchedule::class);
    }

    public function logs()
    {
        return $this->hasMany(MedicineLog::class);
    }
} 
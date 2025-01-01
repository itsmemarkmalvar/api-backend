<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicineSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'medicine_id',
        'time',
        'dosage',
        'frequency',
        'days_of_week',
        'days_of_month',
        'is_active',
        'notes'
    ];

    protected $casts = [
        'time' => 'datetime',
        'days_of_week' => 'array',
        'days_of_month' => 'array',
        'is_active' => 'boolean'
    ];

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function logs()
    {
        return $this->hasMany(MedicineLog::class, 'schedule_id');
    }
} 
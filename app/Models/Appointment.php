<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'baby_id',
        'appointment_date',
        'doctor_name',
        'clinic_location',
        'purpose',
        'notes',
        'status',
        'reminder_enabled',
        'reminder_minutes_before'
    ];

    protected $casts = [
        'appointment_date' => 'datetime',
        'reminder_enabled' => 'boolean',
        'reminder_minutes_before' => 'integer'
    ];

    public function baby()
    {
        return $this->belongsTo(Baby::class);
    }
} 
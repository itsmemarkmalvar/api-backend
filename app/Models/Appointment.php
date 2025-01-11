<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HandlesTimezones;
use Carbon\Carbon;

class Appointment extends Model
{
    use HasFactory, SoftDeletes, HandlesTimezones;

    protected $fillable = [
        'baby_id',
        'appointment_date',
        'doctor_name',
        'clinic_location',
        'purpose',
        'notes',
        'status',
        'reminder_enabled',
        'reminder_minutes_before',
        'timezone'
    ];

    protected $casts = [
        'appointment_date' => 'datetime',
        'reminder_enabled' => 'boolean',
        'reminder_minutes_before' => 'integer'
    ];

    protected function serializeDate($date)
    {
        return $date->setTimezone('UTC')->format('Y-m-d\TH:i:s.u\Z');
    }

    public function getAppointmentDateAttribute($value)
    {
        if (!$value) return null;
        return Carbon::parse($value)->setTimezone($this->timezone ?? 'UTC');
    }

    public function setAppointmentDateAttribute($value)
    {
        if (!$value) {
            $this->attributes['appointment_date'] = null;
            return;
        }
        
        // Convert to UTC for storage
        $date = Carbon::parse($value, $this->timezone ?? 'UTC');
        $this->attributes['appointment_date'] = $date->setTimezone('UTC');
    }

    public function baby()
    {
        return $this->belongsTo(Baby::class);
    }
} 
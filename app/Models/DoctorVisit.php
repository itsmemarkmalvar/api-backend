<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DoctorVisit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'baby_id',
        'visit_date',
        'doctor_name',
        'clinic_location',
        'reason_for_visit',
        'diagnosis',
        'prescription',
        'notes',
        'follow_up_instructions',
        'next_visit_date'
    ];

    protected $casts = [
        'visit_date' => 'datetime',
        'next_visit_date' => 'datetime'
    ];

    public function baby()
    {
        return $this->belongsTo(Baby::class);
    }
} 
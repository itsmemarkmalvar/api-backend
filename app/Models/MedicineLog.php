<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicineLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'medicine_id',
        'schedule_id',
        'taken_at',
        'dosage_taken',
        'skipped',
        'skip_reason',
        'notes',
        'side_effects_noted'
    ];

    protected $casts = [
        'taken_at' => 'datetime',
        'skipped' => 'boolean'
    ];

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function schedule()
    {
        return $this->belongsTo(MedicineSchedule::class);
    }
} 
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Baby extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'gender',
        'birth_date',
        'height',
        'weight',
        'head_size',
        'photo_url',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'height' => 'decimal:2',
        'weight' => 'decimal:2',
        'head_size' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define relationships with cascade delete
    public function growthRecords()
    {
        return $this->hasMany(Growth::class)->onDelete('cascade');
    }

    public function feedingLogs()
    {
        return $this->hasMany(FeedingLog::class)->onDelete('cascade');
    }

    public function sleepLogs()
    {
        return $this->hasMany(SleepLog::class)->onDelete('cascade');
    }

    public function diaperLogs()
    {
        return $this->hasMany(DiaperLog::class)->onDelete('cascade');
    }

    public function healthRecords()
    {
        return $this->hasMany(HealthRecord::class)->onDelete('cascade');
    }

    public function doctorVisits()
    {
        return $this->hasMany(DoctorVisit::class)->onDelete('cascade');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class)->onDelete('cascade');
    }

    public function vaccinations()
    {
        return $this->hasMany(Vaccination::class)->onDelete('cascade');
    }

    public function milestones()
    {
        return $this->hasMany(Milestone::class)->onDelete('cascade');
    }

    public function medicines()
    {
        return $this->hasMany(Medicine::class)->onDelete('cascade');
    }

    public function symptoms()
    {
        return $this->hasMany(Symptom::class)->onDelete('cascade');
    }

    /**
     * Boot function to handle cascade deletes
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($baby) {
            // Delete all related records
            $baby->growthRecords()->delete();
            $baby->feedingLogs()->delete();
            $baby->sleepLogs()->delete();
            $baby->diaperLogs()->delete();
            $baby->healthRecords()->delete();
            $baby->doctorVisits()->delete();
            $baby->appointments()->delete();
            $baby->vaccinations()->delete();
            $baby->milestones()->delete();
            $baby->medicines()->delete();
            $baby->symptoms()->delete();
        });
    }
} 
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VaccinationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'baby_id',
        'vaccine_id',
        'vaccine_name',
        'age_group',
        'status',
        'scheduled_date',
        'given_at',
        'administered_by',
        'administered_at',
        'notes'
    ];

    protected $casts = [
        'given_at' => 'datetime',
        'scheduled_date' => 'datetime',
    ];

    /**
     * Get the baby that owns this vaccination log.
     */
    public function baby()
    {
        return $this->belongsTo(Baby::class);
    }

    /**
     * Get the user that owns this vaccination log.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 
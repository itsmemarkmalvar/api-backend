<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Symptom extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'baby_id',
        'name',
        'onset_date',
        'severity',
        'description',
        'triggers',
        'related_conditions',
        'notes',
        'resolved_date'
    ];

    protected $casts = [
        'onset_date' => 'datetime',
        'resolved_date' => 'datetime'
    ];

    public function baby()
    {
        return $this->belongsTo(Baby::class);
    }
} 
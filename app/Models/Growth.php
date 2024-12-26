<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Growth extends Model
{
    use HasFactory;

    protected $table = 'growth_records';

    protected $fillable = [
        'baby_id',
        'height',
        'weight',
        'head_size',
        'date_recorded',
        'notes'
    ];

    protected $casts = [
        'date_recorded' => 'date'
    ];

    public function baby()
    {
        return $this->belongsTo(Baby::class);
    }
} 
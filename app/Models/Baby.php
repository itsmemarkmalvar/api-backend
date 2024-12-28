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
} 
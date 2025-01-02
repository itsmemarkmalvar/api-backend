<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DevelopmentTip extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'content',
        'category',
        'min_age_months',
        'max_age_months',
        'source',
        'additional_resources'
    ];

    protected $casts = [
        'min_age_months' => 'integer',
        'max_age_months' => 'integer',
        'additional_resources' => 'array'
    ];
} 
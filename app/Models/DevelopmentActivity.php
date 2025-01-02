<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DevelopmentActivity extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'category',
        'min_age_months',
        'max_age_months',
        'benefits',
        'instructions'
    ];

    protected $casts = [
        'min_age_months' => 'integer',
        'max_age_months' => 'integer',
        'benefits' => 'array',
        'instructions' => 'array'
    ];

    public function progress()
    {
        return $this->hasMany(ActivityProgress::class, 'activity_id');
    }
} 
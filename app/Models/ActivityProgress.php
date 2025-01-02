<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityProgress extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'baby_id',
        'activity_id',
        'completed_at',
        'notes'
    ];

    protected $casts = [
        'completed_at' => 'datetime'
    ];

    public function baby()
    {
        return $this->belongsTo(Baby::class);
    }

    public function activity()
    {
        return $this->belongsTo(DevelopmentActivity::class, 'activity_id');
    }
} 
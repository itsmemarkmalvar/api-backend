<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Growth;
use App\Models\Baby;

class GrowthSeeder extends Seeder
{
    public function run()
    {
        $baby = Baby::first();
        
        if ($baby) {
            Growth::create([
                'baby_id' => $baby->id,
                'height' => 50,
                'weight' => 3.5,
                'head_size' => 35,
                'date_recorded' => now(),
                'notes' => 'Initial measurement'
            ]);
        }
    }
} 
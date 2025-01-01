<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sleep_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('baby_id')->constrained('babies')->onDelete('cascade');
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->integer('duration_minutes')->nullable(); // Calculated field
            $table->enum('quality', ['poor', 'fair', 'good', 'excellent'])->nullable();
            $table->enum('location', ['crib', 'bed', 'stroller', 'car', 'other'])->nullable();
            $table->boolean('is_nap')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes for faster queries
            $table->index(['baby_id', 'start_time']);
            $table->index(['baby_id', 'is_nap']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sleep_logs');
    }
}; 
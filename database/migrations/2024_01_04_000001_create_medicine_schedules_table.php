<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('medicine_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained()->onDelete('cascade');
            $table->time('time');
            $table->string('dosage'); // e.g., "1 tablet", "5ml"
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'as_needed']);
            $table->json('days_of_week')->nullable(); // For weekly schedules, store days [1,3,5] etc.
            $table->json('days_of_month')->nullable(); // For monthly schedules
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('medicine_schedules');
    }
}; 
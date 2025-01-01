<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('medicine_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained()->onDelete('cascade');
            $table->foreignId('schedule_id')->nullable()->constrained('medicine_schedules')->onDelete('set null');
            $table->dateTime('taken_at');
            $table->string('dosage_taken');
            $table->boolean('skipped')->default(false);
            $table->string('skip_reason')->nullable();
            $table->text('notes')->nullable();
            $table->text('side_effects_noted')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('medicine_logs');
    }
}; 
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vaccinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('baby_id')->constrained()->onDelete('cascade');
            $table->string('vaccine_id'); // e.g., 'bcg', 'hepb1', etc.
            $table->string('name');
            $table->string('age_group');
            $table->boolean('completed')->default(false);
            $table->dateTime('completed_date')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('reminder_enabled')->default(true);
            $table->integer('reminder_days')->default(7);
            $table->time('reminder_time')->default('09:00');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vaccinations');
    }
}; 
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('feeding_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('baby_id')->constrained('babies')->onDelete('cascade');
            $table->enum('type', ['breast', 'bottle', 'solid']);
            $table->dateTime('start_time');
            $table->integer('duration')->nullable(); // in minutes, for breast/bottle feeding
            $table->decimal('amount', 8, 2)->nullable(); // for bottle feeding (in ml) or solid food
            $table->enum('breast_side', ['left', 'right', 'both'])->nullable(); // for breastfeeding
            $table->string('food_type')->nullable(); // for solid food
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['baby_id', 'start_time']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('feeding_logs');
    }
}; 
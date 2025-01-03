<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('diaper_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('baby_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['wet', 'dirty', 'both']);
            $table->timestamp('time');
            $table->text('notes')->nullable();
            $table->string('color')->nullable();
            $table->string('consistency')->nullable();
            $table->boolean('rash_noticed')->default(false);
            $table->text('rash_description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('diaper_logs');
    }
}; 
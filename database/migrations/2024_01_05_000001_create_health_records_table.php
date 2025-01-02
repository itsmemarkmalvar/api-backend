<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('health_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('baby_id')->constrained()->onDelete('cascade');
            $table->dateTime('record_date');
            $table->enum('category', ['general', 'vaccination', 'medication', 'allergy', 'surgery', 'test_result', 'other']);
            $table->string('title');
            $table->text('description');
            $table->string('severity')->nullable(); // mild, moderate, severe
            $table->text('treatment')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_ongoing')->default(false);
            $table->dateTime('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('health_records');
    }
}; 
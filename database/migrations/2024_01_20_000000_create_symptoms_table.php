<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('symptoms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('baby_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->timestamp('onset_date');
            $table->enum('severity', ['mild', 'moderate', 'severe']);
            $table->text('description');
            $table->text('triggers')->nullable();
            $table->text('related_conditions')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('resolved_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('symptoms');
    }
}; 
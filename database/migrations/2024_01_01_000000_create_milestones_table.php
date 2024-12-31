<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('baby_id')->constrained()->onDelete('cascade');
            $table->string('category');
            $table->string('title');
            $table->boolean('completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('milestones');
    }
}; 
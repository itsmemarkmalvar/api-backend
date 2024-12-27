<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('growth_milestones')) {
            Schema::create('growth_milestones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('baby_id')->constrained()->onDelete('cascade');
                $table->string('milestone');
                $table->date('achieved_date')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('growth_milestones');
    }
}; 
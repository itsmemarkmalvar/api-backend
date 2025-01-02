<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('development_tips', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->enum('category', ['physical', 'cognitive', 'social', 'language', 'general']);
            $table->integer('min_age_months');
            $table->integer('max_age_months');
            $table->string('source')->nullable();
            $table->json('additional_resources')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('development_tips');
    }
}; 
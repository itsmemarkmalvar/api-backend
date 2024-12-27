<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('babies')) {
            Schema::create('babies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('name');
                $table->enum('gender', ['male', 'female']);
                $table->date('birth_date');
                $table->decimal('height', 5, 2);
                $table->decimal('weight', 5, 2);
                $table->decimal('head_size', 5, 2);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('babies');
    }
}; 
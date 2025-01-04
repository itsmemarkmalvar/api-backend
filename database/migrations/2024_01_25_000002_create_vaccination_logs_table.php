<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vaccination_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('baby_id')->constrained()->onDelete('cascade');
            $table->string('vaccine_id');  // Reference to the vaccine (e.g., 'bcg', 'hepb1')
            $table->string('vaccine_name'); // Name of the vaccine
            $table->string('age_group');    // Age group when vaccine should be given
            $table->dateTime('given_at');   // When the vaccine was administered
            $table->string('administered_by')->nullable(); // Doctor/nurse name
            $table->string('administered_at')->nullable(); // Clinic/hospital name
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Add indexes for better performance
            $table->index(['baby_id', 'vaccine_id']);
            $table->index('given_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('vaccination_logs');
    }
}; 
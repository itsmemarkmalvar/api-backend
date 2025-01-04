<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('vaccination_logs', function (Blueprint $table) {
            // Add status field after vaccine_name
            $table->enum('status', ['scheduled', 'completed'])->after('vaccine_name')->default('completed');
            
            // Add scheduled_date field after given_at
            $table->dateTime('scheduled_date')->nullable()->after('given_at');
            
            // Make given_at nullable since it won't be set for scheduled vaccinations
            $table->dateTime('given_at')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('vaccination_logs', function (Blueprint $table) {
            $table->dropColumn(['status', 'scheduled_date']);
            $table->dateTime('given_at')->nullable(false)->change();
        });
    }
}; 
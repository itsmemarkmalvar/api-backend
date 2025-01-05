<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // First, update existing records that should be scheduled
        DB::table('vaccination_logs')
            ->whereNull('given_at')
            ->whereNotNull('scheduled_date')
            ->update(['status' => 'scheduled']);

        // Change the default value for the status column
        Schema::table('vaccination_logs', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->enum('status', ['scheduled', 'completed'])->after('vaccine_name')->default('scheduled');
        });
    }

    public function down()
    {
        Schema::table('vaccination_logs', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->enum('status', ['scheduled', 'completed'])->after('vaccine_name')->default('completed');
        });
    }
}; 
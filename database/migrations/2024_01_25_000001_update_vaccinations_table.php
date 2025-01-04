<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('vaccinations', function (Blueprint $table) {
            // Add indexes for better query performance
            $table->index('baby_id');
            $table->index('vaccine_id');
            $table->index(['baby_id', 'vaccine_id']); // Composite index for lookups
            $table->index('age_group');
            
            // Add new fields for better tracking
            $table->string('status')->default('pending')->after('completed'); // pending, completed, skipped, delayed
            $table->dateTime('due_date')->nullable()->after('age_group'); // Calculate based on baby's birth date and age_group
            $table->dateTime('scheduled_date')->nullable()->after('due_date'); // For rescheduled vaccinations
            $table->text('skip_reason')->nullable()->after('notes'); // If vaccination is skipped
            $table->string('administered_by')->nullable()->after('completed_date'); // Doctor/nurse name
            $table->string('administered_at')->nullable()->after('administered_by'); // Clinic/hospital name
            $table->string('batch_number')->nullable()->after('administered_at'); // Vaccine batch number
            $table->text('side_effects')->nullable()->after('notes'); // Record any side effects
            
            // Add fields for next dose tracking
            $table->dateTime('next_dose_due')->nullable()->after('completed_date');
            $table->string('next_dose_vaccine_id')->nullable()->after('next_dose_due');
        });
    }

    public function down()
    {
        Schema::table('vaccinations', function (Blueprint $table) {
            // Remove indexes
            $table->dropIndex(['baby_id']);
            $table->dropIndex(['vaccine_id']);
            $table->dropIndex(['baby_id', 'vaccine_id']);
            $table->dropIndex(['age_group']);
            
            // Remove new columns
            $table->dropColumn([
                'status',
                'due_date',
                'scheduled_date',
                'skip_reason',
                'administered_by',
                'administered_at',
                'batch_number',
                'side_effects',
                'next_dose_due',
                'next_dose_vaccine_id'
            ]);
        });
    }
}; 
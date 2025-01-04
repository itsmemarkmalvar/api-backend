<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Vaccination extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'baby_id',
        'vaccine_id',
        'name',
        'age_group',
        'completed',
        'status',
        'due_date',
        'scheduled_date',
        'completed_date',
        'administered_by',
        'administered_at',
        'batch_number',
        'notes',
        'side_effects',
        'skip_reason',
        'next_dose_due',
        'next_dose_vaccine_id',
        'reminder_enabled',
        'reminder_days',
        'reminder_time'
    ];

    protected $casts = [
        'completed' => 'boolean',
        'completed_date' => 'datetime',
        'due_date' => 'datetime',
        'scheduled_date' => 'datetime',
        'next_dose_due' => 'datetime',
        'reminder_enabled' => 'boolean',
        'reminder_days' => 'integer',
        'reminder_time' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_SKIPPED = 'skipped';
    const STATUS_DELAYED = 'delayed';

    public function baby()
    {
        return $this->belongsTo(Baby::class);
    }

    // Calculate and set due date based on baby's birth date and age group
    public function calculateDueDate()
    {
        $baby = $this->baby;
        if (!$baby) return null;

        $birthDate = Carbon::parse($baby->birth_date);
        
        switch ($this->age_group) {
            case 'Birth':
                $dueDate = $birthDate;
                break;
            case '6 Weeks':
                $dueDate = $birthDate->copy()->addWeeks(6);
                break;
            case '10 Weeks':
                $dueDate = $birthDate->copy()->addWeeks(10);
                break;
            case '14 Weeks':
                $dueDate = $birthDate->copy()->addWeeks(14);
                break;
            case '6 Months':
                $dueDate = $birthDate->copy()->addMonths(6);
                break;
            case '9 Months':
                $dueDate = $birthDate->copy()->addMonths(9);
                break;
            default:
                $dueDate = null;
        }

        $this->due_date = $dueDate;
        return $dueDate;
    }

    // Check if vaccination is overdue
    public function isOverdue()
    {
        if (!$this->due_date || $this->completed) {
            return false;
        }
        return Carbon::now()->isAfter($this->due_date);
    }

    // Get days until due
    public function daysUntilDue()
    {
        if (!$this->due_date) {
            return null;
        }
        return Carbon::now()->diffInDays($this->due_date, false);
    }

    // Set next dose information
    public function setNextDose($nextVaccineId, $interval)
    {
        $this->next_dose_vaccine_id = $nextVaccineId;
        $this->next_dose_due = $this->completed_date 
            ? Carbon::parse($this->completed_date)->add($interval)
            : null;
        $this->save();
    }

    // Scope for upcoming vaccinations
    public function scopeUpcoming($query)
    {
        return $query->where('completed', false)
            ->whereNotNull('due_date')
            ->where('due_date', '>=', Carbon::now())
            ->orderBy('due_date');
    }

    // Scope for overdue vaccinations
    public function scopeOverdue($query)
    {
        return $query->where('completed', false)
            ->whereNotNull('due_date')
            ->where('due_date', '<', Carbon::now())
            ->orderBy('due_date');
    }

    // Scope for completed vaccinations
    public function scopeCompleted($query)
    {
        return $query->where('completed', true)
            ->orderBy('completed_date', 'desc');
    }
} 
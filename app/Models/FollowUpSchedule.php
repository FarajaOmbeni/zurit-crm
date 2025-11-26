<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FollowUpSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'task_id',
        'type',
        'scheduled_at',
        'completed_at',
        'interval_days',
        'is_recurring',
        'next_follow_up_date',
        'notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'next_follow_up_date' => 'datetime',
        'is_recurring' => 'boolean',
    ];

    /**
     * Get the lead that this follow-up schedule belongs to.
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the task associated with this follow-up schedule.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Schedule the next follow-up date based on interval.
     */
    public function scheduleNext(): void
    {
        if ($this->is_recurring && $this->interval_days) {
            $this->update([
                'next_follow_up_date' => now()->addDays($this->interval_days),
                'scheduled_at' => now()->addDays($this->interval_days),
            ]);
        }
    }

    /**
     * Mark the follow-up as completed.
     */
    public function markCompleted(): void
    {
        $this->update([
            'completed_at' => now(),
        ]);

        // If recurring, schedule the next one
        if ($this->is_recurring) {
            $this->scheduleNext();
        }
    }
}

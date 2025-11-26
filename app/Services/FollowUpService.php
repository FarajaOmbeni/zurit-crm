<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\FollowUpSchedule;
use App\Models\Lead;
use App\Models\Task;
use Illuminate\Support\Facades\Log;

class FollowUpService
{
    /**
     * Schedule initial follow-up (2 days after contact).
     * Called when a lead moves to "follow_ups" status or when initial contact is made.
     */
    public function scheduleInitialFollowUp(Lead $lead, int $userId): FollowUpSchedule
    {
        // Check if initial follow-up already exists
        $existingSchedule = FollowUpSchedule::where('lead_id', $lead->id)
            ->where('type', 'initial_email')
            ->whereNull('completed_at')
            ->first();

        if ($existingSchedule) {
            return $existingSchedule;
        }

        // Create initial follow-up task
        $task = Task::create([
            'lead_id' => $lead->id,
            'created_by' => $userId,
            'type' => 'follow_up',
            'title' => "Follow-up: {$lead->company}",
            'description' => "Initial follow-up email for {$lead->company}. Contact: {$lead->name} ({$lead->email})",
            'due_date' => now()->addDays(2),
            'priority' => 'medium',
            'status' => 'pending',
        ]);

        // Create follow-up schedule
        $schedule = FollowUpSchedule::create([
            'lead_id' => $lead->id,
            'task_id' => $task->id,
            'type' => 'initial_email',
            'scheduled_at' => now()->addDays(2),
            'interval_days' => null,
            'is_recurring' => false,
            'notes' => 'Initial follow-up scheduled 2 days after contact',
        ]);

        Log::info("Initial follow-up scheduled for lead {$lead->id} ({$lead->company})", [
            'lead_id' => $lead->id,
            'task_id' => $task->id,
            'scheduled_at' => $schedule->scheduled_at,
        ]);

        return $schedule;
    }

    /**
     * Schedule recurring follow-ups (every 7 days).
     * Called after initial follow-up is completed or when lead is in "follow_ups" status.
     */
    public function scheduleRecurringFollowUps(Lead $lead, int $userId): FollowUpSchedule
    {
        // Don't schedule if lead is won or lost
        if (in_array($lead->status, ['won', 'lost'])) {
            return null;
        }

        // Check if recurring follow-up already exists
        $existingSchedule = FollowUpSchedule::where('lead_id', $lead->id)
            ->where('type', 'follow_up_email')
            ->where('is_recurring', true)
            ->whereNull('completed_at')
            ->first();

        if ($existingSchedule) {
            return $existingSchedule;
        }

        // Create recurring follow-up task
        $task = Task::create([
            'lead_id' => $lead->id,
            'created_by' => $userId,
            'type' => 'follow_up',
            'title' => "Recurring Follow-up: {$lead->company}",
            'description' => "Recurring follow-up email for {$lead->company}. Contact: {$lead->name} ({$lead->email})",
            'due_date' => now()->addDays(7),
            'priority' => 'medium',
            'status' => 'pending',
        ]);

        // Create recurring follow-up schedule
        $schedule = FollowUpSchedule::create([
            'lead_id' => $lead->id,
            'task_id' => $task->id,
            'type' => 'follow_up_email',
            'scheduled_at' => now()->addDays(7),
            'interval_days' => 7,
            'is_recurring' => true,
            'next_follow_up_date' => now()->addDays(7),
            'notes' => 'Recurring follow-up scheduled every 7 days until deal is closed',
        ]);

        Log::info("Recurring follow-up scheduled for lead {$lead->id} ({$lead->company})", [
            'lead_id' => $lead->id,
            'task_id' => $task->id,
            'interval_days' => 7,
            'scheduled_at' => $schedule->scheduled_at,
        ]);

        return $schedule;
    }

    /**
     * Handle lead status change to "follow_ups".
     * Automatically creates initial and recurring follow-up schedules.
     */
    public function handleLeadStatusChange(Lead $lead, string $oldStatus, string $newStatus): void
    {
        // Only process if status changed to "follow_ups"
        if ($newStatus !== 'follow_ups' || $oldStatus === $newStatus) {
            return;
        }

        // Get the user who added the lead (or use system user)
        $userId = $lead->added_by ?? 1;

        // Schedule initial follow-up (2 days)
        $this->scheduleInitialFollowUp($lead, $userId);

        // Schedule recurring follow-ups (7 days)
        $this->scheduleRecurringFollowUps($lead, $userId);
    }

    /**
     * Mark follow-up as completed when activity is logged.
     * Called when an activity (call, email, meeting) is created for a lead.
     */
    public function markFollowUpCompleted(Activity $activity): void
    {
        $lead = $activity->lead;

        // Find active follow-up schedules for this lead
        $schedules = FollowUpSchedule::where('lead_id', $lead->id)
            ->whereNull('completed_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        foreach ($schedules as $schedule) {
            // Mark as completed
            $schedule->markCompleted();

            // If it's recurring and lead is still active, create next follow-up task
            if ($schedule->is_recurring && $schedule->interval_days && !in_array($lead->status, ['won', 'lost'])) {
                $this->createNextRecurringFollowUp($lead, $schedule);
            }

            Log::info("Follow-up marked as completed", [
                'schedule_id' => $schedule->id,
                'lead_id' => $lead->id,
                'activity_id' => $activity->id,
            ]);
        }
    }

    /**
     * Create next recurring follow-up task.
     */
    protected function createNextRecurringFollowUp(Lead $lead, FollowUpSchedule $completedSchedule): void
    {
        // Don't create if lead is won or lost
        if (in_array($lead->status, ['won', 'lost'])) {
            return;
        }

        // Calculate next scheduled date
        $nextDate = $completedSchedule->next_follow_up_date ?? now()->addDays($completedSchedule->interval_days);

        // Create new task for next follow-up
        $task = Task::create([
            'lead_id' => $lead->id,
            'created_by' => $lead->added_by,
            'type' => 'follow_up',
            'title' => "Recurring Follow-up: {$lead->company}",
            'description' => "Recurring follow-up email for {$lead->company}. Contact: {$lead->name} ({$lead->email})",
            'due_date' => $nextDate,
            'priority' => 'medium',
            'status' => 'pending',
        ]);

        // Update schedule with next follow-up date
        $completedSchedule->update([
            'task_id' => $task->id,
            'scheduled_at' => $nextDate,
            'next_follow_up_date' => $nextDate->copy()->addDays($completedSchedule->interval_days),
        ]);

        Log::info("Next recurring follow-up created", [
            'lead_id' => $lead->id,
            'task_id' => $task->id,
            'next_date' => $nextDate,
        ]);
    }

    /**
     * Cancel all follow-ups for a lead when deal is closed (won or lost).
     */
    public function cancelFollowUpsForClosedDeal(Lead $lead): void
    {
        // Cancel all pending follow-up schedules
        $schedules = FollowUpSchedule::where('lead_id', $lead->id)
            ->whereNull('completed_at')
            ->get();

        foreach ($schedules as $schedule) {
            // Mark as completed (effectively canceling)
            $schedule->update([
                'completed_at' => now(),
                'is_recurring' => false, // Stop recurring
            ]);

            // Cancel associated tasks
            if ($schedule->task_id) {
                Task::where('id', $schedule->task_id)
                    ->where('status', '!=', 'completed')
                    ->update([
                        'status' => 'cancelled',
                        'completed_at' => now(),
                    ]);
            }

            Log::info("Follow-up cancelled for closed deal", [
                'schedule_id' => $schedule->id,
                'lead_id' => $lead->id,
                'status' => $lead->status,
            ]);
        }
    }

    /**
     * Process due follow-ups and create tasks.
     * Called by scheduled command.
     */
    public function processDueFollowUps(): array
    {
        $processed = [];
        $now = now();

        // Get all due follow-up schedules that haven't been completed
        $dueSchedules = FollowUpSchedule::where('scheduled_at', '<=', $now)
            ->whereNull('completed_at')
            ->with('lead')
            ->get();

        foreach ($dueSchedules as $schedule) {
            $lead = $schedule->lead;

            // Skip if lead is won or lost
            if (in_array($lead->status, ['won', 'lost'])) {
                $this->cancelFollowUpsForClosedDeal($lead);
                continue;
            }

            // Create or update task if it doesn't exist
            if (!$schedule->task_id) {
                $task = Task::create([
                    'lead_id' => $lead->id,
                    'created_by' => $lead->added_by,
                    'type' => 'follow_up',
                    'title' => "Follow-up: {$lead->company}",
                    'description' => $schedule->notes ?? "Follow-up for {$lead->company}",
                    'due_date' => $schedule->scheduled_at,
                    'priority' => 'medium',
                    'status' => 'pending',
                ]);

                $schedule->update(['task_id' => $task->id]);
            } else {
                // Ensure task exists and is not completed
                $task = Task::find($schedule->task_id);
                if ($task && $task->status === 'completed') {
                    // Task was completed manually, mark schedule as completed
                    $schedule->markCompleted();
                    continue;
                }
            }

            $processed[] = [
                'schedule_id' => $schedule->id,
                'lead_id' => $lead->id,
                'company' => $lead->company,
                'scheduled_at' => $schedule->scheduled_at,
                'type' => $schedule->type,
            ];
        }

        return $processed;
    }

    /**
     * Get all active follow-up schedules for a lead.
     */
    public function getActiveFollowUps(Lead $lead): \Illuminate\Database\Eloquent\Collection
    {
        return FollowUpSchedule::where('lead_id', $lead->id)
            ->whereNull('completed_at')
            ->with('task')
            ->orderBy('scheduled_at', 'asc')
            ->get();
    }
}


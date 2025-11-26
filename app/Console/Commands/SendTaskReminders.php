<?php

namespace App\Console\Commands;

use App\Mail\TaskReminderMail;
use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendTaskReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:send-reminders {--hours=24 : Number of hours ahead to check for due tasks}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders for tasks due soon';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hoursAhead = (int) $this->option('hours');
        $this->info("Sending task reminders for tasks due in the next {$hoursAhead} hours...");

        $now = now();
        $futureTime = now()->addHours($hoursAhead);

        // Get tasks that are due soon (within the specified hours) and not completed
        $upcomingTasks = Task::whereBetween('due_date', [$now, $futureTime])
            ->where('status', '!=', 'completed')
            ->whereNull('completed_at')
            ->with(['createdBy', 'lead'])
            ->get();

        // Get overdue tasks (due before now and not completed)
        $overdueTasks = Task::where('due_date', '<', $now)
            ->where('status', '!=', 'completed')
            ->whereNull('completed_at')
            ->with(['createdBy', 'lead'])
            ->get();

        // Combine and deduplicate tasks
        $allTasks = $upcomingTasks->merge($overdueTasks)->unique('id');

        if ($allTasks->isEmpty()) {
            $this->info('No tasks due soon found.');
            return Command::SUCCESS;
        }

        $sent = 0;
        $failed = 0;

        foreach ($allTasks as $task) {
            // Get the user who created the task
            $user = $task->createdBy;

            if (!$user || !$user->email) {
                $this->warn("Skipping task {$task->id}: No email found for user ID {$task->created_by}");
                continue;
            }

            // Skip if user is not active
            if (!$user->is_active) {
                $this->warn("Skipping task {$task->id}: User {$user->email} is not active");
                continue;
            }

            // Determine if task is overdue
            $isOverdue = $task->due_date < $now;

            try {
                Mail::to($user->email)->send(
                    new TaskReminderMail($task, $user->name, $isOverdue)
                );

                $status = $isOverdue ? 'overdue' : 'upcoming';
                $this->info("Sent {$status} task reminder to {$user->email} for task: {$task->title}");

                $sent++;

                Log::info("Task reminder sent", [
                    'task_id' => $task->id,
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'due_date' => $task->due_date,
                    'is_overdue' => $isOverdue,
                ]);
            } catch (\Exception $e) {
                $this->error("Failed to send email to {$user->email} for task {$task->id}: {$e->getMessage()}");
                $failed++;

                Log::error("Failed to send task reminder", [
                    'task_id' => $task->id,
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Sent {$sent} reminder(s), {$failed} failed.");

        // Display summary
        if ($sent > 0) {
            $overdueCount = $allTasks->filter(fn($task) => $task->due_date < $now)->count();
            $upcomingCount = $allTasks->count() - $overdueCount;

            $this->table(
                ['Status', 'Count'],
                [
                    ['Overdue', $overdueCount],
                    ['Upcoming', $upcomingCount],
                    ['Total', $allTasks->count()],
                ]
            );
        }

        return Command::SUCCESS;
    }
}


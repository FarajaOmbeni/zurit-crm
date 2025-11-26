<?php

namespace App\Console\Commands;

use App\Mail\FollowUpEmailMail;
use App\Models\FollowUpSchedule;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendFollowUpEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'follow-ups:send-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send scheduled follow-up reminder emails to users';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Sending follow-up reminder emails...');

        // Get all due follow-up schedules that haven't been completed
        $dueSchedules = FollowUpSchedule::where('scheduled_at', '<=', now())
            ->whereNull('completed_at')
            ->with(['lead.addedBy', 'task'])
            ->get();

        if ($dueSchedules->isEmpty()) {
            $this->info('No due follow-ups found.');
            return Command::SUCCESS;
        }

        $sent = 0;
        $failed = 0;

        foreach ($dueSchedules as $schedule) {
            $lead = $schedule->lead;

            // Skip if lead is won or lost
            if (in_array($lead->status, ['won', 'lost'])) {
                $this->warn("Skipping follow-up for closed deal: {$lead->company}");
                continue;
            }

            // Get the user who added the lead
            $user = $lead->addedBy;

            if (!$user || !$user->email) {
                $this->error("No email found for user ID {$lead->added_by}");
                $failed++;
                continue;
            }

            try {
                Mail::to($user->email)->send(
                    new FollowUpEmailMail($lead, $schedule, $user->name)
                );

                $this->info("Sent follow-up email to {$user->email} for {$lead->company}");
                $sent++;

                Log::info("Follow-up email sent", [
                    'schedule_id' => $schedule->id,
                    'lead_id' => $lead->id,
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            } catch (\Exception $e) {
                $this->error("Failed to send email to {$user->email}: {$e->getMessage()}");
                $failed++;

                Log::error("Failed to send follow-up email", [
                    'schedule_id' => $schedule->id,
                    'lead_id' => $lead->id,
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Sent {$sent} email(s), {$failed} failed.");

        return Command::SUCCESS;
    }
}

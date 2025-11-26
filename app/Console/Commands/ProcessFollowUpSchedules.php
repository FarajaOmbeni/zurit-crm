<?php

namespace App\Console\Commands;

use App\Services\FollowUpService;
use Illuminate\Console\Command;

class ProcessFollowUpSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'follow-ups:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process due follow-up schedules and create tasks';

    /**
     * Execute the console command.
     */
    public function handle(FollowUpService $followUpService): int
    {
        $this->info('Processing due follow-up schedules...');

        $processed = $followUpService->processDueFollowUps();

        if (empty($processed)) {
            $this->info('No due follow-ups found.');
            return Command::SUCCESS;
        }

        $this->info("Processed " . count($processed) . " follow-up schedule(s).");

        // Display processed follow-ups in a table
        $this->table(
            ['Schedule ID', 'Lead ID', 'Company', 'Scheduled At', 'Type'],
            array_map(function ($item) {
                return [
                    $item['schedule_id'],
                    $item['lead_id'],
                    $item['company'],
                    $item['scheduled_at']->format('Y-m-d H:i:s'),
                    $item['type'],
                ];
            }, $processed)
        );

        return Command::SUCCESS;
    }
}

<?php

namespace Database\Seeders;

use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the user (assuming user ID 3 from previous context)
        $user = User::find(3) ?? User::first();

        if (!$user) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        // Generate reports for the last 7 days
        for ($i = 0; $i < 7; $i++) {
            $date = now()->subDays($i);

            Report::create([
                'user_id' => $user->id,
                'type' => 'eod',
                'report_date' => $date->format('Y-m-d'),
                'highlights' => '',
                'challenges' => '',
                'data' => [
                    'outreach_summary' => [
                        'schemes_contacted' => rand(5, 15),
                        'schemes_newly_engaged' => rand(1, 5),
                        'follow_ups_conducted' => rand(3, 10),
                        'active_pipeline' => rand(10, 30),
                    ],
                    'schemes_engagement' => [],
                    'new_leads' => [],
                    'won_deals' => [],
                    'lost_deals' => [],
                    'key_reminders' => [
                        'upcoming_tasks' => [],
                        'overdue_tasks' => [],
                    ],
                ],
            ]);
        }

        $this->command->info('Reports seeded successfully for the last 7 days!');
    }
}

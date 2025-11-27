<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin user or first user as fallback
        // For testing, use the currently logged in user or admin
        $adminUser = User::where('email', 'ombenifaraja@gmail.com')->first() 
            ?? User::where('role', 'admin')->first() 
            ?? User::first();

        if (!$adminUser) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        // Get some leads
        $leads = Lead::take(10)->get();

        if ($leads->isEmpty()) {
            $this->command->warn('No leads found. Please run LeadSeeder first.');
            return;
        }

        // Sample tasks
        $tasks = [
            [
                'lead_id' => $leads[0]->id ?? null,
                'created_by' => $adminUser->id,
                'type' => 'call',
                'title' => 'Call follow-up',
                'description' => 'Follow up on initial proposal discussion',
                'due_date' => now()->addHours(2),
                'priority' => 'high',
                'status' => 'pending',
            ],
            [
                'lead_id' => $leads[1]->id ?? null,
                'created_by' => $adminUser->id,
                'type' => 'meeting',
                'title' => 'Schedule meeting',
                'description' => 'Set up demo presentation',
                'due_date' => now()->addHours(5),
                'priority' => 'high',
                'status' => 'pending',
            ],
            [
                'lead_id' => $leads[2]->id ?? null,
                'created_by' => $adminUser->id,
                'type' => 'email',
                'title' => 'Send proposal',
                'description' => 'Email detailed proposal document',
                'due_date' => now()->addDay(),
                'priority' => 'medium',
                'status' => 'in_progress',
            ],
            [
                'lead_id' => $leads[3]->id ?? null,
                'created_by' => $adminUser->id,
                'type' => 'follow_up',
                'title' => 'Check contract status',
                'description' => 'Verify if contract has been signed',
                'due_date' => now()->addDays(2),
                'priority' => 'medium',
                'status' => 'pending',
            ],
            [
                'lead_id' => $leads[4]->id ?? null,
                'created_by' => $adminUser->id,
                'type' => 'call',
                'title' => 'Price negotiation',
                'description' => 'Discuss pricing options',
                'due_date' => now()->addDays(3),
                'priority' => 'low',
                'status' => 'pending',
            ],
            [
                'lead_id' => $leads[5]->id ?? null,
                'created_by' => $adminUser->id,
                'type' => 'other',
                'title' => 'Prepare presentation',
                'description' => 'Create custom presentation for client',
                'due_date' => now()->subHours(1),
                'priority' => 'high',
                'status' => 'pending',
            ],
        ];

        // Create tasks
        foreach ($tasks as $task) {
            Task::create($task);
        }

        $this->command->info('Tasks seeded successfully!');
    }
}

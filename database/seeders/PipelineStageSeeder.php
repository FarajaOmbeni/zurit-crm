<?php

namespace Database\Seeders;

use App\Models\PipelineStage;
use Illuminate\Database\Seeder;

class PipelineStageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stages = [
            [
                'name' => 'New Lead',
                'slug' => 'new_lead',
                'order' => 1,
                'color' => '#7639C2',
                'is_active' => true,
                'description' => 'Newly added leads that haven\'t been contacted yet',
            ],
            [
                'name' => 'Initial Outreach',
                'slug' => 'initial_outreach',
                'order' => 2,
                'color' => '#7639C2',
                'is_active' => true,
                'description' => 'First contact has been made with the lead',
            ],
            [
                'name' => 'Follow Ups',
                'slug' => 'follow_ups',
                'order' => 3,
                'color' => '#FF5B5D',
                'is_active' => true,
                'description' => 'Ongoing follow-up communications',
            ],
            [
                'name' => 'Negotiations',
                'slug' => 'negotiations',
                'order' => 4,
                'color' => '#FF5B5D',
                'is_active' => true,
                'description' => 'In negotiation phase',
            ],
            [
                'name' => 'Won',
                'slug' => 'won',
                'order' => 5,
                'color' => '#7639C2',
                'is_active' => true,
                'description' => 'Deal closed successfully - converted to client',
            ],
            [
                'name' => 'Lost',
                'slug' => 'lost',
                'order' => 6,
                'color' => '#6B6B6B',
                'is_active' => true,
                'description' => 'Deal lost or closed without conversion',
            ],
        ];

        foreach ($stages as $stage) {
            PipelineStage::firstOrCreate(
                ['slug' => $stage['slug']],
                $stage
            );
        }
    }
}

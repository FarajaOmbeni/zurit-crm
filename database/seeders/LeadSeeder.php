<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin user or first user as fallback
        $adminUser = User::where('role', 'admin')->first() ?? User::first();

        if (!$adminUser) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        // Sample leads (not clients)
        $leads = [
            [
                'name' => 'John Mwangi',
                'position' => 'HR Manager',
                'company' => 'Tech Solutions Ltd',
                'email' => 'john.mwangi@techsolutions.co.ke',
                'phone' => '+254712345678',
                'mobile' => '+254712345678',
                'city' => 'Nairobi',
                'country' => 'Kenya',
                'source' => 'Website',
                'added_by' => $adminUser->id,
                'status' => 'new_lead',
                'value' => 50000.00,
                'product' => 'Pension Scheme Consultation',
                'expected_close_date' => now()->addDays(30),
                'notes' => 'Interested in pension scheme consultation for their employees.',
            ],
            [
                'name' => 'Sarah Wanjiku',
                'position' => 'Finance Director',
                'company' => 'Green Energy Corp',
                'email' => 'sarah.wanjiku@greenenergy.co.ke',
                'phone' => '+254723456789',
                'mobile' => '+254723456789',
                'city' => 'Nairobi',
                'country' => 'Kenya',
                'source' => 'Referral',
                'added_by' => $adminUser->id,
                'status' => 'initial_outreach',
                'value' => 75000.00,
                'product' => 'Retirement Planning Package',
                'expected_close_date' => now()->addDays(45),
                'notes' => 'Initial contact made, waiting for response.',
            ],
            [
                'name' => 'David Ochieng',
                'position' => 'CEO',
                'company' => 'Manufacturing Industries',
                'email' => 'david.ochieng@manufacturing.co.ke',
                'phone' => '+254734567890',
                'mobile' => '+254734567890',
                'city' => 'Mombasa',
                'country' => 'Kenya',
                'source' => 'LinkedIn',
                'added_by' => $adminUser->id,
                'status' => 'follow_ups',
                'value' => 100000.00,
                'product' => 'Pension Fund Management',
                'expected_close_date' => now()->addDays(60),
                'notes' => 'Multiple follow-ups conducted, very interested.',
            ],
            [
                'name' => 'Mary Akinyi',
                'position' => 'Operations Manager',
                'company' => 'Logistics Pro',
                'email' => 'mary.akinyi@logisticspro.co.ke',
                'phone' => '+254745678901',
                'mobile' => '+254745678901',
                'city' => 'Nairobi',
                'country' => 'Kenya',
                'source' => 'Email Campaign',
                'added_by' => $adminUser->id,
                'status' => 'negotiations',
                'value' => 125000.00,
                'product' => 'Employee Benefits Package',
                'expected_close_date' => now()->addDays(15),
                'notes' => 'In final negotiation phase, discussing terms.',
            ],
            [
                'name' => 'Peter Kamau',
                'position' => 'CFO',
                'company' => 'Financial Services Group',
                'email' => 'peter.kamau@fsg.co.ke',
                'phone' => '+254756789012',
                'mobile' => '+254756789012',
                'city' => 'Nairobi',
                'country' => 'Kenya',
                'source' => 'Cold Call',
                'added_by' => $adminUser->id,
                'status' => 'lost',
                'value' => 60000.00,
                'product' => 'Pension Scheme Audit',
                'actual_close_date' => now()->subDays(5),
                'lost_reason' => 'Decided to go with another provider due to pricing.',
                'notes' => 'Lost to competitor.',
            ],
        ];

        // Sample clients (won leads)
        $clients = [
            [
                'name' => 'James Kariuki',
                'position' => 'Managing Director',
                'company' => 'Construction Masters',
                'email' => 'james.kariuki@constructionmasters.co.ke',
                'phone' => '+254767890123',
                'mobile' => '+254767890123',
                'city' => 'Nairobi',
                'country' => 'Kenya',
                'source' => 'Referral',
                'added_by' => $adminUser->id,
                'status' => 'won',
                'value' => 50000.00,
                'product' => 'Pension Scheme Consultation',
                'actual_close_date' => now()->subDays(10),
                'won_at' => now()->subDays(10),
                'is_client' => true,
                'notes' => 'Successfully converted to client.',
            ],
            [
                'name' => 'Grace Njeri',
                'position' => 'HR Director',
                'company' => 'Healthcare Services',
                'email' => 'grace.njeri@healthcare.co.ke',
                'phone' => '+254778901234',
                'mobile' => '+254778901234',
                'city' => 'Nairobi',
                'country' => 'Kenya',
                'source' => 'Website',
                'added_by' => $adminUser->id,
                'status' => 'won',
                'value' => 75000.00,
                'product' => 'Retirement Planning Package',
                'actual_close_date' => now()->subDays(20),
                'won_at' => now()->subDays(20),
                'is_client' => true,
                'notes' => 'Client since last month.',
            ],
            [
                'name' => 'Michael Otieno',
                'position' => 'Finance Manager',
                'company' => 'Retail Chain Ltd',
                'email' => 'michael.otieno@retailchain.co.ke',
                'phone' => '+254789012345',
                'mobile' => '+254789012345',
                'city' => 'Kisumu',
                'country' => 'Kenya',
                'source' => 'Trade Show',
                'added_by' => $adminUser->id,
                'status' => 'won',
                'value' => 100000.00,
                'product' => 'Pension Fund Management',
                'actual_close_date' => now()->subDays(30),
                'won_at' => now()->subDays(30),
                'is_client' => true,
                'notes' => 'Long-term client, very satisfied.',
            ],
        ];

        // Create leads
        foreach ($leads as $lead) {
            Lead::firstOrCreate(
                ['email' => $lead['email']],
                $lead
            );
        }

        // Create clients
        foreach ($clients as $client) {
            Lead::firstOrCreate(
                ['email' => $client['email']],
                $client
            );
        }
    }
}

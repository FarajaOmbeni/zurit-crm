<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Report;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;

class ReportService
{
    /**
     * Generate End of Day Report.
     */
    public function generateEODReport(User $user, string $date, ?string $highlights = null, ?string $challenges = null): array
    {
        $startOfDay = Carbon::parse($date)->startOfDay();
        $endOfDay = Carbon::parse($date)->endOfDay();

        // 1. Outreach Summary
        $outreachSummary = $this->calculateOutreachSummary($user, $startOfDay, $endOfDay);

        // 2. Schemes Engagement Update
        $schemesEngagement = $this->getSchemesEngagementUpdate($user, $startOfDay, $endOfDay);

        // 3. New Leads or Emails Received
        $newLeads = $this->getNewLeads($user, $startOfDay, $endOfDay);

        // 4. Won Deals
        $wonDeals = $this->getWonDeals($user, $startOfDay, $endOfDay);

        // 5. Lost Deals
        $lostDeals = $this->getLostDeals($user, $startOfDay, $endOfDay);

        // 6. Highlights and Challenges (user input)
        $highlights = $highlights ?? '';
        $challenges = $challenges ?? '';

        // 7. Key Reminders
        $keyReminders = $this->getKeyReminders($user, $date);

        // Save report to database
        $report = Report::create([
            'user_id' => $user->id,
            'type' => 'eod',
            'report_date' => $date,
            'highlights' => $highlights,
            'challenges' => $challenges,
            'data' => [
                'outreach_summary' => $outreachSummary,
                'schemes_engagement' => $schemesEngagement,
                'new_leads' => $newLeads,
                'won_deals' => $wonDeals,
                'lost_deals' => $lostDeals,
                'key_reminders' => $keyReminders,
            ],
        ]);

        return [
            'report' => $report,
            'salesperson_name' => $user->name,
            'date' => $date,
            'data' => [
                'outreach_summary' => $outreachSummary,
                'schemes_engagement' => $schemesEngagement,
                'new_leads' => $newLeads,
                'won_deals' => $wonDeals,
                'lost_deals' => $lostDeals,
                'highlights' => $highlights,
                'challenges' => $challenges,
                'key_reminders' => $keyReminders,
            ],
        ];
    }

    /**
     * Generate custom report for date range.
     */
    public function generateCustomReport(User $user, string $startDate, string $endDate, ?string $highlights = null, ?string $challenges = null): array
    {
        $startDateCarbon = Carbon::parse($startDate)->startOfDay();
        $endDateCarbon = Carbon::parse($endDate)->endOfDay();

        // Calculate aggregated data for the date range
        $outreachSummary = $this->calculateOutreachSummary($user, $startDateCarbon, $endDateCarbon);
        $schemesEngagement = $this->getSchemesEngagementUpdate($user, $startDateCarbon, $endDateCarbon);
        $newLeads = $this->getNewLeads($user, $startDateCarbon, $endDateCarbon);
        $wonDeals = $this->getWonDeals($user, $startDateCarbon, $endDateCarbon);
        $lostDeals = $this->getLostDeals($user, $startDateCarbon, $endDateCarbon);
        $keyReminders = $this->getKeyReminders($user, $endDate);

        $report = Report::create([
            'user_id' => $user->id,
            'type' => 'custom',
            'report_date' => $endDate,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'highlights' => $highlights ?? '',
            'challenges' => $challenges ?? '',
            'data' => [
                'outreach_summary' => $outreachSummary,
                'schemes_engagement' => $schemesEngagement,
                'new_leads' => $newLeads,
                'won_deals' => $wonDeals,
                'lost_deals' => $lostDeals,
                'key_reminders' => $keyReminders,
            ],
        ]);

        return [
            'report' => $report,
            'salesperson_name' => $user->name,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'data' => [
                'outreach_summary' => $outreachSummary,
                'schemes_engagement' => $schemesEngagement,
                'new_leads' => $newLeads,
                'won_deals' => $wonDeals,
                'lost_deals' => $lostDeals,
                'highlights' => $highlights ?? '',
                'challenges' => $challenges ?? '',
                'key_reminders' => $keyReminders,
            ],
        ];
    }

    /**
     * Calculate outreach summary.
     * Returns total leads contacted and list of "lead name - product" entries.
     */
    public function calculateOutreachSummary(User $user, Carbon $startDate, Carbon $endDate): array
    {
        $leadIds = $this->getUserLeadIds($user);

        // Get leads that had stage changes in the period (contacted leads)
        // A lead is considered "contacted" if its status in lead_product was updated
        $contactedLeadIds = \DB::table('lead_product')
            ->whereIn('lead_id', $leadIds)
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->whereNotNull('status')
            ->where('status', '!=', 'new_lead')  // Exclude uncontacted leads
            ->distinct('lead_id')
            ->pluck('lead_id');

        // Count total contacted leads
        $totalContacted = $contactedLeadIds->count();

        // Get lead details with products for display
        $contactedLeads = \DB::table('lead_product')
            ->join('leads', 'lead_product.lead_id', '=', 'leads.id')
            ->leftJoin('products', 'lead_product.product_id', '=', 'products.id')
            ->whereIn('lead_product.lead_id', $contactedLeadIds)
            ->whereBetween('lead_product.updated_at', [$startDate, $endDate])
            ->whereNotNull('lead_product.status')
            ->where('lead_product.status', '!=', 'new_lead')
            ->select(
                'leads.id',
                'leads.contact_type',
                'leads.name',
                'leads.company',
                'products.name as product_name'
            )
            ->get()
            ->map(function ($item) {
                // For company contacts, show company name
                // For personal contacts, show person's name
                $displayName = $item->contact_type === 'company'
                    ? ($item->company ?? $item->name)
                    : $item->name;

                // Format as "lead name - product" or just "lead name" if no product
                $formattedDisplay = $item->product_name
                    ? "{$displayName} - {$item->product_name}"
                    : "{$displayName} - No Product";

                return [
                    'id' => $item->id,
                    'display_name' => $formattedDisplay,
                    'contact_type' => $item->contact_type,
                    'lead_name' => $displayName,
                    'product_name' => $item->product_name ?? 'No Product',
                ];
            })
            ->toArray();

        return [
            'total_contacted' => $totalContacted,
            'contacted_leads' => $contactedLeads,
        ];
    }

    /**
     * Get schemes engagement update.
     * Returns leads that had notes added during the period.
     */
    public function getSchemesEngagementUpdate(User $user, Carbon $startDate, Carbon $endDate): array
    {
        $leadIds = $this->getUserLeadIds($user);

        // Get lead-product combinations where notes were updated in the period
        // We check for notes that are not empty and were updated in the date range
        $engagements = \DB::table('lead_product')
            ->join('leads', 'lead_product.lead_id', '=', 'leads.id')
            ->join('products', 'lead_product.product_id', '=', 'products.id')
            ->whereIn('lead_product.lead_id', $leadIds)
            ->whereBetween('lead_product.updated_at', [$startDate, $endDate])
            ->whereNotNull('lead_product.notes')
            ->where('lead_product.notes', '!=', '')
            ->select(
                'leads.name as contact_person',
                'leads.phone',
                'lead_product.notes as feedback',
                'products.name as product_name'
            )
            ->get()
            ->map(function ($item) {
                return [
                    'contact_person' => $item->contact_person,
                    'phone' => $item->phone ?? 'N/A',
                    'feedback' => $item->feedback,
                ];
            })
            ->toArray();

        return $engagements;
    }

    /**
     * Get new leads.
     */
    public function getNewLeads(User $user, Carbon $startDate, Carbon $endDate): array
    {
        $leadIds = $this->getUserLeadIds($user);

        $leads = Lead::whereIn('id', $leadIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        return $leads->map(function ($lead) {
            return [
                'id' => $lead->id,
                'name' => $lead->name,
                'company' => $lead->company,
                'email' => $lead->email,
                'status' => $lead->status,
                'created_at' => $lead->created_at,
            ];
        })->toArray();
    }

    /**
     * Get won deals.
     * Returns deals from lead_product where status='won' and won_at is in the period.
     */
    public function getWonDeals(User $user, Carbon $startDate, Carbon $endDate): array
    {
        $leadIds = $this->getUserLeadIds($user);

        // Get won deals from lead_product pivot table
        $wonDeals = \DB::table('lead_product')
            ->join('leads', 'lead_product.lead_id', '=', 'leads.id')
            ->join('products', 'lead_product.product_id', '=', 'products.id')
            ->whereIn('lead_product.lead_id', $leadIds)
            ->where('lead_product.status', 'won')
            ->whereBetween('lead_product.won_at', [$startDate, $endDate])
            ->select(
                'leads.contact_type',
                'leads.company',
                'leads.name',
                'products.name as product_name',
                'lead_product.value as amount'
            )
            ->get()
            ->map(function ($item) {
                // For company contacts, show company name
                // For personal contacts, show person's name
                $displayName = $item->contact_type === 'company'
                    ? ($item->company ?? $item->name)
                    : $item->name;

                return [
                    'client_name' => $displayName,
                    'product' => $item->product_name,
                    'amount' => $item->amount ?? 0,
                ];
            })
            ->toArray();

        return $wonDeals;
    }

    /**
     * Get lost deals.
     */
    public function getLostDeals(User $user, Carbon $startDate, Carbon $endDate): array
    {
        $leadIds = $this->getUserLeadIds($user);

        $deals = Lead::whereIn('id', $leadIds)
            ->where('status', 'lost')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->get();

        return $deals->map(function ($lead) {
            return [
                'client' => $lead->company,
                'product' => $lead->product,
                'lost_reason' => $lead->lost_reason,
            ];
        })->toArray();
    }

    /**
     * Get key reminders from tasks.
     */
    public function getKeyReminders(User $user, string $date): array
    {
        $leadIds = $this->getUserLeadIds($user);
        $taskIds = Task::whereIn('lead_id', $leadIds)
            ->orWhere('created_by', $user->id)
            ->pluck('id');

        // Upcoming tasks (tomorrow and next 3 days)
        $upcomingTasks = Task::whereIn('id', $taskIds)
            ->whereBetween('due_date', [now()->addDay(), now()->addDays(4)])
            ->where('status', '!=', 'completed')
            ->with('lead')
            ->get();

        // Overdue tasks
        $overdueTasks = Task::whereIn('id', $taskIds)
            ->overdue()
            ->with('lead')
            ->get();

        return [
            'upcoming_tasks' => $upcomingTasks->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'due_date' => $task->due_date,
                    'priority' => $task->priority,
                    'lead' => $task->lead ? $task->lead->company : null,
                ];
            })->toArray(),
            'overdue_tasks' => $overdueTasks->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'due_date' => $task->due_date,
                    'priority' => $task->priority,
                    'lead' => $task->lead ? $task->lead->company : null,
                ];
            })->toArray(),
        ];
    }

    /**
     * Get user's lead IDs based on role.
     */
    protected function getUserLeadIds(User $user): \Illuminate\Support\Collection
    {
        if ($user->isAdmin()) {
            return Lead::pluck('id');
        }

        if ($user->isManager()) {
            $teamMemberIds = $user->teamMembers()->pluck('id');

            return Lead::whereIn('added_by', $teamMemberIds->push($user->id))->pluck('id');
        }

        return Lead::where('added_by', $user->id)->pluck('id');
    }
}

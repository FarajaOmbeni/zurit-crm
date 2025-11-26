<?php

namespace App\Services;

use App\Models\Activity;
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
            'data' => [
                'salesperson_name' => $user->name,
                'date' => $date,
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
            'data' => [
                'salesperson_name' => $user->name,
                'start_date' => $startDate,
                'end_date' => $endDate,
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
     */
    public function calculateOutreachSummary(User $user, Carbon $startDate, Carbon $endDate): array
    {
        $leadIds = $this->getUserLeadIds($user);

        // Number of Pension Schemes Contacted Today (calls or emails)
        $schemesContacted = Activity::whereIn('lead_id', $leadIds)
            ->whereBetween('activity_date', [$startDate, $endDate])
            ->whereIn('type', ['call', 'email'])
            ->distinct('lead_id')
            ->count('lead_id');

        // Schemes Newly Engaged (new leads created)
        $schemesNewlyEngaged = Lead::whereIn('id', $leadIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'new_lead')
            ->count();

        // Follow-Ups Conducted (activities where lead status is 'follow_ups')
        $followUpsConducted = Activity::whereIn('lead_id', $leadIds)
            ->whereBetween('activity_date', [$startDate, $endDate])
            ->whereHas('lead', function ($q) {
                $q->where('status', 'follow_ups');
            })
            ->count();

        // Total Schemes in Active Pipeline
        $activePipeline = Lead::whereIn('id', $leadIds)
            ->active()
            ->count();

        return [
            'schemes_contacted' => $schemesContacted,
            'schemes_newly_engaged' => $schemesNewlyEngaged,
            'follow_ups_conducted' => $followUpsConducted,
            'active_pipeline' => $activePipeline,
        ];
    }

    /**
     * Get schemes engagement update.
     */
    public function getSchemesEngagementUpdate(User $user, Carbon $startDate, Carbon $endDate): array
    {
        $leadIds = $this->getUserLeadIds($user);

        $leads = Lead::whereIn('id', $leadIds)
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->with(['activities' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('activity_date', [$startDate, $endDate]);
            }])
            ->get();

        return $leads->map(function ($lead) {
            return [
                'client' => $lead->company,
                'product' => $lead->product,
                'stage' => $lead->status,
                'activities' => $lead->activities->count(),
            ];
        })->toArray();
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
     */
    public function getWonDeals(User $user, Carbon $startDate, Carbon $endDate): array
    {
        $leadIds = $this->getUserLeadIds($user);

        $deals = Lead::whereIn('id', $leadIds)
            ->where('is_client', true)
            ->whereBetween('won_at', [$startDate, $endDate])
            ->get();

        return $deals->map(function ($lead) {
            return [
                'client' => $lead->company,
                'product' => $lead->product,
                'payment' => $lead->value,
            ];
        })->toArray();
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


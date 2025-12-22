<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Lead;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Get daily overview and quick stats.
     */
    public function overview(Request $request): JsonResponse
    {
        $user = Auth::user();
        $today = now()->startOfDay();
        $startOfMonth = now()->startOfMonth();

        // Get today's leads
        $leadsToday = $this->getUserLeadsQuery($user)
            ->whereDate('created_at', $today)
            ->count();

        // Get today's clients (won leads)
        $clientsToday = $this->getUserLeadsQuery($user)
            ->where('is_client', true)
            ->whereDate('won_at', $today)
            ->count();

        // Get total revenue from all won deals (all time)
        $totalRevenue = $this->getUserLeadsQuery($user)
            ->where('is_client', true)
            ->sum('value') ?? 0;

        // Get revenue this month
        $revenueThisMonth = $this->getUserLeadsQuery($user)
            ->where('is_client', true)
            ->where('won_at', '>=', $startOfMonth)
            ->sum('value') ?? 0;

        // Get revenue today
        $revenueToday = $this->getUserLeadsQuery($user)
            ->where('is_client', true)
            ->whereDate('won_at', $today)
            ->sum('value') ?? 0;

        // Calculate overall conversion rate (all time)
        $totalLeadsAllTime = $this->getUserLeadsQuery($user)->count();
        $totalClientsAllTime = $this->getUserLeadsQuery($user)->where('is_client', true)->count();
        $conversionRate = $totalLeadsAllTime > 0 ? ($totalClientsAllTime / $totalLeadsAllTime) * 100 : 0;

        // Get tasks due today
        $tasksDueToday = $this->getUserTasksQuery($user)
            ->dueToday()
            ->count();

        // Get overdue tasks
        $overdueTasks = $this->getUserTasksQuery($user)
            ->overdue()
            ->count();

        return response()->json([
            'snapshot' => [
                'leadsToday' => $leadsToday,
                'clientsToday' => $clientsToday,
                'totalRevenue' => $totalRevenue,
                'revenueThisMonth' => $revenueThisMonth,
                'revenueToday' => $revenueToday,
                'conversionRate' => round($conversionRate, 1),
            ],
            'tasks' => [
                'dueToday' => $tasksDueToday,
                'overdue' => $overdueTasks,
            ],
        ]);
    }

    /**
     * Get tasks due today.
     */
    public function tasksDueToday(Request $request): JsonResponse
    {
        $user = Auth::user();

        $tasks = $this->getUserTasksQuery($user)
            ->dueToday()
            ->with(['lead', 'createdBy'])
            ->get();

        return response()->json([
            'tasks' => $tasks,
        ]);
    }

    /**
     * Get quick stats for dashboard.
     */
    public function stats(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Lead counts by status
        $leadsByStatus = $this->getUserLeadsQuery($user)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Total leads (excluding clients)
        $totalLeads = $this->getUserLeadsQuery($user)
            ->where('is_client', false)
            ->count();

        // Total clients
        $totalClients = $this->getUserLeadsQuery($user)
            ->where('is_client', true)
            ->count();

        // Total revenue from all won deals (all time)
        $totalRevenue = $this->getUserLeadsQuery($user)
            ->where('is_client', true)
            ->sum('value') ?? 0;

        // Revenue this month
        $revenueThisMonth = $this->getUserLeadsQuery($user)
            ->where('is_client', true)
            ->whereMonth('won_at', now()->month)
            ->whereYear('won_at', now()->year)
            ->sum('value') ?? 0;

        // Deals won this month
        $dealsWonThisMonth = $this->getUserLeadsQuery($user)
            ->where('is_client', true)
            ->whereMonth('won_at', now()->month)
            ->whereYear('won_at', now()->year)
            ->count();

        // Active pipeline count
        $activePipeline = $this->getUserLeadsQuery($user)
            ->active()
            ->count();

        // Pipeline value (sum of values for active leads)
        $pipelineValue = $this->getUserLeadsQuery($user)
            ->active()
            ->sum('value') ?? 0;

        return response()->json([
            'leadsByStatus' => $leadsByStatus,
            'totalLeads' => $totalLeads,
            'totalClients' => $totalClients,
            'totalRevenue' => $totalRevenue,
            'revenueThisMonth' => $revenueThisMonth,
            'dealsWonThisMonth' => $dealsWonThisMonth,
            'activePipeline' => $activePipeline,
            'pipelineValue' => $pipelineValue,
        ]);
    }

    /**
     * Get leads query based on user role.
     */
    protected function getUserLeadsQuery($user)
    {
        $query = Lead::query();

        if ($user->isAdmin()) {
            // Admin sees all leads
            return $query;
        }

        if ($user->isManager()) {
            // Manager sees their leads and team members' leads
            $teamMemberIds = $user->teamMembers()->pluck('id');
            return $query->whereIn('added_by', $teamMemberIds->push($user->id));
        }

        // Team member sees only their own leads
        return $query->where('added_by', $user->id);
    }

    /**
     * Get tasks query based on user role.
     */
    protected function getUserTasksQuery($user)
    {
        $query = Task::query();

        if ($user->isAdmin()) {
            // Admin sees all tasks
            return $query;
        }

        if ($user->isManager()) {
            // Manager sees tasks for their leads and team members' leads
            $teamMemberIds = $user->teamMembers()->pluck('id');
            $leadIds = Lead::whereIn('added_by', $teamMemberIds->push($user->id))->pluck('id');
            return $query->where(function ($q) use ($user, $teamMemberIds, $leadIds) {
                $q->whereIn('created_by', $teamMemberIds->push($user->id))
                    ->orWhereIn('lead_id', $leadIds);
            });
        }

        // Team member sees their own tasks or tasks for their leads
        $leadIds = Lead::where('added_by', $user->id)->pluck('id');
        return $query->where(function ($q) use ($user, $leadIds) {
            $q->where('created_by', $user->id)
                ->orWhereIn('lead_id', $leadIds);
        });
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $today = now()->startOfDay();
        $startOfMonth = now()->startOfMonth();

        // Build query based on user role
        $leadsQuery = $this->getUserLeadsQuery($user);

        // Get authorized lead IDs for pivot table queries
        $authorizedLeadIds = (clone $leadsQuery)->pluck('id');

        // Get leads created today
        $leadsToday = (clone $leadsQuery)
            ->whereDate('created_at', $today)
            ->count();

        // Get clients (won deals) today - count from pivot table
        $clientsToday = DB::table('lead_product')
            ->whereIn('lead_id', $authorizedLeadIds)
            ->where('status', 'won')
            ->whereDate('won_at', $today)
            ->count();

        // Get total revenue from deals won today - sum from pivot table
        $revenueToday = DB::table('lead_product')
            ->whereIn('lead_id', $authorizedLeadIds)
            ->where('status', 'won')
            ->whereDate('won_at', $today)
            ->sum('value') ?? 0;

        // Get revenue this month - sum from pivot table
        $revenueThisMonth = DB::table('lead_product')
            ->whereIn('lead_id', $authorizedLeadIds)
            ->where('status', 'won')
            ->where('won_at', '>=', $startOfMonth)
            ->sum('value') ?? 0;

        // Calculate overall conversion rate
        $totalLeads = (clone $leadsQuery)->count();
        $totalClients = (clone $leadsQuery)->where('is_client', true)->count();
        $conversionRate = $totalLeads > 0 ? ($totalClients / $totalLeads) * 100 : 0;

        return Inertia::render('Dashboard', [
            'snapshot' => [
                'leadsToday' => $leadsToday,
                'clientsToday' => $clientsToday,
                'revenueToday' => $revenueToday,
                'revenueThisMonth' => $revenueThisMonth,
                'conversionRate' => round($conversionRate, 1),
            ],
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
}

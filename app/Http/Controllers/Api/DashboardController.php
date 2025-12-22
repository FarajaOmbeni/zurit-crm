<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard overview data.
     */
    public function overview(Request $request): JsonResponse
    {
        $user = Auth::user();
        $authorizedLeadIds = $this->getAuthorizedLeadIds($user);
        $today = now()->startOfDay();
        $startOfMonth = now()->startOfMonth();

        // Leads created today
        $leadsToday = Lead::whereIn('id', $authorizedLeadIds)
            ->whereDate('created_at', $today)
            ->count();

        // Clients won today
        $clientsToday = DB::table('lead_product')
            ->whereIn('lead_id', $authorizedLeadIds)
            ->where('status', 'won')
            ->whereDate('won_at', $today)
            ->count();

        // Revenue today
        $revenueToday = DB::table('lead_product')
            ->whereIn('lead_id', $authorizedLeadIds)
            ->where('status', 'won')
            ->whereDate('won_at', $today)
            ->sum('value') ?? 0;

        // Revenue this month
        $revenueThisMonth = DB::table('lead_product')
            ->whereIn('lead_id', $authorizedLeadIds)
            ->where('status', 'won')
            ->where('won_at', '>=', $startOfMonth)
            ->sum('value') ?? 0;

        return response()->json([
            'leadsToday' => $leadsToday,
            'clientsToday' => $clientsToday,
            'revenueToday' => $revenueToday,
            'revenueThisMonth' => $revenueThisMonth,
        ]);
    }

    /**
     * Get tasks due today.
     */
    public function tasksDueToday(Request $request): JsonResponse
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $query = Task::with(['lead', 'createdBy'])
            ->whereDate('due_date', $today)
            ->where('status', '!=', 'completed');

        // Filter by user role
        if (!$user->isAdmin()) {
            if ($user->isManager()) {
                $teamMemberIds = $user->teamMembers()->pluck('id')->push($user->id);
                $query->whereIn('created_by', $teamMemberIds);
            } else {
                $query->where('created_by', $user->id);
            }
        }

        $tasks = $query->orderBy('due_date')->limit(10)->get();

        return response()->json([
            'tasks' => $tasks,
        ]);
    }

    /**
     * Get dashboard statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $user = Auth::user();
        $authorizedLeadIds = $this->getAuthorizedLeadIds($user);

        // Total leads
        $totalLeads = Lead::whereIn('id', $authorizedLeadIds)->count();

        // Total clients
        $totalClients = Lead::whereIn('id', $authorizedLeadIds)
            ->where('is_client', true)
            ->count();

        // Conversion rate
        $conversionRate = $totalLeads > 0 ? ($totalClients / $totalLeads) * 100 : 0;

        return response()->json([
            'totalLeads' => $totalLeads,
            'totalClients' => $totalClients,
            'conversionRate' => round($conversionRate, 1),
        ]);
    }

    /**
     * Get products by purchase (won deals grouped by product).
     */
    public function productsByPurchase(Request $request): JsonResponse
    {
        $user = Auth::user();
        $authorizedLeadIds = $this->getAuthorizedLeadIds($user);

        // Get won deals grouped by product from pivot table
        $products = DB::table('lead_product')
            ->join('products', 'lead_product.product_id', '=', 'products.id')
            ->whereIn('lead_product.lead_id', $authorizedLeadIds)
            ->where('lead_product.status', 'won')
            ->select(
                'products.id',
                'products.name',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(lead_product.value) as total_value')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        return response()->json([
            'products' => $products,
        ]);
    }

    /**
     * Get authorized lead IDs based on user role.
     */
    protected function getAuthorizedLeadIds($user)
    {
        $query = Lead::query();

        if ($user->isAdmin()) {
            return $query->pluck('id');
        }

        if ($user->isManager()) {
            $teamMemberIds = $user->teamMembers()->pluck('id');
            return $query->whereIn('added_by', $teamMemberIds->push($user->id))->pluck('id');
        }

        return $query->where('added_by', $user->id)->pluck('id');
    }
}

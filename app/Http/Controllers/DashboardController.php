<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        // Get leads created today (will be updated when Lead model exists)
        // For now, using placeholder logic
        $leadsToday = 0;
        try {
            if (class_exists(\App\Models\Lead::class)) {
                $leadsToday = \App\Models\Lead::where('added_by', $user->id)
                    ->whereDate('created_at', $today)
                    ->count();
            }
        } catch (\Exception $e) {
            // Lead model doesn't exist yet, use placeholder
        }

        // Get clients (won leads) created today
        $clientsToday = 0;
        try {
            if (class_exists(\App\Models\Lead::class)) {
                $clientsToday = \App\Models\Lead::where('added_by', $user->id)
                    ->where('is_client', true)
                    ->whereDate('won_at', $today)
                    ->count();
            }
        } catch (\Exception $e) {
            // Lead model doesn't exist yet, use placeholder
        }

        // Get total revenue from won deals today
        $totalRevenue = 0;
        try {
            if (class_exists(\App\Models\Lead::class)) {
                $totalRevenue = \App\Models\Lead::where('added_by', $user->id)
                    ->where('is_client', true)
                    ->whereDate('won_at', $today)
                    ->sum('value') ?? 0;
            }
        } catch (\Exception $e) {
            // Lead model doesn't exist yet, use placeholder
        }

        // Calculate conversion rate (clients / leads * 100)
        $conversionRate = 0;
        if ($leadsToday > 0) {
            $conversionRate = ($clientsToday / $leadsToday) * 100;
        }

        return Inertia::render('Dashboard', [
            'snapshot' => [
                'leadsToday' => $leadsToday,
                'clientsToday' => $clientsToday,
                'totalRevenue' => $totalRevenue,
                'conversionRate' => $conversionRate,
            ],
        ]);
    }
}

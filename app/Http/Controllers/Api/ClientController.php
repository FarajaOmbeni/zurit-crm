<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    /**
     * Display a listing of clients and leads.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = $this->getAuthorizedLeadsQuery($user);

        // Apply filters
        if ($request->has('source')) {
            $query->where('source', $request->source);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        if ($request->has('sort_by') && $request->has('sort_order')) {
            $sortBy = $request->get('sort_by');
            $sortOrder = $request->get('sort_order');

            // Validate sort field and order
            if (
                in_array($sortBy, ['company', 'name', 'email', 'created_at', 'won_at']) &&
                in_array($sortOrder, ['asc', 'desc'])
            ) {
                // Use case-insensitive sorting for text fields
                if (in_array($sortBy, ['company', 'name', 'email'])) {
                    // Case-insensitive sort with NULL handling
                    // In MySQL, NULL values come first in ASC, last in DESC
                    // We'll use COALESCE to handle NULLs consistently
                    if ($sortOrder === 'asc') {
                        $query->orderByRaw("COALESCE(LOWER({$sortBy}), '') {$sortOrder}");
                    } else {
                        $query->orderByRaw("COALESCE(LOWER({$sortBy}), 'zzzzzzzzzz') {$sortOrder}");
                    }
                } else {
                    $query->orderBy($sortBy, $sortOrder);
                }

                // Add secondary sort by ID for consistent ordering when values are equal
                $query->orderBy('id', $sortOrder);
            }
        } else {
            // Default sorting by created_at desc (newest first)
            $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $clients = $query->with(['addedBy', 'products'])
            ->paginate($perPage);

        return response()->json($clients);
    }

    /**
     * Display the specified client.
     */
    public function show(string $id): JsonResponse
    {
        $client = Lead::where('is_client', true)
            ->with(['addedBy', 'activities.user', 'tasks.createdBy', 'products'])
            ->findOrFail($id);

        $this->authorize('view', $client);

        return response()->json($client);
    }

    /**
     * Update the specified client.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $client = Lead::where('is_client', true)->findOrFail($id);
        $this->authorize('update', $client);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'company' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:255'],
            'sector' => ['nullable', 'string', 'max:255'],
            'value' => ['nullable', 'numeric', 'min:0'],
            'product' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $client->update($validated);
        $client->load('addedBy');

        return response()->json($client);
    }

    /**
     * Get stats for the clients page (specific to logged-in user based on their role).
     */
    public function stats(): JsonResponse
    {
        $user = Auth::user();
        $query = $this->getAuthorizedLeadsQuery($user);

        // Active clients (is_client = true, status = won)
        $activeClients = (clone $query)
            ->where('is_client', true)
            ->where('status', 'won')
            ->count();

        // Paused/Lost clients (status = lost)
        $pausedClients = (clone $query)
            ->where('status', 'lost')
            ->count();

        // Completed - clients with actual_close_date set (deals that have been closed)
        $completed = (clone $query)
            ->where('is_client', true)
            ->whereNotNull('actual_close_date')
            ->count();

        // Calculate average progress based on pipeline stages
        // new_lead = 10%, initial_outreach = 25%, follow_ups = 50%, negotiations = 75%, won = 100%, lost = 0%
        $statusWeights = [
            'new_lead' => 10,
            'initial_outreach' => 25,
            'follow_ups' => 50,
            'negotiations' => 75,
            'won' => 100,
            'lost' => 0,
        ];

        $leadsWithStatus = (clone $query)
            ->whereNotNull('status')
            ->get(['status']);

        $totalProgress = 0;
        $count = $leadsWithStatus->count();

        foreach ($leadsWithStatus as $lead) {
            $totalProgress += $statusWeights[$lead->status] ?? 0;
        }

        $avgProgress = $count > 0 ? round($totalProgress / $count) : 0;

        return response()->json([
            'avgProgress' => $avgProgress,
            'activeClients' => $activeClients,
            'pausedClients' => $pausedClients,
            'completed' => $completed,
        ]);
    }

    /**
     * Get authorized leads query based on user role (returns all leads, not just clients).
     */
    protected function getAuthorizedLeadsQuery($user)
    {
        $query = Lead::query();

        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isManager()) {
            $teamMemberIds = $user->teamMembers()->pluck('id');
            return $query->whereIn('added_by', $teamMemberIds->push($user->id));
        }

        return $query->where('added_by', $user->id);
    }
}

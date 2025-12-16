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
     * Display a listing of clients.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = $this->getAuthorizedClientsQuery($user);

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
                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            // Default sorting by won_at desc
            $query->orderBy('won_at', 'desc');
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
            'mobile' => ['nullable', 'string', 'max:255'],
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
     * Get authorized clients query based on user role.
     */
    protected function getAuthorizedClientsQuery($user)
    {
        $query = Lead::where('is_client', true);

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

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    /**
     * Display a listing of leads.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = $this->getAuthorizedLeadsQuery($user);

        // Exclude clients by default unless explicitly requested
        if (!$request->boolean('include_clients')) {
            $query->where('is_client', false);
        }

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

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

        // Pagination
        $perPage = $request->get('per_page', 15);
        $leads = $query->with('addedBy')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($leads);
    }

    /**
     * Store a newly created lead.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'company' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['new_lead', 'initial_outreach', 'follow_ups', 'negotiations', 'won', 'lost'])],
            'value' => ['nullable', 'numeric', 'min:0'],
            'product' => ['nullable', 'string', 'max:255'],
            'expected_close_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $lead = Lead::create([
            ...$validated,
            'added_by' => Auth::id(),
            'status' => $validated['status'] ?? 'new_lead',
        ]);

        $lead->load('addedBy');

        return response()->json($lead, 201);
    }

    /**
     * Display the specified lead.
     */
    public function show(string $id): JsonResponse
    {
        $lead = Lead::with(['addedBy', 'activities.user', 'tasks.createdBy', 'followUpSchedules'])
            ->findOrFail($id);

        $this->authorize('view', $lead);

        return response()->json($lead);
    }

    /**
     * Update the specified lead.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        $this->authorize('update', $lead);

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
            'status' => ['sometimes', Rule::in(['new_lead', 'initial_outreach', 'follow_ups', 'negotiations', 'won', 'lost'])],
            'value' => ['nullable', 'numeric', 'min:0'],
            'product' => ['nullable', 'string', 'max:255'],
            'expected_close_date' => ['nullable', 'date'],
            'actual_close_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $lead->update($validated);
        $lead->load('addedBy');

        return response()->json($lead);
    }

    /**
     * Remove the specified lead.
     */
    public function destroy(string $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        $this->authorize('delete', $lead);

        $lead->delete();

        return response()->json(['message' => 'Lead deleted successfully'], 200);
    }

    /**
     * Update lead status.
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        $this->authorize('updateStatus', $lead);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['new_lead', 'initial_outreach', 'follow_ups', 'negotiations', 'won', 'lost'])],
        ]);

        $lead->update(['status' => $validated['status']]);

        // Auto-convert to client if status is 'won'
        if ($validated['status'] === 'won' && !$lead->is_client) {
            $lead->markAsWon();
        }

        $lead->load('addedBy');

        return response()->json($lead);
    }

    /**
     * Mark lead as won (converts to client).
     */
    public function markAsWon(Request $request, string $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        $this->authorize('markAsWon', $lead);

        $validated = $request->validate([
            'value' => ['nullable', 'numeric', 'min:0'],
            'actual_close_date' => ['nullable', 'date'],
        ]);

        if (isset($validated['value'])) {
            $lead->value = $validated['value'];
        }
        if (isset($validated['actual_close_date'])) {
            $lead->actual_close_date = $validated['actual_close_date'];
        }

        $lead->markAsWon();
        $lead->load('addedBy');

        return response()->json($lead);
    }

    /**
     * Mark lead as lost.
     */
    public function markAsLost(Request $request, string $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        $this->authorize('markAsLost', $lead);

        $validated = $request->validate([
            'lost_reason' => ['required', 'string'],
            'actual_close_date' => ['nullable', 'date'],
        ]);

        $lead->markAsLost($validated['lost_reason']);

        if (isset($validated['actual_close_date'])) {
            $lead->actual_close_date = $validated['actual_close_date'];
            $lead->save();
        }

        $lead->load('addedBy');

        return response()->json($lead);
    }

    /**
     * Get kanban view data (grouped by status).
     */
    public function kanban(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = $this->getAuthorizedLeadsQuery($user);

        // Exclude clients
        $query->where('is_client', false);

        $leads = $query->with('addedBy')
            ->orderBy('created_at', 'desc')
            ->get();

        // Group by status
        $grouped = $leads->groupBy('status')->map(function ($group) {
            return $group->values();
        });

        return response()->json([
            'leads' => $grouped,
            'statuses' => ['new_lead', 'initial_outreach', 'follow_ups', 'negotiations', 'won', 'lost'],
        ]);
    }

    /**
     * Get authorized leads query based on user role.
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

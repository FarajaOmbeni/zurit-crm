<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ActivityController extends Controller
{
    /**
     * Display a listing of activities.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = $this->getAuthorizedActivitiesQuery($user);

        // Apply filters
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('lead_id')) {
            $query->where('lead_id', $request->lead_id);
        }

        if ($request->has('date')) {
            $query->whereDate('activity_date', $request->date);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $activities = $query->with(['lead', 'user'])
            ->orderBy('activity_date', 'desc')
            ->paginate($perPage);

        return response()->json($activities);
    }

    /**
     * Store a newly created activity.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lead_id' => ['required', 'exists:leads,id'],
            'type' => ['required', Rule::in(['call', 'email', 'meeting', 'note'])],
            'activity_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'outcome' => ['nullable', 'string'],
        ]);

        // Check authorization for the lead
        $lead = Lead::findOrFail($validated['lead_id']);
        $this->authorize('view', $lead);

        $activity = Activity::create([
            ...$validated,
            'user_id' => Auth::id(),
        ]);

        $activity->load(['lead', 'user']);

        return response()->json($activity, 201);
    }

    /**
     * Display the specified activity.
     */
    public function show(string $id): JsonResponse
    {
        $activity = Activity::with(['lead', 'user'])->findOrFail($id);

        // Check authorization through the lead
        $this->authorize('view', $activity->lead);

        return response()->json($activity);
    }

    /**
     * Update the specified activity.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $activity = Activity::findOrFail($id);

        // Check authorization through the lead
        $this->authorize('view', $activity->lead);

        $validated = $request->validate([
            'type' => ['sometimes', Rule::in(['call', 'email', 'meeting', 'note'])],
            'activity_date' => ['sometimes', 'required', 'date'],
            'description' => ['nullable', 'string'],
            'outcome' => ['nullable', 'string'],
        ]);

        $activity->update($validated);
        $activity->load(['lead', 'user']);

        return response()->json($activity);
    }

    /**
     * Remove the specified activity.
     */
    public function destroy(string $id): JsonResponse
    {
        $activity = Activity::findOrFail($id);

        // Check authorization through the lead
        $this->authorize('view', $activity->lead);

        $activity->delete();

        return response()->json(['message' => 'Activity deleted successfully'], 200);
    }

    /**
     * Get activities for a specific lead/client.
     */
    public function leadActivities(Request $request, string $leadId): JsonResponse
    {
        $lead = Lead::findOrFail($leadId);
        $this->authorize('view', $lead);

        $activities = Activity::where('lead_id', $leadId)
            ->with('user')
            ->orderBy('activity_date', 'desc')
            ->get();

        return response()->json(['activities' => $activities]);
    }

    /**
     * Get authorized activities query based on user role.
     */
    protected function getAuthorizedActivitiesQuery($user)
    {
        $query = Activity::query();

        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isManager()) {
            $teamMemberIds = $user->teamMembers()->pluck('id');
            $leadIds = Lead::whereIn('added_by', $teamMemberIds->push($user->id))->pluck('id');
            return $query->whereIn('lead_id', $leadIds);
        }

        $leadIds = Lead::where('added_by', $user->id)->pluck('id');
        return $query->whereIn('lead_id', $leadIds);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = $this->getAuthorizedTasksQuery($user);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('lead_id')) {
            $query->where('lead_id', $request->lead_id);
        }

        if ($request->has('due_date')) {
            $query->whereDate('due_date', $request->due_date);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $tasks = $query->with(['lead', 'createdBy'])
            ->orderBy('due_date', 'asc')
            ->paginate($perPage);

        return response()->json($tasks);
    }

    /**
     * Store a newly created task.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lead_id' => ['nullable', 'exists:leads,id'],
            'type' => ['required', Rule::in(['follow_up', 'call', 'email', 'meeting', 'other'])],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['required', 'date'],
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high'])],
            'status' => ['nullable', Rule::in(['pending', 'in_progress', 'completed', 'cancelled'])],
        ]);

        $task = Task::create([
            ...$validated,
            'created_by' => Auth::id(),
            'priority' => $validated['priority'] ?? 'medium',
            'status' => $validated['status'] ?? 'pending',
        ]);

        $task->load(['lead', 'createdBy']);

        return response()->json($task, 201);
    }

    /**
     * Display the specified task.
     */
    public function show(string $id): JsonResponse
    {
        $task = Task::with(['lead', 'createdBy'])->findOrFail($id);
        $this->authorize('view', $task);

        return response()->json($task);
    }

    /**
     * Update the specified task.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $task = Task::findOrFail($id);
        $this->authorize('update', $task);

        $validated = $request->validate([
            'lead_id' => ['nullable', 'exists:leads,id'],
            'type' => ['sometimes', Rule::in(['follow_up', 'call', 'email', 'meeting', 'other'])],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['sometimes', 'required', 'date'],
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high'])],
            'status' => ['sometimes', Rule::in(['pending', 'in_progress', 'completed', 'cancelled'])],
        ]);

        $task->update($validated);
        $task->load(['lead', 'createdBy']);

        return response()->json($task);
    }

    /**
     * Remove the specified task.
     */
    public function destroy(string $id): JsonResponse
    {
        $task = Task::findOrFail($id);
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully'], 200);
    }

    /**
     * Mark task as complete.
     */
    public function complete(Request $request, string $id): JsonResponse
    {
        $task = Task::findOrFail($id);
        $this->authorize('complete', $task);

        $task->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $task->load(['lead', 'createdBy']);

        return response()->json($task);
    }

    /**
     * Get upcoming tasks.
     */
    public function upcoming(Request $request): JsonResponse
    {
        $user = Auth::user();
        $days = $request->get('days', 7);

        $tasks = $this->getAuthorizedTasksQuery($user)
            ->upcoming($days)
            ->with(['lead', 'createdBy'])
            ->orderBy('due_date', 'asc')
            ->get();

        return response()->json(['tasks' => $tasks]);
    }

    /**
     * Get authorized tasks query based on user role.
     */
    protected function getAuthorizedTasksQuery($user)
    {
        $query = Task::query();

        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isManager()) {
            $teamMemberIds = $user->teamMembers()->pluck('id');
            $leadIds = \App\Models\Lead::whereIn('added_by', $teamMemberIds->push($user->id))->pluck('id');
            return $query->where(function ($q) use ($user, $teamMemberIds, $leadIds) {
                $q->whereIn('created_by', $teamMemberIds->push($user->id))
                    ->orWhereIn('lead_id', $leadIds);
            });
        }

        $leadIds = \App\Models\Lead::where('added_by', $user->id)->pluck('id');
        return $query->where(function ($q) use ($user, $leadIds) {
            $q->where('created_by', $user->id)
                ->orWhereIn('lead_id', $leadIds);
        });
    }
}

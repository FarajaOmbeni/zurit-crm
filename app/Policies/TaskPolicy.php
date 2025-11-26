<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view tasks
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        // Admin can view all tasks
        if ($user->isAdmin()) {
            return true;
        }

        // Manager can view tasks for their team members' leads
        if ($user->isManager()) {
            // If task has a lead, check if manager can access that lead
            if ($task->lead_id) {
                $lead = $task->lead;
                return $lead->added_by === $user->id ||
                    $user->teamMembers()->pluck('id')->contains($lead->added_by);
            }
            // If no lead, check if task was created by team member
            return $task->created_by === $user->id ||
                $user->teamMembers()->pluck('id')->contains($task->created_by);
        }

        // Team member can view their own tasks or tasks for their leads
        if ($task->lead_id) {
            return $task->lead->added_by === $user->id ||
                $task->created_by === $user->id;
        }

        return $task->created_by === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // All authenticated users can create tasks
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        // Admin can update all tasks
        if ($user->isAdmin()) {
            return true;
        }

        // Manager can update tasks for their team members' leads
        if ($user->isManager()) {
            if ($task->lead_id) {
                $lead = $task->lead;
                return $lead->added_by === $user->id ||
                    $user->teamMembers()->pluck('id')->contains($lead->added_by);
            }
            return $task->created_by === $user->id ||
                $user->teamMembers()->pluck('id')->contains($task->created_by);
        }

        // Team member can update their own tasks or tasks for their leads
        if ($task->lead_id) {
            return $task->lead->added_by === $user->id ||
                $task->created_by === $user->id;
        }

        return $task->created_by === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        // Same as update
        return $this->update($user, $task);
    }

    /**
     * Determine whether the user can mark the task as complete.
     */
    public function complete(User $user, Task $task): bool
    {
        return $this->update($user, $task);
    }
}

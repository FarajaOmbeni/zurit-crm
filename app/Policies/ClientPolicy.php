<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class ClientPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view clients
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * Clients are leads with is_client = true, so we use Lead model
     */
    public function view(User $user, Lead $lead): bool
    {
        // Only view if it's actually a client
        if (!$lead->is_client) {
            return false;
        }

        // Admin can view all clients
        if ($user->isAdmin()) {
            return true;
        }

        // Manager can view their team members' clients
        if ($user->isManager()) {
            return $lead->added_by === $user->id ||
                $user->teamMembers()->pluck('id')->contains($lead->added_by);
        }

        // Team member can only view their own clients
        return $lead->added_by === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Clients are created by marking leads as won, so use LeadPolicy
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Lead $lead): bool
    {
        // Only update if it's actually a client
        if (!$lead->is_client) {
            return false;
        }

        // Admin can update all clients
        if ($user->isAdmin()) {
            return true;
        }

        // Manager can update their team members' clients
        if ($user->isManager()) {
            return $lead->added_by === $user->id ||
                $user->teamMembers()->pluck('id')->contains($lead->added_by);
        }

        // Team member can only update their own clients
        return $lead->added_by === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Lead $lead): bool
    {
        // Only delete if it's actually a client
        if (!$lead->is_client) {
            return false;
        }

        // Only admin can delete clients
        return $user->isAdmin();
    }
}

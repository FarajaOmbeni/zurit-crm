<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view leads
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Lead $lead): bool
    {
        // Admin can view all leads
        if ($user->isAdmin()) {
            return true;
        }

        // Manager can view their team members' leads
        if ($user->isManager()) {
            return $lead->added_by === $user->id ||
                $user->teamMembers()->pluck('id')->contains($lead->added_by);
        }

        // Team member can only view their own leads
        return $lead->added_by === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // All authenticated users can create leads
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Lead $lead): bool
    {
        // Admin can update all leads
        if ($user->isAdmin()) {
            return true;
        }

        // Manager can update their team members' leads
        if ($user->isManager()) {
            return $lead->added_by === $user->id ||
                $user->teamMembers()->pluck('id')->contains($lead->added_by);
        }

        // Team member can only update their own leads
        return $lead->added_by === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Lead $lead): bool
    {
        // Admin can delete all leads
        if ($user->isAdmin()) {
            return true;
        }

        // Manager can delete their team members' leads
        if ($user->isManager()) {
            return $lead->added_by === $user->id ||
                $user->teamMembers()->pluck('id')->contains($lead->added_by);
        }

        // Team member can only delete their own leads
        return $lead->added_by === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Lead $lead): bool
    {
        // Same as delete
        return $this->delete($user, $lead);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Lead $lead): bool
    {
        // Only admin can permanently delete
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can mark the lead as won.
     */
    public function markAsWon(User $user, Lead $lead): bool
    {
        return $this->update($user, $lead);
    }

    /**
     * Determine whether the user can mark the lead as lost.
     */
    public function markAsLost(User $user, Lead $lead): bool
    {
        return $this->update($user, $lead);
    }

    /**
     * Determine whether the user can update the lead status.
     */
    public function updateStatus(User $user, Lead $lead): bool
    {
        return $this->update($user, $lead);
    }

    /**
     * Determine whether the user can reassign the lead to another user.
     */
    public function reassign(User $user, Lead $lead): bool
    {
        // Admin can reassign any lead
        if ($user->isAdmin()) {
            return true;
        }

        // Manager can reassign their own leads or team members' leads
        if ($user->isManager()) {
            return $lead->added_by === $user->id ||
                $user->teamMembers()->pluck('id')->contains($lead->added_by);
        }

        // Team members cannot reassign leads
        return false;
    }
}

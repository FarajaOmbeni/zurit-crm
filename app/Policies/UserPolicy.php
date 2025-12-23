<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Admin can view all users, managers can view their team
        return $user->isAdmin() || $user->isManager();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Admin can view all users
        if ($user->isAdmin()) {
            return true;
        }

        // Manager can view their team members
        if ($user->isManager()) {
            return $model->id === $user->id ||
                $user->teamMembers()->pluck('id')->contains($model->id);
        }

        // Team member can only view themselves
        return $model->id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Admin and managers can create users
        return $user->isAdmin() || $user->isManager();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Admin can update all users
        if ($user->isAdmin()) {
            return true;
        }

        // Manager can update their team members
        if ($user->isManager()) {
            return $model->id === $user->id ||
                $user->teamMembers()->pluck('id')->contains($model->id);
        }

        // Team member can only update themselves
        return $model->id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Only admin can delete users
        // Cannot delete yourself
        return $user->isAdmin() && $model->id !== $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        // Only admin can restore users
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        // Only admin can permanently delete users
        // Cannot delete yourself
        return $user->isAdmin() && $model->id !== $user->id;
    }

    /**
     * Determine whether the user can manage users (admin only).
     */
    public function manageUsers(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can resend OTP.
     */
    public function resendOtp(User $user, User $model): bool
    {
        // Admin can resend OTP for all users
        if ($user->isAdmin()) {
            return true;
        }

        // Manager can resend OTP for their team members
        if ($user->isManager()) {
            return $user->teamMembers()->pluck('id')->contains($model->id);
        }

        return false;
    }
}

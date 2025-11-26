<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized. Please log in.');
        }

        $user = Auth::user();

        // Check if user has any of the required roles
        foreach ($roles as $role) {
            if ($this->hasRole($user, $role)) {
                return $next($request);
            }
        }

        // User doesn't have any of the required roles
        abort(403, 'Unauthorized. Insufficient permissions.');
    }

    /**
     * Check if user has the specified role.
     */
    protected function hasRole($user, string $role): bool
    {
        return match ($role) {
            'admin' => $user->isAdmin(),
            'manager' => $user->isManager() || $user->isAdmin(),
            'team_member' => $user->isTeamMember() || $user->isManager() || $user->isAdmin(),
            default => false,
        };
    }
}

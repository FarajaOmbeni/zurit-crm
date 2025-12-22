<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        // Apply role filter
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Apply search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $users = $query->with('manager')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($users);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'role' => ['required', Rule::in(['admin', 'manager', 'team_member'])],
            'manager_id' => ['nullable', 'exists:users,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // Generate 6-digit OTP
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'password' => Hash::make($otp),
            'role' => $validated['role'],
            'manager_id' => $validated['manager_id'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'otp' => $otp,
            'otp_expires_at' => now()->addHours(24),
            'must_reset_password' => true,
        ]);

        // Send OTP email (would be implemented with Mail class)
        // Mail::to($user->email)->send(new UserOtpMail($otp, $user->name));

        $user->load('manager');

        return response()->json($user, 201);
    }

    /**
     * Display the specified user.
     */
    public function show(string $id): JsonResponse
    {
        $user = User::with(['manager', 'teamMembers'])->findOrFail($id);
        $this->authorize('view', $user);

        return response()->json($user);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['sometimes', Rule::in(['admin', 'manager', 'team_member'])],
            'manager_id' => ['nullable', 'exists:users,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $user->update($validated);
        $user->load('manager');

        return response()->json($user);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $this->authorize('delete', $user);

        $user->delete();

        return response()->json(['message' => 'User deleted successfully'], 200);
    }

    /**
     * Get user's team (for managers).
     */
    public function team(string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        // Only managers and admins can view teams
        // Since route is protected by auth middleware, Auth::user() will always return User
        /** @var User $authUser */
        $authUser = Auth::user();
        if (!$user->isManager() && !$authUser->isAdmin()) {
            abort(403, 'Only managers can have teams.');
        }

        $teamMembers = $user->teamMembers()->with('manager')->get();

        return response()->json(['team' => $teamMembers]);
    }

    /**
     * Get users that can be assigned leads by the current user.
     * - Admin: all active users
     * - Manager: team members + themselves
     */
    public function assignable(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->isAdmin()) {
            // Admin can assign to any active user
            $users = User::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'role']);
        } elseif ($user->isManager()) {
            // Manager can assign to team members + themselves
            $teamMemberIds = $user->teamMembers()->pluck('id');
            $users = User::whereIn('id', $teamMemberIds->push($user->id))
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'role']);
        } else {
            // Team members cannot reassign, return empty
            $users = collect();
        }

        return response()->json(['users' => $users]);
    }
}

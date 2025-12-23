<?php

namespace App\Http\Controllers;

use App\Mail\UserOtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index()
    {
        $currentUser = auth()->user();

        // Only admins and managers can access user management
        if (!$currentUser->isAdmin() && !$currentUser->isManager()) {
            abort(403, 'Unauthorized access.');
        }

        // Build the user query based on role
        $query = User::with('manager')->orderBy('created_at', 'desc');

        // Managers can only see their team members
        if ($currentUser->isManager()) {
            $query->where('manager_id', $currentUser->id);
        }

        $users = $query->get()
            ->map(function ($user) {
                $otpExpired = $user->otp && $user->otp_expires_at && $user->otp_expires_at->isPast();
                $hasOtp = !empty($user->otp) && $user->must_reset_password;

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'manager_name' => $user->manager ? $user->manager->name : null,
                    'is_active' => $user->is_active,
                    'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                    'must_reset_password' => $user->must_reset_password,
                    'otp_expired' => $otpExpired,
                    'has_otp' => $hasOtp,
                ];
            });

        // Get managers for the dropdown (admins and managers can have team members)
        $managers = User::whereIn('role', ['admin', 'manager'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        return Inertia::render('Users/Index', [
            'users' => $users,
            'managers' => $managers,
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $currentUser = auth()->user();

        // Only admins and managers can create users
        if (!$currentUser->isAdmin() && !$currentUser->isManager()) {
            abort(403, 'Unauthorized access.');
        }

        // Validation rules depend on user role
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'is_active' => ['boolean'],
        ];

        // Admins can set role and manager_id
        if ($currentUser->isAdmin()) {
            $rules['role'] = ['required', 'in:admin,manager,team_member'];
            $rules['manager_id'] = ['nullable', 'exists:users,id'];
        }

        $validated = $request->validate($rules);

        // For managers, force the role to team_member and set manager_id to their own ID
        if ($currentUser->isManager()) {
            $validated['role'] = 'team_member';
            $validated['manager_id'] = $currentUser->id;
        }

        // Generate 6-digit OTP
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Create user with temporary password (OTP)
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($otp), // Store OTP as hashed password temporarily
            'role' => $validated['role'],
            'manager_id' => $validated['manager_id'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'otp' => $otp,
            'otp_expires_at' => now()->addHours(24), // OTP expires in 24 hours
            'must_reset_password' => true, // User must reset password after first login
        ]);

        // Send OTP email
        Mail::to($user->email)->send(new UserOtpMail($otp, $user->name));

        return redirect()->route('users.index')->with('success', 'User created successfully. OTP has been sent to their email.');
    }

    /**
     * Resend OTP to a user.
     */
    public function resendOtp(User $user)
    {
        $currentUser = auth()->user();

        // Check authorization using the policy
        if (!$currentUser->can('resendOtp', $user)) {
            abort(403, 'Unauthorized access.');
        }

        // Only resend if user must reset password
        if (!$user->must_reset_password) {
            return redirect()->route('users.index')->with('error', 'User has already set their password.');
        }

        // Generate new 6-digit OTP
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Update user with new OTP
        $user->update([
            'otp' => $otp,
            'otp_expires_at' => now()->addHours(24), // OTP expires in 24 hours
            'password' => Hash::make($otp), // Update password with new OTP
        ]);

        // Send OTP email
        Mail::to($user->email)->send(new UserOtpMail($otp, $user->name));

        return redirect()->route('users.index')->with('success', 'OTP has been resent to ' . $user->email);
    }
}

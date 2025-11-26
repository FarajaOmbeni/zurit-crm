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
        // Only admins can access user management
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $users = User::with('manager')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'manager_name' => $user->manager ? $user->manager->name : null,
                    'is_active' => $user->is_active,
                    'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return Inertia::render('Users/Index', [
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        // Only admins can create users
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'role' => ['required', 'in:admin,manager,team_member'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'is_active' => ['boolean'],
        ]);

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
}

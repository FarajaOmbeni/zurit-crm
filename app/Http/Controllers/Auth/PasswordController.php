<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back();
    }

    /**
     * Display the password reset form for authenticated users (first-time login).
     */
    public function showResetForm(Request $request): Response
    {
        // Only allow if user must reset password
        if (!$request->user()->must_reset_password) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Auth/ResetPasswordFirstTime');
    }

    /**
     * Handle password reset for authenticated users (first-time login).
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        // Only allow if user must reset password
        if (!$request->user()->must_reset_password) {
            return redirect()->route('dashboard');
        }

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = $request->user();
        $user->update([
            'password' => Hash::make($validated['password']),
            'must_reset_password' => false,
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        return redirect()->route('dashboard')->with('status', 'Password has been set successfully.');
    }
}

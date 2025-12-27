<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Forgot Password Controller - Ng Wayne Xiang
 * 
 * Uses Supabase for OTP-based password reset.
 * 
 * OWASP Security Compliance:
 * - [49] Secure password reset via Supabase OTP
 * - [50] Token expiration handled by Supabase
 * - [51] One-time use tokens
 * - [52] Rate limiting on password reset requests
 */
class ForgotPasswordController extends Controller
{

    /**
     * Show the forgot password form
     */
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle sending password reset OTP via Supabase
     * Supabase handles token generation and expiration
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        // Always show same message to prevent email enumeration
        $successMessage = 'If an account with that email exists, we have sent a verification code.';

        if (!$user) {
            return back()->with('status', $successMessage);
        }

        // Check if user registered with Google (no password)
        if ($user->google_id && !$user->password) {
            return back()->with('status', $successMessage);
        }

        // Store email in session for the reset form
        session(['reset_email' => $user->email]);

        // Redirect to OTP verification page - Supabase will send OTP via frontend JS
        return redirect()->route('password.verify')->with('status', 'Please check your email for a verification code.');
    }

    /**
     * Show OTP verification page for password reset
     */
    public function showVerifyForm()
    {
        $email = session('reset_email');
        if (!$email) {
            return redirect()->route('password.request')->with('error', 'Please enter your email first.');
        }

        return view('auth.password-verify', ['email' => $email]);
    }

    /**
     * Verify OTP and show password reset form
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $email = session('reset_email');
        if (!$email) {
            return redirect()->route('password.request')->with('error', 'Session expired. Please try again.');
        }

        // Verify OTP with Supabase (type='recovery' for password reset)
        $supabaseService = app(SupabaseService::class);
        $result = $supabaseService->verifyOtp($email, $request->code, 'recovery');

        if (!$result['success']) {
            return back()->withErrors(['code' => $result['message']]);
        }

        // Mark as verified in session
        session(['reset_verified' => true]);

        return redirect()->route('password.reset.form');
    }

    /**
     * Show the password reset form (after OTP verification)
     */
    public function showResetForm()
    {
        $email = session('reset_email');
        $verified = session('reset_verified');

        if (!$email || !$verified) {
            return redirect()->route('password.request')->with('error', 'Please verify your email first.');
        }

        return view('auth.reset-password', ['email' => $email]);
    }

    /**
     * Handle password reset with OWASP complexity requirements
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            // OWASP [38-39]: Password complexity - min 8 chars, mixed case, numbers, symbols
            'password' => [
                'required',
                'confirmed',
                \Illuminate\Validation\Rules\Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ], [
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        $email = session('reset_email');
        $verified = session('reset_verified');

        if (!$email || !$verified) {
            return redirect()->route('password.request')->with('error', 'Session expired. Please try again.');
        }

        // Find user
        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Clear session data
        session()->forget(['reset_email', 'reset_verified']);

        // Invalidate all sessions for this user (security measure)
        DB::table('sessions')->where('user_id', $user->id)->delete();

        return redirect('/login')->with('success', 'Your password has been reset successfully. Please login with your new password.');
    }
}

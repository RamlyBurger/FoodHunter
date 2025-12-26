<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmailVerification;
use App\Services\AuthService;
use App\Patterns\Strategy\AuthContext;
use App\Patterns\Strategy\PasswordAuthStrategy;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;
use App\Services\SecurityLogService;

/**
 * Web Auth Controller - Student 1
 * 
 * Uses Strategy Pattern for authentication via AuthService.
 * Security: Rate Limiting, Session Regeneration
 */
class AuthController extends Controller
{
    use ApiResponse;
    
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Login using Strategy Pattern via AuthService
     * Security: Rate limiting, session regeneration, single-device login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Use Strategy Pattern via AuthService for authentication
        $result = $this->authService->attemptLogin(
            $request->email,
            $request->password,
            $request->ip()
        );

        if (!$result['success']) {
            return back()->withErrors([
                'email' => $result['message'],
            ]);
        }

        // Log in the user for web session
        Auth::login($result['user'], $request->boolean('remember'));
        
        // OWASP [66-67]: Single-device login - invalidate all other sessions
        $currentSessionId = session()->getId();
        $deletedSessions = DB::table('sessions')
            ->where('user_id', $result['user']->id)
            ->where('id', '!=', $currentSessionId)
            ->delete();
        
        if ($deletedSessions > 0) {
            SecurityLogService::logSessionRevoked(
                $result['user']->id,
                $result['user']->email,
                $deletedSessions,
                'new_login_from_another_device',
                $request->ip()
            );
        }
        
        // OWASP [66-67]: Session regeneration to prevent session fixation
        $request->session()->regenerate();
        
        return redirect()->intended('/');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle registration with OWASP password complexity requirements
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100', 'regex:/^[\pL\s\-\.]+$/u'],
            'email' => ['required', 'email:rfc', 'unique:users,email', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[\d\s\+\-\(\)]+$/'],
            'role' => ['required', 'in:customer,vendor'],
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
            'name.regex' => 'Name can only contain letters, spaces, hyphens, and periods.',
            'phone.regex' => 'Phone number format is invalid.',
        ]);

        // Store registration data in session
        $request->session()->put('registration_data', [
            'name' => $request->name,
            'email' => strtolower($request->email),
            'phone' => $request->phone,
            'password' => $request->password,
            'role' => $request->role,
        ]);

        // Supabase will handle OTP sending via frontend JS
        return redirect()->route('verify.show');
    }

    public function showVerify()
    {
        $registrationData = session('registration_data');
        if (!$registrationData) {
            return redirect()->route('register')->with('error', 'Please complete the registration form first.');
        }

        return view('auth.verify', ['email' => $registrationData['email']]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $registrationData = session('registration_data');
        if (!$registrationData) {
            return redirect()->route('register')->with('error', 'Registration session expired. Please try again.');
        }

        // Verify OTP with Supabase
        $supabaseService = app(\App\Services\SupabaseService::class);
        $result = $supabaseService->verifyOtp($registrationData['email'], $request->code);

        if (!$result['success']) {
            return back()->withErrors(['code' => $result['message']]);
        }

        // Create the user with selected role
        $user = User::create([
            'name' => $registrationData['name'],
            'email' => $registrationData['email'],
            'phone' => $registrationData['phone'],
            'password' => Hash::make($registrationData['password']),
            'role' => $registrationData['role'] ?? 'customer',
            'email_verified_at' => now(),
        ]);

        // If vendor, create vendor record
        if ($registrationData['role'] === 'vendor') {
            \App\Models\Vendor::create([
                'user_id' => $user->id,
                'store_name' => $registrationData['name'] . "'s Store",
                'description' => 'Welcome to my store!',
                'is_open' => false,
            ]);
        }

        // Clear session
        $request->session()->forget('registration_data');

        Auth::login($user);

        $welcomeMessage = $registrationData['role'] === 'vendor' 
            ? 'Welcome to FoodHunter! Your vendor account has been created.' 
            : 'Welcome to FoodHunter! Your email has been verified.';

        return redirect('/')->with('success', $welcomeMessage);
    }

    public function resendCode(Request $request)
    {
        $registrationData = session('registration_data');
        if (!$registrationData) {
            return redirect()->route('register')->with('error', 'Registration session expired.');
        }

        $verification = EmailVerification::createForSignup($registrationData['email']);
        
        // Store OTP data in session for frontend EmailJS to send
        session(['otp_data' => [
            'email' => $registrationData['email'],
            'code' => $verification->code,
            'sent' => false,
        ]]);
        
        return back()->with('success', 'A new verification code has been generated.');
    }

    public function resendCodeAjax(Request $request)
    {
        $registrationData = session('registration_data');
        if (!$registrationData) {
            return $this->errorResponse('Registration session expired.', 400);
        }

        $verification = EmailVerification::createForSignup($registrationData['email']);
        
        // Store OTP data in session
        session(['otp_data' => [
            'email' => $registrationData['email'],
            'code' => $verification->code,
            'sent' => false,
        ]]);
        
        return $this->successResponse([
            'email' => $registrationData['email'],
            'code' => $verification->code,
        ]);
    }

    public function markOtpSent(Request $request)
    {
        $otpData = session('otp_data');
        if ($otpData) {
            $otpData['sent'] = true;
            session(['otp_data' => $otpData]);
        }
        return $this->successResponse(null);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Logged out successfully.');
    }

    /**
     * Show change password page
     */
    public function showChangePassword()
    {
        return view('auth.change-password');
    }

    /**
     * Request password change - store new password in session
     * OTP will be sent via Supabase from frontend
     */
    public function requestPasswordChange(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return $this->errorResponse('Current password is incorrect.', 400);
        }

        // Check if new password is different
        if (Hash::check($request->new_password, $user->password)) {
            return $this->errorResponse('New password must be different from current password.', 400);
        }

        // Store new password in session (OTP will be handled by Supabase)
        session([
            'password_change_new_password' => Hash::make($request->new_password),
            'password_change_expires' => now()->addMinutes(15),
        ]);

        return $this->successResponse(null, 'Please verify with OTP sent to your email.');
    }

    /**
     * Show OTP verification page
     */
    public function showChangePasswordVerify()
    {
        if (!session()->has('password_change_new_password')) {
            return redirect()->route('auth.change-password')
                ->with('error', 'Please start the password change process first.');
        }

        return view('auth.change-password-verify', [
            'email' => Auth::user()->email
        ]);
    }

    /**
     * Verify OTP and change password using Supabase
     */
    public function verifyPasswordChange(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        if (!session()->has('password_change_new_password')) {
            return $this->errorResponse('Session expired. Please start again.', 400);
        }

        // Check if session expired
        if (now()->gt(session('password_change_expires'))) {
            session()->forget(['password_change_new_password', 'password_change_expires']);
            return $this->errorResponse('Session has expired. Please start again.', 400);
        }

        $user = Auth::user();

        // Verify OTP with Supabase (type='recovery' for password change)
        $supabaseService = app(\App\Services\SupabaseService::class);
        $result = $supabaseService->verifyOtp($user->email, $request->otp, 'recovery');

        if (!$result['success']) {
            return $this->errorResponse($result['message'], 400);
        }

        // Update password
        $user->password = session('password_change_new_password');
        $user->save();

        // Clear session
        session()->forget(['password_change_new_password', 'password_change_expires']);

        // Log security event
        SecurityLogService::logPasswordChange($user->id, request()->ip());

        // Determine redirect based on user type
        $redirect = $user->vendor ? '/profile/vendor' : '/profile';

        return $this->successResponse(['redirect' => $redirect], 'Password changed successfully.');
    }

    /**
     * Resend OTP for password change (handled by frontend Supabase)
     */
    public function resendPasswordChangeOtp(Request $request)
    {
        if (!session()->has('password_change_new_password')) {
            return $this->errorResponse('Session expired. Please start again.', 400);
        }

        // Update session expiry
        session([
            'password_change_expires' => now()->addMinutes(15),
        ]);

        return $this->successResponse(null, 'Ready to resend OTP.');
    }

    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Find or create user
            $user = User::where('email', $googleUser->getEmail())->first();
            
            if (!$user) {
                // Create new user
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => Hash::make(uniqid() . time()), // Random password
                    'role' => 'customer',
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                ]);
                
            } else {
                // Update Google ID if not set
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleUser->getId(),
                        'avatar' => $googleUser->getAvatar(),
                    ]);
                }
            }
            
            // OWASP [66-67]: Single-device login - invalidate ALL existing sessions for this user
            // Count existing sessions before deletion
            $existingSessions = DB::table('sessions')
                ->where('user_id', $user->id)
                ->count();
            
            \Log::info('Google Login - Before deletion', [
                'user_id' => $user->id,
                'existing_sessions' => $existingSessions,
                'all_sessions' => DB::table('sessions')->count()
            ]);
            
            // Delete ALL sessions for this user
            $deletedSessions = DB::table('sessions')
                ->where('user_id', $user->id)
                ->delete();
            
            \Log::info('Google Login - After deletion', [
                'user_id' => $user->id,
                'deleted_sessions' => $deletedSessions
            ]);
            
            if ($deletedSessions > 0) {
                SecurityLogService::logSessionRevoked(
                    $user->id,
                    $user->email,
                    $deletedSessions,
                    'new_google_login_from_another_device',
                    request()->ip()
                );
            }
            
            // Revoke any API tokens
            $user->tokens()->delete();
            
            // Now login - this creates a new session
            Auth::login($user, true);
            
            // Regenerate session to ensure fresh session ID
            session()->regenerate();
            
            \Log::info('Google Login - After login', [
                'user_id' => $user->id,
                'new_session_id' => session()->getId(),
                'sessions_after' => DB::table('sessions')->where('user_id', $user->id)->count()
            ]);
            
            return redirect('/')->with('success', 'Welcome back, ' . $user->name . '!');
            
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['google' => 'Google login failed. Please try again.']);
        }
    }

    /**
     * Show change email page
     */
    public function showChangeEmail()
    {
        return view('auth.change-email');
    }

    /**
     * Request email change - store new email in session
     * OTP will be sent via Supabase from frontend
     */
    public function requestEmailChange(Request $request)
    {
        $request->validate([
            'new_email' => 'required|email|confirmed|unique:users,email',
        ]);

        $user = Auth::user();

        // Check if new email is different from current
        if ($request->new_email === $user->email) {
            return $this->errorResponse('New email must be different from current email.', 400);
        }

        // Store new email in session (OTP will be handled by Supabase)
        session([
            'email_change_new_email' => $request->new_email,
            'email_change_expires' => now()->addMinutes(15),
        ]);

        return $this->successResponse(null, 'Please verify your new email address.');
    }

    /**
     * Show email change OTP verification page
     */
    public function showChangeEmailVerify()
    {
        if (!session()->has('email_change_new_email')) {
            return redirect()->route('auth.change-email')
                ->with('error', 'Please start the email change process first.');
        }

        return view('auth.change-email-verify', [
            'email' => session('email_change_new_email')
        ]);
    }

    /**
     * Verify OTP and change email using Supabase
     */
    public function verifyEmailChange(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        if (!session()->has('email_change_new_email')) {
            return $this->errorResponse('Session expired. Please start again.', 400);
        }

        // Check if session expired
        if (now()->gt(session('email_change_expires'))) {
            session()->forget(['email_change_new_email', 'email_change_expires']);
            return $this->errorResponse('Session has expired. Please start again.', 400);
        }

        $newEmail = session('email_change_new_email');

        // Verify OTP with Supabase (type='magiclink' for email change)
        $supabaseService = app(\App\Services\SupabaseService::class);
        $result = $supabaseService->verifyOtp($newEmail, $request->otp, 'magiclink');

        if (!$result['success']) {
            return $this->errorResponse($result['message'], 400);
        }

        // Update email
        $user = Auth::user();
        $user->email = $newEmail;
        $user->save();

        // Clear session
        session()->forget(['email_change_new_email', 'email_change_expires']);

        // Log security event
        SecurityLogService::logEmailChange($user->id, $newEmail, request()->ip());

        // Determine redirect based on user type
        $redirect = $user->vendor ? '/profile/vendor' : '/profile';

        return $this->successResponse(['redirect' => $redirect], 'Email changed successfully.');
    }

    /**
     * Resend OTP for email change (handled by frontend Supabase)
     */
    public function resendEmailChangeOtp(Request $request)
    {
        if (!session()->has('email_change_new_email')) {
            return $this->errorResponse('Session expired. Please start again.', 400);
        }

        // Update session expiry
        session([
            'email_change_expires' => now()->addMinutes(15),
        ]);

        return $this->successResponse(null, 'Ready to resend OTP.');
    }
}

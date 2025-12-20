<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LoyaltyPoint;
use App\Services\SecurityLoggingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * AuthController - Handles user authentication
 * 
 * Design Patterns:
 * - Singleton Pattern: Auth facade
 * - Facade Pattern: Auth, Hash, Validator facades
 * - Repository Pattern: Using Eloquent ORM
 * 
 * Secure Coding Practices:
 * - Password hashing with bcrypt
 * - CSRF token validation
 * - Input validation and sanitization
 * - SQL injection prevention via ORM
 * - Session regeneration on login
 * - Error logging without exposing sensitive data
 * - [122] Log all authentication attempts, especially failures
 * - [107] Generic error messages without sensitive information
 */
class AuthController extends Controller
{
    private SecurityLoggingService $securityLogger;

    public function __construct(SecurityLoggingService $securityLogger)
    {
        $this->securityLogger = $securityLogger;
    }
    /**
     * Show login form
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectToDashboard();
        }
        return view('auth.login');
    }

    /**
     * Show registration form
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return $this->redirectToDashboard();
        }
        return view('auth.register');
    }

    /**
     * Handle user registration
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        try {
            // Input validation - prevents XSS and malformed data
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s]+$/'],
                'email' => ['required', 'string', 'email', 'max:100', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'],
                'role' => ['required', 'in:student,staff,vendor'],
                'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            ], [
                'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number.',
                'name.regex' => 'Name must contain only letters and spaces.',
                'phone.regex' => 'Invalid phone number format.',
            ]);

            if ($validator->fails()) {
                // [121] Log all input validation failures
                $this->securityLogger->logValidationFailure('user_registration', $validator->errors()->toArray());
                return back()
                    ->withErrors($validator)
                    ->withInput($request->except('password', 'password_confirmation'));
            }

            // Database transaction - ensures data integrity
            DB::beginTransaction();

            // Create user with hashed password
            $user = User::create([
                'name' => strip_tags($request->name), // XSS prevention
                'email' => strtolower(trim($request->email)), // Normalize email
                'password' => Hash::make($request->password), // Secure password hashing
                'role' => $request->role,
                'phone' => $request->phone ? strip_tags($request->phone) : null,
            ]);

            // Initialize loyalty points for new user
            LoyaltyPoint::create([
                'user_id' => $user->user_id,
                'points' => 0,
            ]);

            DB::commit();

            // [122] Log successful registration
            $this->securityLogger->logAuthenticationAttempt('register', true, [
                'user_id' => $user->user_id,
                'email' => $user->email,
                'role' => $user->role,
            ]);

            // Auto-login after registration
            Auth::login($user);
            
            // Regenerate session to prevent session fixation
            $request->session()->regenerate();

            return $this->redirectToDashboard()
                ->with('success', 'Registration successful! Welcome to FoodHunter.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // [126] Log all system exceptions
            // [107] Generic error - no sensitive details exposed
            $this->securityLogger->logException($e, 'user_registration');

            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', 'Registration failed. Please try again.');
        }
    }

    /**
     * Handle user login
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        try {
            // Input validation
            $validator = Validator::make($request->all(), [
                'email' => ['required', 'email', 'max:100'],
                'password' => ['required', 'string'],
            ]);

            if ($validator->fails()) {
                // [121] Log validation failure
                $this->securityLogger->logValidationFailure('user_login', $validator->errors()->toArray());
                return back()
                    ->withErrors($validator)
                    ->withInput($request->only('email'));
            }

            // Sanitize input
            $credentials = [
                'email' => strtolower(trim($request->email)),
                'password' => $request->password,
            ];

            // Rate limiting would be implemented here in production
            // using Laravel's built-in RateLimiter or middleware

            // Attempt authentication
            $remember = $request->boolean('remember');
            
            if (Auth::attempt($credentials, $remember)) {
                // Regenerate session to prevent session fixation
                $request->session()->regenerate();

                $user = Auth::user();

                // [122] Log successful login
                $this->securityLogger->logAuthenticationAttempt('login', true, [
                    'user_id' => $user->user_id,
                    'email' => $user->email,
                    'role' => $user->role,
                ]);

                return $this->redirectToDashboard()
                    ->with('success', 'Welcome back, ' . $user->name . '!');
            }

            // [122] Log failed login attempt
            $this->securityLogger->logAuthenticationAttempt('login', false, [
                'email' => $credentials['email'],
            ]);

            // [107] Generic error message to prevent user enumeration
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Invalid email or password.');

        } catch (\Exception $e) {
            // Log error
            Log::error('Login failed', [
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Login failed. Please try again.');
        }
    }

    /**
     * Handle user logout
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        try {
            $user = Auth::user();

            // Log logout
            if ($user) {
                Log::info('User logged out', [
                    'user_id' => $user->user_id,
                    'email' => $user->email,
                ]);
            }

            Auth::logout();

            // Invalidate session
            $request->session()->invalidate();
            
            // Regenerate CSRF token
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('success', 'You have been logged out successfully.');

        } catch (\Exception $e) {
            Log::error('Logout failed', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('login')
                ->with('error', 'Logout failed. Please try again.');
        }
    }

    /**
     * Redirect user to appropriate dashboard based on role
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    private function redirectToDashboard()
    {
        $user = Auth::user();

        // Role-based redirection
        if ($user->role === 'vendor') {
            return redirect()->route('vendor.dashboard');
        }

        // Students, staff, and admins go to home
        return redirect()->route('home');
    }
}

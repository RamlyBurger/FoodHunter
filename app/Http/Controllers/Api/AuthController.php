<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\AuthService;
use App\Services\NotificationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Auth Controller - Student 1
 * 
 * Design Pattern: Strategy Pattern (for authentication methods)
 * Security: Rate Limiting, Session Regeneration
 */
class AuthController extends Controller
{
    use ApiResponse;

    private AuthService $authService;
    private NotificationService $notificationService;

    public function __construct(AuthService $authService, NotificationService $notificationService)
    {
        $this->authService = $authService;
        $this->notificationService = $notificationService;
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request->validated());

        $token = $user->createToken('auth-token')->plainTextToken;

        // Web Service: Consume Notification Service (Student 5)
        $this->notificationService->send(
            $user->id,
            'welcome',
            'Welcome to FoodHunter!',
            'Thank you for joining FoodHunter. Start exploring delicious food now!',
            ['registration_date' => now()->toDateString()]
        );

        return $this->createdResponse([
            'user' => $this->formatUser($user),
            'token' => $token,
        ], 'Registration successful');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        // Uses Strategy Pattern via AuthService
        $result = $this->authService->attemptLogin(
            $request->email,
            $request->password,
            $request->ip()
        );

        if (!$result['success']) {
            if (isset($result['locked_out']) && $result['locked_out']) {
                return $this->tooManyRequestsResponse($result['message']);
            }
            return $this->unauthorizedResponse($result['message']);
        }

        // Security: Session regeneration handled by Sanctum token refresh

        return $this->successResponse([
            'user' => $this->formatUser($result['user']),
            'token' => $result['token'],
        ], 'Login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logged out successfully');
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->successResponse($this->formatUser($user));
    }

    /**
     * Web Service: Expose - Validate Token API
     * Other modules consume this to validate user tokens
     */
    public function validateToken(Request $request): JsonResponse
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return $this->unauthorizedResponse('No token provided');
        }

        $user = $this->authService->validateToken($token);

        if (!$user) {
            return $this->unauthorizedResponse('Invalid or expired token');
        }

        return $this->successResponse([
            'valid' => true,
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
        ]);
    }

    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'created_at' => $user->created_at,
        ];
    }
}

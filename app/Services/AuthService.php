<?php

namespace App\Services;

use App\Models\User;
use App\Patterns\Strategy\AuthContext;
use App\Patterns\Strategy\PasswordAuthStrategy;
use App\Patterns\Strategy\TokenAuthStrategy;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

/**
 * Auth Service - Ng Wayne Xiang
 * 
 * Handles authentication with Strategy Pattern and security features:
 * - Rate Limiting (Brute Force Protection) [OWASP 41, 94]
 * - Session Regeneration (Session Hijacking Protection) [OWASP 66-67]
 * - Generic Error Messages [OWASP 33]
 * - Security Logging [OWASP 122]
 */
class AuthService
{
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES = 15;

    private AuthContext $authContext;

    public function __construct()
    {
        $this->authContext = new AuthContext(new PasswordAuthStrategy());
    }

    public function attemptLogin(string $email, string $password, string $ipAddress): array
    {
        // OWASP [41, 94]: Rate Limiting - prevent brute force attacks
        if ($this->isLockedOut($email, $ipAddress)) {
            SecurityLogService::logRateLimitExceeded($email, '/api/auth/login');
            SecurityLogService::logAuthAttempt($email, false, $ipAddress, 'rate_limited');
            
            return [
                'success' => false,
                // OWASP [33]: Generic error message - don't reveal lockout details
                'message' => 'Invalid username and/or password.',
                'locked_out' => true,
            ];
        }

        $user = $this->authContext->authenticate([
            'email' => $email,
            'password' => $password,
        ]);

        if (!$user) {
            $this->recordFailedAttempt($email, $ipAddress);
            
            // OWASP [122]: Log failed authentication attempt
            SecurityLogService::logAuthAttempt($email, false, $ipAddress, 'invalid_credentials');
            
            return [
                'success' => false,
                // OWASP [33]: Generic error message - don't reveal which part was wrong
                'message' => 'Invalid username and/or password.',
            ];
        }

        // Clear failed attempts on successful login
        $this->clearFailedAttempts($email, $ipAddress);

        // OWASP [122]: Log successful authentication
        SecurityLogService::logAuthAttempt($email, true, $ipAddress);

        // OWASP [66-67]: Single-device login - revoke ALL existing tokens before creating new one
        // This ensures user can only be logged in from one device at a time
        $existingTokenCount = $user->tokens()->count();
        if ($existingTokenCount > 0) {
            $user->tokens()->delete();
            SecurityLogService::logSessionRevoked(
                $user->id,
                $email,
                $existingTokenCount,
                'new_login_from_another_device',
                $ipAddress
            );
        }

        // Create new token (only one active token per user)
        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'success' => true,
            'user' => $user,
            'token' => $token,
        ];
    }

    public function validateToken(string $token): ?User
    {
        $this->authContext->setStrategy(new TokenAuthStrategy());
        return $this->authContext->authenticate(['token' => $token]);
    }

    public function register(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'role' => 'customer',
        ]);
    }

    // Rate Limiting Methods
    private function getCacheKey(string $email, string $ip): string
    {
        return 'login_attempts:' . md5($email . $ip);
    }

    private function getFailedAttempts(string $email, string $ip): int
    {
        return (int) Cache::get($this->getCacheKey($email, $ip), 0);
    }

    private function recordFailedAttempt(string $email, string $ip): void
    {
        $key = $this->getCacheKey($email, $ip);
        $attempts = $this->getFailedAttempts($email, $ip) + 1;
        Cache::put($key, $attempts, now()->addMinutes(self::LOCKOUT_MINUTES));
    }

    private function clearFailedAttempts(string $email, string $ip): void
    {
        Cache::forget($this->getCacheKey($email, $ip));
    }

    private function isLockedOut(string $email, string $ip): bool
    {
        return $this->getFailedAttempts($email, $ip) >= self::MAX_ATTEMPTS;
    }

    private function getLockoutMinutesRemaining(string $email, string $ip): int
    {
        $key = $this->getCacheKey($email, $ip);
        $ttl = Cache::getStore()->get($key . ':ttl');
        return $ttl ? (int) ceil($ttl / 60) : self::LOCKOUT_MINUTES;
    }
}

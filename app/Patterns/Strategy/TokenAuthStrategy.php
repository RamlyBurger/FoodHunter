<?php

namespace App\Patterns\Strategy;

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Strategy Pattern - Token Authentication Strategy
 * Student 1: User & Authentication Module
 * 
 * Authenticates users using API tokens (Sanctum).
 */
class TokenAuthStrategy implements AuthStrategyInterface
{
    public function authenticate(array $credentials): ?User
    {
        $token = $credentials['token'] ?? null;

        if (!$token) {
            return null;
        }

        // Remove 'Bearer ' prefix if present
        if (str_starts_with($token, 'Bearer ')) {
            $token = substr($token, 7);
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return null;
        }

        // Check if token is expired (optional: tokens can have expiration)
        if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
            return null;
        }

        return $accessToken->tokenable;
    }

    public function getStrategyName(): string
    {
        return 'token';
    }
}

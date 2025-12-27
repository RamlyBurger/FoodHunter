<?php

namespace App\Patterns\Strategy;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Strategy Pattern - Password Authentication Strategy
 * Ng Wayne Xiang: User & Authentication Module
 * 
 * Authenticates users using email and password credentials.
 */
class PasswordAuthStrategy implements AuthStrategyInterface
{
    public function authenticate(array $credentials): ?User
    {
        $email = $credentials['email'] ?? null;
        $password = $credentials['password'] ?? null;

        if (!$email || !$password) {
            return null;
        }

        $user = User::where('email', strtolower($email))->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }

    public function getStrategyName(): string
    {
        return 'password';
    }
}

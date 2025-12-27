<?php

namespace App\Patterns\Strategy;

use App\Models\User;

/**
 * Strategy Pattern - Authentication Strategy Interface
 * Ng Wayne Xiang: User & Authentication Module
 * 
 * This interface defines the contract for different authentication strategies.
 * Each strategy implements a different way to authenticate users.
 */
interface AuthStrategyInterface
{
    /**
     * Authenticate user with given credentials
     * 
     * @param array $credentials
     * @return User|null
     */
    public function authenticate(array $credentials): ?User;

    /**
     * Get the strategy name for logging/debugging
     * 
     * @return string
     */
    public function getStrategyName(): string;
}

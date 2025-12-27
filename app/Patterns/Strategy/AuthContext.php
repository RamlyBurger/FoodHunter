<?php

namespace App\Patterns\Strategy;

use App\Models\User;

/**
 * Strategy Pattern - Auth Context
 * Ng Wayne Xiang: User & Authentication Module
 * 
 * The Context maintains a reference to one of the Strategy objects.
 * The Context does not know the concrete class of a strategy.
 */
class AuthContext
{
    private AuthStrategyInterface $strategy;

    public function __construct(AuthStrategyInterface $strategy)
    {
        $this->strategy = $strategy;
    }

    public function setStrategy(AuthStrategyInterface $strategy): void
    {
        $this->strategy = $strategy;
    }

    public function authenticate(array $credentials): ?User
    {
        return $this->strategy->authenticate($credentials);
    }

    public function getStrategyName(): string
    {
        return $this->strategy->getStrategyName();
    }
}

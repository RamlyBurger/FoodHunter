<?php

namespace App\Patterns\State;

use App\Models\Order;
use InvalidArgumentException;

/**
 * State Pattern - Order State Manager
 * Student 3: Order & Pickup Module
 * 
 * Manages order state transitions using the State pattern.
 */
class OrderStateManager
{
    private static array $states = [
        'pending' => PendingState::class,
        'confirmed' => ConfirmedState::class,
        'preparing' => PreparingState::class,
        'ready' => ReadyState::class,
    ];

    public static function getState(Order $order): OrderStateInterface
    {
        $stateClass = self::$states[$order->status] ?? null;
        
        if (!$stateClass) {
            throw new InvalidArgumentException("Unknown order state: {$order->status}");
        }

        return new $stateClass();
    }

    public static function confirm(Order $order): bool
    {
        return self::getState($order)->confirm($order);
    }

    public static function startPreparing(Order $order): bool
    {
        return self::getState($order)->startPreparing($order);
    }

    public static function markReady(Order $order): bool
    {
        return self::getState($order)->markReady($order);
    }

    public static function complete(Order $order): bool
    {
        return self::getState($order)->complete($order);
    }

    public static function cancel(Order $order, ?string $reason = null): bool
    {
        return self::getState($order)->cancel($order, $reason);
    }

    public static function canTransitionTo(Order $order, string $newState): bool
    {
        return self::getState($order)->canTransitionTo($newState);
    }
}

<?php

namespace App\Patterns\State;

use App\Models\Order;

/**
 * State Pattern - Abstract Order State
 * Low Nam Lee: Order & Pickup Module
 * 
 * Base class providing default implementations for state transitions.
 */
abstract class AbstractOrderState implements OrderStateInterface
{
    protected array $allowedTransitions = [];

    public function canTransitionTo(string $newState): bool
    {
        return in_array($newState, $this->allowedTransitions);
    }

    public function confirm(Order $order): bool
    {
        return false;
    }

    public function startPreparing(Order $order): bool
    {
        return false;
    }

    public function markReady(Order $order): bool
    {
        return false;
    }

    public function complete(Order $order): bool
    {
        return false;
    }

    public function cancel(Order $order, ?string $reason = null): bool
    {
        return false;
    }

    protected function updateOrderStatus(Order $order, string $status, array $extra = []): bool
    {
        $data = array_merge(['status' => $status], $extra);
        return $order->update($data);
    }
}

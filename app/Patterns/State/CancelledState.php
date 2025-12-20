<?php

namespace App\Patterns\State;

use App\Models\Order;

/**
 * Cancelled State - Order cancelled
 */
class CancelledState implements OrderState
{
    public function handle(Order $order): void
    {
        // Cancel state - mark cancellation time
        if (!$order->cancelled_at) {
            $order->update(['cancelled_at' => now()]);
        }
    }

    public function next(Order $order): bool
    {
        // No next state - this is final
        return false;
    }

    public function canCancel(): bool
    {
        return false;
    }

    public function getStateName(): string
    {
        return 'cancelled';
    }

    public function getDescription(): string
    {
        return 'Order has been cancelled';
    }
}

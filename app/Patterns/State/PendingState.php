<?php

namespace App\Patterns\State;

use App\Models\Order;

/**
 * State Pattern - Pending State
 * Low Nam Lee: Order & Pickup Module
 */
class PendingState extends AbstractOrderState
{
    protected array $allowedTransitions = ['confirmed', 'cancelled'];

    public function getStateName(): string
    {
        return 'pending';
    }

    public function confirm(Order $order): bool
    {
        return $this->updateOrderStatus($order, 'confirmed', [
            'confirmed_at' => now(),
        ]);
    }

    public function cancel(Order $order, ?string $reason = null): bool
    {
        return $this->updateOrderStatus($order, 'cancelled', [
            'cancelled_at' => now(),
            'cancel_reason' => $reason,
        ]);
    }
}

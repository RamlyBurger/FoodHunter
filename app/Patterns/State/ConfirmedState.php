<?php

namespace App\Patterns\State;

use App\Models\Order;

/**
 * State Pattern - Confirmed State
 * Low Nam Lee: Order & Pickup Module
 */
class ConfirmedState extends AbstractOrderState
{
    protected array $allowedTransitions = ['preparing', 'cancelled'];

    public function getStateName(): string
    {
        return 'confirmed';
    }

    public function startPreparing(Order $order): bool
    {
        return $this->updateOrderStatus($order, 'preparing');
    }

    public function cancel(Order $order, ?string $reason = null): bool
    {
        return $this->updateOrderStatus($order, 'cancelled', [
            'cancelled_at' => now(),
            'cancel_reason' => $reason,
        ]);
    }
}

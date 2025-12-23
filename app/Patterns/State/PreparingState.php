<?php

namespace App\Patterns\State;

use App\Models\Order;

/**
 * State Pattern - Preparing State
 * Student 3: Order & Pickup Module
 */
class PreparingState extends AbstractOrderState
{
    protected array $allowedTransitions = ['ready'];

    public function getStateName(): string
    {
        return 'preparing';
    }

    public function markReady(Order $order): bool
    {
        $result = $this->updateOrderStatus($order, 'ready', [
            'ready_at' => now(),
        ]);

        if ($result && $order->pickup) {
            $order->pickup->update([
                'status' => 'ready',
                'ready_at' => now(),
            ]);
        }

        return $result;
    }
}

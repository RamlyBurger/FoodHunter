<?php

namespace App\Patterns\State;

use App\Models\Order;
use App\Models\StudentNotification;

/**
 * Preparing State - Order is being prepared
 */
class PreparingState implements OrderState
{
    public function handle(Order $order): void
    {
        // Update preparation start time
        if (!$order->preparing_at) {
            $order->update(['preparing_at' => now()]);
        }
    }

    public function next(Order $order): bool
    {
        $order->update(['status' => 'ready']);
        
        // Update pickup queue
        if ($order->pickup) {
            $order->pickup->update([
                'status' => 'ready',
                'ready_at' => now(),
            ]);
        }

        // Notify student
        StudentNotification::create([
            'user_id' => $order->user_id,
            'title' => 'Order Ready for Pickup',
            'message' => "Your order #{$order->order_id} is ready! Please collect it now.",
            'type' => 'order',
            'data' => json_encode([
                'order_id' => $order->order_id,
                'queue_number' => $order->pickup?->queue_number
            ]),
        ]);

        return true;
    }

    public function canCancel(): bool
    {
        return false; // Cannot cancel once preparing
    }

    public function getStateName(): string
    {
        return 'preparing';
    }

    public function getDescription(): string
    {
        return 'Order is being prepared by vendor';
    }
}

<?php

namespace App\Patterns\State;

use App\Models\Order;
use App\Models\StudentNotification;

/**
 * Ready State - Order ready for pickup
 */
class ReadyState implements OrderState
{
    public function handle(Order $order): void
    {
        // Set ready time
        if (!$order->ready_at) {
            $order->update(['ready_at' => now()]);
        }
    }

    public function next(Order $order): bool
    {
        $order->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // Update pickup status
        if ($order->pickup) {
            $order->pickup->update([
                'status' => 'collected',
                'collected_at' => now(),
            ]);
        }

        // Notify student
        StudentNotification::create([
            'user_id' => $order->user_id,
            'title' => 'Order Completed',
            'message' => "Thank you! Your order #{$order->order_id} has been completed.",
            'type' => 'order',
            'data' => json_encode(['order_id' => $order->order_id]),
        ]);

        return true;
    }

    public function canCancel(): bool
    {
        return false;
    }

    public function getStateName(): string
    {
        return 'ready';
    }

    public function getDescription(): string
    {
        return 'Order is ready for pickup';
    }
}

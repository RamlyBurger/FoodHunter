<?php

namespace App\Patterns\State;

use App\Models\Order;
use App\Models\StudentNotification;

/**
 * Accepted State - Vendor accepted, starting preparation
 */
class AcceptedState implements OrderState
{
    public function handle(Order $order): void
    {
        // Log acceptance time
        $order->update(['accepted_at' => now()]);
    }

    public function next(Order $order): bool
    {
        $order->update(['status' => 'preparing']);
        
        // Notify student
        StudentNotification::create([
            'user_id' => $order->user_id,
            'title' => 'Order in Preparation',
            'message' => "Your order #{$order->order_id} is now being prepared.",
            'type' => 'order',
            'data' => json_encode(['order_id' => $order->order_id]),
        ]);

        return true;
    }

    public function canCancel(): bool
    {
        return true;
    }

    public function getStateName(): string
    {
        return 'accepted';
    }

    public function getDescription(): string
    {
        return 'Order accepted by vendor';
    }
}

<?php

namespace App\Patterns\State;

use App\Models\Order;
use App\Models\StudentNotification;

/**
 * Pending State - Order placed, waiting for vendor acceptance
 */
class PendingState implements OrderState
{
    public function handle(Order $order): void
    {
        // Notify vendor about new order
        \App\Models\VendorNotification::create([
            'vendor_id' => $order->vendor_id,
            'title' => 'New Order Received',
            'message' => "Order #{$order->order_id} has been placed. Please review and accept.",
            'type' => 'order',
            'data' => json_encode(['order_id' => $order->order_id]),
        ]);
    }

    public function next(Order $order): bool
    {
        $order->update(['status' => 'accepted']);
        
        // Notify student
        StudentNotification::create([
            'user_id' => $order->user_id,
            'title' => 'Order Accepted',
            'message' => "Your order #{$order->order_id} has been accepted and is being prepared.",
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
        return 'pending';
    }

    public function getDescription(): string
    {
        return 'Order placed, waiting for vendor acceptance';
    }
}

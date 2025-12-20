<?php

namespace App\Patterns\Observer;

use App\Models\Order;
use App\Models\StudentNotification;
use App\Models\VendorNotification;

/**
 * Notification Observer - Sends notifications when queue changes
 */
class NotificationObserver implements QueueObserver
{
    public function update(Order $order, string $event): void
    {
        match($event) {
            'created' => $this->sendOrderPlacedNotification($order),
            'ready' => $this->sendOrderReadyNotification($order),
            'collected' => $this->sendOrderCollectedNotification($order),
            'cancelled' => $this->sendOrderCancelledNotification($order),
            default => null,
        };
    }

    private function sendOrderPlacedNotification(Order $order): void
    {
        // Notify vendor
        VendorNotification::create([
            'vendor_id' => $order->vendor_id,
            'title' => 'New Order in Queue',
            'message' => "Order #{$order->order_id} added to queue. Queue #" . $order->pickup?->queue_number,
            'type' => 'queue',
            'data' => json_encode([
                'order_id' => $order->order_id,
                'queue_number' => $order->pickup?->queue_number
            ]),
        ]);
    }

    private function sendOrderReadyNotification(Order $order): void
    {
        // Notify student
        StudentNotification::create([
            'user_id' => $order->user_id,
            'title' => 'Order Ready - Queue #' . $order->pickup?->queue_number,
            'message' => "Your order is ready for pickup! Please proceed to the counter.",
            'type' => 'pickup',
            'data' => json_encode([
                'order_id' => $order->order_id,
                'queue_number' => $order->pickup?->queue_number,
                'vendor' => $order->vendor->name
            ]),
        ]);
    }

    private function sendOrderCollectedNotification(Order $order): void
    {
        // Notify vendor
        VendorNotification::create([
            'vendor_id' => $order->vendor_id,
            'title' => 'Order Collected',
            'message' => "Order #{$order->order_id} has been collected by customer.",
            'type' => 'queue',
            'data' => json_encode(['order_id' => $order->order_id]),
        ]);
    }

    private function sendOrderCancelledNotification(Order $order): void
    {
        // Notify both parties
        StudentNotification::create([
            'user_id' => $order->user_id,
            'title' => 'Order Cancelled',
            'message' => "Your order #{$order->order_id} has been cancelled.",
            'type' => 'order',
            'data' => json_encode(['order_id' => $order->order_id]),
        ]);

        VendorNotification::create([
            'vendor_id' => $order->vendor_id,
            'title' => 'Order Cancelled',
            'message' => "Order #{$order->order_id} was cancelled.",
            'type' => 'queue',
            'data' => json_encode(['order_id' => $order->order_id]),
        ]);
    }

    public function getName(): string
    {
        return 'Notification Observer';
    }
}

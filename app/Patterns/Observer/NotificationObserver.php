<?php

namespace App\Patterns\Observer;

use App\Models\Notification;

/**
 * Observer Pattern - Notification Observer
 * Student 4: Cart, Checkout & Notifications Module
 * 
 * Creates in-app notifications when order events occur.
 */
class NotificationObserver implements ObserverInterface
{
    public function update(SubjectInterface $subject, string $event, array $data): void
    {
        match ($event) {
            'order.created' => $this->handleOrderCreated($data),
            'order.status_changed' => $this->handleStatusChanged($data),
            'order.completed' => $this->handleOrderCompleted($data),
            default => null,
        };
    }

    private function handleOrderCreated(array $data): void
    {
        Notification::create([
            'user_id' => $data['user_id'],
            'type' => 'order_created',
            'title' => 'Order Placed Successfully',
            'message' => "Your order #{$data['order_id']} has been placed and is awaiting confirmation.",
            'data' => ['order_id' => $data['order_id']],
        ]);
    }

    private function handleStatusChanged(array $data): void
    {
        $messages = [
            'confirmed' => 'Your order has been confirmed by the vendor.',
            'preparing' => 'Your order is now being prepared.',
            'ready' => 'Your order is ready for pickup!',
            'cancelled' => 'Your order has been cancelled.',
        ];

        $message = $messages[$data['new_status']] ?? "Order status changed to {$data['new_status']}.";

        Notification::create([
            'user_id' => $data['user_id'],
            'type' => 'order_status',
            'title' => 'Order Update',
            'message' => $message,
            'data' => [
                'order_id' => $data['order_id'],
                'status' => $data['new_status'],
            ],
        ]);
    }

    private function handleOrderCompleted(array $data): void
    {
        Notification::create([
            'user_id' => $data['user_id'],
            'type' => 'order_completed',
            'title' => 'Order Completed',
            'message' => 'Thank you for your order! Your order has been completed.',
            'data' => [
                'order_id' => $data['order_id'],
            ],
        ]);
    }
}

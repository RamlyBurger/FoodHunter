<?php

namespace App\Patterns\Observer;

use App\Models\Order;

/**
 * Observer Pattern - Order Subject
 * Student 4: Cart, Checkout & Notifications Module
 * 
 * Concrete subject that notifies observers about order events.
 */
class OrderSubject implements SubjectInterface
{
    private array $observers = [];
    private Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function attach(ObserverInterface $observer): void
    {
        $key = spl_object_hash($observer);
        $this->observers[$key] = $observer;
    }

    public function detach(ObserverInterface $observer): void
    {
        $key = spl_object_hash($observer);
        unset($this->observers[$key]);
    }

    public function notify(string $event, array $data = []): void
    {
        $data['order'] = $this->order;
        
        foreach ($this->observers as $observer) {
            $observer->update($this, $event, $data);
        }
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    // Event trigger methods
    public function orderCreated(): void
    {
        $this->notify('order.created', [
            'order_id' => $this->order->id,
            'user_id' => $this->order->user_id,
            'total' => $this->order->total,
        ]);
    }

    public function orderStatusChanged(string $oldStatus, string $newStatus): void
    {
        $this->notify('order.status_changed', [
            'order_id' => $this->order->id,
            'user_id' => $this->order->user_id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);
    }

    public function orderCompleted(): void
    {
        $this->notify('order.completed', [
            'order_id' => $this->order->id,
            'user_id' => $this->order->user_id,
            'total' => $this->order->total,
            'points_earned' => (int) floor((float) $this->order->total),
        ]);
    }
}

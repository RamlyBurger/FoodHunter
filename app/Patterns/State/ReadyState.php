<?php

namespace App\Patterns\State;

use App\Models\Order;
use App\Patterns\Observer\OrderSubject;
use App\Patterns\Observer\NotificationObserver;

/**
 * State Pattern - Ready State
 * Student 3: Order & Pickup Module
 */
class ReadyState extends AbstractOrderState
{
    protected array $allowedTransitions = ['completed'];

    public function getStateName(): string
    {
        return 'ready';
    }

    public function complete(Order $order): bool
    {
        $result = $this->updateOrderStatus($order, 'completed', [
            'completed_at' => now(),
        ]);

        if ($result) {
            // Update pickup status
            if ($order->pickup) {
                $order->pickup->update([
                    'status' => 'collected',
                    'collected_at' => now(),
                ]);
            }

            // Mark payment as paid if cash
            if ($order->payment && $order->payment->status === 'pending') {
                $order->payment->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
            }

            // Increment vendor's total orders
            if ($order->vendor) {
                $order->vendor->increment('total_orders');
            }

            // Trigger Observer Pattern - Send notification
            $order->refresh();
            $subject = new OrderSubject($order);
            $subject->attach(new NotificationObserver());
            $subject->orderCompleted();
        }

        return $result;
    }
}

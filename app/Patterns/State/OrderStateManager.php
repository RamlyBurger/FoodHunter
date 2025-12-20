<?php

namespace App\Patterns\State;

use App\Models\Order;

/**
 * Order State Manager - Context class for managing order states
 */
class OrderStateManager
{
    private OrderState $state;
    private Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->state = $this->getStateFromOrder($order);
    }

    /**
     * Get state object based on order status
     *
     * @param Order $order
     * @return OrderState
     */
    private function getStateFromOrder(Order $order): OrderState
    {
        return match($order->status) {
            'pending' => new PendingState(),
            'accepted' => new AcceptedState(),
            'preparing' => new PreparingState(),
            'ready' => new ReadyState(),
            'completed' => new CompletedState(),
            'cancelled' => new CancelledState(),
            default => new PendingState(),
        };
    }

    /**
     * Process current state
     *
     * @return void
     */
    public function process(): void
    {
        $this->state->handle($this->order);
    }

    /**
     * Move to next state
     *
     * @return bool
     */
    public function moveToNext(): bool
    {
        $success = $this->state->next($this->order);
        
        if ($success) {
            // Refresh order and update state
            $this->order->refresh();
            $this->state = $this->getStateFromOrder($this->order);
        }

        return $success;
    }

    /**
     * Cancel order if allowed
     *
     * @return bool
     */
    public function cancel(): bool
    {
        if (!$this->state->canCancel()) {
            return false;
        }

        $this->order->update(['status' => 'cancelled']);
        $this->state = new CancelledState();
        $this->state->handle($this->order);

        return true;
    }

    /**
     * Get current state name
     *
     * @return string
     */
    public function getCurrentStateName(): string
    {
        return $this->state->getStateName();
    }

    /**
     * Get state description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->state->getDescription();
    }

    /**
     * Can cancel in current state?
     *
     * @return bool
     */
    public function canCancel(): bool
    {
        return $this->state->canCancel();
    }
}

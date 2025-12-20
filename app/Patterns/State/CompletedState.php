<?php

namespace App\Patterns\State;

use App\Models\Order;

/**
 * Completed State - Order completed successfully
 */
class CompletedState implements OrderState
{
    public function handle(Order $order): void
    {
        // Final state - no actions needed
    }

    public function next(Order $order): bool
    {
        // No next state - this is final
        return false;
    }

    public function canCancel(): bool
    {
        return false;
    }

    public function getStateName(): string
    {
        return 'completed';
    }

    public function getDescription(): string
    {
        return 'Order completed successfully';
    }
}

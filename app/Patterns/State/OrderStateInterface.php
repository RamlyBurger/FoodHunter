<?php

namespace App\Patterns\State;

use App\Models\Order;

/**
 * State Pattern - Order State Interface
 * Low Nam Lee: Order & Pickup Module
 * 
 * Defines the interface for order states.
 * Each state handles transitions and actions specific to that state.
 */
interface OrderStateInterface
{
    public function getStateName(): string;
    
    public function canTransitionTo(string $newState): bool;
    
    public function confirm(Order $order): bool;
    
    public function startPreparing(Order $order): bool;
    
    public function markReady(Order $order): bool;
    
    public function complete(Order $order): bool;
    
    public function cancel(Order $order, ?string $reason = null): bool;
}

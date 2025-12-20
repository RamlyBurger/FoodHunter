<?php

namespace App\Patterns\State;

use App\Models\Order;

/**
 * State Pattern Interface for Payment & Order Processing Module
 * 
 * Defines the contract for order state management
 */
interface OrderState
{
    /**
     * Handle the current state
     *
     * @param Order $order
     * @return void
     */
    public function handle(Order $order): void;

    /**
     * Transition to next state
     *
     * @param Order $order
     * @return bool
     */
    public function next(Order $order): bool;

    /**
     * Can cancel from this state?
     *
     * @return bool
     */
    public function canCancel(): bool;

    /**
     * Get state name
     *
     * @return string
     */
    public function getStateName(): string;

    /**
     * Get state description
     *
     * @return string
     */
    public function getDescription(): string;
}

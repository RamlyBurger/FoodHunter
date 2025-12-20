<?php

namespace App\Patterns\Observer;

use App\Models\Order;

/**
 * Observer Pattern Interface for Pickup & Queue Management Module
 * 
 * Observers are notified when queue status changes
 */
interface QueueObserver
{
    /**
     * Update observer when queue changes
     *
     * @param Order $order
     * @param string $event Type of event (created, ready, collected, cancelled)
     * @return void
     */
    public function update(Order $order, string $event): void;

    /**
     * Get observer name
     *
     * @return string
     */
    public function getName(): string;
}

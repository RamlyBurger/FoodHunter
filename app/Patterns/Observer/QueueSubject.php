<?php

namespace App\Patterns\Observer;

use App\Models\Order;

/**
 * Queue Subject - Manages observers and notifies them of queue changes
 */
class QueueSubject
{
    private array $observers = [];

    /**
     * Attach an observer
     *
     * @param QueueObserver $observer
     * @return void
     */
    public function attach(QueueObserver $observer): void
    {
        $observerName = $observer->getName();
        
        if (!isset($this->observers[$observerName])) {
            $this->observers[$observerName] = $observer;
        }
    }

    /**
     * Detach an observer
     *
     * @param QueueObserver $observer
     * @return void
     */
    public function detach(QueueObserver $observer): void
    {
        $observerName = $observer->getName();
        unset($this->observers[$observerName]);
    }

    /**
     * Notify all observers of a queue event
     *
     * @param Order $order
     * @param string $event
     * @return void
     */
    public function notify(Order $order, string $event): void
    {
        foreach ($this->observers as $observer) {
            $observer->update($order, $event);
        }
    }

    /**
     * Get count of attached observers
     *
     * @return int
     */
    public function getObserverCount(): int
    {
        return count($this->observers);
    }

    /**
     * Get observer names
     *
     * @return array
     */
    public function getObserverNames(): array
    {
        return array_keys($this->observers);
    }
}

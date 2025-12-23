<?php

namespace App\Patterns\Observer;

/**
 * Observer Pattern - Observer Interface
 * Student 4: Cart, Checkout & Notifications Module
 * 
 * Defines the interface for objects that should be notified of changes.
 */
interface ObserverInterface
{
    /**
     * Receive update from subject
     * 
     * @param SubjectInterface $subject
     * @param string $event
     * @param array $data
     * @return void
     */
    public function update(SubjectInterface $subject, string $event, array $data): void;
}

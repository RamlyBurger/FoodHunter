<?php

namespace App\Patterns\Observer;

/**
 * Observer Pattern - Subject Interface
 * Student 4: Cart, Checkout & Notifications Module
 * 
 * Defines the interface for objects that can be observed.
 */
interface SubjectInterface
{
    public function attach(ObserverInterface $observer): void;
    
    public function detach(ObserverInterface $observer): void;
    
    public function notify(string $event, array $data): void;
}

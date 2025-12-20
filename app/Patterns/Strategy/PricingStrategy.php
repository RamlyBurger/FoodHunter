<?php

namespace App\Patterns\Strategy;

/**
 * Strategy Pattern Interface for Menu & Cart Management Module
 * 
 * Defines the contract for different pricing calculation strategies
 */
interface PricingStrategy
{
    /**
     * Calculate total price based on strategy
     *
     * @param float $subtotal
     * @param array $context Additional context (voucher, quantity, etc.)
     * @return array ['total' => float, 'discount' => float, 'details' => string]
     */
    public function calculateTotal(float $subtotal, array $context = []): array;

    /**
     * Get strategy name
     *
     * @return string
     */
    public function getName(): string;
}

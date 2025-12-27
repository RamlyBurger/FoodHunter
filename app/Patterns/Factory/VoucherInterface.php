<?php

namespace App\Patterns\Factory;

/**
 * Factory Pattern - Voucher Interface
 * Lee Kin Hang: Vendor Management Module
 * 
 * Defines the contract for all voucher types.
 */
interface VoucherInterface
{
    /**
     * Calculate the discount amount based on subtotal
     */
    public function calculateDiscount(float $subtotal): float;

    /**
     * Get the voucher type
     */
    public function getType(): string;

    /**
     * Get the voucher value
     */
    public function getValue(): float;

    /**
     * Check if voucher is applicable for the given subtotal
     */
    public function isApplicable(float $subtotal): bool;

    /**
     * Get formatted discount description
     */
    public function getDescription(): string;
}

<?php

namespace App\Patterns\Factory;

/**
 * Factory Pattern - Percentage Discount Voucher
 * Student 5: Vendor Management Module
 * 
 * Concrete product: Percentage discount (e.g., 10% off)
 */
class PercentageVoucher implements VoucherInterface
{
    private float $value; // percentage (e.g., 10 for 10%)
    private ?float $minOrder;
    private ?float $maxDiscount;
    private string $code;

    public function __construct(float $value, ?float $minOrder = null, ?float $maxDiscount = null, string $code = '')
    {
        $this->value = $value;
        $this->minOrder = $minOrder;
        $this->maxDiscount = $maxDiscount;
        $this->code = $code;
    }

    public function calculateDiscount(float $subtotal): float
    {
        if (!$this->isApplicable($subtotal)) {
            return 0.0;
        }

        $discount = $subtotal * ($this->value / 100);

        // Apply max discount cap if set
        if ($this->maxDiscount !== null && $this->maxDiscount > 0) {
            $discount = min($discount, $this->maxDiscount);
        }

        // Discount cannot exceed subtotal
        return min($discount, $subtotal);
    }

    public function getType(): string
    {
        return 'percentage';
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function isApplicable(float $subtotal): bool
    {
        if ($this->minOrder === null) {
            return true;
        }
        return $subtotal >= $this->minOrder;
    }

    public function getDescription(): string
    {
        $desc = number_format($this->value, 0) . "% off";
        if ($this->minOrder > 0) {
            $desc .= " (min order RM" . number_format($this->minOrder, 2) . ")";
        }
        if ($this->maxDiscount > 0) {
            $desc .= " (max RM" . number_format($this->maxDiscount, 2) . ")";
        }
        return $desc;
    }
}

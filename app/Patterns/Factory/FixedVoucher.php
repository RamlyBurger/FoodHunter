<?php

namespace App\Patterns\Factory;

/**
 * Factory Pattern - Fixed Discount Voucher
 * Student 5: Vendor Management Module
 * 
 * Concrete product: Fixed amount discount (e.g., RM5 off)
 */
class FixedVoucher implements VoucherInterface
{
    private float $value;
    private ?float $minOrder;
    private string $code;

    public function __construct(float $value, ?float $minOrder = null, string $code = '')
    {
        $this->value = $value;
        $this->minOrder = $minOrder;
        $this->code = $code;
    }

    public function calculateDiscount(float $subtotal): float
    {
        if (!$this->isApplicable($subtotal)) {
            return 0.0;
        }

        // Fixed discount cannot exceed subtotal
        return min($this->value, $subtotal);
    }

    public function getType(): string
    {
        return 'fixed';
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
        $desc = "RM" . number_format($this->value, 2) . " off";
        if ($this->minOrder > 0) {
            $desc .= " (min order RM" . number_format($this->minOrder, 2) . ")";
        }
        return $desc;
    }
}

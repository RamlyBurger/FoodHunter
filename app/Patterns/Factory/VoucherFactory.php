<?php

namespace App\Patterns\Factory;

use App\Models\Voucher;

/**
 * Factory Pattern - Voucher Factory
 * Lee Kin Hang: Vendor Management Module
 * 
 * Creates appropriate voucher objects based on voucher type.
 * This factory encapsulates the creation logic and returns
 * the correct voucher implementation.
 */
class VoucherFactory
{
    /**
     * Create a voucher object from database model
     */
    public static function createFromModel(Voucher $voucher): VoucherInterface
    {
        return self::create(
            $voucher->type,
            (float) $voucher->value,
            $voucher->min_order ? (float) $voucher->min_order : null,
            $voucher->max_discount ? (float) $voucher->max_discount : null,
            $voucher->code
        );
    }

    /**
     * Create a voucher object from parameters
     */
    public static function create(
        string $type,
        float $value,
        ?float $minOrder = null,
        ?float $maxDiscount = null,
        string $code = ''
    ): VoucherInterface {
        return match ($type) {
            'fixed' => new FixedVoucher($value, $minOrder, $code),
            'percentage' => new PercentageVoucher($value, $minOrder, $maxDiscount, $code),
            default => new FixedVoucher($value, $minOrder, $code),
        };
    }

    /**
     * Calculate discount for a voucher model
     */
    public static function calculateDiscount(Voucher $voucher, float $subtotal): float
    {
        $voucherObject = self::createFromModel($voucher);
        return $voucherObject->calculateDiscount($subtotal);
    }

    /**
     * Check if voucher is applicable
     */
    public static function isApplicable(Voucher $voucher, float $subtotal): bool
    {
        $voucherObject = self::createFromModel($voucher);
        return $voucherObject->isApplicable($subtotal);
    }

    /**
     * Get voucher description
     */
    public static function getDescription(Voucher $voucher): string
    {
        $voucherObject = self::createFromModel($voucher);
        return $voucherObject->getDescription();
    }
}

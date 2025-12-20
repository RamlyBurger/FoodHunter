<?php

namespace App\Patterns\Strategy;

/**
 * Voucher Discount Strategy - Applies percentage or fixed discount
 */
class VoucherDiscountStrategy implements PricingStrategy
{
    /**
     * Calculate total with voucher discount
     *
     * @param float $subtotal
     * @param array $context ['voucher_type' => 'percentage|fixed', 'voucher_value' => float]
     * @return array
     */
    public function calculateTotal(float $subtotal, array $context = []): array
    {
        $serviceFee = $context['service_fee'] ?? 2.00;
        $voucherType = $context['voucher_type'] ?? 'percentage';
        $voucherValue = $context['voucher_value'] ?? 10;

        // Calculate discount
        if ($voucherType === 'percentage') {
            $discount = ($subtotal * $voucherValue) / 100;
            $details = "{$voucherValue}% voucher discount applied";
        } else {
            $discount = $voucherValue;
            $details = "RM{$voucherValue} voucher discount applied";
        }

        // Ensure discount doesn't exceed subtotal
        $discount = min($discount, $subtotal);

        $total = ($subtotal - $discount) + $serviceFee;

        return [
            'subtotal' => round($subtotal, 2),
            'service_fee' => round($serviceFee, 2),
            'discount' => round($discount, 2),
            'total' => round($total, 2),
            'details' => $details,
        ];
    }

    /**
     * Get strategy name
     *
     * @return string
     */
    public function getName(): string
    {
        return 'Voucher Discount';
    }
}

<?php

namespace App\Patterns\Strategy;

/**
 * Regular Pricing Strategy - No discounts applied
 */
class RegularPricingStrategy implements PricingStrategy
{
    /**
     * Calculate total without any discounts
     *
     * @param float $subtotal
     * @param array $context
     * @return array
     */
    public function calculateTotal(float $subtotal, array $context = []): array
    {
        $serviceFee = $context['service_fee'] ?? 2.00;
        $total = $subtotal + $serviceFee;

        return [
            'subtotal' => round($subtotal, 2),
            'service_fee' => round($serviceFee, 2),
            'discount' => 0,
            'total' => round($total, 2),
            'details' => 'Regular pricing applied',
        ];
    }

    /**
     * Get strategy name
     *
     * @return string
     */
    public function getName(): string
    {
        return 'Regular Pricing';
    }
}

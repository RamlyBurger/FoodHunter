<?php

namespace App\Patterns\Strategy;

/**
 * Bulk Discount Strategy - Applies discount based on quantity
 */
class BulkDiscountStrategy implements PricingStrategy
{
    private array $tiers = [
        ['min' => 10, 'discount' => 15],
        ['min' => 5, 'discount' => 10],
        ['min' => 3, 'discount' => 5],
    ];

    /**
     * Calculate total with bulk discount
     *
     * @param float $subtotal
     * @param array $context ['quantity' => int]
     * @return array
     */
    public function calculateTotal(float $subtotal, array $context = []): array
    {
        $serviceFee = $context['service_fee'] ?? 2.00;
        $quantity = $context['quantity'] ?? 1;

        // Determine discount tier
        $discountPercentage = 0;
        foreach ($this->tiers as $tier) {
            if ($quantity >= $tier['min']) {
                $discountPercentage = $tier['discount'];
                break;
            }
        }

        $discount = ($subtotal * $discountPercentage) / 100;
        $total = ($subtotal - $discount) + $serviceFee;

        $details = $discountPercentage > 0 
            ? "Bulk discount: {$discountPercentage}% off for {$quantity} items"
            : "No bulk discount (minimum 3 items)";

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
        return 'Bulk Discount';
    }
}

<?php

namespace App\Patterns\Strategy;

/**
 * Cart Price Calculator - Context class that uses pricing strategies
 */
class CartPriceCalculator
{
    private PricingStrategy $strategy;

    /**
     * Set pricing strategy
     *
     * @param PricingStrategy $strategy
     * @return void
     */
    public function setStrategy(PricingStrategy $strategy): void
    {
        $this->strategy = $strategy;
    }

    /**
     * Calculate cart total using current strategy
     *
     * @param float $subtotal
     * @param array $context
     * @return array
     */
    public function calculate(float $subtotal, array $context = []): array
    {
        if (!isset($this->strategy)) {
            $this->strategy = new RegularPricingStrategy();
        }

        return $this->strategy->calculateTotal($subtotal, $context);
    }

    /**
     * Get strategy name
     *
     * @return string
     */
    public function getStrategyName(): string
    {
        return $this->strategy?->getName() ?? 'No Strategy Set';
    }
}

<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\MenuItem;
use App\Models\UserVoucher;
use App\Patterns\Builder\OrderBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Checkout Service - Low Nam Lee
 * 
 * Uses Builder Pattern with security features:
 * - Server-side Price Validation (Price Manipulation Protection)
 * - CSRF Protection (handled in middleware)
 */
class CheckoutService
{
    private const SERVICE_FEE = 2.00;

    public function getCartSummary(int $userId): array
    {
        $cartItems = CartItem::where('user_id', $userId)
            ->with('menuItem.vendor')
            ->get();

        // Security: Server-side price validation
        $subtotal = $this->calculateSubtotal($cartItems);
        $serviceFee = self::SERVICE_FEE;
        $total = $subtotal + $serviceFee;

        return [
            'items' => $cartItems->map(fn($item) => [
                'id' => $item->id,
                'menu_item_id' => $item->menu_item_id,
                'name' => $item->menuItem->name,
                'price' => (float) $item->menuItem->price, // Always use DB price
                'quantity' => $item->quantity,
                'subtotal' => (float) ($item->menuItem->price * $item->quantity),
            ]),
            'subtotal' => $subtotal,
            'service_fee' => $serviceFee,
            'discount' => 0.00,
            'total' => $total,
            'item_count' => $cartItems->sum('quantity'),
        ];
    }

    public function validateCart(int $userId): array
    {
        $cartItems = CartItem::where('user_id', $userId)
            ->with('menuItem')
            ->get();

        $errors = [];

        foreach ($cartItems as $item) {
            if (!$item->menuItem) {
                $errors[] = "Item #{$item->menu_item_id} no longer exists.";
                continue;
            }

            if (!$item->menuItem->is_available) {
                $errors[] = "{$item->menuItem->name} is no longer available.";
            }

        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    public function applyVoucher(int $userId, string $code, float $subtotal): array
    {
        // Find the voucher by code
        $voucher = \App\Models\Voucher::where('code', strtoupper($code))
            ->where('is_active', true)
            ->first();

        if (!$voucher || !$voucher->isValid()) {
            return [
                'success' => false,
                'message' => 'Invalid or expired voucher code.',
            ];
        }

        // Check if user has redeemed this voucher
        $userVoucher = UserVoucher::where('user_id', $userId)
            ->where('voucher_id', $voucher->id)
            ->first();

        if (!$userVoucher) {
            return [
                'success' => false,
                'message' => 'You have not redeemed this voucher.',
            ];
        }

        // Check per-user usage limit
        if ($userVoucher->usage_count >= $voucher->per_user_limit) {
            return [
                'success' => false,
                'message' => 'You have already used this voucher the maximum number of times.',
            ];
        }

        $discount = $voucher->calculateDiscount($subtotal);

        return [
            'success' => true,
            'discount' => $discount,
            'voucher' => [
                'id' => $voucher->id,
                'code' => $voucher->code,
                'name' => $voucher->name,
            ],
        ];
    }

    public function processCheckout(int $userId, string $paymentMethod, ?string $voucherCode = null, ?string $notes = null): array
    {
        $cartItems = CartItem::where('user_id', $userId)
            ->with('menuItem.vendor')
            ->get();

        if ($cartItems->isEmpty()) {
            return ['success' => false, 'message' => 'Cart is empty.'];
        }

        // Validate cart items
        $validation = $this->validateCart($userId);
        if (!$validation['valid']) {
            return ['success' => false, 'message' => 'Cart validation failed.', 'errors' => $validation['errors']];
        }

        // Group by vendor
        $vendorGroups = $cartItems->groupBy(fn($item) => $item->menuItem->vendor_id);

        try {
            DB::beginTransaction();

            $orders = [];
            $subtotal = $this->calculateSubtotal($cartItems);
            $discount = 0;
            $voucher = null;

            // Apply voucher
            if ($voucherCode) {
                $voucherResult = $this->applyVoucher($userId, $voucherCode, $subtotal);
                if ($voucherResult['success']) {
                    $discount = $voucherResult['discount'];
                    $voucher = UserVoucher::where('code', strtoupper($voucherCode))
                        ->where('user_id', $userId)
                        ->first();
                }
            }

            foreach ($vendorGroups as $vendorId => $items) {
                // Use Builder Pattern
                $builder = new OrderBuilder();
                
                $order = $builder
                    ->setCustomer($userId)
                    ->setVendor($vendorId)
                    ->setCartItems($items)
                    ->setNotes($notes)
                    ->setPaymentMethod($paymentMethod)
                    ->applyVoucher($voucherCode ?? '', $discount * ($items->sum(fn($i) => $i->menuItem->price * $i->quantity) / $subtotal))
                    ->calculateTotals()
                    ->build();

                $orders[] = $order;
            }

            // Mark voucher as used
            if ($voucher && $voucherCode) {
                $voucherModel = \App\Models\Voucher::where('code', strtoupper($voucherCode))->first();
                if ($voucherModel) {
                    $userVoucher = UserVoucher::where('user_id', $userId)
                        ->where('voucher_id', $voucherModel->id)
                        ->first();
                    if ($userVoucher) {
                        $userVoucher->increment('usage_count');
                        $userVoucher->update(['used_at' => now()]);
                    }
                    $voucherModel->increment('usage_count');
                }
            }

            // Clear cart
            CartItem::where('user_id', $userId)->delete();

            DB::commit();

            return [
                'success' => true,
                'orders' => $orders,
                'total_paid' => collect($orders)->sum('total'),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Checkout failed. Please try again.'];
        }
    }

    private function calculateSubtotal(Collection $cartItems): float
    {
        // Security: Always recalculate from database prices
        return $cartItems->sum(function ($item) {
            // Re-fetch price from database to prevent manipulation
            $freshItem = MenuItem::find($item->menu_item_id);
            return $freshItem ? (float) $freshItem->price * $item->quantity : 0;
        });
    }
}

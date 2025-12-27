<?php

namespace App\Patterns\Builder;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Pickup;
use App\Models\CartItem;
use Illuminate\Support\Collection;

/**
 * Builder Pattern - Order Builder
 * Low Nam Lee: Cart & Checkout Module
 * 
 * Separates the construction of a complex Order object from its representation.
 * Allows step-by-step construction of orders with items, payment, and pickup info.
 */
class OrderBuilder
{
    private array $orderData = [];
    private Collection $cartItems;
    private array $paymentData = [];
    private ?string $voucherCode = null;
    private float $discount = 0;

    public function __construct()
    {
        $this->cartItems = collect();
    }

    public function setCustomer(int $userId): self
    {
        $this->orderData['user_id'] = $userId;
        return $this;
    }

    public function setVendor(int $vendorId): self
    {
        $this->orderData['vendor_id'] = $vendorId;
        return $this;
    }

    public function setCartItems(Collection $cartItems): self
    {
        $this->cartItems = $cartItems;
        return $this;
    }

    public function setNotes(?string $notes): self
    {
        $this->orderData['notes'] = $notes;
        return $this;
    }

    public function setPaymentMethod(string $method): self
    {
        $this->paymentData['method'] = $method;
        return $this;
    }

    public function applyVoucher(string $code, float $discount): self
    {
        $this->voucherCode = $code;
        $this->discount = $discount;
        return $this;
    }

    public function calculateTotals(): self
    {
        $subtotal = $this->cartItems->sum(function ($item) {
            return $item->menuItem->price * $item->quantity;
        });

        $serviceFee = 2.00;
        $total = $subtotal + $serviceFee - $this->discount;

        $this->orderData['subtotal'] = $subtotal;
        $this->orderData['service_fee'] = $serviceFee;
        $this->orderData['discount'] = $this->discount;
        $this->orderData['total'] = max(0, $total);

        return $this;
    }

    public function build(): Order
    {
        // Generate order number
        $this->orderData['order_number'] = Order::generateOrderNumber();
        $this->orderData['status'] = 'pending';

        // Create order
        $order = Order::create($this->orderData);

        // Create order items
        foreach ($this->cartItems as $cartItem) {
            OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $cartItem->menu_item_id,
                'item_name' => $cartItem->menuItem->name,
                'unit_price' => $cartItem->menuItem->price,
                'quantity' => $cartItem->quantity,
                'subtotal' => $cartItem->menuItem->price * $cartItem->quantity,
                'special_instructions' => $cartItem->special_instructions,
            ]);

            // Update sold count
            $cartItem->menuItem->increment('total_sold', $cartItem->quantity);
        }

        // Create payment record
        $paymentStatus = $this->paymentData['method'] === 'cash' ? 'pending' : 'paid';
        Payment::create([
            'order_id' => $order->id,
            'amount' => $order->total,
            'method' => $this->paymentData['method'],
            'status' => $paymentStatus,
            'transaction_id' => $paymentStatus === 'paid' ? 'TXN-' . strtoupper(uniqid()) : null,
            'paid_at' => $paymentStatus === 'paid' ? now() : null,
        ]);

        // Create pickup record
        $queueNumber = Pickup::whereDate('created_at', today())->count() + 100;
        Pickup::create([
            'order_id' => $order->id,
            'queue_number' => $queueNumber,
            'qr_code' => Pickup::generateQrCode($order->id),
            'status' => 'waiting',
        ]);

        return $order->load(['items', 'payment', 'pickup']);
    }

    public function reset(): self
    {
        $this->orderData = [];
        $this->cartItems = collect();
        $this->paymentData = [];
        $this->voucherCode = null;
        $this->discount = 0;
        return $this;
    }
}

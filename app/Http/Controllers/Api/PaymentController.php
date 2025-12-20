<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Pickup;
use App\Models\CartItem;
use App\Models\LoyaltyPoint;
use App\Models\UserRedeemedReward;
use App\Models\VendorNotification;
use App\Models\VendorSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    /**
     * Get checkout summary
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkout(Request $request)
    {
        $user = $request->user();

        // Get cart items grouped by vendor
        $cartItems = CartItem::with(['menuItem.vendor', 'menuItem.category'])
            ->where('user_id', $user->user_id)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty'
            ], 400);
        }

        // Group items by vendor
        $vendorGroups = $cartItems->groupBy(function($item) {
            return $item->menuItem->vendor_id;
        });

        // Calculate totals
        $subtotal = 0;
        $itemCount = 0;
        foreach ($cartItems as $item) {
            if ($item->menuItem) {
                $subtotal += $item->menuItem->price * $item->quantity;
                $itemCount += $item->quantity;
            }
        }

        $serviceFee = 2.00;
        $discount = 0.00;
        $appliedVoucher = null;

        // Check if voucher is applied
        if ($request->has('voucher_code') && $request->voucher_code) {
            $voucherCode = strtoupper(trim($request->voucher_code));
            $redeemedReward = UserRedeemedReward::where('voucher_code', $voucherCode)
                ->where('user_id', $user->user_id)
                ->where('is_used', 0)
                ->with('reward')
                ->first();

            if ($redeemedReward && $redeemedReward->reward) {
                $reward = $redeemedReward->reward;
                $expiryDate = $redeemedReward->redeemed_at->addDays(30);

                if (now()->lte($expiryDate)) {
                    if ($reward->reward_type === 'voucher') {
                        if (!$reward->min_spend || $subtotal >= $reward->min_spend) {
                            $discount = $reward->reward_value;
                            $appliedVoucher = [
                                'voucher_code' => $voucherCode,
                                'reward_name' => $reward->reward_name,
                                'discount' => (float) $discount,
                            ];
                        }
                    } elseif ($reward->reward_type === 'percentage') {
                        $calculatedDiscount = ($subtotal * $reward->reward_value) / 100;
                        if ($reward->max_discount) {
                            $discount = min($calculatedDiscount, $reward->max_discount);
                        } else {
                            $discount = $calculatedDiscount;
                        }
                        $appliedVoucher = [
                            'voucher_code' => $voucherCode,
                            'reward_name' => $reward->reward_name,
                            'discount' => (float) $discount,
                        ];
                    }
                }
            }
        }

        $total = $subtotal + $serviceFee - $discount;

        // Get user's loyalty points
        $loyaltyPoints = LoyaltyPoint::where('user_id', $user->user_id)->first();
        $pointsToEarn = floor($total);

        // Format vendor groups for response
        $formattedVendorGroups = [];
        foreach ($vendorGroups as $vendorId => $items) {
            $vendor = $items->first()->menuItem->vendor;
            $vendorSettings = VendorSetting::where('vendor_id', $vendorId)->first();
            
            $vendorSubtotal = $items->sum(function($item) {
                return $item->menuItem->price * $item->quantity;
            });

            $formattedVendorGroups[] = [
                'vendor_id' => $vendorId,
                'vendor_name' => $vendorSettings->store_name ?? $vendor->name,
                'accepting_orders' => $vendorSettings ? $vendorSettings->accepting_orders : true,
                'subtotal' => (float) $vendorSubtotal,
                'items' => $items->map(function($item) {
                    return [
                        'cart_id' => $item->cart_id,
                        'item_id' => $item->item_id,
                        'name' => $item->menuItem->name,
                        'price' => (float) $item->menuItem->price,
                        'quantity' => $item->quantity,
                        'special_request' => $item->special_request,
                        'item_total' => (float) ($item->menuItem->price * $item->quantity),
                        'image_url' => $item->menuItem->image_path ? asset($item->menuItem->image_path) : null,
                    ];
                })->values(),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'vendor_groups' => $formattedVendorGroups,
                'summary' => [
                    'item_count' => $itemCount,
                    'subtotal' => (float) $subtotal,
                    'service_fee' => (float) $serviceFee,
                    'discount' => (float) $discount,
                    'total' => (float) $total,
                ],
                'loyalty' => [
                    'current_points' => $loyaltyPoints ? $loyaltyPoints->points : 0,
                    'points_to_earn' => $pointsToEarn,
                ],
                'applied_voucher' => $appliedVoucher,
                'payment_methods' => ['cash', 'online', 'ewallet'],
            ]
        ]);
    }

    /**
     * Process payment and create orders
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:online,ewallet,cash',
            'pickup_instructions' => 'nullable|string|max:500',
            'voucher_code' => 'nullable|string',
            // Online payment fields (for future integration)
            'card_number' => 'nullable|digits:16',
            'card_name' => 'nullable|string|max:100',
            'expiry_date' => 'nullable|regex:/^(0[1-9]|1[0-2])\/\d{2}$/',
            'cvv' => 'nullable|digits:3',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $paymentMethod = $request->payment_method;

        try {
            DB::beginTransaction();

            // Get cart items grouped by vendor
            $cartItems = CartItem::with('menuItem')
                ->where('user_id', $user->user_id)
                ->get();

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your cart is empty'
                ], 400);
            }

            // Group items by vendor
            $vendorGroups = $cartItems->groupBy(function($item) {
                return $item->menuItem->vendor_id;
            });

            // Calculate cart totals
            $cartSubtotal = 0;
            foreach ($cartItems as $item) {
                $cartSubtotal += $item->menuItem->price * $item->quantity;
            }

            $serviceFee = 2.00;
            $discount = 0.00;
            $redeemedReward = null;

            // Check if voucher is applied
            if ($request->voucher_code) {
                $voucherCode = strtoupper(trim($request->voucher_code));
                $redeemedReward = UserRedeemedReward::where('voucher_code', $voucherCode)
                    ->where('user_id', $user->user_id)
                    ->where('is_used', 0)
                    ->with('reward')
                    ->first();

                if ($redeemedReward && $redeemedReward->reward) {
                    $reward = $redeemedReward->reward;

                    if ($reward->reward_type === 'voucher') {
                        if (!$reward->min_spend || $cartSubtotal >= $reward->min_spend) {
                            $discount = $reward->reward_value;
                        }
                    } elseif ($reward->reward_type === 'percentage') {
                        $calculatedDiscount = ($cartSubtotal * $reward->reward_value) / 100;
                        if ($reward->max_discount) {
                            $discount = min($calculatedDiscount, $reward->max_discount);
                        } else {
                            $discount = $calculatedDiscount;
                        }
                    }
                }
            }

            $cartTotal = $cartSubtotal + $serviceFee - $discount;
            $allOrders = [];

            foreach ($vendorGroups as $vendorId => $items) {
                // Calculate order subtotal for this vendor
                $vendorSubtotal = 0;
                foreach ($items as $item) {
                    $vendorSubtotal += $item->menuItem->price * $item->quantity;
                }

                // Calculate proportional fees and discount for this vendor
                $proportion = $vendorSubtotal / $cartSubtotal;
                $vendorServiceFee = $serviceFee * $proportion;
                $vendorDiscount = $discount * $proportion;
                $orderTotal = $vendorSubtotal + $vendorServiceFee - $vendorDiscount;

                // Create order
                $order = Order::create([
                    'user_id' => $user->user_id,
                    'vendor_id' => $vendorId,
                    'total_price' => $orderTotal,
                    'status' => 'pending',
                ]);

                // Create vendor notification
                $vendorSettings = VendorSetting::where('vendor_id', $vendorId)->first();
                if (!$vendorSettings || $vendorSettings->notify_new_orders) {
                    VendorNotification::create([
                        'vendor_id' => $vendorId,
                        'type' => 'new_order',
                        'title' => 'New Order #' . $order->order_id,
                        'message' => 'You have a new order from ' . $user->name . ' totaling RM ' . number_format($orderTotal, 2),
                        'order_id' => $order->order_id,
                    ]);
                }

                // Create order items
                foreach ($items as $cartItem) {
                    OrderItem::create([
                        'order_id' => $order->order_id,
                        'item_id' => $cartItem->item_id,
                        'quantity' => $cartItem->quantity,
                        'price' => $cartItem->menuItem->price,
                        'special_request' => $cartItem->special_request,
                    ]);
                }

                // Process payment
                $paymentStatus = 'pending';
                $transactionRef = null;
                $paidAt = null;

                if ($paymentMethod === 'online') {
                    $transactionRef = 'TXN' . date('Ymd') . strtoupper(substr(md5(uniqid()), 0, 8));
                    // In production, integrate with actual payment gateway
                    $paymentStatus = 'paid';
                    $paidAt = now();
                    $order->update(['status' => 'accepted']);
                } elseif ($paymentMethod === 'ewallet') {
                    $transactionRef = 'TNG' . date('Ymd') . strtoupper(substr(md5(uniqid()), 0, 8));
                    $paymentStatus = 'paid';
                    $paidAt = now();
                    $order->update(['status' => 'accepted']);
                }
                // Cash on pickup - payment stays pending

                // Create payment record
                Payment::create([
                    'order_id' => $order->order_id,
                    'amount' => $orderTotal,
                    'method' => $paymentMethod,
                    'status' => $paymentStatus,
                    'transaction_ref' => $transactionRef,
                    'paid_at' => $paidAt,
                ]);

                // Create pickup record with queue number
                $queueNumber = $this->generateQueueNumber($vendorId);
                $qrCode = 'QR-ORD' . str_pad($order->order_id, 6, '0', STR_PAD_LEFT) . '-' . date('Y');

                Pickup::create([
                    'order_id' => $order->order_id,
                    'queue_number' => $queueNumber,
                    'qr_code' => $qrCode,
                    'status' => 'waiting',
                    'pickup_instructions' => $request->pickup_instructions,
                ]);

                $allOrders[] = $order->load(['orderItems.menuItem', 'payment', 'pickup', 'vendor']);
            }

            // Award loyalty points
            $pointsEarned = floor($cartTotal);

            if ($pointsEarned > 0) {
                $loyaltyPoints = LoyaltyPoint::firstOrCreate(
                    ['user_id' => $user->user_id],
                    ['points' => 0]
                );
                $loyaltyPoints->increment('points', $pointsEarned);
            }

            // Mark voucher as used
            if ($redeemedReward && $discount > 0) {
                $redeemedReward->update([
                    'is_used' => 1,
                    'used_at' => now(),
                ]);
            }

            // Clear cart
            CartItem::where('user_id', $user->user_id)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully!',
                'data' => [
                    'orders' => collect($allOrders)->map(function($order) {
                        return $this->transformOrder($order);
                    }),
                    'points_earned' => $pointsEarned,
                    'total_paid' => (float) $cartTotal,
                    'payment_method' => $paymentMethod,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get order confirmation details
     * 
     * @param Request $request
     * @param int $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirmation(Request $request, $orderId)
    {
        $user = $request->user();

        $order = Order::with(['orderItems.menuItem', 'payment', 'pickup', 'vendor'])
            ->where('order_id', $orderId)
            ->where('user_id', $user->user_id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->transformOrder($order)
        ]);
    }

    /**
     * Generate unique queue number for vendor
     */
    private function generateQueueNumber($vendorId)
    {
        $today = date('Y-m-d');
        $lastPickup = Pickup::whereHas('order', function($query) use ($vendorId, $today) {
            $query->where('vendor_id', $vendorId)
                  ->whereDate('created_at', $today);
        })->orderBy('queue_number', 'desc')->first();

        return $lastPickup ? $lastPickup->queue_number + 1 : 100;
    }

    /**
     * Transform order data for response
     */
    private function transformOrder($order)
    {
        $vendorSettings = VendorSetting::where('vendor_id', $order->vendor_id)->first();

        return [
            'order_id' => $order->order_id,
            'total_price' => (float) $order->total_price,
            'status' => $order->status,
            'created_at' => $order->created_at,
            'vendor' => [
                'vendor_id' => $order->vendor_id,
                'name' => $order->vendor->name,
                'store_name' => $vendorSettings->store_name ?? $order->vendor->name,
            ],
            'items' => $order->orderItems->map(function($item) {
                return [
                    'name' => $item->menuItem ? $item->menuItem->name : 'Unknown',
                    'quantity' => $item->quantity,
                    'price' => (float) $item->price,
                    'special_request' => $item->special_request,
                    'subtotal' => (float) ($item->price * $item->quantity),
                ];
            }),
            'payment' => $order->payment ? [
                'amount' => (float) $order->payment->amount,
                'method' => $order->payment->method,
                'status' => $order->payment->status,
                'transaction_ref' => $order->payment->transaction_ref,
                'paid_at' => $order->payment->paid_at,
            ] : null,
            'pickup' => $order->pickup ? [
                'queue_number' => $order->pickup->queue_number,
                'qr_code' => $order->pickup->qr_code,
                'status' => $order->pickup->status,
            ] : null,
        ];
    }
}

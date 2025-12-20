<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Pickup;
use App\Models\CartItem;
use App\Models\MenuItem;
use App\Models\LoyaltyPoint;
use App\Models\UserRedeemedReward;
use App\Models\VendorNotification;
use App\Models\VendorSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Get user's orders
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Order::with(['orderItems.menuItem', 'payment', 'pickup', 'vendor'])
            ->where('user_id', $user->user_id);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('date_range')) {
            switch ($request->date_range) {
                case '7days':
                    $query->where('created_at', '>=', now()->subDays(7));
                    break;
                case '3months':
                    $query->where('created_at', '>=', now()->subMonths(3));
                    break;
                case '1year':
                    $query->where('created_at', '>=', now()->subYear());
                    break;
                default:
                    $query->where('created_at', '>=', now()->subDays(30));
            }
        }

        $perPage = $request->input('per_page', 10);
        $orders = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'orders' => $orders->getCollection()->map(function($order) {
                    return $this->transformOrder($order);
                }),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                ]
            ]
        ]);
    }

    /**
     * Get single order details
     * 
     * @param Request $request
     * @param int $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $orderId)
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
            'data' => $this->transformOrder($order, true)
        ]);
    }

    /**
     * Get active order
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function active(Request $request)
    {
        $user = $request->user();

        $activeOrder = Order::with(['orderItems.menuItem', 'payment', 'pickup', 'vendor'])
            ->where('user_id', $user->user_id)
            ->whereIn('status', ['pending', 'accepted', 'preparing', 'ready'])
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$activeOrder) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'No active orders'
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $this->transformOrder($activeOrder, true)
        ]);
    }

    /**
     * Create a new order (checkout)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:online,ewallet,cash',
            'pickup_instructions' => 'nullable|string|max:500',
            'voucher_code' => 'nullable|string',
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

            // Get cart items
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

                // Calculate proportional fees and discount
                $proportion = $vendorSubtotal / $cartSubtotal;
                $vendorServiceFee = $serviceFee * $proportion;
                $vendorDiscount = $discount * $proportion;
                $vendorTotal = $vendorSubtotal + $vendorServiceFee - $vendorDiscount;

                // Create order
                $order = Order::create([
                    'user_id' => $user->user_id,
                    'vendor_id' => $vendorId,
                    'total_price' => $vendorTotal,
                    'status' => 'pending',
                ]);

                // Create order items
                foreach ($items as $cartItem) {
                    OrderItem::create([
                        'order_id' => $order->order_id,
                        'item_id' => $cartItem->item_id,
                        'quantity' => $cartItem->quantity,
                        'price_at_time' => $cartItem->menuItem->price,
                        'special_request' => $cartItem->special_request,
                    ]);
                }

                // Create payment record
                $paymentStatus = ($paymentMethod === 'cash') ? 'pending' : 'completed';
                Payment::create([
                    'order_id' => $order->order_id,
                    'amount' => $vendorTotal,
                    'payment_method' => $paymentMethod,
                    'status' => $paymentStatus,
                ]);

                // Create pickup record
                $queueNumber = Pickup::where('vendor_id', $vendorId)
                    ->whereDate('created_at', today())
                    ->count() + 1;

                Pickup::create([
                    'order_id' => $order->order_id,
                    'vendor_id' => $vendorId,
                    'queue_number' => $queueNumber,
                    'status' => 'waiting',
                    'pickup_instructions' => $request->pickup_instructions,
                ]);

                // Create vendor notification
                VendorNotification::create([
                    'vendor_id' => $vendorId,
                    'order_id' => $order->order_id,
                    'type' => 'new_order',
                    'title' => 'New Order Received',
                    'message' => 'You have received a new order #' . $order->order_id,
                ]);

                $allOrders[] = $order;
            }

            // Mark voucher as used
            if ($redeemedReward && $discount > 0) {
                $redeemedReward->is_used = 1;
                $redeemedReward->used_at = now();
                $redeemedReward->save();
            }

            // Award loyalty points (1 point per RM spent)
            $pointsToAward = floor($cartTotal);
            if ($pointsToAward > 0) {
                $loyaltyPoints = LoyaltyPoint::firstOrCreate(
                    ['user_id' => $user->user_id],
                    ['points' => 0]
                );
                $loyaltyPoints->increment('points', $pointsToAward);
            }

            // Clear cart
            CartItem::where('user_id', $user->user_id)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'data' => [
                    'orders' => collect($allOrders)->map(function($order) {
                        return $this->transformOrder($order->load(['orderItems.menuItem', 'payment', 'pickup', 'vendor']), true);
                    }),
                    'points_earned' => $pointsToAward,
                    'total_paid' => (float) $cartTotal,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Reorder items from previous order
     * 
     * @param Request $request
     * @param int $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function reorder(Request $request, $orderId)
    {
        $user = $request->user();

        $order = Order::with(['orderItems.menuItem'])
            ->where('order_id', $orderId)
            ->where('user_id', $user->user_id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        // Clear current cart
        CartItem::where('user_id', $user->user_id)->delete();

        $addedItems = [];
        $unavailableItems = [];

        // Add items from previous order to cart
        foreach ($order->orderItems as $item) {
            if ($item->menuItem && $item->menuItem->is_available) {
                CartItem::create([
                    'user_id' => $user->user_id,
                    'item_id' => $item->item_id,
                    'quantity' => $item->quantity,
                ]);
                $addedItems[] = $item->menuItem->name;
            } else {
                $unavailableItems[] = $item->menuItem ? $item->menuItem->name : 'Unknown item';
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Items added to cart',
            'data' => [
                'added_items' => $addedItems,
                'unavailable_items' => $unavailableItems,
                'cart_count' => CartItem::where('user_id', $user->user_id)->sum('quantity'),
            ]
        ]);
    }

    /**
     * Cancel an order
     * 
     * @param Request $request
     * @param int $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Request $request, $orderId)
    {
        $user = $request->user();

        $order = Order::where('order_id', $orderId)
            ->where('user_id', $user->user_id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        // Can only cancel pending orders
        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending orders can be cancelled'
            ], 400);
        }

        $order->status = 'cancelled';
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled successfully'
        ]);
    }

    /**
     * Transform order data
     * 
     * @param Order $order
     * @param bool $detailed
     * @return array
     */
    private function transformOrder($order, $detailed = false)
    {
        $data = [
            'order_id' => $order->order_id,
            'total_price' => (float) $order->total_price,
            'status' => $order->status,
            'created_at' => $order->created_at,
            'vendor' => $order->vendor ? [
                'vendor_id' => $order->vendor->user_id,
                'name' => $order->vendor->name,
            ] : null,
            'items_count' => $order->orderItems->sum('quantity'),
        ];

        if ($detailed) {
            $data['items'] = $order->orderItems->map(function($item) {
                return [
                    'order_item_id' => $item->order_item_id,
                    'quantity' => $item->quantity,
                    'price_at_time' => (float) $item->price_at_time,
                    'special_request' => $item->special_request,
                    'subtotal' => (float) ($item->price_at_time * $item->quantity),
                    'menu_item' => $item->menuItem ? [
                        'item_id' => $item->menuItem->item_id,
                        'name' => $item->menuItem->name,
                        'image_url' => $item->menuItem->image_path 
                            ? asset($item->menuItem->image_path) 
                            : null,
                    ] : null,
                ];
            });

            $data['payment'] = $order->payment ? [
                'payment_id' => $order->payment->payment_id,
                'amount' => (float) $order->payment->amount,
                'payment_method' => $order->payment->payment_method,
                'status' => $order->payment->status,
                'paid_at' => $order->payment->paid_at,
            ] : null;

            $data['pickup'] = $order->pickup ? [
                'pickup_id' => $order->pickup->pickup_id,
                'queue_number' => $order->pickup->queue_number,
                'status' => $order->pickup->status,
                'pickup_instructions' => $order->pickup->pickup_instructions,
                'picked_up_at' => $order->pickup->picked_up_at,
            ] : null;
        }

        return $data;
    }
}

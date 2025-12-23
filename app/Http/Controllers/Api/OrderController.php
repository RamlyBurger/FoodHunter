<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Pickup;
use App\Models\Voucher;
use App\Models\UserVoucher;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with(['vendor:id,store_name', 'items', 'payment', 'pickup'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return $this->successResponse([
            'orders' => $orders->getCollection()->map(fn($order) => $this->formatOrder($order)),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return $this->forbiddenResponse('Unauthorized');
        }

        $order->load(['vendor:id,store_name,phone', 'items.menuItem', 'payment', 'pickup']);

        return $this->successResponse($this->formatOrder($order, true));
    }

    public function store(CreateOrderRequest $request): JsonResponse
    {
        $user = $request->user();
        
        $cartItems = CartItem::where('user_id', $user->id)
            ->with('menuItem.vendor')
            ->get();

        if ($cartItems->isEmpty()) {
            return $this->errorResponse('Cart is empty', 400, 'EMPTY_CART');
        }

        // Group by vendor
        $vendorGroups = $cartItems->groupBy(fn($item) => $item->menuItem->vendor_id);

        try {
            DB::beginTransaction();

            $orders = [];
            $subtotal = $cartItems->sum(fn($item) => $item->menuItem->price * $item->quantity);
            $serviceFee = 2.00;
            $discount = 0.00;

            // Apply voucher if provided
            if ($request->voucher_code) {
                $voucher = Voucher::where('code', strtoupper($request->voucher_code))
                    ->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                    })
                    ->first();

                if ($voucher && $subtotal >= ($voucher->min_order ?? 0)) {
                    // Check if user has redeemed this voucher
                    $userVoucher = UserVoucher::where('user_id', $user->id)
                        ->where('voucher_id', $voucher->id)
                        ->first();

                    if ($userVoucher && ($voucher->max_uses_per_user === null || $userVoucher->usage_count < $voucher->max_uses_per_user)) {
                        if ($voucher->type === 'percentage') {
                            $discount = $subtotal * ($voucher->value / 100);
                        } else {
                            $discount = $voucher->value;
                        }
                        if ($voucher->max_discount) {
                            $discount = min($discount, $voucher->max_discount);
                        }
                        $discount = min($discount, $subtotal);
                        
                        // Mark voucher as used
                        $userVoucher->increment('usage_count');
                        $userVoucher->update(['used_at' => now()]);
                        $voucher->increment('usage_count');
                    }
                }
            }

            $total = $subtotal + $serviceFee - $discount;

            foreach ($vendorGroups as $vendorId => $items) {
                $vendorSubtotal = $items->sum(fn($item) => $item->menuItem->price * $item->quantity);
                $proportion = $subtotal > 0 ? $vendorSubtotal / $subtotal : 0;
                $vendorTotal = $vendorSubtotal + ($serviceFee * $proportion) - ($discount * $proportion);

                $order = Order::create([
                    'user_id' => $user->id,
                    'vendor_id' => $vendorId,
                    'order_number' => Order::generateOrderNumber(),
                    'subtotal' => $vendorSubtotal,
                    'service_fee' => $serviceFee * $proportion,
                    'discount' => $discount * $proportion,
                    'total' => $vendorTotal,
                    'status' => 'pending',
                    'notes' => $request->notes,
                ]);

                // Create order items
                foreach ($items as $cartItem) {
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

                // Create payment
                $paymentStatus = $request->payment_method === 'cash' ? 'pending' : 'paid';
                Payment::create([
                    'order_id' => $order->id,
                    'amount' => $vendorTotal,
                    'method' => $request->payment_method,
                    'status' => $paymentStatus,
                    'transaction_id' => $paymentStatus === 'paid' ? 'TXN-' . strtoupper(uniqid()) : null,
                    'paid_at' => $paymentStatus === 'paid' ? now() : null,
                ]);

                // Create pickup
                $queueNumber = Pickup::whereDate('created_at', today())->count() + 100;
                Pickup::create([
                    'order_id' => $order->id,
                    'queue_number' => $queueNumber,
                    'qr_code' => Pickup::generateQrCode($order->id),
                    'status' => 'waiting',
                ]);

                $orders[] = $order->load(['items', 'payment', 'pickup', 'vendor:id,store_name']);
            }

            // Clear cart
            CartItem::where('user_id', $user->id)->delete();

            DB::commit();

            return $this->createdResponse([
                'orders' => collect($orders)->map(fn($order) => $this->formatOrder($order)),
                'total_paid' => $total,
            ], 'Order placed successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverErrorResponse('Failed to create order');
        }
    }

    public function cancel(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return $this->forbiddenResponse('Unauthorized');
        }

        if (!$order->canBeCancelled()) {
            return $this->errorResponse('Order cannot be cancelled', 400, 'CANNOT_CANCEL');
        }

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancel_reason' => $request->reason,
        ]);

        return $this->successResponse(null, 'Order cancelled');
    }

    public function active(Request $request): JsonResponse
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready'])
            ->with(['vendor:id,store_name', 'pickup'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse($orders->map(fn($order) => $this->formatOrder($order)));
    }

    /**
     * Web Service: Expose - Get Order Status
     * Student 5 (Notifications) consumes this to get order details
     */
    public function status(Request $request, Order $order): JsonResponse
    {
        // Security: IDOR Protection - verify ownership
        if ($order->user_id !== $request->user()->id) {
            return $this->forbiddenResponse('Unauthorized');
        }

        return $this->successResponse([
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'total' => (float) $order->total,
            'pickup' => $order->pickup ? [
                'queue_number' => $order->pickup->queue_number,
                'status' => $order->pickup->status,
            ] : null,
            'updated_at' => $order->updated_at,
        ]);
    }

    private function formatOrder(Order $order, bool $detailed = false): array
    {
        $data = [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'total' => (float) $order->total,
            'status' => $order->status,
            'created_at' => $order->created_at,
            'vendor' => $order->vendor ? [
                'id' => $order->vendor->id,
                'store_name' => $order->vendor->store_name,
            ] : null,
            'pickup' => $order->pickup ? [
                'queue_number' => $order->pickup->queue_number,
                'status' => $order->pickup->status,
            ] : null,
        ];

        if ($detailed) {
            $data['subtotal'] = (float) $order->subtotal;
            $data['service_fee'] = (float) $order->service_fee;
            $data['discount'] = (float) $order->discount;
            $data['notes'] = $order->notes;
            $data['items'] = $order->items->map(fn($item) => [
                'id' => $item->id,
                'name' => $item->item_name,
                'unit_price' => (float) $item->unit_price,
                'quantity' => $item->quantity,
                'subtotal' => (float) $item->subtotal,
                'special_instructions' => $item->special_instructions,
            ]);
            $data['payment'] = $order->payment ? [
                'method' => $order->payment->method,
                'status' => $order->payment->status,
                'paid_at' => $order->payment->paid_at,
            ] : null;
            $data['pickup']['qr_code'] = $order->pickup?->qr_code;
        }

        return $data;
    }
}

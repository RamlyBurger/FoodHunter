<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Pickup;
use App\Models\StudentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Get vendor's orders with filtering and pagination
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $vendor = $request->user();

        $statusFilter = $request->input('status', 'all');
        $perPage = $request->input('per_page', 15);

        $query = Order::with(['user', 'orderItems.menuItem', 'payment', 'pickup'])
            ->where('vendor_id', $vendor->user_id)
            ->orderBy('created_at', 'desc');

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $orders = $query->paginate($perPage);

        // Get statistics
        $stats = $this->getOrderStatistics($vendor->user_id);

        return response()->json([
            'success' => true,
            'data' => [
                'orders' => $orders->getCollection()->map(function($order) {
                    return $this->transformOrder($order);
                }),
                'statistics' => $stats,
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
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $vendor = $request->user();

        $order = Order::with(['user', 'orderItems.menuItem', 'payment', 'pickup'])
            ->where('order_id', $id)
            ->where('vendor_id', $vendor->user_id)
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
     * Update order status
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,accepted,preparing,ready,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $vendor = $request->user();

        $order = Order::where('order_id', $id)
            ->where('vendor_id', $vendor->user_id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $oldStatus = $order->status;
        $order->status = $request->status;
        $order->save();

        // If status changed to ready, generate queue number
        if ($request->status === 'ready' && $oldStatus !== 'ready') {
            $this->generateQueueNumber($order);
        }

        // Create student notification
        $this->createStudentNotification($order, $request->status, $oldStatus);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully',
            'data' => [
                'order_id' => $order->order_id,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
            ]
        ]);
    }

    /**
     * Accept an order
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function accept(Request $request, $id)
    {
        $vendor = $request->user();

        $order = Order::where('order_id', $id)
            ->where('vendor_id', $vendor->user_id)
            ->where('status', 'pending')
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or cannot be accepted'
            ], 404);
        }

        $oldStatus = $order->status;
        $order->status = 'accepted';
        $order->save();

        $this->createStudentNotification($order, 'accepted', $oldStatus);

        return response()->json([
            'success' => true,
            'message' => 'Order accepted successfully',
            'data' => [
                'order_id' => $order->order_id,
                'status' => 'accepted',
            ]
        ]);
    }

    /**
     * Reject an order
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function reject(Request $request, $id)
    {
        $vendor = $request->user();

        $order = Order::where('order_id', $id)
            ->where('vendor_id', $vendor->user_id)
            ->where('status', 'pending')
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or cannot be rejected'
            ], 404);
        }

        $oldStatus = $order->status;
        $order->status = 'cancelled';
        $order->save();

        $this->createStudentNotification($order, 'cancelled', $oldStatus);

        return response()->json([
            'success' => true,
            'message' => 'Order rejected',
            'data' => [
                'order_id' => $order->order_id,
                'status' => 'cancelled',
            ]
        ]);
    }

    /**
     * Get order statistics
     */
    private function getOrderStatistics($vendorId)
    {
        $today = now()->startOfDay();

        return [
            'pending' => Order::where('vendor_id', $vendorId)->where('status', 'pending')->count(),
            'accepted' => Order::where('vendor_id', $vendorId)->where('status', 'accepted')->count(),
            'preparing' => Order::where('vendor_id', $vendorId)->where('status', 'preparing')->count(),
            'ready' => Order::where('vendor_id', $vendorId)->where('status', 'ready')->count(),
            'completed' => Order::where('vendor_id', $vendorId)->where('status', 'completed')->count(),
            'today_revenue' => (float) Order::where('vendor_id', $vendorId)
                ->where('status', 'completed')
                ->whereDate('created_at', $today)
                ->sum('total_price'),
        ];
    }

    /**
     * Generate queue number for pickup
     */
    private function generateQueueNumber($order)
    {
        $today = now()->startOfDay();
        $lastPickup = Pickup::whereDate('created_at', $today)
            ->orderBy('queue_number', 'desc')
            ->first();

        $queueNumber = $lastPickup ? $lastPickup->queue_number + 1 : 100;

        Pickup::updateOrCreate(
            ['order_id' => $order->order_id],
            [
                'queue_number' => $queueNumber,
                'status' => 'waiting',
            ]
        );
    }

    /**
     * Create student notification for order status changes
     */
    private function createStudentNotification($order, $newStatus, $oldStatus)
    {
        $statusMessages = [
            'accepted' => [
                'title' => 'Order Accepted',
                'message' => "Your order #{$order->order_id} has been accepted by the vendor and will be prepared soon.",
                'type' => 'order_accepted'
            ],
            'preparing' => [
                'title' => 'Order Being Prepared',
                'message' => "Your order #{$order->order_id} is now being prepared. We'll notify you when it's ready!",
                'type' => 'order_preparing'
            ],
            'ready' => [
                'title' => 'Order Ready for Pickup!',
                'message' => "Your order #{$order->order_id} is ready! Please collect your order.",
                'type' => 'order_ready'
            ],
            'completed' => [
                'title' => 'Order Completed',
                'message' => "Thank you! Your order #{$order->order_id} has been completed. We hope you enjoyed your meal!",
                'type' => 'order_completed'
            ],
            'cancelled' => [
                'title' => 'Order Cancelled',
                'message' => "We're sorry, but your order #{$order->order_id} has been cancelled. Please contact the vendor for more information.",
                'type' => 'order_cancelled'
            ],
        ];

        if (isset($statusMessages[$newStatus]) && $newStatus !== $oldStatus) {
            StudentNotification::create([
                'user_id' => $order->user_id,
                'order_id' => $order->order_id,
                'type' => $statusMessages[$newStatus]['type'],
                'title' => $statusMessages[$newStatus]['title'],
                'message' => $statusMessages[$newStatus]['message'],
            ]);
        }
    }

    /**
     * Transform order data
     */
    private function transformOrder($order, $detailed = false)
    {
        $data = [
            'order_id' => $order->order_id,
            'customer' => [
                'user_id' => $order->user->user_id,
                'name' => $order->user->name,
                'email' => $order->user->email,
            ],
            'total_price' => (float) $order->total_price,
            'status' => $order->status,
            'items_count' => $order->orderItems->sum('quantity'),
            'created_at' => $order->created_at,
        ];

        if ($detailed) {
            $data['items'] = $order->orderItems->map(function($item) {
                return [
                    'order_item_id' => $item->order_item_id,
                    'item_id' => $item->item_id,
                    'name' => $item->menuItem ? $item->menuItem->name : 'Unknown',
                    'quantity' => $item->quantity,
                    'price' => (float) $item->price,
                    'special_request' => $item->special_request,
                    'subtotal' => (float) ($item->price * $item->quantity),
                ];
            });

            $data['payment'] = $order->payment ? [
                'payment_id' => $order->payment->payment_id,
                'amount' => (float) $order->payment->amount,
                'method' => $order->payment->method,
                'status' => $order->payment->status,
                'paid_at' => $order->payment->paid_at,
            ] : null;

            $data['pickup'] = $order->pickup ? [
                'pickup_id' => $order->pickup->pickup_id,
                'queue_number' => $order->pickup->queue_number,
                'status' => $order->pickup->status,
            ] : null;
        }

        return $data;
    }
}

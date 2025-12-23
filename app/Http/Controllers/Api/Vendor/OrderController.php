<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Vendor Order Controller - Student 3
 * 
 * Uses OrderService for status updates with database locking
 * to prevent race conditions.
 */
class OrderController extends Controller
{
    use ApiResponse;

    private OrderService $orderService;

    public function __construct()
    {
        $this->orderService = new OrderService();
    }

    public function index(Request $request): JsonResponse
    {
        $vendor = $request->user()->vendor;
        $status = $request->get('status', 'all');

        $query = Order::where('vendor_id', $vendor->id)
            ->with(['user:id,name,phone', 'items', 'payment', 'pickup'])
            ->orderBy('created_at', 'desc');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $orders = $query->paginate(15);

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
        $vendor = $request->user()->vendor;

        if ($order->vendor_id !== $vendor->id) {
            return $this->notFoundResponse('Order not found');
        }

        $order->load(['user:id,name,phone,email', 'items', 'payment', 'pickup']);

        return $this->successResponse($this->formatOrder($order, true));
    }

    /**
     * Update order status using OrderService with database locking
     * Security: Race Condition Protection [OWASP 89]
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $vendor = $request->user()->vendor;

        // Security: IDOR Protection - verify vendor ownership
        if ($order->vendor_id !== $vendor->id) {
            return $this->notFoundResponse('Order not found');
        }

        $request->validate([
            'status' => ['required', 'in:confirmed,preparing,ready,completed,cancelled'],
        ]);

        // Use OrderService with database locking to prevent race conditions
        $result = $this->orderService->updateStatusWithLocking(
            $order->id,
            $request->status,
            $request->reason ?? 'Cancelled by vendor'
        );

        if (!$result['success']) {
            return $this->errorResponse($result['message'], 400, 'STATUS_UPDATE_FAILED');
        }

        return $this->successResponse(['status' => $result['new_status']], 'Order status updated');
    }

    public function pending(Request $request): JsonResponse
    {
        $vendor = $request->user()->vendor;

        $orders = Order::where('vendor_id', $vendor->id)
            ->whereIn('status', ['pending', 'confirmed', 'preparing'])
            ->with(['user:id,name', 'items', 'pickup'])
            ->orderBy('created_at', 'asc')
            ->get();

        return $this->successResponse($orders->map(fn($order) => $this->formatOrder($order)));
    }

    private function formatOrder(Order $order, bool $detailed = false): array
    {
        $data = [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'customer' => [
                'name' => $order->user->name,
                'phone' => $order->user->phone,
            ],
            'total' => (float) $order->total,
            'status' => $order->status,
            'items_count' => $order->items->sum('quantity'),
            'created_at' => $order->created_at,
            'pickup' => $order->pickup ? [
                'queue_number' => $order->pickup->queue_number,
                'status' => $order->pickup->status,
            ] : null,
        ];

        if ($detailed) {
            $data['customer']['email'] = $order->user->email;
            $data['subtotal'] = (float) $order->subtotal;
            $data['service_fee'] = (float) $order->service_fee;
            $data['discount'] = (float) $order->discount;
            $data['notes'] = $order->notes;
            $data['items'] = $order->items->map(fn($item) => [
                'name' => $item->item_name,
                'unit_price' => (float) $item->unit_price,
                'quantity' => $item->quantity,
                'subtotal' => (float) $item->subtotal,
                'special_instructions' => $item->special_instructions,
            ]);
            $data['payment'] = $order->payment ? [
                'method' => $order->payment->method,
                'status' => $order->payment->status,
            ] : null;
        }

        return $data;
    }
}

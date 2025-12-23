<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return $this->notFoundResponse('Vendor profile not found');
        }

        $today = now()->startOfDay();

        $stats = [
            'today_orders' => Order::where('vendor_id', $vendor->id)
                ->whereDate('created_at', $today)
                ->count(),
            'today_revenue' => (float) Order::where('vendor_id', $vendor->id)
                ->whereDate('created_at', $today)
                ->where('status', 'completed')
                ->sum('total'),
            'pending_orders' => Order::where('vendor_id', $vendor->id)
                ->where('status', 'pending')
                ->count(),
            'total_orders' => $vendor->total_orders,
        ];

        $recentOrders = Order::where('vendor_id', $vendor->id)
            ->with(['user:id,name', 'items'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer' => $order->user->name,
                'total' => (float) $order->total,
                'status' => $order->status,
                'items_count' => $order->items->sum('quantity'),
                'created_at' => $order->created_at,
            ]);

        return $this->successResponse([
            'vendor' => [
                'id' => $vendor->id,
                'store_name' => $vendor->store_name,
                'is_open' => $vendor->is_open,
            ],
            'stats' => $stats,
            'recent_orders' => $recentOrders,
        ]);
    }

    public function toggleOpen(Request $request): JsonResponse
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return $this->notFoundResponse('Vendor profile not found');
        }

        $vendor->update(['is_open' => !$vendor->is_open]);

        return $this->successResponse(
            ['is_open' => $vendor->is_open],
            $vendor->is_open ? 'Store is now open' : 'Store is now closed'
        );
    }
}

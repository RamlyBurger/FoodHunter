<?php
/**
 * =============================================================================
 * Vendor Dashboard API Controller - Lee Kin Hang (Vendor Management Module)
 * =============================================================================
 * 
 * @author     Lee Kin Hang
 * @module     Vendor Management Module
 * @pattern    Factory Pattern (VoucherFactory)
 * 
 * Provides vendor dashboard statistics and analytics via API.
 * Includes sales data, order counts, and revenue metrics.
 * =============================================================================
 */

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Helpers\ImageHelper;
use App\Models\Order;
use App\Models\MenuItem;
use App\Models\OrderItem;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    /**
     * Get top selling items
     * URL: /api/vendor/dashboard/top-items
     */
    public function topItems(Request $request): JsonResponse
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return $this->notFoundResponse('Vendor profile not found');
        }

        $limit = $request->get('limit', 10);

        $topItems = MenuItem::where('vendor_id', $vendor->id)
            ->orderBy('total_sold', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'total_sold' => $item->total_sold,
                'price' => (float) $item->price,
                'revenue' => (float) ($item->total_sold * $item->price),
                'image' => ImageHelper::menuItem($item->image),
            ]);

        return $this->successResponse($topItems);
    }

    /**
     * Get recent orders
     * URL: /api/vendor/dashboard/recent-orders
     */
    public function recentOrders(Request $request): JsonResponse
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return $this->notFoundResponse('Vendor profile not found');
        }

        $limit = $request->get('limit', 10);

        $orders = Order::where('vendor_id', $vendor->id)
            ->with(['user:id,name', 'items', 'pickup'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->user->name,
                'total' => (float) $order->total,
                'status' => $order->status,
                'items_count' => $order->items->sum('quantity'),
                'queue_number' => $order->pickup?->queue_number,
                'created_at' => $order->created_at,
            ]);

        return $this->successResponse($orders);
    }

    /**
     * Get revenue report
     * URL: /api/vendor/reports/revenue
     */
    public function revenueReport(Request $request): JsonResponse
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return $this->notFoundResponse('Vendor profile not found');
        }

        $period = $request->get('period', 'week'); // day, week, month, year
        $startDate = match($period) {
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfWeek(),
        };

        // Get revenue data
        $orders = Order::where('vendor_id', $vendor->id)
            ->where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Calculate totals
        $totalRevenue = $orders->sum('revenue');
        $totalOrders = $orders->sum('orders_count');
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        return $this->successResponse([
            'period' => $period,
            'start_date' => $startDate->toDateString(),
            'end_date' => now()->toDateString(),
            'summary' => [
                'total_revenue' => (float) $totalRevenue,
                'total_orders' => (int) $totalOrders,
                'average_order_value' => (float) $avgOrderValue,
            ],
            'daily_data' => $orders->map(fn($item) => [
                'date' => $item->date,
                'orders_count' => (int) $item->orders_count,
                'revenue' => (float) $item->revenue,
            ]),
        ]);
    }
}

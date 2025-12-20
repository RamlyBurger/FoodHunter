<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get vendor dashboard statistics
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $vendor = $request->user();
        $today = now()->startOfDay();
        $yesterday = now()->subDay()->startOfDay();

        // Today's statistics
        $todayOrders = Order::where('vendor_id', $vendor->user_id)
            ->whereDate('created_at', $today)
            ->count();

        $todayRevenue = Order::where('vendor_id', $vendor->user_id)
            ->where('status', 'completed')
            ->whereDate('created_at', $today)
            ->sum('total_price');

        // Yesterday's statistics for comparison
        $yesterdayOrders = Order::where('vendor_id', $vendor->user_id)
            ->whereDate('created_at', $yesterday)
            ->count();

        $yesterdayRevenue = Order::where('vendor_id', $vendor->user_id)
            ->where('status', 'completed')
            ->whereDate('created_at', $yesterday)
            ->sum('total_price');

        // Calculate growth percentages
        $orderGrowth = $yesterdayOrders > 0
            ? (($todayOrders - $yesterdayOrders) / $yesterdayOrders) * 100
            : 0;

        $revenueGrowth = $yesterdayRevenue > 0
            ? (($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100
            : 0;

        // Pending orders count
        $pendingOrders = Order::where('vendor_id', $vendor->user_id)
            ->where('status', 'pending')
            ->count();

        // Accepted orders count (ready to be prepared)
        $acceptedOrders = Order::where('vendor_id', $vendor->user_id)
            ->where('status', 'accepted')
            ->count();

        // Menu items statistics
        $totalMenuItems = MenuItem::where('vendor_id', $vendor->user_id)->count();
        $availableMenuItems = MenuItem::where('vendor_id', $vendor->user_id)
            ->where('is_available', 1)
            ->count();

        // This week's statistics
        $weekStart = now()->startOfWeek();
        $weekRevenue = Order::where('vendor_id', $vendor->user_id)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$weekStart, now()])
            ->sum('total_price');

        $weekOrders = Order::where('vendor_id', $vendor->user_id)
            ->whereBetween('created_at', [$weekStart, now()])
            ->count();

        // Last week's revenue for comparison
        $lastWeekStart = now()->subWeek()->startOfWeek();
        $lastWeekEnd = now()->subWeek()->endOfWeek();
        $lastWeekRevenue = Order::where('vendor_id', $vendor->user_id)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])
            ->sum('total_price');

        $weekGrowth = $lastWeekRevenue > 0
            ? (($weekRevenue - $lastWeekRevenue) / $lastWeekRevenue) * 100
            : 0;

        $avgOrderValue = $weekOrders > 0 ? $weekRevenue / $weekOrders : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'today' => [
                    'orders' => $todayOrders,
                    'revenue' => (float) $todayRevenue,
                    'order_growth' => round($orderGrowth, 1),
                    'revenue_growth' => round($revenueGrowth, 1),
                ],
                'orders' => [
                    'pending' => $pendingOrders,
                    'accepted' => $acceptedOrders,
                ],
                'menu_items' => [
                    'total' => $totalMenuItems,
                    'available' => $availableMenuItems,
                    'unavailable' => $totalMenuItems - $availableMenuItems,
                ],
                'week' => [
                    'revenue' => (float) $weekRevenue,
                    'orders' => $weekOrders,
                    'growth' => round($weekGrowth, 1),
                    'avg_order_value' => round($avgOrderValue, 2),
                ],
            ]
        ]);
    }

    /**
     * Get recent orders
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recentOrders(Request $request)
    {
        $vendor = $request->user();
        $limit = $request->input('limit', 10);

        $recentOrders = Order::with(['user', 'orderItems.menuItem'])
            ->where('vendor_id', $vendor->user_id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $recentOrders->map(function($order) {
                return [
                    'order_id' => $order->order_id,
                    'customer' => [
                        'user_id' => $order->user->user_id,
                        'name' => $order->user->name,
                    ],
                    'total_price' => (float) $order->total_price,
                    'status' => $order->status,
                    'items_count' => $order->orderItems->sum('quantity'),
                    'created_at' => $order->created_at,
                ];
            })
        ]);
    }

    /**
     * Get top selling items
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function topSellingItems(Request $request)
    {
        $vendor = $request->user();
        $limit = $request->input('limit', 5);
        $weekStart = now()->startOfWeek();

        $topSellingItems = OrderItem::select(
                'order_items.item_id',
                'menu_items.name',
                'menu_items.price',
                'menu_items.image_path',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue')
            )
            ->join('orders', 'order_items.order_id', '=', 'orders.order_id')
            ->join('menu_items', 'order_items.item_id', '=', 'menu_items.item_id')
            ->where('orders.vendor_id', $vendor->user_id)
            ->where('orders.status', 'completed')
            ->whereBetween('orders.created_at', [$weekStart, now()])
            ->groupBy('order_items.item_id', 'menu_items.name', 'menu_items.price', 'menu_items.image_path')
            ->orderBy('total_sold', 'desc')
            ->limit($limit)
            ->get();

        $maxSold = $topSellingItems->max('total_sold') ?: 1;

        return response()->json([
            'success' => true,
            'data' => $topSellingItems->map(function($item) use ($maxSold) {
                return [
                    'item_id' => $item->item_id,
                    'name' => $item->name,
                    'price' => (float) $item->price,
                    'image_url' => $item->image_path ? asset($item->image_path) : null,
                    'total_sold' => (int) $item->total_sold,
                    'total_revenue' => (float) $item->total_revenue,
                    'percentage' => round(($item->total_sold / $maxSold) * 100, 1),
                ];
            })
        ]);
    }
}

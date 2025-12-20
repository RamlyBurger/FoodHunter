<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VendorDashboardController extends Controller
{
    /**
     * Display vendor dashboard with statistics and recent data
     */
    public function index()
    {
        $vendor = Auth::user();
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
        
        // Accepted orders count (orders ready to be prepared)
        $pendingOrders = Order::where('vendor_id', $vendor->user_id)
            ->where('status', 'accepted')
            ->count();
        
        // Menu items statistics
        $totalMenuItems = MenuItem::where('vendor_id', $vendor->user_id)->count();
        $availableMenuItems = MenuItem::where('vendor_id', $vendor->user_id)
            ->where('is_available', 1)
            ->count();
        
        // Recent orders (last 10)
        $recentOrders = Order::with(['user', 'orderItems.menuItem'])
            ->where('vendor_id', $vendor->user_id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Recent menu items (last 4 for quick view)
        $recentMenuItems = MenuItem::with('category')
            ->where('vendor_id', $vendor->user_id)
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get();
        
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
        
        // Top selling items (this week)
        $topSellingItems = OrderItem::select(
                'order_items.item_id',
                'menu_items.name',
                'menu_items.image_path',
                DB::raw('SUM(order_items.quantity) as total_sold')
            )
            ->join('orders', 'order_items.order_id', '=', 'orders.order_id')
            ->join('menu_items', 'order_items.item_id', '=', 'menu_items.item_id')
            ->where('orders.vendor_id', $vendor->user_id)
            ->where('orders.status', 'completed')
            ->whereBetween('orders.created_at', [$weekStart, now()])
            ->groupBy('order_items.item_id', 'menu_items.name', 'menu_items.image_path')
            ->orderBy('total_sold', 'desc')
            ->limit(3)
            ->get();
        
        // Calculate max for progress bar percentage
        $maxSold = $topSellingItems->max('total_sold') ?: 1;
        foreach ($topSellingItems as $item) {
            $item->percentage = ($item->total_sold / $maxSold) * 100;
        }
        
        return view('vendor.dashboard', compact(
            'todayOrders',
            'todayRevenue',
            'orderGrowth',
            'revenueGrowth',
            'pendingOrders',
            'totalMenuItems',
            'availableMenuItems',
            'recentOrders',
            'recentMenuItems',
            'weekRevenue',
            'weekOrders',
            'weekGrowth',
            'avgOrderValue',
            'topSellingItems'
        ));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VendorReportController extends Controller
{
    /**
     * Display vendor reports and analytics
     */
    public function index(Request $request)
    {
        $vendor = Auth::user();
        
        // Get date range filter (default: last 7 days)
        $period = $request->input('period', '7days');
        $dateRange = $this->getDateRange($period);
        
        // Get revenue statistics
        $revenueStats = $this->getRevenueStatistics($vendor->user_id, $dateRange);
        
        // Get order statistics
        $orderStats = $this->getOrderStatistics($vendor->user_id, $dateRange);
        
        // Get top selling items
        $topSellingItems = $this->getTopSellingItems($vendor->user_id, $dateRange, 5);
        
        // Get sales chart data (daily sales for the period)
        $salesChartData = $this->getSalesChartData($vendor->user_id, $dateRange);
        
        // Get order status distribution
        $orderStatusDistribution = $this->getOrderStatusDistribution($vendor->user_id, $dateRange);
        
        return view('vendor.reports', compact(
            'revenueStats',
            'orderStats',
            'topSellingItems',
            'salesChartData',
            'orderStatusDistribution',
            'period'
        ));
    }
    
    /**
     * Get date range based on period
     */
    private function getDateRange($period)
    {
        $end = now()->endOfDay();
        
        switch ($period) {
            case '7days':
                $start = now()->subDays(6)->startOfDay();
                break;
            case '30days':
                $start = now()->subDays(29)->startOfDay();
                break;
            case 'this_month':
                $start = now()->startOfMonth();
                break;
            case 'last_month':
                $start = now()->subMonth()->startOfMonth();
                $end = now()->subMonth()->endOfMonth();
                break;
            case 'this_year':
                $start = now()->startOfYear();
                break;
            case 'custom':
                $start = request('start_date') ? \Carbon\Carbon::parse(request('start_date'))->startOfDay() : now()->subDays(6)->startOfDay();
                $end = request('end_date') ? \Carbon\Carbon::parse(request('end_date'))->endOfDay() : now()->endOfDay();
                break;
            default:
                $start = now()->subDays(6)->startOfDay();
        }
        
        return ['start' => $start, 'end' => $end];
    }
    
    /**
     * Get revenue statistics
     */
    private function getRevenueStatistics($vendorId, $dateRange)
    {
        // Total revenue for the period (only completed orders)
        $totalRevenue = Order::where('vendor_id', $vendorId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->sum('total_price');
        
        // Compare with previous period
        $periodLength = $dateRange['start']->diffInDays($dateRange['end']) + 1;
        $previousStart = $dateRange['start']->copy()->subDays($periodLength);
        $previousEnd = $dateRange['start']->copy()->subDay();
        
        $previousRevenue = Order::where('vendor_id', $vendorId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->sum('total_price');
        
        $revenueGrowth = $previousRevenue > 0 
            ? (($totalRevenue - $previousRevenue) / $previousRevenue) * 100 
            : 0;
        
        return [
            'total' => $totalRevenue,
            'growth' => round($revenueGrowth, 1),
            'previous' => $previousRevenue,
        ];
    }
    
    /**
     * Get order statistics
     */
    private function getOrderStatistics($vendorId, $dateRange)
    {
        // Total orders
        $totalOrders = Order::where('vendor_id', $vendorId)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->count();
        
        // Completed orders
        $completedOrders = Order::where('vendor_id', $vendorId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->count();
        
        // Compare with previous period
        $periodLength = $dateRange['start']->diffInDays($dateRange['end']) + 1;
        $previousStart = $dateRange['start']->copy()->subDays($periodLength);
        $previousEnd = $dateRange['start']->copy()->subDay();
        
        $previousOrders = Order::where('vendor_id', $vendorId)
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->count();
        
        $orderGrowth = $previousOrders > 0 
            ? (($totalOrders - $previousOrders) / $previousOrders) * 100 
            : 0;
        
        // Average order value (completed orders only)
        $avgOrderValue = $completedOrders > 0 
            ? Order::where('vendor_id', $vendorId)
                ->where('status', 'completed')
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->avg('total_price')
            : 0;
        
        return [
            'total' => $totalOrders,
            'completed' => $completedOrders,
            'growth' => round($orderGrowth, 1),
            'avg_value' => $avgOrderValue,
        ];
    }
    
    /**
     * Get top selling items
     */
    private function getTopSellingItems($vendorId, $dateRange, $limit = 5)
    {
        return OrderItem::select(
                'order_items.item_id',
                'menu_items.name',
                'menu_items.price',
                DB::raw('COUNT(order_items.order_item_id) as order_count'),
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.quantity * order_items.price) as total_sales')
            )
            ->join('orders', 'order_items.order_id', '=', 'orders.order_id')
            ->join('menu_items', 'order_items.item_id', '=', 'menu_items.item_id')
            ->where('orders.vendor_id', $vendorId)
            ->where('orders.status', 'completed')
            ->whereBetween('orders.created_at', [$dateRange['start'], $dateRange['end']])
            ->groupBy('order_items.item_id', 'menu_items.name', 'menu_items.price')
            ->orderBy('total_sales', 'desc')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Get daily sales data for chart
     */
    private function getSalesChartData($vendorId, $dateRange)
    {
        $dailySales = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_price) as revenue')
            )
            ->where('vendor_id', $vendorId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'asc')
            ->get();
        
        // Fill in missing dates with zero values
        $result = [];
        $currentDate = $dateRange['start']->copy();
        
        while ($currentDate <= $dateRange['end']) {
            $dateString = $currentDate->format('Y-m-d');
            $dayData = $dailySales->firstWhere('date', $dateString);
            
            $result[] = [
                'date' => $currentDate->format('M d'),
                'full_date' => $dateString,
                'revenue' => $dayData ? floatval($dayData->revenue) : 0,
                'orders' => $dayData ? $dayData->order_count : 0,
            ];
            
            $currentDate->addDay();
        }
        
        return $result;
    }
    
    /**
     * Get order status distribution
     */
    private function getOrderStatusDistribution($vendorId, $dateRange)
    {
        $statusCounts = Order::select('status', DB::raw('COUNT(*) as count'))
            ->where('vendor_id', $vendorId)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');
        
        $total = $statusCounts->sum();
        
        return [
            'completed' => [
                'count' => $statusCounts->get('completed', 0),
                'percentage' => $total > 0 ? round(($statusCounts->get('completed', 0) / $total) * 100, 1) : 0,
            ],
            'accepted' => [
                'count' => $statusCounts->get('accepted', 0),
                'percentage' => $total > 0 ? round(($statusCounts->get('accepted', 0) / $total) * 100, 1) : 0,
            ],
            'preparing' => [
                'count' => $statusCounts->get('preparing', 0),
                'percentage' => $total > 0 ? round(($statusCounts->get('preparing', 0) / $total) * 100, 1) : 0,
            ],
            'ready' => [
                'count' => $statusCounts->get('ready', 0),
                'percentage' => $total > 0 ? round(($statusCounts->get('ready', 0) / $total) * 100, 1) : 0,
            ],
        ];
    }
}

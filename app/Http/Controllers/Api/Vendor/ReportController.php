<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Get vendor reports and analytics
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $vendor = $request->user();

        // Get date range filter (default: last 7 days)
        $period = $request->input('period', '7days');
        $dateRange = $this->getDateRange($period, $request);

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

        return response()->json([
            'success' => true,
            'data' => [
                'period' => $period,
                'date_range' => [
                    'start' => $dateRange['start']->toDateString(),
                    'end' => $dateRange['end']->toDateString(),
                ],
                'revenue' => $revenueStats,
                'orders' => $orderStats,
                'top_selling_items' => $topSellingItems,
                'sales_chart' => $salesChartData,
                'order_status_distribution' => $orderStatusDistribution,
            ]
        ]);
    }

    /**
     * Get revenue statistics only
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function revenue(Request $request)
    {
        $vendor = $request->user();
        $period = $request->input('period', '7days');
        $dateRange = $this->getDateRange($period, $request);

        $revenueStats = $this->getRevenueStatistics($vendor->user_id, $dateRange);

        return response()->json([
            'success' => true,
            'data' => $revenueStats
        ]);
    }

    /**
     * Get order statistics only
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function orders(Request $request)
    {
        $vendor = $request->user();
        $period = $request->input('period', '7days');
        $dateRange = $this->getDateRange($period, $request);

        $orderStats = $this->getOrderStatistics($vendor->user_id, $dateRange);
        $orderStatusDistribution = $this->getOrderStatusDistribution($vendor->user_id, $dateRange);

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => $orderStats,
                'status_distribution' => $orderStatusDistribution,
            ]
        ]);
    }

    /**
     * Get top selling items
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function topItems(Request $request)
    {
        $vendor = $request->user();
        $period = $request->input('period', '7days');
        $limit = $request->input('limit', 10);
        $dateRange = $this->getDateRange($period, $request);

        $topSellingItems = $this->getTopSellingItems($vendor->user_id, $dateRange, $limit);

        return response()->json([
            'success' => true,
            'data' => $topSellingItems
        ]);
    }

    /**
     * Get sales chart data
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function salesChart(Request $request)
    {
        $vendor = $request->user();
        $period = $request->input('period', '7days');
        $dateRange = $this->getDateRange($period, $request);

        $salesChartData = $this->getSalesChartData($vendor->user_id, $dateRange);

        return response()->json([
            'success' => true,
            'data' => $salesChartData
        ]);
    }

    /**
     * Get date range based on period
     */
    private function getDateRange($period, $request = null)
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
                $start = $request && $request->start_date 
                    ? \Carbon\Carbon::parse($request->start_date)->startOfDay() 
                    : now()->subDays(6)->startOfDay();
                $end = $request && $request->end_date 
                    ? \Carbon\Carbon::parse($request->end_date)->endOfDay() 
                    : now()->endOfDay();
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
            'total' => (float) $totalRevenue,
            'growth' => round($revenueGrowth, 1),
            'previous' => (float) $previousRevenue,
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

        // Cancelled orders
        $cancelledOrders = Order::where('vendor_id', $vendorId)
            ->where('status', 'cancelled')
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

        // Completion rate
        $completionRate = $totalOrders > 0 ? ($completedOrders / $totalOrders) * 100 : 0;

        return [
            'total' => $totalOrders,
            'completed' => $completedOrders,
            'cancelled' => $cancelledOrders,
            'growth' => round($orderGrowth, 1),
            'avg_value' => round($avgOrderValue, 2),
            'completion_rate' => round($completionRate, 1),
        ];
    }

    /**
     * Get top selling items
     */
    private function getTopSellingItems($vendorId, $dateRange, $limit = 5)
    {
        $items = OrderItem::select(
                'order_items.item_id',
                'menu_items.name',
                'menu_items.price',
                'menu_items.image_path',
                DB::raw('COUNT(order_items.order_item_id) as order_count'),
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.quantity * order_items.price) as total_sales')
            )
            ->join('orders', 'order_items.order_id', '=', 'orders.order_id')
            ->join('menu_items', 'order_items.item_id', '=', 'menu_items.item_id')
            ->where('orders.vendor_id', $vendorId)
            ->where('orders.status', 'completed')
            ->whereBetween('orders.created_at', [$dateRange['start'], $dateRange['end']])
            ->groupBy('order_items.item_id', 'menu_items.name', 'menu_items.price', 'menu_items.image_path')
            ->orderBy('total_sales', 'desc')
            ->limit($limit)
            ->get();

        return $items->map(function($item) {
            return [
                'item_id' => $item->item_id,
                'name' => $item->name,
                'price' => (float) $item->price,
                'image_url' => $item->image_path ? asset($item->image_path) : null,
                'order_count' => (int) $item->order_count,
                'total_quantity' => (int) $item->total_quantity,
                'total_sales' => (float) $item->total_sales,
            ];
        });
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
            $dateString = $currentDate->toDateString();
            $dayData = $dailySales->firstWhere('date', $dateString);
            
            $result[] = [
                'date' => $dateString,
                'order_count' => $dayData ? (int) $dayData->order_count : 0,
                'revenue' => $dayData ? (float) $dayData->revenue : 0,
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
        $distribution = Order::select('status', DB::raw('COUNT(*) as count'))
            ->where('vendor_id', $vendorId)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->groupBy('status')
            ->get();

        $result = [
            'pending' => 0,
            'accepted' => 0,
            'preparing' => 0,
            'ready' => 0,
            'completed' => 0,
            'cancelled' => 0,
        ];

        foreach ($distribution as $item) {
            if (isset($result[$item->status])) {
                $result[$item->status] = (int) $item->count;
            }
        }

        return $result;
    }
}

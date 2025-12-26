<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VendorReportController extends Controller
{
    use ApiResponse;
    
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isVendor() || !$user->vendor) {
            if ($request->ajax() || $request->wantsJson()) {
                return $this->forbiddenResponse('Access denied. Vendor only.');
            }
            abort(403, 'Access denied. Vendor only.');
        }
        
        $vendor = $user->vendor;
        $period = $request->input('period', '7days');
        $dateRange = $this->getDateRange($period);
        
        $revenueStats = $this->getRevenueStatistics($vendor->id, $dateRange);
        $orderStats = $this->getOrderStatistics($vendor->id, $dateRange);
        $topSellingItems = $this->getTopSellingItems($vendor->id, $dateRange, 5);
        $salesChartData = $this->getSalesChartData($vendor->id, $dateRange);
        $orderStatusDistribution = $this->getOrderStatusDistribution($vendor->id, $dateRange);
        
        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return $this->successResponse([
                'revenueStats' => $revenueStats,
                'orderStats' => $orderStats,
                'topSellingItems' => $topSellingItems->map(fn($item) => [
                    'menu_item_id' => $item->menu_item_id,
                    'name' => $item->name,
                    'price' => (float) $item->price,
                    'order_count' => $item->order_count,
                    'total_quantity' => $item->total_quantity,
                    'total_sales' => (float) $item->total_sales,
                ]),
                'salesChartData' => $salesChartData,
                'orderStatusDistribution' => $orderStatusDistribution,
                'period' => $period,
            ]);
        }
        
        return view('vendor.reports', compact(
            'vendor',
            'revenueStats',
            'orderStats',
            'topSellingItems',
            'salesChartData',
            'orderStatusDistribution',
            'period'
        ));
    }
    
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
            default:
                $start = now()->subDays(6)->startOfDay();
        }
        
        return ['start' => $start, 'end' => $end];
    }
    
    private function getRevenueStatistics($vendorId, $dateRange)
    {
        $totalRevenue = Order::where('vendor_id', $vendorId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->sum('total');
        
        $periodLength = $dateRange['start']->diffInDays($dateRange['end']) + 1;
        $previousStart = $dateRange['start']->copy()->subDays($periodLength);
        $previousEnd = $dateRange['start']->copy()->subDay();
        
        $previousRevenue = Order::where('vendor_id', $vendorId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->sum('total');
        
        $revenueGrowth = $previousRevenue > 0 
            ? (($totalRevenue - $previousRevenue) / $previousRevenue) * 100 
            : 0;
        
        return [
            'total' => $totalRevenue,
            'growth' => round($revenueGrowth, 1),
            'previous' => $previousRevenue,
        ];
    }
    
    private function getOrderStatistics($vendorId, $dateRange)
    {
        $totalOrders = Order::where('vendor_id', $vendorId)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->count();
        
        $completedOrders = Order::where('vendor_id', $vendorId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->count();
        
        $periodLength = $dateRange['start']->diffInDays($dateRange['end']) + 1;
        $previousStart = $dateRange['start']->copy()->subDays($periodLength);
        $previousEnd = $dateRange['start']->copy()->subDay();
        
        $previousOrders = Order::where('vendor_id', $vendorId)
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->count();
        
        $orderGrowth = $previousOrders > 0 
            ? (($totalOrders - $previousOrders) / $previousOrders) * 100 
            : 0;
        
        $avgOrderValue = $completedOrders > 0 
            ? Order::where('vendor_id', $vendorId)
                ->where('status', 'completed')
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->avg('total')
            : 0;
        
        return [
            'total' => $totalOrders,
            'completed' => $completedOrders,
            'growth' => round($orderGrowth, 1),
            'avg_value' => $avgOrderValue,
        ];
    }
    
    private function getTopSellingItems($vendorId, $dateRange, $limit = 5)
    {
        return OrderItem::select(
                'order_items.menu_item_id',
                'menu_items.name',
                'menu_items.price',
                DB::raw('COUNT(order_items.id) as order_count'),
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.quantity * order_items.unit_price) as total_sales')
            )
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('menu_items', 'order_items.menu_item_id', '=', 'menu_items.id')
            ->where('orders.vendor_id', $vendorId)
            ->where('orders.status', 'completed')
            ->whereBetween('orders.created_at', [$dateRange['start'], $dateRange['end']])
            ->groupBy('order_items.menu_item_id', 'menu_items.name', 'menu_items.price')
            ->orderBy('total_sales', 'desc')
            ->limit($limit)
            ->get();
    }
    
    private function getSalesChartData($vendorId, $dateRange)
    {
        $dailySales = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total) as revenue')
            )
            ->where('vendor_id', $vendorId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'asc')
            ->get();
        
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
            'confirmed' => [
                'count' => $statusCounts->get('confirmed', 0),
                'percentage' => $total > 0 ? round(($statusCounts->get('confirmed', 0) / $total) * 100, 1) : 0,
            ],
            'preparing' => [
                'count' => $statusCounts->get('preparing', 0),
                'percentage' => $total > 0 ? round(($statusCounts->get('preparing', 0) / $total) * 100, 1) : 0,
            ],
            'ready' => [
                'count' => $statusCounts->get('ready', 0),
                'percentage' => $total > 0 ? round(($statusCounts->get('ready', 0) / $total) * 100, 1) : 0,
            ],
            'cancelled' => [
                'count' => $statusCounts->get('cancelled', 0),
                'percentage' => $total > 0 ? round(($statusCounts->get('cancelled', 0) / $total) * 100, 1) : 0,
            ],
        ];
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\CartItem;
use App\Patterns\State\OrderStateManager;
use App\Patterns\Observer\QueueSubject;
use App\Patterns\Observer\NotificationObserver;
use App\Patterns\Observer\DashboardObserver;
use App\Patterns\Observer\AnalyticsObserver;

class OrderController extends Controller
{
    /**
     * Display user's orders
     */
    public function index(Request $request)
    {
        $query = Order::with(['orderItems.menuItem', 'payment', 'pickup', 'vendor'])
            ->where('user_id', Auth::id());
        
        // Filter by status if provided
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        // Filter by date range if provided
        if ($request->has('date_range')) {
            switch ($request->date_range) {
                case '7days':
                    $query->where('created_at', '>=', now()->subDays(7));
                    break;
                case '3months':
                    $query->where('created_at', '>=', now()->subMonths(3));
                    break;
                case '1year':
                    $query->where('created_at', '>=', now()->subYear());
                    break;
                default: // 30days
                    $query->where('created_at', '>=', now()->subDays(30));
            }
        }
        
        // Search by order ID
        if ($request->has('search') && $request->search) {
            $query->where('order_id', 'like', '%' . $request->search . '%');
        }
        
        // Get orders with pagination
        $orders = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Get active order (most recent non-completed order)
        $activeOrder = Order::with(['orderItems.menuItem', 'payment', 'pickup', 'vendor'])
            ->where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'accepted', 'preparing', 'ready'])
            ->orderBy('created_at', 'desc')
            ->first();
        
        return view('orders', compact('orders', 'activeOrder'));
    }
    
    /**
     * Display single order details
     */
    public function show($orderId)
    {
        $order = Order::with(['orderItems.menuItem', 'payment', 'pickup', 'vendor'])
            ->where('order_id', $orderId)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        return view('order-details', compact('order'));
    }
    
    /**
     * Reorder items from previous order
     */
    public function reorder($orderId)
    {
        $order = Order::with(['orderItems.menuItem'])
            ->where('order_id', $orderId)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        // Clear current cart
        CartItem::where('user_id', Auth::id())->delete();
        
        // Add items from previous order to cart
        foreach ($order->orderItems as $item) {
            // Check if menu item still exists and is available
            if ($item->menuItem && $item->menuItem->availability === 1) {
                CartItem::create([
                    'user_id' => Auth::id(),
                    'item_id' => $item->item_id,
                    'quantity' => $item->quantity,
                ]);
            }
        }
        
        return redirect()->route('cart')->with('success', 'Items added to cart! Some items may not be available anymore.');
    }
}

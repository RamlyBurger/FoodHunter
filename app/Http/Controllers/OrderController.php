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
use App\Services\OutputEncodingService;

class OrderController extends Controller
{
    private OutputEncodingService $outputEncoder;

    public function __construct(OutputEncodingService $outputEncoder)
    {
        // [19] Initialize output encoding service
        $this->outputEncoder = $outputEncoder;
        
        // [140] Disable client-side caching on sensitive order pages
        /** @phpstan-ignore-next-line */
        $this->middleware(function ($request, $next) {
            $response = $next($request);
            
            // Set cache control headers to prevent caching of sensitive data
            $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->header('Pragma', 'no-cache');
            $response->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
            
            return $response;
        });
    }
    
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
            // [19] Sanitize search input
            $sanitizedSearch = $this->outputEncoder->encodeOrderId($request->search);
            $query->where('order_id', 'like', '%' . $sanitizedSearch . '%');
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
        // [19] Sanitize order ID input
        $sanitizedOrderId = $this->outputEncoder->encodeOrderId($orderId);
        
        $order = Order::with(['orderItems.menuItem', 'payment', 'pickup', 'vendor'])
            ->where('order_id', $sanitizedOrderId)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        // [19] Sanitize order data for display
        $sanitizedOrder = [
            'order' => $order,
            'order_id_display' => $this->outputEncoder->encodeForHtml($order->order_id),
            'total_display' => $this->outputEncoder->encodeMonetaryValue($order->total_amount),
        ];
        
        return view('order-details', $sanitizedOrder);
    }
    
    /**
     * Reorder items from previous order
     */
    public function reorder($orderId)
    {
        // [19] Sanitize order ID input
        $sanitizedOrderId = $this->outputEncoder->encodeOrderId($orderId);
        
        $order = Order::with(['orderItems.menuItem'])
            ->where('order_id', $sanitizedOrderId)
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

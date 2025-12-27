<?php
/**
 * =============================================================================
 * Order Controller - Low Nam Lee (Order & Pickup Module)
 * =============================================================================
 * 
 * @author     Low Nam Lee
 * @module     Order & Pickup Module
 * @pattern    State Pattern (OrderStateManager)
 * 
 * Handles customer order viewing, status tracking, cancellation, and reordering.
 * Integrates with Lee Song Yan's Cart module for reorder functionality.
 * =============================================================================
 */

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Helpers\ImageHelper;
use App\Models\Order;
use App\Models\CartItem;
use App\Patterns\State\OrderStateManager;
use App\Services\NotificationService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    use ApiResponse;
    public function index()
    {
        $filter = request('filter', 'active');
        $search = request('search');
        $dateRange = request('date_range', '7days');
        $statusFilter = request('status', 'active');
        
        $query = Order::where('user_id', Auth::id())
            ->with(['vendor', 'items.menuItem', 'pickup']);

        // Apply search filter
        if ($search) {
            $query->where('order_number', 'like', '%' . $search . '%');
        }

        // Apply date range filter
        switch ($dateRange) {
            case '7days':
                $query->where('created_at', '>=', now()->subDays(7));
                break;
            case '30days':
                $query->where('created_at', '>=', now()->subDays(30));
                break;
            case '3months':
                $query->where('created_at', '>=', now()->subMonths(3));
                break;
            case '6months':
                $query->where('created_at', '>=', now()->subMonths(6));
                break;
            case 'all':
                // No date filter
                break;
        }

        // Apply status filter (unified - from both tabs and dropdown)
        switch ($statusFilter) {
            case 'active':
                $query->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready']);
                break;
            case 'completed':
                $query->where('status', 'completed');
                break;
            case 'cancelled':
                $query->where('status', 'cancelled');
                break;
            case 'pending':
            case 'confirmed':
            case 'preparing':
            case 'ready':
                $query->where('status', $statusFilter);
                break;
            case 'all':
                // No status filter
                break;
        }

        // Sort: Active orders (pending, confirmed, preparing, ready) first, then by created_at desc
        $orders = $query->orderByRaw("
            CASE 
                WHEN status IN ('pending', 'confirmed', 'preparing', 'ready') THEN 0 
                ELSE 1 
            END
        ")
        ->orderBy('created_at', 'desc')
        ->paginate(15);

        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return $this->successResponse([
                'orders' => $orders->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'total' => (float) $order->total,
                        'created_at' => $order->created_at->format('M d, Y H:i'),
                        'created_at_human' => $order->created_at->diffForHumans(),
                        'vendor' => $order->vendor ? [
                            'id' => $order->vendor->id,
                            'store_name' => $order->vendor->store_name,
                            'logo' => ImageHelper::vendorLogo($order->vendor->logo, $order->vendor->store_name),
                        ] : null,
                        'items_count' => $order->items->count(),
                        'pickup' => $order->pickup ? [
                            'queue_number' => $order->pickup->queue_number,
                        ] : null,
                        'can_cancel' => $order->canBeCancelled(),
                    ];
                }),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                    'from' => $orders->firstItem(),
                    'to' => $orders->lastItem(),
                ],
                'filters' => [
                    'status' => $statusFilter,
                    'date_range' => $dateRange,
                    'search' => $search,
                ],
            ]);
        }

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        // Security: IDOR Protection
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load(['items', 'payment', 'pickup', 'vendor']);

        return view('orders.show', compact('order'));
    }

    public function cancel(Order $order)
    {
        // Security: IDOR Protection
        if ($order->user_id !== Auth::id()) {
            if (request()->ajax() || request()->wantsJson()) {
                return $this->forbiddenResponse('Unauthorized');
            }
            abort(403);
        }

        if (!$order->canBeCancelled()) {
            if (request()->ajax() || request()->wantsJson()) {
                return $this->errorResponse('This order cannot be cancelled.', 400);
            }
            return back()->with('error', 'This order cannot be cancelled.');
        }

        // Load vendor relationship before cancellation
        $order->load('vendor.user');

        $result = OrderStateManager::cancel($order, 'Cancelled by customer');

        if ($result) {
            // Notify vendor of cancellation
            if ($order->vendor && $order->vendor->user) {
                $notificationService = app(NotificationService::class);
                $notificationService->notifyVendorOrderCancelled(
                    $order->vendor->user->id,
                    $order->id,
                    Auth::user()->name
                );
            }
            
            if (request()->ajax() || request()->wantsJson()) {
                return $this->successResponse(null, 'Order cancelled successfully.');
            }
            return redirect('/orders')->with('success', 'Order cancelled successfully.');
        }

        if (request()->ajax() || request()->wantsJson()) {
            return $this->serverErrorResponse('Failed to cancel order.');
        }
        return back()->with('error', 'Failed to cancel order.');
    }

    public function reorder(Order $order)
    {
        // Security: IDOR Protection
        if ($order->user_id !== Auth::id()) {
            if (request()->ajax() || request()->wantsJson()) {
                return $this->forbiddenResponse('Unauthorized');
            }
            abort(403);
        }

        $order->load('items.menuItem');
        $addedCount = 0;
        $unavailableItems = [];

        foreach ($order->items as $item) {
            if (!$item->menuItem || !$item->menuItem->is_available) {
                $unavailableItems[] = $item->menuItem->name ?? 'Unknown item';
                continue;
            }

            // Check if item already in cart
            $cartItem = CartItem::where('user_id', Auth::id())
                ->where('menu_item_id', $item->menu_item_id)
                ->first();

            if ($cartItem) {
                $cartItem->increment('quantity', $item->quantity);
            } else {
                CartItem::create([
                    'user_id' => Auth::id(),
                    'menu_item_id' => $item->menu_item_id,
                    'quantity' => $item->quantity,
                    'special_instructions' => $item->special_instructions,
                ]);
            }
            $addedCount++;
        }

        if ($addedCount === 0) {
            if (request()->ajax() || request()->wantsJson()) {
                return $this->errorResponse('None of the items from this order are currently available.', 400);
            }
            return back()->with('error', 'None of the items from this order are currently available.');
        }

        $message = "{$addedCount} item(s) added to your cart.";
        if (!empty($unavailableItems)) {
            $message .= ' Some items were unavailable: ' . implode(', ', $unavailableItems);
        }

        if (request()->ajax() || request()->wantsJson()) {
            return $this->successResponse([
                'redirect' => route('cart.index')
            ], $message);
        }

        return redirect()->route('cart.index')->with('success', $message);
    }
}

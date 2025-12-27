<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Helpers\ImageHelper;
use App\Models\CartItem;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Pickup;
use App\Patterns\Builder\OrderBuilder;
use App\Patterns\Factory\VoucherFactory;
use App\Models\UserVoucher;
use App\Models\Voucher;
use App\Services\NotificationService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Cart Controller - Lee Song Yan
 * 
 * Uses Factory Pattern (via VoucherFactory) for voucher discount calculations.
 * Uses Observer Pattern for notifications when checkout completes.
 */
class CartController extends Controller
{
    use ApiResponse;
    public function index(Request $request)
    {
        $cartItems = CartItem::where('user_id', Auth::id())
            ->with('menuItem.vendor')
            ->get();

        $summary = $this->calculateSummary($cartItems);

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return $this->successResponse([
                'items' => $cartItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'menu_item_id' => $item->menu_item_id,
                        'quantity' => $item->quantity,
                        'special_instructions' => $item->special_instructions,
                        'menu_item' => [
                            'id' => $item->menuItem->id,
                            'name' => $item->menuItem->name,
                            'price' => (float) $item->menuItem->price,
                            'image' => ImageHelper::menuItem($item->menuItem->image),
                            'is_available' => $item->menuItem->is_available,
                        ],
                        'vendor' => $item->menuItem->vendor ? [
                            'id' => $item->menuItem->vendor->id,
                            'store_name' => $item->menuItem->vendor->store_name,
                            'is_open' => $item->menuItem->vendor->is_open,
                        ] : null,
                        'subtotal' => (float) ($item->menuItem->price * $item->quantity),
                    ];
                }),
                'summary' => $summary,
            ]);
        }

        return view('cart.index', compact('cartItems', 'summary'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'menu_item_id' => 'required|exists:menu_items,id',
            'quantity' => 'required|integer|min:1|max:99',
            'special_instructions' => 'nullable|string|max:500',
        ]);

        $item = MenuItem::findOrFail($request->menu_item_id);

        if (!$item->is_available) {
            if ($request->ajax() || $request->wantsJson()) {
                return $this->errorResponse('This item is currently unavailable.', 400);
            }
            return back()->with('error', 'This item is currently unavailable.');
        }

        $cartItem = CartItem::where('user_id', Auth::id())
            ->where('menu_item_id', $request->menu_item_id)
            ->first();

        if ($cartItem) {
            $cartItem->update([
                'quantity' => min(99, $cartItem->quantity + $request->quantity),
                'special_instructions' => $request->special_instructions,
            ]);
        } else {
            CartItem::create([
                'user_id' => Auth::id(),
                'menu_item_id' => $request->menu_item_id,
                'quantity' => $request->quantity,
                'special_instructions' => $request->special_instructions,
            ]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            $cartCount = CartItem::where('user_id', Auth::id())->sum('quantity');
            return $this->successResponse([
                'item_name' => $item->name,
                'cart_count' => (int) $cartCount,
            ], $item->name . ' added to cart!');
        }

        return redirect('/cart')->with('success', $item->name . ' added to cart!');
    }

    public function update(Request $request, CartItem $cartItem)
    {
        if ($cartItem->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate(['quantity' => 'required|integer|min:1|max:99']);
        $cartItem->update(['quantity' => $request->quantity]);

        if ($request->ajax() || $request->wantsJson()) {
            $cartItems = CartItem::where('user_id', Auth::id())->with('menuItem')->get();
            $summary = $this->calculateSummary($cartItems);
            
            return $this->successResponse([
                'item_total' => $cartItem->menuItem->price * $cartItem->quantity,
                'summary' => $summary,
                'cart_count' => (int) $cartItems->sum('quantity'),
            ], 'Cart updated.');
        }

        return back()->with('success', 'Cart updated.');
    }

    public function remove(CartItem $cartItem)
    {
        if ($cartItem->user_id !== Auth::id()) {
            abort(403);
        }

        $cartItem->delete();

        if (request()->ajax() || request()->wantsJson()) {
            $cartItems = CartItem::where('user_id', Auth::id())->with('menuItem')->get();
            $summary = $this->calculateSummary($cartItems);
            
            return $this->successResponse([
                'summary' => $summary,
                'cart_count' => (int) $cartItems->sum('quantity'),
            ], 'Item removed from cart.');
        }

        return back()->with('success', 'Item removed from cart.');
    }

    public function clear()
    {
        CartItem::where('user_id', Auth::id())->delete();
        
        // Clear any applied voucher
        session()->forget('applied_voucher');

        if (request()->ajax() || request()->wantsJson()) {
            return $this->successResponse([
                'cart_count' => 0,
            ], 'Cart cleared.');
        }

        return back()->with('success', 'Cart cleared.');
    }

    public function checkout()
    {
        $cartItems = CartItem::where('user_id', Auth::id())
            ->with('menuItem.vendor')
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect('/cart')->with('error', 'Your cart is empty.');
        }

        $summary = $this->calculateSummary($cartItems);

        return view('cart.checkout', compact('cartItems', 'summary'));
    }

    /**
     * Process checkout with idempotency protection
     * Security: Replay Attack Prevention [OWASP 64]
     */
    public function processCheckout(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,card,ewallet,online_banking',
            'notes' => 'nullable|string|max:500',
            'stripe_payment_method_id' => 'nullable|string',
            'idempotency_key' => 'nullable|string|max:64',
        ]);

        // Security: Replay Attack Prevention [OWASP 64]
        // Check for duplicate submission using idempotency key
        $idempotencyKey = $request->idempotency_key ?? session('checkout_idempotency_key');
        if ($idempotencyKey) {
            $cacheKey = 'checkout_' . Auth::id() . '_' . $idempotencyKey;
            if (cache()->has($cacheKey)) {
                // This is a replay/duplicate request
                $existingOrderId = cache()->get($cacheKey);
                return redirect('/orders/' . $existingOrderId)
                    ->with('info', 'This order was already processed.');
            }
        }

        $cartItems = CartItem::where('user_id', Auth::id())
            ->with('menuItem.vendor')
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect('/cart')->with('error', 'Your cart is empty.');
        }

        // Process Stripe payment for card payments
        if ($request->payment_method === 'card' && $request->stripe_payment_method_id) {
            try {
                \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
                
                $summary = $this->calculateSummary($cartItems);
                $amountInCents = (int) round($summary['total'] * 100);
                
                $paymentIntent = \Stripe\PaymentIntent::create([
                    'amount' => $amountInCents,
                    'currency' => 'myr',
                    'payment_method' => $request->stripe_payment_method_id,
                    'confirm' => true,
                    'automatic_payment_methods' => [
                        'enabled' => true,
                        'allow_redirects' => 'never',
                    ],
                    'metadata' => [
                        'user_id' => Auth::id(),
                        'user_email' => Auth::user()->email,
                    ],
                ]);

                if ($paymentIntent->status !== 'succeeded') {
                    return back()->with('error', 'Payment failed. Please try again.');
                }
            } catch (\Stripe\Exception\CardException $e) {
                return back()->with('error', 'Card declined: ' . $e->getMessage());
            } catch (\Exception $e) {
                return back()->with('error', 'Payment failed: ' . $e->getMessage());
            }
        }

        try {
            DB::beginTransaction();

            $vendorGroups = $cartItems->groupBy(fn($item) => $item->menuItem->vendor_id);
            $orders = [];

            // Calculate voucher discount before building orders using Factory Pattern
            $appliedVoucher = session('applied_voucher');
            $totalSubtotal = $cartItems->sum(fn($item) => $item->menuItem->price * $item->quantity);
            $totalDiscount = 0;

            if ($appliedVoucher) {
                // Use Factory Pattern to calculate discount
                $voucher = Voucher::find($appliedVoucher['id']);
                if ($voucher && VoucherFactory::isApplicable($voucher, $totalSubtotal)) {
                    $totalDiscount = VoucherFactory::calculateDiscount($voucher, $totalSubtotal);
                }
            }

            foreach ($vendorGroups as $vendorId => $items) {
                $builder = new OrderBuilder();
                
                // Calculate proportional discount for this vendor's items
                $vendorSubtotal = $items->sum(fn($item) => $item->menuItem->price * $item->quantity);
                $vendorDiscount = $totalSubtotal > 0 ? $totalDiscount * ($vendorSubtotal / $totalSubtotal) : 0;
                
                $order = $builder
                    ->setCustomer(Auth::id())
                    ->setVendor($vendorId)
                    ->setCartItems($items)
                    ->setNotes($request->notes)
                    ->setPaymentMethod($request->payment_method)
                    ->applyVoucher($appliedVoucher['code'] ?? '', round($vendorDiscount, 2))
                    ->calculateTotals()
                    ->build();

                $orders[] = $order;
            }

            CartItem::where('user_id', Auth::id())->delete();

            // Mark voucher as used if applied
            if ($appliedVoucher) {
                $userVoucher = UserVoucher::where('user_id', Auth::id())
                    ->where('voucher_id', $appliedVoucher['id'])
                    ->first();
                
                if ($userVoucher) {
                    $userVoucher->increment('usage_count');
                    $userVoucher->update(['used_at' => now()]);
                }

                // Increment voucher global usage count
                Voucher::where('id', $appliedVoucher['id'])->increment('usage_count');

                // Clear applied voucher from session
                session()->forget('applied_voucher');
            }

            DB::commit();

            // Security: Store idempotency key to prevent replay attacks [OWASP 64]
            // Cache the order ID for 1 hour to detect duplicate submissions
            if ($idempotencyKey) {
                $cacheKey = 'checkout_' . Auth::id() . '_' . $idempotencyKey;
                cache()->put($cacheKey, $orders[0]->id, now()->addHour());
            }

            // Send notifications to vendors for each order
            $notificationService = app(NotificationService::class);
            $customerName = Auth::user()->name;
            
            foreach ($orders as $order) {
                // Reload order with vendor and user relationships
                $order = Order::with('vendor.user')->find($order->id);
                
                // Notify vendor of new order
                if ($order && $order->vendor && $order->vendor->user) {
                    $notificationService->notifyVendorNewOrder(
                        $order->vendor->user->id,
                        $order->id,
                        $customerName,
                        (float) $order->total
                    );
                }
                
                // Notify customer of order placed
                $notificationService->notifyOrderCreated(Auth::id(), $order->id);
            }

            // Return JSON for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return $this->successResponse([
                    'orders' => collect($orders)->map(fn($o) => [
                        'id' => $o->id,
                        'order_number' => $o->order_number,
                    ]),
                    'redirect' => count($orders) === 1 ? '/orders/' . $orders[0]->id : '/orders',
                ], count($orders) === 1 ? 'Order placed successfully!' : count($orders) . ' orders placed successfully!');
            }

            if (count($orders) === 1) {
                return redirect('/orders/' . $orders[0]->id)->with('success', 'Order placed successfully!');
            }

            return redirect('/orders')->with('success', count($orders) . ' orders placed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return $this->serverErrorResponse('Failed to place order. Please try again.');
            }
            return back()->with('error', 'Failed to place order. Please try again.');
        }
    }

    public function count()
    {
        $count = CartItem::where('user_id', Auth::id())->sum('quantity');
        return $this->successResponse(['count' => (int) $count]);
    }

    public function dropdown()
    {
        $cartItems = CartItem::where('user_id', Auth::id())
            ->with('menuItem')
            ->get();

        $items = $cartItems->map(function ($item) {
            return [
                'id' => $item->id,
                'menu_item_id' => $item->menu_item_id,
                'name' => $item->menuItem->name,
                'price' => $item->menuItem->price,
                'quantity' => $item->quantity,
                'image' => ImageHelper::menuItem($item->menuItem->image),
            ];
        });

        $total = $cartItems->sum(fn($item) => $item->menuItem->price * $item->quantity);

        return $this->successResponse([
            'count' => $cartItems->sum('quantity'),
            'total' => $total,
            'items' => $items,
        ]);
    }

    /**
     * Calculate cart summary using Factory Pattern for voucher discount
     */
    private function calculateSummary($cartItems)
    {
        $subtotal = $cartItems->sum(fn($item) => $item->menuItem->price * $item->quantity);
        $serviceFee = 2.00;
        $discount = 0;

        // Apply voucher discount using Factory Pattern
        $appliedVoucher = session('applied_voucher');
        if ($appliedVoucher) {
            $voucher = Voucher::find($appliedVoucher['id']);
            if ($voucher && VoucherFactory::isApplicable($voucher, $subtotal)) {
                $discount = VoucherFactory::calculateDiscount($voucher, $subtotal);
            }
        }

        $total = max(0, $subtotal + $serviceFee - $discount);

        return [
            'item_count' => $cartItems->sum('quantity'),
            'subtotal' => $subtotal,
            'service_fee' => $serviceFee,
            'discount' => $discount,
            'total' => $total,
            'voucher' => $appliedVoucher,
        ];
    }

}

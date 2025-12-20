<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\MenuItem;
use App\Models\UserRedeemedReward;
use App\Services\CartRateLimiterService;
use App\Services\CartDataProtectionService;
use App\Services\OutputEncodingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    private CartRateLimiterService $rateLimiter;
    private CartDataProtectionService $dataProtection;
    private OutputEncodingService $outputEncoder;

    public function __construct(
        CartRateLimiterService $rateLimiter,
        CartDataProtectionService $dataProtection,
        OutputEncodingService $outputEncoder
    ) {
        $this->rateLimiter = $rateLimiter;
        $this->dataProtection = $dataProtection;
        $this->outputEncoder = $outputEncoder;
        
        // Apply cache control headers to sensitive endpoints
        $this->middleware(function ($request, $next) {
            $response = $next($request);
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            return $response;
        });
    }

    /**
     * Get cart items
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $cartItems = CartItem::where('user_id', $user->user_id)
            ->with(['menuItem.vendor', 'menuItem.category'])
            ->get();

        // Calculate totals
        $subtotal = 0;
        $itemCount = 0;

        $items = $cartItems->map(function($cartItem) use (&$subtotal, &$itemCount) {
            if ($cartItem->menuItem) {
                $itemTotal = $cartItem->menuItem->price * $cartItem->quantity;
                $subtotal += $itemTotal;
                $itemCount += $cartItem->quantity;

                return [
                    'cart_id' => $cartItem->cart_id,
                    'quantity' => $cartItem->quantity,
                    'special_request' => $cartItem->special_request,
                    'item_total' => (float) $itemTotal,
                    'menu_item' => [
                        'item_id' => $cartItem->menuItem->item_id,
                        'name' => $cartItem->menuItem->name,
                        'description' => $cartItem->menuItem->description,
                        'price' => (float) $cartItem->menuItem->price,
                        'image_url' => $cartItem->menuItem->image_path 
                            ? asset($cartItem->menuItem->image_path) 
                            : null,
                        'is_available' => $cartItem->menuItem->is_available,
                        'category' => $cartItem->menuItem->category ? [
                            'category_id' => $cartItem->menuItem->category->category_id,
                            'category_name' => $cartItem->menuItem->category->category_name,
                        ] : null,
                        'vendor' => $cartItem->menuItem->vendor ? [
                            'vendor_id' => $cartItem->menuItem->vendor->user_id,
                            'name' => $cartItem->menuItem->vendor->name,
                        ] : null,
                    ],
                ];
            }
            return null;
        })->filter();

        $serviceFee = 2.00;
        $total = $subtotal + $serviceFee;

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items->values(),
                'summary' => [
                    'item_count' => $itemCount,
                    'subtotal' => (float) $subtotal,
                    'service_fee' => (float) $serviceFee,
                    'discount' => 0.00,
                    'total' => (float) $total,
                ]
            ]
        ]);
    }

    /**
     * Add item to cart
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = $request->user();
        
        // [94] Check rate limit
        $rateCheck = $this->rateLimiter->canAddToCart($user->user_id);
        if (!$rateCheck['allowed']) {
            return response()->json([
                'success' => false,
                'message' => $rateCheck['message'],
                'retry_after' => $rateCheck['reset_in']
            ], 429);
        }
        
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:menu_items,item_id',
            'quantity' => 'required|integer|min:1|max:10',
            'special_request' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Record rate-limited action
        $this->rateLimiter->recordAction($user->user_id, 'cart_add');
        
        $itemId = $request->item_id;
        $quantity = $request->quantity;
        $specialRequest = $request->special_request ? strip_tags($request->special_request) : null;

        // Check if item is available
        $menuItem = MenuItem::where('item_id', $itemId)
            ->where('is_available', 1)
            ->first();

        if (!$menuItem) {
            return response()->json([
                'success' => false,
                'message' => 'This item is currently unavailable'
            ], 404);
        }

        // Check if item already exists in cart
        $cartItem = CartItem::where('user_id', $user->user_id)
            ->where('item_id', $itemId)
            ->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $quantity;
            if ($newQuantity > 10) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maximum quantity per item is 10'
                ], 400);
            }

            $cartItem->quantity = $newQuantity;
            if ($specialRequest) {
                $cartItem->special_request = $specialRequest;
            }
            $cartItem->save();

            $message = 'Cart updated successfully';
        } else {
            $cartItem = CartItem::create([
                'user_id' => $user->user_id,
                'item_id' => $itemId,
                'quantity' => $quantity,
                'special_request' => $specialRequest,
            ]);

            $message = 'Item added to cart successfully';
        }

        // Get updated cart count
        $cartCount = CartItem::where('user_id', $user->user_id)->sum('quantity');
        
        // [132] Purge cart cache to ensure fresh data
        $this->dataProtection->purgeCartCache($user->user_id);

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'cart_id' => $cartItem->cart_id,
                'cart_count' => (int) $cartCount,
                'item_name' => $menuItem->name,
            ]
        ]);
    }

    /**
     * Update cart item quantity
     * 
     * @param Request $request
     * @param int $cartId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $cartId)
    {
        $user = $request->user();
        
        // [94] Check rate limit
        $rateCheck = $this->rateLimiter->canUpdateCart($user->user_id);
        if (!$rateCheck['allowed']) {
            return response()->json([
                'success' => false,
                'message' => $rateCheck['message'],
                'retry_after' => $rateCheck['reset_in']
            ], 429);
        }
        
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1|max:10',
            'special_request' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Record rate-limited action
        $this->rateLimiter->recordAction($user->user_id, 'cart_update');

        $cartItem = CartItem::where('cart_id', $cartId)
            ->where('user_id', $user->user_id)
            ->with('menuItem')
            ->first();

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found'
            ], 404);
        }

        $cartItem->quantity = $request->quantity;
        if ($request->has('special_request')) {
            $cartItem->special_request = $request->special_request ? strip_tags($request->special_request) : null;
        }
        $cartItem->save();

        // Calculate new totals
        $itemSubtotal = $cartItem->menuItem->price * $cartItem->quantity;
        $cartSummary = $this->getCartSummary($user->user_id);
        
        // [132] Purge cart cache
        $this->dataProtection->purgeCartCache($user->user_id);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully',
            'data' => [
                'item_subtotal' => (float) $itemSubtotal,
                'summary' => $cartSummary,
            ]
        ]);
    }

    /**
     * Remove item from cart
     * 
     * @param Request $request
     * @param int $cartId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $cartId)
    {
        $user = $request->user();

        $cartItem = CartItem::where('cart_id', $cartId)
            ->where('user_id', $user->user_id)
            ->first();

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found'
            ], 404);
        }

        $cartItem->delete();

        $cartSummary = $this->getCartSummary($user->user_id);
        
        // [132] Purge cart cache
        $this->dataProtection->purgeCartCache($user->user_id);

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart',
            'data' => [
                'summary' => $cartSummary,
                'cart_empty' => $cartSummary['item_count'] === 0,
            ]
        ]);
    }

    /**
     * Clear all items from cart
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clear(Request $request)
    {
        $user = $request->user();
        
        // [94] Check rate limit
        $rateCheck = $this->rateLimiter->canClearCart($user->user_id);
        if (!$rateCheck['allowed']) {
            return response()->json([
                'success' => false,
                'message' => $rateCheck['message'],
                'retry_after' => $rateCheck['reset_in']
            ], 429);
        }
        
        // Record rate-limited action
        $this->rateLimiter->recordAction($user->user_id, 'cart_clear');

        CartItem::where('user_id', $user->user_id)->delete();
        
        // [132] Purge cart cache
        $this->dataProtection->purgeCartCache($user->user_id);

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully'
        ]);
    }

    /**
     * Get cart item count
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function count(Request $request)
    {
        $user = $request->user();

        $count = CartItem::where('user_id', $user->user_id)->sum('quantity');

        return response()->json([
            'success' => true,
            'data' => [
                'count' => (int) $count
            ]
        ]);
    }

    /**
     * Apply voucher to cart
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function applyVoucher(Request $request)
    {
        $user = $request->user();
        
        // [94] Check rate limit to prevent voucher brute force
        $rateCheck = $this->rateLimiter->canApplyVoucher($user->user_id);
        if (!$rateCheck['allowed']) {
            return response()->json([
                'success' => false,
                'message' => $rateCheck['message'],
                'retry_after' => $rateCheck['reset_in']
            ], 429);
        }
        
        $validator = Validator::make($request->all(), [
            'voucher_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a voucher code'
            ], 422);
        }
        
        // Record rate-limited action
        $this->rateLimiter->recordAction($user->user_id, 'voucher_apply');
        
        $voucherCode = strtoupper(trim($request->voucher_code));

        // Find the redeemed reward
        $redeemedReward = UserRedeemedReward::where('voucher_code', $voucherCode)
            ->where('user_id', $user->user_id)
            ->where('is_used', 0)
            ->with('reward')
            ->first();

        if (!$redeemedReward) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid voucher code or already used'
            ], 400);
        }

        // Check if voucher is expired (30 days from redemption)
        $expiryDate = $redeemedReward->redeemed_at->addDays(30);
        if (now()->gt($expiryDate)) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher has expired'
            ], 400);
        }

        // Calculate cart subtotal
        $cartItems = CartItem::where('user_id', $user->user_id)
            ->with('menuItem')
            ->get();

        $subtotal = 0;
        foreach ($cartItems as $item) {
            if ($item->menuItem) {
                $subtotal += $item->menuItem->price * $item->quantity;
            }
        }

        $reward = $redeemedReward->reward;
        $discount = 0;

        // Check minimum spend for voucher type
        if ($reward->reward_type === 'voucher') {
            if ($reward->min_spend && $subtotal < $reward->min_spend) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minimum spend of RM ' . number_format($reward->min_spend, 2) . ' required for this voucher'
                ], 400);
            }
            $discount = $reward->reward_value;
        } elseif ($reward->reward_type === 'percentage') {
            $calculatedDiscount = ($subtotal * $reward->reward_value) / 100;
            if ($reward->max_discount) {
                $discount = min($calculatedDiscount, $reward->max_discount);
            } else {
                $discount = $calculatedDiscount;
            }
        }

        $serviceFee = 2.00;
        $total = $subtotal + $serviceFee - $discount;
        
        // [132] Cache voucher data securely
        $this->dataProtection->cacheVoucherData($user->user_id, [
            'voucher_code' => $voucherCode,
            'discount' => $discount,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Voucher applied successfully!',
            'data' => [
                'voucher_code' => $voucherCode,
                'reward_name' => $reward->reward_name,
                'reward_type' => $reward->reward_type,
                'discount' => (float) $discount,
                'summary' => [
                    'subtotal' => (float) $subtotal,
                    'service_fee' => (float) $serviceFee,
                    'discount' => (float) $discount,
                    'total' => (float) $total,
                ]
            ]
        ]);
    }

    /**
     * Remove voucher from cart
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeVoucher(Request $request)
    {
        $user = $request->user();

        // Get cart summary without voucher
        $cartSummary = $this->getCartSummary($user->user_id);

        return response()->json([
            'success' => true,
            'message' => 'Voucher removed',
            'data' => [
                'summary' => $cartSummary,
            ]
        ]);
    }

    /**
     * Get recommended items (items not in cart)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recommended(Request $request)
    {
        $user = $request->user();
        $limit = $request->input('limit', 4);

        $cartItemIds = CartItem::where('user_id', $user->user_id)->pluck('item_id');

        $recommendedItems = MenuItem::where('is_available', 1)
            ->whereNotIn('item_id', $cartItemIds)
            ->with(['category', 'vendor'])
            ->inRandomOrder()
            ->limit($limit)
            ->get()
            ->map(function($item) {
                return [
                    'item_id' => $item->item_id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'price' => (float) $item->price,
                    'image_url' => $item->image_path ? asset($item->image_path) : null,
                    'category' => $item->category ? [
                        'category_id' => $item->category->category_id,
                        'category_name' => $item->category->category_name,
                    ] : null,
                    'vendor' => $item->vendor ? [
                        'vendor_id' => $item->vendor->user_id,
                        'name' => $item->vendor->name,
                    ] : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $recommendedItems
        ]);
    }

    /**
     * Get cart summary
     * 
     * @param int $userId
     * @return array
     */
    private function getCartSummary($userId)
    {
        $cartItems = CartItem::where('user_id', $userId)
            ->with('menuItem')
            ->get();

        $subtotal = 0;
        $itemCount = 0;

        foreach ($cartItems as $item) {
            if ($item->menuItem) {
                $subtotal += $item->menuItem->price * $item->quantity;
                $itemCount += $item->quantity;
            }
        }

        $serviceFee = 2.00;
        $total = $subtotal + $serviceFee;

        return [
            'item_count' => $itemCount,
            'subtotal' => (float) $subtotal,
            'service_fee' => (float) $serviceFee,
            'discount' => 0.00,
            'total' => (float) $total,
        ];
    }
}

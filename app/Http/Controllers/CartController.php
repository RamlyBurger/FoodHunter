<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\MenuItem;
use App\Models\UserRedeemedReward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use App\Patterns\Strategy\CartPriceCalculator;
use App\Patterns\Strategy\RegularPricingStrategy;
use App\Patterns\Strategy\VoucherDiscountStrategy;
use App\Patterns\Strategy\BulkDiscountStrategy;

class CartController extends Controller
{
    /**
     * Display the shopping cart
     * Uses Strategy Pattern for flexible pricing calculations
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get all cart items for the authenticated user with relationships
        $cartItems = CartItem::where('user_id', $user->user_id)
            ->with(['menuItem.vendor', 'menuItem.category'])
            ->get();
        
        // Calculate totals
        $subtotal = 0;
        $itemCount = 0;
        
        foreach ($cartItems as $cartItem) {
            if ($cartItem->menuItem) {
                $subtotal += $cartItem->menuItem->price * $cartItem->quantity;
                $itemCount += $cartItem->quantity;
            }
        }
        
        // Initialize price calculator with Strategy Pattern
        $calculator = new CartPriceCalculator();
        $serviceFee = 2.00;
        $discount = 0.00;
        $appliedVoucher = null;
        $voucherError = null;
        $strategyUsed = 'Regular Pricing';
        
        // Check if voucher is applied (Use Strategy Pattern)
        if (Session::has('applied_voucher')) {
            $voucherCode = Session::get('applied_voucher');
            $redeemedReward = UserRedeemedReward::where('voucher_code', $voucherCode)
                ->where('user_id', $user->user_id)
                ->where('is_used', 0)
                ->with('reward')
                ->first();
            
            if ($redeemedReward && $redeemedReward->reward) {
                $reward = $redeemedReward->reward;
                
                // Check if voucher is still valid (30 days from redemption)
                $expiryDate = $redeemedReward->redeemed_at->addDays(30);
                if (now()->gt($expiryDate)) {
                    $voucherError = 'Voucher has expired';
                    Session::forget('applied_voucher');
                } else {
                    // Apply discount using Voucher Strategy Pattern
                    $calculator->setStrategy(new VoucherDiscountStrategy());
                    
                    $context = [
                        'service_fee' => $serviceFee,
                        'voucher_type' => $reward->reward_type === 'percentage' ? 'percentage' : 'fixed',
                        'voucher_value' => $reward->reward_value,
                    ];
                    
                    $result = $calculator->calculate($subtotal, $context);
                    $discount = $result['discount'];
                    $appliedVoucher = $redeemedReward;
                    $strategyUsed = 'Voucher Discount Strategy';
                }
            } else {
                $voucherError = 'Invalid or already used voucher';
                Session::forget('applied_voucher');
            }
        } elseif ($itemCount >= 3) {
            // Apply bulk discount strategy if 3+ items
            $calculator->setStrategy(new BulkDiscountStrategy());
            $result = $calculator->calculate($subtotal, [
                'service_fee' => $serviceFee,
                'quantity' => $itemCount
            ]);
            $discount = $result['discount'];
            $strategyUsed = 'Bulk Discount Strategy';
        } else {
            // Regular pricing strategy
            $calculator->setStrategy(new RegularPricingStrategy());
            $result = $calculator->calculate($subtotal, ['service_fee' => $serviceFee]);
            $strategyUsed = 'Regular Pricing Strategy';
        }
        
        $total = $subtotal + $serviceFee - $discount;
        
        // Get recommended items (random available items, max 4)
        $recommendedItems = MenuItem::where('is_available', 1)
            ->whereNotIn('item_id', $cartItems->pluck('item_id'))
            ->inRandomOrder()
            ->limit(4)
            ->get();
        
        return view('cart', compact(
            'cartItems',
            'subtotal',
            'serviceFee',
            'discount',
            'total',
            'itemCount',
            'recommendedItems',
            'appliedVoucher',
            'voucherError'
        ));
    }
    
    /**
     * Add item to cart
     */
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:menu_items,item_id',
            'quantity' => 'required|integer|min:1|max:10',
            'special_request' => 'nullable|string|max:500',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid input data',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user = Auth::user();
        $itemId = $request->item_id;
        $quantity = $request->quantity;
        $specialRequest = strip_tags($request->special_request);
        
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
            // Update quantity if item exists
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
            // Create new cart item
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
        
        return response()->json([
            'success' => true,
            'message' => $message,
            'cart_count' => $cartCount,
            'item_name' => $menuItem->name
        ]);
    }
    
    /**
     * Update cart item quantity
     */
    public function update(Request $request, $cartId)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1|max:10',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid quantity'
            ], 422);
        }
        
        $user = Auth::user();
        
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
        $cartItem->save();
        
        // Calculate new subtotal for this item
        $itemSubtotal = $cartItem->menuItem->price * $cartItem->quantity;
        
        // Calculate new cart totals
        $allCartItems = CartItem::where('user_id', $user->user_id)
            ->with('menuItem')
            ->get();
        
        $subtotal = 0;
        $itemCount = 0;
        foreach ($allCartItems as $item) {
            if ($item->menuItem) {
                $subtotal += $item->menuItem->price * $item->quantity;
                $itemCount += $item->quantity;
            }
        }
        
        $serviceFee = 2.00;
        $total = $subtotal + $serviceFee;
        
        return response()->json([
            'success' => true,
            'message' => 'Quantity updated',
            'item_subtotal' => number_format($itemSubtotal, 2),
            'subtotal' => number_format($subtotal, 2),
            'total' => number_format($total, 2),
            'item_count' => $itemCount
        ]);
    }
    
    /**
     * Remove item from cart
     */
    public function remove($cartId)
    {
        $user = Auth::user();
        
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
        
        // Calculate new cart totals
        $allCartItems = CartItem::where('user_id', $user->user_id)
            ->with('menuItem')
            ->get();
        
        $subtotal = 0;
        $itemCount = 0;
        foreach ($allCartItems as $item) {
            if ($item->menuItem) {
                $subtotal += $item->menuItem->price * $item->quantity;
                $itemCount += $item->quantity;
            }
        }
        
        $serviceFee = 2.00;
        $total = $subtotal + $serviceFee;
        
        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart',
            'subtotal' => number_format($subtotal, 2),
            'total' => number_format($total, 2),
            'item_count' => $itemCount,
            'cart_empty' => $itemCount === 0
        ]);
    }
    
    /**
     * Clear all items from cart
     */
    public function clear()
    {
        $user = Auth::user();
        
        CartItem::where('user_id', $user->user_id)->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully'
        ]);
    }
    
    /**
     * Get cart count (for navbar)
     */
    public function getCount()
    {
        $user = Auth::user();
        
        $count = CartItem::where('user_id', $user->user_id)->sum('quantity');
        
        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }
    
    /**
     * Apply voucher to cart
     */
    public function applyVoucher(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'voucher_code' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a voucher code'
            ], 422);
        }
        
        $voucherCode = strtoupper(trim($request->voucher_code));
        
        // Check if this voucher is already applied in current session
        if (Session::has('applied_voucher') && Session::get('applied_voucher') === $voucherCode) {
            return response()->json([
                'success' => false,
                'message' => 'This voucher is already applied to your cart'
            ]);
        }
        
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
            ]);
        }
        
        // Check if voucher is expired (30 days from redemption)
        $expiryDate = $redeemedReward->redeemed_at->addDays(30);
        if (now()->gt($expiryDate)) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher has expired'
            ]);
        }
        
        // Calculate cart totals to check minimum spend
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
        
        // Check minimum spend for voucher type
        if ($reward->reward_type === 'voucher' && $reward->min_spend) {
            if ($subtotal < $reward->min_spend) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minimum spend of RM ' . number_format($reward->min_spend, 2) . ' required for this voucher'
                ]);
            }
        }
        
        // Store voucher in session
        Session::put('applied_voucher', $voucherCode);
        
        return response()->json([
            'success' => true,
            'message' => 'Voucher applied successfully!'
        ]);
    }
    
    /**
     * Remove voucher from cart
     */
    public function removeVoucher()
    {
        Session::forget('applied_voucher');
        
        return response()->json([
            'success' => true,
            'message' => 'Voucher removed'
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Pickup;
use App\Models\CartItem;
use App\Models\LoyaltyPoint;
use App\Models\UserRedeemedReward;
use App\Models\VendorNotification;
use App\Models\VendorSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Patterns\State\OrderStateManager;
use App\Patterns\Observer\QueueSubject;
use App\Patterns\Observer\NotificationObserver;
use App\Patterns\Observer\DashboardObserver;
use App\Patterns\Observer\AnalyticsObserver;

class PaymentController extends Controller
{
    private QueueSubject $queueSubject;

    public function __construct()
    {
        // Initialize Observer Pattern for queue management
        $this->queueSubject = new QueueSubject();
        $this->queueSubject->attach(new NotificationObserver());
        $this->queueSubject->attach(new DashboardObserver());
        $this->queueSubject->attach(new AnalyticsObserver());
    }
    /**
     * Show checkout page
     */
    public function showCheckout()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get cart items grouped by vendor
        $cartItems = CartItem::with(['menuItem.vendor', 'menuItem.category'])
            ->where('user_id', $user->user_id)
            ->get();
        
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Your cart is empty');
        }
        
        // Group items by vendor
        $vendorGroups = $cartItems->groupBy(function($item) {
            return $item->menuItem->vendor_id;
        });
        
        // Calculate totals (same as cart)
        $subtotal = 0;
        foreach ($cartItems as $item) {
            if ($item->menuItem) {
                $subtotal += $item->menuItem->price * $item->quantity;
            }
        }
        
        $serviceFee = 2.00; // Fixed service fee (same as cart)
        $discount = 0.00;
        $appliedVoucher = null;
        
        // Check if voucher is applied
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
                if (now()->lte($expiryDate)) {
                    // Apply discount based on reward type
                    if ($reward->reward_type === 'voucher') {
                        // Check minimum spend
                        if (!$reward->min_spend || $subtotal >= $reward->min_spend) {
                            $discount = $reward->reward_value;
                            $appliedVoucher = $redeemedReward;
                        }
                    } elseif ($reward->reward_type === 'percentage') {
                        $calculatedDiscount = ($subtotal * $reward->reward_value) / 100;
                        if ($reward->max_discount) {
                            $discount = min($calculatedDiscount, $reward->max_discount);
                        } else {
                            $discount = $calculatedDiscount;
                        }
                        $appliedVoucher = $redeemedReward;
                    }
                }
            }
        }
        
        $total = $subtotal + $serviceFee - $discount;
        
        // Get user's loyalty points
        $loyaltyPoints = $user->loyaltyPoints;
        $pointsToEarn = floor($total); // Earn 1 point per RM1 spent
        
        return view('payment', compact(
            'cartItems',
            'vendorGroups',
            'subtotal',
            'serviceFee',
            'discount',
            'total',
            'loyaltyPoints',
            'pointsToEarn',
            'appliedVoucher'
        ));
    }
    
    /**
     * Process payment
     */
    public function processPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:online,ewallet,cash',
            'pickup_instructions' => 'nullable|string|max:500',
            'agree_terms' => 'required|accepted',
            // Online payment fields
            'card_number' => 'required_if:payment_method,online|nullable|digits:16',
            'card_name' => 'required_if:payment_method,online|nullable|string|max:100',
            'expiry_date' => 'required_if:payment_method,online|nullable|regex:/^(0[1-9]|1[0-2])\/\d{2}$/',
            'cvv' => 'required_if:payment_method,online|nullable|digits:3',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $user = Auth::user();
        $paymentMethod = $request->payment_method;
        
        try {
            DB::beginTransaction();
            
            // Get cart items grouped by vendor
            $cartItems = CartItem::with('menuItem')
                ->where('user_id', $user->user_id)
                ->get();
            
            if ($cartItems->isEmpty()) {
                return redirect()->route('cart')->with('error', 'Your cart is empty');
            }
            
            // Group items by vendor (create separate order for each vendor)
            $vendorGroups = $cartItems->groupBy(function($item) {
                return $item->menuItem->vendor_id;
            });
            
            // Calculate cart totals
            $cartSubtotal = 0;
            foreach ($cartItems as $item) {
                $cartSubtotal += $item->menuItem->price * $item->quantity;
            }
            
            $serviceFee = 2.00;
            $discount = 0.00;
            
            // Check if voucher is applied
            if (Session::has('applied_voucher')) {
                $voucherCode = Session::get('applied_voucher');
                $redeemedReward = UserRedeemedReward::where('voucher_code', $voucherCode)
                    ->where('user_id', $user->user_id)
                    ->where('is_used', 0)
                    ->with('reward')
                    ->first();
                
                if ($redeemedReward && $redeemedReward->reward) {
                    $reward = $redeemedReward->reward;
                    
                    // Apply discount based on reward type
                    if ($reward->reward_type === 'voucher') {
                        if (!$reward->min_spend || $cartSubtotal >= $reward->min_spend) {
                            $discount = $reward->reward_value;
                        }
                    } elseif ($reward->reward_type === 'percentage') {
                        $calculatedDiscount = ($cartSubtotal * $reward->reward_value) / 100;
                        if ($reward->max_discount) {
                            $discount = min($calculatedDiscount, $reward->max_discount);
                        } else {
                            $discount = $calculatedDiscount;
                        }
                    }
                }
            }
            
            $cartTotal = $cartSubtotal + $serviceFee - $discount;
            
            $allOrders = [];
            
            foreach ($vendorGroups as $vendorId => $items) {
                // Calculate order subtotal for this vendor
                $vendorSubtotal = 0;
                foreach ($items as $item) {
                    $vendorSubtotal += $item->menuItem->price * $item->quantity;
                }
                
                // Calculate proportional fees and discount for this vendor
                $proportion = $vendorSubtotal / $cartSubtotal;
                $vendorServiceFee = $serviceFee * $proportion;
                $vendorDiscount = $discount * $proportion;
                $orderTotal = $vendorSubtotal + $vendorServiceFee - $vendorDiscount;
                
                // Create order
                $order = Order::create([
                    'user_id' => $user->user_id,
                    'vendor_id' => $vendorId,
                    'total_price' => $orderTotal,
                    'status' => 'pending',
                ]);
                                // Use State Pattern to initialize order state
                $stateManager = new OrderStateManager($order);
                $stateManager->process(); // Process initial pending state
                                // Create vendor notification
                $vendorSettings = VendorSetting::where('vendor_id', $vendorId)->first();
                if ($vendorSettings && $vendorSettings->notify_new_orders) {
                    VendorNotification::create([
                        'vendor_id' => $vendorId,
                        'type' => 'new_order',
                        'title' => 'New Order #' . $order->order_id,
                        'message' => 'You have a new order from ' . $user->name . ' totaling RM ' . number_format($orderTotal, 2),
                        'order_id' => $order->order_id,
                    ]);
                }
                
                // Create order items
                foreach ($items as $cartItem) {
                    OrderItem::create([
                        'order_id' => $order->order_id,
                        'item_id' => $cartItem->item_id,
                        'quantity' => $cartItem->quantity,
                        'price' => $cartItem->menuItem->price,
                        'special_request' => $cartItem->special_request,
                    ]);
                }
                
                // Process payment based on method
                $paymentStatus = 'pending';
                $transactionRef = null;
                $paidAt = null;
                
                if ($paymentMethod === 'online') {
                    // Simulate online payment processing
                    // In production, integrate with actual payment gateway (Stripe, PayPal, etc.)
                    $transactionRef = 'TXN' . date('Ymd') . strtoupper(substr(md5(uniqid()), 0, 8));
                    
                    // Simulate payment success (in real scenario, wait for gateway callback)
                    if ($this->processOnlinePayment($request->all())) {
                        $paymentStatus = 'paid';
                        $paidAt = now();
                        $order->update(['status' => 'accepted']);
                    } else {
                        throw new \Exception('Payment failed. Please try again.');
                    }
                    
                } elseif ($paymentMethod === 'ewallet') {
                    // Simulate TNG eWallet payment
                    $transactionRef = 'TNG' . date('Ymd') . strtoupper(substr(md5(uniqid()), 0, 8));
                    
                    // In production, redirect to TNG payment page
                    // For now, simulate instant payment
                    $paymentStatus = 'paid';
                    $paidAt = now();
                    $order->update(['status' => 'accepted']);
                    
                } else {
                    // Cash on pickup - payment pending
                    $paymentStatus = 'pending';
                }
                
                // Create payment record
                Payment::create([
                    'order_id' => $order->order_id,
                    'amount' => $orderTotal,
                    'method' => $paymentMethod,
                    'status' => $paymentStatus,
                    'transaction_ref' => $transactionRef,
                    'paid_at' => $paidAt,
                ]);
                
                // Create pickup record
                $queueNumber = $this->generateQueueNumber($vendorId);
                $qrCode = 'QR-ORD' . str_pad($order->order_id, 6, '0', STR_PAD_LEFT) . '-' . date('Y');
                
                Pickup::create([
                    'order_id' => $order->order_id,
                    'queue_number' => $queueNumber,
                    'qr_code' => $qrCode,
                    'status' => 'waiting',
                ]);
                
                // Notify observers about new order in queue (Observer Pattern)
                $this->queueSubject->notify($order->fresh(), 'created');
                
                $allOrders[] = $order;
            }
            
            // Award loyalty points based on final total (after discount)
            $pointsEarned = floor($cartTotal); // 1 point per RM1 on final total
            
            if ($pointsEarned > 0) {
                $loyaltyPoints = $user->loyaltyPoints;
                if ($loyaltyPoints) {
                    $loyaltyPoints->increment('points', $pointsEarned);
                } else {
                    LoyaltyPoint::create([
                        'user_id' => $user->user_id,
                        'points' => $pointsEarned,
                    ]);
                }
            }
            
            // Mark voucher as used if applied
            if (Session::has('applied_voucher')) {
                $voucherCode = Session::get('applied_voucher');
                $redeemedReward = UserRedeemedReward::where('voucher_code', $voucherCode)
                    ->where('user_id', $user->user_id)
                    ->where('is_used', 0)
                    ->first();
                
                if ($redeemedReward) {
                    $redeemedReward->update([
                        'is_used' => 1,
                        'used_at' => now(),
                    ]);
                }
                
                // Clear voucher from session
                Session::forget('applied_voucher');
            }
            
            // Clear cart
            CartItem::where('user_id', $user->user_id)->delete();
            
            DB::commit();
            
            // Redirect to confirmation page
            return redirect()->route('order.confirmation', ['order' => $allOrders[0]->order_id])
                ->with('success', 'Order placed successfully!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }
    
    /**
     * Show order confirmation
     */
    public function showConfirmation($orderId)
    {
        $order = Order::with(['orderItems.menuItem', 'payment', 'pickup', 'vendor'])
            ->where('order_id', $orderId)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        return view('order-confirmation', compact('order'));
    }
    
    /**
     * Simulate online payment processing
     * In production, integrate with Stripe, PayPal, iPay88, etc.
     */
    private function processOnlinePayment($paymentData)
    {
        // Test card numbers that will succeed
        $testCards = ['4111111111111111', '4242424242424242', '5555555555554444'];
        
        if (in_array($paymentData['card_number'], $testCards)) {
            return true;
        }
        
        // For testing, any card starting with 4 succeeds
        return substr($paymentData['card_number'], 0, 1) === '4';
    }
    
    /**
     * Generate unique queue number for vendor
     */
    private function generateQueueNumber($vendorId)
    {
        $today = date('Y-m-d');
        $lastPickup = Pickup::whereHas('order', function($query) use ($vendorId, $today) {
            $query->where('vendor_id', $vendorId)
                  ->whereDate('created_at', $today);
        })->orderBy('queue_number', 'desc')->first();
        
        return $lastPickup ? $lastPickup->queue_number + 1 : 100;
    }
}

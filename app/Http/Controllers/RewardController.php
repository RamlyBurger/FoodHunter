<?php

namespace App\Http\Controllers;

use App\Models\Reward;
use App\Models\LoyaltyPoint;
use App\Models\UserRedeemedReward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RewardController extends Controller
{
    /**
     * Display rewards page
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get user's loyalty points
        $loyaltyPoints = LoyaltyPoint::where('user_id', $user->user_id)->first();
        $currentPoints = $loyaltyPoints ? $loyaltyPoints->points : 0;
        
        // Get available rewards
        $rewards = Reward::where('is_active', 1)
            ->orderBy('points_required', 'asc')
            ->get();
        
        // Get user's redeemed rewards
        $redeemedRewards = UserRedeemedReward::where('user_id', $user->user_id)
            ->with('reward')
            ->orderBy('redeemed_at', 'desc')
            ->get();
        
        return view('rewards', compact('currentPoints', 'rewards', 'redeemedRewards'));
    }
    
    /**
     * Redeem a reward
     */
    public function redeem(Request $request, $rewardId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get reward
        $reward = Reward::where('reward_id', $rewardId)
            ->where('is_active', 1)
            ->firstOrFail();
        
        // Get user's loyalty points
        $loyaltyPoints = LoyaltyPoint::where('user_id', $user->user_id)->first();
        
        if (!$loyaltyPoints) {
            return back()->with('error', 'You don\'t have any loyalty points yet!');
        }
        
        // Check if user has enough points
        if ($loyaltyPoints->points < $reward->points_required) {
            return back()->with('error', 'Insufficient points! You need ' . $reward->points_required . ' points.');
        }
        
        // Check stock
        if ($reward->stock !== null && $reward->stock <= 0) {
            return back()->with('error', 'This reward is out of stock!');
        }
        
        // For free_item rewards, validate that this can actually be applied
        // (This is informational - they can still redeem it for future use)
        if ($reward->reward_type === 'free_item') {
            // Note: Free item rewards are vouchers to be presented at vendor
            // They don't need items in cart to redeem
        }
        
        DB::beginTransaction();
        
        try {
            // Deduct points
            $loyaltyPoints->decrement('points', $reward->points_required);
            
            // Generate unique voucher code
            $voucherCode = 'FH-' . strtoupper(substr(md5(uniqid()), 0, 8));
            
            // Create redeemed reward record
            UserRedeemedReward::create([
                'user_id' => $user->user_id,
                'reward_id' => $reward->reward_id,
                'voucher_code' => $voucherCode,
                'is_used' => 0,
            ]);
            
            // Decrease stock if applicable
            if ($reward->stock !== null) {
                $reward->decrement('stock');
            }
            
            DB::commit();
            
            $message = 'Reward redeemed successfully! Your voucher code is: <strong>' . $voucherCode . '</strong>. ';
            
            if ($reward->reward_type === 'voucher' || $reward->reward_type === 'percentage') {
                $message .= 'Apply this code in your cart to get the discount.';
            } else {
                $message .= 'Present this code to the vendor when ordering.';
            }
            
            return back()->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to redeem reward. Please try again.');
        }
    }
}

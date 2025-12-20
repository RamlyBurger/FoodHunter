<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use App\Models\LoyaltyPoint;
use App\Models\UserRedeemedReward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RewardController extends Controller
{
    /**
     * Get all available rewards
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Get user's loyalty points
        $loyaltyPoints = LoyaltyPoint::where('user_id', $user->user_id)->first();
        $currentPoints = $loyaltyPoints ? $loyaltyPoints->points : 0;

        // Get available rewards
        $rewards = Reward::where('is_active', 1)
            ->orderBy('points_required', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'current_points' => $currentPoints,
                'rewards' => $rewards->map(function($reward) use ($currentPoints) {
                    return [
                        'reward_id' => $reward->reward_id,
                        'reward_name' => $reward->reward_name,
                        'description' => $reward->description,
                        'points_required' => $reward->points_required,
                        'reward_type' => $reward->reward_type,
                        'reward_value' => (float) $reward->reward_value,
                        'min_spend' => $reward->min_spend ? (float) $reward->min_spend : null,
                        'max_discount' => $reward->max_discount ? (float) $reward->max_discount : null,
                        'stock' => $reward->stock,
                        'is_active' => (bool) $reward->is_active,
                        'can_redeem' => $currentPoints >= $reward->points_required && ($reward->stock === null || $reward->stock > 0),
                    ];
                })
            ]
        ]);
    }

    /**
     * Get single reward details
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $reward = Reward::where('reward_id', $id)
            ->where('is_active', 1)
            ->first();

        if (!$reward) {
            return response()->json([
                'success' => false,
                'message' => 'Reward not found'
            ], 404);
        }

        // Get user's loyalty points
        $loyaltyPoints = LoyaltyPoint::where('user_id', $user->user_id)->first();
        $currentPoints = $loyaltyPoints ? $loyaltyPoints->points : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'reward_id' => $reward->reward_id,
                'reward_name' => $reward->reward_name,
                'description' => $reward->description,
                'points_required' => $reward->points_required,
                'reward_type' => $reward->reward_type,
                'reward_value' => (float) $reward->reward_value,
                'min_spend' => $reward->min_spend ? (float) $reward->min_spend : null,
                'max_discount' => $reward->max_discount ? (float) $reward->max_discount : null,
                'stock' => $reward->stock,
                'is_active' => (bool) $reward->is_active,
                'can_redeem' => $currentPoints >= $reward->points_required && ($reward->stock === null || $reward->stock > 0),
                'user_points' => $currentPoints,
                'points_needed' => max(0, $reward->points_required - $currentPoints),
            ]
        ]);
    }

    /**
     * Redeem a reward
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function redeem(Request $request, $id)
    {
        $user = $request->user();

        // Get reward
        $reward = Reward::where('reward_id', $id)
            ->where('is_active', 1)
            ->first();

        if (!$reward) {
            return response()->json([
                'success' => false,
                'message' => 'Reward not found'
            ], 404);
        }

        // Get user's loyalty points
        $loyaltyPoints = LoyaltyPoint::where('user_id', $user->user_id)->first();

        if (!$loyaltyPoints) {
            return response()->json([
                'success' => false,
                'message' => 'You don\'t have any loyalty points yet!'
            ], 400);
        }

        // Check if user has enough points
        if ($loyaltyPoints->points < $reward->points_required) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient points! You need ' . $reward->points_required . ' points.',
                'data' => [
                    'current_points' => $loyaltyPoints->points,
                    'points_required' => $reward->points_required,
                    'points_needed' => $reward->points_required - $loyaltyPoints->points,
                ]
            ], 400);
        }

        // Check stock
        if ($reward->stock !== null && $reward->stock <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'This reward is out of stock!'
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Deduct points
            $loyaltyPoints->decrement('points', $reward->points_required);

            // Generate unique voucher code
            $voucherCode = 'FH-' . strtoupper(substr(md5(uniqid()), 0, 8));

            // Create redeemed reward record
            $redeemedReward = UserRedeemedReward::create([
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

            return response()->json([
                'success' => true,
                'message' => 'Reward redeemed successfully!',
                'data' => [
                    'voucher_code' => $voucherCode,
                    'reward' => [
                        'reward_id' => $reward->reward_id,
                        'reward_name' => $reward->reward_name,
                        'reward_type' => $reward->reward_type,
                        'reward_value' => (float) $reward->reward_value,
                    ],
                    'remaining_points' => $loyaltyPoints->points - $reward->points_required,
                    'expires_in_days' => 30,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to redeem reward. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get user's redeemed rewards
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function redeemed(Request $request)
    {
        $user = $request->user();

        $redeemedRewards = UserRedeemedReward::where('user_id', $user->user_id)
            ->with('reward')
            ->orderBy('redeemed_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $redeemedRewards->map(function($redeemed) {
                $expiryDate = $redeemed->redeemed_at->addDays(30);
                $isExpired = now()->gt($expiryDate);

                return [
                    'id' => $redeemed->id,
                    'voucher_code' => $redeemed->voucher_code,
                    'is_used' => (bool) $redeemed->is_used,
                    'used_at' => $redeemed->used_at,
                    'redeemed_at' => $redeemed->redeemed_at,
                    'expires_at' => $expiryDate,
                    'is_expired' => $isExpired,
                    'is_valid' => !$redeemed->is_used && !$isExpired,
                    'reward' => $redeemed->reward ? [
                        'reward_id' => $redeemed->reward->reward_id,
                        'reward_name' => $redeemed->reward->reward_name,
                        'description' => $redeemed->reward->description,
                        'reward_type' => $redeemed->reward->reward_type,
                        'reward_value' => (float) $redeemed->reward->reward_value,
                        'min_spend' => $redeemed->reward->min_spend ? (float) $redeemed->reward->min_spend : null,
                    ] : null,
                ];
            })
        ]);
    }

    /**
     * Get user's loyalty points
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function points(Request $request)
    {
        $user = $request->user();

        $loyaltyPoints = LoyaltyPoint::where('user_id', $user->user_id)->first();
        $currentPoints = $loyaltyPoints ? $loyaltyPoints->points : 0;

        // Get unused vouchers count
        $unusedVouchers = UserRedeemedReward::where('user_id', $user->user_id)
            ->where('is_used', 0)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'current_points' => $currentPoints,
                'unused_vouchers' => $unusedVouchers,
            ]
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Wishlist;
use App\Models\LoyaltyPoint;
use App\Models\UserRedeemedReward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Get user profile
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Get statistics
        $totalOrders = Order::where('user_id', $user->user_id)->count();
        $totalSpent = Order::where('user_id', $user->user_id)
            ->where('status', 'completed')
            ->sum('total_price');
        $totalFavorites = Wishlist::where('user_id', $user->user_id)->count();

        // Get loyalty points
        $loyaltyPoints = LoyaltyPoint::where('user_id', $user->user_id)->first();
        $currentPoints = $loyaltyPoints ? $loyaltyPoints->points : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'user_id' => $user->user_id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'created_at' => $user->created_at,
                ],
                'statistics' => [
                    'total_orders' => $totalOrders,
                    'total_spent' => (float) $totalSpent,
                    'total_favorites' => $totalFavorites,
                    'loyalty_points' => $currentPoints,
                ]
            ]
        ]);
    }

    /**
     * Update profile information
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->user_id . ',user_id',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = [];
        if ($request->has('name')) {
            $updateData['name'] = $request->name;
        }
        if ($request->has('email')) {
            $updateData['email'] = $request->email;
        }
        if ($request->has('phone')) {
            $updateData['phone'] = $request->phone;
        }

        if (!empty($updateData)) {
            $user->update($updateData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'user' => [
                    'user_id' => $user->user_id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                ]
            ]
        ]);
    }

    /**
     * Update password
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully'
        ]);
    }

    /**
     * Get recent orders
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recentOrders(Request $request)
    {
        $user = $request->user();
        $limit = $request->input('limit', 5);

        $recentOrders = Order::where('user_id', $user->user_id)
            ->with(['orderItems.menuItem', 'vendor'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $recentOrders->map(function($order) {
                return [
                    'order_id' => $order->order_id,
                    'total_price' => (float) $order->total_price,
                    'status' => $order->status,
                    'created_at' => $order->created_at,
                    'vendor' => $order->vendor ? [
                        'vendor_id' => $order->vendor->user_id,
                        'name' => $order->vendor->name,
                    ] : null,
                    'items_count' => $order->orderItems->sum('quantity'),
                ];
            })
        ]);
    }

    /**
     * Get user's favorites (wishlist items)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function favorites(Request $request)
    {
        $user = $request->user();
        $limit = $request->input('limit', 6);

        $favorites = Wishlist::where('user_id', $user->user_id)
            ->with('menuItem.vendor')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $favorites->map(function($wishlist) {
                if (!$wishlist->menuItem) {
                    return null;
                }
                return [
                    'wishlist_id' => $wishlist->wishlist_id,
                    'menu_item' => [
                        'item_id' => $wishlist->menuItem->item_id,
                        'name' => $wishlist->menuItem->name,
                        'price' => (float) $wishlist->menuItem->price,
                        'image_url' => $wishlist->menuItem->image_path 
                            ? asset($wishlist->menuItem->image_path) 
                            : null,
                        'is_available' => $wishlist->menuItem->is_available,
                        'vendor' => $wishlist->menuItem->vendor ? [
                            'vendor_id' => $wishlist->menuItem->vendor->user_id,
                            'name' => $wishlist->menuItem->vendor->name,
                        ] : null,
                    ],
                ];
            })->filter()->values()
        ]);
    }

    /**
     * Get loyalty points details
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loyaltyPoints(Request $request)
    {
        $user = $request->user();

        $loyaltyPoints = LoyaltyPoint::where('user_id', $user->user_id)->first();
        $currentPoints = $loyaltyPoints ? $loyaltyPoints->points : 0;

        // Get redeemed rewards
        $redeemedRewards = UserRedeemedReward::where('user_id', $user->user_id)
            ->with('reward')
            ->orderBy('redeemed_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'current_points' => $currentPoints,
                'redeemed_rewards' => $redeemedRewards->map(function($redeemed) {
                    return [
                        'id' => $redeemed->id,
                        'voucher_code' => $redeemed->voucher_code,
                        'is_used' => (bool) $redeemed->is_used,
                        'used_at' => $redeemed->used_at,
                        'redeemed_at' => $redeemed->redeemed_at,
                        'reward' => $redeemed->reward ? [
                            'reward_id' => $redeemed->reward->reward_id,
                            'reward_name' => $redeemed->reward->reward_name,
                            'reward_type' => $redeemed->reward->reward_type,
                            'reward_value' => (float) $redeemed->reward->reward_value,
                        ] : null,
                    ];
                })
            ]
        ]);
    }

    /**
     * Delete account
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password is incorrect'
            ], 400);
        }

        // Revoke all tokens
        $user->tokens()->delete();

        // Delete user (you may want to soft delete instead)
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully'
        ]);
    }
}

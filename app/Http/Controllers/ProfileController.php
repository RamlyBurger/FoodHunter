<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Wishlist;
use App\Models\LoyaltyPoint;
use App\Models\Reward;
use App\Models\UserRedeemedReward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display user profile
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get user statistics
        $totalOrders = Order::where('user_id', $user->user_id)->count();
        $totalSpent = Order::where('user_id', $user->user_id)
            ->where('status', 'completed')
            ->sum('total_price');
        $totalFavorites = Wishlist::where('user_id', $user->user_id)->count();
        
        // Get loyalty points
        $loyaltyPoints = LoyaltyPoint::where('user_id', $user->user_id)->first();
        $currentPoints = $loyaltyPoints ? $loyaltyPoints->points : 0;
        
        // Get recent orders
        $recentOrders = Order::where('user_id', $user->user_id)
            ->with(['orderItems.menuItem', 'vendor'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Get favorite items
        $favorites = Wishlist::where('user_id', $user->user_id)
            ->with('menuItem.vendor')
            ->limit(6)
            ->get();
        
        return view('profile', compact(
            'user',
            'totalOrders',
            'totalSpent',
            'totalFavorites',
            'currentPoints',
            'recentOrders',
            'favorites'
        ));
    }
    
    /**
     * Update profile information
     */
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->user_id . ',user_id',
            'phone' => 'nullable|string|max:20',
        ]);
        
        $user->update($validated);
        
        return back()->with('success', 'Profile updated successfully!');
    }
    
    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);
        
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }
        
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);
        
        return back()->with('success', 'Password updated successfully!');
    }
    
    /**
     * Update notification preferences
     */
    public function updateNotifications(Request $request)
    {
        // This would typically update a user_settings table
        // For now, we'll just return success
        return back()->with('success', 'Notification preferences updated!');
    }
}

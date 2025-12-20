<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Category;
use App\Models\User;
use App\Models\Order;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Display the home page with dynamic data
     */
    public function index()
    {
        // Get statistics
        $totalMenuItems = MenuItem::where('is_available', 1)->count();
        $totalVendors = User::where('role', 'vendor')->count();
        $totalOrders = Order::where('status', 'completed')->count();
        
        // Get categories with item counts
        $categories = Category::withCount(['menuItems' => function($query) {
            $query->where('is_available', 1);
        }])
        ->having('menu_items_count', '>', 0)
        ->orderBy('menu_items_count', 'desc')
        ->limit(8)
        ->get();
        
        // Get featured items (newest items, limit 8)
        $featuredItems = MenuItem::where('is_available', 1)
            ->with(['vendor', 'category'])
            ->withCount(['orderItems' => function($query) {
                $query->whereHas('order', function($q) {
                    $q->where('status', 'completed');
                });
            }])
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();
        
        // Get popular items (most ordered items, limit 4)
        $popularItems = MenuItem::where('is_available', 1)
            ->with(['vendor', 'category'])
            ->withCount(['orderItems' => function($query) {
                $query->whereHas('order', function($q) {
                    $q->where('status', 'completed');
                });
            }])
            ->having('order_items_count', '>', 0)
            ->orderBy('order_items_count', 'desc')
            ->limit(4)
            ->get();
        
        // Get recent reviews/testimonials from completed orders (if available)
        // For now, we'll use static testimonials but prepare for dynamic ones
        $recentOrders = Order::where('status', 'completed')
            ->with('user')
            ->orderBy('updated_at', 'desc')
            ->limit(3)
            ->get();
        
        // Get user's wishlist item IDs
        $wishlistItemIds = [];
        if (Auth::check()) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $wishlistItemIds = $user->wishlists()->pluck('item_id')->toArray();
        }
        
        return view('home', compact(
            'totalMenuItems',
            'totalVendors',
            'totalOrders',
            'categories',
            'featuredItems',
            'popularItems',
            'recentOrders',
            'wishlistItemIds'
        ));
    }
}

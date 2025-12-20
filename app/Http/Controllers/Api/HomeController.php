<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Category;
use App\Models\User;
use App\Models\Order;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Get home page data including statistics, categories, featured and popular items
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Get statistics
        $totalMenuItems = MenuItem::where('is_available', 1)->count();
        $totalVendors = User::where('role', 'vendor')->count();
        $totalOrders = Order::where('status', 'completed')->count();

        // Get categories with item counts (top 8)
        $categories = Category::withCount(['menuItems' => function($query) {
            $query->where('is_available', 1);
        }])
        ->having('menu_items_count', '>', 0)
        ->orderBy('menu_items_count', 'desc')
        ->limit(8)
        ->get()
        ->map(function($category) {
            return [
                'category_id' => $category->category_id,
                'category_name' => $category->category_name,
                'items_count' => $category->menu_items_count,
            ];
        });

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
            ->get()
            ->map(function($item) {
                return $this->transformMenuItem($item);
            });

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
            ->get()
            ->map(function($item) {
                return $this->transformMenuItem($item);
            });

        // Get user's wishlist item IDs if authenticated
        $wishlistItemIds = [];
        if (Auth::guard('sanctum')->check()) {
            $user = Auth::guard('sanctum')->user();
            $wishlistItemIds = Wishlist::where('user_id', $user->user_id)->pluck('item_id')->toArray();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => [
                    'total_menu_items' => $totalMenuItems,
                    'total_vendors' => $totalVendors,
                    'total_orders' => $totalOrders,
                ],
                'categories' => $categories,
                'featured_items' => $featuredItems,
                'popular_items' => $popularItems,
                'wishlist_item_ids' => $wishlistItemIds,
            ]
        ]);
    }

    /**
     * Get home statistics only
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        $totalMenuItems = MenuItem::where('is_available', 1)->count();
        $totalVendors = User::where('role', 'vendor')->count();
        $totalOrders = Order::where('status', 'completed')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_menu_items' => $totalMenuItems,
                'total_vendors' => $totalVendors,
                'total_orders' => $totalOrders,
            ]
        ]);
    }

    /**
     * Transform menu item data
     * 
     * @param MenuItem $item
     * @return array
     */
    private function transformMenuItem($item)
    {
        return [
            'item_id' => $item->item_id,
            'name' => $item->name,
            'description' => $item->description,
            'price' => (float) $item->price,
            'image_url' => $item->image_path ? asset($item->image_path) : null,
            'is_available' => $item->is_available,
            'order_count' => $item->order_items_count ?? 0,
            'category' => $item->category ? [
                'category_id' => $item->category->category_id,
                'category_name' => $item->category->category_name,
            ] : null,
            'vendor' => $item->vendor ? [
                'vendor_id' => $item->vendor->user_id,
                'name' => $item->vendor->name,
            ] : null,
        ];
    }
}

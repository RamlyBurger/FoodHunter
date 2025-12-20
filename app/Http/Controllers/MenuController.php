<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Category;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\VendorSetting;
use App\Models\VendorOperatingHour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MenuController extends Controller
{
    /**
     * Display menu items with filtering and pagination
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $search = $request->input('search');
        $categoryId = $request->input('category');
        $vendorId = $request->input('vendor');
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');
        $sortBy = $request->input('sort', 'popular');
        $perPage = 12;
        
        // Base query - only available items
        $query = MenuItem::with(['category', 'vendor'])
            ->where('is_available', 1);
        
        // Apply search
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }
        
        // Apply category filter
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        
        // Apply vendor filter
        if ($vendorId) {
            $query->where('vendor_id', $vendorId);
        }
        
        // Apply price range filter
        if ($minPrice !== null) {
            $query->where('price', '>=', $minPrice);
        }
        if ($maxPrice !== null) {
            $query->where('price', '<=', $maxPrice);
        }
        
        // Apply sorting
        switch ($sortBy) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'popular':
            default:
                // Order by number of orders (popularity)
                $query->withCount(['orderItems' => function($q) {
                    $q->join('orders', 'order_items.order_id', '=', 'orders.order_id')
                      ->where('orders.status', 'completed');
                }])->orderBy('order_items_count', 'desc');
                break;
        }
        
        // Paginate results
        $menuItems = $query->paginate($perPage)->appends($request->except('page'));
        
        // Get all categories for filter
        $categories = Category::withCount('menuItems')
            ->having('menu_items_count', '>', 0)
            ->get();
        
        // Get all vendors for filter
        $vendors = User::where('role', 'vendor')
            ->whereHas('menuItems', function($q) {
                $q->where('is_available', 1);
            })
            ->get();
        
        // Get statistics
        $totalItems = MenuItem::where('is_available', 1)->count();
        
        // Get user's wishlist item IDs
        $wishlistItemIds = [];
        if (Auth::check()) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $wishlistItemIds = $user->wishlists()->pluck('item_id')->toArray();
        }
        
        return view('menu', compact(
            'menuItems',
            'categories',
            'vendors',
            'totalItems',
            'search',
            'categoryId',
            'vendorId',
            'minPrice',
            'maxPrice',
            'sortBy',
            'wishlistItemIds'
        ));
    }
    
    /**
     * Display single menu item details
     */
    public function show($id)
    {
        $menuItem = MenuItem::with(['category', 'vendor', 'orderItems'])
            ->where('item_id', $id)
            ->where('is_available', 1)
            ->firstOrFail();
        
        // Calculate average rating and review count (placeholder for now)
        // You can add a reviews table later
        $averageRating = 4.5;
        $reviewCount = 0;
        
        // Calculate total orders for this item
        $totalOrders = $menuItem->orderItems()
            ->join('orders', 'order_items.order_id', '=', 'orders.order_id')
            ->where('orders.status', 'completed')
            ->sum('order_items.quantity');
        
        // Get related items (same category, different items)
        $relatedItems = MenuItem::where('category_id', $menuItem->category_id)
            ->where('item_id', '!=', $id)
            ->where('is_available', 1)
            ->limit(4)
            ->get();
        
        // Get vendor settings and operating hours
        $vendorSettings = VendorSetting::where('vendor_id', $menuItem->vendor_id)->first();
        $operatingHours = VendorOperatingHour::where('vendor_id', $menuItem->vendor_id)
            ->orderByRaw("FIELD(day, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
            ->get();
        
        return view('food-details', compact(
            'menuItem',
            'averageRating',
            'reviewCount',
            'totalOrders',
            'relatedItems',
            'vendorSettings',
            'operatingHours'
        ));
    }
    
    /**
     * Display vendor store page
     */
    public function showVendorStore($vendorId)
    {
        $vendor = User::where('user_id', $vendorId)
            ->where('role', 'vendor')
            ->firstOrFail();
        
        // Get vendor settings
        $vendorSettings = VendorSetting::where('vendor_id', $vendorId)->first();
        
        // Get operating hours
        $operatingHours = VendorOperatingHour::where('vendor_id', $vendorId)
            ->orderByRaw("FIELD(day, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
            ->get();
        
        // Get all menu items from this vendor
        $menuItems = MenuItem::where('vendor_id', $vendorId)
            ->where('is_available', 1)
            ->with('category')
            ->paginate(12);
        
        // Get categories for this vendor
        $categories = Category::whereHas('menuItems', function($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId)
              ->where('is_available', 1);
        })->get();
        
        // Get statistics
        $totalItems = MenuItem::where('vendor_id', $vendorId)
            ->where('is_available', 1)
            ->count();
        
        $totalOrders = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.order_id')
            ->join('menu_items', 'order_items.item_id', '=', 'menu_items.item_id')
            ->where('menu_items.vendor_id', $vendorId)
            ->where('orders.status', 'completed')
            ->count();
        
        return view('vendor-store', compact(
            'vendor',
            'vendorSettings',
            'operatingHours',
            'menuItems',
            'categories',
            'totalItems',
            'totalOrders'
        ));
    }
}

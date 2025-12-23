<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Vendor;
use App\Models\Wishlist;
use App\Patterns\Repository\MenuItemRepositoryInterface;
use App\Patterns\Repository\EloquentMenuItemRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Menu Web Controller - Student 2
 * 
 * Uses Repository Pattern to abstract database access for menu items.
 */
class MenuController extends Controller
{
    private MenuItemRepositoryInterface $menuRepository;

    public function __construct()
    {
        $this->menuRepository = new EloquentMenuItemRepository();
    }

    public function index(Request $request)
    {
        $categories = Category::active()->orderBy('name')->get();
        $vendors = Vendor::active()->orderBy('store_name')->get();

        $query = MenuItem::with(['vendor', 'category']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('vendor')) {
            $query->where('vendor_id', $request->vendor);
        }

        // Only available items by default
        $query->where('is_available', true);

        // Apply sorting
        $sort = $request->get('sort', 'popular');
        switch ($sort) {
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
                $query->orderBy('total_sold', 'desc')->orderBy('name', 'asc');
                break;
        }

        $items = $query->paginate(12);

        // Get user's wishlist item IDs
        $wishlistIds = [];
        if (Auth::check()) {
            $wishlistIds = Wishlist::where('user_id', Auth::id())->pluck('menu_item_id')->toArray();
        }

        return view('menu.index', compact('categories', 'vendors', 'items', 'wishlistIds'));
    }

    /**
     * Show single menu item using Repository Pattern
     */
    public function show(MenuItem $item)
    {
        // Using Repository Pattern - findById() method
        $menuItem = $this->menuRepository->findById($item->id);
        
        if (!$menuItem) {
            abort(404);
        }
        
        $wishlistIds = [];
        if (Auth::check()) {
            $wishlistIds = Wishlist::where('user_id', Auth::id())->pluck('menu_item_id')->toArray();
        }
        
        return view('menu.show', ['item' => $menuItem, 'wishlistIds' => $wishlistIds]);
    }

    /**
     * Show vendor menu using Repository Pattern
     */
    public function vendor(Vendor $vendor)
    {
        // Using Repository Pattern - getByVendor() method
        $allItems = $this->menuRepository->getByVendor($vendor->id);
        
        // Filter available items and paginate manually
        $availableItems = $allItems->filter(fn($item) => $item->is_available);
        $items = MenuItem::where('vendor_id', $vendor->id)
            ->where('is_available', true)
            ->with('category')
            ->orderBy('name')
            ->paginate(12);

        // Get top selling items from repository data
        $topItems = $availableItems
            ->filter(fn($item) => $item->total_sold > 0)
            ->sortByDesc('total_sold')
            ->take(3);

        $wishlistIds = [];
        if (Auth::check()) {
            $wishlistIds = Wishlist::where('user_id', Auth::id())->pluck('menu_item_id')->toArray();
        }

        return view('menu.vendor', compact('vendor', 'items', 'topItems', 'wishlistIds'));
    }
}

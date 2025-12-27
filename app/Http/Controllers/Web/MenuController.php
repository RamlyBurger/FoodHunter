<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Helpers\ImageHelper;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Vendor;
use App\Models\Wishlist;
use App\Patterns\Repository\MenuItemRepositoryInterface;
use App\Patterns\Repository\EloquentMenuItemRepository;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Menu Web Controller - Haerine Deepak Singh
 * 
 * Uses Repository Pattern to abstract database access for menu items.
 */
class MenuController extends Controller
{
    use ApiResponse;
    
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

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return $this->successResponse([
                'items' => $items->map(function ($item) use ($wishlistIds) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'description' => $item->description,
                        'price' => (float) $item->price,
                        'image' => ImageHelper::menuItem($item->image),
                        'is_available' => $item->is_available,
                        'total_sold' => $item->total_sold,
                        'vendor' => [
                            'id' => $item->vendor->id,
                            'store_name' => $item->vendor->store_name,
                            'is_open' => $item->vendor->is_open,
                            'logo' => ImageHelper::vendorLogo($item->vendor->logo ?? null, $item->vendor->store_name),
                        ],
                        'category' => $item->category ? [
                            'id' => $item->category->id,
                            'name' => $item->category->name,
                        ] : null,
                        'in_wishlist' => in_array($item->id, $wishlistIds),
                    ];
                }),
                'pagination' => [
                    'current_page' => $items->currentPage(),
                    'last_page' => $items->lastPage(),
                    'per_page' => $items->perPage(),
                    'total' => $items->total(),
                    'from' => $items->firstItem(),
                    'to' => $items->lastItem(),
                ],
                'filters' => [
                    'search' => $request->search,
                    'category' => $request->category,
                    'vendor' => $request->vendor,
                    'sort' => $sort,
                ],
            ]);
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

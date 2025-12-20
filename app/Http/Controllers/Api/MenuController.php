<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Category;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\VendorSetting;
use App\Models\VendorOperatingHour;
use App\Services\CartRateLimiterService;
use App\Services\CartDataProtectionService;
use App\Services\OutputEncodingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MenuController extends Controller
{
    private CartRateLimiterService $rateLimiter;
    private CartDataProtectionService $dataProtection;
    private OutputEncodingService $outputEncoder;

    public function __construct(
        CartRateLimiterService $rateLimiter,
        CartDataProtectionService $dataProtection,
        OutputEncodingService $outputEncoder
    ) {
        $this->rateLimiter = $rateLimiter;
        $this->dataProtection = $dataProtection;
        $this->outputEncoder = $outputEncoder;
        
        // Apply cache control headers for menu pages
        $this->middleware(function ($request, $next) {
            $response = $next($request);
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            return $response;
        });
    }

    /**
     * Get all menu items with filtering and pagination
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // [94] Rate limit menu browsing (uses IP for guests, user_id for authenticated)
        $identifier = Auth::guard('sanctum')->check() 
            ? Auth::guard('sanctum')->id() 
            : $request->ip();
        
        $rateCheck = $this->rateLimiter->canBrowseMenu($identifier);
        if (!$rateCheck['allowed']) {
            return response()->json([
                'success' => false,
                'message' => $rateCheck['message'],
                'retry_after' => $rateCheck['reset_in']
            ], 429);
        }
        
        // Record action
        if (Auth::guard('sanctum')->check()) {
            $this->rateLimiter->recordAction(Auth::guard('sanctum')->id(), 'menu_browse');
        }
        
        $search = $request->input('search');
        $categoryId = $request->input('category_id');
        $vendorId = $request->input('vendor_id');
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');
        $sortBy = $request->input('sort', 'popular');
        $perPage = $request->input('per_page', 12);

        $query = MenuItem::with(['category', 'vendor'])
            ->where('is_available', 1);

        // Apply search
        if ($search) {
            // [94] Rate limit search queries
            $rateCheck = $this->rateLimiter->canSearchMenu($identifier);
            if (!$rateCheck['allowed']) {
                return response()->json([
                    'success' => false,
                    'message' => $rateCheck['message'],
                    'retry_after' => $rateCheck['reset_in']
                ], 429);
            }
            
            // Record search action
            if (Auth::guard('sanctum')->check()) {
                $this->rateLimiter->recordAction(Auth::guard('sanctum')->id(), 'menu_search');
            }
            
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
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'popular':
            default:
                $query->withCount(['orderItems' => function($q) {
                    $q->join('orders', 'order_items.order_id', '=', 'orders.order_id')
                      ->where('orders.status', 'completed');
                }])->orderBy('order_items_count', 'desc');
                break;
        }

        $menuItems = $query->paginate($perPage);

        // Transform data
        $items = $menuItems->getCollection()->map(function($item) {
            return $this->transformMenuItem($item);
        });

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'pagination' => [
                    'current_page' => $menuItems->currentPage(),
                    'last_page' => $menuItems->lastPage(),
                    'per_page' => $menuItems->perPage(),
                    'total' => $menuItems->total(),
                ]
            ]
        ]);
    }

    /**
     * Get single menu item details
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $menuItem = MenuItem::with(['category', 'vendor', 'orderItems'])
            ->where('item_id', $id)
            ->first();

        if (!$menuItem) {
            return response()->json([
                'success' => false,
                'message' => 'Menu item not found'
            ], 404);
        }

        // Calculate total orders
        $totalOrders = $menuItem->orderItems()
            ->join('orders', 'order_items.order_id', '=', 'orders.order_id')
            ->where('orders.status', 'completed')
            ->sum('order_items.quantity');

        // Get related items
        $relatedItems = MenuItem::where('category_id', $menuItem->category_id)
            ->where('item_id', '!=', $id)
            ->where('is_available', 1)
            ->limit(4)
            ->get()
            ->map(function($item) {
                return $this->transformMenuItem($item);
            });

        // Get vendor settings and operating hours
        $vendorSettings = VendorSetting::where('vendor_id', $menuItem->vendor_id)->first();
        $operatingHours = VendorOperatingHour::where('vendor_id', $menuItem->vendor_id)
            ->orderByRaw("FIELD(day, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
            ->get();

        // Check if in wishlist
        $inWishlist = false;
        if (Auth::guard('sanctum')->check()) {
            $inWishlist = Wishlist::where('user_id', Auth::guard('sanctum')->id())
                ->where('item_id', $id)
                ->exists();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'item' => $this->transformMenuItem($menuItem, true),
                'total_orders' => $totalOrders,
                'in_wishlist' => $inWishlist,
                'related_items' => $relatedItems,
                'vendor' => [
                    'vendor_id' => $menuItem->vendor_id,
                    'name' => $menuItem->vendor->name,
                    'email' => $menuItem->vendor->email,
                    'store_name' => $vendorSettings->store_name ?? $menuItem->vendor->name,
                    'phone' => $vendorSettings->phone ?? null,
                    'description' => $vendorSettings->description ?? null,
                    'logo_url' => $vendorSettings && $vendorSettings->logo_path 
                        ? asset('storage/' . $vendorSettings->logo_path) 
                        : null,
                    'accepting_orders' => $vendorSettings ? $vendorSettings->accepting_orders : false,
                ],
                'operating_hours' => $operatingHours->map(function($hour) {
                    return [
                        'day' => $hour->day,
                        'is_open' => $hour->is_open,
                        'opening_time' => $hour->opening_time,
                        'closing_time' => $hour->closing_time,
                    ];
                })
            ]
        ]);
    }

    /**
     * Get all categories
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function categories()
    {
        $categories = Category::withCount(['menuItems' => function($q) {
            $q->where('is_available', 1);
        }])->get();

        return response()->json([
            'success' => true,
            'data' => $categories->map(function($category) {
                return [
                    'category_id' => $category->category_id,
                    'category_name' => $category->category_name,
                    'items_count' => $category->menu_items_count,
                ];
            })
        ]);
    }

    /**
     * Get all vendors
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function vendors()
    {
        $vendors = User::where('role', 'vendor')
            ->whereHas('menuItems', function($q) {
                $q->where('is_available', 1);
            })
            ->withCount(['menuItems' => function($q) {
                $q->where('is_available', 1);
            }])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $vendors->map(function($vendor) {
                $settings = VendorSetting::where('vendor_id', $vendor->user_id)->first();
                return [
                    'vendor_id' => $vendor->user_id,
                    'name' => $vendor->name,
                    'store_name' => $settings->store_name ?? $vendor->name,
                    'logo_url' => $settings && $settings->logo_path 
                        ? asset('storage/' . $settings->logo_path) 
                        : null,
                    'accepting_orders' => $settings ? $settings->accepting_orders : false,
                    'items_count' => $vendor->menu_items_count,
                ];
            })
        ]);
    }

    /**
     * Get vendor store details
     * 
     * @param int $vendorId
     * @return \Illuminate\Http\JsonResponse
     */
    public function vendorStore($vendorId)
    {
        $vendor = User::where('user_id', $vendorId)
            ->where('role', 'vendor')
            ->first();

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found'
            ], 404);
        }

        $vendorSettings = VendorSetting::where('vendor_id', $vendorId)->first();
        $operatingHours = VendorOperatingHour::where('vendor_id', $vendorId)
            ->orderByRaw("FIELD(day, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
            ->get();

        $menuItems = MenuItem::where('vendor_id', $vendorId)
            ->where('is_available', 1)
            ->with('category')
            ->paginate(12);

        $categories = Category::whereHas('menuItems', function($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId)
              ->where('is_available', 1);
        })->get();

        $totalItems = MenuItem::where('vendor_id', $vendorId)
            ->where('is_available', 1)
            ->count();

        $totalOrders = \App\Models\Order::where('vendor_id', $vendorId)
            ->where('status', 'completed')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'vendor' => [
                    'vendor_id' => $vendor->user_id,
                    'name' => $vendor->name,
                    'email' => $vendor->email,
                    'store_name' => $vendorSettings->store_name ?? $vendor->name,
                    'phone' => $vendorSettings->phone ?? null,
                    'description' => $vendorSettings->description ?? null,
                    'logo_url' => $vendorSettings && $vendorSettings->logo_path 
                        ? asset('storage/' . $vendorSettings->logo_path) 
                        : null,
                    'accepting_orders' => $vendorSettings ? $vendorSettings->accepting_orders : false,
                    'payment_methods' => $vendorSettings ? explode(',', $vendorSettings->payment_methods) : ['cash'],
                ],
                'statistics' => [
                    'total_items' => $totalItems,
                    'total_orders' => $totalOrders,
                ],
                'operating_hours' => $operatingHours->map(function($hour) {
                    return [
                        'day' => $hour->day,
                        'is_open' => $hour->is_open,
                        'opening_time' => $hour->opening_time,
                        'closing_time' => $hour->closing_time,
                    ];
                }),
                'categories' => $categories->map(function($category) {
                    return [
                        'category_id' => $category->category_id,
                        'category_name' => $category->category_name,
                    ];
                }),
                'menu_items' => $menuItems->getCollection()->map(function($item) {
                    return $this->transformMenuItem($item);
                }),
                'pagination' => [
                    'current_page' => $menuItems->currentPage(),
                    'last_page' => $menuItems->lastPage(),
                    'per_page' => $menuItems->perPage(),
                    'total' => $menuItems->total(),
                ]
            ]
        ]);
    }

    /**
     * Search menu items
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Search query must be at least 2 characters'
            ], 422);
        }

        $items = MenuItem::with(['category', 'vendor'])
            ->where('is_available', 1)
            ->where(function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%");
            })
            ->limit(10)
            ->get()
            ->map(function($item) {
                return $this->transformMenuItem($item);
            });

        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    }

    /**
     * Transform menu item data
     * 
     * @param MenuItem $item
     * @param bool $detailed
     * @return array
     */
    private function transformMenuItem($item, $detailed = false)
    {
        $data = [
            'item_id' => $item->item_id,
            'name' => $item->name,
            'description' => $item->description,
            'price' => (float) $item->price,
            'image_url' => $item->image_path ? asset($item->image_path) : null,
            'is_available' => $item->is_available,
            'category' => $item->category ? [
                'category_id' => $item->category->category_id,
                'category_name' => $item->category->category_name,
            ] : null,
            'vendor' => $item->vendor ? [
                'vendor_id' => $item->vendor->user_id,
                'name' => $item->vendor->name,
            ] : null,
            'created_at' => $item->created_at,
        ];

        if ($detailed) {
            $data['updated_at'] = $item->updated_at;
        }

        return $data;
    }

    /**
     * Get featured items (newest items)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function featured(Request $request)
    {
        $limit = $request->input('limit', 8);

        $featuredItems = MenuItem::where('is_available', 1)
            ->with(['vendor', 'category'])
            ->withCount(['orderItems' => function($query) {
                $query->whereHas('order', function($q) {
                    $q->where('status', 'completed');
                });
            }])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($item) {
                return $this->transformMenuItem($item);
            });

        return response()->json([
            'success' => true,
            'data' => $featuredItems
        ]);
    }

    /**
     * Get popular items (most ordered)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function popular(Request $request)
    {
        $limit = $request->input('limit', 8);

        $popularItems = MenuItem::where('is_available', 1)
            ->with(['vendor', 'category'])
            ->withCount(['orderItems' => function($query) {
                $query->whereHas('order', function($q) {
                    $q->where('status', 'completed');
                });
            }])
            ->having('order_items_count', '>', 0)
            ->orderBy('order_items_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($item) {
                return $this->transformMenuItem($item);
            });

        return response()->json([
            'success' => true,
            'data' => $popularItems
        ]);
    }

    /**
     * Get related items for a menu item
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function related(Request $request, $id)
    {
        $limit = $request->input('limit', 4);

        $menuItem = MenuItem::find($id);

        if (!$menuItem) {
            return response()->json([
                'success' => false,
                'message' => 'Menu item not found'
            ], 404);
        }

        $relatedItems = MenuItem::where('category_id', $menuItem->category_id)
            ->where('item_id', '!=', $id)
            ->where('is_available', 1)
            ->with(['category', 'vendor'])
            ->limit($limit)
            ->get()
            ->map(function($item) {
                return $this->transformMenuItem($item);
            });

        return response()->json([
            'success' => true,
            'data' => $relatedItems
        ]);
    }
}

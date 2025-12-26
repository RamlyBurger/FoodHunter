<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ImageHelper;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Vendor;
use App\Patterns\Repository\MenuItemRepositoryInterface;
use App\Patterns\Repository\EloquentMenuItemRepository;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Menu API Controller - Student 2
 * 
 * Uses Repository Pattern to abstract database access for menu items.
 * The repository encapsulates all data access logic, making the controller
 * cleaner and more maintainable.
 */
class MenuController extends Controller
{
    use ApiResponse;

    private MenuItemRepositoryInterface $menuRepository;

    public function __construct()
    {
        $this->menuRepository = new EloquentMenuItemRepository();
    }

    public function categories(): JsonResponse
    {
        $categories = Category::active()
            ->orderBy('sort_order')
            ->get(['id', 'name', 'slug', 'description', 'image']);

        return $this->successResponse($categories);
    }

    public function vendors(Request $request): JsonResponse
    {
        $query = Vendor::active()->open()->with('user:id,name');

        if ($request->has('category_id')) {
            $query->whereHas('menuItems', function ($q) use ($request) {
                $q->where('category_id', $request->category_id)->available();
            });
        }

        $vendors = $query->get()->map(fn($vendor) => [
            'id' => $vendor->id,
            'store_name' => $vendor->store_name,
            'slug' => $vendor->slug,
            'description' => $vendor->description,
            'logo' => ImageHelper::vendorLogo($vendor->logo, $vendor->store_name),
            'avg_prep_time' => $vendor->avg_prep_time,
            'is_open' => $vendor->isCurrentlyOpen(),
        ]);

        return $this->successResponse($vendors);
    }

    public function vendorMenu(Vendor $vendor): JsonResponse
    {
        $menuItems = $vendor->menuItems()
            ->available()
            ->with('category:id,name,slug')
            ->get()
            ->map(fn($item) => $this->formatMenuItem($item));

        $grouped = $menuItems->groupBy('category.name');

        return $this->successResponse([
            'vendor' => [
                'id' => $vendor->id,
                'store_name' => $vendor->store_name,
                'description' => $vendor->description,
                'logo' => ImageHelper::vendorLogo($vendor->logo, $vendor->store_name),
                'is_open' => $vendor->isCurrentlyOpen(),
            ],
            'menu' => $grouped,
        ]);
    }

    /**
     * Get featured menu items using Repository Pattern
     */
    public function featured(): JsonResponse
    {
        // Using Repository Pattern - getFeatured() method
        $items = $this->menuRepository->getFeatured(10)
            ->map(fn($item) => $this->formatMenuItem($item));

        return $this->successResponse($items);
    }

    /**
     * Search menu items using Repository Pattern
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return $this->errorResponse('Search query must be at least 2 characters', 400, 'INVALID_QUERY');
        }

        // Using Repository Pattern - search() method with sanitized input
        $sanitizedQuery = trim(strip_tags($query));
        $items = $this->menuRepository->search($sanitizedQuery)
            ->map(fn($item) => $this->formatMenuItem($item));

        return $this->successResponse($items);
    }

    /**
     * Get single menu item using Repository Pattern
     */
    public function show(MenuItem $menuItem): JsonResponse
    {
        // Using Repository Pattern - findById() method
        $item = $this->menuRepository->findById($menuItem->id);
        
        if (!$item) {
            return $this->errorResponse('Menu item not found', 404, 'NOT_FOUND');
        }

        return $this->successResponse($this->formatMenuItem($item, true));
    }

    /**
     * Web Service: Expose - Check Item Availability
     * Student 3 (Cart) consumes this to validate items before checkout
     * Uses Repository Pattern for data access
     */
    public function checkAvailability(MenuItem $menuItem): JsonResponse
    {
        // Using Repository Pattern - findById() for consistent data access
        $item = $this->menuRepository->findById($menuItem->id);
        
        if (!$item) {
            return $this->errorResponse('Menu item not found', 404, 'NOT_FOUND');
        }

        // Security: Output encoding (XSS protection)
        return $this->successResponse([
            'item_id' => $item->id,
            'name' => htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8'),
            'available' => $item->is_available,
            'is_available' => $item->is_available,
            'price' => (float) $item->price,
        ]);
    }

    /**
     * Get related/similar menu items
     * URL: /api/menu/{menuItem}/related
     */
    public function related(MenuItem $menuItem): JsonResponse
    {
        $relatedItems = MenuItem::where('id', '!=', $menuItem->id)
            ->where('is_available', true)
            ->where(function($query) use ($menuItem) {
                $query->where('category_id', $menuItem->category_id)
                      ->orWhere('vendor_id', $menuItem->vendor_id);
            })
            ->with(['category:id,name', 'vendor:id,store_name'])
            ->limit(6)
            ->get();

        return $this->successResponse(
            $relatedItems->map(fn($item) => $this->formatMenuItem($item))
        );
    }

    /**
     * Web Service: Expose - Popular Items API
     * Other modules (Order, Cart) consume this for recommendations
     * Uses Repository Pattern for data access
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function popularItems(Request $request): JsonResponse
    {
        $categoryId = $request->get('category_id');
        $vendorId = $request->get('vendor_id');
        $limit = min((int) $request->get('limit', 10), 20);
        
        $query = MenuItem::where('is_available', true)
            ->with(['category:id,name', 'vendor:id,store_name']);
        
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        
        if ($vendorId) {
            $query->where('vendor_id', $vendorId);
        }
        
        $items = $query->orderBy('total_sold', 'desc')
            ->limit($limit)
            ->get();
        
        return $this->successResponse([
            'items' => $items->map(fn($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'price' => (float) $item->price,
                'total_sold' => $item->total_sold,
                'image' => ImageHelper::menuItem($item->image),
                'category' => $item->category ? ['id' => $item->category->id, 'name' => $item->category->name] : null,
                'vendor' => $item->vendor ? ['id' => $item->vendor->id, 'store_name' => $item->vendor->store_name] : null,
            ]),
            'total' => $items->count(),
        ]);
    }

    private function formatMenuItem(MenuItem $item, bool $detailed = false): array
    {
        $data = [
            'id' => $item->id,
            'name' => $item->name,
            'slug' => $item->slug,
            'price' => (float) $item->price,
            'original_price' => $item->original_price ? (float) $item->original_price : null,
            'image' => ImageHelper::menuItem($item->image),
            'is_available' => $item->is_available,
            'is_featured' => $item->is_featured,
            'has_discount' => $item->hasDiscount(),
            'discount_percentage' => $item->getDiscountPercentage(),
            'category' => $item->category ? [
                'id' => $item->category->id,
                'name' => $item->category->name,
            ] : null,
            'vendor' => $item->vendor ? [
                'id' => $item->vendor->id,
                'store_name' => $item->vendor->store_name,
                'logo' => $item->vendor->logo ? ImageHelper::vendorLogo($item->vendor->logo, $item->vendor->store_name) : null,
            ] : null,
        ];

        if ($detailed) {
            $data['description'] = $item->description;
            $data['prep_time'] = $item->prep_time;
            $data['calories'] = $item->calories;
        }

        return $data;
    }

    /**
     * Web Service: Expose - Vendor Availability API
     * Student 5 (Vendor Management) exposes this
     * Used by Cart, Order, Menu modules to check vendor open/closed status
     * 
     * @param Vendor $vendor
     * @return JsonResponse
     */
    public function vendorAvailability(Vendor $vendor): JsonResponse
    {
        // Get today's operating hours
        $dayOfWeek = now()->dayOfWeek;
        $todayHours = $vendor->operatingHours()
            ->where('day_of_week', $dayOfWeek)
            ->first();

        $todayHoursData = null;
        $closedReason = null;

        if ($todayHours) {
            $todayHoursData = [
                'day' => now()->format('l'),
                'open_time' => $todayHours->is_closed ? null : $todayHours->open_time,
                'close_time' => $todayHours->is_closed ? null : $todayHours->close_time,
                'is_closed' => $todayHours->is_closed,
            ];

            if ($todayHours->is_closed) {
                $closedReason = 'Closed today';
            }
        }

        $isCurrentlyOpen = $vendor->isCurrentlyOpen();
        
        if (!$isCurrentlyOpen && !$closedReason) {
            if (!$vendor->is_open) {
                $closedReason = 'Vendor is currently closed';
            } elseif (!$vendor->is_active) {
                $closedReason = 'Vendor is inactive';
            } else {
                $closedReason = 'Outside operating hours';
            }
        }

        $response = [
            'vendor_id' => $vendor->id,
            'store_name' => $vendor->store_name,
            'is_open' => $vendor->is_open,
            'is_currently_open' => $isCurrentlyOpen,
            'today_hours' => $todayHoursData,
            'avg_prep_time' => $vendor->avg_prep_time,
            'min_order_amount' => $vendor->min_order_amount ? (float) $vendor->min_order_amount : null,
        ];

        if (!$isCurrentlyOpen && $closedReason) {
            $response['closed_reason'] = $closedReason;
        }

        return $this->successResponse($response, 'Vendor availability retrieved');
    }
}

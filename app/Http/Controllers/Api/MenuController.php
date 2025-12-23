<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
            'logo' => $vendor->logo,
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
                'logo' => $vendor->logo,
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

    private function formatMenuItem(MenuItem $item, bool $detailed = false): array
    {
        $data = [
            'id' => $item->id,
            'name' => $item->name,
            'slug' => $item->slug,
            'price' => (float) $item->price,
            'original_price' => $item->original_price ? (float) $item->original_price : null,
            'image' => $item->image,
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
            ] : null,
        ];

        if ($detailed) {
            $data['description'] = $item->description;
            $data['prep_time'] = $item->prep_time;
            $data['calories'] = $item->calories;
        }

        return $data;
    }
}

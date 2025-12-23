<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MenuItem;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $vendor = $request->user()->vendor;

        $items = MenuItem::where('vendor_id', $vendor->id)
            ->with('category:id,name')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'price' => (float) $item->price,
                'image' => $item->image,
                'is_available' => $item->is_available,
                'is_featured' => $item->is_featured,
                'total_sold' => $item->total_sold,
                'category' => $item->category?->name,
            ]);

        return $this->successResponse($items);
    }

    public function store(Request $request): JsonResponse
    {
        $vendor = $request->user()->vendor;

        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999.99'],
            'original_price' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable', 'string'],
            'is_available' => ['boolean'],
            'is_featured' => ['boolean'],
            'prep_time' => ['nullable', 'integer', 'min:1'],
            'calories' => ['nullable', 'integer', 'min:0'],
        ]);

        $item = MenuItem::create([
            'vendor_id' => $vendor->id,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . uniqid(),
            'description' => $request->description,
            'price' => $request->price,
            'original_price' => $request->original_price,
            'image' => $request->image,
            'is_available' => $request->is_available ?? true,
            'is_featured' => $request->is_featured ?? false,
            'prep_time' => $request->prep_time,
            'calories' => $request->calories,
        ]);

        return $this->createdResponse($item, 'Menu item created');
    }

    public function update(Request $request, MenuItem $menuItem): JsonResponse
    {
        $vendor = $request->user()->vendor;

        if ($menuItem->vendor_id !== $vendor->id) {
            return $this->notFoundResponse('Item not found');
        }

        $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'category_id' => ['sometimes', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:500'],
            'price' => ['sometimes', 'numeric', 'min:0', 'max:9999.99'],
            'original_price' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable', 'string'],
            'is_available' => ['boolean'],
            'is_featured' => ['boolean'],
            'prep_time' => ['nullable', 'integer', 'min:1'],
            'calories' => ['nullable', 'integer', 'min:0'],
        ]);

        $menuItem->update($request->only([
            'name', 'category_id', 'description', 'price', 'original_price',
            'image', 'is_available', 'is_featured', 'prep_time', 'calories'
        ]));

        return $this->successResponse($menuItem, 'Menu item updated');
    }

    public function destroy(Request $request, MenuItem $menuItem): JsonResponse
    {
        $vendor = $request->user()->vendor;

        if ($menuItem->vendor_id !== $vendor->id) {
            return $this->notFoundResponse('Item not found');
        }

        $menuItem->delete();

        return $this->successResponse(null, 'Menu item deleted');
    }

    public function toggleAvailability(Request $request, MenuItem $menuItem): JsonResponse
    {
        $vendor = $request->user()->vendor;

        if ($menuItem->vendor_id !== $vendor->id) {
            return $this->notFoundResponse('Item not found');
        }

        $menuItem->update(['is_available' => !$menuItem->is_available]);

        return $this->successResponse(
            ['is_available' => $menuItem->is_available],
            $menuItem->is_available ? 'Item is now available' : 'Item is now unavailable'
        );
    }

    public function categories(): JsonResponse
    {
        $categories = Category::active()
            ->orderBy('sort_order')
            ->get(['id', 'name']);

        return $this->successResponse($categories);
    }

    /**
     * Get single menu item by ID
     * URL: /api/vendor/menu/{id}
     */
    public function show(Request $request, MenuItem $menuItem): JsonResponse
    {
        $vendor = $request->user()->vendor;

        if ($menuItem->vendor_id !== $vendor->id) {
            return $this->notFoundResponse('Item not found');
        }

        $menuItem->load('category:id,name');

        return $this->successResponse([
            'id' => $menuItem->id,
            'name' => $menuItem->name,
            'slug' => $menuItem->slug,
            'description' => $menuItem->description,
            'price' => (float) $menuItem->price,
            'original_price' => $menuItem->original_price ? (float) $menuItem->original_price : null,
            'image' => $menuItem->image,
            'is_available' => $menuItem->is_available,
            'is_featured' => $menuItem->is_featured,
            'total_sold' => $menuItem->total_sold,
            'prep_time' => $menuItem->prep_time,
            'calories' => $menuItem->calories,
            'category' => [
                'id' => $menuItem->category->id,
                'name' => $menuItem->category->name,
            ],
            'created_at' => $menuItem->created_at,
            'updated_at' => $menuItem->updated_at,
        ]);
    }
}

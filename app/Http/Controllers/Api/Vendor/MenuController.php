<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Category;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    /**
     * Get vendor's menu items with pagination and filtering
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $vendor = $request->user();

        $search = $request->input('search');
        $category = $request->input('category_id');
        $status = $request->input('status');
        $perPage = $request->input('per_page', 10);

        $query = MenuItem::where('vendor_id', $vendor->user_id)
            ->with('category');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        if ($category) {
            $query->where('category_id', $category);
        }

        if ($status !== null && $status !== '') {
            $query->where('is_available', $status);
        }

        $query->orderBy('created_at', 'desc');
        $menuItems = $query->paginate($perPage);

        // Get sales data for each menu item
        $menuItemIds = $menuItems->pluck('item_id')->toArray();
        $salesData = OrderItem::select(
                'order_items.item_id',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue')
            )
            ->join('orders', 'order_items.order_id', '=', 'orders.order_id')
            ->where('orders.status', 'completed')
            ->whereIn('order_items.item_id', $menuItemIds)
            ->groupBy('order_items.item_id')
            ->get()
            ->keyBy('item_id');

        // Get statistics
        $stats = [
            'total' => MenuItem::where('vendor_id', $vendor->user_id)->count(),
            'available' => MenuItem::where('vendor_id', $vendor->user_id)->where('is_available', 1)->count(),
            'unavailable' => MenuItem::where('vendor_id', $vendor->user_id)->where('is_available', 0)->count(),
            'categories' => MenuItem::where('vendor_id', $vendor->user_id)->distinct('category_id')->count('category_id'),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $menuItems->getCollection()->map(function($item) use ($salesData) {
                    return [
                        'item_id' => $item->item_id,
                        'name' => $item->name,
                        'description' => $item->description,
                        'price' => (float) $item->price,
                        'image_url' => $item->image_path ? asset($item->image_path) : null,
                        'is_available' => (bool) $item->is_available,
                        'category' => $item->category ? [
                            'category_id' => $item->category->category_id,
                            'category_name' => $item->category->category_name,
                        ] : null,
                        'total_sold' => $salesData->has($item->item_id) ? (int) $salesData[$item->item_id]->total_sold : 0,
                        'total_revenue' => $salesData->has($item->item_id) ? (float) $salesData[$item->item_id]->total_revenue : 0,
                        'created_at' => $item->created_at,
                    ];
                }),
                'statistics' => $stats,
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
     * Get single menu item
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $vendor = $request->user();

        $menuItem = MenuItem::where('item_id', $id)
            ->where('vendor_id', $vendor->user_id)
            ->with('category')
            ->first();

        if (!$menuItem) {
            return response()->json([
                'success' => false,
                'message' => 'Menu item not found'
            ], 404);
        }

        // Get sales data
        $salesData = OrderItem::select(
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue')
            )
            ->join('orders', 'order_items.order_id', '=', 'orders.order_id')
            ->where('orders.status', 'completed')
            ->where('order_items.item_id', $id)
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'item_id' => $menuItem->item_id,
                'name' => $menuItem->name,
                'description' => $menuItem->description,
                'price' => (float) $menuItem->price,
                'image_url' => $menuItem->image_path ? asset($menuItem->image_path) : null,
                'is_available' => (bool) $menuItem->is_available,
                'category' => $menuItem->category ? [
                    'category_id' => $menuItem->category->category_id,
                    'category_name' => $menuItem->category->category_name,
                ] : null,
                'total_sold' => (int) ($salesData->total_sold ?? 0),
                'total_revenue' => (float) ($salesData->total_revenue ?? 0),
                'created_at' => $menuItem->created_at,
                'updated_at' => $menuItem->updated_at,
            ]
        ]);
    }

    /**
     * Create a new menu item
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'category_id' => 'required|exists:categories,category_id',
            'description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0|max:9999.99',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_available' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $vendor = $request->user();

        // Handle image upload
        $imagePath = '/images/menu/default.jpg';
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images/menu'), $filename);
            $imagePath = '/images/menu/' . $filename;
        }

        $menuItem = MenuItem::create([
            'vendor_id' => $vendor->user_id,
            'category_id' => $request->category_id,
            'name' => strip_tags($request->name),
            'description' => strip_tags($request->description),
            'price' => $request->price,
            'image_path' => $imagePath,
            'is_available' => $request->is_available ?? 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Menu item created successfully',
            'data' => [
                'item_id' => $menuItem->item_id,
                'name' => $menuItem->name,
                'price' => (float) $menuItem->price,
                'image_url' => asset($menuItem->image_path),
                'is_available' => (bool) $menuItem->is_available,
            ]
        ], 201);
    }

    /**
     * Update a menu item
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $vendor = $request->user();

        $menuItem = MenuItem::where('item_id', $id)
            ->where('vendor_id', $vendor->user_id)
            ->first();

        if (!$menuItem) {
            return response()->json([
                'success' => false,
                'message' => 'Menu item not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:100',
            'category_id' => 'sometimes|required|exists:categories,category_id',
            'description' => 'nullable|string|max:500',
            'price' => 'sometimes|required|numeric|min:0|max:9999.99',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_available' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if not default
            if ($menuItem->image_path && $menuItem->image_path !== '/images/menu/default.jpg') {
                $oldImagePath = public_path($menuItem->image_path);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images/menu'), $filename);
            $menuItem->image_path = '/images/menu/' . $filename;
        }

        if ($request->has('name')) {
            $menuItem->name = strip_tags($request->name);
        }
        if ($request->has('category_id')) {
            $menuItem->category_id = $request->category_id;
        }
        if ($request->has('description')) {
            $menuItem->description = strip_tags($request->description);
        }
        if ($request->has('price')) {
            $menuItem->price = $request->price;
        }
        if ($request->has('is_available')) {
            $menuItem->is_available = $request->is_available;
        }

        $menuItem->save();

        return response()->json([
            'success' => true,
            'message' => 'Menu item updated successfully',
            'data' => [
                'item_id' => $menuItem->item_id,
                'name' => $menuItem->name,
                'price' => (float) $menuItem->price,
                'image_url' => asset($menuItem->image_path),
                'is_available' => (bool) $menuItem->is_available,
            ]
        ]);
    }

    /**
     * Delete a menu item
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $vendor = $request->user();

        $menuItem = MenuItem::where('item_id', $id)
            ->where('vendor_id', $vendor->user_id)
            ->first();

        if (!$menuItem) {
            return response()->json([
                'success' => false,
                'message' => 'Menu item not found'
            ], 404);
        }

        // Delete image if not default
        if ($menuItem->image_path && $menuItem->image_path !== '/images/menu/default.jpg') {
            $imagePath = public_path($menuItem->image_path);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $menuItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Menu item deleted successfully'
        ]);
    }

    /**
     * Toggle menu item availability
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleAvailability(Request $request, $id)
    {
        $vendor = $request->user();

        $menuItem = MenuItem::where('item_id', $id)
            ->where('vendor_id', $vendor->user_id)
            ->first();

        if (!$menuItem) {
            return response()->json([
                'success' => false,
                'message' => 'Menu item not found'
            ], 404);
        }

        $menuItem->is_available = !$menuItem->is_available;
        $menuItem->save();

        return response()->json([
            'success' => true,
            'message' => $menuItem->is_available ? 'Item is now available' : 'Item is now unavailable',
            'data' => [
                'item_id' => $menuItem->item_id,
                'is_available' => (bool) $menuItem->is_available,
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
        $categories = Category::all();

        return response()->json([
            'success' => true,
            'data' => $categories->map(function($category) {
                return [
                    'category_id' => $category->category_id,
                    'category_name' => $category->category_name,
                ];
            })
        ]);
    }
}

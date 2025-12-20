<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WishlistController extends Controller
{
    /**
     * Get user's wishlist
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $wishlistItems = Wishlist::where('user_id', $user->user_id)
            ->with(['menuItem.vendor', 'menuItem.category'])
            ->orderBy('created_at', 'desc')
            ->get();

        $items = $wishlistItems->map(function($wishlist) {
            if (!$wishlist->menuItem) {
                return null;
            }
            
            return [
                'wishlist_id' => $wishlist->wishlist_id,
                'added_at' => $wishlist->created_at,
                'menu_item' => [
                    'item_id' => $wishlist->menuItem->item_id,
                    'name' => $wishlist->menuItem->name,
                    'description' => $wishlist->menuItem->description,
                    'price' => (float) $wishlist->menuItem->price,
                    'image_url' => $wishlist->menuItem->image_path 
                        ? asset($wishlist->menuItem->image_path) 
                        : null,
                    'is_available' => $wishlist->menuItem->is_available,
                    'category' => $wishlist->menuItem->category ? [
                        'category_id' => $wishlist->menuItem->category->category_id,
                        'category_name' => $wishlist->menuItem->category->category_name,
                    ] : null,
                    'vendor' => $wishlist->menuItem->vendor ? [
                        'vendor_id' => $wishlist->menuItem->vendor->user_id,
                        'name' => $wishlist->menuItem->vendor->name,
                    ] : null,
                ],
            ];
        })->filter()->values();

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'total' => $items->count(),
            ]
        ]);
    }

    /**
     * Add item to wishlist
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:menu_items,item_id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Check if item exists and is available
        $menuItem = MenuItem::where('item_id', $request->item_id)
            ->where('is_available', 1)
            ->first();

        if (!$menuItem) {
            return response()->json([
                'success' => false,
                'message' => 'Item is not available'
            ], 400);
        }

        // Check if already in wishlist
        $exists = Wishlist::where('user_id', $user->user_id)
            ->where('item_id', $request->item_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Item already in wishlist'
            ], 400);
        }

        // Add to wishlist
        $wishlist = Wishlist::create([
            'user_id' => $user->user_id,
            'item_id' => $request->item_id
        ]);

        $wishlistCount = Wishlist::where('user_id', $user->user_id)->count();

        return response()->json([
            'success' => true,
            'message' => 'Added to wishlist',
            'data' => [
                'wishlist_id' => $wishlist->wishlist_id,
                'wishlist_count' => $wishlistCount,
            ]
        ]);
    }

    /**
     * Remove item from wishlist
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $wishlistItem = Wishlist::where('wishlist_id', $id)
            ->where('user_id', $user->user_id)
            ->first();

        if (!$wishlistItem) {
            return response()->json([
                'success' => false,
                'message' => 'Wishlist item not found'
            ], 404);
        }

        $wishlistItem->delete();

        $wishlistCount = Wishlist::where('user_id', $user->user_id)->count();

        return response()->json([
            'success' => true,
            'message' => 'Removed from wishlist',
            'data' => [
                'wishlist_count' => $wishlistCount,
            ]
        ]);
    }

    /**
     * Toggle wishlist (add or remove by item_id)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:menu_items,item_id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        $wishlistItem = Wishlist::where('user_id', $user->user_id)
            ->where('item_id', $request->item_id)
            ->first();

        if ($wishlistItem) {
            $wishlistItem->delete();
            $inWishlist = false;
            $message = 'Removed from wishlist';
        } else {
            Wishlist::create([
                'user_id' => $user->user_id,
                'item_id' => $request->item_id
            ]);
            $inWishlist = true;
            $message = 'Added to wishlist';
        }

        $wishlistCount = Wishlist::where('user_id', $user->user_id)->count();

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'in_wishlist' => $inWishlist,
                'wishlist_count' => $wishlistCount,
            ]
        ]);
    }

    /**
     * Get wishlist count
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function count(Request $request)
    {
        $user = $request->user();

        $count = Wishlist::where('user_id', $user->user_id)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'count' => $count
            ]
        ]);
    }

    /**
     * Check if item is in wishlist
     * 
     * @param Request $request
     * @param int $itemId
     * @return \Illuminate\Http\JsonResponse
     */
    public function check(Request $request, $itemId)
    {
        $user = $request->user();

        $exists = Wishlist::where('user_id', $user->user_id)
            ->where('item_id', $itemId)
            ->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'in_wishlist' => $exists
            ]
        ]);
    }

    /**
     * Clear all items from wishlist
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clear(Request $request)
    {
        $user = $request->user();

        Wishlist::where('user_id', $user->user_id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Wishlist cleared successfully'
        ]);
    }
}

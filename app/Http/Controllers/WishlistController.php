<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wishlist;
use App\Models\MenuItem;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * Display the wishlist page
     */
    public function index()
    {
        $wishlistItems = Wishlist::where('user_id', Auth::user()->user_id)
            ->with(['menuItem.vendor', 'menuItem.category'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('wishlist', compact('wishlistItems'));
    }

    /**
     * Add item to wishlist
     */
    public function add(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:menu_items,item_id'
        ]);

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
        $exists = Wishlist::where('user_id', Auth::user()->user_id)
            ->where('item_id', $request->item_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Item already in wishlist'
            ], 400);
        }

        // Add to wishlist
        Wishlist::create([
            'user_id' => Auth::user()->user_id,
            'item_id' => $request->item_id
        ]);

        // Get updated wishlist count
        $wishlistCount = Wishlist::where('user_id', Auth::user()->user_id)->count();

        return response()->json([
            'success' => true,
            'message' => 'Added to wishlist',
            'wishlist_count' => $wishlistCount
        ]);
    }

    /**
     * Remove item from wishlist
     */
    public function remove(Request $request, $id)
    {
        $wishlistItem = Wishlist::where('wishlist_id', $id)
            ->where('user_id', Auth::user()->user_id)
            ->first();

        if (!$wishlistItem) {
            return response()->json([
                'success' => false,
                'message' => 'Wishlist item not found'
            ], 404);
        }

        $wishlistItem->delete();

        // Get updated wishlist count
        $wishlistCount = Wishlist::where('user_id', Auth::user()->user_id)->count();

        return response()->json([
            'success' => true,
            'message' => 'Removed from wishlist',
            'wishlist_count' => $wishlistCount
        ]);
    }

    /**
     * Toggle wishlist (add or remove)
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:menu_items,item_id'
        ]);

        $wishlistItem = Wishlist::where('user_id', Auth::user()->user_id)
            ->where('item_id', $request->item_id)
            ->first();

        if ($wishlistItem) {
            // Remove from wishlist
            $wishlistItem->delete();
            $inWishlist = false;
            $message = 'Removed from wishlist';
        } else {
            // Add to wishlist
            Wishlist::create([
                'user_id' => Auth::user()->user_id,
                'item_id' => $request->item_id
            ]);
            $inWishlist = true;
            $message = 'Added to wishlist';
        }

        // Get updated wishlist count
        $wishlistCount = Wishlist::where('user_id', Auth::user()->user_id)->count();

        return response()->json([
            'success' => true,
            'message' => $message,
            'in_wishlist' => $inWishlist,
            'wishlist_count' => $wishlistCount
        ]);
    }

    /**
     * Get wishlist count
     */
    public function getCount()
    {
        $count = Wishlist::where('user_id', Auth::user()->user_id)->count();
        
        return response()->json([
            'count' => $count
        ]);
    }
}

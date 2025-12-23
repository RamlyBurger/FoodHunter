<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlistItems = Wishlist::where('user_id', Auth::id())
            ->with(['menuItem.vendor', 'menuItem.category'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('wishlist.index', compact('wishlistItems'));
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'menu_item_id' => 'required|exists:menu_items,id'
        ]);

        $wishlistItem = Wishlist::where('user_id', Auth::id())
            ->where('menu_item_id', $request->menu_item_id)
            ->first();

        if ($wishlistItem) {
            $wishlistItem->delete();
            $inWishlist = false;
            $message = 'Removed from wishlist';
        } else {
            Wishlist::create([
                'user_id' => Auth::id(),
                'menu_item_id' => $request->menu_item_id
            ]);
            $inWishlist = true;
            $message = 'Added to wishlist';
        }

        $wishlistCount = Wishlist::where('user_id', Auth::id())->count();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'in_wishlist' => $inWishlist,
                'wishlist_count' => $wishlistCount
            ]);
        }

        return back()->with('success', $message);
    }

    public function remove($id)
    {
        $wishlistItem = Wishlist::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $wishlistItem->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Removed from wishlist',
                'wishlist_count' => Wishlist::where('user_id', Auth::id())->count()
            ]);
        }

        return back()->with('success', 'Item removed from wishlist');
    }

    public function count()
    {
        $count = Wishlist::where('user_id', Auth::id())->count();
        
        return response()->json(['count' => $count]);
    }

    public function dropdown()
    {
        $wishlistItems = Wishlist::where('user_id', Auth::id())
            ->with('menuItem')
            ->latest()
            ->limit(10)
            ->get();

        $items = $wishlistItems->map(function ($item) {
            return [
                'id' => $item->id,
                'menu_item_id' => $item->menu_item_id,
                'name' => $item->menuItem->name,
                'price' => $item->menuItem->price,
                'image' => $item->menuItem->image,
                'is_available' => $item->menuItem->is_available,
            ];
        });

        return response()->json([
            'count' => Wishlist::where('user_id', Auth::id())->count(),
            'items' => $items,
        ]);
    }
}

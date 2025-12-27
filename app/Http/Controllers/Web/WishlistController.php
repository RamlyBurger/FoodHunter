<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Helpers\ImageHelper;
use App\Models\Wishlist;
use App\Models\MenuItem;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    use ApiResponse;
    public function index(Request $request)
    {
        $wishlistItems = Wishlist::where('user_id', Auth::id())
            ->with(['menuItem.vendor', 'menuItem.category'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return $this->successResponse([
                'items' => $wishlistItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'menu_item_id' => $item->menu_item_id,
                        'name' => $item->menuItem->name,
                        'description' => $item->menuItem->description,
                        'price' => (float) $item->menuItem->price,
                        'image' => ImageHelper::menuItem($item->menuItem->image),
                        'is_available' => $item->menuItem->is_available,
                        'vendor' => $item->menuItem->vendor ? [
                            'id' => $item->menuItem->vendor->id,
                            'store_name' => $item->menuItem->vendor->store_name,
                            'is_open' => $item->menuItem->vendor->is_open,
                        ] : null,
                        'category' => $item->menuItem->category ? [
                            'id' => $item->menuItem->category->id,
                            'name' => $item->menuItem->category->name,
                        ] : null,
                        'added_at' => $item->created_at->format('M d, Y'),
                    ];
                }),
                'count' => $wishlistItems->count(),
            ]);
        }

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
            $menuItem = MenuItem::find($request->menu_item_id);
            return $this->successResponse([
                'in_wishlist' => $inWishlist,
                'wishlist_count' => $wishlistCount,
                'item_name' => $menuItem ? $menuItem->name : null,
            ], $message);
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
            return $this->successResponse([
                'wishlist_count' => Wishlist::where('user_id', Auth::id())->count()
            ], 'Removed from wishlist');
        }

        return back()->with('success', 'Item removed from wishlist');
    }

    public function count()
    {
        $count = Wishlist::where('user_id', Auth::id())->count();
        
        return $this->successResponse(['count' => $count]);
    }

    public function clear()
    {
        Wishlist::where('user_id', Auth::id())->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return $this->successResponse([
                'wishlist_count' => 0,
            ], 'Wishlist cleared.');
        }

        return back()->with('success', 'Wishlist cleared.');
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
                'image' => ImageHelper::menuItem($item->menuItem->image),
                'is_available' => $item->menuItem->is_available,
            ];
        });

        return $this->successResponse([
            'count' => Wishlist::where('user_id', Auth::id())->count(),
            'items' => $items,
        ]);
    }
}

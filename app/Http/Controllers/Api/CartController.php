<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Models\CartItem;
use App\Models\MenuItem;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $cartItems = CartItem::where('user_id', $request->user()->id)
            ->with(['menuItem.vendor:id,store_name', 'menuItem.category:id,name'])
            ->get();

        $summary = $this->calculateSummary($cartItems);

        return $this->successResponse([
            'items' => $cartItems->map(fn($item) => $this->formatCartItem($item)),
            'summary' => $summary,
        ]);
    }

    public function add(AddToCartRequest $request): JsonResponse
    {
        $menuItem = MenuItem::findOrFail($request->menu_item_id);

        if (!$menuItem->is_available) {
            return $this->errorResponse('Item is not available', 400, 'ITEM_UNAVAILABLE');
        }

        $cartItem = CartItem::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'menu_item_id' => $request->menu_item_id,
            ],
            [
                'quantity' => $request->quantity,
                'special_instructions' => $request->special_instructions,
            ]
        );

        return $this->successResponse(
            $this->formatCartItem($cartItem->load('menuItem')),
            'Item added to cart'
        );
    }

    public function update(Request $request, CartItem $cartItem): JsonResponse
    {
        if ($cartItem->user_id !== $request->user()->id) {
            return $this->forbiddenResponse('Unauthorized');
        }

        $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
            'special_instructions' => ['nullable', 'string', 'max:500'],
        ]);

        $cartItem->update([
            'quantity' => $request->quantity,
            'special_instructions' => $request->special_instructions,
        ]);

        return $this->successResponse(
            $this->formatCartItem($cartItem->load('menuItem')),
            'Cart updated'
        );
    }

    public function remove(Request $request, CartItem $cartItem): JsonResponse
    {
        if ($cartItem->user_id !== $request->user()->id) {
            return $this->forbiddenResponse('Unauthorized');
        }

        $cartItem->delete();

        return $this->successResponse(null, 'Item removed from cart');
    }

    public function clear(Request $request): JsonResponse
    {
        CartItem::where('user_id', $request->user()->id)->delete();

        return $this->successResponse(null, 'Cart cleared');
    }

    /**
     * Web Service: Expose - Cart Summary
     * Student 2 (Menu) can consume this to show recommendations
     */
    public function summary(Request $request): JsonResponse
    {
        $cartItems = CartItem::where('user_id', $request->user()->id)
            ->with('menuItem')
            ->get();

        $summary = $this->calculateSummary($cartItems);

        return $this->successResponse($summary);
    }

    /**
     * Get cart item count
     * URL: /api/cart/count
     */
    public function count(Request $request): JsonResponse
    {
        $count = CartItem::where('user_id', $request->user()->id)
            ->sum('quantity');

        return $this->successResponse([
            'count' => (int) $count,
        ]);
    }

    private function formatCartItem(CartItem $item): array
    {
        return [
            'id' => $item->id,
            'quantity' => $item->quantity,
            'special_instructions' => $item->special_instructions,
            'subtotal' => $item->getSubtotal(),
            'menu_item' => $item->menuItem ? [
                'id' => $item->menuItem->id,
                'name' => $item->menuItem->name,
                'price' => (float) $item->menuItem->price,
                'image' => $item->menuItem->image,
                'is_available' => $item->menuItem->is_available,
                'vendor' => $item->menuItem->vendor ? [
                    'id' => $item->menuItem->vendor->id,
                    'store_name' => $item->menuItem->vendor->store_name,
                ] : null,
            ] : null,
        ];
    }

    private function calculateSummary($cartItems): array
    {
        $subtotal = $cartItems->sum(fn($item) => $item->getSubtotal());
        $serviceFee = 2.00;
        $total = $subtotal + $serviceFee;

        return [
            'item_count' => $cartItems->sum('quantity'),
            'subtotal' => (float) $subtotal,
            'service_fee' => $serviceFee,
            'discount' => 0.00,
            'total' => (float) $total,
        ];
    }
}

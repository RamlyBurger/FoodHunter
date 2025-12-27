<?php
/**
 * =============================================================================
 * AddToCartRequest - Lee Song Yan (Cart, Checkout & Notifications Module)
 * =============================================================================
 * 
 * @author     Lee Song Yan
 * @module     Cart, Checkout & Notifications Module
 * 
 * Form request validation for adding items to cart.
 * Validates menu item ID, quantity, and special instructions.
 * =============================================================================
 */

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'menu_item_id' => ['required', 'integer', 'exists:menu_items,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
            'special_instructions' => ['nullable', 'string', 'max:500'],
        ];
    }
}

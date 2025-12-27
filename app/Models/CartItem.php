<?php
/**
 * =============================================================================
 * CartItem Model - Lee Song Yan (Cart, Checkout & Notifications Module)
 * =============================================================================
 * 
 * @author     Lee Song Yan
 * @module     Cart, Checkout & Notifications Module
 * 
 * Represents items in a user's shopping cart before checkout.
 * Linked to MenuItem for product details and pricing.
 * =============================================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'menu_item_id',
        'quantity',
        'special_instructions',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function getSubtotal(): float
    {
        return $this->menuItem->price * $this->quantity;
    }
}

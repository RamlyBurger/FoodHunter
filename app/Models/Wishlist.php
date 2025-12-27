<?php
/**
 * =============================================================================
 * Wishlist Model - Haerine Deepak Singh (Menu & Catalog Module)
 * =============================================================================
 * 
 * @author     Haerine Deepak Singh
 * @module     Menu & Catalog Module
 * 
 * Represents user's saved/favorite menu items.
 * Part of the menu browsing experience.
 * =============================================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wishlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'menu_item_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }
}

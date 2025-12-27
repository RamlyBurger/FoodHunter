<?php
/**
 * =============================================================================
 * MenuItem Model - Haerine Deepak Singh (Menu & Catalog Module)
 * =============================================================================
 * 
 * @author     Haerine Deepak Singh
 * @module     Menu & Catalog Module
 * @pattern    Repository Pattern (EloquentMenuItemRepository)
 * 
 * Represents food items available for ordering from vendors.
 * Accessed through Repository Pattern for data abstraction.
 * =============================================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'original_price',
        'image',
        'is_available',
        'is_featured',
        'prep_time',
        'calories',
        'total_sold',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'original_price' => 'decimal:2',
            'is_available' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function hasDiscount(): bool
    {
        return $this->original_price && $this->original_price > $this->price;
    }

    public function getDiscountPercentage(): ?int
    {
        if (!$this->hasDiscount()) {
            return null;
        }
        return (int) round((($this->original_price - $this->price) / $this->original_price) * 100);
    }

}

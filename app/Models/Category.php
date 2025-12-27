<?php
/**
 * =============================================================================
 * Category Model - Haerine Deepak Singh (Menu & Catalog Module)
 * =============================================================================
 * 
 * @author     Haerine Deepak Singh
 * @module     Menu & Catalog Module
 * 
 * Represents food categories for organizing menu items.
 * Used for filtering and browsing the menu catalog.
 * =============================================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

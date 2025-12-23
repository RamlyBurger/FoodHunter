<?php

namespace App\Patterns\Repository;

use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

/**
 * Repository Pattern - Eloquent Menu Item Repository
 * Student 2: Menu & Catalog Module
 * 
 * Concrete implementation using Eloquent ORM.
 * Encapsulates all database operations for menu items.
 */
class EloquentMenuItemRepository implements MenuItemRepositoryInterface
{
    public function findById(int $id): ?MenuItem
    {
        return MenuItem::with(['vendor', 'category'])->find($id);
    }

    public function findBySlug(string $slug): ?MenuItem
    {
        return MenuItem::with(['vendor', 'category'])
            ->where('slug', $slug)
            ->first();
    }

    public function getAll(): Collection
    {
        return MenuItem::with(['vendor', 'category'])
            ->orderBy('name')
            ->get();
    }

    public function getAvailable(): Collection
    {
        return MenuItem::available()
            ->with(['vendor', 'category'])
            ->orderBy('name')
            ->get();
    }

    public function getFeatured(int $limit = 10): Collection
    {
        return MenuItem::available()
            ->featured()
            ->with(['vendor', 'category'])
            ->limit($limit)
            ->get();
    }

    public function getByCategory(int $categoryId): Collection
    {
        return MenuItem::available()
            ->where('category_id', $categoryId)
            ->with(['vendor', 'category'])
            ->orderBy('name')
            ->get();
    }

    public function getByVendor(int $vendorId): Collection
    {
        return MenuItem::where('vendor_id', $vendorId)
            ->with('category')
            ->orderBy('name')
            ->get();
    }

    public function search(string $query): Collection
    {
        return MenuItem::available()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->with(['vendor', 'category'])
            ->limit(20)
            ->get();
    }

    public function create(array $data): MenuItem
    {
        $data['slug'] = Str::slug($data['name']) . '-' . uniqid();
        return MenuItem::create($data);
    }

    public function update(int $id, array $data): ?MenuItem
    {
        $item = MenuItem::find($id);
        if (!$item) {
            return null;
        }
        
        if (isset($data['name']) && $data['name'] !== $item->name) {
            $data['slug'] = Str::slug($data['name']) . '-' . uniqid();
        }
        
        $item->update($data);
        return $item->fresh();
    }

    public function delete(int $id): bool
    {
        $item = MenuItem::find($id);
        return $item ? $item->delete() : false;
    }

    public function toggleAvailability(int $id): ?MenuItem
    {
        $item = MenuItem::find($id);
        if (!$item) {
            return null;
        }
        
        $item->update(['is_available' => !$item->is_available]);
        return $item->fresh();
    }
}

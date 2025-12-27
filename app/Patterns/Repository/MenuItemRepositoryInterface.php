<?php

namespace App\Patterns\Repository;

use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository Pattern - Menu Item Repository Interface
 * Haerine Deepak Singh: Menu & Catalog Module
 * 
 * This interface abstracts the data layer for menu items,
 * providing a collection-like interface for accessing domain objects.
 */
interface MenuItemRepositoryInterface
{
    public function findById(int $id): ?MenuItem;
    
    public function findBySlug(string $slug): ?MenuItem;
    
    public function getAll(): Collection;
    
    public function getAvailable(): Collection;
    
    public function getFeatured(int $limit = 10): Collection;
    
    public function getByCategory(int $categoryId): Collection;
    
    public function getByVendor(int $vendorId): Collection;
    
    public function search(string $query): Collection;
    
    public function create(array $data): MenuItem;
    
    public function update(int $id, array $data): ?MenuItem;
    
    public function delete(int $id): bool;
    
    public function toggleAvailability(int $id): ?MenuItem;
}

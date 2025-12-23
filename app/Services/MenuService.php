<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Patterns\Repository\EloquentMenuItemRepository;
use App\Patterns\Repository\MenuItemRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Menu Service - Student 2
 * 
 * Uses Repository Pattern with security features:
 * - Parameterized Queries (SQL Injection Protection) - via Eloquent ORM
 * - Output Encoding (XSS Protection)
 */
class MenuService
{
    private MenuItemRepositoryInterface $repository;

    public function __construct()
    {
        $this->repository = new EloquentMenuItemRepository();
    }

    public function getAvailableItems(): Collection
    {
        return $this->repository->getAvailable();
    }

    public function getFeaturedItems(int $limit = 10): Collection
    {
        return $this->repository->getFeatured($limit);
    }

    public function getByCategory(int $categoryId): Collection
    {
        return $this->repository->getByCategory($categoryId);
    }

    public function getByVendor(int $vendorId): Collection
    {
        return $this->repository->getByVendor($vendorId);
    }

    public function search(string $query): Collection
    {
        // Security: Sanitize search query
        $query = $this->sanitizeInput($query);
        return $this->repository->search($query);
    }

    public function getItem(int $id): ?MenuItem
    {
        return $this->repository->findById($id);
    }

    public function createItem(int $vendorId, array $data): MenuItem
    {
        $data['vendor_id'] = $vendorId;
        $data = $this->sanitizeItemData($data);
        return $this->repository->create($data);
    }

    public function updateItem(int $id, array $data): ?MenuItem
    {
        $data = $this->sanitizeItemData($data);
        return $this->repository->update($id, $data);
    }

    public function deleteItem(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function toggleAvailability(int $id): ?MenuItem
    {
        return $this->repository->toggleAvailability($id);
    }

    public function checkAvailability(int $itemId): array
    {
        $item = $this->repository->findById($itemId);
        
        if (!$item) {
            return [
                'available' => false,
                'reason' => 'Item not found',
            ];
        }

        return [
            'available' => $item->is_available,
            'item_id' => $item->id,
            'name' => $this->encodeOutput($item->name),
            'is_available' => $item->is_available,
        ];
    }

    // Security: Output Encoding (XSS Protection)
    public function encodeOutput(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }

    public function formatItemForDisplay(MenuItem $item): array
    {
        return [
            'id' => $item->id,
            'name' => $this->encodeOutput($item->name),
            'description' => $this->encodeOutput($item->description),
            'price' => (float) $item->price,
            'image' => $item->image,
            'is_available' => $item->is_available,
            'is_featured' => $item->is_featured,
            'vendor' => $item->vendor ? [
                'id' => $item->vendor->id,
                'store_name' => $this->encodeOutput($item->vendor->store_name),
            ] : null,
            'category' => $item->category ? [
                'id' => $item->category->id,
                'name' => $this->encodeOutput($item->category->name),
            ] : null,
        ];
    }

    private function sanitizeInput(string $input): string
    {
        return trim(strip_tags($input));
    }

    private function sanitizeItemData(array $data): array
    {
        if (isset($data['name'])) {
            $data['name'] = $this->sanitizeInput($data['name']);
        }
        if (isset($data['description'])) {
            $data['description'] = $this->sanitizeInput($data['description']);
        }
        // Security: Validate image path to prevent path traversal
        if (isset($data['image'])) {
            $data['image'] = $this->validateImagePath($data['image']);
        }
        return $data;
    }

    /**
     * Security: Path Traversal Prevention [OWASP 35]
     * Validates and sanitizes image paths to prevent directory traversal attacks.
     * Attackers may try paths like "../../../etc/passwd" to access sensitive files.
     */
    public function validateImagePath(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        // Remove any path traversal sequences
        $path = str_replace(['../', '..\\', '..'], '', $path);
        
        // Get only the basename to prevent directory traversal
        $filename = basename($path);
        
        // Validate file extension
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($extension, $allowedExtensions)) {
            return null;
        }

        // Return safe path within allowed directory
        return '/images/menu/' . $filename;
    }

    /**
     * Security: Safe file path resolution [OWASP 35]
     * Ensures requested file is within the allowed directory.
     */
    public function getSecureImagePath(string $requestedPath): ?string
    {
        $baseDir = public_path('images/menu');
        $requestedPath = $this->validateImagePath($requestedPath);
        
        if (!$requestedPath) {
            return null;
        }

        $fullPath = realpath($baseDir . '/' . basename($requestedPath));
        
        // Verify the resolved path is within the base directory
        if ($fullPath === false || strpos($fullPath, realpath($baseDir)) !== 0) {
            return null; // Path traversal attempt detected
        }

        return $fullPath;
    }
}

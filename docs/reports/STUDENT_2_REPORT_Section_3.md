## 3. Design Pattern

### 3.1 Description of Design Pattern

The Repository Pattern is a design pattern that mediates between the domain and data mapping layers, acting like an in-memory collection of domain objects. It provides a more object-oriented view of the persistence layer and decouples the business logic from data access code.

In the FoodHunter Menu & Catalog Module, the Repository Pattern is used to abstract all database operations for menu items. This allows the controllers and services to work with a clean interface without knowing about Eloquent ORM specifics.

The Repository Pattern is ideal for this use case because:

- **Abstraction**: The data access logic is hidden behind an interface
- **Testability**: Easy to mock the repository for unit testing
- **Single Responsibility**: Data access logic is separated from business logic
- **Flexibility**: Can switch from Eloquent to another ORM without changing business code

The pattern consists of two main components:

- **Repository Interface (`MenuItemRepositoryInterface`)**: Defines the contract for data access operations
- **Concrete Repository (`EloquentMenuItemRepository`)**: Implements the interface using Eloquent ORM

### 3.2 Implementation of Design Pattern

The Repository Pattern is implemented in the `app/Patterns/Repository` directory with the following classes:

**File: `app/Patterns/Repository/MenuItemRepositoryInterface.php`**
```php
<?php

namespace App\Patterns\Repository;

use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository Pattern - Menu Item Repository Interface
 * Student 2: Menu & Catalog Module
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
```

**File: `app/Patterns/Repository/EloquentMenuItemRepository.php`**
```php
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
```

**Usage in `app/Http/Controllers/Api/MenuController.php`:**
```php
<?php

namespace App\Http\Controllers\Api;

use App\Patterns\Repository\MenuItemRepositoryInterface;
use App\Patterns\Repository\EloquentMenuItemRepository;

class MenuController extends Controller
{
    private MenuItemRepositoryInterface $menuRepository;

    public function __construct()
    {
        // Inject the concrete repository implementation
        $this->menuRepository = new EloquentMenuItemRepository();
    }

    public function featured(): JsonResponse
    {
        // Using Repository Pattern - getFeatured() method
        $items = $this->menuRepository->getFeatured(10)
            ->map(fn($item) => $this->formatMenuItem($item));

        return $this->successResponse($items);
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        
        // Using Repository Pattern - search() method
        $sanitizedQuery = trim(strip_tags($query));
        $items = $this->menuRepository->search($sanitizedQuery)
            ->map(fn($item) => $this->formatMenuItem($item));

        return $this->successResponse($items);
    }

    public function checkAvailability(MenuItem $menuItem): JsonResponse
    {
        // Using Repository Pattern - findById() for consistent data access
        $item = $this->menuRepository->findById($menuItem->id);
        
        return $this->successResponse([
            'item_id' => $item->id,
            'name' => htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8'),
            'available' => $item->is_available,
            'price' => (float) $item->price,
        ]);
    }
}
```

### 3.3 Class Diagram

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                          Repository Pattern                                  │
│                       Menu & Catalog Module                                  │
└─────────────────────────────────────────────────────────────────────────────┘

                    ┌────────────────────────────────────────┐
                    │    <<interface>>                       │
                    │    MenuItemRepositoryInterface         │
                    ├────────────────────────────────────────┤
                    │ + findById(id): MenuItem               │
                    │ + findBySlug(slug): MenuItem           │
                    │ + getAll(): Collection                 │
                    │ + getAvailable(): Collection           │
                    │ + getFeatured(limit): Collection       │
                    │ + getByCategory(id): Collection        │
                    │ + getByVendor(id): Collection          │
                    │ + search(query): Collection            │
                    │ + create(data): MenuItem               │
                    │ + update(id, data): MenuItem           │
                    │ + delete(id): bool                     │
                    │ + toggleAvailability(id): MenuItem     │
                    └────────────────────────────────────────┘
                                       △
                                       │ implements
                                       │
                    ┌────────────────────────────────────────┐
                    │    EloquentMenuItemRepository          │
                    ├────────────────────────────────────────┤
                    │ + findById(id): MenuItem               │
                    │ + findBySlug(slug): MenuItem           │
                    │ + getAvailable(): Collection           │
                    │ + getFeatured(limit): Collection       │
                    │ + search(query): Collection            │
                    │ + create(data): MenuItem               │
                    │ + update(id, data): MenuItem           │
                    │ + delete(id): bool                     │
                    └────────────────────────────────────────┘
                                       │
                                       │ uses
                                       ▼
                    ┌────────────────────────────────────────┐
                    │              MenuItem                   │
                    ├────────────────────────────────────────┤
                    │ - id, vendor_id, category_id           │
                    │ - name, description, price             │
                    │ - is_available, is_featured            │
                    ├────────────────────────────────────────┤
                    │ + scopeAvailable()                     │
                    │ + scopeFeatured()                      │
                    └────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                              Client Usage                                    │
└─────────────────────────────────────────────────────────────────────────────┘

        ┌───────────────────────┐       ┌─────────────────────────────────┐
        │     MenuController    │──────▶│ MenuItemRepositoryInterface     │
        ├───────────────────────┤       └─────────────────────────────────┘
        │ - menuRepository      │
        │ + featured()          │
        │ + search()            │
        │ + checkAvailability() │
        └───────────────────────┘
```

### 3.4 Justification for Using Repository Pattern

The Repository Pattern was chosen for the Menu & Catalog Module for the following reasons:

1. **Data Access Abstraction**: All database queries are encapsulated in the repository, keeping controllers thin and focused on handling HTTP requests.

2. **Testability**: The repository interface can be easily mocked for unit testing without requiring a database connection.

3. **Consistency**: All data access follows the same patterns, making the codebase predictable and easier to maintain.

4. **Flexibility**: If the application needs to switch from MySQL to another database or use a different ORM, only the concrete repository implementation needs to change.

5. **Query Reusability**: Common queries like `getAvailable()` and `getFeatured()` are defined once and reused across multiple controllers.

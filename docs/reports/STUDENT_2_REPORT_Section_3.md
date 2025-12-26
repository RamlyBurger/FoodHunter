## 3. Design Pattern

### 3.1 Description of Design Pattern

The Repository Pattern is a design pattern that mediates between the domain and data mapping layers, acting like an in-memory collection of domain objects. It provides a more object-oriented view of the persistence layer and decouples the business logic from data access code. Originally described by Martin Fowler in his book "Patterns of Enterprise Application Architecture" (2002), this pattern has become a cornerstone of clean architecture in modern web applications.

#### 3.1.1 Pattern Overview and Purpose

The Repository Pattern serves as a mediator between the domain model and data mapping layers. It encapsulates the logic required to access data sources, centralizing common data access functionality and providing better maintainability. The repository conceptually acts like an in-memory domain object collection, providing methods to add, remove, and retrieve objects.

Key characteristics of the Repository Pattern include:

- **Collection-like Interface**: Repositories expose methods similar to collection operations (find, add, remove, update)
- **Domain-Centric**: Queries are expressed in terms of the domain model, not database-specific syntax
- **Persistence Ignorance**: The domain layer doesn't need to know how data is persisted
- **Query Encapsulation**: Complex queries are encapsulated within repository methods

#### 3.1.2 Application in FoodHunter Menu Module

In the FoodHunter Menu & Catalog Module, the Repository Pattern is used to abstract all database operations for menu items. This allows the controllers and services to work with a clean interface without knowing about Eloquent ORM specifics or database structure.

The pattern addresses several specific needs in this module:

1. **Menu Item Retrieval**: Multiple methods for fetching items (by ID, by category, by vendor, featured, available)
2. **Search Functionality**: Encapsulates the search query logic including text matching and filtering
3. **CRUD Operations**: Create, read, update, and delete operations for menu items
4. **Availability Checking**: Determining if items are in stock and vendors are open

#### 3.1.3 Why Repository Pattern is Ideal for Menu Management

The Repository Pattern is ideal for this use case because:

- **Abstraction**: The data access logic is hidden behind an interface, allowing the `MenuService` and controllers to focus on business logic without worrying about database queries. This creates a clean separation between "what data we need" and "how we get that data."

- **Testability**: Easy to mock the repository for unit testing. Test cases can use a mock repository that returns predetermined data, eliminating the need for a real database connection during testing. This significantly speeds up test execution and makes tests more reliable.

- **Single Responsibility Principle (SRP)**: Data access logic is separated from business logic. The repository handles only data retrieval and persistence, while services handle business rules and validation. This makes each component easier to understand and modify.

- **Flexibility and Portability**: Can switch from Eloquent to another ORM (like Doctrine) or even a different data source (like an external API or cache) without changing business code. Only the repository implementation needs to change.

- **Query Reusability**: Common queries are defined once in the repository and can be reused across multiple controllers and services, reducing code duplication and ensuring consistency.

- **Centralized Query Optimization**: Performance optimizations (eager loading, caching, query optimization) can be implemented in one place, benefiting all code that uses the repository.

#### 3.1.4 Pattern Components

The pattern consists of two main components:

- **Repository Interface (`MenuItemRepositoryInterface`)**: Defines the contract for data access operations. This interface declares all methods that any repository implementation must provide, ensuring consistency and enabling dependency injection.

- **Concrete Repository (`EloquentMenuItemRepository`)**: Implements the interface using Eloquent ORM. This class contains the actual database queries, leveraging Laravel's Eloquent ORM features like eager loading, scopes, and query builder methods.

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

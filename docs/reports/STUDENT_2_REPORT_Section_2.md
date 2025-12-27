## 2. Module Description

### 2.1 Menu & Catalog Module

The Menu & Catalog Module serves as the product browsing foundation of the FoodHunter Food Ordering System. This module is responsible for managing all menu-related operations including category management, menu item browsing, search functionality, and vendor listings. It provides customers with an intuitive interface to discover and explore available food items from various vendors. As the primary interface between customers and the food offerings, this module plays a crucial role in the user experience and conversion rate of the platform.

#### 2.1.1 Module Architecture Overview

The module follows a clean architecture approach with clear separation of concerns:

- **Controllers Layer**: Handles HTTP requests, validates input, and returns appropriate responses
- **Services Layer**: Contains the `MenuService` class with business logic for menu operations
- **Repository Layer**: Implements the Repository Pattern through `EloquentMenuItemRepository` for data access abstraction
- **Models Layer**: Eloquent models (`MenuItem`, `Category`, `Wishlist`) representing database entities

This layered architecture ensures that changes in one layer don't ripple through the entire codebase, making the module highly maintainable and testable.

#### 2.1.2 Customer Browsing Experience

When customers visit the FoodHunter platform, they can browse menu items organized by categories such as Rice, Noodles, Beverages, and Snacks. The browsing experience is designed to be intuitive and efficient:

1. **Homepage Display**: Featured items and popular items (loaded via Haerine Deepak Singh's Popular Items API) are prominently displayed to showcase the best offerings
2. **Category Navigation**: Categories are displayed with images and item counts, allowing quick navigation to specific food types
3. **Vendor Discovery**: Customers can browse by vendor to find their favorite stalls or discover new ones
4. **Visual Presentation**: Each menu item displays an image, name, price, vendor name, and availability status

The module supports advanced filtering by category, vendor, and price range, along with full-text search capabilities. Pagination (12 items per page) ensures fast page loads while providing access to the complete catalog.

#### 2.1.3 Repository Pattern Implementation

The module implements the Repository Pattern for data access, abstracting all database operations through a clean interface defined in `MenuItemRepositoryInterface`. This separation of concerns provides several benefits:

- **Abstraction**: Controllers and services interact with the repository interface, not directly with Eloquent
- **Testability**: The repository can be easily mocked for unit testing without database dependencies
- **Flexibility**: The underlying data source can be changed (e.g., from MySQL to PostgreSQL or even an API) without modifying business logic
- **Consistency**: All data access follows the same patterns and conventions

The `EloquentMenuItemRepository` class provides methods for common operations: `findById()`, `search()`, `getAvailable()`, `getFeatured()`, `getByCategory()`, and more.

#### 2.1.4 Web Service Integration

A key feature of this module is the web service endpoints that enable integration with other modules:

- **Item Availability API** (`GET /api/menu/{id}/availability`): Consumed by the Cart module (Lee Song Yan) to validate items before adding to cart, ensuring customers cannot order unavailable items
- **Popular Items API** (`GET /api/menu/popular`): Returns trending items based on sales data, consumed by the Home page to display dynamic content

These APIs follow RESTful conventions and return standardized JSON responses with proper error handling.

**Sub-Modules Implemented:**

**Category Management:** Organizes menu items by food type. Categories have names, descriptions, icons, and sort orders for display. Only active categories are shown to customers. The system supports hierarchical categories for future expansion.

**Menu Browsing:** Allows customers to browse available menu items with pagination (12 items per page). Items can be sorted by popularity (based on order count), price (low to high or high to low), or newest (by creation date). All data access uses the Repository Pattern for clean abstraction.

**Search & Filter:** Full-text search across item names and descriptions using parameterized queries to prevent SQL injection. Filtering supports multiple criteria simultaneously: category, vendor, price range, and availability status. Search results are ranked by relevance.

**Featured Items:** Displays promoted menu items on the homepage and category pages. Vendors can mark items as featured through the vendor dashboard. Featured items receive prominent placement and visual highlighting.

**Item Availability API:** Exposes a RESTful web service endpoint consumed by other modules to check if an item is available and in stock. The API returns detailed availability information including stock status, vendor open/closed status, and preparation time estimates.

**Popular Items API:** Provides a list of trending menu items based on order history and sales data. This API powers the "Most Popular" section on the homepage and helps customers discover well-reviewed items.

**Wishlist Management:** Allows authenticated users to save favorite items for quick access later. Users can add/remove items from their wishlist and view all saved items on a dedicated page. Wishlist items display availability status updates.
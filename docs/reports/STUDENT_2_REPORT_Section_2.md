## 2. Module Description

### 2.1 Menu & Catalog Module

The Menu & Catalog Module serves as the product browsing foundation of the FoodHunter Food Ordering System. This module is responsible for managing all menu-related operations including category management, menu item browsing, search functionality, and vendor listings. It provides customers with an intuitive interface to discover and explore available food items from various vendors.

When customers visit the FoodHunter platform, they can browse menu items organized by categories such as Rice, Noodles, Beverages, and Snacks. The module supports advanced filtering by category, vendor, and price, along with full-text search capabilities. Featured items are prominently displayed on the homepage to highlight promotional offerings.

The module implements the Repository Pattern for data access, abstracting all database operations through a clean interface. This separation of concerns allows the business logic to remain independent of the underlying data storage mechanism, making the code more maintainable and testable.

A key feature of this module is the web service endpoint for checking item availability. This API is consumed by the Cart module (Student 3) to validate items before checkout, ensuring customers cannot order unavailable items.

**Sub-Modules Implemented:**

**Category Management:** Organizes menu items by food type. Categories have names, descriptions, icons, and sort orders for display. Only active categories are shown to customers.

**Menu Browsing:** Allows customers to browse available menu items with pagination (12 items per page). Items can be sorted by popularity, price (low/high), or newest. Uses the Repository Pattern for all data access.

**Search & Filter:** Full-text search across item names and descriptions. Filtering by category and vendor. Input is sanitized to prevent SQL injection attacks.

**Featured Items:** Displays promoted menu items on the homepage. Vendors can mark items as featured through the vendor dashboard.

**Item Availability API:** Exposes a web service endpoint consumed by other modules to check if an item is available and in stock before adding to cart or checkout.

**Wishlist Management:** Allows authenticated users to save favorite items for quick access later.
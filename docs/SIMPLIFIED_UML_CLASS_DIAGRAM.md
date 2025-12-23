# FoodHunter System - UML Class Diagram

## 1. DOMAIN ENTITIES

### 1.1 User Entity
```
Class: User
Attributes:
  - id: int
  - name: string
  - email: string
  - password: string
  - phone: string
  - role: enum ['customer', 'vendor']
  - avatar: string
  - email_verified_at: datetime
  - created_at: datetime
  - updated_at: datetime

Methods:
  + isVendor(): bool
  + isCustomer(): bool
  + getVendorProfile(): Vendor
  + getOrders(): Collection<Order>
  + getCartItems(): Collection<CartItem>
  + getWishlist(): Collection<Wishlist>
  + getNotifications(): Collection<Notification>
  + getVouchers(): Collection<Voucher>
```

### 1.2 Vendor Entity
```
Class: Vendor
Attributes:
  - id: int
  - user_id: int
  - store_name: string
  - description: text
  - logo: string
  - is_open: bool
  - is_active: bool
  - avg_prep_time: int
  - total_orders: int
  - rating: decimal
  - created_at: datetime
  - updated_at: datetime

Methods:
  + getUser(): User
  + getMenuItems(): Collection<MenuItem>
  + getOrders(): Collection<Order>
  + getOperatingHours(): Collection<VendorHour>
  + isCurrentlyOpen(): bool
  + calculateAvgPrepTime(): int
  + updateStats(): void
  + toggleOpenStatus(): void
```

### 1.3 MenuItem Entity
```
Class: MenuItem
Attributes:
  - id: int
  - vendor_id: int
  - category_id: int
  - name: string
  - description: text
  - price: decimal
  - discount_price: decimal
  - image: string
  - is_available: bool
  - is_featured: bool
  - prep_time: int
  - total_sold: int
  - created_at: datetime
  - updated_at: datetime

Methods:
  + getVendor(): Vendor
  + getCategory(): Category
  + hasDiscount(): bool
  + getDiscountPercentage(): float
  + getFinalPrice(): decimal
  + incrementSold(): void
```

### 1.4 Category Entity
```
Class: Category
Attributes:
  - id: int
  - name: string
  - slug: string
  - description: text
  - icon: string
  - is_active: bool
  - created_at: datetime
  - updated_at: datetime

Methods:
  + getMenuItems(): Collection<MenuItem>
```

### 1.5 Order Entity
```
Class: Order
Attributes:
  - id: int
  - order_number: string
  - user_id: int
  - vendor_id: int
  - status: enum ['pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled']
  - subtotal: decimal
  - service_fee: decimal
  - discount: decimal
  - total: decimal
  - payment_method: enum ['cash', 'card', 'ewallet']
  - notes: text
  - cancelled_reason: text
  - created_at: datetime
  - updated_at: datetime

Methods:
  + getUser(): User
  + getVendor(): Vendor
  + getItems(): Collection<OrderItem>
  + getPayment(): Payment
  + getPickup(): Pickup
  + isPending(): bool
  + isActive(): bool
  + canBeCancelled(): bool
  + generateOrderNumber(): string {static}
```

### 1.6 OrderItem Entity
```
Class: OrderItem
Attributes:
  - id: int
  - order_id: int
  - menu_item_id: int
  - quantity: int
  - price: decimal
  - subtotal: decimal
  - special_instructions: text
  - created_at: datetime
  - updated_at: datetime

Methods:
  + getOrder(): Order
  + getMenuItem(): MenuItem
  + calculateSubtotal(): decimal
```

### 1.7 CartItem Entity
```
Class: CartItem
Attributes:
  - id: int
  - user_id: int
  - menu_item_id: int
  - quantity: int
  - special_instructions: text
  - created_at: datetime
  - updated_at: datetime

Methods:
  + getUser(): User
  + getMenuItem(): MenuItem
  + getSubtotal(): decimal
  + updateQuantity(quantity: int): void
```

### 1.8 Voucher Entity
```
Class: Voucher
Attributes:
  - id: int
  - vendor_id: int
  - code: string
  - name: string
  - description: text
  - type: enum ['fixed', 'percentage']
  - value: decimal
  - min_order: decimal
  - max_discount: decimal
  - per_user_limit: int
  - usage_count: int
  - max_usage: int
  - start_date: datetime
  - end_date: datetime
  - is_active: bool
  - created_at: datetime
  - updated_at: datetime

Methods:
  + getVendor(): Vendor
  + getUsers(): Collection<User>
  + isValid(): bool
  + canBeUsedBy(user: User): bool
  + calculateDiscount(subtotal: decimal): decimal
```

### 1.9 UserVoucher Entity
```
Class: UserVoucher
Attributes:
  - id: int
  - user_id: int
  - voucher_id: int
  - usage_count: int
  - redeemed_at: datetime
  - used_at: datetime
  - created_at: datetime
  - updated_at: datetime

Methods:
  + getUser(): User
  + getVoucher(): Voucher
  + canUse(): bool
  + incrementUsage(): void
```

### 1.10 Payment Entity
```
Class: Payment
Attributes:
  - id: int
  - order_id: int
  - amount: decimal
  - payment_method: enum ['cash', 'card', 'ewallet']
  - status: enum ['pending', 'paid', 'failed', 'refunded']
  - paid_at: datetime
  - created_at: datetime
  - updated_at: datetime

Methods:
  + getOrder(): Order
  + isPaid(): bool
  + isPending(): bool
  + markAsPaid(): void
```

### 1.11 Pickup Entity
```
Class: Pickup
Attributes:
  - id: int
  - order_id: int
  - qr_code: string
  - qr_signature: string
  - status: enum ['waiting', 'ready', 'collected']
  - ready_at: datetime
  - collected_at: datetime
  - created_at: datetime
  - updated_at: datetime

Methods:
  + getOrder(): Order
  + isWaiting(): bool
  + isReady(): bool
  + isCollected(): bool
  + generateQrCode(): string {static}
  + verifySignature(signature: string): bool
```

### 1.12 Notification Entity
```
Class: Notification
Attributes:
  - id: int
  - user_id: int
  - type: string
  - title: string
  - message: text
  - data: json
  - is_read: bool
  - read_at: datetime
  - created_at: datetime
  - updated_at: datetime

Methods:
  + getUser(): User
  + markAsRead(): void
  + isUnread(): bool
```

### 1.13 Wishlist Entity
```
Class: Wishlist
Attributes:
  - id: int
  - user_id: int
  - menu_item_id: int
  - created_at: datetime
  - updated_at: datetime

Methods:
  + getUser(): User
  + getMenuItem(): MenuItem
```

---

## 2. SERVICE LAYER

### 2.1 AuthService
```
Class: AuthService
Attributes:
  - authContext: AuthContext
  - maxAttempts: int = 5
  - decayMinutes: int = 15

Methods:
  + register(data: array): User
  + attemptLogin(email: string, password: string, ip: string): array
  + logout(user: User): void
  + validateToken(token: string): User|null
  + checkRateLimit(email: string, ip: string): bool
  + recordLoginAttempt(email: string, ip: string, success: bool): void
  + enforceSingleDeviceLogin(user: User): void
```

### 2.2 OrderService
```
Class: OrderService
Attributes:
  - orderSubject: OrderSubject

Methods:
  + getUserOrders(userId: int): Collection<Order>
  + getVendorOrders(vendorId: int, status: string): Collection<Order>
  + getOrderById(orderId: int, userId: int): Order
  + updateOrderStatus(order: Order, newStatus: string): bool
  + cancelOrder(order: Order, reason: string): bool
  + generateQrCode(order: Order): string
  + verifyQrCode(orderId: int, signature: string): bool
```

### 2.3 CheckoutService
```
Class: CheckoutService
Attributes:
  - SERVICE_FEE: decimal = 2.00

Methods:
  + getCartSummary(userId: int): array
  + validateCart(userId: int): array
  + applyVoucher(userId: int, code: string, subtotal: decimal): array
  + processCheckout(userId: int, paymentMethod: string, voucherCode: string, notes: string): Order
  + calculateSubtotal(cartItems: Collection): decimal
```

### 2.4 MenuService
```
Class: MenuService
Attributes:
  - menuRepository: MenuItemRepositoryInterface

Methods:
  + getAllMenuItems(): Collection<MenuItem>
  + getMenuItemById(id: int): MenuItem
  + searchMenuItems(query: string): Collection<MenuItem>
  + getMenuItemsByCategory(categoryId: int): Collection<MenuItem>
  + getMenuItemsByVendor(vendorId: int): Collection<MenuItem>
  + getFeaturedItems(): Collection<MenuItem>
  + checkAvailability(itemId: int): array
  + createMenuItem(data: array): MenuItem
  + updateMenuItem(id: int, data: array): MenuItem
  + deleteMenuItem(id: int): bool
```

### 2.5 NotificationService
```
Class: NotificationService

Methods:
  + send(userId: int, type: string, title: string, message: string, data: array): Notification
  + sendBulk(userIds: array, type: string, title: string, message: string): void
  + getNotifications(userId: int, limit: int): Collection<Notification>
  + markAsRead(notificationId: int): void
  + markAllAsRead(userId: int): int
  + getUnreadCount(userId: int): int
  + sendOrderNotification(order: Order, type: string): void
```

### 2.6 SupabaseService
```
Class: SupabaseService
Attributes:
  - supabaseUrl: string
  - supabaseKey: string

Methods:
  + sendOtp(email: string): array
  + verifyOtp(email: string, token: string, type: string): array
```

---

## 3. DESIGN PATTERNS

### 3.1 Strategy Pattern (Authentication)

```
<<interface>>
Interface: AuthStrategyInterface

Methods:
  + authenticate(credentials: array): User|null
  + getStrategyName(): string
```

```
Class: AuthContext
Attributes:
  - strategy: AuthStrategyInterface

Methods:
  + __construct(strategy: AuthStrategyInterface)
  + setStrategy(strategy: AuthStrategyInterface): void
  + authenticate(credentials: array): User|null
```

```
Class: PasswordAuthStrategy
Implements: AuthStrategyInterface

Methods:
  + authenticate(credentials: array): User|null
  + getStrategyName(): string
```

```
Class: TokenAuthStrategy
Implements: AuthStrategyInterface

Methods:
  + authenticate(credentials: array): User|null
  + getStrategyName(): string
```

### 3.2 Factory Pattern (Voucher Discount Calculation)

```
<<interface>>
Interface: VoucherInterface

Methods:
  + calculateDiscount(subtotal: decimal): decimal
  + getType(): string
  + getValue(): decimal
  + isApplicable(subtotal: decimal): bool
  + getDescription(): string
```

```
Class: VoucherFactory

Methods:
  + createFromModel(voucher: Voucher): VoucherInterface {static}
  + create(type: string, value: decimal, minOrder: decimal, maxDiscount: decimal, code: string): VoucherInterface {static}
  + calculateDiscount(voucher: Voucher, subtotal: decimal): decimal {static}
  + isApplicable(voucher: Voucher, subtotal: decimal): bool {static}
  + getDescription(voucher: Voucher): string {static}
```

```
Class: FixedVoucher
Implements: VoucherInterface
Attributes:
  - value: decimal
  - minOrder: decimal
  - code: string

Methods:
  + __construct(value: decimal, minOrder: decimal, code: string)
  + calculateDiscount(subtotal: decimal): decimal
  + getType(): string
  + getValue(): decimal
  + isApplicable(subtotal: decimal): bool
  + getDescription(): string
```

```
Class: PercentageVoucher
Implements: VoucherInterface
Attributes:
  - value: decimal
  - minOrder: decimal
  - maxDiscount: decimal
  - code: string

Methods:
  + __construct(value: decimal, minOrder: decimal, maxDiscount: decimal, code: string)
  + calculateDiscount(subtotal: decimal): decimal
  + getType(): string
  + getValue(): decimal
  + isApplicable(subtotal: decimal): bool
  + getDescription(): string
```

### 3.3 Builder Pattern (Order Construction)

```
Class: OrderBuilder
Attributes:
  - order: Order
  - orderItems: array
  - payment: Payment
  - pickup: Pickup

Methods:
  + setCustomer(userId: int): OrderBuilder
  + setVendor(vendorId: int): OrderBuilder
  + addCartItems(cartItems: Collection): OrderBuilder
  + setPaymentMethod(method: string): OrderBuilder
  + applyVoucher(code: string, discount: decimal): OrderBuilder
  + setNotes(notes: string): OrderBuilder
  + calculateTotals(): OrderBuilder
  + build(): Order
  + reset(): void
```

### 3.4 Observer Pattern (Order Notifications)

```
<<interface>>
Interface: ObserverInterface

Methods:
  + update(subject: SubjectInterface): void
```

```
<<interface>>
Interface: SubjectInterface

Methods:
  + attach(observer: ObserverInterface): void
  + detach(observer: ObserverInterface): void
  + notify(): void
```

```
Class: OrderSubject
Implements: SubjectInterface
Attributes:
  - observers: array
  - order: Order

Methods:
  + __construct(order: Order)
  + attach(observer: ObserverInterface): void
  + detach(observer: ObserverInterface): void
  + notify(): void
  + getOrder(): Order
```

```
Class: NotificationObserver
Implements: ObserverInterface

Methods:
  + update(subject: SubjectInterface): void
  + sendOrderCreatedNotification(order: Order): void
  + sendOrderStatusChangedNotification(order: Order): void
  + sendOrderCompletedNotification(order: Order): void
```

### 3.5 State Pattern (Order Status Management)

```
<<interface>>
Interface: OrderStateInterface

Methods:
  + getStateName(): string
  + canTransitionTo(newState: string): bool
  + confirm(order: Order): bool
  + startPreparing(order: Order): bool
  + markReady(order: Order): bool
  + complete(order: Order): bool
  + cancel(order: Order, reason: string): bool
```

```
Class: AbstractOrderState
Implements: OrderStateInterface
Attributes:
  # allowedTransitions: array

Methods:
  + canTransitionTo(newState: string): bool
  + confirm(order: Order): bool
  + startPreparing(order: Order): bool
  + markReady(order: Order): bool
  + complete(order: Order): bool
  + cancel(order: Order, reason: string): bool
  # transitionTo(order: Order, newState: string): bool
```

```
Class: PendingState
Extends: AbstractOrderState
Attributes:
  # allowedTransitions: array = ['confirmed', 'cancelled']

Methods:
  + getStateName(): string
  + confirm(order: Order): bool
  + cancel(order: Order, reason: string): bool
```

```
Class: ConfirmedState
Extends: AbstractOrderState
Attributes:
  # allowedTransitions: array = ['preparing', 'cancelled']

Methods:
  + getStateName(): string
  + startPreparing(order: Order): bool
  + cancel(order: Order, reason: string): bool
```

```
Class: PreparingState
Extends: AbstractOrderState
Attributes:
  # allowedTransitions: array = ['ready']

Methods:
  + getStateName(): string
  + markReady(order: Order): bool
```

```
Class: ReadyState
Extends: AbstractOrderState
Attributes:
  # allowedTransitions: array = ['completed']

Methods:
  + getStateName(): string
  + complete(order: Order): bool
```

```
Class: OrderStateManager
Attributes:
  - states: array {static}

Methods:
  + getState(order: Order): OrderStateInterface {static}
  + confirm(order: Order): bool {static}
  + startPreparing(order: Order): bool {static}
  + markReady(order: Order): bool {static}
  + complete(order: Order): bool {static}
  + cancel(order: Order, reason: string): bool {static}
  + canTransitionTo(order: Order, newState: string): bool {static}
```

### 3.6 Repository Pattern (Data Access)

```
<<interface>>
Interface: MenuItemRepositoryInterface

Methods:
  + findById(id: int): MenuItem|null
  + getAll(): Collection<MenuItem>
  + getByVendor(vendorId: int): Collection<MenuItem>
  + getByCategory(categoryId: int): Collection<MenuItem>
  + search(query: string): Collection<MenuItem>
  + create(data: array): MenuItem
  + update(id: int, data: array): MenuItem
  + delete(id: int): bool
```

```
Class: EloquentMenuItemRepository
Implements: MenuItemRepositoryInterface

Methods:
  + findById(id: int): MenuItem|null
  + getAll(): Collection<MenuItem>
  + getByVendor(vendorId: int): Collection<MenuItem>
  + getByCategory(categoryId: int): Collection<MenuItem>
  + search(query: string): Collection<MenuItem>
  + create(data: array): MenuItem
  + update(id: int, data: array): MenuItem
  + delete(id: int): bool
```

---

## 4. RELATIONSHIPS

### 4.1 Entity Relationships

```
USER RELATIONSHIPS:
  User "1" -------- "0..1" Vendor              [one-to-one]
  User "1" -------- "0..*" Order               [one-to-many]
  User "1" -------- "0..*" CartItem            [one-to-many]
  User "1" -------- "0..*" Wishlist            [one-to-many]
  User "1" -------- "0..*" Notification        [one-to-many]
  User "0..*" ------ "0..*" Voucher            [many-to-many via UserVoucher]

VENDOR RELATIONSHIPS:
  Vendor "1" -------- "1" User                 [one-to-one]
  Vendor "1" -------- "0..*" MenuItem          [one-to-many]
  Vendor "1" -------- "0..*" Order             [one-to-many]
  Vendor "1" -------- "0..*" Voucher           [one-to-many]

MENU ITEM RELATIONSHIPS:
  MenuItem "0..*" -------- "1" Vendor          [many-to-one]
  MenuItem "0..*" -------- "1" Category        [many-to-one]
  MenuItem "1" -------- "0..*" OrderItem       [one-to-many]
  MenuItem "1" -------- "0..*" CartItem        [one-to-many]
  MenuItem "1" -------- "0..*" Wishlist        [one-to-many]

ORDER RELATIONSHIPS:
  Order "0..*" -------- "1" User               [many-to-one]
  Order "0..*" -------- "1" Vendor             [many-to-one]
  Order "1" -------- "1..*" OrderItem          [one-to-many]
  Order "1" -------- "0..1" Payment            [one-to-one]
  Order "1" -------- "0..1" Pickup             [one-to-one]

VOUCHER RELATIONSHIPS:
  Voucher "0..*" -------- "0..1" Vendor        [many-to-one]
  Voucher "0..*" ------ "0..*" User            [many-to-many via UserVoucher]
```

### 4.2 Design Pattern Relationships

```
STRATEGY PATTERN:
  AuthContext ◆-------- AuthStrategyInterface     [composition]
  PasswordAuthStrategy --|> AuthStrategyInterface [implements]
  TokenAuthStrategy --------|> AuthStrategyInterface [implements]
  AuthService ◆-------- AuthContext              [composition]

FACTORY PATTERN:
  VoucherFactory ........> VoucherInterface       [creates]
  FixedVoucher ---------|> VoucherInterface       [implements]
  PercentageVoucher ----|> VoucherInterface       [implements]

BUILDER PATTERN:
  OrderBuilder ........> Order                    [creates]
  OrderBuilder ........> OrderItem                [creates]
  OrderBuilder ........> Payment                  [creates]
  OrderBuilder ........> Pickup                   [creates]

OBSERVER PATTERN:
  OrderSubject ---------|> SubjectInterface       [implements]
  NotificationObserver -|> ObserverInterface      [implements]
  OrderSubject ◆-------- ObserverInterface        [composition]
  OrderService ◆-------- OrderSubject             [composition]

STATE PATTERN:
  AbstractOrderState ---|> OrderStateInterface    [implements]
  PendingState ---------|> AbstractOrderState     [extends]
  ConfirmedState -------|> AbstractOrderState     [extends]
  PreparingState -------|> AbstractOrderState     [extends]
  ReadyState -----------|> AbstractOrderState     [extends]
  OrderStateManager ...> OrderStateInterface      [uses]

REPOSITORY PATTERN:
  EloquentMenuItemRepository --|> MenuItemRepositoryInterface [implements]
  MenuService ◆-------- MenuItemRepositoryInterface [composition]
```

### 4.3 Service Dependencies

```
SERVICE DEPENDENCIES:
  AuthService ---------> AuthContext              [depends on]
  AuthService ---------> User                     [depends on]
  
  OrderService --------> Order                    [depends on]
  OrderService --------> OrderStateManager        [depends on]
  OrderService --------> OrderSubject             [depends on]
  
  CheckoutService -----> CartItem                 [depends on]
  CheckoutService -----> MenuItem                 [depends on]
  CheckoutService -----> OrderBuilder             [depends on]
  CheckoutService -----> Voucher                  [depends on]
  CheckoutService -----> UserVoucher              [depends on]
  
  MenuService ---------> MenuItemRepositoryInterface [depends on]
  
  NotificationService -> Notification             [depends on]
```

---
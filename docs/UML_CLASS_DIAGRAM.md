# FoodHunter System - UML Class Diagram (Plain Text)

## System Overview
FoodHunter is a Laravel-based university canteen food ordering system implementing multiple design patterns including Strategy, Factory, Builder, Observer, State, and Repository patterns.

---

## 1. MODEL CLASSES (Domain Layer)

### 1.1 User
```
Class: User
Extends: Illuminate\Foundation\Auth\User (Authenticatable)
Uses: HasFactory, Notifiable, HasApiTokens

Attributes:
  - id: int
  - name: string
  - email: string
  - pending_email: string (nullable)
  - password: string (hashed)
  - role: string (customer|vendor)
  - phone: string (nullable)
  - avatar: string (nullable)
  - google_id: string (nullable)
  - email_verified_at: datetime (nullable)
  - remember_token: string (nullable)
  - created_at: datetime
  - updated_at: datetime

Methods:
  + isVendor(): bool
  + isCustomer(): bool
  + vendor(): HasOne<Vendor>
  + orders(): HasMany<Order>
  + cartItems(): HasMany<CartItem>
  + wishlists(): HasMany<Wishlist>
  + notifications(): HasMany<Notification>
  + vouchers(): BelongsToMany<Voucher>
  + unreadNotificationsCount(): int
  # casts(): array
```

### 1.2 Vendor
```
Class: Vendor
Extends: Illuminate\Database\Eloquent\Model
Uses: HasFactory

Attributes:
  - id: int
  - user_id: int (FK)
  - store_name: string
  - slug: string
  - description: string (nullable)
  - phone: string (nullable)
  - logo: string (nullable)
  - banner: string (nullable)
  - is_open: bool
  - is_active: bool
  - min_order_amount: decimal(10,2)
  - avg_prep_time: int
  - total_orders: int
  - created_at: datetime
  - updated_at: datetime

Methods:
  + user(): BelongsTo<User>
  + menuItems(): HasMany<MenuItem>
  + orders(): HasMany<Order>
  + operatingHours(): HasMany<VendorHour>
  + scopeActive($query): Builder
  + scopeOpen($query): Builder
  + isCurrentlyOpen(): bool
  + calculateAvgPrepTime(): int
  + updateStats(): void
  # casts(): array
```

### 1.3 MenuItem
```
Class: MenuItem
Extends: Illuminate\Database\Eloquent\Model
Uses: HasFactory

Attributes:
  - id: int
  - vendor_id: int (FK)
  - category_id: int (FK)
  - name: string
  - slug: string
  - description: string (nullable)
  - price: decimal(10,2)
  - original_price: decimal(10,2) (nullable)
  - image: string (nullable)
  - is_available: bool
  - is_featured: bool
  - prep_time: int (nullable)
  - calories: int (nullable)
  - total_sold: int
  - created_at: datetime
  - updated_at: datetime

Methods:
  + vendor(): BelongsTo<Vendor>
  + category(): BelongsTo<Category>
  + orderItems(): HasMany<OrderItem>
  + cartItems(): HasMany<CartItem>
  + wishlists(): HasMany<Wishlist>
  + scopeAvailable($query): Builder
  + scopeFeatured($query): Builder
  + hasDiscount(): bool
  + getDiscountPercentage(): int|null
  # casts(): array
```

### 1.4 Category
```
Class: Category
Extends: Illuminate\Database\Eloquent\Model
Uses: HasFactory

Attributes:
  - id: int
  - name: string
  - slug: string
  - description: string (nullable)
  - image: string (nullable)
  - is_active: bool
  - sort_order: int
  - created_at: datetime
  - updated_at: datetime

Methods:
  + menuItems(): HasMany<MenuItem>
  + scopeActive($query): Builder
  # casts(): array
```

### 1.5 Order
```
Class: Order
Extends: Illuminate\Database\Eloquent\Model
Uses: HasFactory

Attributes:
  - id: int
  - user_id: int (FK)
  - vendor_id: int (FK)
  - order_number: string
  - subtotal: decimal(10,2)
  - service_fee: decimal(10,2)
  - discount: decimal(10,2)
  - total: decimal(10,2)
  - status: string (pending|confirmed|preparing|ready|completed|cancelled)
  - notes: string (nullable)
  - cancel_reason: string (nullable)
  - confirmed_at: datetime (nullable)
  - ready_at: datetime (nullable)
  - completed_at: datetime (nullable)
  - cancelled_at: datetime (nullable)
  - created_at: datetime
  - updated_at: datetime

Methods:
  + user(): BelongsTo<User>
  + vendor(): BelongsTo<Vendor>
  + items(): HasMany<OrderItem>
  + payment(): HasOne<Payment>
  + pickup(): HasOne<Pickup>
  + isPending(): bool
  + isActive(): bool
  + canBeCancelled(): bool
  + generateOrderNumber(): string {static}
  # casts(): array
```

### 1.6 OrderItem
```
Class: OrderItem
Extends: Illuminate\Database\Eloquent\Model
Uses: HasFactory

Attributes:
  - id: int
  - order_id: int (FK)
  - menu_item_id: int (FK)
  - item_name: string
  - unit_price: decimal(10,2)
  - quantity: int
  - subtotal: decimal(10,2)
  - special_instructions: string (nullable)
  - created_at: datetime
  - updated_at: datetime

Methods:
  + order(): BelongsTo<Order>
  + menuItem(): BelongsTo<MenuItem>
  # casts(): array
```

### 1.7 CartItem
```
Class: CartItem
Extends: Illuminate\Database\Eloquent\Model
Uses: HasFactory

Attributes:
  - id: int
  - user_id: int (FK)
  - menu_item_id: int (FK)
  - quantity: int
  - special_instructions: string (nullable)
  - created_at: datetime
  - updated_at: datetime

Methods:
  + user(): BelongsTo<User>
  + menuItem(): BelongsTo<MenuItem>
  + getSubtotal(): float
```

### 1.8 Payment
```
Class: Payment
Extends: Illuminate\Database\Eloquent\Model
Uses: HasFactory

Attributes:
  - id: int
  - order_id: int (FK)
  - amount: decimal(10,2)
  - method: string (cash|card|ewallet|online_banking)
  - status: string (pending|paid|failed|refunded)
  - transaction_id: string (nullable)
  - paid_at: datetime (nullable)
  - created_at: datetime
  - updated_at: datetime

Methods:
  + order(): BelongsTo<Order>
  + isPaid(): bool
  + isPending(): bool
  # casts(): array
```

### 1.9 Pickup
```
Class: Pickup
Extends: Illuminate\Database\Eloquent\Model
Uses: HasFactory

Attributes:
  - id: int
  - order_id: int (FK)
  - queue_number: int
  - qr_code: string
  - status: string (waiting|ready|collected)
  - ready_at: datetime (nullable)
  - collected_at: datetime (nullable)
  - created_at: datetime
  - updated_at: datetime

Methods:
  + order(): BelongsTo<Order>
  + isWaiting(): bool
  + isReady(): bool
  + isCollected(): bool
  + generateQrCode(orderId: int): string {static}
  # casts(): array
```

### 1.10 Voucher
```
Class: Voucher
Extends: Illuminate\Database\Eloquent\Model
Uses: HasFactory

Attributes:
  - id: int
  - vendor_id: int (FK, nullable)
  - code: string
  - name: string
  - description: string (nullable)
  - type: string (fixed|percentage)
  - value: decimal(10,2)
  - min_order: decimal(10,2) (nullable)
  - max_discount: decimal(10,2) (nullable)
  - usage_limit: int (nullable)
  - usage_count: int
  - per_user_limit: int
  - starts_at: datetime (nullable)
  - expires_at: datetime (nullable)
  - is_active: bool
  - created_at: datetime
  - updated_at: datetime

Methods:
  + vendor(): BelongsTo<Vendor>
  + users(): BelongsToMany<User>
  + isValid(): bool
  + canBeUsedBy(user: User): bool
  + calculateDiscount(orderTotal: float): float
  + scopeActive($query): Builder
  # casts(): array
```

### 1.11 UserVoucher
```
Class: UserVoucher
Extends: Illuminate\Database\Eloquent\Model
Uses: HasFactory

Attributes:
  - id: int
  - user_id: int (FK)
  - voucher_id: int (FK)
  - usage_count: int
  - redeemed_at: datetime (nullable)
  - used_at: datetime (nullable)
  - created_at: datetime
  - updated_at: datetime

Methods:
  + user(): BelongsTo<User>
  + voucher(): BelongsTo<Voucher>
  # casts(): array
```

### 1.12 VendorHour
```
Class: VendorHour
Extends: Illuminate\Database\Eloquent\Model
Uses: HasFactory

Attributes:
  - id: int
  - vendor_id: int (FK)
  - day_of_week: int (0-6)
  - open_time: time
  - close_time: time
  - is_closed: bool
  - created_at: datetime
  - updated_at: datetime

Methods:
  + vendor(): BelongsTo<Vendor>
  + getDayName(dayOfWeek: int): string {static}
  # casts(): array
```

### 1.13 Wishlist
```
Class: Wishlist
Extends: Illuminate\Database\Eloquent\Model
Uses: HasFactory

Attributes:
  - id: int
  - user_id: int (FK)
  - menu_item_id: int (FK)
  - created_at: datetime
  - updated_at: datetime

Methods:
  + user(): BelongsTo<User>
  + menuItem(): BelongsTo<MenuItem>
```

### 1.14 Notification
```
Class: Notification
Extends: Illuminate\Database\Eloquent\Model
Uses: HasFactory

Attributes:
  - id: int
  - user_id: int (FK)
  - type: string
  - title: string
  - message: string
  - data: json (nullable)
  - is_read: bool
  - read_at: datetime (nullable)
  - created_at: datetime
  - updated_at: datetime

Methods:
  + user(): BelongsTo<User>
  + markAsRead(): void
  + scopeUnread($query): Builder
  + scopeRecent($query, limit: int): Builder
  # casts(): array
```

### 1.15 EmailVerification
```
Class: EmailVerification
Extends: Illuminate\Database\Eloquent\Model

Attributes:
  - id: int
  - email: string
  - code: string
  - type: string (signup|email_change)
  - user_id: int (FK, nullable)
  - expires_at: datetime
  - verified_at: datetime (nullable)
  - created_at: datetime
  - updated_at: datetime

Methods:
  + user(): BelongsTo<User>
  + generateCode(): string {static}
  + createForSignup(email: string): EmailVerification {static}
  + createForEmailChange(userId: int, newEmail: string): EmailVerification {static}
  + isExpired(): bool
  + isVerified(): bool
  + verify(): bool
  + findValidCode(email: string, code: string, type: string): EmailVerification|null {static}
```

---

## 2. SERVICE CLASSES (Business Logic Layer)

### 2.1 AuthService
```
Class: AuthService
Namespace: App\Services

Constants:
  - MAX_ATTEMPTS: int = 5
  - LOCKOUT_MINUTES: int = 15

Attributes:
  - authContext: AuthContext

Methods:
  + __construct()
  + attemptLogin(email: string, password: string, ipAddress: string): array
  + validateToken(token: string): User|null
  + register(data: array): User
  - getCacheKey(email: string, ip: string): string
  - getFailedAttempts(email: string, ip: string): int
  - recordFailedAttempt(email: string, ip: string): void
  - clearFailedAttempts(email: string, ip: string): void
  - isLockedOut(email: string, ip: string): bool
  - getLockoutMinutesRemaining(email: string, ip: string): int
```

### 2.2 OrderService
```
Class: OrderService
Namespace: App\Services

Constants:
  - QR_SECRET: string

Methods:
  + getOrderForUser(orderId: int, userId: int): Order|null
  + getOrderForVendor(orderId: int, vendorId: int): Order|null
  + getUserOrders(userId: int, status: string|null): LengthAwarePaginator
  + getVendorOrders(vendorId: int, status: string|null): LengthAwarePaginator
  + updateStatus(order: Order, newStatus: string, reason: string|null): array
  + cancelOrder(order: Order, userId: int, reason: string|null): array
  + generateSignedQrCode(orderId: int, queueNumber: int): string
  + verifyQrCode(qrCode: string): array
  + getOrderStatus(orderId: int): array
  + updateStatusWithLocking(orderId: int, newStatus: string, reason: string|null): array
  - createOrderSubject(order: Order): OrderSubject
```

### 2.3 CheckoutService
```
Class: CheckoutService
Namespace: App\Services

Constants:
  - SERVICE_FEE: float = 2.00

Methods:
  + getCartSummary(userId: int): array
  + validateCart(userId: int): array
  + applyVoucher(userId: int, code: string, subtotal: float): array
  + processCheckout(userId: int, paymentMethod: string, voucherCode: string|null, notes: string|null): array
  - calculateSubtotal(cartItems: Collection): float
```

### 2.4 MenuService
```
Class: MenuService
Namespace: App\Services

Attributes:
  - repository: MenuItemRepositoryInterface

Methods:
  + __construct()
  + getAvailableItems(): Collection
  + getFeaturedItems(limit: int): Collection
  + getByCategory(categoryId: int): Collection
  + getByVendor(vendorId: int): Collection
  + search(query: string): Collection
  + getItem(id: int): MenuItem|null
  + createItem(vendorId: int, data: array): MenuItem
  + updateItem(id: int, data: array): MenuItem|null
  + deleteItem(id: int): bool
  + toggleAvailability(id: int): MenuItem|null
  + checkAvailability(itemId: int): array
  + encodeOutput(value: string|null): string
  + formatItemForDisplay(item: MenuItem): array
  + validateImagePath(path: string|null): string|null
  + getSecureImagePath(requestedPath: string): string|null
  - sanitizeInput(input: string): string
  - sanitizeItemData(data: array): array
```

### 2.5 NotificationService
```
Class: NotificationService
Namespace: App\Services

Methods:
  + send(userId: int, type: string, title: string, message: string, data: array): Notification
  + sendBulk(userIds: array, type: string, title: string, message: string, data: array): int
  + getUserNotifications(userId: int, limit: int): Collection
  + getUnreadNotifications(userId: int): Collection
  + getUnreadCount(userId: int): int
  + markAsRead(notificationId: int, userId: int): bool
  + markAllAsRead(userId: int): int
  + delete(notificationId: int, userId: int): bool
  + generateSecureCode(length: int): string {static}
  + notifyOrderCreated(userId: int, orderId: int): void
  + notifyOrderStatusChanged(userId: int, orderId: int, status: string): void
  + notifyVoucherExpiring(userId: int, voucherCode: string, expiresAt: string): void
  + notifyVendorNewOrder(vendorUserId: int, orderId: int, customerName: string, total: float): void
  + notifyVendorOrderCancelled(vendorUserId: int, orderId: int, customerName: string): void
  + notifyCustomerOrderUpdate(customerId: int, orderId: int, status: string, vendorName: string): void
```

### 2.6 SupabaseService
```
Class: SupabaseService
Namespace: App\Services

Attributes:
  - supabaseUrl: string
  - supabaseKey: string

Methods:
  + __construct()
  + sendOtp(email: string): array
  + verifyOtp(email: string, token: string, type: string): array
```

### 2.7 EmailJSService
```
Class: EmailJSService
Namespace: App\Services

Attributes:
  # publicKey: string
  # privateKey: string
  # serviceId: string
  # templateId: string

Methods:
  + __construct()
  + sendOtp(email: string, code: string, type: string): array
  + sendPasswordResetEmail(user: mixed, resetUrl: string): array
```

### 2.8 SecurityLogService
```
Class: SecurityLogService
Namespace: App\Services

Constants:
  - CHANNEL: string = 'security'

Methods:
  + logAuthAttempt(email: string, success: bool, ip: string|null, reason: string|null): void {static}
  + logAccessDenied(resource: string, userId: int|null, reason: string|null): void {static}
  + logValidationFailure(endpoint: string, errors: array, userId: int|null): void {static}
  + logInvalidToken(ip: string|null): void {static}
  + logTamperingAttempt(type: string, details: array): void {static}
  + logRateLimitExceeded(identifier: string, endpoint: string): void {static}
  + logSessionRevoked(userId: int, email: string, revokedTokens: int, reason: string, ip: string|null): void {static}
  + logPasswordChange(email: string, ip: string|null): void {static}
  + logEmailChange(userId: int, newEmail: string, ip: string|null): void {static}
  - maskEmail(email: string): string {static}
  - log(level: string, message: string, context: array): void {static}
```

---

## 3. DESIGN PATTERNS

### 3.1 STRATEGY PATTERN (Authentication)

```
<<interface>>
Interface: AuthStrategyInterface
Namespace: App\Patterns\Strategy

Methods:
  + authenticate(credentials: array): User|null
  + getStrategyName(): string
```

```
Class: AuthContext
Namespace: App\Patterns\Strategy

Attributes:
  - strategy: AuthStrategyInterface

Methods:
  + __construct(strategy: AuthStrategyInterface)
  + setStrategy(strategy: AuthStrategyInterface): void
  + authenticate(credentials: array): User|null
  + getStrategyName(): string
```

```
Class: PasswordAuthStrategy
Implements: AuthStrategyInterface
Namespace: App\Patterns\Strategy

Methods:
  + authenticate(credentials: array): User|null
  + getStrategyName(): string
```

```
Class: TokenAuthStrategy
Implements: AuthStrategyInterface
Namespace: App\Patterns\Strategy

Methods:
  + authenticate(credentials: array): User|null
  + getStrategyName(): string
```

### 3.2 FACTORY PATTERN (Vouchers)

```
<<interface>>
Interface: VoucherInterface
Namespace: App\Patterns\Factory

Methods:
  + calculateDiscount(subtotal: float): float
  + getType(): string
  + getValue(): float
  + isApplicable(subtotal: float): bool
  + getDescription(): string
```

```
Class: VoucherFactory
Namespace: App\Patterns\Factory

Methods:
  + createFromModel(voucher: Voucher): VoucherInterface {static}
  + create(type: string, value: float, minOrder: float|null, maxDiscount: float|null, code: string): VoucherInterface {static}
  + calculateDiscount(voucher: Voucher, subtotal: float): float {static}
  + isApplicable(voucher: Voucher, subtotal: float): bool {static}
  + getDescription(voucher: Voucher): string {static}
```

```
Class: FixedVoucher
Implements: VoucherInterface
Namespace: App\Patterns\Factory

Attributes:
  - value: float
  - minOrder: float|null
  - code: string

Methods:
  + __construct(value: float, minOrder: float|null, code: string)
  + calculateDiscount(subtotal: float): float
  + getType(): string
  + getValue(): float
  + isApplicable(subtotal: float): bool
  + getDescription(): string
```

```
Class: PercentageVoucher
Implements: VoucherInterface
Namespace: App\Patterns\Factory

Attributes:
  - value: float
  - minOrder: float|null
  - maxDiscount: float|null
  - code: string

Methods:
  + __construct(value: float, minOrder: float|null, maxDiscount: float|null, code: string)
  + calculateDiscount(subtotal: float): float
  + getType(): string
  + getValue(): float
  + isApplicable(subtotal: float): bool
  + getDescription(): string
```

### 3.3 BUILDER PATTERN (Order Creation)

```
Class: OrderBuilder
Namespace: App\Patterns\Builder

Attributes:
  - orderData: array
  - cartItems: Collection
  - paymentData: array
  - voucherCode: string|null
  - discount: float

Methods:
  + __construct()
  + setCustomer(userId: int): OrderBuilder
  + setVendor(vendorId: int): OrderBuilder
  + setCartItems(cartItems: Collection): OrderBuilder
  + setNotes(notes: string|null): OrderBuilder
  + setPaymentMethod(method: string): OrderBuilder
  + applyVoucher(code: string, discount: float): OrderBuilder
  + calculateTotals(): OrderBuilder
  + build(): Order
  + reset(): OrderBuilder
```

### 3.4 OBSERVER PATTERN (Order Events)

```
<<interface>>
Interface: SubjectInterface
Namespace: App\Patterns\Observer

Methods:
  + attach(observer: ObserverInterface): void
  + detach(observer: ObserverInterface): void
  + notify(event: string, data: array): void
```

```
<<interface>>
Interface: ObserverInterface
Namespace: App\Patterns\Observer

Methods:
  + update(subject: SubjectInterface, event: string, data: array): void
```

```
Class: OrderSubject
Implements: SubjectInterface
Namespace: App\Patterns\Observer

Attributes:
  - observers: array
  - order: Order

Methods:
  + __construct(order: Order)
  + attach(observer: ObserverInterface): void
  + detach(observer: ObserverInterface): void
  + notify(event: string, data: array): void
  + getOrder(): Order
  + orderCreated(): void
  + orderStatusChanged(oldStatus: string, newStatus: string): void
  + orderCompleted(): void
```

```
Class: NotificationObserver
Implements: ObserverInterface
Namespace: App\Patterns\Observer

Methods:
  + update(subject: SubjectInterface, event: string, data: array): void
  - handleOrderCreated(data: array): void
  - handleStatusChanged(data: array): void
  - handleOrderCompleted(data: array): void
```

### 3.5 STATE PATTERN (Order Status)

```
<<interface>>
Interface: OrderStateInterface
Namespace: App\Patterns\State

Methods:
  + getStateName(): string
  + canTransitionTo(newState: string): bool
  + confirm(order: Order): bool
  + startPreparing(order: Order): bool
  + markReady(order: Order): bool
  + complete(order: Order): bool
  + cancel(order: Order, reason: string|null): bool
```

```
<<abstract>>
Class: AbstractOrderState
Implements: OrderStateInterface
Namespace: App\Patterns\State

Attributes:
  # allowedTransitions: array

Methods:
  + canTransitionTo(newState: string): bool
  + confirm(order: Order): bool
  + startPreparing(order: Order): bool
  + markReady(order: Order): bool
  + complete(order: Order): bool
  + cancel(order: Order, reason: string|null): bool
  # updateOrderStatus(order: Order, status: string, extra: array): bool
```

```
Class: PendingState
Extends: AbstractOrderState
Namespace: App\Patterns\State

Attributes:
  # allowedTransitions: array = ['confirmed', 'cancelled']

Methods:
  + getStateName(): string
  + confirm(order: Order): bool
  + cancel(order: Order, reason: string|null): bool
```

```
Class: ConfirmedState
Extends: AbstractOrderState
Namespace: App\Patterns\State

Attributes:
  # allowedTransitions: array = ['preparing', 'cancelled']

Methods:
  + getStateName(): string
  + startPreparing(order: Order): bool
  + cancel(order: Order, reason: string|null): bool
```

```
Class: PreparingState
Extends: AbstractOrderState
Namespace: App\Patterns\State

Attributes:
  # allowedTransitions: array = ['ready']

Methods:
  + getStateName(): string
  + markReady(order: Order): bool
```

```
Class: ReadyState
Extends: AbstractOrderState
Namespace: App\Patterns\State

Attributes:
  # allowedTransitions: array = ['completed']

Methods:
  + getStateName(): string
  + complete(order: Order): bool
```

```
Class: OrderStateManager
Namespace: App\Patterns\State

Attributes:
  - states: array {static}

Methods:
  + getState(order: Order): OrderStateInterface {static}
  + confirm(order: Order): bool {static}
  + startPreparing(order: Order): bool {static}
  + markReady(order: Order): bool {static}
  + complete(order: Order): bool {static}
  + cancel(order: Order, reason: string|null): bool {static}
  + canTransitionTo(order: Order, newState: string): bool {static}
```

### 3.6 REPOSITORY PATTERN (Menu Items)

```
<<interface>>
Interface: MenuItemRepositoryInterface
Namespace: App\Patterns\Repository

Methods:
  + findById(id: int): MenuItem|null
  + findBySlug(slug: string): MenuItem|null
  + getAll(): Collection
  + getAvailable(): Collection
  + getFeatured(limit: int): Collection
  + getByCategory(categoryId: int): Collection
  + getByVendor(vendorId: int): Collection
  + search(query: string): Collection
  + create(data: array): MenuItem
  + update(id: int, data: array): MenuItem|null
  + delete(id: int): bool
  + toggleAvailability(id: int): MenuItem|null
```

```
Class: EloquentMenuItemRepository
Implements: MenuItemRepositoryInterface
Namespace: App\Patterns\Repository

Methods:
  + findById(id: int): MenuItem|null
  + findBySlug(slug: string): MenuItem|null
  + getAll(): Collection
  + getAvailable(): Collection
  + getFeatured(limit: int): Collection
  + getByCategory(categoryId: int): Collection
  + getByVendor(vendorId: int): Collection
  + search(query: string): Collection
  + create(data: array): MenuItem
  + update(id: int, data: array): MenuItem|null
  + delete(id: int): bool
  + toggleAvailability(id: int): MenuItem|null
```

---

## 4. CONTROLLER CLASSES (Presentation Layer)

### 4.1 Base Controller
```
<<abstract>>
Class: Controller
Namespace: App\Http\Controllers
```

### 4.2 Web Controllers

```
Class: Web\AuthController
Extends: Controller
Namespace: App\Http\Controllers\Web

Attributes:
  - authService: AuthService

Methods:
  + __construct(authService: AuthService)
  + showLogin(): View
  + login(request: Request): RedirectResponse
  + showRegister(): View
  + register(request: Request): RedirectResponse
  + showVerify(): View
  + verify(request: Request): RedirectResponse
  + resendCode(request: Request): RedirectResponse
  + resendCodeAjax(request: Request): JsonResponse
  + markOtpSent(request: Request): JsonResponse
  + logout(request: Request): RedirectResponse
  + showChangePassword(): View
  + requestPasswordChange(request: Request): JsonResponse
  + showChangePasswordVerify(): View
  + verifyPasswordChange(request: Request): JsonResponse
  + resendPasswordChangeOtp(request: Request): JsonResponse
  + redirectToGoogle(): RedirectResponse
  + handleGoogleCallback(): RedirectResponse
  + showChangeEmail(): View
  + requestEmailChange(request: Request): JsonResponse
  + showChangeEmailVerify(): View
  + verifyEmailChange(request: Request): JsonResponse
  + resendEmailChangeOtp(request: Request): JsonResponse
```

```
Class: Web\CartController
Extends: Controller
Namespace: App\Http\Controllers\Web

Methods:
  + index(): View
  + add(request: Request): Response
  + update(request: Request, cartItem: CartItem): Response
  + remove(cartItem: CartItem): Response
  + clear(): RedirectResponse
  + checkout(): View
  + processCheckout(request: Request): RedirectResponse
  + count(): JsonResponse
  + dropdown(): JsonResponse
  - calculateSummary(cartItems: Collection): array
```

```
Class: Web\OrderController
Extends: Controller
Namespace: App\Http\Controllers\Web

Methods:
  + index(): View
  + show(order: Order): View
  + cancel(order: Order): RedirectResponse
  + reorder(order: Order): RedirectResponse
```

```
Class: Web\MenuController
Extends: Controller
Namespace: App\Http\Controllers\Web

Attributes:
  - menuRepository: MenuItemRepositoryInterface

Methods:
  + __construct()
  + index(request: Request): View
  + show(item: MenuItem): View
  + vendor(vendor: Vendor): View
```

```
Class: Web\VendorController
Extends: Controller
Namespace: App\Http\Controllers\Web

Methods:
  + dashboard(request: Request): Response
  + orders(request: Request): View
  + orderShow(request: Request, order: Order): Response
  + updateOrderStatus(request: Request, order: Order): Response
  + menu(request: Request): View
  + menuShow(request: Request, menuItem: MenuItem): Response
  + menuStore(request: Request): Response
  + menuUpdate(request: Request, menuItem: MenuItem): Response
  + menuDestroy(request: Request, menuItem: MenuItem): Response
  + toggleOpen(): RedirectResponse
  + scanQrCode(): View
  + verifyQrCode(request: Request): Response
  + completePickup(order: Order): RedirectResponse
  + completePickupWithQR(request: Request, order: Order): JsonResponse
  + vouchers(request: Request): View
  + voucherStore(request: Request): Response
  + voucherUpdate(request: Request, voucher: Voucher): Response
  + voucherDestroy(voucher: Voucher): Response
  + voucherToggle(voucher: Voucher): JsonResponse
  + toggleStatus(request: Request): JsonResponse
  + updateHours(request: Request): JsonResponse
```

```
Class: Web\ProfileController
Extends: Controller
Namespace: App\Http\Controllers\Web

Methods:
  + index(): View
  + update(request: Request): RedirectResponse
  + updateAvatar(request: Request): RedirectResponse
  + removeAvatar(): RedirectResponse
  + updatePassword(request: Request): RedirectResponse
  + requestEmailChange(request: Request): RedirectResponse
  + showVerifyEmail(): View
  + verifyEmailChange(request: Request): RedirectResponse
  + cancelEmailChange(): RedirectResponse
```

```
Class: Web\NotificationController
Extends: Controller
Namespace: App\Http\Controllers\Web

Methods:
  + index(): View
  + dropdown(): JsonResponse
  + markAsRead(request: Request, notification: Notification): Response
  + markAllAsRead(request: Request): Response
  + destroy(notification: Notification): RedirectResponse
```

```
Class: Web\VoucherController
Extends: Controller
Namespace: App\Http\Controllers\Web

Methods:
  + index(): View
  + redeem(voucher: Voucher): RedirectResponse
  + myVouchers(): View
  + apply(request: Request): JsonResponse
  + remove(): JsonResponse
```

```
Class: Web\WishlistController
Extends: Controller
Namespace: App\Http\Controllers\Web

Methods:
  + index(): View
  + toggle(request: Request): Response
  + remove(id: int): Response
  + count(): JsonResponse
  + dropdown(): JsonResponse
```

```
Class: Web\ForgotPasswordController
Extends: Controller
Namespace: App\Http\Controllers\Web

Methods:
  + showForgotForm(): View
  + sendResetLink(request: Request): RedirectResponse
  + showVerifyForm(): View
  + verifyOtp(request: Request): RedirectResponse
  + showResetForm(): View
  + resetPassword(request: Request): RedirectResponse
```

```
Class: Web\HomeController
Extends: Controller
Namespace: App\Http\Controllers\Web

Methods:
  + index(): View
```

```
Class: Web\VendorReportController
Extends: Controller
Namespace: App\Http\Controllers\Web

Methods:
  + index(request: Request): View
  - getDateRange(period: string): array
  - getRevenueStatistics(vendorId: int, dateRange: array): array
  - getOrderStatistics(vendorId: int, dateRange: array): array
  - getTopSellingItems(vendorId: int, dateRange: array, limit: int): Collection
  - getSalesChartData(vendorId: int, dateRange: array): array
  - getOrderStatusDistribution(vendorId: int, dateRange: array): array
```

```
Class: Web\SecurityTestController
Extends: Controller
Namespace: App\Http\Controllers\Web

Methods:
  + __construct()
  + index(): View
  + testRateLimiting(request: Request): JsonResponse
  + testGenericErrors(request: Request): JsonResponse
  + testSessionSecurity(): JsonResponse
  + testSqlInjection(request: Request): JsonResponse
  + testXssPrevention(request: Request): JsonResponse
  + testCsrfProtection(request: Request): JsonResponse
  + testPriceValidation(request: Request): JsonResponse
  + testIdorPrevention(request: Request): JsonResponse
  + testQrCodeSignature(): JsonResponse
  + testVoucherGeneration(): JsonResponse
  + testAuditLogging(): JsonResponse
  + testCorsConfig(): JsonResponse
  + clearRateLimit(request: Request): JsonResponse
  + testSingleDeviceLogin(request: Request): JsonResponse
```

### 4.3 API Controllers

```
Class: Api\AuthController
Extends: Controller
Uses: ApiResponse
Namespace: App\Http\Controllers\Api

Attributes:
  - authService: AuthService
  - notificationService: NotificationService

Methods:
  + __construct(authService: AuthService, notificationService: NotificationService)
  + register(request: RegisterRequest): JsonResponse
  + login(request: LoginRequest): JsonResponse
  + logout(request: Request): JsonResponse
  + user(request: Request): JsonResponse
  + validateToken(request: Request): JsonResponse
  - formatUser(user: User): array
```

```
Class: Api\CartController
Extends: Controller
Uses: ApiResponse
Namespace: App\Http\Controllers\Api

Methods:
  + index(request: Request): JsonResponse
  + add(request: AddToCartRequest): JsonResponse
  + update(request: Request, cartItem: CartItem): JsonResponse
  + remove(request: Request, cartItem: CartItem): JsonResponse
  + clear(request: Request): JsonResponse
  + summary(request: Request): JsonResponse
  - formatCartItem(item: CartItem): array
  - calculateSummary(cartItems: Collection): array
```

```
Class: Api\MenuController
Extends: Controller
Uses: ApiResponse
Namespace: App\Http\Controllers\Api

Attributes:
  - menuRepository: MenuItemRepositoryInterface

Methods:
  + __construct()
  + categories(): JsonResponse
  + vendors(request: Request): JsonResponse
  + vendorMenu(vendor: Vendor): JsonResponse
  + featured(): JsonResponse
  + search(request: Request): JsonResponse
  + show(menuItem: MenuItem): JsonResponse
  + checkAvailability(menuItem: MenuItem): JsonResponse
  - formatMenuItem(item: MenuItem, detailed: bool): array
```

```
Class: Api\OrderController
Extends: Controller
Uses: ApiResponse
Namespace: App\Http\Controllers\Api

Methods:
  + index(request: Request): JsonResponse
  + show(request: Request, order: Order): JsonResponse
  + store(request: CreateOrderRequest): JsonResponse
  + cancel(request: Request, order: Order): JsonResponse
  + active(request: Request): JsonResponse
  + status(request: Request, order: Order): JsonResponse
  - formatOrder(order: Order, detailed: bool): array
```

```
Class: Api\NotificationController
Extends: Controller
Uses: ApiResponse
Namespace: App\Http\Controllers\Api

Attributes:
  - notificationService: NotificationService

Methods:
  + __construct(notificationService: NotificationService)
  + index(request: Request): JsonResponse
  + unreadCount(request: Request): JsonResponse
  + dropdown(request: Request): JsonResponse
  + markAsRead(request: Request, id: int): JsonResponse
  + markAllAsRead(request: Request): JsonResponse
  + destroy(request: Request, id: int): JsonResponse
  + send(request: Request): JsonResponse
  - formatNotification(notification: Notification): array
```

```
Class: Api\Vendor\DashboardController
Extends: Controller
Uses: ApiResponse
Namespace: App\Http\Controllers\Api\Vendor

Methods:
  + index(request: Request): JsonResponse
  + toggleOpen(request: Request): JsonResponse
```

```
Class: Api\Vendor\MenuController
Extends: Controller
Uses: ApiResponse
Namespace: App\Http\Controllers\Api\Vendor

Methods:
  + index(request: Request): JsonResponse
  + store(request: Request): JsonResponse
  + update(request: Request, menuItem: MenuItem): JsonResponse
  + destroy(request: Request, menuItem: MenuItem): JsonResponse
  + toggleAvailability(request: Request, menuItem: MenuItem): JsonResponse
  + categories(): JsonResponse
```

```
Class: Api\Vendor\OrderController
Extends: Controller
Uses: ApiResponse
Namespace: App\Http\Controllers\Api\Vendor

Attributes:
  - orderService: OrderService

Methods:
  + __construct()
  + index(request: Request): JsonResponse
  + show(request: Request, order: Order): JsonResponse
  + updateStatus(request: Request, order: Order): JsonResponse
  + pending(request: Request): JsonResponse
  - formatOrder(order: Order, detailed: bool): array
```

---

## 5. MIDDLEWARE CLASSES

```
Class: EnsureUserIsCustomer
Namespace: App\Http\Middleware

Methods:
  + handle(request: Request, next: Closure): Response
```

```
Class: EnsureUserIsVendor
Namespace: App\Http\Middleware

Methods:
  + handle(request: Request, next: Closure): Response
```

---

## 6. REQUEST CLASSES (Form Requests)

```
Class: LoginRequest
Extends: FormRequest
Namespace: App\Http\Requests\Auth

Methods:
  + authorize(): bool
  + rules(): array
```

```
Class: RegisterRequest
Extends: FormRequest
Namespace: App\Http\Requests\Auth

Methods:
  + authorize(): bool
  + rules(): array
  + messages(): array
```

```
Class: AddToCartRequest
Extends: FormRequest
Namespace: App\Http\Requests\Cart

Methods:
  + authorize(): bool
  + rules(): array
```

```
Class: CreateOrderRequest
Extends: FormRequest
Namespace: App\Http\Requests\Order

Methods:
  + authorize(): bool
  + rules(): array
```

---

## 7. MAIL CLASSES

```
Class: EmailChangeOtp
Extends: Mailable
Uses: Queueable, SerializesModels
Namespace: App\Mail

Attributes:
  + user: User
  + otp: string
  + newEmail: string

Methods:
  + __construct(user: User, otp: string, newEmail: string)
  + build(): Mailable
```

```
Class: PasswordChangeOtp
Extends: Mailable
Uses: Queueable, SerializesModels
Namespace: App\Mail

Attributes:
  + user: User
  + otp: string

Methods:
  + __construct(user: User, otp: string)
  + build(): Mailable
```

---

## 8. HELPER CLASSES

```
Class: ImageHelper
Namespace: App\Helpers

Methods:
  + avatar(path: string|null, name: string, updatedAt: string|null): string {static}
  + vendorLogo(path: string|null, name: string): string {static}
  + menuItem(path: string|null): string {static}
  + fallbackUrl(name: string): string {static}
  - getInitials(name: string): string {static}
  - getColorFromName(name: string): string {static}
```

---

## 9. TRAITS

```
<<trait>>
Trait: ApiResponse
Namespace: App\Traits

Methods:
  # generateRequestId(): string
  # successResponse(data: mixed, message: string, status: int): JsonResponse
  # errorResponse(message: string, status: int, error: string|null, errors: mixed): JsonResponse
  # createdResponse(data: mixed, message: string): JsonResponse
  # noContentResponse(): JsonResponse
  # unauthorizedResponse(message: string): JsonResponse
  # forbiddenResponse(message: string): JsonResponse
  # notFoundResponse(message: string): JsonResponse
  # validationErrorResponse(errors: mixed, message: string): JsonResponse
  # tooManyRequestsResponse(message: string): JsonResponse
  # serverErrorResponse(message: string): JsonResponse
  - getErrorCodeFromStatus(status: int): string
```

---

## 10. PROVIDER CLASSES

```
Class: AppServiceProvider
Extends: ServiceProvider
Namespace: App\Providers

Methods:
  + register(): void
  + boot(): void
```

---

## 11. CLASS RELATIONSHIPS

### 11.1 Model Relationships (Entity Relationships)

```
USER RELATIONSHIPS:
  User "1" -------- "0..1" Vendor              [hasOne]
  User "1" -------- "0..*" Order               [hasMany]
  User "1" -------- "0..*" CartItem            [hasMany]
  User "1" -------- "0..*" Wishlist            [hasMany]
  User "1" -------- "0..*" Notification        [hasMany]
  User "0..*" ------ "0..*" Voucher            [belongsToMany via UserVoucher]

VENDOR RELATIONSHIPS:
  Vendor "0..*" -------- "1" User              [belongsTo]
  Vendor "1" -------- "0..*" MenuItem          [hasMany]
  Vendor "1" -------- "0..*" Order             [hasMany]
  Vendor "1" -------- "0..*" VendorHour        [hasMany]
  Vendor "1" -------- "0..*" Voucher           [hasMany]

MENU ITEM RELATIONSHIPS:
  MenuItem "0..*" -------- "1" Vendor          [belongsTo]
  MenuItem "0..*" -------- "1" Category        [belongsTo]
  MenuItem "1" -------- "0..*" OrderItem       [hasMany]
  MenuItem "1" -------- "0..*" CartItem        [hasMany]
  MenuItem "1" -------- "0..*" Wishlist        [hasMany]

CATEGORY RELATIONSHIPS:
  Category "1" -------- "0..*" MenuItem        [hasMany]

ORDER RELATIONSHIPS:
  Order "0..*" -------- "1" User               [belongsTo]
  Order "0..*" -------- "1" Vendor             [belongsTo]
  Order "1" -------- "1..*" OrderItem          [hasMany]
  Order "1" -------- "0..1" Payment            [hasOne]
  Order "1" -------- "0..1" Pickup             [hasOne]

ORDER ITEM RELATIONSHIPS:
  OrderItem "0..*" -------- "1" Order          [belongsTo]
  OrderItem "0..*" -------- "1" MenuItem       [belongsTo]

CART ITEM RELATIONSHIPS:
  CartItem "0..*" -------- "1" User            [belongsTo]
  CartItem "0..*" -------- "1" MenuItem        [belongsTo]

PAYMENT RELATIONSHIPS:
  Payment "0..1" -------- "1" Order            [belongsTo]

PICKUP RELATIONSHIPS:
  Pickup "0..1" -------- "1" Order             [belongsTo]

VOUCHER RELATIONSHIPS:
  Voucher "0..*" -------- "0..1" Vendor        [belongsTo]
  Voucher "0..*" ------ "0..*" User            [belongsToMany via UserVoucher]

USER VOUCHER RELATIONSHIPS:
  UserVoucher "0..*" -------- "1" User         [belongsTo]
  UserVoucher "0..*" -------- "1" Voucher      [belongsTo]

VENDOR HOUR RELATIONSHIPS:
  VendorHour "0..*" -------- "1" Vendor        [belongsTo]

WISHLIST RELATIONSHIPS:
  Wishlist "0..*" -------- "1" User            [belongsTo]
  Wishlist "0..*" -------- "1" MenuItem        [belongsTo]

NOTIFICATION RELATIONSHIPS:
  Notification "0..*" -------- "1" User        [belongsTo]

EMAIL VERIFICATION RELATIONSHIPS:
  EmailVerification "0..*" -------- "0..1" User [belongsTo]
```

### 11.2 Design Pattern Relationships

```
STRATEGY PATTERN:
  AuthContext "1" ◆-------- "1" AuthStrategyInterface     [composition]
  PasswordAuthStrategy ---------|> AuthStrategyInterface  [implements]
  TokenAuthStrategy -----------|> AuthStrategyInterface   [implements]
  AuthService "1" ◆-------- "1" AuthContext              [composition]

FACTORY PATTERN:
  VoucherFactory ..........> VoucherInterface             [creates]
  FixedVoucher -------------|> VoucherInterface           [implements]
  PercentageVoucher --------|> VoucherInterface           [implements]

BUILDER PATTERN:
  OrderBuilder ..........> Order                          [creates]
  OrderBuilder ..........> OrderItem                      [creates]
  OrderBuilder ..........> Payment                        [creates]
  OrderBuilder ..........> Pickup                         [creates]

OBSERVER PATTERN:
  OrderSubject -------------|> SubjectInterface           [implements]
  NotificationObserver -----|> ObserverInterface          [implements]
  OrderSubject "1" ◇-------- "0..*" ObserverInterface    [aggregation]
  OrderService "1" ..........> OrderSubject              [creates]

STATE PATTERN:
  AbstractOrderState -------|> OrderStateInterface        [implements]
  PendingState -------------|> AbstractOrderState         [extends]
  ConfirmedState ----------|> AbstractOrderState          [extends]
  PreparingState ----------|> AbstractOrderState          [extends]
  ReadyState --------------|> AbstractOrderState          [extends]
  OrderStateManager "1" ..........> OrderStateInterface   [uses]

REPOSITORY PATTERN:
  EloquentMenuItemRepository ---|> MenuItemRepositoryInterface [implements]
  MenuService "1" ◆-------- "1" MenuItemRepositoryInterface    [composition]
  MenuController "1" ◆-------- "1" MenuItemRepositoryInterface [composition]
```

### 11.3 Service Dependencies

```
SERVICE LAYER DEPENDENCIES:
  AuthService ---------> AuthContext                      [uses]
  AuthService ---------> User                             [uses]
  AuthService ---------> SecurityLogService               [uses]
  
  OrderService ---------> Order                           [uses]
  OrderService ---------> OrderStateManager               [uses]
  OrderService ---------> OrderSubject                    [uses]
  OrderService ---------> NotificationObserver            [uses]
  
  CheckoutService -------> CartItem                       [uses]
  CheckoutService -------> MenuItem                       [uses]
  CheckoutService -------> OrderBuilder                   [uses]
  CheckoutService -------> UserVoucher                    [uses]
  
  MenuService -----------> EloquentMenuItemRepository     [uses]
  MenuService -----------> MenuItem                       [uses]
  
  NotificationService ---> Notification                   [uses]
  
  SupabaseService -------> (External Supabase API)        [external]
  
  EmailJSService --------> (External EmailJS API)         [external]
  
  SecurityLogService ----> (Laravel Log Facade)           [uses]
```

### 11.4 Controller Dependencies

```
CONTROLLER DEPENDENCIES:
  Web\AuthController ---------> AuthService               [depends on]
  Web\AuthController ---------> SupabaseService           [depends on]
  Web\AuthController ---------> SecurityLogService        [depends on]
  
  Web\CartController ---------> OrderBuilder              [depends on]
  Web\CartController ---------> VoucherFactory            [depends on]
  Web\CartController ---------> NotificationService       [depends on]
  
  Web\OrderController --------> OrderStateManager         [depends on]
  Web\OrderController --------> NotificationService       [depends on]
  
  Web\MenuController ---------> MenuItemRepositoryInterface [depends on]
  
  Web\VendorController -------> OrderStateManager         [depends on]
  Web\VendorController -------> NotificationService       [depends on]
  
  Web\VoucherController ------> VoucherFactory            [depends on]
  
  Web\ProfileController ------> SupabaseService           [depends on]
  
  Web\ForgotPasswordController -> SupabaseService         [depends on]
  
  Api\AuthController ---------> AuthService               [depends on]
  Api\AuthController ---------> NotificationService       [depends on]
  
  Api\OrderController --------> OrderService              [depends on]
  Api\OrderController --------> CheckoutService           [depends on]
  
  Api\MenuController ---------> MenuService               [depends on]
  
  Api\NotificationController -> NotificationService       [depends on]
  
  Api\Vendor\MenuController --> MenuService               [depends on]
  
  Api\Vendor\OrderController -> OrderService              [depends on]
  Api\Vendor\OrderController -> NotificationService       [depends on]
```

---

## 12. NOTATION LEGEND

```
VISIBILITY:
  + public
  - private
  # protected
  ~ package/internal

RELATIONSHIPS:
  -------->     Association (uses/depends on)
  ◆--------    Composition (strong ownership, lifecycle dependency)
  ◇--------    Aggregation (weak ownership)
  ..........>  Creates/Instantiates
  ---------|>  Implements (interface)
  ----------|> Extends (inheritance)
  ----        Line connection

MULTIPLICITY:
  1           Exactly one
  0..1        Zero or one (optional)
  0..*        Zero or more
  1..*        One or more

STEREOTYPES:
  <<interface>>     Interface definition
  <<abstract>>      Abstract class
  <<trait>>         PHP Trait
  <<static>>        Static method/attribute
  {static}          Static member
```

---

## 13. ARCHITECTURE SUMMARY

### Layers:
1. **Presentation Layer**: Controllers (Web & API), Views, Middleware
2. **Business Logic Layer**: Services, Design Patterns (Strategy, Factory, Builder, Observer, State)
3. **Data Access Layer**: Repository Pattern, Eloquent Models
4. **Domain Layer**: Entity Models representing database tables

### Design Patterns Used:
1. **Strategy Pattern** - Authentication (Password vs Token)
2. **Factory Pattern** - Voucher discount calculation (Fixed vs Percentage)
3. **Builder Pattern** - Complex Order object construction
4. **Observer Pattern** - Order event notifications
5. **State Pattern** - Order status workflow management
6. **Repository Pattern** - Data access abstraction for Menu Items

### Security Features (OWASP Compliance):
- Rate limiting for authentication
- Session regeneration
- Single-device login enforcement
- Parameterized queries (via Eloquent)
- Output encoding (XSS protection)
- Path traversal prevention
- Digital signatures for QR codes
- Access control middleware
- Security audit logging

---

*Document generated from FoodHunter codebase analysis*
*Total Classes: 65+ classes/interfaces across all layers*
*Last Updated: Analysis verified against actual codebase with discrepancies documented*

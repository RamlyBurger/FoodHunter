## 3. Design Pattern

### 3.1 Description of Design Pattern

The Factory Pattern is a creational design pattern that provides an interface for creating objects without specifying the exact class to create. It encapsulates object creation logic and returns objects of a common interface based on input parameters. Originally described by the Gang of Four (GoF), the Factory Pattern is one of the most commonly used patterns in enterprise software development.

#### 3.1.1 Pattern Overview and Variants

The Factory Pattern exists in several variants:

- **Simple Factory**: A single factory class with a method that creates objects based on parameters (used in FoodHunter)
- **Factory Method**: Defines an interface for creating objects, letting subclasses decide which class to instantiate
- **Abstract Factory**: Creates families of related objects without specifying concrete classes

In FoodHunter, we use the Simple Factory (also called Static Factory) variant, where static methods on the `VoucherFactory` class create the appropriate voucher objects. This approach is simpler and well-suited for our use case where we have a small number of voucher types.

#### 3.1.2 Application in FoodHunter Voucher System

In the FoodHunter Vendor Management Module, the Factory Pattern is used to create different voucher types with different discount calculation logic. The system supports two primary voucher types:

| Voucher Type | Example | Calculation Logic |
|--------------|---------|-------------------|
| **Fixed** | RM5 off | `discount = min(value, subtotal)` |
| **Percentage** | 10% off (max RM20) | `discount = min(subtotal × rate, maxDiscount)` |

When a voucher is applied during checkout, the VoucherFactory creates the appropriate voucher object (FixedVoucher or PercentageVoucher) based on the voucher type stored in the database. The calling code doesn't need to know which concrete class is being used - it simply works with the `VoucherInterface`.

#### 3.1.3 Why Factory Pattern is Ideal for Voucher Management

The Factory Pattern is ideal for this use case because:

- **Encapsulation**: Voucher creation logic is centralized in one place (`VoucherFactory`). If the creation process changes (e.g., adding validation), only one class needs to be modified.

- **Polymorphism**: Different voucher types share a common interface but have different behaviors. The checkout process can call `calculateDiscount()` on any voucher without knowing its concrete type.

- **Open/Closed Principle (OCP)**: New voucher types can be added without modifying existing code. Future voucher types could include:
  - `BuyOneGetOneFreeVoucher`: Second item free
  - `FreeDeliveryVoucher`: Waive delivery fees
  - `BundleVoucher`: Discount on item combinations
  - `LoyaltyVoucher`: Points-based discounts

- **Single Responsibility Principle (SRP)**: Each voucher class handles only its own discount calculation. `FixedVoucher` doesn't know about percentages, and `PercentageVoucher` doesn't know about fixed amounts.

- **Testability**: Each voucher type can be unit tested independently, and the factory's creation logic can be tested separately.

- **Type Safety**: The factory ensures only valid voucher types are created, preventing runtime errors from invalid type strings.

#### 3.1.4 Pattern Components

The pattern consists of:

- **Product Interface (`VoucherInterface`)**: Defines the contract for all voucher types with methods `calculateDiscount()`, `isApplicable()`, `getDescription()`, `getType()`, and `getValue()`.

- **Concrete Products**: 
  - `FixedVoucher`: Applies a fixed monetary discount (e.g., RM5 off), ensuring the discount doesn't exceed the subtotal
  - `PercentageVoucher`: Applies a percentage discount with an optional maximum cap (e.g., 10% off, max RM20)

- **Factory (`VoucherFactory`)**: Static factory class with `create()` and `createFromModel()` methods that instantiate the appropriate voucher object based on type. Also provides convenience methods like `calculateDiscount()`, `isApplicable()`, and `getDescription()`.

### 3.2 Implementation of Design Pattern

The Factory Pattern is implemented in the `app/Patterns/Factory` directory:

**File: `app/Patterns/Factory/VoucherInterface.php`**
```php
<?php

namespace App\Patterns\Factory;

/**
 * Factory Pattern - Voucher Interface
 * Student 5: Vendor Management Module
 * 
 * Defines the contract for all voucher types.
 */
interface VoucherInterface
{
    public function calculateDiscount(float $subtotal): float;
    public function getType(): string;
    public function getValue(): float;
    public function isApplicable(float $subtotal): bool;
    public function getDescription(): string;
}
```

**File: `app/Patterns/Factory/FixedVoucher.php`**
```php
<?php

namespace App\Patterns\Factory;

/**
 * Factory Pattern - Fixed Discount Voucher
 * Student 5: Vendor Management Module
 * 
 * Concrete product: Fixed amount discount (e.g., RM5 off)
 */
class FixedVoucher implements VoucherInterface
{
    private float $value;
    private ?float $minOrder;
    private string $code;

    public function __construct(float $value, ?float $minOrder = null, string $code = '')
    {
        $this->value = $value;
        $this->minOrder = $minOrder;
        $this->code = $code;
    }

    public function calculateDiscount(float $subtotal): float
    {
        if (!$this->isApplicable($subtotal)) {
            return 0.0;
        }
        // Fixed discount cannot exceed subtotal
        return min($this->value, $subtotal);
    }

    public function getType(): string { return 'fixed'; }
    public function getValue(): float { return $this->value; }

    public function isApplicable(float $subtotal): bool
    {
        return $this->minOrder === null || $subtotal >= $this->minOrder;
    }

    public function getDescription(): string
    {
        $desc = "RM" . number_format($this->value, 2) . " off";
        if ($this->minOrder > 0) {
            $desc .= " (min order RM" . number_format($this->minOrder, 2) . ")";
        }
        return $desc;
    }
}
```

**File: `app/Patterns/Factory/PercentageVoucher.php`**
```php
<?php

namespace App\Patterns\Factory;

/**
 * Factory Pattern - Percentage Discount Voucher
 * Student 5: Vendor Management Module
 * 
 * Concrete product: Percentage discount (e.g., 10% off)
 */
class PercentageVoucher implements VoucherInterface
{
    private float $value; // percentage (e.g., 10 for 10%)
    private ?float $minOrder;
    private ?float $maxDiscount;

    public function calculateDiscount(float $subtotal): float
    {
        if (!$this->isApplicable($subtotal)) {
            return 0.0;
        }

        $discount = $subtotal * ($this->value / 100);

        // Apply max discount cap if set
        if ($this->maxDiscount !== null && $this->maxDiscount > 0) {
            $discount = min($discount, $this->maxDiscount);
        }

        return min($discount, $subtotal);
    }

    public function getDescription(): string
    {
        $desc = number_format($this->value, 0) . "% off";
        if ($this->maxDiscount > 0) {
            $desc .= " (max RM" . number_format($this->maxDiscount, 2) . ")";
        }
        return $desc;
    }
}
```

**File: `app/Patterns/Factory/VoucherFactory.php`**
```php
<?php

namespace App\Patterns\Factory;

use App\Models\Voucher;

/**
 * Factory Pattern - Voucher Factory
 * Student 5: Vendor Management Module
 * 
 * Creates appropriate voucher objects based on voucher type.
 */
class VoucherFactory
{
    public static function createFromModel(Voucher $voucher): VoucherInterface
    {
        return self::create(
            $voucher->type,
            (float) $voucher->value,
            $voucher->min_order ? (float) $voucher->min_order : null,
            $voucher->max_discount ? (float) $voucher->max_discount : null,
            $voucher->code
        );
    }

    public static function create(
        string $type,
        float $value,
        ?float $minOrder = null,
        ?float $maxDiscount = null,
        string $code = ''
    ): VoucherInterface {
        return match ($type) {
            'fixed' => new FixedVoucher($value, $minOrder, $code),
            'percentage' => new PercentageVoucher($value, $minOrder, $maxDiscount, $code),
            default => new FixedVoucher($value, $minOrder, $code),
        };
    }

    public static function calculateDiscount(Voucher $voucher, float $subtotal): float
    {
        $voucherObject = self::createFromModel($voucher);
        return $voucherObject->calculateDiscount($subtotal);
    }

    public static function isApplicable(Voucher $voucher, float $subtotal): bool
    {
        $voucherObject = self::createFromModel($voucher);
        return $voucherObject->isApplicable($subtotal);
    }

    public static function getDescription(Voucher $voucher): string
    {
        $voucherObject = self::createFromModel($voucher);
        return $voucherObject->getDescription();
    }
}
```

### 3.3 Class Diagram

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           Factory Pattern                                    │
│                      Vendor Management Module                                │
└─────────────────────────────────────────────────────────────────────────────┘

                    ┌────────────────────────────────────────┐
                    │      <<interface>>                     │
                    │      VoucherInterface                  │
                    ├────────────────────────────────────────┤
                    │ + calculateDiscount(subtotal): float   │
                    │ + getType(): string                    │
                    │ + getValue(): float                    │
                    │ + isApplicable(subtotal): bool         │
                    │ + getDescription(): string             │
                    └────────────────────────────────────────┘
                                       △
                                       │ implements
                       ┌───────────────┴───────────────┐
                       │                               │
              ┌────────┴────────┐            ┌─────────┴─────────┐
              │  FixedVoucher   │            │ PercentageVoucher │
              ├─────────────────┤            ├───────────────────┤
              │ - value: float  │            │ - value: float    │
              │ - minOrder: float│           │ - minOrder: float │
              │ - code: string  │            │ - maxDiscount: float│
              ├─────────────────┤            │ - code: string    │
              │ + calculateDiscount()│       ├───────────────────┤
              │ + getType(): string│         │ + calculateDiscount()│
              │ + getValue(): float│         │ + getType(): string│
              │ + isApplicable()│            │ + getValue(): float│
              │ + getDescription()│          │ + isApplicable()│
              └─────────────────┘            │ + getDescription()│
                       △                     └───────────────────┘
                       │                               △
                       │ creates                       │ creates
                       │                               │
              ┌────────┴───────────────────────────────┴────────┐
              │                VoucherFactory                    │
              ├─────────────────────────────────────────────────┤
              │ + create(type, value, minOrder, maxDiscount,    │
              │         code): VoucherInterface                 │
              │ + createFromModel(voucher): VoucherInterface    │
              │ + calculateDiscount(voucher, subtotal): float   │
              │ + isApplicable(voucher, subtotal): bool         │
              │ + getDescription(voucher): string               │
              └─────────────────────────────────────────────────┘
```

### 3.4 Justification for Using Factory Pattern

The Factory Pattern was chosen for the Voucher system for the following reasons:

1. **Different Calculation Logic**: Fixed vouchers subtract a flat amount while percentage vouchers calculate a percentage of the subtotal with optional caps.

2. **Encapsulated Creation**: The factory hides the complexity of creating the right voucher type from the calling code.

3. **Extensibility**: New voucher types (e.g., BuyOneGetOneFree, FreeShipping) can be added by creating new classes and updating the factory.

4. **Consistent Interface**: All voucher types implement the same interface, allowing uniform handling regardless of type.

5. **Separation of Concerns**: Cart/checkout code doesn't need to know about different voucher types - it just calls the factory.

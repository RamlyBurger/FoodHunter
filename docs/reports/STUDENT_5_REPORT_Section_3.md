## 3. Design Pattern

### 3.1 Description of Design Pattern

The Factory Pattern is a creational design pattern that provides an interface for creating objects without specifying the exact class to create. It encapsulates object creation logic and returns objects of a common interface based on input parameters.

In the FoodHunter Vendor Management Module, the Factory Pattern is used to create different voucher types with different discount calculation logic. When a voucher is applied during checkout, the VoucherFactory creates the appropriate voucher object (FixedVoucher or PercentageVoucher) based on the voucher type stored in the database.

The Factory Pattern is ideal for this use case because:

- **Encapsulation**: Voucher creation logic is centralized in one place
- **Polymorphism**: Different voucher types share a common interface but have different behaviors
- **Open/Closed**: New voucher types can be added without modifying existing code
- **Single Responsibility**: Each voucher class handles only its own discount calculation

The pattern consists of:

- **Product Interface (`VoucherInterface`)**: Defines `calculateDiscount()`, `isApplicable()`, `getDescription()` methods
- **Concrete Products**: `FixedVoucher` (fixed amount off), `PercentageVoucher` (percentage off)
- **Factory (`VoucherFactory`)**: Creates appropriate voucher object based on type

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

    public function __construct(float $value, ?float $minOrder = null)
    {
        $this->value = $value;
        $this->minOrder = $minOrder;
    }

    public function calculateDiscount(float $subtotal): float
    {
        if (!$this->isApplicable($subtotal)) {
            return 0.0;
        }
        // Fixed discount cannot exceed subtotal
        return min($this->value, $subtotal);
    }

    public function isApplicable(float $subtotal): bool
    {
        return $this->minOrder === null || $subtotal >= $this->minOrder;
    }

    public function getDescription(): string
    {
        return "RM" . number_format($this->value, 2) . " off";
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
    public static function create(string $type, float $value, ?float $minOrder = null, ?float $maxDiscount = null): VoucherInterface
    {
        return match ($type) {
            'fixed' => new FixedVoucher($value, $minOrder),
            'percentage' => new PercentageVoucher($value, $minOrder, $maxDiscount),
            default => new FixedVoucher($value, $minOrder),
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
              ├─────────────────┤            │ - maxDiscount: float│
              │ + calculateDiscount()│       ├───────────────────┤
              │ + getDescription()│          │ + calculateDiscount()│
              └─────────────────┘            │ + getDescription()│
                       △                     └───────────────────┘
                       │                               △
                       │ creates                       │ creates
                       │                               │
              ┌────────┴───────────────────────────────┴────────┐
              │                VoucherFactory                    │
              ├─────────────────────────────────────────────────┤
              │ + create(type, value, minOrder, maxDiscount)    │
              │ + createFromModel(voucher): VoucherInterface    │
              │ + calculateDiscount(voucher, subtotal): float   │
              │ + isApplicable(voucher, subtotal): bool         │
              └─────────────────────────────────────────────────┘
```

### 3.4 Justification for Using Factory Pattern

The Factory Pattern was chosen for the Voucher system for the following reasons:

1. **Different Calculation Logic**: Fixed vouchers subtract a flat amount while percentage vouchers calculate a percentage of the subtotal with optional caps.

2. **Encapsulated Creation**: The factory hides the complexity of creating the right voucher type from the calling code.

3. **Extensibility**: New voucher types (e.g., BuyOneGetOneFree, FreeShipping) can be added by creating new classes and updating the factory.

4. **Consistent Interface**: All voucher types implement the same interface, allowing uniform handling regardless of type.

5. **Separation of Concerns**: Cart/checkout code doesn't need to know about different voucher types - it just calls the factory.

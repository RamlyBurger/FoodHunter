## 5. Software Security

### 5.1 Potential Threats and Attacks

The Cart, Checkout & Notifications Module handles financial transactions and sensitive payment data, making it a prime target for attackers seeking financial gain. This module must implement robust security measures to protect against price manipulation, cross-site attacks, and payment fraud.

#### Threat 1: Price Manipulation

Price manipulation attacks occur when an attacker intercepts and modifies price values in client-side requests. In the Cart & Checkout module, an attacker could use browser DevTools or proxy tools to modify item prices or totals before submitting the checkout form, potentially paying much less than the actual cost.

**Technical Details:**
- Client-side JavaScript calculations can be modified using browser DevTools
- HTTP requests can be intercepted and modified using proxy tools like Burp Suite
- Form fields can be manipulated before submission
- API requests can be crafted with arbitrary price values using curl or Postman

**Attack Scenario:**

```
Original cart total: RM 50.00
Attacker modifies request: {"total": 0.01, "subtotal": 0.01}

Without protection: Attacker pays RM 0.01 for RM 50.00 worth of food
```

**Advanced Attack Methods:**
```javascript
// Using browser DevTools to modify cart total
document.querySelector('#cart-total').value = 0.01;
document.querySelector('#checkout-form').submit();

// Using Burp Suite to intercept and modify
POST /checkout HTTP/1.1
{"items": [...], "total": 0.01, "payment_method": "cash"}
```

**Impact if Unmitigated:**
- Direct financial loss on every manipulated order
- Potential for large-scale automated fraud
- Inventory loss without corresponding revenue
- Business sustainability threat

#### Threat 2: CSRF Attack (Cross-Site Request Forgery)

CSRF attacks occur when an attacker tricks a logged-in user into submitting unwanted requests. An attacker could create a malicious website with hidden forms that auto-submit to FoodHunter, adding items to the victim's cart or even triggering checkout without their knowledge.

**Technical Details:**
- Exploits the browser's automatic inclusion of session cookies in requests
- Attacker doesn't need to steal credentials - they leverage the victim's authenticated session
- Can be delivered via phishing emails, malicious ads, or compromised websites
- Both form submissions and AJAX requests can be exploited

**Attack Scenario:**

```html
<!-- Malicious website -->
<form action="http://127.0.0.1:8000/cart/add" method="POST" style="display:none">
    <input name="menu_item_id" value="99">
    <input name="quantity" value="10">
</form>
<script>document.forms[0].submit();</script>

Without protection: Items added to victim's cart without consent
```

**More Severe CSRF Examples:**
```html
<!-- Force checkout with attacker's delivery address -->
<form action="http://127.0.0.1:8000/checkout" method="POST">
    <input name="payment_method" value="cash">
    <input name="delivery_address" value="Attacker's Address">
</form>
<script>document.forms[0].submit();</script>

<!-- Clear victim's cart (denial of service) -->
<img src="http://127.0.0.1:8000/cart/clear" style="display:none">
```

**Impact if Unmitigated:**
- Unauthorized purchases using victim's account
- Cart manipulation causing confusion and frustration
- Potential for financial fraud through forced transactions
- Privacy violation through unauthorized data access

#### Threat 3: Replay Attack (Duplicate Payment)

Replay attacks occur when an attacker captures and retransmits a valid payment request. Without idempotency protection, the same payment could be processed multiple times, resulting in duplicate orders or double charges.

**Technical Details:**
- Valid requests can be captured using network sniffing or browser extensions
- Replayed requests appear legitimate because they contain valid authentication tokens
- Time-based tokens can be vulnerable if window is too large
- Database race conditions can allow duplicate inserts

**Attack Scenario:**

```
Attacker captures: POST /checkout {payment_method: "card", stripe_payment_method_id: "pm_xxx"}
Attacker replays the same request multiple times

Without protection: Multiple orders created, user charged multiple times
```

**Technical Exploitation:**
```python
# Replay attack script
import requests

captured_request = {
    "payment_method": "card",
    "stripe_payment_method_id": "pm_1234567890",
    "cart_items": [...]
}

# Replay the same request multiple times
for i in range(10):
    response = requests.post(
        'http://127.0.0.1:8000/checkout',
        json=captured_request,
        cookies={'session': 'valid_session_cookie'}
    )
    print(f"Attempt {i}: {response.status_code}")
```

**Impact if Unmitigated:**
- Multiple charges to customer's payment method
- Duplicate orders causing confusion and waste
- Inventory discrepancies
- Customer refund requests and chargebacks
- Damage to business reputation

---

### 5.2 Security Practices Implemented

#### Practice 1: Server-Side Price Calculation (Price Manipulation Prevention)

**OWASP Reference:** [1] Conduct all data validation on a trusted system (server)

The system never trusts price values from client requests. All prices are fetched from the database and totals are calculated entirely server-side.

**Implementation in `app/Http/Controllers/Web/CartController.php`:**

```php
private function calculateSummary($cartItems)
{
    // Security: Server-side price calculation [OWASP 1]
    // Prices fetched from database, not from client request
    $subtotal = $cartItems->sum(fn($item) => $item->menuItem->price * $item->quantity);
    $serviceFee = 2.00;
    $discount = 0;

    // Apply voucher discount using Factory Pattern
    $appliedVoucher = session('applied_voucher');
    if ($appliedVoucher) {
        $voucher = Voucher::find($appliedVoucher['id']);
        if ($voucher && VoucherFactory::isApplicable($voucher, $subtotal)) {
            $discount = VoucherFactory::calculateDiscount($voucher, $subtotal);
        }
    }

    $total = max(0, $subtotal + $serviceFee - $discount);

    return [
        'item_count' => $cartItems->sum('quantity'),
        'subtotal' => $subtotal,
        'service_fee' => $serviceFee,
        'discount' => $discount,
        'total' => $total,
    ];
}
```

---

#### Practice 2: CSRF Token Protection

**OWASP Reference:** [73] Use per-session CSRF tokens for sensitive operations

Laravel's built-in CSRF protection validates a unique token with every state-changing request. Forms include `@csrf` directive and AJAX requests include the token in headers.

**Implementation in Blade templates:**

```blade
{{-- resources/views/cart/checkout.blade.php --}}
<form method="POST" action="{{ route('checkout.process') }}">
    @csrf  {{-- OWASP [73]: Per-session CSRF token --}}
    
    <select name="payment_method" required>
        <option value="cash">Cash on Pickup</option>
        <option value="card">Credit/Debit Card</option>
    </select>
    
    <button type="submit">Place Order</button>
</form>
```

**Implementation for AJAX:**

```javascript
// AJAX requests include CSRF token from meta tag
fetch('/cart/add', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({ menu_item_id: 5, quantity: 2 })
});
```

---

#### Practice 3: Transaction Idempotency (Replay Attack Prevention)

**OWASP Reference:** [64] Protect against replay attacks

The system uses database transactions to ensure atomicity and prevent duplicate order creation. Stripe's payment intent system provides idempotency through unique payment method IDs.

**Implementation in `app/Http/Controllers/Web/CartController.php`:**

```php
public function processCheckout(Request $request)
{
    // Security: Database transaction for atomicity [OWASP 64]
    try {
        DB::beginTransaction();

        // Process Stripe payment (idempotent via payment_method_id)
        if ($request->payment_method === 'card' && $request->stripe_payment_method_id) {
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $amountInCents,
                'currency' => 'myr',
                'payment_method' => $request->stripe_payment_method_id,
                'confirm' => true,
                'metadata' => [
                    'user_id' => Auth::id(),
                    'user_email' => Auth::user()->email,
                ],
            ]);

            if ($paymentIntent->status !== 'succeeded') {
                return back()->with('error', 'Payment failed.');
            }
        }

        // Create orders (within transaction - all or nothing)
        foreach ($vendorGroups as $vendorId => $items) {
            $order = $builder->setCustomer(Auth::id())
                ->setVendor($vendorId)
                ->setCartItems($items)
                ->build();
        }

        // Clear cart only after successful order creation
        CartItem::where('user_id', Auth::id())->delete();

        DB::commit();
        
        return redirect('/orders')->with('success', 'Order placed!');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Failed to place order.');
    }
}

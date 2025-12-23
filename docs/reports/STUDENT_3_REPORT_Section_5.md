## 5. Software Security

### 5.1 Potential Threats and Attacks

#### Threat 1: IDOR (Insecure Direct Object Reference)

IDOR attacks occur when an attacker manipulates object identifiers to access resources belonging to other users. In the Order & Pickup module, an attacker could modify the order ID in API requests to view or cancel orders belonging to other customers.

**Attack Scenario:**

```
Legitimate request: GET /api/orders/123 (user's own order)
Malicious request: GET /api/orders/124 (another user's order)

Without protection: Attacker views other customers' order details, addresses, payment info
```

#### Threat 2: QR Code Tampering

QR code tampering occurs when an attacker modifies or forges QR codes to collect orders that don't belong to them. Without digital signatures, an attacker could generate fake QR codes to fraudulently claim food orders.

**Attack Scenario:**

```
Original QR: {"order_id": 123, "queue": 100}
Forged QR: {"order_id": 456, "queue": 100}

Without protection: Attacker claims someone else's food order
```

#### Threat 3: Race Condition (Double Order Processing)

Race conditions occur when multiple concurrent requests attempt to modify the same resource simultaneously. In order processing, this could result in an order being confirmed twice, status being skipped, or inventory being decremented multiple times.

**Attack Scenario:**

```
Request 1: POST /api/orders/123/confirm (at time T)
Request 2: POST /api/orders/123/confirm (at time T+1ms)

Without protection: Both requests succeed, order confirmed twice, duplicate notifications sent
```

---

### 5.2 Security Practices Implemented

#### Practice 1: Authorization Checks (IDOR Prevention)

**OWASP Reference:** [86] Restrict access to protected functions/resources to authorized users only

The system verifies user ownership before allowing access to any order. Every order operation checks that the requesting user owns the order.

**Implementation in `app/Services/OrderService.php`:**

```php
public function getOrderForUser(int $orderId, int $userId): ?Order
{
    // Security: IDOR Protection - verify ownership
    $order = Order::with(['items', 'payment', 'pickup', 'vendor'])
        ->where('id', $orderId)
        ->where('user_id', $userId)  // Must match authenticated user
        ->first();

    return $order;
}

public function cancelOrder(Order $order, int $userId, ?string $reason = null): array
{
    // Security: Verify ownership before cancellation
    if ($order->user_id !== $userId) {
        return ['success' => false, 'message' => 'Unauthorized.'];
    }

    if (!$order->canBeCancelled()) {
        return ['success' => false, 'message' => 'Order cannot be cancelled at this stage.'];
    }

    $result = OrderStateManager::cancel($order, $reason);
    return ['success' => $result, 'message' => $result ? 'Order cancelled.' : 'Failed.'];
}
```

---

#### Practice 2: Digital Signatures (QR Code Tampering Prevention)

**OWASP Reference:** [104] Use cryptographic controls to verify data integrity

The system generates HMAC-SHA256 signed QR codes. The signature is verified when customers collect their orders, preventing forgery.

**Implementation in `app/Services/OrderService.php`:**

```php
private const QR_SECRET = 'foodhunter_qr_secret_2025';

// Security: Digital Signature for QR Codes
public function generateSignedQrCode(int $orderId, int $queueNumber): string
{
    $data = [
        'order_id' => $orderId,
        'queue' => $queueNumber,
        'timestamp' => time(),
    ];
    
    $payload = json_encode($data);
    $signature = hash_hmac('sha256', $payload, self::QR_SECRET);
    
    return base64_encode($payload . '.' . $signature);
}

public function verifyQrCode(string $qrCode): array
{
    try {
        $decoded = base64_decode($qrCode);
        $parts = explode('.', $decoded, 2);
        
        if (count($parts) !== 2) {
            return ['valid' => false, 'message' => 'Invalid QR format.'];
        }

        [$payload, $signature] = $parts;
        $expectedSignature = hash_hmac('sha256', $payload, self::QR_SECRET);

        // Timing-safe comparison to prevent timing attacks
        if (!hash_equals($expectedSignature, $signature)) {
            return ['valid' => false, 'message' => 'Invalid QR signature.'];
        }

        $data = json_decode($payload, true);
        $order = Order::find($data['order_id']);
        
        if (!$order) {
            return ['valid' => false, 'message' => 'Order not found.'];
        }

        return [
            'valid' => true,
            'order_id' => $data['order_id'],
            'queue_number' => $data['queue'],
            'order' => $order,
        ];
    } catch (\Exception $e) {
        return ['valid' => false, 'message' => 'QR verification failed.'];
    }
}
```

---

#### Practice 3: Database Transactions with Locking (Race Condition Prevention)

**OWASP Reference:** [89] Protect against race conditions

The system uses database transactions with pessimistic locking to prevent concurrent modifications. The `lockForUpdate()` method ensures only one process can modify an order at a time.

**Implementation in `app/Services/OrderService.php`:**

```php
/**
 * Security: Race Condition Protection [OWASP 89]
 * Updates order status with database locking to prevent concurrent modifications.
 * Uses pessimistic locking to ensure only one process can modify the order at a time.
 */
public function updateStatusWithLocking(int $orderId, string $newStatus, ?string $reason = null): array
{
    return DB::transaction(function () use ($orderId, $newStatus, $reason) {
        // Lock the order row for update to prevent race conditions
        $order = Order::where('id', $orderId)->lockForUpdate()->first();

        if (!$order) {
            return ['success' => false, 'message' => 'Order not found.'];
        }

        $oldStatus = $order->status;

        if (!OrderStateManager::canTransitionTo($order, $newStatus)) {
            return [
                'success' => false,
                'message' => "Cannot transition from {$oldStatus} to {$newStatus}.",
            ];
        }

        $result = match ($newStatus) {
            'confirmed' => OrderStateManager::confirm($order),
            'preparing' => OrderStateManager::startPreparing($order),
            'ready' => OrderStateManager::markReady($order),
            'completed' => OrderStateManager::complete($order),
            'cancelled' => OrderStateManager::cancel($order, $reason),
            default => false,
        };

        return [
            'success' => $result,
            'message' => $result ? 'Status updated.' : 'Failed.',
            'new_status' => $order->fresh()->status,
        ];
    });
}

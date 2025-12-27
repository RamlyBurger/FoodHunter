<?php

namespace App\Services;

use App\Models\Order;
use App\Patterns\State\OrderStateManager;
use App\Patterns\Observer\OrderSubject;
use App\Patterns\Observer\NotificationObserver;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * Order Service - Low Nam Lee
 * 
 * Uses State Pattern with security features:
 * - Authorization Checks (IDOR Protection) [OWASP 86]
 * - Digital Signatures for QR Codes (Tampering Protection) [OWASP 104]
 * - Database Transactions with Locking (Race Condition Protection) [OWASP 89]
 */
class OrderService
{
    private const QR_SECRET = 'foodhunter_qr_secret_2025';

    public function getOrderForUser(int $orderId, int $userId): ?Order
    {
        // Security: IDOR Protection - verify ownership
        $order = Order::with(['items', 'payment', 'pickup', 'vendor'])
            ->where('id', $orderId)
            ->where('user_id', $userId)
            ->first();

        return $order;
    }

    public function getOrderForVendor(int $orderId, int $vendorId): ?Order
    {
        // Security: IDOR Protection - verify vendor ownership
        $order = Order::with(['items', 'payment', 'pickup', 'user'])
            ->where('id', $orderId)
            ->where('vendor_id', $vendorId)
            ->first();

        return $order;
    }

    public function getUserOrders(int $userId, ?string $status = null): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Order::where('user_id', $userId)
            ->with(['vendor:id,store_name', 'pickup'])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->paginate(10);
    }

    public function getVendorOrders(int $vendorId, ?string $status = null): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Order::where('vendor_id', $vendorId)
            ->with(['user:id,name,phone', 'items', 'pickup'])
            ->orderBy('created_at', 'desc');

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        return $query->paginate(15);
    }

    public function updateStatus(Order $order, string $newStatus, ?string $reason = null): array
    {
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

        if ($result) {
            // Trigger observers
            $subject = $this->createOrderSubject($order->fresh());
            
            if ($newStatus === 'completed') {
                $subject->orderCompleted();
            } else {
                $subject->orderStatusChanged($oldStatus, $newStatus);
            }
        }

        return [
            'success' => $result,
            'message' => $result ? 'Status updated successfully.' : 'Failed to update status.',
            'new_status' => $order->fresh()->status,
        ];
    }

    public function cancelOrder(Order $order, int $userId, ?string $reason = null): array
    {
        // Security: Verify ownership
        if ($order->user_id !== $userId) {
            return ['success' => false, 'message' => 'Unauthorized.'];
        }

        if (!$order->canBeCancelled()) {
            return ['success' => false, 'message' => 'Order cannot be cancelled at this stage.'];
        }

        $result = OrderStateManager::cancel($order, $reason);

        if ($result) {
            $subject = $this->createOrderSubject($order->fresh());
            $subject->orderStatusChanged($order->status, 'cancelled');
        }

        return [
            'success' => $result,
            'message' => $result ? 'Order cancelled successfully.' : 'Failed to cancel order.',
        ];
    }

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

            if (!hash_equals($expectedSignature, $signature)) {
                return ['valid' => false, 'message' => 'Invalid QR signature.'];
            }

            $data = json_decode($payload, true);
            
            // Verify order exists
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

    public function getOrderStatus(int $orderId): array
    {
        $order = Order::with('pickup')->find($orderId);

        if (!$order) {
            return ['success' => false, 'message' => 'Order not found.'];
        }

        return [
            'success' => true,
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'pickup' => $order->pickup ? [
                    'queue_number' => $order->pickup->queue_number,
                    'status' => $order->pickup->status,
                ] : null,
                'updated_at' => $order->updated_at,
            ],
        ];
    }

    private function createOrderSubject(Order $order): OrderSubject
    {
        $subject = new OrderSubject($order);
        $subject->attach(new NotificationObserver());
        return $subject;
    }

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

            if ($result) {
                $subject = $this->createOrderSubject($order->fresh());
                if ($newStatus === 'completed') {
                    $subject->orderCompleted();
                } else {
                    $subject->orderStatusChanged($oldStatus, $newStatus);
                }
            }

            return [
                'success' => $result,
                'message' => $result ? 'Status updated successfully.' : 'Failed to update status.',
                'new_status' => $order->fresh()->status,
            ];
        });
    }
}

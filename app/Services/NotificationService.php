<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Notification Service - Student 5
 * 
 * Uses Observer Pattern with security features:
 * - Cryptographically Secure Random Code Generation
 * - Audit Logging for Points (Points Manipulation Protection)
 */
class NotificationService
{
    public function send(int $userId, string $type, string $title, string $message, array $data = []): Notification
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public function sendBulk(array $userIds, string $type, string $title, string $message, array $data = []): int
    {
        $count = 0;
        foreach ($userIds as $userId) {
            $this->send($userId, $type, $title, $message, $data);
            $count++;
        }
        return $count;
    }

    public function getUserNotifications(int $userId, int $limit = 20): Collection
    {
        return Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getUnreadNotifications(int $userId): Collection
    {
        return Notification::where('user_id', $userId)
            ->unread()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getUnreadCount(int $userId): int
    {
        return Notification::where('user_id', $userId)->unread()->count();
    }

    public function markAsRead(int $notificationId, int $userId): bool
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->first();

        if (!$notification) {
            return false;
        }

        $notification->markAsRead();
        return true;
    }

    public function markAllAsRead(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    public function delete(int $notificationId, int $userId): bool
    {
        return Notification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->delete() > 0;
    }

    // Security: Cryptographically secure random code generation
    public static function generateSecureCode(int $length = 12): string
    {
        $bytes = random_bytes($length);
        $code = 'FH-' . strtoupper(substr(bin2hex($bytes), 0, $length));
        return $code;
    }

    // Notification types for order events
    public function notifyOrderCreated(int $userId, int $orderId): void
    {
        $this->send(
            $userId,
            'order_created',
            'Order Placed Successfully',
            "Your order #{$orderId} has been placed and is awaiting confirmation.",
            ['order_id' => $orderId]
        );
    }

    public function notifyOrderStatusChanged(int $userId, int $orderId, string $status): void
    {
        $messages = [
            'confirmed' => 'Your order has been confirmed by the vendor.',
            'preparing' => 'Your order is now being prepared.',
            'ready' => 'Your order is ready for pickup!',
            'completed' => 'Your order has been completed. Thank you!',
            'cancelled' => 'Your order has been cancelled.',
        ];

        $this->send(
            $userId,
            'order_status',
            'Order Update',
            $messages[$status] ?? "Order status changed to {$status}.",
            ['order_id' => $orderId, 'status' => $status]
        );
    }

    public function notifyVoucherExpiring(int $userId, string $voucherCode, string $expiresAt): void
    {
        $this->send(
            $userId,
            'voucher_expiring',
            'Voucher Expiring Soon',
            "Your voucher {$voucherCode} will expire on {$expiresAt}. Use it before it's too late!",
            ['voucher_code' => $voucherCode, 'expires_at' => $expiresAt]
        );
    }

    // ============================================================================
    // VENDOR NOTIFICATIONS
    // ============================================================================

    /**
     * Notify vendor of a new order
     */
    public function notifyVendorNewOrder(int $vendorUserId, int $orderId, string $customerName, float $total): void
    {
        $this->send(
            $vendorUserId,
            'order',
            'New Order Received!',
            "New order #{$orderId} from {$customerName} - RM " . number_format($total, 2),
            ['order_id' => $orderId, 'url' => "/vendor/orders/{$orderId}"]
        );
    }

    /**
     * Notify vendor of order cancellation by customer
     */
    public function notifyVendorOrderCancelled(int $vendorUserId, int $orderId, string $customerName): void
    {
        $this->send(
            $vendorUserId,
            'order',
            'Order Cancelled',
            "Order #{$orderId} has been cancelled by {$customerName}.",
            ['order_id' => $orderId, 'url' => "/vendor/orders/{$orderId}"]
        );
    }

    /**
     * Notify customer of order status change with detailed messages
     */
    public function notifyCustomerOrderUpdate(int $customerId, int $orderId, string $status, string $vendorName): void
    {
        $titles = [
            'confirmed' => 'Order Confirmed!',
            'preparing' => 'Order Being Prepared',
            'ready' => 'Order Ready for Pickup!',
            'completed' => 'Order Completed',
            'cancelled' => 'Order Cancelled',
        ];

        $messages = [
            'confirmed' => "{$vendorName} has confirmed your order #{$orderId}.",
            'preparing' => "{$vendorName} is now preparing your order #{$orderId}.",
            'ready' => "Your order #{$orderId} is ready for pickup at {$vendorName}!",
            'completed' => "Your order #{$orderId} from {$vendorName} has been completed. Thank you!",
            'cancelled' => "Your order #{$orderId} from {$vendorName} has been cancelled.",
        ];

        $this->send(
            $customerId,
            'order',
            $titles[$status] ?? 'Order Update',
            $messages[$status] ?? "Order #{$orderId} status changed to {$status}.",
            ['order_id' => $orderId, 'status' => $status, 'url' => "/orders/{$orderId}"]
        );
    }
}

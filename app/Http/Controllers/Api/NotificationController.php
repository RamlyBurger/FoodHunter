<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Notification Controller - Student 5
 * 
 * Design Pattern: Observer Pattern (notifications triggered by events)
 * Security: Cryptographically Secure Codes, Audit Logging
 * 
 * Web Service: Exposes notification sending API for other modules
 */
class NotificationController extends Controller
{
    use ApiResponse;

    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request): JsonResponse
    {
        $notifications = $this->notificationService->getUserNotifications(
            $request->user()->id,
            $request->get('limit', 20)
        );

        return $this->successResponse($notifications->map(fn($n) => $this->formatNotification($n)));
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $count = $this->notificationService->getUnreadCount($request->user()->id);

        return $this->successResponse(['unread_count' => $count]);
    }

    /**
     * Get notifications for dropdown display
     */
    public function dropdown(Request $request): JsonResponse
    {
        $notifications = $this->notificationService->getUserNotifications(
            $request->user()->id,
            5
        );

        $unreadCount = $this->notificationService->getUnreadCount($request->user()->id);

        return $this->successResponse([
            'notifications' => $notifications->map(fn($n) => [
                'id' => $n->id,
                'type' => $n->type,
                'title' => $n->title,
                'message' => $n->message,
                'url' => $n->data['url'] ?? null,
                'read_at' => $n->read_at,
                'time_ago' => $n->created_at->diffForHumans(),
            ]),
            'unread_count' => $unreadCount,
        ]);
    }

    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $result = $this->notificationService->markAsRead($id, $request->user()->id);

        if (!$result) {
            return $this->notFoundResponse('Notification not found');
        }

        return $this->successResponse(null, 'Notification marked as read');
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $count = $this->notificationService->markAllAsRead($request->user()->id);

        return $this->successResponse(['count' => $count], "{$count} notifications marked as read");
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $result = $this->notificationService->delete($id, $request->user()->id);

        if (!$result) {
            return $this->notFoundResponse('Notification not found');
        }

        return $this->successResponse(null, 'Notification deleted');
    }

    /**
     * Web Service: Expose - Send Notification API
     * Other modules (Order, Auth) consume this to send notifications
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'type' => 'required|string|max:50',
            'title' => 'required|string|max:100',
            'message' => 'required|string|max:500',
            'data' => 'nullable|array',
        ]);

        $notification = $this->notificationService->send(
            $request->user_id,
            $request->type,
            $request->title,
            $request->message,
            $request->data ?? []
        );

        return $this->createdResponse([
            'notification_id' => $notification->id,
            'sent_at' => $notification->created_at->toIso8601String(),
        ], 'Notification sent successfully');
    }

    private function formatNotification($notification): array
    {
        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'title' => $notification->title,
            'message' => $notification->message,
            'data' => $notification->data,
            'is_read' => $notification->is_read,
            'read_at' => $notification->read_at,
            'created_at' => $notification->created_at,
        ];
    }
}

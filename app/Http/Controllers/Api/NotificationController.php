<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentNotification;
use App\Models\VendorNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get notifications for authenticated user
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = $request->input('per_page', 20);

        if ($user->role === 'vendor') {
            $notifications = VendorNotification::where('vendor_id', $user->user_id)
                ->with('order')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
        } else {
            $notifications = StudentNotification::where('user_id', $user->user_id)
                ->with('order')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
        }

        $unreadCount = $this->getUnreadNotificationCount($user);

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $notifications->getCollection()->map(function($notification) {
                    return $this->transformNotification($notification);
                }),
                'unread_count' => $unreadCount,
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                ]
            ]
        ]);
    }

    /**
     * Get recent notifications (limited)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recent(Request $request)
    {
        $user = $request->user();
        $limit = $request->input('limit', 10);

        if ($user->role === 'vendor') {
            $notifications = VendorNotification::where('vendor_id', $user->user_id)
                ->with('order')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        } else {
            $notifications = StudentNotification::where('user_id', $user->user_id)
                ->with('order')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        }

        $unreadCount = $this->getUnreadNotificationCount($user);

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $notifications->map(function($notification) {
                    return $this->transformNotification($notification);
                }),
                'unread_count' => $unreadCount,
            ]
        ]);
    }

    /**
     * Get unread notification count
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unreadCount(Request $request)
    {
        $user = $request->user();
        $count = $this->getUnreadNotificationCount($user);

        return response()->json([
            'success' => true,
            'data' => [
                'count' => $count
            ]
        ]);
    }

    /**
     * Mark notification as read
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role === 'vendor') {
            $notification = VendorNotification::where('notification_id', $id)
                ->where('vendor_id', $user->user_id)
                ->first();
        } else {
            $notification = StudentNotification::where('notification_id', $id)
                ->where('user_id', $user->user_id)
                ->first();
        }

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Mark all notifications as read
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'vendor') {
            VendorNotification::where('vendor_id', $user->user_id)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now()
                ]);
        } else {
            StudentNotification::where('user_id', $user->user_id)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now()
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * Delete a notification
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role === 'vendor') {
            $notification = VendorNotification::where('notification_id', $id)
                ->where('vendor_id', $user->user_id)
                ->first();
        } else {
            $notification = StudentNotification::where('notification_id', $id)
                ->where('user_id', $user->user_id)
                ->first();
        }

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully'
        ]);
    }

    /**
     * Clear all notifications
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clear(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'vendor') {
            VendorNotification::where('vendor_id', $user->user_id)->delete();
        } else {
            StudentNotification::where('user_id', $user->user_id)->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'All notifications cleared'
        ]);
    }

    /**
     * Get unread notification count for user
     * 
     * @param \App\Models\User $user
     * @return int
     */
    private function getUnreadNotificationCount($user)
    {
        if ($user->role === 'vendor') {
            return VendorNotification::where('vendor_id', $user->user_id)
                ->where('is_read', false)
                ->count();
        } else {
            return StudentNotification::where('user_id', $user->user_id)
                ->where('is_read', false)
                ->count();
        }
    }

    /**
     * Transform notification data
     * 
     * @param mixed $notification
     * @return array
     */
    private function transformNotification($notification)
    {
        return [
            'notification_id' => $notification->notification_id,
            'type' => $notification->type,
            'title' => $notification->title,
            'message' => $notification->message,
            'is_read' => (bool) $notification->is_read,
            'read_at' => $notification->read_at,
            'created_at' => $notification->created_at,
            'order' => $notification->order ? [
                'order_id' => $notification->order->order_id,
                'status' => $notification->order->status,
            ] : null,
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\StudentNotification;
use App\Models\VendorNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get notifications for authenticated user
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->role === 'vendor') {
            $notifications = VendorNotification::where('vendor_id', $user->user_id)
                ->with('order')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        } else {
            $notifications = StudentNotification::where('user_id', $user->user_id)
                ->with('order')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        }
        
        $unreadCount = $notifications->where('is_read', false)->count();
        
        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }
    
    /**
     * Get unread count
     */
    public function getUnreadCount()
    {
        $user = Auth::user();
        
        if ($user->role === 'vendor') {
            $count = VendorNotification::where('vendor_id', $user->user_id)
                ->where('is_read', false)
                ->count();
        } else {
            $count = StudentNotification::where('user_id', $user->user_id)
                ->where('is_read', false)
                ->count();
        }
        
        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        
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
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        
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
     * Delete notification
     */
    public function delete($id)
    {
        $user = Auth::user();
        
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
            'message' => 'Notification deleted'
        ]);
    }
}

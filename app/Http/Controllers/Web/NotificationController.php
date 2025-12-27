<?php
/**
 * =============================================================================
 * Notification Controller - Lee Song Yan (Cart, Checkout & Notifications Module)
 * =============================================================================
 * 
 * @author     Lee Song Yan
 * @module     Cart, Checkout & Notifications Module
 * @pattern    Observer Pattern (NotificationObserver)
 * 
 * Handles user notification display, marking as read, and deletion.
 * Notifications are triggered by the Observer Pattern from order events.
 * =============================================================================
 */

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    use ApiResponse;
    public function index(Request $request)
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return $this->successResponse([
                'notifications' => $notifications->map(fn($n) => [
                    'id' => $n->id,
                    'type' => $n->type,
                    'title' => $n->title,
                    'message' => $n->message,
                    'url' => $n->data['url'] ?? null,
                    'is_read' => $n->is_read,
                    'read_at' => $n->read_at,
                    'time_ago' => $n->created_at->diffForHumans(),
                    'created_at' => $n->created_at->format('M d, Y H:i'),
                ]),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                    'from' => $notifications->firstItem(),
                    'to' => $notifications->lastItem(),
                ],
                'unread_count' => Notification::where('user_id', Auth::id())->where('is_read', false)->count(),
            ]);
        }

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Get notifications for dropdown display (JSON response for AJAX)
     */
    public function dropdown()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $unreadCount = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();

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

    public function markAsRead(Request $request, Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            if ($request->expectsJson()) {
                return $this->forbiddenResponse('Unauthorized');
            }
            abort(403);
        }

        $notification->markAsRead();

        if ($request->expectsJson()) {
            return $this->successResponse([
                'unread_count' => Notification::where('user_id', Auth::id())->where('is_read', false)->count(),
            ], 'Notification marked as read.');
        }

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead(Request $request)
    {
        $count = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        if ($request->expectsJson()) {
            return $this->successResponse([
                'count' => $count,
                'unread_count' => 0,
            ], 'All notifications marked as read.');
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    public function destroy(Request $request, Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            if ($request->expectsJson()) {
                return $this->forbiddenResponse('Unauthorized');
            }
            abort(403);
        }

        $notification->delete();

        if ($request->expectsJson()) {
            return $this->successResponse([
                'unread_count' => Notification::where('user_id', Auth::id())->where('is_read', false)->count(),
                'total_count' => Notification::where('user_id', Auth::id())->count(),
            ], 'Notification deleted.');
        }

        return back()->with('success', 'Notification deleted.');
    }
}

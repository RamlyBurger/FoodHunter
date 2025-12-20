<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pickup;
use App\Models\StudentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Patterns\State\OrderStateManager;
use App\Patterns\Observer\QueueSubject;
use App\Patterns\Observer\NotificationObserver;
use App\Patterns\Observer\DashboardObserver;
use App\Patterns\Observer\AnalyticsObserver;

class VendorOrderController extends Controller
{
    private QueueSubject $queueSubject;

    public function __construct()
    {
        // Initialize Observer Pattern - Queue Subject
        $this->queueSubject = new QueueSubject();
        $this->queueSubject->attach(new NotificationObserver());
        $this->queueSubject->attach(new DashboardObserver());
        $this->queueSubject->attach(new AnalyticsObserver());
    }
    /**
     * Display vendor's orders with filtering and statistics
     */
    public function index(Request $request)
    {
        $vendor = Auth::user();
        
        // Get filter status
        $statusFilter = $request->input('status', 'all');
        
        // Base query for vendor's orders
        $query = Order::with(['user', 'orderItems.menuItem', 'payment', 'pickup'])
            ->where('vendor_id', $vendor->user_id)
            ->orderBy('created_at', 'desc');
        
        // Apply status filter
        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }
        
        // Get paginated orders
        $orders = $query->paginate(15)->appends(['status' => $statusFilter]);
        
        // Get statistics for today and all time
        $stats = $this->getOrderStatistics($vendor->user_id);
        
        return view('vendor.orders', compact('orders', 'stats', 'statusFilter'));
    }
    
    /**
     * Get order statistics
     */
    private function getOrderStatistics($vendorId)
    {
        // Today's stats
        $today = now()->startOfDay();
        
        $pendingCount = Order::where('vendor_id', $vendorId)
            ->where('status', 'pending')
            ->count();
        
        $acceptedCount = Order::where('vendor_id', $vendorId)
            ->where('status', 'accepted')
            ->count();
            
        $preparingCount = Order::where('vendor_id', $vendorId)
            ->where('status', 'preparing')
            ->count();
            
        $readyCount = Order::where('vendor_id', $vendorId)
            ->where('status', 'ready')
            ->count();
            
        $completedCount = Order::where('vendor_id', $vendorId)
            ->where('status', 'completed')
            ->count();
            
        $todayRevenue = Order::where('vendor_id', $vendorId)
            ->where('status', 'completed')
            ->whereDate('created_at', $today)
            ->sum('total_price');
        
        return [
            'pending' => $pendingCount,
            'accepted' => $acceptedCount,
            'preparing' => $preparingCount,
            'ready' => $readyCount,
            'completed' => $completedCount,
            'today_revenue' => $todayRevenue,
        ];
    }
    
    /**
     * Update order status - Uses State Pattern for state management
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,accepted,preparing,ready,completed,cancelled',
            ]);
            
            $order = Order::where('order_id', $id)
                ->where('vendor_id', Auth::id())
                ->firstOrFail();
            
            $oldStatus = $order->status;
            
            // Use State Pattern to manage order state transitions
            $stateManager = new OrderStateManager($order);
            
            // Handle the current state
            $stateManager->process();
            
            // Move to next state or handle specific transition
            if ($request->status === 'accepted' && $oldStatus === 'pending') {
                $stateManager->moveToNext();
            } elseif ($request->status === 'preparing' && $oldStatus === 'accepted') {
                $stateManager->moveToNext();
            } elseif ($request->status === 'ready' && $oldStatus === 'preparing') {
                $stateManager->moveToNext();
                // Notify observers about queue change
                $this->queueSubject->notify($order->fresh(), 'ready');
            } elseif ($request->status === 'completed' && $oldStatus === 'ready') {
                $stateManager->moveToNext();
                // Notify observers about order collected
                $this->queueSubject->notify($order->fresh(), 'collected');
            } elseif ($request->status === 'cancelled') {
                $success = $stateManager->cancel();
                if ($success) {
                    // Notify observers about cancellation
                    $this->queueSubject->notify($order->fresh(), 'cancelled');
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot cancel order in current state',
                    ], 400);
                }
            } else {
                // Direct status update if not following normal flow
                $order->update(['status' => $request->status]);
            }
            
            Log::info("Order {$id} status updated from {$oldStatus} to {$request->status} using State Pattern");
            
            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'new_status' => $order->fresh()->status,
                'state_description' => $stateManager->getDescription(),
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error updating order status: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status',
            ], 500);
        }
    }
    
    /**
     * Generate queue number for pickup
     */
    private function generateQueueNumber($order)
    {
        // Get the last queue number for today
        $today = now()->startOfDay();
        $lastPickup = Pickup::whereDate('created_at', $today)
            ->orderBy('queue_number', 'desc')
            ->first();
        
        $queueNumber = $lastPickup ? $lastPickup->queue_number + 1 : 100;
        
        // Create or update pickup record
        Pickup::updateOrCreate(
            ['order_id' => $order->order_id],
            [
                'queue_number' => $queueNumber,
                'status' => 'waiting',
            ]
        );
    }
    
    /**
     * Create student notification for order status changes
     */
    private function createStudentNotification($order, $newStatus, $oldStatus)
    {
        $statusMessages = [
            'accepted' => [
                'title' => 'Order Accepted',
                'message' => "Your order #{$order->order_id} has been accepted by the vendor and will be prepared soon.",
                'type' => 'order_accepted'
            ],
            'preparing' => [
                'title' => 'Order Being Prepared',
                'message' => "Your order #{$order->order_id} is now being prepared. We'll notify you when it's ready!",
                'type' => 'order_preparing'
            ],
            'ready' => [
                'title' => 'Order Ready for Pickup!',
                'message' => "Your order #{$order->order_id} is ready! Queue number: {$order->pickup->queue_number}. Please collect your order.",
                'type' => 'order_ready'
            ],
            'completed' => [
                'title' => 'Order Completed',
                'message' => "Thank you! Your order #{$order->order_id} has been completed. We hope you enjoyed your meal!",
                'type' => 'order_completed'
            ],
            'cancelled' => [
                'title' => 'Order Cancelled',
                'message' => "We're sorry, but your order #{$order->order_id} has been cancelled. Please contact the vendor for more information.",
                'type' => 'order_cancelled'
            ],
        ];
        
        if (isset($statusMessages[$newStatus]) && $newStatus !== $oldStatus) {
            StudentNotification::create([
                'user_id' => $order->user_id,
                'order_id' => $order->order_id,
                'type' => $statusMessages[$newStatus]['type'],
                'title' => $statusMessages[$newStatus]['title'],
                'message' => $statusMessages[$newStatus]['message'],
            ]);
        }
    }
    
    /**
     * Get order details
     */
    public function show($id)
    {
        $order = Order::with(['user', 'orderItems.menuItem', 'payment', 'pickup'])
            ->where('order_id', $id)
            ->where('vendor_id', Auth::id())
            ->firstOrFail();
        
        return response()->json([
            'success' => true,
            'order' => $order,
        ]);
    }
    
    /**
     * Accept order (change from pending to accepted)
     */
    public function accept($id)
    {
        try {
            $order = Order::where('order_id', $id)
                ->where('vendor_id', Auth::id())
                ->where('status', 'pending')
                ->firstOrFail();
            
            $oldStatus = $order->status;
            $order->status = 'accepted';
            $order->save();
            
            // Create student notification
            $this->createStudentNotification($order, 'accepted', $oldStatus);
            
            Log::info("Order {$id} accepted by vendor " . Auth::id());
            
            return response()->json([
                'success' => true,
                'message' => 'Order accepted successfully',
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error accepting order: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to accept order',
            ], 500);
        }
    }
    
    /**
     * Reject order
     */
    public function reject(Request $request, $id)
    {
        try {
            $order = Order::where('order_id', $id)
                ->where('vendor_id', Auth::id())
                ->where('status', 'pending')
                ->firstOrFail();
            
            $oldStatus = $order->status;
            $order->status = 'cancelled';
            $order->save();
            
            // Create student notification
            $this->createStudentNotification($order, 'cancelled', $oldStatus);
            
            Log::info("Order {$id} rejected by vendor " . Auth::id());
            
            return response()->json([
                'success' => true,
                'message' => 'Order rejected',
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error rejecting order: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject order',
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Pickup;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\PickupRateLimiterService;
use App\Services\PickupDataProtectionService;
use App\Services\OutputEncodingService;

class PickupController extends Controller
{
    private PickupRateLimiterService $rateLimiter;
    private PickupDataProtectionService $dataProtection;
    private OutputEncodingService $outputEncoder;

    public function __construct(
        PickupRateLimiterService $rateLimiter,
        PickupDataProtectionService $dataProtection,
        OutputEncodingService $outputEncoder
    ) {
        // [94] Initialize rate limiter
        $this->rateLimiter = $rateLimiter;
        
        // [132] Initialize data protection
        $this->dataProtection = $dataProtection;
        
        // [19] Initialize output encoding
        $this->outputEncoder = $outputEncoder;
        
        // Disable client-side caching on pickup pages
        $this->middleware(function ($request, $next) {
            $response = $next($request);
            $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->header('Pragma', 'no-cache');
            $response->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
            return $response;
        });
    }

    /**
     * Get pickup status
     * [94] Rate limited to prevent spam
     * [132] Uses secure caching
     */
    public function getStatus(Request $request, $pickupId)
    {
        $user = Auth::user();
        
        // [94] Check rate limit
        $rateCheck = $this->rateLimiter->canCheckPickupStatus($user->user_id);
        
        if (!$rateCheck['allowed']) {
            return response()->json([
                'success' => false,
                'message' => $rateCheck['message'],
                'retry_after' => $rateCheck['reset_in']
            ], 429);
        }
        
        // [132] Get secure pickup data
        $pickupData = $this->dataProtection->getSecurePickupData($pickupId, $user->user_id);
        
        if (!$pickupData) {
            return response()->json([
                'success' => false,
                'message' => 'Pickup not found or unauthorized access'
            ], 404);
        }
        
        // Record the action
        $this->rateLimiter->recordAction($user->user_id, 'status_check');
        
        // [19] Encode output data
        return response()->json([
            'success' => true,
            'data' => [
                'pickup_id' => $this->outputEncoder->encodeForHtml($pickupData['pickup_id']),
                'status' => $this->outputEncoder->encodeForHtml($pickupData['pickup_status']),
                'queue_position' => $pickupData['queue_position'],
                'estimated_ready_time' => $pickupData['estimated_ready_time'],
                'vendor_name' => $this->outputEncoder->encodeForHtml($pickupData['vendor_name']),
            ],
            'rate_limit' => [
                'remaining' => $rateCheck['remaining'],
                'reset_in' => $rateCheck['reset_in']
            ]
        ]);
    }

    /**
     * Get queue position
     * [94] Rate limited to prevent queue gaming
     */
    public function getQueuePosition(Request $request, $pickupId)
    {
        $user = Auth::user();
        
        // [94] Check rate limit
        $rateCheck = $this->rateLimiter->canCheckQueuePosition($user->user_id);
        
        if (!$rateCheck['allowed']) {
            return response()->json([
                'success' => false,
                'message' => $rateCheck['message'],
                'retry_after' => $rateCheck['reset_in']
            ], 429);
        }
        
        $pickup = Pickup::with('order')
            ->where('pickup_id', $pickupId)
            ->whereHas('order', function($query) use ($user) {
                $query->where('user_id', $user->user_id);
            })
            ->firstOrFail();
        
        // Record the action
        $this->rateLimiter->recordAction($user->user_id, 'queue_check');
        
        // Calculate position in queue
        $position = Pickup::where('order_id', $pickup->order->vendor_id)
            ->where('pickup_status', 'pending')
            ->where('created_at', '<', $pickup->created_at)
            ->count() + 1;
        
        return response()->json([
            'success' => true,
            'data' => [
                'queue_position' => $position,
                'queue_number' => $pickup->queue_number,
                'status' => $this->outputEncoder->encodeForHtml($pickup->pickup_status),
            ],
            'rate_limit' => [
                'remaining' => $rateCheck['remaining']
            ]
        ]);
    }

    /**
     * Update pickup information
     * [94] Rate limited to prevent abuse
     */
    public function updatePickup(Request $request, $pickupId)
    {
        $user = Auth::user();
        
        // [94] Check rate limit
        $rateCheck = $this->rateLimiter->canUpdatePickup($user->user_id);
        
        if (!$rateCheck['allowed']) {
            return back()->with('error', $rateCheck['message']);
        }
        
        $request->validate([
            'pickup_instructions' => 'nullable|string|max:500',
        ]);
        
        $pickup = Pickup::whereHas('order', function($query) use ($user) {
            $query->where('user_id', $user->user_id);
        })->findOrFail($pickupId);
        
        // [132] Encrypt sensitive instructions
        if ($request->pickup_instructions) {
            $encryptedInstructions = $this->dataProtection->encryptInstructions(
                $request->pickup_instructions
            );
            $pickup->update(['pickup_instructions' => $encryptedInstructions]);
        }
        
        // Record the action
        $this->rateLimiter->recordAction($user->user_id, 'update');
        
        // [132] Purge old cache
        $this->dataProtection->purgePickupCache($pickupId, $user->user_id);
        
        return back()->with('success', 'Pickup information updated successfully');
    }

    /**
     * Cancel pickup
     * [94] Rate limited to prevent excessive cancellations
     */
    public function cancelPickup(Request $request, $pickupId)
    {
        $user = Auth::user();
        
        // [94] Check rate limit
        $rateCheck = $this->rateLimiter->canCancelPickup($user->user_id);
        
        if (!$rateCheck['allowed']) {
            return back()->with('error', $rateCheck['message']);
        }
        
        $pickup = Pickup::with('order')
            ->whereHas('order', function($query) use ($user) {
                $query->where('user_id', $user->user_id);
            })
            ->findOrFail($pickupId);
        
        // Can only cancel if not yet ready
        if (in_array($pickup->pickup_status, ['ready', 'completed'])) {
            return back()->with('error', 'Cannot cancel pickup at this stage');
        }
        
        $pickup->update(['pickup_status' => 'cancelled']);
        $pickup->order->update(['status' => 'cancelled']);
        
        // Record the action
        $this->rateLimiter->recordAction($user->user_id, 'cancel');
        
        // [132] Purge pickup data
        $this->dataProtection->purgePickupCache($pickupId, $user->user_id);
        
        return back()->with('success', 'Pickup cancelled successfully');
    }

    /**
     * Get user's rate limit status
     * Admin/debugging endpoint
     */
    public function getRateLimitStatus(Request $request)
    {
        $user = Auth::user();
        $status = $this->rateLimiter->getUserLimitStatus($user->user_id);
        
        return response()->json([
            'success' => true,
            'data' => $status
        ]);
    }
}

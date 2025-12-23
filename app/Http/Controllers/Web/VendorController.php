<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\MenuItem;
use App\Models\Pickup;
use App\Models\Voucher;
use App\Patterns\State\OrderStateManager;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class VendorController extends Controller
{
    public function dashboard(Request $request)
    {
        $vendor = Auth::user()->vendor;

        if (!$vendor) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No vendor profile found.'], 403);
            }
            return redirect('/')->with('error', 'No vendor profile found.');
        }

        $todayOrders = Order::where('vendor_id', $vendor->id)
            ->whereDate('created_at', today())
            ->count();

        $todayRevenue = Order::where('vendor_id', $vendor->id)
            ->whereDate('created_at', today())
            ->where('status', 'completed')
            ->sum('total');

        $pendingOrders = Order::where('vendor_id', $vendor->id)
            ->whereIn('status', ['pending', 'confirmed', 'preparing'])
            ->with(['user', 'items'])
            ->orderBy('created_at', 'asc')
            ->get();

        $readyOrders = Order::where('vendor_id', $vendor->id)
            ->where('status', 'ready')
            ->with(['user', 'pickup'])
            ->get();

        $recentOrders = Order::where('vendor_id', $vendor->id)
            ->with(['user', 'items'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Return JSON for AJAX requests (polling)
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'todayOrders' => $todayOrders,
                'todayRevenue' => (float) $todayRevenue,
                'pendingOrders' => $pendingOrders->count(),
                'readyOrders' => $readyOrders->count(),
            ]);
        }

        return view('vendor.dashboard', compact('vendor', 'todayOrders', 'todayRevenue', 'pendingOrders', 'readyOrders', 'recentOrders'));
    }

    public function orders(Request $request)
    {
        $vendor = Auth::user()->vendor;
        $status = $request->get('status', 'all');
        $perPage = $request->get('per_page', 15);

        $query = Order::where('vendor_id', $vendor->id)
            ->with(['user', 'items.menuItem', 'pickup'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($status !== 'all' && $status) {
            $query->where('status', $status);
        }

        // Search by order number or customer name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function($q2) use ($search) {
                      $q2->where('name', 'like', '%' . $search . '%')
                         ->orWhere('email', 'like', '%' . $search . '%');
                  });
            });
        }

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $orders = $query->paginate($perPage)->withQueryString();

        // Get order stats for the status cards
        $stats = [
            'pending' => Order::where('vendor_id', $vendor->id)->where('status', 'pending')->count(),
            'confirmed' => Order::where('vendor_id', $vendor->id)->where('status', 'confirmed')->count(),
            'preparing' => Order::where('vendor_id', $vendor->id)->where('status', 'preparing')->count(),
            'ready' => Order::where('vendor_id', $vendor->id)->where('status', 'ready')->count(),
            'completed' => Order::where('vendor_id', $vendor->id)->where('status', 'completed')->count(),
            'cancelled' => Order::where('vendor_id', $vendor->id)->where('status', 'cancelled')->count(),
        ];

        return view('vendor.orders', compact('vendor', 'orders', 'status', 'stats'));
    }

    public function orderShow(Request $request, Order $order)
    {
        $vendor = Auth::user()->vendor;

        if ($order->vendor_id !== $vendor->id) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            abort(403);
        }

        $order->load(['user', 'items.menuItem', 'payment', 'pickup']);

        if ($request->ajax() || $request->wantsJson()) {
            // Get customer avatar with proper fallback
            $customerAvatar = null;
            if ($order->user) {
                $avatar = $order->user->avatar;
                if ($avatar) {
                    $customerAvatar = str_starts_with($avatar, 'http') ? $avatar : asset('storage/' . $avatar);
                }
            }
            
            return response()->json([
                'success' => true,
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'total' => (float) $order->total,
                    'customer_name' => $order->user->name ?? 'Unknown',
                    'customer_email' => $order->user->email ?? '',
                    'customer_avatar' => $customerAvatar,
                    'payment_method' => $order->payment->method ?? 'Cash',
                    'queue_number' => $order->pickup->queue_number ?? null,
                    'created_at' => $order->created_at->format('M d, Y h:i A'),
                    'items' => $order->items->map(function($item) {
                        $image = null;
                        if ($item->menuItem && $item->menuItem->image) {
                            $img = $item->menuItem->image;
                            $image = str_starts_with($img, 'http') ? $img : asset('storage/' . $img);
                        }
                        return [
                            'id' => $item->id,
                            'item_name' => $item->item_name,
                            'quantity' => (int) $item->quantity,
                            'price' => (float) $item->price,
                            'unit_price' => (float) ($item->price / max($item->quantity, 1)),
                            'image' => $image,
                        ];
                    }),
                ]
            ]);
        }

        // Redirect to orders page - order details are shown in modal on dashboard
        return redirect()->route('vendor.orders');
    }

    public function updateOrderStatus(Request $request, Order $order)
    {
        $vendor = Auth::user()->vendor;

        if ($order->vendor_id !== $vendor->id) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:confirmed,preparing,ready,completed,cancelled',
        ]);

        $newStatus = $request->status;

        if (!OrderStateManager::canTransitionTo($order, $newStatus)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => "Cannot change status from {$order->status} to {$newStatus}."]);
            }
            return back()->with('error', "Cannot change status from {$order->status} to {$newStatus}.");
        }

        $result = match ($newStatus) {
            'confirmed' => OrderStateManager::confirm($order),
            'preparing' => OrderStateManager::startPreparing($order),
            'ready' => OrderStateManager::markReady($order),
            'completed' => OrderStateManager::complete($order),
            'cancelled' => OrderStateManager::cancel($order, 'Cancelled by vendor'),
            default => false,
        };

        if ($result) {
            // Send notification to customer about status update
            $notificationService = app(NotificationService::class);
            $notificationService->notifyCustomerOrderUpdate(
                $order->user_id,
                $order->id,
                $newStatus,
                $vendor->store_name
            );
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Order status updated to ' . ucfirst($newStatus)]);
            }
            return back()->with('success', 'Order status updated to ' . ucfirst($newStatus));
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => false, 'message' => 'Failed to update order status.']);
        }
        return back()->with('error', 'Failed to update order status.');
    }

    public function menu(Request $request)
    {
        $vendor = Auth::user()->vendor;
        $perPage = $request->get('per_page', 10);
        
        $query = MenuItem::where('vendor_id', $vendor->id)->with('category');
        
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        if ($request->filled('category')) {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('name', $request->category);
            });
        }
        
        if ($request->filled('status')) {
            $query->where('is_available', $request->status);
        }
        
        $items = $query->orderBy('name')->paginate($perPage)->withQueryString();

        return view('vendor.menu', compact('vendor', 'items'));
    }

    public function menuShow(Request $request, MenuItem $menuItem)
    {
        $vendor = Auth::user()->vendor;

        if ($menuItem->vendor_id !== $vendor->id) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            abort(403);
        }

        $menuItem->load('category');
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'item' => [
                    'id' => $menuItem->id,
                    'name' => $menuItem->name,
                    'description' => $menuItem->description,
                    'price' => $menuItem->price,
                    'category' => $menuItem->category->name ?? 'Uncategorized',
                    'is_available' => $menuItem->is_available,
                    'image' => $menuItem->image ? (str_starts_with($menuItem->image, 'http') ? $menuItem->image : asset('storage/' . $menuItem->image)) : null,
                    'prep_time' => $menuItem->prep_time,
                    'total_sold' => $menuItem->total_sold ?? 0,
                    'created_at' => $menuItem->created_at->format('d M Y'),
                    'updated_at' => $menuItem->updated_at->format('d M Y'),
                ]
            ]);
        }

        return redirect()->route('vendor.menu');
    }

    public function menuStore(Request $request)
    {
        $vendor = Auth::user()->vendor;

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'category_id' => 'required|exists:categories,id',
                'price' => 'required|numeric|min:0|max:9999.99',
                'description' => 'nullable|string|max:1000',
                'image' => 'nullable|image|max:2048',
                'is_available' => 'nullable',
            ]);

            $validated['vendor_id'] = $vendor->id;
            
            // Handle is_available from various input types (checkbox returns 0/1, boolean, or string)
            $isAvailable = $request->input('is_available');
            $validated['is_available'] = filter_var($isAvailable, FILTER_VALIDATE_BOOLEAN) || $isAvailable === 1 || $isAvailable === '1';

            if ($request->hasFile('image')) {
                // Security: File Header (Magic Byte) Validation [OWASP 104, 143]
                $file = $request->file('image');
                $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $detectedMime = mime_content_type($file->getRealPath());
                
                if (!in_array($detectedMime, $allowedMimes)) {
                    throw new \Exception('Invalid file type detected. Only JPEG, PNG, GIF, and WebP images are allowed.');
                }
                
                $validated['image'] = $file->store('menu-items', 'public');
            }

            MenuItem::create($validated);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Menu item created successfully.']);
            }

            return redirect()->route('vendor.menu')->with('success', 'Menu item created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'errors' => $e->errors()], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred while creating the item.'], 500);
            }
            throw $e;
        }
    }

    public function menuUpdate(Request $request, MenuItem $menuItem)
    {
        $vendor = Auth::user()->vendor;

        if ($menuItem->vendor_id !== $vendor->id) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403);
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'category_id' => 'required|exists:categories,id',
                'price' => 'required|numeric|min:0|max:9999.99',
                'description' => 'nullable|string|max:1000',
                'image' => 'nullable|image|max:2048',
                'is_available' => 'nullable',
            ]);

            // Handle is_available from various input types (checkbox returns 0/1, boolean, or string)
            $isAvailable = $request->input('is_available');
            $validated['is_available'] = filter_var($isAvailable, FILTER_VALIDATE_BOOLEAN) || $isAvailable === 1 || $isAvailable === '1';

            if ($request->hasFile('image')) {
                // Security: File Header (Magic Byte) Validation [OWASP 104, 143]
                $file = $request->file('image');
                $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $detectedMime = mime_content_type($file->getRealPath());
                
                if (!in_array($detectedMime, $allowedMimes)) {
                    throw new \Exception('Invalid file type detected. Only JPEG, PNG, GIF, and WebP images are allowed.');
                }
                
                if ($menuItem->image && \Storage::disk('public')->exists($menuItem->image)) {
                    \Storage::disk('public')->delete($menuItem->image);
                }
                $validated['image'] = $file->store('menu-items', 'public');
            }

            $menuItem->update($validated);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Menu item updated successfully.']);
            }

            return redirect()->route('vendor.menu')->with('success', 'Menu item updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'errors' => $e->errors()], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred while updating the item.'], 500);
            }
            throw $e;
        }
    }

    public function menuDestroy(Request $request, MenuItem $menuItem)
    {
        $vendor = Auth::user()->vendor;

        if ($menuItem->vendor_id !== $vendor->id) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403);
        }

        try {
            // Delete image if exists
            if ($menuItem->image && \Storage::disk('public')->exists($menuItem->image)) {
                \Storage::disk('public')->delete($menuItem->image);
            }

            $menuItem->delete();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Menu item deleted successfully.']);
            }

            return redirect()->route('vendor.menu')->with('success', 'Menu item deleted successfully.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'An error occurred while deleting the item.'], 500);
            }
            throw $e;
        }
    }

    public function toggleOpen()
    {
        $vendor = Auth::user()->vendor;
        $vendor->update(['is_open' => !$vendor->is_open]);

        $status = $vendor->is_open ? 'open' : 'closed';
        return back()->with('success', "Store is now {$status}.");
    }

    public function scanQrCode()
    {
        $vendor = Auth::user()->vendor;
        if (!$vendor) {
            return redirect('/')->with('error', 'No vendor profile found.');
        }

        return view('vendor.scan', compact('vendor'));
    }

    public function verifyQrCode(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        $vendor = Auth::user()->vendor;
        $qrCode = trim($request->qr_code);

        // Find pickup by QR code
        $pickup = Pickup::where('qr_code', $qrCode)
            ->with(['order.user', 'order.items'])
            ->first();

        if (!$pickup) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Invalid QR code.']);
            }
            return back()->with('error', 'Invalid QR code.');
        }

        $order = $pickup->order;

        // Verify this order belongs to this vendor
        if ($order->vendor_id !== $vendor->id) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'This order is not for your store.']);
            }
            return back()->with('error', 'This order is not for your store.');
        }

        // Check order status
        if ($order->status !== 'ready') {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => "Order is not ready for pickup. Current status: {$order->status}"
                ]);
            }
            return back()->with('error', "Order is not ready for pickup. Current status: {$order->status}");
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->user->name,
                    'total' => $order->total,
                    'items' => $order->items->map(fn($item) => [
                        'name' => $item->item_name,
                        'quantity' => $item->quantity,
                    ]),
                    'queue_number' => $pickup->queue_number,
                ],
            ]);
        }

        return view('vendor.pickup-confirm', compact('vendor', 'order', 'pickup'));
    }

    public function completePickup(Order $order)
    {
        $vendor = Auth::user()->vendor;

        if ($order->vendor_id !== $vendor->id) {
            abort(403);
        }

        if ($order->status !== 'ready') {
            return back()->with('error', 'Order must be ready before completing pickup.');
        }

        // Complete the order
        $result = OrderStateManager::complete($order);

        if ($result) {
            // Update pickup status
            $order->pickup->update([
                'status' => 'collected',
                'collected_at' => now(),
            ]);

            // Notify customer of order completion
            $notificationService = app(NotificationService::class);
            $notificationService->notifyCustomerOrderUpdate(
                $order->user_id,
                $order->id,
                'completed',
                $vendor->store_name
            );

            return redirect()->route('vendor.dashboard')->with('success', "Order #{$order->order_number} completed!");
        }

        return back()->with('error', 'Failed to complete order.');
    }

    /**
     * Complete pickup with QR code verification
     * Security: Validates QR code matches the order's pickup record
     * Consistent with verifyQrCode method for QR handling
     */
    public function completePickupWithQR(Request $request, Order $order)
    {
        try {
            $vendor = Auth::user()->vendor;

            // Verify order belongs to this vendor
            if ($order->vendor_id !== $vendor->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            // Validate request
            $qrCode = $request->input('qr_code');
            if (empty($qrCode) || !is_string($qrCode)) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR code is required'
                ]);
            }

            $qrCode = trim($qrCode);

            // Validate format - QR codes start with PU-
            if (!str_starts_with(strtoupper($qrCode), 'PU-')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid QR code format'
                ]);
            }

            // Verify order status is ready
            if ($order->status !== 'ready') {
                return response()->json([
                    'success' => false,
                    'message' => "Order is not ready for pickup. Current status: {$order->status}"
                ]);
            }

            // Find pickup by QR code (consistent with verifyQrCode method)
            $pickup = Pickup::where('qr_code', $qrCode)->first();
            
            // Also try uppercase comparison for flexibility
            if (!$pickup) {
                $pickup = Pickup::whereRaw('UPPER(qr_code) = ?', [strtoupper($qrCode)])->first();
            }

            if (!$pickup) {
                \Log::warning('Invalid QR code attempt', [
                    'order_id' => $order->id,
                    'vendor_id' => $vendor->id,
                    'attempted_qr' => $qrCode,
                    'ip' => $request->ip(),
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid QR code. Please check the code and try again.'
                ]);
            }

            // Verify the pickup belongs to this order
            if ($pickup->order_id !== $order->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'This QR code does not match the selected order.'
                ]);
            }

            // Complete the order
            $result = OrderStateManager::complete($order);

            if ($result) {
                // Update pickup status
                $pickup->update([
                    'status' => 'collected',
                    'collected_at' => now(),
                ]);

                // Notify customer of order completion (non-blocking)
                try {
                    $notificationService = app(NotificationService::class);
                    $notificationService->notifyCustomerOrderUpdate(
                        $order->user_id,
                        $order->id,
                        'completed',
                        $vendor->store_name
                    );
                } catch (\Exception $notifyError) {
                    // Log but don't fail the completion
                    \Log::warning('Failed to send order completion notification', [
                        'order_id' => $order->id,
                        'error' => $notifyError->getMessage()
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Order completed successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to complete order. Please try again.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Complete pickup error', [
                'order_id' => $order->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }

    public function vouchers(Request $request)
    {
        $vendor = Auth::user()->vendor;
        if (!$vendor) {
            return redirect('/')->with('error', 'No vendor profile found.');
        }

        $query = Voucher::where('vendor_id', $vendor->id);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true)->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                });
            } elseif ($request->status === 'expired') {
                $query->where('expires_at', '<', now());
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $vouchers = $query->orderBy('created_at', 'desc')->paginate(10);

        // Stats
        $stats = [
            'total' => Voucher::where('vendor_id', $vendor->id)->count(),
            'active' => Voucher::where('vendor_id', $vendor->id)->where('is_active', true)->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })->count(),
            'total_usage' => Voucher::where('vendor_id', $vendor->id)->sum('usage_count'),
        ];

        return view('vendor.vouchers', compact('vendor', 'vouchers', 'stats'));
    }

    public function voucherStore(Request $request)
    {
        $vendor = Auth::user()->vendor;
        if (!$vendor) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            // Log incoming request for debugging
            \Log::info('Voucher creation request', ['data' => $request->all()]);

            // Clean up empty strings to null for nullable fields
            $data = $request->all();
            foreach (['code', 'min_order', 'max_discount', 'usage_limit', 'per_user_limit', 'expires_at'] as $field) {
                if (isset($data[$field]) && $data[$field] === '') {
                    $data[$field] = null;
                }
            }
            $request->merge($data);

            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'code' => 'nullable|string|max:20|unique:vouchers,code',
                'type' => 'required|in:fixed,percentage',
                'value' => 'required|numeric|min:0.01|max:100000',
                'min_order' => 'nullable|numeric|min:0',
                'max_discount' => 'nullable|numeric|min:0',
                'usage_limit' => 'nullable|integer|min:1',
                'per_user_limit' => 'nullable|integer|min:1',
                'expires_at' => 'nullable|date|after:now',
            ], [
                'name.required' => 'Voucher name is required.',
                'type.required' => 'Please select a discount type.',
                'value.required' => 'Discount value is required.',
                'value.min' => 'Discount value must be at least 0.01.',
                'value.max' => 'Discount value is too large.',
                'code.unique' => 'This voucher code is already taken.',
                'expires_at.after' => 'Expiry date must be in the future.',
                'usage_limit.integer' => 'Usage limit must be a whole number.',
                'per_user_limit.integer' => 'Per user limit must be a whole number.',
            ]);

            $code = $request->code ?: strtoupper(Str::random(8));

            $voucher = Voucher::create([
                'vendor_id' => $vendor->id,
                'code' => strtoupper($code),
                'name' => $request->name,
                'description' => $request->description,
                'type' => $request->type,
                'value' => $request->value,
                'min_order' => $request->min_order ?? 0,
                'max_discount' => $request->max_discount,
                'usage_limit' => $request->usage_limit,
                'per_user_limit' => $request->per_user_limit ?? 1,
                'expires_at' => $request->expires_at,
                'is_active' => true,
            ]);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Voucher created successfully!', 'voucher' => $voucher]);
            }

            return back()->with('success', 'Voucher created successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                $errors = $e->errors();
                $firstError = collect($errors)->flatten()->first();
                return response()->json([
                    'success' => false, 
                    'message' => $firstError,
                    'errors' => $errors
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Voucher creation failed', ['error' => $e->getMessage()]);
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to create voucher. Please try again.'], 500);
            }
            return back()->with('error', 'Failed to create voucher.');
        }
    }

    public function voucherUpdate(Request $request, Voucher $voucher)
    {
        $vendor = Auth::user()->vendor;
        if (!$vendor || $voucher->vendor_id !== $vendor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            // Clean up empty strings to null for nullable fields
            $data = $request->all();
            foreach (['min_order', 'max_discount', 'usage_limit', 'per_user_limit', 'expires_at'] as $field) {
                if (isset($data[$field]) && $data[$field] === '') {
                    $data[$field] = null;
                }
            }
            $request->merge($data);

            $request->validate([
                'name' => 'required|string|max:100',
                'type' => 'required|in:fixed,percentage',
                'value' => 'required|numeric|min:0.01|max:100000',
                'min_order' => 'nullable|numeric|min:0',
                'max_discount' => 'nullable|numeric|min:0',
                'usage_limit' => 'nullable|integer|min:1',
                'per_user_limit' => 'nullable|integer|min:1',
                'expires_at' => 'nullable|date',
            ], [
                'name.required' => 'Voucher name is required.',
                'type.required' => 'Please select a discount type.',
                'value.required' => 'Discount value is required.',
                'value.min' => 'Discount value must be at least 0.01.',
            ]);

            $voucher->update([
                'name' => $request->name,
                'description' => $request->description,
                'type' => $request->type,
                'value' => $request->value,
                'min_order' => $request->min_order ?? 0,
                'max_discount' => $request->max_discount,
                'usage_limit' => $request->usage_limit,
                'per_user_limit' => $request->per_user_limit ?? 1,
                'expires_at' => $request->expires_at,
            ]);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Voucher updated successfully!']);
            }

            return back()->with('success', 'Voucher updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                $errors = $e->errors();
                $firstError = collect($errors)->flatten()->first();
                return response()->json([
                    'success' => false, 
                    'message' => $firstError,
                    'errors' => $errors
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Voucher update failed', ['error' => $e->getMessage()]);
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to update voucher.'], 500);
            }
            return back()->with('error', 'Failed to update voucher.');
        }
    }

    public function voucherDestroy(Voucher $voucher)
    {
        $vendor = Auth::user()->vendor;
        if (!$vendor || $voucher->vendor_id !== $vendor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $voucher->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Voucher deleted successfully!']);
        }

        return back()->with('success', 'Voucher deleted successfully!');
    }

    public function voucherToggle(Voucher $voucher)
    {
        $vendor = Auth::user()->vendor;
        if (!$vendor || $voucher->vendor_id !== $vendor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $voucher->update(['is_active' => !$voucher->is_active]);

        return response()->json([
            'success' => true,
            'message' => $voucher->is_active ? 'Voucher activated!' : 'Voucher deactivated!',
            'is_active' => $voucher->is_active,
        ]);
    }

    public function toggleStatus(Request $request)
    {
        $vendor = Auth::user()->vendor;
        if (!$vendor) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'is_open' => 'required|boolean',
        ]);

        $vendor->update(['is_open' => $request->is_open]);

        return response()->json([
            'success' => true,
            'message' => $request->is_open ? 'Your store is now open!' : 'Your store is now closed.',
            'is_open' => $vendor->is_open,
        ]);
    }

    public function updateHours(Request $request)
    {
        $vendor = Auth::user()->vendor;
        if (!$vendor) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'hours' => 'required|array',
            'hours.*.open_time' => 'required|date_format:H:i',
            'hours.*.close_time' => 'required|date_format:H:i',
            'hours.*.is_closed' => 'required|boolean',
        ]);

        $hours = $request->input('hours', []);
        
        foreach ($hours as $dayOfWeek => $data) {
            // Handle is_closed as boolean (true/false) from JSON
            $isClosed = filter_var($data['is_closed'] ?? false, FILTER_VALIDATE_BOOLEAN);
            
            \App\Models\VendorHour::updateOrCreate(
                ['vendor_id' => $vendor->id, 'day_of_week' => (int)$dayOfWeek],
                [
                    'open_time' => $data['open_time'] ?? '09:00',
                    'close_time' => $data['close_time'] ?? '21:00',
                    'is_closed' => $isClosed,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Operating hours updated successfully!',
        ]);
    }
}

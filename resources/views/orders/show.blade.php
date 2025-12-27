{{--
|==============================================================================
| Order Detail Page - Low Nam Lee (Order & Pickup Module)
|==============================================================================
|
| @author     Low Nam Lee
| @module     Order & Pickup Module
| @pattern    State Pattern (OrderStateManager)
|
| Displays order details with QR code for pickup and status polling.
| Includes cancel and reorder functionality via AJAX.
|==============================================================================
--}}

@extends('layouts.app')

@section('title', 'Order ' . $order->order_number)

@push('styles')
<style>
    .receipt-container {
        max-width: 600px;
        margin: 0 auto;
    }
    .receipt-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    .receipt-header {
        background: linear-gradient(135deg, var(--primary-color), #e67e00);
        color: #fff;
        padding: 24px;
        text-align: center;
    }
    .receipt-header h1 {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 4px;
    }
    .receipt-body {
        padding: 24px;
    }
    .queue-number {
        background: #fff;
        border-radius: 12px;
        padding: 16px;
        text-align: center;
        margin: -40px 24px 0;
        position: relative;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .queue-number .number {
        font-size: 3rem;
        font-weight: 800;
        color: var(--primary-color);
        line-height: 1;
    }
    .queue-number .label {
        font-size: 0.85rem;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .status-banner {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 20px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
    }
    .status-banner.pending { background: rgba(255,204,0,0.15); color: #CC9900; }
    .status-banner.confirmed { background: rgba(0,122,255,0.15); color: #007AFF; }
    .status-banner.preparing { background: rgba(255,149,0,0.15); color: #FF9500; }
    .status-banner.ready { background: rgba(52,199,89,0.15); color: #34C759; }
    .status-banner.completed { background: rgba(52,199,89,0.15); color: #34C759; }
    .status-banner.cancelled { background: rgba(255,59,48,0.15); color: #FF3B30; }
    .order-info-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px dashed #e0e0e0;
        font-size: 0.9rem;
    }
    .order-info-row:last-child {
        border-bottom: none;
    }
    .order-info-row .label {
        color: #666;
    }
    .order-info-row .value {
        font-weight: 500;
        color: #333;
    }
    .section-title {
        font-size: 0.8rem;
        font-weight: 600;
        color: #999;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 2px solid #f0f0f0;
    }
    .item-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #f5f5f5;
        text-decoration: none;
        color: inherit;
        transition: background 0.2s;
    }
    .item-row:hover {
        background: #fafafa;
        margin: 0 -12px;
        padding-left: 12px;
        padding-right: 12px;
    }
    .item-row:last-child {
        border-bottom: none;
    }
    .item-row img {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        object-fit: cover;
    }
    .item-row .item-info {
        flex: 1;
    }
    .item-row .item-name {
        font-weight: 600;
        font-size: 0.95rem;
        color: #333;
    }
    .item-row .item-qty {
        font-size: 0.85rem;
        color: #666;
    }
    .item-row .item-price {
        font-weight: 600;
        color: #333;
        text-align: right;
    }
    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        font-size: 0.9rem;
    }
    .summary-row.total {
        font-size: 1.1rem;
        font-weight: 700;
        padding-top: 12px;
        margin-top: 8px;
        border-top: 2px solid #333;
    }
    .summary-row.total .value {
        color: var(--primary-color);
    }
    .qr-section {
        text-align: center;
        padding: 20px;
        background: #fafafa;
        border-radius: 12px;
        margin-top: 20px;
    }
    .qr-section img {
        border-radius: 8px;
        border: 4px solid #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    /* Horizontal Timeline - Delivery Style */
    .delivery-timeline {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        position: relative;
        padding: 20px 0;
        overflow-x: auto;
    }
    .delivery-timeline::before {
        content: '';
        position: absolute;
        top: 34px;
        left: 24px;
        right: 24px;
        height: 3px;
        background: #e0e0e0;
        z-index: 0;
    }
    .timeline-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        position: relative;
        z-index: 1;
        flex: 1;
        min-width: 80px;
    }
    .timeline-step .step-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        background: #f0f0f0;
        color: #999;
        border: 3px solid #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 8px;
        transition: all 0.3s ease;
    }
    .timeline-step.done .step-icon {
        background: linear-gradient(135deg, #34C759, #2FB350);
        color: #fff;
    }
    .timeline-step.active .step-icon {
        background: linear-gradient(135deg, #FF6B35, #e55a2b);
        color: #fff;
        animation: pulse 1.5s infinite;
    }
    .timeline-step.cancelled .step-icon {
        background: linear-gradient(135deg, #FF3B30, #dc3545);
        color: #fff;
    }
    @keyframes pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(255, 107, 53, 0.4); }
        50% { box-shadow: 0 0 0 10px rgba(255, 107, 53, 0); }
    }
    .timeline-step .step-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #999;
        margin-bottom: 2px;
    }
    .timeline-step.done .step-label,
    .timeline-step.active .step-label {
        color: #333;
    }
    .timeline-step .step-time {
        font-size: 0.65rem;
        color: #bbb;
    }
    .timeline-step.done .step-time {
        color: #34C759;
    }
    /* Progress line fill */
    .timeline-progress {
        position: absolute;
        top: 34px;
        left: 24px;
        height: 3px;
        background: linear-gradient(90deg, #34C759, #2FB350);
        z-index: 0;
        transition: width 0.5s ease;
    }
    .action-buttons {
        display: flex;
        gap: 12px;
        margin-top: 24px;
    }
    .action-buttons .btn {
        flex: 1;
        padding: 12px;
        border-radius: 10px;
        font-weight: 600;
    }
    .order-completed-msg {
        background: linear-gradient(135deg, #34C759, #2FB350);
        color: #fff;
        padding: 16px;
        border-radius: 12px;
        text-align: center;
        margin-top: 20px;
    }
    .order-completed-msg .msg {
        font-size: 1.5rem;
        font-weight: 700;
    }
</style>
@endpush

@section('content')
<div class="page-content">
<div class="container py-4">
    <div class="receipt-container">
        <!-- Back Button -->
        <a href="{{ url('/orders') }}" class="btn btn-link text-muted mb-3 ps-0">
            <i class="bi bi-arrow-left me-1"></i> Back to Orders
        </a>

        <div class="receipt-card">
            <!-- Header -->
            <div class="receipt-header">
                <h1>{{ $order->vendor->store_name ?? 'FoodHunter' }}</h1>
                <p class="mb-0 opacity-75">{{ $order->order_number }}</p>
            </div>

            <!-- Queue Number -->
            @if($order->pickup)
            <div class="queue-number">
                <div class="label">Queue Number</div>
                <div class="number">#{{ $order->pickup->queue_number }}</div>
                @if(in_array($order->status, ['pending', 'confirmed', 'preparing', 'ready']))
                <small class="text-muted">Show this when collecting your order</small>
                @endif
            </div>
            @endif

            <div class="receipt-body" style="{{ $order->pickup ? 'padding-top: 16px;' : '' }}">
                <!-- Status -->
                <div class="text-center mb-4">
                    <span class="status-banner {{ $order->status }}">
                        @if($order->status === 'pending')
                        <i class="bi bi-hourglass-split"></i>
                        @elseif($order->status === 'confirmed')
                        <i class="bi bi-check-circle"></i>
                        @elseif($order->status === 'preparing')
                        <i class="bi bi-fire"></i>
                        @elseif($order->status === 'ready')
                        <i class="bi bi-bell"></i>
                        @elseif($order->status === 'completed')
                        <i class="bi bi-bag-check"></i>
                        @elseif($order->status === 'cancelled')
                        <i class="bi bi-x-circle"></i>
                        @endif
                        {{ ucfirst($order->status) }}
                    </span>
                </div>

                <!-- Order Info -->
                <div class="mb-4">
                    <div class="section-title">Order Details</div>
                    <div class="order-info-row">
                        <span class="label">Date</span>
                        <span class="value">{{ $order->created_at->format('d M Y, h:i A') }}</span>
                    </div>
                    @if($order->payment)
                    <div class="order-info-row">
                        <span class="label">Payment</span>
                        <span class="value">{{ ucfirst($order->payment->method) }} 
                            <span class="badge bg-{{ $order->payment->status === 'paid' ? 'success' : 'warning' }}" style="font-size: 0.7rem;">{{ ucfirst($order->payment->status) }}</span>
                        </span>
                    </div>
                    @endif
                    @if($order->notes)
                    <div class="order-info-row">
                        <span class="label">Notes</span>
                        <span class="value">{{ $order->notes }}</span>
                    </div>
                    @endif
                </div>

                <!-- Items -->
                <div class="mb-4">
                    <div class="section-title">Items Ordered</div>
                    @foreach($order->items as $item)
                    <a href="{{ $item->menuItem ? url('/menu/' . $item->menuItem->id) : '#' }}" class="item-row">
                        <img src="{{ $item->menuItem ? \App\Helpers\ImageHelper::menuItem($item->menuItem->image) : '' }}" 
                             alt="{{ $item->item_name }}"
                             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($item->item_name) }}&background=f3f4f6&color=9ca3af&size=100&font-size=0.33&bold=true';">
                        <div class="item-info">
                            <div class="item-name">{{ $item->item_name }}</div>
                            <div class="item-qty">x{{ $item->quantity }} @ RM {{ number_format((float)$item->unit_price, 2) }}</div>
                            @if($item->special_instructions)
                            <small class="text-info"><i class="bi bi-chat-text"></i> {{ $item->special_instructions }}</small>
                            @endif
                        </div>
                        <div class="item-price">RM {{ number_format((float)$item->subtotal, 2) }}</div>
                    </a>
                    @endforeach
                </div>

                <!-- Summary -->
                <div class="mb-4">
                    <div class="section-title">Payment Summary</div>
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>RM {{ number_format((float)$order->subtotal, 2) }}</span>
                    </div>
                    <div class="summary-row">
                        <span>Service Fee</span>
                        <span>RM {{ number_format((float)$order->service_fee, 2) }}</span>
                    </div>
                    @if($order->discount > 0)
                    <div class="summary-row text-success">
                        <span>Discount</span>
                        <span>-RM {{ number_format((float)$order->discount, 2) }}</span>
                    </div>
                    @endif
                    <div class="summary-row total">
                        <span>Total</span>
                        <span class="value">RM {{ number_format((float)$order->total, 2) }}</span>
                    </div>
                </div>

                <!-- QR Code -->
                @if($order->pickup && $order->pickup->qr_code && in_array($order->status, ['pending', 'confirmed', 'preparing', 'ready']))
                <div class="qr-section">
                    <div class="section-title text-center" style="border: none;">Pickup QR Code</div>
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($order->pickup->qr_code) }}" alt="QR Code">
                    <p class="mt-2 mb-0"><code style="font-size: 0.8rem;">{{ $order->pickup->qr_code }}</code></p>
                    <small class="text-muted">Show this to the vendor for pickup</small>
                </div>
                @endif

                <!-- Order Completed Message -->
                @if($order->status === 'completed')
                <div class="order-completed-msg">
                    <i class="bi bi-check-circle-fill me-1"></i>
                    <span class="msg">Thank you!</span>
                    <div>Your order has been completed</div>
                </div>
                @endif

                <!-- Timeline - Horizontal Delivery Style -->
                <div class="mt-4">
                    <div class="section-title">Order Progress</div>
                    @php
                        $statusOrder = ['pending', 'confirmed', 'preparing', 'ready', 'completed'];
                        $currentIndex = array_search($order->status, $statusOrder);
                        if ($currentIndex === false) $currentIndex = -1;
                        $isCancelled = $order->status === 'cancelled';
                        
                        // Calculate progress percentage
                        $progressPercent = $isCancelled ? 0 : (($currentIndex + 1) / count($statusOrder)) * 100;
                    @endphp
                    
                    <div class="delivery-timeline">
                        <div class="timeline-progress" style="width: {{ $progressPercent }}%;"></div>
                        
                        <!-- Order Placed -->
                        <div class="timeline-step {{ $isCancelled ? 'cancelled' : 'done' }}">
                            <div class="step-icon">
                                <i class="bi bi-cart-check"></i>
                            </div>
                            <div class="step-label">Placed</div>
                            <div class="step-time">{{ $order->created_at->format('h:i A') }}</div>
                        </div>
                        
                        <!-- Confirmed -->
                        <div class="timeline-step {{ $isCancelled ? '' : ($currentIndex >= 1 ? 'done' : ($currentIndex == 0 ? 'active' : '')) }}">
                            <div class="step-icon">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div class="step-label">Confirmed</div>
                            <div class="step-time">{{ $order->confirmed_at ? $order->confirmed_at->format('h:i A') : '--:--' }}</div>
                        </div>
                        
                        <!-- Preparing -->
                        <div class="timeline-step {{ $isCancelled ? '' : ($currentIndex >= 2 ? 'done' : ($currentIndex == 1 ? 'active' : '')) }}">
                            <div class="step-icon">
                                <i class="bi bi-fire"></i>
                            </div>
                            <div class="step-label">Preparing</div>
                            <div class="step-time">{{ $order->status === 'preparing' || $currentIndex > 2 ? 'In Progress' : '--:--' }}</div>
                        </div>
                        
                        <!-- Ready -->
                        <div class="timeline-step {{ $isCancelled ? '' : ($currentIndex >= 3 ? 'done' : ($currentIndex == 2 ? 'active' : '')) }}">
                            <div class="step-icon">
                                <i class="bi bi-bell"></i>
                            </div>
                            <div class="step-label">Ready</div>
                            <div class="step-time">{{ $order->ready_at ? $order->ready_at->format('h:i A') : '--:--' }}</div>
                        </div>
                        
                        <!-- Completed -->
                        <div class="timeline-step {{ $isCancelled ? '' : ($currentIndex >= 4 ? 'done' : ($currentIndex == 3 ? 'active' : '')) }}">
                            <div class="step-icon">
                                <i class="bi bi-bag-check"></i>
                            </div>
                            <div class="step-label">Completed</div>
                            <div class="step-time">{{ $order->completed_at ? $order->completed_at->format('h:i A') : '--:--' }}</div>
                        </div>
                    </div>
                    
                    @if($isCancelled)
                    <div class="text-center mt-3">
                        <span class="badge bg-danger px-3 py-2">
                            <i class="bi bi-x-circle me-1"></i> Order Cancelled
                            @if($order->cancelled_at)
                            - {{ $order->cancelled_at->format('d M Y, h:i A') }}
                            @endif
                        </span>
                        @if($order->cancel_reason)
                        <p class="text-muted mt-2 mb-0 small">{{ $order->cancel_reason }}</p>
                        @endif
                    </div>
                    @endif
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    @if($order->canBeCancelled())
                    <button type="button" class="btn btn-outline-danger flex-fill" id="cancel-order-btn" onclick="confirmCancelOrder({{ $order->id }})">
                        <i class="bi bi-x-circle me-1"></i> Cancel Order
                    </button>
                    @endif
                    
                    @if($order->status === 'completed')
                    <button type="button" class="btn btn-primary flex-fill" id="reorder-btn" onclick="reorderItems({{ $order->id }})">
                        <i class="bi bi-arrow-repeat me-1"></i> Order Again
                    </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-4 text-muted" style="font-size: 0.85rem;">
            <p class="mb-1">Thank you for ordering with us!</p>
            <p class="mb-0">{{ $order->vendor->store_name ?? 'FoodHunter' }} • {{ $order->created_at->format('d M Y') }}</p>
        </div>
    </div>
</div>
</div>

@push('scripts')
<script>
// AJAX polling for order status updates (consumes Lee Song Yan's Order Status API)
const orderId = {{ $order->id }};
const currentStatus = '{{ $order->status }}';
let statusCheckInterval = null;

function checkOrderStatus() {
    // Only poll for active orders (not completed or cancelled)
    if (['completed', 'cancelled'].includes(currentStatus)) {
        return;
    }
    
    fetch('/api/orders/' + orderId + '/status', {
        headers: {
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + (document.querySelector('meta[name="api-token"]')?.content || '')
        }
    })
    .then(res => res.json())
    .then(response => {
        const data = response.data || response;
        if (data.status && data.status !== currentStatus) {
            // Status changed - update UI dynamically
            const statusConfig = {
                'pending': { bg: '#fef3c7', color: '#d97706', icon: 'hourglass-split' },
                'confirmed': { bg: '#dcfce7', color: '#16a34a', icon: 'check-circle' },
                'preparing': { bg: '#dbeafe', color: '#2563eb', icon: 'fire' },
                'ready': { bg: '#ede9fe', color: '#7c3aed', icon: 'bell' },
                'completed': { bg: '#f3f4f6', color: '#374151', icon: 'check-circle-fill' },
                'cancelled': { bg: '#fee2e2', color: '#dc2626', icon: 'x-circle' }
            };
            const config = statusConfig[data.status] || statusConfig['pending'];
            
            // Update status badge
            const statusBadge = document.querySelector('.order-status-badge');
            if (statusBadge) {
                statusBadge.style.background = config.bg;
                statusBadge.style.color = config.color;
                statusBadge.innerHTML = `<i class="bi bi-${config.icon} me-1"></i>${data.status.charAt(0).toUpperCase() + data.status.slice(1)}`;
            }
            
            // Update current status for next poll
            currentStatus = data.status;
            
            // Stop polling if order is completed or cancelled
            if (['completed', 'cancelled'].includes(data.status) && statusCheckInterval) {
                clearInterval(statusCheckInterval);
            }
            
            showToast('Order status updated to: ' + data.status.charAt(0).toUpperCase() + data.status.slice(1), 'info');
        }
    })
    .catch(() => {
        // Silent fail - will retry on next interval
    });
}

// Start polling every 15 seconds for active orders
if (!['completed', 'cancelled'].includes(currentStatus)) {
    statusCheckInterval = setInterval(checkOrderStatus, 15000);
    // Also check immediately after 3 seconds
    setTimeout(checkOrderStatus, 3000);
}

function confirmCancelOrder(orderId) {
    Swal.fire({
        title: 'Cancel Order?',
        text: 'Are you sure you want to cancel this order? This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#FF3B30',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, cancel it',
        cancelButtonText: 'No, keep it'
    }).then((result) => {
        if (result.isConfirmed) {
            cancelOrder(orderId);
        }
    });
}

function cancelOrder(orderId) {
    const btn = document.getElementById('cancel-order-btn');
    const originalContent = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Cancelling...';
    
    fetch('/orders/' + orderId + '/cancel', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Order Cancelled',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.location.href = '/orders';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Failed',
                text: data.message || 'Failed to cancel order.'
            });
            btn.disabled = false;
            btn.innerHTML = originalContent;
        }
    })
    .catch(err => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred. Please try again.'
        });
        btn.disabled = false;
        btn.innerHTML = originalContent;
    });
}

function reorderItems(orderId) {
    const btn = document.getElementById('reorder-btn');
    const originalContent = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Adding to cart...';
    
    fetch('/orders/' + orderId + '/reorder', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Added to Cart!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.location.href = data.redirect || '/cart';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Failed',
                text: data.message || 'Failed to add items to cart.'
            });
            btn.disabled = false;
            btn.innerHTML = originalContent;
        }
    })
    .catch(err => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred. Please try again.'
        });
        btn.disabled = false;
        btn.innerHTML = originalContent;
    });
}
</script>
@endpush
@endsection

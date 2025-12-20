@extends('layouts.app')

@section('title', 'Orders - Vendor Dashboard')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-lg-12">
            <h2 class="fw-bold mb-2">
                <i class="bi bi-receipt-cutoff text-primary me-2"></i>
                Order Management
            </h2>
            <p class="text-muted">View and manage incoming orders</p>
        </div>
    </div>

    <!-- Order Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small">Accepted</p>
                            <h3 class="mb-0 fw-bold text-success">{{ $stats['accepted'] }}</h3>
                        </div>
                        <i class="bi bi-check-circle fs-2 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-info border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small">Preparing</p>
                            <h3 class="mb-0 fw-bold text-info">{{ $stats['preparing'] }}</h3>
                        </div>
                        <i class="bi bi-fire fs-2 text-info"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small">Ready</p>
                            <h3 class="mb-0 fw-bold text-primary">{{ $stats['ready'] }}</h3>
                        </div>
                        <i class="bi bi-bell fs-2 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-dark border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small">Completed</p>
                            <h3 class="mb-0 fw-bold text-dark">{{ $stats['completed'] }}</h3>
                        </div>
                        <i class="bi bi-check-circle-fill fs-2 text-dark"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <ul class="nav nav-pills">
                <li class="nav-item">
                    <a class="nav-link {{ $statusFilter === 'all' ? 'active' : '' }}" href="{{ route('vendor.orders') }}">All Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $statusFilter === 'accepted' ? 'active' : '' }}" href="{{ route('vendor.orders', ['status' => 'accepted']) }}">
                        Accepted <span class="badge bg-success ms-1">{{ $stats['accepted'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $statusFilter === 'preparing' ? 'active' : '' }}" href="{{ route('vendor.orders', ['status' => 'preparing']) }}">
                        Preparing <span class="badge bg-info ms-1">{{ $stats['preparing'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $statusFilter === 'ready' ? 'active' : '' }}" href="{{ route('vendor.orders', ['status' => 'ready']) }}">
                        Ready <span class="badge bg-primary ms-1">{{ $stats['ready'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $statusFilter === 'completed' ? 'active' : '' }}" href="{{ route('vendor.orders', ['status' => 'completed']) }}">
                        Completed <span class="badge bg-dark ms-1">{{ $stats['completed'] }}</span>
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4">Order ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Time</th>
                            <th class="text-end px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td class="px-4">
                                <span class="fw-bold">#{{ $order->order_id }}</span>
                                @if($order->pickup)
                                <br><small class="text-muted">Queue: {{ $order->pickup->queue_number }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($order->user->name) }}" class="rounded-circle me-2" width="35" height="35">
                                    <div>
                                        <div class="fw-semibold">{{ $order->user->name }}</div>
                                        <small class="text-muted">{{ ucfirst($order->user->role) }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $firstItem = $order->orderItems->first();
                                    $remainingCount = $order->orderItems->count() - 1;
                                @endphp
                                @if($firstItem)
                                    <div>{{ $firstItem->quantity }}x {{ $firstItem->menuItem->name }}</div>
                                    @if($remainingCount > 0)
                                        <small class="text-muted">+{{ $remainingCount }} more item{{ $remainingCount > 1 ? 's' : '' }}</small>
                                    @endif
                                @endif
                            </td>
                            <td class="fw-bold text-success">RM {{ number_format($order->total_price, 2) }}</td>
                            <td>
                                @if($order->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($order->status === 'accepted')
                                    <span class="badge bg-success">Accepted</span>
                                @elseif($order->status === 'preparing')
                                    <span class="badge bg-info">Preparing</span>
                                @elseif($order->status === 'ready')
                                    <span class="badge bg-primary">Ready</span>
                                @elseif($order->status === 'completed')
                                    <span class="badge bg-dark">Completed</span>
                                @else
                                    <span class="badge bg-danger">{{ ucfirst($order->status) }}</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $order->created_at->diffForHumans() }}</small>
                                <br><small class="text-muted">{{ $order->created_at->format('h:i A') }}</small>
                            </td>
                            <td class="text-end px-4">
                                <div class="btn-group btn-group-sm">
                                    @if($order->status === 'pending')
                                        <button class="btn btn-success accept-order" data-order-id="{{ $order->order_id }}" title="Accept">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button class="btn btn-primary view-details" data-order-id="{{ $order->order_id }}" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-danger reject-order" data-order-id="{{ $order->order_id }}" title="Reject">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    @elseif($order->status === 'accepted')
                                        <button class="btn btn-info start-preparing" data-order-id="{{ $order->order_id }}" title="Start Preparing">
                                            <i class="bi bi-play-fill"></i>
                                        </button>
                                        <button class="btn btn-primary view-details" data-order-id="{{ $order->order_id }}" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    @elseif($order->status === 'preparing')
                                        <button class="btn btn-primary mark-ready" data-order-id="{{ $order->order_id }}" title="Mark as Ready">
                                            <i class="bi bi-check2-all"></i>
                                        </button>
                                        <button class="btn btn-secondary view-details" data-order-id="{{ $order->order_id }}" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    @elseif($order->status === 'ready')
                                        <button class="btn btn-success mark-completed" data-order-id="{{ $order->order_id }}" title="Mark as Completed">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                        <button class="btn btn-secondary view-details" data-order-id="{{ $order->order_id }}" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    @else
                                        <button class="btn btn-primary view-details" data-order-id="{{ $order->order_id }}" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                                <p class="text-muted mb-0">No orders found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($orders->hasPages())
        <div class="card-footer bg-white">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.border-4 {
    border-width: 4px !important;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    
    // Accept Order
    document.querySelectorAll('.accept-order').forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            if (confirm('Accept this order?')) {
                updateOrderStatus(orderId, 'accept', 'accepted');
            }
        });
    });
    
    // Reject Order
    document.querySelectorAll('.reject-order').forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            if (confirm('Reject this order? This action cannot be undone.')) {
                updateOrderStatus(orderId, 'reject', 'cancelled');
            }
        });
    });
    
    // Start Preparing
    document.querySelectorAll('.start-preparing').forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            if (confirm('Start preparing this order?')) {
                updateOrderStatus(orderId, 'status', 'preparing');
            }
        });
    });
    
    // Mark as Ready
    document.querySelectorAll('.mark-ready').forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            if (confirm('Mark this order as ready for pickup?')) {
                updateOrderStatus(orderId, 'status', 'ready');
            }
        });
    });
    
    // Mark as Completed
    document.querySelectorAll('.mark-completed').forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            if (confirm('Mark this order as completed?')) {
                updateOrderStatus(orderId, 'status', 'completed');
            }
        });
    });
    
    // View Details
    document.querySelectorAll('.view-details').forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            viewOrderDetails(orderId);
        });
    });
    
    function updateOrderStatus(orderId, action, status) {
        const url = action === 'status' 
            ? `/vendor/orders/${orderId}/status`
            : `/vendor/orders/${orderId}/${action}`;
        
        const button = event.target.closest('button');
        const originalHTML = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="bi bi-hourglass-split"></i>';
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showNotification('success', data.message);
                // Reload page after short delay
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showNotification('error', data.message || 'Failed to update order');
                button.disabled = false;
                button.innerHTML = originalHTML;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'An error occurred');
            button.disabled = false;
            button.innerHTML = originalHTML;
        });
    }
    
    function viewOrderDetails(orderId) {
        fetch(`/vendor/orders/${orderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayOrderDetails(data.order);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('error', 'Failed to load order details');
            });
    }
    
    function displayOrderDetails(order) {
        let itemsHTML = '';
        let itemsSubtotal = 0;
        
        order.order_items.forEach(item => {
            const itemTotal = parseFloat(item.price) * parseInt(item.quantity);
            itemsSubtotal += itemTotal;
            itemsHTML += `
                <div class="d-flex justify-content-between mb-2">
                    <div>
                        <strong>${item.quantity}x</strong> ${item.menu_item.name}
                        ${item.special_request ? '<br><small class="text-muted">Note: ' + item.special_request + '</small>' : ''}
                    </div>
                    <div>RM ${itemTotal.toFixed(2)}</div>
                </div>
            `;
        });
        
        const orderTotal = parseFloat(order.total_price);
        const adjustment = orderTotal - itemsSubtotal;
        
        const modalHTML = `
            <div class="modal fade" id="orderDetailsModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Order #${order.order_id}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <strong>Customer:</strong> ${order.user.name}<br>
                                <strong>Email:</strong> ${order.user.email}<br>
                                <strong>Order Time:</strong> ${new Date(order.created_at).toLocaleString()}
                            </div>
                            <hr>
                            <h6>Order Items:</h6>
                            ${itemsHTML}
                            <hr>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>RM ${itemsSubtotal.toFixed(2)}</span>
                            </div>
                            ${adjustment !== 0 ? `
                            <div class="d-flex justify-content-between mb-2 ${adjustment > 0 ? 'text-muted' : 'text-success'}">
                                <span><small>${adjustment > 0 ? 'Service Fee & Fees' : 'Discount & Adjustments'}:</small></span>
                                <span><small>RM ${adjustment.toFixed(2)}</small></span>
                            </div>
                            ` : ''}
                            <div class="d-flex justify-content-between border-top pt-2">
                                <strong>Total:</strong>
                                <strong class="text-success">RM ${orderTotal.toFixed(2)}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        const existingModal = document.getElementById('orderDetailsModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Add and show new modal
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
        modal.show();
    }
    
    function showNotification(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHTML = `
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 9999;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', alertHTML);
        
        // Auto dismiss after 3 seconds
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) {
                alert.remove();
            }
        }, 3000);
    }
});
</script>
@endpush

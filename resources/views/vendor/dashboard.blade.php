@extends('layouts.app')

@section('title', 'Vendor Dashboard - FoodHunter')

@section('content')
<div class="container" style="padding-top: 100px; padding-bottom: 50px;">
    <!-- Dashboard Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold mb-2">
                <i class="bi bi-speedometer2 text-warning me-2"></i>
                Vendor Dashboard
            </h2>
            <p class="text-muted">Welcome back, {{ auth()->user()->name }}!</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="{{ route('vendor.menu') }}" class="btn btn-primary rounded-pill">
                <i class="bi bi-plus-circle me-2"></i> Add Menu Item
            </a>
        </div>
    </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-sm-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 small">Today's Orders</p>
                                <h3 class="fw-bold mb-0">{{ $todayOrders }}</h3>
                            </div>
                            <div class="rounded p-3" style="background: #ffc107;">
                                <i class="bi bi-receipt fs-4 text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-sm-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 small">Today's Revenue</p>
                                <h3 class="fw-bold mb-0 text-success">RM {{ number_format($todayRevenue, 2) }}</h3>
                            </div>
                            <div class="rounded p-3" style="background: #28a745;">
                                <i class="bi bi-cash-stack fs-4 text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-sm-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 small">Pending Orders</p>
                                <h3 class="fw-bold mb-0 text-warning">{{ $pendingOrders->count() }}</h3>
                            </div>
                            <div class="rounded p-3" style="background: #fd7e14;">
                                <i class="bi bi-hourglass-split fs-4 text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-sm-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 small">Completed Today</p>
                                <h3 class="fw-bold mb-0">{{ $readyOrders->count() }}</h3>
                            </div>
                            <div class="rounded p-3" style="background: #17a2b8;">
                                <i class="bi bi-check-circle fs-4 text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-4">
            <!-- Recent Orders -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">Recent Orders</h5>
                            <a href="{{ route('vendor.orders') }}" class="text-decoration-none">View All</a>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="px-3">Order</th>
                                        <th>Customer</th>
                                        <th>Items</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th class="text-end px-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentOrders as $order)
                                    <tr class="order-row">
                                        <td class="px-3">
                                            <div class="d-flex align-items-center">
                                                <div class="me-2" style="width: 36px; height: 36px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="bi bi-receipt text-white" style="font-size: 0.85rem;"></i>
                                                </div>
                                                <span class="fw-bold" style="font-size: 0.85rem;">{{ $order->order_number }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="https://ui-avatars.com/api/?name={{ urlencode($order->user->name ?? 'U') }}&background=f3f4f6&color=6b7280&size=32" class="rounded-circle me-2" width="32" height="32">
                                                <span class="fw-medium" style="font-size: 0.85rem;">{{ Str::limit($order->user->name, 15) }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $firstItem = $order->items->first();
                                                $remainingCount = $order->items->count() - 1;
                                            @endphp
                                            @if($firstItem)
                                                <span style="font-size: 0.85rem;">{{ $firstItem->quantity }}x {{ Str::limit($firstItem->item_name, 15) }}</span>
                                                @if($remainingCount > 0)
                                                    <br><small class="text-muted">+{{ $remainingCount }} more</small>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-bold" style="color: #FF9500; font-size: 0.85rem;">RM {{ number_format($order->total, 2) }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $statusConfig = [
                                                    'pending' => ['bg' => '#fef3c7', 'color' => '#d97706', 'icon' => 'hourglass-split'],
                                                    'confirmed' => ['bg' => '#dcfce7', 'color' => '#16a34a', 'icon' => 'check-circle'],
                                                    'preparing' => ['bg' => '#dbeafe', 'color' => '#2563eb', 'icon' => 'fire'],
                                                    'ready' => ['bg' => '#ede9fe', 'color' => '#7c3aed', 'icon' => 'bell'],
                                                    'completed' => ['bg' => '#f3f4f6', 'color' => '#374151', 'icon' => 'check-circle-fill'],
                                                    'cancelled' => ['bg' => '#fee2e2', 'color' => '#dc2626', 'icon' => 'x-circle'],
                                                ];
                                                $config = $statusConfig[$order->status] ?? $statusConfig['pending'];
                                            @endphp
                                            <span class="status-badge" style="background: {{ $config['bg'] }}; color: {{ $config['color'] }}; padding: 0.3rem 0.6rem; border-radius: 50px; font-size: 0.7rem; font-weight: 600;">
                                                <i class="bi bi-{{ $config['icon'] }} me-1"></i>{{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td class="text-end px-3">
                                            <button type="button" class="btn btn-sm" style="background: #6366f1; color: white; border-radius: 8px; padding: 0.4rem 0.75rem; font-size: 0.75rem;" 
                                                    onclick="viewOrder({{ $order->id }})" title="View Details">
                                                <i class="bi bi-eye me-1"></i>View
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div style="width: 60px; height: 60px; background: #f3f4f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.75rem;">
                                                <i class="bi bi-inbox fs-3 text-muted"></i>
                                            </div>
                                            <p class="text-muted mb-0" style="font-size: 0.85rem;">No orders yet</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Sidebar -->
            <div class="col-md-4">
                <!-- Quick Actions -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">Quick Actions</h6>
                        <div class="d-grid gap-2">
                            <a href="{{ route('vendor.menu') }}" class="btn btn-primary rounded-pill">
                                <i class="bi bi-plus-circle me-2"></i> Add Menu Item
                            </a>
                            <a href="{{ route('vendor.orders') }}" class="btn btn-outline-primary rounded-pill">
                                <i class="bi bi-receipt me-2"></i> View Orders
                            </a>
                            <a href="{{ route('vendor.vouchers') }}" class="btn btn-outline-primary rounded-pill">
                                <i class="bi bi-ticket-perforated me-2"></i> Manage Vouchers
                            </a>
                            <a href="{{ route('vendor.reports') }}" class="btn btn-outline-primary rounded-pill">
                                <i class="bi bi-graph-up me-2"></i> View Analytics
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Store Status -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">Store Status</h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1">{{ $vendor->store_name }}</p>
                                <span class="store-status-badge badge bg-{{ $vendor->is_open ? 'success' : 'secondary' }}">
                                    <i class="bi bi-{{ $vendor->is_open ? 'check-circle' : 'x-circle' }} me-1"></i>{{ $vendor->is_open ? 'Open' : 'Closed' }}
                                </span>
                            </div>
                            <button type="button" class="store-toggle-btn btn btn-sm btn-outline-{{ $vendor->is_open ? 'danger' : 'success' }}" onclick="toggleStoreStatus({{ $vendor->is_open ? 'false' : 'true' }})">
                                <i class="bi bi-{{ $vendor->is_open ? 'pause' : 'play' }}-fill me-1"></i>{{ $vendor->is_open ? 'Close Store' : 'Open Store' }}
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Ready for Pickup -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">Ready for Pickup</h6>
                        @forelse($readyOrders->take(5) as $order)
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div>
                                <strong class="text-primary">#{{ $order->pickup->queue_number ?? 'N/A' }}</strong>
                                <p class="mb-0 small text-muted">{{ $order->user->name }}</p>
                            </div>
                            <button type="button" class="btn btn-sm btn-success" onclick="completeOrder({{ $order->id }})">
                                <i class="bi bi-check-lg"></i> Done
                            </button>
                        </div>
                        @empty
                        <p class="text-center text-muted mb-0">No orders ready for pickup</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Order View Modal -->
<div id="orderViewModal" class="custom-modal">
    <div class="modal-backdrop-custom" onclick="closeOrderModal()"></div>
    <div class="modal-content-custom">
        <button class="modal-close-btn" onclick="closeOrderModal()"><i class="bi bi-x"></i></button>
        <div id="orderModalContent">
            <div style="padding: 3rem; text-align: center;">
                <div class="spinner-border text-primary"></div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Order Row Hover */
.order-row {
    transition: background-color 0.15s ease;
}
.order-row:hover {
    background-color: rgba(255, 149, 0, 0.03);
}

/* Custom Modal Styling */
.custom-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9999;
    visibility: hidden;
    opacity: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    transition: opacity 0.2s ease, visibility 0.2s ease;
}
.custom-modal.show {
    visibility: visible;
    opacity: 1;
}
.modal-backdrop-custom {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
}
.modal-content-custom {
    position: relative;
    background: white;
    border-radius: 20px;
    max-width: 550px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    animation: modalZoomIn 0.3s ease;
}
@keyframes modalZoomIn {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
}
.modal-close-btn {
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: none;
    background: rgba(255,255,255,0.9);
    color: #64748b;
    font-size: 1.25rem;
    cursor: pointer;
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}
.modal-close-btn:hover {
    background: white;
    color: #1f2937;
    transform: scale(1.1);
}

/* Order Modal Styles */
.order-modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 2rem;
    border-radius: 20px 20px 0 0;
    color: white;
    text-align: center;
}
.order-modal-header .order-number {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}
.order-modal-header .order-status {
    display: inline-block;
    padding: 0.4rem 1rem;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 600;
}
.order-modal-body {
    padding: 1.5rem;
}
.order-info-row {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f5f9;
}
.order-info-row:last-child {
    border-bottom: none;
}
.order-info-label {
    color: #64748b;
    font-size: 0.85rem;
}
.order-info-value {
    font-weight: 600;
    color: #1e293b;
}
.order-items-list {
    background: #f8fafc;
    border-radius: 12px;
    padding: 1rem;
    margin: 1rem 0;
}
.order-item-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
}
.order-item-row:not(:last-child) {
    border-bottom: 1px dashed #e2e8f0;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let lastOrderCount = {{ $todayOrders }};
    let lastPendingCount = {{ $pendingOrders->count() }};

    // Poll for dashboard updates every 15 seconds
    function checkForUpdates() {
        fetch('/vendor/dashboard', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const stats = data.data || data;
                // Check if there are new orders
                if (stats.todayOrders > lastOrderCount || stats.pendingOrders > lastPendingCount) {
                    // Show notification with action button
                    Swal.fire({
                        icon: 'info',
                        title: 'New Order!',
                        text: 'You have received a new order.',
                        showCancelButton: true,
                        confirmButtonText: '<i class="bi bi-arrow-clockwise me-1"></i> Refresh Now',
                        cancelButtonText: 'Later',
                        confirmButtonColor: '#FF9500',
                        timer: 10000,
                        timerProgressBar: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload();
                        }
                    });
                    
                    // Update stats cards
                    const todayOrdersEl = document.querySelector('.col-sm-6.col-md-3:nth-child(1) h3');
                    if (todayOrdersEl) todayOrdersEl.textContent = stats.todayOrders;
                    
                    const pendingBadge = document.querySelector('.badge.bg-warning');
                    if (pendingBadge) pendingBadge.textContent = stats.pendingOrders + ' Pending';
                }
                lastOrderCount = stats.todayOrders;
                lastPendingCount = stats.pendingOrders;
            }
        })
        .catch(() => {});
    }

    // Start polling every 15 seconds
    setInterval(checkForUpdates, 15000);

    // View Order Modal
    window.viewOrder = async function(orderId) {
        document.getElementById('orderViewModal').classList.add('show');
        document.getElementById('orderModalContent').innerHTML = '<div style="padding: 3rem; text-align: center;"><div class="spinner-border text-primary"></div></div>';

        try {
            const res = await fetch(`/vendor/orders/${orderId}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            if (data.success) {
                const order = data.data?.order || data.order;
                const statusColors = {
                    'pending': 'background: #fbbf24; color: #1f2937;',
                    'confirmed': 'background: #22c55e; color: white;',
                    'preparing': 'background: #3b82f6; color: white;',
                    'ready': 'background: #8b5cf6; color: white;',
                    'completed': 'background: #1f2937; color: white;',
                    'cancelled': 'background: #ef4444; color: white;'
                };
                
                let itemsHtml = order.items.map(item => `
                    <div class="order-item-row">
                        <div>
                            <span class="fw-semibold">${item.quantity}x</span> ${item.item_name}
                        </div>
                        <div class="text-muted">RM ${parseFloat(item.price).toFixed(2)}</div>
                    </div>
                `).join('');

                document.getElementById('orderModalContent').innerHTML = `
                    <div class="order-modal-header">
                        <div class="order-number">${order.order_number}</div>
                        <span class="order-status" style="${statusColors[order.status] || ''}">${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</span>
                        ${order.queue_number ? `<div class="mt-2"><small>Queue: <strong>#${order.queue_number}</strong></small></div>` : ''}
                    </div>
                    <div class="order-modal-body">
                        <div class="order-info-row">
                            <span class="order-info-label"><i class="bi bi-person"></i>Customer</span>
                            <span class="order-info-value">${order.customer_name}</span>
                        </div>
                        <div class="order-info-row">
                            <span class="order-info-label"><i class="bi bi-clock me-2"></i>Order Time</span>
                            <span class="order-info-value">${order.created_at}</span>
                        </div>
                        <div class="order-info-row">
                            <span class="order-info-label"><i class="bi bi-credit-card me-2"></i>Payment</span>
                            <span class="order-info-value">${order.payment_method || 'N/A'}</span>
                        </div>
                        
                        <h6 class="fw-bold mt-4 mb-2"><i class="bi bi-bag me-2"></i>Order Items</h6>
                        <div class="order-items-list">
                            ${itemsHtml}
                        </div>
                        
                        <div class="order-info-row" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); margin: 1rem -1.5rem -1.5rem; padding: 1rem 1.5rem; border-radius: 0 0 20px 20px;">
                            <span style="color: rgba(255,255,255,0.8); font-weight: 600;">Total Amount</span>
                            <span style="color: white; font-size: 1.25rem; font-weight: 700;">RM ${parseFloat(order.total).toFixed(2)}</span>
                        </div>
                    </div>
                `;
            } else {
                document.getElementById('orderModalContent').innerHTML = '<div style="padding: 2rem; text-align: center; color: #ef4444;">Failed to load order</div>';
            }
        } catch (e) {
            document.getElementById('orderModalContent').innerHTML = '<div style="padding: 2rem; text-align: center; color: #ef4444;">An error occurred</div>';
        }
    };

    window.closeOrderModal = function() {
        document.getElementById('orderViewModal').classList.remove('show');
    };

    // Toggle store status via AJAX
    window.toggleStoreStatus = async function(isOpen) {
        try {
            const res = await fetch('/vendor/toggle-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ is_open: isOpen })
            });
            const data = await res.json();
            if (data.success) {
                // Update store status UI without reload
                const statusBadge = document.querySelector('.store-status-badge');
                const toggleBtn = document.querySelector('.store-toggle-btn');
                
                if (statusBadge) {
                    statusBadge.className = `store-status-badge badge ${isOpen ? 'bg-success' : 'bg-secondary'}`;
                    statusBadge.innerHTML = `<i class="bi bi-${isOpen ? 'check-circle' : 'x-circle'} me-1"></i>${isOpen ? 'Open' : 'Closed'}`;
                }
                
                if (toggleBtn) {
                    toggleBtn.className = `store-toggle-btn btn btn-sm ${isOpen ? 'btn-outline-danger' : 'btn-outline-success'}`;
                    toggleBtn.innerHTML = `<i class="bi bi-${isOpen ? 'pause' : 'play'}-fill me-1"></i>${isOpen ? 'Close Store' : 'Open Store'}`;
                    toggleBtn.setAttribute('onclick', `toggleStoreStatus(${!isOpen})`);
                }
                
                showToast(data.message || (isOpen ? 'Store is now open!' : 'Store is now closed!'), 'success');
            } else {
                Swal.fire('Error', data.message || 'Failed to update store status', 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'An error occurred', 'error');
        }
    };

    // Complete order via AJAX
    window.completeOrder = async function(orderId) {
        const result = await Swal.fire({
            title: 'Complete Order?',
            text: 'Mark this order as completed?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Yes, complete it'
        });
        
        if (!result.isConfirmed) return;
        
        try {
            const res = await fetch(`/vendor/orders/${orderId}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ _method: 'PUT', status: 'completed' })
            });
            const data = await res.json();
            if (data.success) {
                // Remove order card from recent orders without reload
                const orderCard = document.querySelector(`[data-order-id="${orderId}"]`);
                if (orderCard) {
                    orderCard.style.animation = 'fadeOut 0.3s ease';
                    setTimeout(() => orderCard.remove(), 300);
                }
                showToast('Order completed successfully!', 'success');
            } else {
                Swal.fire('Error', data.message || 'Failed to complete order', 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'An error occurred', 'error');
        }
    };

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeOrderModal();
    });
});
</script>
@endpush
@endsection

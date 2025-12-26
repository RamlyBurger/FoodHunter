@extends('layouts.app')

@section('title', 'My Orders')

@push('styles')
<style>
    .profile-container {
        max-width: 1200px;
        margin: 0 auto;
    }
    .content-card {
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 24px;
        margin-bottom: 20px;
        box-shadow: none;
    }
    .filter-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }
    .filter-tab {
        padding: 8px 16px;
        border-radius: 6px;
        border: 1px solid #e0e0e0;
        background: #fff;
        color: #666;
        font-weight: 500;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }
    .filter-tab:hover {
        background: #f5f5f5;
        color: #333;
        border-color: #d0d0d0;
    }
    .filter-tab.active {
        background: var(--primary-color);
        color: #fff;
        border-color: var(--primary-color);
        box-shadow: 0 2px 4px rgba(255,149,0,0.2);
    }
    .orders-table {
        width: 100%;
        border-collapse: collapse;
    }
    .orders-table thead th {
        background: #fafafa;
        padding: 12px 16px;
        font-weight: 600;
        font-size: 0.8rem;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e0e0e0;
        text-align: left;
    }
    .orders-table tbody tr.order-row-main {
        background: #fff;
        border-bottom: 1px solid #f0f0f0;
    }
    .orders-table tbody tr.order-row-main:hover {
        background: #fafafa;
    }
    .order-row-main {
        cursor: pointer;
    }
    .order-items-expanded {
        background: #fafafa;
        border-bottom: 1px solid #f0f0f0;
    }
    .order-items-expanded td {
        padding: 16px !important;
    }
    .item-detail {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px 0;
        border-bottom: 1px solid #eee;
    }
    .item-detail:last-child {
        border-bottom: none;
    }
    .item-detail img {
        width: 60px;
        height: 60px;
        border-radius: 8px;
        object-fit: cover;
    }
    .expand-icon {
        transition: transform 0.2s;
        color: #999;
        font-size: 0.9rem;
    }
    .expand-icon.expanded {
        transform: rotate(180deg);
    }
    .orders-table tbody td {
        padding: 14px 16px;
        vertical-align: middle;
        border: none;
    }
    .order-number {
        font-weight: 600;
        color: #333;
        font-size: 0.9rem;
    }
    .order-date {
        font-size: 0.75rem;
        color: #999;
        margin-top: 2px;
    }
    .order-items-preview {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .order-items-preview img {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        object-fit: cover;
    }
    .order-items-count {
        font-size: 0.85rem;
        color: #666;
    }
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .status-badge.pending { background: rgba(255,204,0,0.15); color: #FFCC00; }
    .status-badge.confirmed { background: rgba(0,122,255,0.15); color: #007AFF; }
    .status-badge.preparing { background: rgba(255,149,0,0.15); color: #FF9500; }
    .status-badge.ready { background: rgba(52,199,89,0.15); color: #34C759; }
    .status-badge.completed { background: rgba(52,199,89,0.15); color: #34C759; }
    .status-badge.cancelled { background: rgba(255,59,48,0.15); color: #FF3B30; }
    .priority-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: 600;
        background: rgba(255,59,48,0.1);
        color: #FF3B30;
    }
    .order-total {
        font-weight: 600;
        color: #333;
        font-size: 0.95rem;
    }
    .action-btn {
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 500;
        transition: all 0.2s;
    }
    @media (max-width: 768px) {
        .orders-table {
            display: block;
            overflow-x: auto;
        }
    }
</style>
@endpush

@section('content')
<div class="page-content">
<div class="container py-4">
    <div class="profile-container">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 style="font-weight: 700; letter-spacing: -0.5px;"><i class="bi bi-receipt me-2" style="color: var(--primary-color);"></i>My Orders</h2>
                <p class="text-muted mb-0">Track and manage your food orders</p>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <button type="button" class="filter-tab {{ request('status', 'active') === 'active' ? 'active' : '' }}" onclick="setStatusFilter('active')">
                <i class="bi bi-lightning-charge-fill me-1"></i> Active Orders
            </button>
            <button type="button" class="filter-tab {{ request('status') === 'completed' ? 'active' : '' }}" onclick="setStatusFilter('completed')">
                <i class="bi bi-check-circle me-1"></i> Completed
            </button>
            <button type="button" class="filter-tab {{ request('status') === 'cancelled' ? 'active' : '' }}" onclick="setStatusFilter('cancelled')">
                <i class="bi bi-x-circle me-1"></i> Cancelled
            </button>
            <button type="button" class="filter-tab {{ request('status') === 'all' ? 'active' : '' }}" onclick="setStatusFilter('all')">
                <i class="bi bi-list-ul me-1"></i> All Orders
            </button>
        </div>

        <!-- Advanced Filters -->
        <div class="content-card mb-4">
            <form method="GET" action="{{ url('/orders') }}" id="filter-form">
                <input type="hidden" name="filter" value="{{ request('filter', 'active') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold"><i class="bi bi-search me-1"></i>Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search by order number..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold"><i class="bi bi-calendar-range me-1"></i>Date Range</label>
                        <select class="form-select" name="date_range">
                            <option value="7days" {{ request('date_range') === '7days' || !request('date_range') ? 'selected' : '' }}>Last 7 days</option>
                            <option value="30days" {{ request('date_range') === '30days' ? 'selected' : '' }}>Last 30 days</option>
                            <option value="3months" {{ request('date_range') === '3months' ? 'selected' : '' }}>Last 3 months</option>
                            <option value="6months" {{ request('date_range') === '6months' ? 'selected' : '' }}>Last 6 months</option>
                            <option value="all" {{ request('date_range') === 'all' ? 'selected' : '' }}>All time</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold"><i class="bi bi-funnel me-1"></i>Status</label>
                        <select class="form-select" name="status" id="statusFilter">
                            <option value="active" {{ request('status') === 'active' || (!request('status') && request('filter', 'active') === 'active') ? 'selected' : '' }}>Active Orders</option>
                            <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="preparing" {{ request('status') === 'preparing' ? 'selected' : '' }}>Preparing</option>
                            <option value="ready" {{ request('status') === 'ready' ? 'selected' : '' }}>Ready</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel me-1"></i> Apply
                        </button>
                    </div>
                </div>
                @if(request('search') || (request('status') && request('status') !== 'all') || (request('date_range') && request('date_range') !== '7days'))
                <div class="mt-3">
                    <a href="{{ url('/orders?filter=' . request('filter', 'active')) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i> Clear Filters
                    </a>
                </div>
                @endif
            </form>
        </div>


        <!-- Orders Table -->
        <div class="content-card" id="orders-container">
            @if($orders->count() > 0)
            <div class="table-responsive">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Order Details</th>
                            <th style="width: 10%;">Items</th>
                            <th style="width: 20%;">Vendor</th>
                            <th style="width: 15%;">Status</th>
                            <th style="width: 12%;">Total</th>
                            <th style="width: 18%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        <tr class="order-row-main" onclick="toggleOrderItems({{ $order->id }})">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-chevron-down expand-icon" id="expand-icon-{{ $order->id }}"></i>
                                    <div>
                                        <div class="order-number">{{ $order->order_number }}</div>
                                        <div class="order-date">{{ $order->created_at->format('d M Y, h:i A') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="order-items-count">
                                    {{ $order->items->count() }} {{ Str::plural('item', $order->items->count()) }}
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 500;">{{ $order->vendor->store_name ?? 'N/A' }}</div>
                                @if($order->pickup && $order->pickup->queue_number)
                                <small class="text-muted">Queue #{{ $order->pickup->queue_number }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="status-badge {{ $order->status }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                                @if(in_array($order->status, ['pending', 'confirmed', 'preparing', 'ready']))
                                <span class="priority-badge ms-2">
                                    <i class="bi bi-lightning-charge-fill"></i>
                                </span>
                                @endif
                            </td>
                            <td>
                                <div class="order-total">RM {{ number_format($order->total, 2) }}</div>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ url('/orders/' . $order->id) }}" class="btn btn-sm btn-outline-primary action-btn" onclick="event.stopPropagation()">
                                        <i class="bi bi-receipt"></i> Receipt
                                    </a>
                                    @if($order->status === 'completed')
                                    <button type="button" class="btn btn-sm btn-primary action-btn" id="reorder-btn-{{ $order->id }}" onclick="event.stopPropagation(); reorderItems({{ $order->id }})">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        <tr class="order-items-expanded" id="order-items-{{ $order->id }}" style="display: none;">
                            <td colspan="6">
                                <div class="px-3">
                                    <h6 class="mb-3 fw-semibold">Order Items</h6>
                                    @foreach($order->items as $item)
                                    <a href="{{ $item->menuItem ? url('/menu/' . $item->menuItem->id) : '#' }}" class="item-detail text-decoration-none text-dark">
                                        <img src="{{ $item->menuItem ? \App\Helpers\ImageHelper::menuItem($item->menuItem->image) : '' }}" 
                                             alt="{{ $item->item_name ?? 'Item' }}"
                                             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($item->item_name ?? 'Item') }}&background=f3f4f6&color=9ca3af&size=120&font-size=0.33&bold=true';">
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold">{{ $item->item_name }}</div>
                                            @if($item->special_instructions)
                                            <small class="text-info"><i class="bi bi-chat-text"></i> {{ $item->special_instructions }}</small>
                                            @endif
                                        </div>
                                        <div class="text-end" style="min-width: 90px;">
                                            <div class="fw-semibold">x{{ $item->quantity }}</div>
                                            <small class="text-muted">RM {{ number_format((float)$item->unit_price, 2) }}</small>
                                        </div>
                                        <div class="text-end" style="min-width: 100px;">
                                            <div class="fw-bold" style="color: var(--primary-color);">RM {{ number_format((float)$item->subtotal, 2) }}</div>
                                        </div>
                                    </a>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 100px; height: 100px; background: var(--gray-100);">
                    <i class="bi bi-receipt" style="font-size: 40px; color: var(--gray-400);"></i>
                </div>
                <h5 style="font-weight: 600;">No orders found</h5>
                <p class="text-muted mb-4">{{ request('filter') && request('filter') !== 'active' ? 'No orders in this category' : 'Start ordering delicious food from our menu!' }}</p>
                <a href="{{ url('/menu') }}" class="btn btn-primary">
                    <i class="bi bi-search me-1"></i> Browse Menu
                </a>
            </div>
            @endif
        </div>

        <!-- Pagination (Outside Container) -->
        <div class="mt-4 d-flex justify-content-center">
            {{ $orders->withQueryString()->links('vendor.pagination.custom') }}
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
// AJAX filtering for orders
function setStatusFilter(status) {
    const statusSelect = document.getElementById('statusFilter');
    if (statusSelect) {
        statusSelect.value = status;
        loadOrders();
    }
    // Update active tab
    document.querySelectorAll('.filter-tab').forEach(tab => tab.classList.remove('active'));
    event.target.classList.add('active');
}

function loadOrders(page = 1) {
    const form = document.getElementById('filter-form');
    const formData = new FormData(form);
    formData.set('page', page);
    const params = new URLSearchParams(formData);
    const targetUrl = `{{ url('/orders') }}?${params.toString()}`;
    
    const ordersContainer = document.getElementById('orders-container');
    ordersContainer.style.opacity = '0.5';
    
    fetch(targetUrl, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(response => {
        if (response.success && response.data) {
            renderOrders(response.data);
            window.history.pushState({}, '', targetUrl);
        }
        ordersContainer.style.opacity = '1';
    })
    .catch(err => {
        console.error('Filter error:', err);
        ordersContainer.style.opacity = '1';
        showToast('Error loading orders', 'error');
    });
}

function renderOrders(data) {
    const ordersContainer = document.getElementById('orders-container');
    const paginationContainer = document.querySelector('.pagination')?.parentElement;
    
    if (!data.orders || data.orders.length === 0) {
        ordersContainer.innerHTML = `
            <div class="text-center py-5">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 100px; height: 100px; background: var(--gray-100);">
                    <i class="bi bi-receipt" style="font-size: 40px; color: var(--gray-400);"></i>
                </div>
                <h5 style="font-weight: 600;">No orders found</h5>
                <p class="text-muted mb-4">No orders in this category</p>
                <a href="{{ url('/menu') }}" class="btn btn-primary"><i class="bi bi-search me-1"></i> Browse Menu</a>
            </div>
        `;
        if (paginationContainer) paginationContainer.innerHTML = '';
        return;
    }
    
    let html = `<div class="table-responsive"><table class="orders-table"><thead><tr>
        <th style="width: 40px;"></th><th>Order</th><th>Restaurant</th><th>Items</th><th>Total</th><th>Status</th><th>Date</th><th style="width: 120px;"></th>
    </tr></thead><tbody>`;
    
    data.orders.forEach(order => {
        const statusClass = {
            'pending': 'warning', 'confirmed': 'info', 'preparing': 'primary',
            'ready': 'success', 'completed': 'success', 'cancelled': 'danger'
        }[order.status] || 'secondary';
        
        html += `
            <tr class="order-row-main" onclick="toggleOrderItems(${order.id})">
                <td class="text-center"><i class="bi bi-chevron-right expand-icon" id="expand-icon-${order.id}"></i></td>
                <td><a href="/orders/${order.id}" class="fw-semibold text-decoration-none" style="color: var(--primary-color);">${order.order_number}</a></td>
                <td>${order.vendor?.store_name || 'N/A'}</td>
                <td><span class="badge bg-light text-dark">${order.items_count} item(s)</span></td>
                <td class="fw-semibold">RM ${order.total.toFixed(2)}</td>
                <td><span class="badge bg-${statusClass}">${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</span></td>
                <td class="text-muted small">${order.created_at_human}</td>
                <td>
                    <a href="/orders/${order.id}" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-eye"></i></a>
                    ${['completed', 'cancelled'].includes(order.status) ? `<button class="btn btn-sm btn-outline-success" id="reorder-btn-${order.id}" onclick="event.stopPropagation(); reorderItems(${order.id})"><i class="bi bi-arrow-repeat"></i></button>` : ''}
                </td>
            </tr>
            <tr class="order-items-expanded" id="order-items-${order.id}" style="display: none;">
                <td colspan="8"><div class="p-2"><em class="text-muted">View order details for item breakdown</em></div></td>
            </tr>
        `;
    });
    
    html += '</tbody></table></div>';
    ordersContainer.innerHTML = html;
}

// Attach filter listeners
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('filter-form');
    form?.querySelectorAll('select, input').forEach(el => {
        el.addEventListener('change', () => loadOrders(1));
    });
});

function toggleOrderItems(orderId) {
    const itemsRow = document.getElementById('order-items-' + orderId);
    const expandIcon = document.getElementById('expand-icon-' + orderId);
    
    if (itemsRow.style.display === 'none') {
        itemsRow.style.display = 'table-row';
        expandIcon.classList.add('expanded');
    } else {
        itemsRow.style.display = 'none';
        expandIcon.classList.remove('expanded');
    }
}

function reorderItems(orderId) {
    const btn = document.getElementById('reorder-btn-' + orderId);
    const originalContent = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    
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

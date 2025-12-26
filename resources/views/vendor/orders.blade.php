@extends('layouts.app')

@section('title', 'Orders - Vendor Dashboard')

@section('content')
<div class="container" style="padding-top: 100px; padding-bottom: 50px;">
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
            <div class="card border-0 shadow-sm border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small">Pending</p>
                            <h3 class="mb-0 fw-bold text-warning">{{ $stats['pending'] ?? 0 }}</h3>
                        </div>
                        <i class="bi bi-hourglass-split fs-2 text-warning"></i>
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
                            <h3 class="mb-0 fw-bold text-info">{{ $stats['preparing'] ?? 0 }}</h3>
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
                            <h3 class="mb-0 fw-bold text-primary">{{ $stats['ready'] ?? 0 }}</h3>
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
                            <h3 class="mb-0 fw-bold text-dark">{{ $stats['completed'] ?? 0 }}</h3>
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
            <form method="GET" action="{{ route('vendor.orders') }}">
                <div class="row align-items-center g-2 mb-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Search order #, customer..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="preparing" {{ request('status') === 'preparing' ? 'selected' : '' }}>Preparing</option>
                            <option value="ready" {{ request('status') === 'ready' ? 'selected' : '' }}>Ready</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date" class="form-control" value="{{ request('date') }}" placeholder="Filter by date">
                    </div>
                    <div class="col-md-2">
                        <select name="per_page" class="form-select">
                            <option value="10" {{ request('per_page', 15) == 10 ? 'selected' : '' }}>10 per page</option>
                            <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15 per page</option>
                            <option value="25" {{ request('per_page', 15) == 25 ? 'selected' : '' }}>25 per page</option>
                            <option value="50" {{ request('per_page', 15) == 50 ? 'selected' : '' }}>50 per page</option>
                        </select>
                    </div>
                    <div class="col-md-3 text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Filter
                        </button>
                        <a href="{{ route('vendor.orders') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 orders-table">
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
                        @php
                            $firstItem = $order->items->first();
                            $remainingCount = $order->items->count() - 1;
                            $firstItemImage = ($firstItem && $firstItem->menuItem) 
                                ? \App\Helpers\ImageHelper::menuItem($firstItem->menuItem->image) 
                                : null;
                        @endphp
                        <tr class="order-row" id="order-row-{{ $order->id }}" data-order-id="{{ $order->id }}">
                            <td class="px-4">
                                <div class="d-flex align-items-center">
                                    @if($firstItemImage)
                                    <img src="{{ $firstItemImage }}" class="me-3 rounded" width="48" height="48" style="object-fit: cover;" 
                                         onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($firstItem->item_name ?? 'O') }}&background=667eea&color=fff&size=100';">
                                    @else
                                    <div class="me-3" style="width: 48px; height: 48px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-receipt text-white"></i>
                                    </div>
                                    @endif
                                    <div>
                                        <span class="fw-bold">{{ $order->order_number }}</span>
                                        @if($order->pickup)
                                        <br><small class="text-muted"><i class="bi bi-hash"></i>Queue: {{ $order->pickup->queue_number }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ \App\Helpers\ImageHelper::avatar($order->user->avatar ?? null, $order->user->name ?? 'Customer') }}" 
                                         alt="{{ $order->user->name ?? 'Customer' }}" 
                                         class="rounded-circle me-2" 
                                         width="38" height="38" 
                                         style="object-fit: cover; border: 2px solid #e5e7eb;"
                                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($order->user->name ?? 'U') }}&background=6c757d&color=fff&size=100'">
                                    <div>
                                        <div class="fw-semibold">{{ $order->user->name ?? 'Unknown' }}</div>
                                        <small class="text-muted">{{ Str::limit($order->user->email ?? '', 20) }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($firstItem)
                                    <div class="fw-medium">{{ $firstItem->quantity }}x {{ Str::limit($firstItem->item_name, 18) }}</div>
                                    @if($remainingCount > 0)
                                        <small class="text-muted">+{{ $remainingCount }} more item{{ $remainingCount > 1 ? 's' : '' }}</small>
                                    @endif
                                @endif
                            </td>
                            <td>
                                <span class="fw-bold" style="color: #FF9500;">RM {{ number_format($order->total, 2) }}</span>
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
                                <span class="status-badge" style="background: {{ $config['bg'] }}; color: {{ $config['color'] }};">
                                    <i class="bi bi-{{ $config['icon'] }} me-1"></i>{{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-medium">{{ $order->created_at->diffForHumans() }}</div>
                                <small class="text-muted">{{ $order->created_at->format('M d, h:i A') }}</small>
                            </td>
                            <td class="text-end px-4">
                                <div class="btn-group btn-group-sm">
                                    @if($order->status === 'pending')
                                        <button type="button" class="btn btn-action-sm" style="background: #22c55e; color: white;" title="Accept Order"
                                                onclick="updateOrderStatus({{ $order->id }}, 'confirmed', 'Accept Order')">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button type="button" class="btn btn-action-sm" style="background: #ef4444; color: white;" title="Reject Order"
                                                onclick="rejectOrder({{ $order->id }}, '{{ $order->order_number }}')">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    @elseif($order->status === 'confirmed')
                                        <button type="button" class="btn btn-action-sm" style="background: #3b82f6; color: white;" title="Start Preparing"
                                                onclick="updateOrderStatus({{ $order->id }}, 'preparing', 'Start Preparing')">
                                            <i class="bi bi-play-fill"></i>
                                        </button>
                                    @elseif($order->status === 'preparing')
                                        <button type="button" class="btn btn-action-sm" style="background: #8b5cf6; color: white;" title="Mark Ready"
                                                onclick="updateOrderStatus({{ $order->id }}, 'ready', 'Mark as Ready')">
                                            <i class="bi bi-bell"></i>
                                        </button>
                                    @elseif($order->status === 'ready')
                                        <button type="button" class="btn btn-action-sm" style="background: #22c55e; color: white;" title="Complete with QR"
                                                onclick="completeWithQR({{ $order->id }}, '{{ $order->order_number }}')">
                                            <i class="bi bi-qr-code-scan"></i>
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-action-sm" style="background: #6366f1; color: white;" title="View Receipt"
                                            onclick="viewOrder({{ $order->id }})">
                                        <i class="bi bi-receipt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div style="width: 80px; height: 80px; background: #f3f4f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                    <i class="bi bi-inbox fs-1 text-muted"></i>
                                </div>
                                <h6 class="text-muted mb-1">No orders found</h6>
                                <p class="text-muted small mb-0">Orders will appear here when customers place them</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($orders->hasPages())
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Showing {{ $orders->firstItem() ?? 0 }} to {{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }} orders
                </small>
                {{ $orders->withQueryString()->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
/* Row animations for CRUD operations */
@keyframes fadeOut {
    0% { opacity: 1; transform: translateX(0); }
    100% { opacity: 0; transform: translateX(-20px); }
}

.border-4 {
    border-width: 4px !important;
}

/* Action Button Styling */
.btn-action-sm {
    padding: 0.5rem 0.75rem;
    font-size: 0.8rem;
    transition: all 0.15s ease;
    border: none;
}
.btn-action-sm:hover {
    filter: brightness(0.9);
    transform: translateY(-1px);
}

/* Status Badge Styling */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 0.35rem 0.65rem;
    font-weight: 600;
    border-radius: 50px;
    font-size: 0.7rem;
    white-space: nowrap;
}

/* Table Font Size */
.orders-table {
    font-size: 0.85rem;
}
.orders-table th {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #64748b;
}
.orders-table td {
    vertical-align: middle;
    padding: 0.75rem 0.5rem;
}

/* Order Row Hover Effect */
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

/* Order Modal Card Style */
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
.order-modal-footer {
    padding: 1rem 1.5rem 1.5rem;
    display: flex;
    gap: 0.75rem;
}
.order-modal-footer .btn {
    flex: 1;
    padding: 0.75rem;
    border-radius: 12px;
    font-weight: 600;
}

/* Nav Pills Modern Style */
.nav-pills .nav-link {
    border-radius: 50px;
    padding: 0.5rem 1rem;
    font-weight: 500;
    color: #64748b;
    transition: all 0.2s ease;
}
.nav-pills .nav-link:hover {
    background: rgba(255, 149, 0, 0.1);
    color: #FF9500;
}
.nav-pills .nav-link.active {
    background: linear-gradient(135deg, #FF9500 0%, #FF6B00 100%);
    color: white;
}

/* Receipt Modal Styles */
.receipt-container {
    background: #fefefe;
    font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}
.receipt-header {
    text-align: center;
    padding: 1.5rem 1.5rem 1rem;
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    color: white;
    border-radius: 20px 20px 0 0;
}
.receipt-logo {
    width: 60px;
    height: 60px;
    background: rgba(255,255,255,0.15);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.75rem;
    font-size: 1.75rem;
}
.receipt-title {
    font-size: 1.25rem;
    font-weight: 700;
    margin: 0 0 0.25rem;
}
.receipt-order-number {
    font-size: 1.1rem;
    font-weight: 600;
    letter-spacing: 1px;
    margin-bottom: 0.5rem;
}
.receipt-status {
    display: inline-block;
    padding: 0.3rem 0.75rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.receipt-queue {
    margin-top: 0.5rem;
    font-size: 0.9rem;
    opacity: 0.9;
}
.receipt-separator {
    border-bottom: 2px dashed #e2e8f0;
    margin: 0 1.5rem;
}
.receipt-section {
    padding: 1rem 1.5rem;
}
.receipt-section-title {
    font-size: 0.7rem;
    font-weight: 700;
    color: #64748b;
    letter-spacing: 1px;
    margin-bottom: 0.75rem;
    text-transform: uppercase;
}
.receipt-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.4rem 0;
    font-size: 0.85rem;
}
.receipt-label {
    color: #64748b;
}
.receipt-value {
    font-weight: 600;
    color: #1e293b;
}
.receipt-items {
    margin-top: 0.5rem;
}
.receipt-item {
    padding: 0.5rem 0;
    border-bottom: 1px dotted #e2e8f0;
}
.receipt-item:last-child {
    border-bottom: none;
}
.receipt-item .item-name {
    font-weight: 600;
    color: #1e293b;
}
.receipt-item .item-total {
    font-weight: 700;
    color: #1e293b;
}
.receipt-item .item-qty {
    font-size: 0.75rem;
    margin-top: 0.15rem;
}
.receipt-total {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1e293b;
    padding: 0.75rem 0;
    border-top: 2px solid #1e293b;
    margin-top: 0.5rem;
}
.receipt-footer {
    text-align: center;
    padding: 1rem 1.5rem 1.5rem;
    background: #f8fafc;
    border-radius: 0 0 20px 20px;
}
.receipt-barcode {
    font-size: 2rem;
    color: #cbd5e1;
    margin-bottom: 0.5rem;
}
.receipt-thank-you {
    font-size: 0.85rem;
    color: #64748b;
    margin: 0;
}

/* Receipt Item with Image */
.receipt-item-img {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    object-fit: cover;
    margin-right: 0.75rem;
    flex-shrink: 0;
}
.receipt-item-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.75rem;
    flex-shrink: 0;
    color: white;
    font-size: 0.75rem;
}

/* Order Progress Bar */
.order-progress {
    padding: 2rem 1rem 1.5rem 1rem;
    background: #f8fafc;
}
.progress-steps {
    display: flex;
    justify-content: space-between;
    position: relative;
    margin-bottom: 0.5rem;
}
.progress-steps::before {
    content: '';
    position: absolute;
    top: 12px;
    left: 0;
    right: 0;
    height: 3px;
    background: #e2e8f0;
    z-index: 1;
}
.progress-line {
    position: absolute;
    top: 12px;
    left: 0;
    height: 3px;
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    z-index: 2;
    transition: width 0.3s ease;
}
.progress-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    z-index: 3;
    flex: 1;
}
.progress-step .step-icon {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    color: #94a3b8;
    margin-bottom: 0.35rem;
    transition: all 0.3s ease;
}
.progress-step.completed .step-icon {
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    color: white;
}
.progress-step.active .step-icon {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
}
.progress-step .step-label {
    font-size: 0.65rem;
    color: #94a3b8;
    font-weight: 500;
    text-align: center;
}
.progress-step.completed .step-label,
.progress-step.active .step-label {
    color: #1e293b;
}

/* QR Verification Modal */
.qr-verify-modal .modal-content-custom {
    max-width: 480px;
}
.qr-modal-header {
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    padding: 1.5rem;
    border-radius: 20px 20px 0 0;
    color: white;
    text-align: center;
}
.qr-modal-header .qr-icon {
    width: 60px;
    height: 60px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.75rem;
    font-size: 1.5rem;
}
.qr-modal-header h4 {
    margin: 0 0 0.25rem;
    font-weight: 700;
}
.qr-modal-header p {
    margin: 0;
    opacity: 0.9;
    font-size: 0.9rem;
}
.qr-modal-body {
    padding: 1.5rem;
}
.qr-method-tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}
.qr-method-tab {
    flex: 1;
    padding: 0.75rem;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    background: white;
    cursor: pointer;
    text-align: center;
    transition: all 0.2s;
}
.qr-method-tab:hover {
    border-color: #22c55e;
    background: #f0fdf4;
}
.qr-method-tab.active {
    border-color: #22c55e;
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    color: white;
}
.qr-method-tab i {
    font-size: 1.25rem;
    display: block;
    margin-bottom: 0.25rem;
}
.qr-method-tab span {
    font-size: 0.75rem;
    font-weight: 600;
}
.qr-input-section {
    display: none;
}
.qr-input-section.active {
    display: block;
}
.qr-camera-preview {
    width: 100%;
    height: 200px;
    background: #1e293b;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    margin-bottom: 1rem;
    overflow: hidden;
    position: relative;
}
.qr-camera-preview video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.qr-camera-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 150px;
    height: 150px;
    border: 3px solid rgba(255,255,255,0.5);
    border-radius: 12px;
}
.qr-upload-zone {
    border: 2px dashed #e2e8f0;
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    margin-bottom: 1rem;
}
.qr-upload-zone:hover {
    border-color: #22c55e;
    background: #f0fdf4;
}
.qr-upload-zone i {
    font-size: 2.5rem;
    color: #94a3b8;
    margin-bottom: 0.5rem;
}
.qr-upload-zone p {
    margin: 0;
    color: #64748b;
    font-size: 0.85rem;
}
.qr-upload-preview {
    max-width: 100%;
    max-height: 150px;
    border-radius: 8px;
    margin-bottom: 1rem;
}
.qr-manual-input {
    font-family: 'Courier New', monospace;
    font-size: 1.1rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    text-align: center;
    padding: 1rem;
    border-radius: 12px;
}
.qr-error-msg {
    background: #fef2f2;
    color: #dc2626;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    font-size: 0.85rem;
    margin-top: 1rem;
    display: none;
}
.qr-error-msg.show {
    display: block;
}
.qr-modal-footer {
    padding: 0 1.5rem 1.5rem;
    display: flex;
    gap: 0.75rem;
}
.qr-modal-footer .btn {
    flex: 1;
    padding: 0.875rem;
    border-radius: 12px;
    font-weight: 600;
}
</style>
@endpush

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

<!-- QR Verification Modal -->
<div id="qrVerifyModal" class="custom-modal qr-verify-modal">
    <div class="modal-backdrop-custom" onclick="closeQRModal()"></div>
    <div class="modal-content-custom">
        <button class="modal-close-btn" onclick="closeQRModal()"><i class="bi bi-x"></i></button>
        <div class="qr-modal-header">
            <div class="qr-icon"><i class="bi bi-qr-code-scan"></i></div>
            <h4>Complete Order</h4>
            <p id="qrModalOrderNumber"></p>
        </div>
        <div class="qr-modal-body">
            <!-- Method Tabs -->
            <div class="qr-method-tabs">
                <div class="qr-method-tab active" data-method="camera" onclick="switchQRMethod('camera')">
                    <i class="bi bi-camera"></i>
                    <span>Camera</span>
                </div>
                <div class="qr-method-tab" data-method="upload" onclick="switchQRMethod('upload')">
                    <i class="bi bi-upload"></i>
                    <span>Upload</span>
                </div>
                <div class="qr-method-tab" data-method="manual" onclick="switchQRMethod('manual')">
                    <i class="bi bi-keyboard"></i>
                    <span>Manual</span>
                </div>
            </div>
            
            <!-- Camera Section -->
            <div id="qrCameraSection" class="qr-input-section active">
                <div class="qr-camera-preview" id="qrCameraPreview">
                    <video id="qrVideo" autoplay playsinline></video>
                    <div class="qr-camera-overlay"></div>
                </div>
                <p class="text-center text-muted small mb-0">Point camera at customer's QR code</p>
            </div>
            
            <!-- Upload Section -->
            <div id="qrUploadSection" class="qr-input-section">
                <div class="qr-upload-zone" onclick="document.getElementById('qrFileInput').click()">
                    <i class="bi bi-cloud-arrow-up"></i>
                    <p>Click to upload QR code image</p>
                    <input type="file" id="qrFileInput" accept="image/*" style="display: none;" onchange="handleQRUpload(this)">
                </div>
                <div id="qrUploadPreviewContainer" style="display: none; text-align: center;">
                    <img id="qrUploadPreviewImg" class="qr-upload-preview">
                    <p class="text-muted small">Processing image...</p>
                </div>
            </div>
            
            <!-- Manual Entry Section -->
            <div id="qrManualSection" class="qr-input-section">
                <label class="form-label fw-semibold">Enter QR Code:</label>
                <input type="text" id="qrManualInput" class="form-control qr-manual-input" 
                       placeholder="PU-XXX-XXXXXXXX-XXXXXX" autocomplete="off">
                <p class="text-center text-muted small mt-2">Ask customer for their pickup code</p>
            </div>
            
            <!-- Error Message -->
            <div id="qrErrorMsg" class="qr-error-msg">
                <i class="bi bi-exclamation-circle me-1"></i>
                <span id="qrErrorText"></span>
            </div>
        </div>
        <div class="qr-modal-footer">
            <button type="button" class="btn btn-light" onclick="closeQRModal()">Cancel</button>
            <button type="button" class="btn btn-success" id="qrVerifyBtn" onclick="verifyQRCode()">
                <i class="bi bi-check-circle me-1"></i> Verify & Complete
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let currentQROrderId = null;
    let html5QrCode = null;
    let qrScanning = false;

    // View Order Modal - Receipt Style
    window.viewOrder = async function(orderId) {
        document.getElementById('orderViewModal').classList.add('show');
        document.getElementById('orderModalContent').innerHTML = '<div style="padding: 3rem; text-align: center;"><div class="spinner-border text-primary"></div></div>';

        try {
            const res = await fetch(`/vendor/orders/${orderId}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            if (data.success) {
                const order = data.order;
                const statusColors = {
                    'pending': 'background: #fbbf24; color: #1f2937;',
                    'confirmed': 'background: #22c55e; color: white;',
                    'preparing': 'background: #3b82f6; color: white;',
                    'ready': 'background: #8b5cf6; color: white;',
                    'completed': 'background: #1f2937; color: white;',
                    'cancelled': 'background: #ef4444; color: white;'
                };
                
                let subtotal = 0;
                let itemsHtml = order.items.map(item => {
                    const unitPrice = parseFloat(item.unit_price) || parseFloat(item.price) / item.quantity || 0;
                    const lineTotal = parseFloat(item.price) || 0;
                    subtotal += lineTotal;
                    
                    const itemImage = item.image 
                        ? `<img src="${item.image}" class="receipt-item-img" onerror="this.outerHTML='<div class=\\'receipt-item-placeholder\\'><i class=\\'bi bi-box\\'></i></div>'">`
                        : `<div class="receipt-item-placeholder"><i class="bi bi-box"></i></div>`;
                    
                    return `
                    <div class="receipt-item d-flex align-items-center">
                        ${itemImage}
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <span class="item-name">${item.item_name}</span>
                                <span class="item-total">RM ${lineTotal.toFixed(2)}</span>
                            </div>
                            <div class="item-qty text-muted">${item.quantity} × RM ${unitPrice.toFixed(2)}</div>
                        </div>
                    </div>`;
                }).join('');
                
                // Progress bar logic
                const statusOrder = ['pending', 'confirmed', 'preparing', 'ready', 'completed'];
                const currentIndex = statusOrder.indexOf(order.status);
                const isCancelled = order.status === 'cancelled';
                const progressPercent = isCancelled ? 0 : (currentIndex / (statusOrder.length - 1)) * 100;
                
                const progressHtml = `
                    <div class="order-progress">
                        <div class="progress-steps">
                            <div class="progress-line" style="width: ${progressPercent}%"></div>
                            <div class="progress-step ${currentIndex >= 0 ? 'completed' : ''} ${currentIndex === 0 ? 'active' : ''}">
                                <div class="step-icon"><i class="bi bi-clock"></i></div>
                                <span class="step-label">Pending</span>
                            </div>
                            <div class="progress-step ${currentIndex >= 1 ? 'completed' : ''} ${currentIndex === 1 ? 'active' : ''}">
                                <div class="step-icon"><i class="bi bi-check"></i></div>
                                <span class="step-label">Confirmed</span>
                            </div>
                            <div class="progress-step ${currentIndex >= 2 ? 'completed' : ''} ${currentIndex === 2 ? 'active' : ''}">
                                <div class="step-icon"><i class="bi bi-fire"></i></div>
                                <span class="step-label">Preparing</span>
                            </div>
                            <div class="progress-step ${currentIndex >= 3 ? 'completed' : ''} ${currentIndex === 3 ? 'active' : ''}">
                                <div class="step-icon"><i class="bi bi-bell"></i></div>
                                <span class="step-label">Ready</span>
                            </div>
                            <div class="progress-step ${currentIndex >= 4 ? 'completed' : ''} ${currentIndex === 4 ? 'active' : ''}">
                                <div class="step-icon"><i class="bi bi-bag-check"></i></div>
                                <span class="step-label">Done</span>
                            </div>
                        </div>
                    </div>`;
                
                // Customer avatar
                const customerAvatar = order.customer_avatar 
                    ? `<img src="${order.customer_avatar}" class="rounded-circle" width="32" height="32" style="object-fit: cover;" onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(order.customer_name)}&background=6c757d&color=fff&size=100'">`
                    : `<img src="https://ui-avatars.com/api/?name=${encodeURIComponent(order.customer_name)}&background=6c757d&color=fff&size=100" class="rounded-circle" width="32" height="32">`;

                document.getElementById('orderModalContent').innerHTML = `
                    <div class="receipt-container">
                        <!-- Receipt Header -->
                        <div class="receipt-header">
                            <div class="receipt-logo">
                                <i class="bi bi-receipt-cutoff"></i>
                            </div>
                            <h4 class="receipt-title">Order Receipt</h4>
                            <div class="receipt-order-number">${order.order_number}</div>
                            <span class="receipt-status" style="${statusColors[order.status] || ''}">${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</span>
                            ${order.queue_number ? `<div class="receipt-queue">Queue #${order.queue_number}</div>` : ''}
                        </div>
                        
                        <!-- Order Progress -->
                        ${progressHtml}
                        
                        <!-- Dashed Separator -->
                        <div class="receipt-separator"></div>
                        
                        <!-- Customer Info -->
                        <div class="receipt-section">
                            <div class="receipt-row">
                                <span class="receipt-label d-flex align-items-center gap-2">
                                    ${customerAvatar} Customer
                                </span>
                                <span class="receipt-value">${order.customer_name}</span>
                            </div>
                            <div class="receipt-row">
                                <span class="receipt-label"><i class="bi bi-calendar3 me-2"></i>Date & Time</span>
                                <span class="receipt-value">${order.created_at}</span>
                            </div>
                            <div class="receipt-row">
                                <span class="receipt-label"><i class="bi bi-credit-card me-2"></i>Payment</span>
                                <span class="receipt-value">${order.payment_method || 'Cash'}</span>
                            </div>
                        </div>
                        
                        <!-- Dashed Separator -->
                        <div class="receipt-separator"></div>
                        
                        <!-- Order Items -->
                        <div class="receipt-section">
                            <div class="receipt-section-title"><i class="bi bi-bag me-2"></i>ORDER ITEMS (${order.items.length})</div>
                            <div class="receipt-items">
                                ${itemsHtml}
                            </div>
                        </div>
                        
                        <!-- Dashed Separator -->
                        <div class="receipt-separator"></div>
                        
                        <!-- Totals -->
                        <div class="receipt-section">
                            <div class="receipt-row">
                                <span class="receipt-label">Subtotal</span>
                                <span class="receipt-value">RM ${subtotal.toFixed(2)}</span>
                            </div>
                            <div class="receipt-row receipt-total">
                                <span>TOTAL</span>
                                <span>RM ${(parseFloat(order.total) || subtotal).toFixed(2)}</span>
                            </div>
                        </div>
                        
                        <!-- Receipt Footer -->
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

    // Update order status with SweetAlert
    window.updateOrderStatus = function(orderId, newStatus, statusLabel) {
        Swal.fire({
            title: `${statusLabel}?`,
            text: `Update this order to "${statusLabel}"`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#FF9500',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, update it',
            showLoaderOnConfirm: true,
            preConfirm: async () => {
                try {
                    const res = await fetch(`/vendor/orders/${orderId}/status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ _method: 'PUT', status: newStatus })
                    });
                    const data = await res.json();
                    if (!data.success) throw new Error(data.message || 'Failed to update');
                    return data;
                } catch (error) {
                    Swal.showValidationMessage(error.message);
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Remove row from current view since it moved to different status tab
                const row = document.getElementById('order-row-' + orderId);
                if (row) {
                    row.style.animation = 'fadeOut 0.3s ease';
                    setTimeout(() => {
                        row.remove();
                        // Check if table is empty
                        const tbody = document.querySelector('.orders-table tbody');
                        if (tbody && tbody.querySelectorAll('.order-row').length === 0) {
                            tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5">
                                <div style="width: 80px; height: 80px; background: #f3f4f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                    <i class="bi bi-inbox fs-1 text-muted"></i>
                                </div>
                                <h6 class="text-muted mb-1">No orders found</h6>
                                <p class="text-muted small mb-0">Orders will appear here when customers place them</p>
                            </td></tr>`;
                        }
                    }, 300);
                }
                showToast(`Order status changed to ${statusLabel}`, 'success');
            }
        });
    };

    // Complete order with QR code verification - Opens custom modal
    window.completeWithQR = function(orderId, orderNumber) {
        currentQROrderId = orderId;
        document.getElementById('qrModalOrderNumber').textContent = orderNumber;
        document.getElementById('qrVerifyModal').classList.add('show');
        document.getElementById('qrErrorMsg').classList.remove('show');
        document.getElementById('qrManualInput').value = '';
        
        // Reset to camera tab
        switchQRMethod('camera');
    };

    // Switch QR input method
    window.switchQRMethod = function(method) {
        // Update tabs
        document.querySelectorAll('.qr-method-tab').forEach(tab => {
            tab.classList.toggle('active', tab.dataset.method === method);
        });
        
        // Update sections
        document.getElementById('qrCameraSection').classList.toggle('active', method === 'camera');
        document.getElementById('qrUploadSection').classList.toggle('active', method === 'upload');
        document.getElementById('qrManualSection').classList.toggle('active', method === 'manual');
        
        // Stop camera when switching away
        if (method !== 'camera') {
            stopQRScanner();
        } else {
            startQRScanner();
        }
        
        // Focus manual input
        if (method === 'manual') {
            setTimeout(() => document.getElementById('qrManualInput').focus(), 100);
        }
        
        // Hide error
        document.getElementById('qrErrorMsg').classList.remove('show');
    };

    // Start QR Scanner
    async function startQRScanner() {
        if (qrScanning) return;
        
        try {
            // Check if Html5Qrcode is available
            if (typeof Html5Qrcode === 'undefined') {
                throw new Error('QR library not loaded');
            }
            
            html5QrCode = new Html5Qrcode("qrCameraPreview");
            await html5QrCode.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 150, height: 150 } },
                onQRCodeScanned,
                () => {} // Ignore scan errors
            );
            qrScanning = true;
        } catch (err) {
            console.log('Camera not available:', err);
            document.getElementById('qrCameraPreview').innerHTML = `
                <div class="text-center p-4">
                    <i class="bi bi-camera-video-off fs-1 mb-2 d-block"></i>
                    <p class="mb-1 small fw-semibold">Camera not available</p>
                    <p class="mb-2 small text-muted">Please use Upload or Manual entry</p>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="switchQRMethod('manual')">
                        <i class="bi bi-keyboard me-1"></i> Enter Manually
                    </button>
                </div>
            `;
        }
    }

    // Stop QR Scanner
    function stopQRScanner() {
        if (html5QrCode && qrScanning) {
            html5QrCode.stop().catch(() => {});
            qrScanning = false;
        }
    }

    // Handle scanned QR code
    function onQRCodeScanned(decodedText) {
        stopQRScanner();
        submitQRCode(decodedText);
    }

    // Handle QR upload
    window.handleQRUpload = function(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            document.getElementById('qrUploadPreviewContainer').style.display = 'block';
            document.querySelector('.qr-upload-zone').style.display = 'none';
            
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('qrUploadPreviewImg').src = e.target.result;
                
                // Decode QR from image
                const tempQr = new Html5Qrcode("qrUploadPreviewContainer");
                tempQr.scanFile(file, true)
                    .then(decodedText => {
                        submitQRCode(decodedText);
                    })
                    .catch(err => {
                        showQRError('Could not read QR code from image. Please try another image or enter manually.');
                        resetUploadSection();
                    });
            };
            reader.readAsDataURL(file);
        }
    };

    function resetUploadSection() {
        document.getElementById('qrUploadPreviewContainer').style.display = 'none';
        document.querySelector('.qr-upload-zone').style.display = 'block';
        document.getElementById('qrFileInput').value = '';
    }

    // Verify QR Code (manual entry)
    window.verifyQRCode = function() {
        const activeSection = document.querySelector('.qr-input-section.active');
        
        if (activeSection.id === 'qrManualSection') {
            const qrCode = document.getElementById('qrManualInput').value.trim().toUpperCase();
            if (!qrCode) {
                showQRError('Please enter the QR code');
                return;
            }
            submitQRCode(qrCode);
        } else {
            showQRError('Please scan a QR code or switch to Manual entry');
        }
    };

    // Submit QR code to server
    async function submitQRCode(qrCode) {
        qrCode = qrCode.trim().toUpperCase();
        
        if (!qrCode.startsWith('PU-')) {
            showQRError('Invalid QR code format. Code should start with PU-');
            return;
        }
        
        const btn = document.getElementById('qrVerifyBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Verifying...';
        
        try {
            const res = await fetch(`/vendor/orders/${currentQROrderId}/complete-pickup`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ qr_code: qrCode })
            });
            
            // Check if response is ok
            if (!res.ok) {
                const text = await res.text();
                console.error('Server error:', res.status, text);
                throw new Error(`Server error: ${res.status}`);
            }
            
            const data = await res.json();
            
            if (data.success) {
                closeQRModal();
                // Remove row from current view
                const row = document.getElementById('order-row-' + currentQROrderId);
                if (row) {
                    row.style.animation = 'fadeOut 0.3s ease';
                    setTimeout(() => {
                        row.remove();
                        const tbody = document.querySelector('.orders-table tbody');
                        if (tbody && tbody.querySelectorAll('.order-row').length === 0) {
                            tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5">
                                <div style="width: 80px; height: 80px; background: #f3f4f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                    <i class="bi bi-inbox fs-1 text-muted"></i>
                                </div>
                                <h6 class="text-muted mb-1">No orders found</h6>
                            </td></tr>`;
                        }
                    }, 300);
                }
                showToast('Order completed successfully!', 'success');
            } else {
                showQRError(data.message || 'Invalid QR code');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Verify & Complete';
            }
        } catch (error) {
            console.error('QR verification error:', error);
            showQRError(error.message || 'An error occurred. Please try again.');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Verify & Complete';
        }
    }

    // Show error message
    function showQRError(message) {
        document.getElementById('qrErrorText').textContent = message;
        document.getElementById('qrErrorMsg').classList.add('show');
    }

    // Close QR Modal
    window.closeQRModal = function() {
        stopQRScanner();
        document.getElementById('qrVerifyModal').classList.remove('show');
        currentQROrderId = null;
        resetUploadSection();
    };

    // Reject order with reason
    window.rejectOrder = function(orderId, orderNumber) {
        Swal.fire({
            title: 'Reject Order?',
            html: `
                <p class="mb-3">Are you sure you want to reject order <strong>${orderNumber}</strong>?</p>
                <div class="text-start">
                    <label class="form-label fw-semibold">Reason for rejection:</label>
                    <select id="reject-reason" class="form-select mb-2">
                        <option value="Out of stock">Out of stock</option>
                        <option value="Kitchen too busy">Kitchen too busy</option>
                        <option value="Store closing soon">Store closing soon</option>
                        <option value="Item unavailable">Item unavailable</option>
                        <option value="other">Other (specify below)</option>
                    </select>
                    <textarea id="reject-note" class="form-control" rows="2" placeholder="Additional notes (optional)"></textarea>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-x-circle me-1"></i> Reject Order',
            cancelButtonText: 'Cancel',
            showLoaderOnConfirm: true,
            preConfirm: async () => {
                const reason = document.getElementById('reject-reason').value;
                const note = document.getElementById('reject-note').value;
                const fullReason = reason === 'other' ? note : (note ? `${reason}: ${note}` : reason);
                
                try {
                    const res = await fetch(`/vendor/orders/${orderId}/status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ _method: 'PUT', status: 'cancelled', reason: fullReason })
                    });
                    const data = await res.json();
                    if (!data.success) throw new Error(data.message || 'Failed to reject order');
                    return data;
                } catch (error) {
                    Swal.showValidationMessage(error.message);
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Remove row from current view
                const row = document.getElementById('order-row-' + orderId);
                if (row) {
                    row.style.animation = 'fadeOut 0.3s ease';
                    setTimeout(() => {
                        row.remove();
                        const tbody = document.querySelector('.orders-table tbody');
                        if (tbody && tbody.querySelectorAll('.order-row').length === 0) {
                            tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5">
                                <div style="width: 80px; height: 80px; background: #f3f4f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                    <i class="bi bi-inbox fs-1 text-muted"></i>
                                </div>
                                <h6 class="text-muted mb-1">No orders found</h6>
                            </td></tr>`;
                        }
                    }, 300);
                }
                showToast('Order rejected successfully', 'success');
            }
        });
    };

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeOrderModal();
            closeQRModal();
        }
    });
});
</script>
@endpush
@endsection

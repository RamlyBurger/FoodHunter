@extends('layouts.app')

@section('title', 'Vendor Dashboard - FoodHunter')

@section('content')
<!-- Dashboard Header -->
<section class="bg-light py-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-6 fw-bold mb-2">Vendor Dashboard</h1>
                <p class="text-muted mb-0">Welcome back, {{ auth()->user()->name }}!</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="{{ route('vendor.menu') }}" class="btn btn-primary rounded-pill">
                    <i class="bi bi-plus-circle me-2"></i> Add Menu Item
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Dashboard Content -->
<section class="py-5">
    <div class="container">
        <!-- Statistics Cards -->
        <div class="row g-4 mb-5">
            <div class="col-lg-3 col-md-6" data-aos="fade-up">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1">Today's Orders</p>
                            <h3 class="fw-bold mb-0">{{ $todayOrders }}</h3>
                            @if($orderGrowth >= 0)
                                <small class="text-success">
                                    <i class="bi bi-arrow-up"></i> {{ number_format(abs($orderGrowth), 1) }}% vs yesterday
                                </small>
                            @else
                                <small class="text-danger">
                                    <i class="bi bi-arrow-down"></i> {{ number_format(abs($orderGrowth), 1) }}% vs yesterday
                                </small>
                            @endif
                        </div>
                        <div class="stat-icon primary">
                            <i class="bi bi-receipt"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1">Today's Revenue</p>
                            <h3 class="fw-bold mb-0">RM {{ number_format($todayRevenue, 2) }}</h3>
                            @if($revenueGrowth >= 0)
                                <small class="text-success">
                                    <i class="bi bi-arrow-up"></i> {{ number_format(abs($revenueGrowth), 1) }}% vs yesterday
                                </small>
                            @else
                                <small class="text-danger">
                                    <i class="bi bi-arrow-down"></i> {{ number_format(abs($revenueGrowth), 1) }}% vs yesterday
                                </small>
                            @endif
                        </div>
                        <div class="stat-icon success">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1">Accepted Orders</p>
                            <h3 class="fw-bold mb-0">{{ $pendingOrders }}</h3>
                            <small class="{{ $pendingOrders > 0 ? 'text-success' : 'text-muted' }}">
                                {{ $pendingOrders > 0 ? 'Ready to prepare' : 'All clear' }}
                            </small>
                        </div>
                        <div class="stat-icon success">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1">Menu Items</p>
                            <h3 class="fw-bold mb-0">{{ $totalMenuItems }}</h3>
                            <small class="text-muted">
                                {{ $availableMenuItems }} available
                            </small>
                        </div>
                        <div class="stat-icon danger">
                            <i class="bi bi-grid-3x3-gap"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-4">
            <!-- Recent Orders -->
            <div class="col-lg-8">
                <div class="card border-0" data-aos="fade-up">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">Recent Orders</h5>
                            <a href="{{ route('vendor.orders') }}" class="text-decoration-none">View All</a>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Items</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentOrders as $order)
                                    <tr>
                                        <td class="fw-bold">#{{ $order->order_id }}</td>
                                        <td>{{ $order->user->name }}</td>
                                        <td>
                                            @php
                                                $firstItem = $order->orderItems->first();
                                                $remainingCount = $order->orderItems->count() - 1;
                                            @endphp
                                            @if($firstItem)
                                                {{ $firstItem->menuItem->name }} x{{ $firstItem->quantity }}
                                                @if($remainingCount > 0)
                                                    <small class="text-muted">, +{{ $remainingCount }} more</small>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="fw-bold">RM {{ number_format($order->total_price, 2) }}</td>
                                        <td>
                                            @if($order->status === 'pending')
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @elseif($order->status === 'accepted')
                                                <span class="badge bg-success">Accepted</span>
                                            @elseif($order->status === 'preparing')
                                                <span class="badge bg-primary">Preparing</span>
                                            @elseif($order->status === 'ready')
                                                <span class="badge bg-info">Ready</span>
                                            @elseif($order->status === 'completed')
                                                <span class="badge bg-dark">Completed</span>
                                            @else
                                                <span class="badge bg-danger">{{ ucfirst($order->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('vendor.orders') }}" class="btn btn-sm btn-outline-primary rounded-pill">View</a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            No orders yet
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Menu Management -->
                <div class="card border-0 mt-4" data-aos="fade-up">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">Menu Items</h5>
                            <div>
                                <select class="form-select form-select-sm">
                                    <option selected>All Items</option>
                                    <option>Available</option>
                                    <option>Out of Stock</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row g-3">
                            @forelse($recentMenuItems as $item)
                            <div class="col-md-6">
                                <div class="card border">
                                    <div class="row g-0">
                                        <div class="col-4">
                                            @if($item->image_path && file_exists(public_path($item->image_path)))
                                                <img src="{{ asset($item->image_path) }}" 
                                                     class="img-fluid h-100 rounded-start object-fit-cover" alt="{{ $item->name }}">
                                            @else
                                                <div class="bg-light h-100 d-flex align-items-center justify-content-center rounded-start">
                                                    <i class="bi bi-image fs-2 text-muted"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-8">
                                            <div class="card-body p-3">
                                                <h6 class="fw-bold mb-1">{{ Str::limit($item->name, 20) }}</h6>
                                                <p class="text-muted small mb-2">RM {{ number_format($item->price, 2) }}</p>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" disabled {{ $item->is_available ? 'checked' : '' }}>
                                                    <label class="form-check-label small {{ $item->is_available ? 'text-success' : 'text-danger' }}">
                                                        {{ $item->is_available ? 'Available' : 'Out of Stock' }}
                                                    </label>
                                                </div>
                                                <div class="mt-2">
                                                    <a href="{{ route('vendor.menu') }}" class="btn btn-sm btn-outline-primary">Manage</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-12">
                                <p class="text-center text-muted">No menu items yet. <a href="{{ route('vendor.menu') }}">Add your first item</a></p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Sidebar -->
            <div class="col-lg-4">
                <!-- Quick Actions -->
                <div class="card border-0 mb-4" data-aos="fade-left">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">Quick Actions</h6>
                        <div class="d-grid gap-2">
                            <a href="{{ route('vendor.menu') }}" class="btn btn-primary rounded-pill">
                                <i class="bi bi-plus-circle me-2"></i> Add Menu Item
                            </a>
                            <a href="{{ route('vendor.menu') }}" class="btn btn-outline-primary rounded-pill">
                                <i class="bi bi-pencil me-2"></i> Edit Menu
                            </a>
                            <a href="{{ route('vendor.reports') }}" class="btn btn-outline-primary rounded-pill">
                                <i class="bi bi-graph-up me-2"></i> View Analytics
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Sales Chart -->
                <div class="card border-0 mb-4" data-aos="fade-left" data-aos-delay="100">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">This Week's Sales</h6>
                        <div class="text-center py-4">
                            <h2 class="display-6 fw-bold text-primary mb-2">RM {{ number_format($weekRevenue, 2) }}</h2>
                            @if($weekGrowth >= 0)
                                <p class="text-success mb-0">
                                    <i class="bi bi-arrow-up"></i> {{ number_format(abs($weekGrowth), 1) }}% from last week
                                </p>
                            @else
                                <p class="text-danger mb-0">
                                    <i class="bi bi-arrow-down"></i> {{ number_format(abs($weekGrowth), 1) }}% from last week
                                </p>
                            @endif
                        </div>
                        <div class="row text-center mt-3">
                            <div class="col-6">
                                <p class="text-muted small mb-1">Orders</p>
                                <h6 class="fw-bold">{{ $weekOrders }}</h6>
                            </div>
                            <div class="col-6">
                                <p class="text-muted small mb-1">Avg. Order</p>
                                <h6 class="fw-bold">RM {{ number_format($avgOrderValue, 2) }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Top Selling Items -->
                <div class="card border-0" data-aos="fade-left" data-aos-delay="200">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">Top Selling Items</h6>
                        @forelse($topSellingItems as $item)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center">
                                    @if($item->image_path && file_exists(public_path($item->image_path)))
                                        <img src="{{ asset($item->image_path) }}" 
                                             class="rounded me-2 object-fit-cover" width="40" height="40" alt="{{ $item->name }}">
                                    @else
                                        <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                    @endif
                                    <span class="small">{{ Str::limit($item->name, 20) }}</span>
                                </div>
                                <span class="badge bg-primary">{{ $item->total_sold }}</span>
                            </div>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar" data-percentage="{{ $item->percentage }}" style="width: 0%"></div>
                            </div>
                        </div>
                        @empty
                        <p class="text-center text-muted small">No sales data available for this week</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
// Set progress bar widths from data attributes
document.querySelectorAll('.progress-bar[data-percentage]').forEach(function(bar) {
    bar.style.width = bar.dataset.percentage + '%';
});
</script>
@endpush

@extends('layouts.app')

@section('title', 'My Orders - FoodHunter')

@section('content')
<!-- Page Header -->
<section class="bg-light py-4">
    <div class="container">
        <div class="row">
            <div class="col-12" data-aos="fade-up">
                <h1 class="display-5 fw-bold mb-2">My Orders</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item active">Orders</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Orders Content -->
<section class="py-5">
    <div class="container">
        <!-- Filters -->
        <div class="card border-0 mb-4" data-aos="fade-up">
            <div class="card-body p-4">
                <form method="GET" action="{{ route('orders') }}" id="filter-form">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <div class="search-box">
                                <input type="text" name="search" class="form-control" placeholder="Search orders..." value="{{ request('search') }}">
                                <i class="bi bi-search search-icon"></i>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="status" onchange="document.getElementById('filter-form').submit()">
                                <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Orders</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Accepted</option>
                                <option value="preparing" {{ request('status') === 'preparing' ? 'selected' : '' }}>Preparing</option>
                                <option value="ready" {{ request('status') === 'ready' ? 'selected' : '' }}>Ready</option>
                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="date_range" onchange="document.getElementById('filter-form').submit()">
                                <option value="30days" {{ request('date_range') === '30days' || !request('date_range') ? 'selected' : '' }}>Last 30 days</option>
                                <option value="7days" {{ request('date_range') === '7days' ? 'selected' : '' }}>Last 7 days</option>
                                <option value="3months" {{ request('date_range') === '3months' ? 'selected' : '' }}>Last 3 months</option>
                                <option value="1year" {{ request('date_range') === '1year' ? 'selected' : '' }}>Last year</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-outline-primary w-100">
                                <i class="bi bi-funnel me-2"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Active Order (Status Tracking) -->
        @if($activeOrder)
        <div class="card border-0 mb-4" data-aos="fade-up">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-bold mb-1">Order #{{ $activeOrder->order_id }}</h5>
                        <p class="text-muted mb-0"><small>Placed on {{ $activeOrder->created_at->format('M d, Y \\a\\t h:i A') }}</small></p>
                    </div>
                    <span class="badge bg-{{ $activeOrder->status === 'pending' ? 'warning' : ($activeOrder->status === 'preparing' ? 'info' : ($activeOrder->status === 'ready' ? 'primary' : 'success')) }} text-white px-3 py-2 rounded-pill">
                        {{ ucfirst($activeOrder->status) }}
                    </span>
                </div>
                
                <!-- Order Status Timeline -->
                <div class="order-status mb-4">
                    <div class="status-step {{ in_array($activeOrder->status, ['accepted', 'preparing', 'ready', 'completed']) ? 'completed' : ($activeOrder->status === 'pending' ? 'active' : '') }}">
                        <div class="status-icon">
                            <i class="bi bi-{{ in_array($activeOrder->status, ['accepted', 'preparing', 'ready', 'completed']) ? 'check-lg' : 'clock' }}"></i>
                        </div>
                        <small class="fw-bold">{{ $activeOrder->status === 'pending' ? 'Pending' : 'Accepted' }}</small>
                        <div class="status-line"></div>
                    </div>
                    <div class="status-step {{ in_array($activeOrder->status, ['preparing', 'ready', 'completed']) ? 'completed' : ($activeOrder->status === 'accepted' ? 'active' : '') }}">
                        <div class="status-icon">
                            <i class="bi bi-egg-fried"></i>
                        </div>
                        <small class="fw-bold">Preparing</small>
                        <div class="status-line"></div>
                    </div>
                    <div class="status-step {{ in_array($activeOrder->status, ['ready', 'completed']) ? 'completed' : ($activeOrder->status === 'preparing' ? 'active' : '') }}">
                        <div class="status-icon">
                            <i class="bi bi-bell"></i>
                        </div>
                        <small class="fw-bold">Ready</small>
                        <div class="status-line"></div>
                    </div>
                    <div class="status-step {{ $activeOrder->status === 'completed' ? 'completed' : ($activeOrder->status === 'ready' ? 'active' : '') }}">
                        <div class="status-icon">
                            <i class="bi bi-bag-check"></i>
                        </div>
                        <small class="fw-bold">Completed</small>
                    </div>
                </div>
                
                <!-- Order Items -->
                <div class="bg-light rounded-3 p-3 mb-3">
                    @foreach($activeOrder->orderItems as $index => $item)
                    <div class="d-flex align-items-center {{ $index < count($activeOrder->orderItems) - 1 ? 'mb-2' : '' }}">
                        @if($item->menuItem && $item->menuItem->image)
                        <img src="{{ asset('storage/' . $item->menuItem->image) }}" 
                             class="rounded me-3" width="60" height="60" style="object-fit: cover;" alt="{{ $item->menuItem->name }}">
                        @else
                        <div class="bg-white rounded me-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="bi bi-image text-muted"></i>
                        </div>
                        @endif
                        <div class="flex-grow-1">
                            <h6 class="mb-0 fw-bold">{{ $item->menuItem->name ?? 'Item' }} x{{ $item->quantity }}</h6>
                            <small class="text-muted">{{ $activeOrder->vendor->name ?? 'Vendor' }}</small>
                        </div>
                        <span class="fw-bold">RM {{ number_format($item->subtotal, 2) }}</span>
                    </div>
                    @endforeach
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total:</span>
                            <span class="fw-bold text-primary">RM {{ number_format($activeOrder->total_price, 2) }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-md-end">
                            @if($activeOrder->pickup)
                            <p class="mb-2"><strong>Queue Number:</strong> <span class="badge bg-primary fs-5 px-3 py-2">{{ $activeOrder->pickup->queue_number }}</span></p>
                            <p class="mb-2"><small class="text-muted">Estimated ready time: {{ $activeOrder->pickup->estimated_pickup_time ? \Carbon\Carbon::parse($activeOrder->pickup->estimated_pickup_time)->format('h:i A') : '20-25 mins' }}</small></p>
                            @endif
                            <a href="{{ route('order.details', $activeOrder->order_id) }}" class="btn btn-outline-primary btn-sm rounded-pill mt-2">
                                <i class="bi bi-eye me-2"></i> View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Order History -->
        <h5 class="fw-bold mb-3" data-aos="fade-up">Order History</h5>
        
        @forelse($orders as $order)
        <!-- Order Card -->
        <div class="card border-0 mb-3" data-aos="fade-up" data-aos-delay="{{ $loop->index * 50 }}">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="fw-bold mb-1">Order #{{ $order->order_id }}</h6>
                        <p class="text-muted mb-0"><small>{{ $order->created_at->format('M d, Y \\a\\t h:i A') }}</small></p>
                    </div>
                    <span class="badge bg-{{ $order->status === 'completed' ? 'success' : ($order->status === 'cancelled' ? 'danger' : ($order->status === 'ready' ? 'primary' : ($order->status === 'preparing' ? 'info' : 'warning'))) }} text-white px-3 py-2 rounded-pill">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        @foreach($order->orderItems->take(2) as $item)
                        <div class="d-flex align-items-center mb-2">
                            @if($item->menuItem && $item->menuItem->image)
                            <img src="{{ asset('storage/' . $item->menuItem->image) }}" 
                                 class="rounded me-3" width="50" height="50" style="object-fit: cover;" alt="{{ $item->menuItem->name }}">
                            @else
                            <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-image text-muted"></i>
                            </div>
                            @endif
                            <div>
                                <h6 class="mb-0">{{ $item->menuItem->name ?? 'Item' }} x{{ $item->quantity }}</h6>
                                <small class="text-muted">{{ $order->vendor->name ?? 'Vendor' }}</small>
                            </div>
                        </div>
                        @endforeach
                        @if($order->orderItems->count() > 2)
                        <small class="text-muted">+{{ $order->orderItems->count() - 2 }} more item(s)</small>
                        @endif
                    </div>
                    <div class="col-md-3 text-md-center">
                        <p class="mb-0"><strong>Total:</strong></p>
                        <h6 class="fw-bold text-primary mb-0">RM {{ number_format($order->total_price, 2) }}</h6>
                    </div>
                    <div class="col-md-3 text-md-end">
                        <a href="{{ route('order.details', $order->order_id) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                            View Details
                        </a>
                        <form action="{{ route('order.reorder', $order->order_id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-primary rounded-pill mt-2">
                                <i class="bi bi-arrow-repeat me-1"></i> Reorder
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="card border-0 mb-3" data-aos="fade-up">
            <div class="card-body p-5 text-center">
                <i class="bi bi-inbox fs-1 text-muted mb-3"></i>
                <h5 class="fw-bold mb-2">No Orders Found</h5>
                <p class="text-muted mb-4">You haven't placed any orders yet</p>
                <a href="{{ route('menu') }}" class="btn btn-primary rounded-pill">
                    <i class="bi bi-search me-2"></i> Browse Menu
                </a>
            </div>
        </div>
        @endforelse
        
        <!-- Pagination -->
        @if($orders->hasPages())
        <nav class="mt-4" data-aos="fade-up">
            {{ $orders->links() }}
        </nav>
        @endif
    </div>
</section>
@endsection

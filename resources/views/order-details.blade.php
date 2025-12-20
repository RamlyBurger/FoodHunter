@extends('layouts.app')

@section('title', 'Order Details - FoodHunter')

@section('content')
<!-- Page Header -->
<section class="bg-light py-4">
    <div class="container">
        <div class="row">
            <div class="col-12" data-aos="fade-up">
                <h1 class="display-6 fw-bold mb-2">Order #{{ $order->order_id }}</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('orders') }}" class="text-decoration-none">Orders</a></li>
                        <li class="breadcrumb-item active">Order #{{ $order->order_id }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Order Details -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <!-- Order Status Card -->
                <div class="card border-0 shadow-sm mb-4" data-aos="fade-up">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="fw-bold mb-1">Order Status</h5>
                                <small class="text-muted">
                                    <i class="bi bi-clock me-1"></i>
                                    {{ $order->created_at->format('d M Y, h:i A') }}
                                </small>
                            </div>
                            <span class="badge bg-{{ $order->status === 'completed' ? 'success' : ($order->status === 'cancelled' ? 'danger' : ($order->status === 'ready' ? 'primary' : ($order->status === 'preparing' ? 'info' : 'warning'))) }} text-white px-3 py-2">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                        
                        @if(in_array($order->status, ['pending', 'accepted', 'preparing', 'ready']))
                        <!-- Order Status Timeline -->
                        <div class="order-status mb-3">
                            <div class="status-step {{ in_array($order->status, ['accepted', 'preparing', 'ready', 'completed']) ? 'completed' : ($order->status === 'pending' ? 'active' : '') }}">
                                <div class="status-icon">
                                    <i class="bi bi-{{ in_array($order->status, ['accepted', 'preparing', 'ready', 'completed']) ? 'check-lg' : 'clock' }}"></i>
                                </div>
                                <small class="fw-bold">{{ $order->status === 'pending' ? 'Pending' : 'Accepted' }}</small>
                                <div class="status-line"></div>
                            </div>
                            <div class="status-step {{ in_array($order->status, ['preparing', 'ready', 'completed']) ? 'completed' : ($order->status === 'accepted' ? 'active' : '') }}">
                                <div class="status-icon">
                                    <i class="bi bi-egg-fried"></i>
                                </div>
                                <small class="fw-bold">Preparing</small>
                                <div class="status-line"></div>
                            </div>
                            <div class="status-step {{ in_array($order->status, ['ready', 'completed']) ? 'completed' : ($order->status === 'preparing' ? 'active' : '') }}">
                                <div class="status-icon">
                                    <i class="bi bi-bell"></i>
                                </div>
                                <small class="fw-bold">Ready</small>
                                <div class="status-line"></div>
                            </div>
                            <div class="status-step {{ $order->status === 'completed' ? 'completed' : ($order->status === 'ready' ? 'active' : '') }}">
                                <div class="status-icon">
                                    <i class="bi bi-bag-check"></i>
                                </div>
                                <small class="fw-bold">Completed</small>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                
                <!-- Order Items -->
                <div class="card border-0 shadow-sm mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">Order Items</h5>
                        
                        @foreach($order->orderItems as $item)
                        <div class="d-flex align-items-center {{ !$loop->last ? 'mb-3 pb-3 border-bottom' : '' }}">
                            @if($item->menuItem && $item->menuItem->image)
                            <img src="{{ asset('storage/' . $item->menuItem->image) }}" 
                                 alt="{{ $item->menuItem->name }}" 
                                 class="rounded me-3"
                                 style="width: 80px; height: 80px; object-fit: cover;">
                            @else
                            <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center"
                                 style="width: 80px; height: 80px;">
                                <i class="bi bi-image fs-3 text-muted"></i>
                            </div>
                            @endif
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-bold">{{ $item->menuItem->name ?? 'Item' }}</h6>
                                <p class="text-muted mb-1">
                                    <small>{{ $item->menuItem->description ?? '' }}</small>
                                </p>
                                <p class="mb-0">
                                    <small class="text-muted">Qty: {{ $item->quantity }} × RM {{ number_format($item->price, 2) }}</small>
                                </p>
                            </div>
                            <div class="text-end">
                                <h6 class="mb-0 fw-bold">RM {{ number_format($item->subtotal, 2) }}</h6>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Queue Number & QR Code -->
                @if($order->pickup)
                <div class="card border-0 shadow-sm mb-4" data-aos="fade-up">
                    <div class="card-body p-4 text-center">
                        <h6 class="fw-bold mb-3">Queue Number</h6>
                        <div class="bg-primary text-white rounded-3 py-4 mb-3">
                            <h1 class="display-3 fw-bold mb-0">{{ $order->pickup->queue_number }}</h1>
                        </div>
                        
                        @if($order->pickup->qr_code)
                        <h6 class="fw-bold mb-3">Pickup QR Code</h6>
                        <div class="bg-light rounded-3 p-3 mb-3">
                            <img src="{{ asset('storage/' . $order->pickup->qr_code) }}" 
                                 alt="QR Code" 
                                 class="img-fluid" 
                                 style="max-width: 200px;">
                        </div>
                        <small class="text-muted">Show this code when collecting your order</small>
                        @endif
                        
                        @if($order->pickup->estimated_pickup_time)
                        <div class="alert alert-info border-0 mt-3 mb-0">
                            <i class="bi bi-clock-history me-2"></i>
                            <small>Estimated ready: {{ \Carbon\Carbon::parse($order->pickup->estimated_pickup_time)->format('h:i A') }}</small>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
                
                <!-- Order Summary -->
                <div class="card border-0 shadow-sm mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">Order Summary</h6>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>RM {{ number_format($order->orderItems->sum('subtotal'), 2) }}</span>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-bold">Total:</span>
                            <h5 class="mb-0 text-primary fw-bold">RM {{ number_format($order->total_price, 2) }}</h5>
                        </div>
                        
                        @if($order->payment)
                        <div class="alert alert-light border mb-0">
                            <small class="d-block text-muted mb-1">Payment Method</small>
                            <strong class="text-capitalize">
                                @if($order->payment->payment_method === 'online')
                                    <i class="bi bi-credit-card me-1"></i> Online Payment
                                @elseif($order->payment->payment_method === 'ewallet')
                                    <i class="bi bi-wallet2 me-1"></i> TNG eWallet
                                @else
                                    <i class="bi bi-cash me-1"></i> Cash on Pickup
                                @endif
                            </strong>
                            <div class="mt-2">
                                <span class="badge bg-{{ $order->payment->payment_status === 'completed' ? 'success' : ($order->payment->payment_status === 'pending' ? 'warning' : 'danger') }} text-white">
                                    {{ ucfirst($order->payment->payment_status) }}
                                </span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                
                <!-- Vendor Information -->
                <div class="card border-0 shadow-sm mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">Vendor Details</h6>
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                 style="width: 50px; height: 50px;">
                                <i class="bi bi-shop fs-4"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">{{ $order->vendor->name ?? 'Vendor' }}</h6>
                                <small class="text-muted">
                                    <i class="bi bi-geo-alt me-1"></i>{{ $order->vendor->stall_location ?? 'Canteen' }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="d-grid gap-2" data-aos="fade-up" data-aos-delay="300">
                    <a href="{{ route('orders') }}" class="btn btn-outline-primary rounded-pill">
                        <i class="bi bi-arrow-left me-2"></i> Back to Orders
                    </a>
                    <form action="{{ route('order.reorder', $order->order_id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary rounded-pill w-100">
                            <i class="bi bi-arrow-repeat me-2"></i> Reorder
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

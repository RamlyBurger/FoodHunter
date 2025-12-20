@extends('layouts.app')

@section('title', 'Order Confirmation')

@section('content')
<!-- Success Header -->
<section class="py-5 bg-success text-white text-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8" data-aos="zoom-in">
                <div class="mb-4">
                    <i class="bi bi-check-circle-fill" style="font-size: 80px;"></i>
                </div>
                <h1 class="fw-bold mb-3">Order Placed Successfully!</h1>
                <p class="lead mb-0">Your order has been received and is being prepared</p>
            </div>
        </div>
    </div>
</section>

<!-- Order Details -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Order Information Card -->
                <div class="card border-0 shadow-sm mb-4" data-aos="fade-up">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="fw-bold mb-1">Order #{{ $order->order_id }}</h5>
                                <small class="text-muted">
                                    <i class="bi bi-clock me-1"></i>
                                    {{ $order->created_at->format('d M Y, h:i A') }}
                                </small>
                            </div>
                            <span class="badge bg-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'completed' ? 'success' : 'info') }} text-white px-3 py-2">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                        
                        <!-- Vendor Information -->
                        <div class="alert alert-light border mb-4">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-shop fs-3 text-primary me-3"></i>
                                <div>
                                    <h6 class="mb-0 fw-bold">{{ $order->vendor->name ?? 'Vendor' }}</h6>
                                    <small class="text-muted">
                                        <i class="bi bi-geo-alt me-1"></i>{{ $order->vendor->stall_location ?? 'Canteen' }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Queue Number & QR Code -->
                        @if($order->pickup)
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="card bg-primary text-white border-0 h-100">
                                    <div class="card-body text-center p-4">
                                        <small class="d-block mb-2 opacity-75">Your Queue Number</small>
                                        <h1 class="display-3 fw-bold mb-0">{{ $order->pickup->queue_number }}</h1>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 bg-light h-100">
                                    <div class="card-body text-center p-4">
                                        <small class="d-block mb-3 text-muted">Pickup QR Code</small>
                                        @if($order->pickup->qr_code)
                                        <img src="{{ asset('storage/' . $order->pickup->qr_code) }}" 
                                             alt="QR Code" 
                                             class="img-fluid" 
                                             style="max-width: 150px;">
                                        @else
                                        <div class="bg-white p-3 d-inline-block">
                                            <i class="bi bi-qr-code fs-1 text-muted"></i>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Estimated Pickup Time -->
                        <div class="alert alert-info border-0 mb-4">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-clock-history fs-3 me-3"></i>
                                <div>
                                    <h6 class="mb-1 fw-bold">Estimated Pickup Time</h6>
                                    <p class="mb-0">{{ $order->pickup->estimated_pickup_time ? \Carbon\Carbon::parse($order->pickup->estimated_pickup_time)->format('h:i A') : '20-25 minutes' }}</p>
                                    <small class="text-muted">Please show this QR code when collecting your order</small>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Order Items -->
                        <h6 class="fw-bold mb-3">Order Items</h6>
                        <div class="table-responsive mb-4">
                            <table class="table table-borderless">
                                <tbody>
                                    @foreach($order->orderItems as $item)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($item->menuItem && $item->menuItem->image)
                                                <img src="{{ asset('storage/' . $item->menuItem->image) }}" 
                                                     alt="{{ $item->menuItem->name }}" 
                                                     class="rounded me-3"
                                                     style="width: 60px; height: 60px; object-fit: cover;">
                                                @else
                                                <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center"
                                                     style="width: 60px; height: 60px;">
                                                    <i class="bi bi-image text-muted"></i>
                                                </div>
                                                @endif
                                                <div>
                                                    <h6 class="mb-0">{{ $item->menuItem->name ?? 'Item' }}</h6>
                                                    <small class="text-muted">{{ $item->quantity }}x RM {{ number_format($item->price, 2) }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end align-middle">
                                            <strong>RM {{ number_format($item->subtotal, 2) }}</strong>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="border-top">
                                    <tr>
                                        <td class="text-end"><strong>Subtotal:</strong></td>
                                        <td class="text-end">
                                            <strong>RM {{ number_format($order->orderItems->sum('subtotal'), 2) }}</strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-end">Service Fee:</td>
                                        <td class="text-end">
                                            RM {{ number_format(2.00 * ($order->orderItems->sum('subtotal') / ($order->orderItems->sum('subtotal') + 2.00)), 2) }}
                                        </td>
                                    </tr>
                                    @php
                                        $itemsTotal = $order->orderItems->sum('subtotal');
                                        $estimatedDiscount = $itemsTotal + 2.00 - $order->total_price;
                                    @endphp
                                    @if($estimatedDiscount > 0.01)
                                    <tr>
                                        <td class="text-end text-success"><strong><i class="bi bi-gift me-1"></i> Voucher Discount:</strong></td>
                                        <td class="text-end text-success">
                                            <strong>- RM {{ number_format($estimatedDiscount, 2) }}</strong>
                                        </td>
                                    </tr>
                                    @endif
                                    <tr class="border-top">
                                        <td class="text-end"><strong>Total Amount:</strong></td>
                                        <td class="text-end">
                                            <h5 class="mb-0 text-primary fw-bold">RM {{ number_format($order->total_price, 2) }}</h5>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <!-- Payment Information -->
                        @if($order->payment)
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3">Payment Details</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <small class="text-muted d-block">Payment Method</small>
                                        <strong class="text-capitalize">
                                            @if($order->payment->payment_method === 'online')
                                                <i class="bi bi-credit-card me-1"></i> Online Banking / Card
                                            @elseif($order->payment->payment_method === 'ewallet')
                                                <i class="bi bi-wallet2 me-1"></i> TNG eWallet
                                            @else
                                                <i class="bi bi-cash me-1"></i> Cash on Pickup
                                            @endif
                                        </strong>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <small class="text-muted d-block">Payment Status</small>
                                        <span class="badge bg-{{ $order->payment->payment_status === 'completed' ? 'success' : ($order->payment->payment_status === 'pending' ? 'warning' : 'danger') }} text-white">
                                            {{ ucfirst($order->payment->payment_status) }}
                                        </span>
                                    </div>
                                    @if($order->payment->transaction_id)
                                    <div class="col-12 mt-2">
                                        <small class="text-muted d-block">Transaction ID</small>
                                        <code>{{ $order->payment->transaction_id }}</code>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Loyalty Points Earned -->
                        <div class="alert alert-success border-0 mt-4">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-star-fill text-warning fs-3 me-3"></i>
                                <div>
                                    <h6 class="mb-1 fw-bold">Loyalty Points Earned!</h6>
                                    <p class="mb-0">You've earned <strong>{{ floor($order->total_price) }} points</strong> from this order</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="row g-3" data-aos="fade-up" data-aos-delay="100">
                    <div class="col-md-6">
                        <a href="{{ route('menu') }}" class="btn btn-outline-primary btn-lg w-100 rounded-pill">
                            <i class="bi bi-arrow-left me-2"></i> Continue Shopping
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('orders') }}" class="btn btn-primary btn-lg w-100 rounded-pill">
                            <i class="bi bi-list-check me-2"></i> View My Orders
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

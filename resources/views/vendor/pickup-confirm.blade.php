{{--
|==============================================================================
| Pickup Confirmation Page - Low Nam Lee (Order & Pickup Module)
|==============================================================================
|
| @author     Low Nam Lee
| @module     Order & Pickup Module
|
| Displays order pickup confirmation after QR code verification.
| Shows order details and completion status to vendor.
|==============================================================================
--}}

@extends('layouts.vendor')

@section('title', 'Confirm Pickup')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="content-card">
            <div class="content-card-header text-center" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                <h5 class="text-white mb-0"><i class="bi bi-check-circle me-2"></i>Order Verified</h5>
            </div>
            <div class="content-card-body">
                <div class="text-center mb-4">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center" 
                         style="width: 100px; height: 100px; background: rgba(40, 167, 69, 0.1);">
                        <span class="display-3 fw-bold" style="color: #FF6B35;">{{ $pickup->queue_number }}</span>
                    </div>
                    <h3 class="mt-3 fw-bold">Queue #{{ $pickup->queue_number }}</h3>
                </div>

                <div class="bg-light rounded-3 p-3 mb-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <small class="text-muted">Order Number</small>
                            <div class="fw-semibold">{{ $order->order_number }}</div>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Customer</small>
                            <div class="fw-semibold">{{ $order->user->name }}</div>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Total</small>
                            <div class="fw-bold" style="color: #FF6B35;">RM {{ number_format($order->total, 2) }}</div>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Payment</small>
                            <div>
                                @if($order->payment && $order->payment->status === 'paid')
                                <span class="badge bg-success">Paid</span>
                                @else
                                <span class="badge bg-warning text-dark">{{ ucfirst($order->payment->status ?? 'pending') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="text-muted mb-2">Items</h6>
                    @foreach($order->items as $item)
                    <div class="d-flex justify-content-between py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <span>{{ $item->quantity }}x {{ $item->item_name }}</span>
                        <span class="text-muted">RM {{ number_format($item->subtotal, 2) }}</span>
                    </div>
                    @endforeach
                </div>

                <button type="button" class="btn btn-success btn-lg w-100 mb-2" onclick="completePickup({{ $order->id }})">
                    <i class="bi bi-bag-check me-2"></i>Complete Pickup
                </button>

                <a href="{{ route('vendor.scan') }}" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-qr-code-scan me-2"></i>Scan Another
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
async function completePickup(orderId) {
    try {
        const res = await fetch(`/vendor/orders/${orderId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ _method: 'PUT', status: 'completed' })
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Pickup Complete!',
                text: data.message || 'Order has been marked as completed.',
                timer: 2000,
                showConfirmButton: false
            }).then(() => window.location.href = '/vendor/dashboard');
        } else {
            Swal.fire('Error', data.message || 'Failed to complete pickup', 'error');
        }
    } catch (e) {
        Swal.fire('Error', 'An error occurred. Please try again.', 'error');
    }
}
</script>
@endpush
@endsection

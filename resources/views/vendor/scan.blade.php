@extends('layouts.vendor')

@section('title', 'Scan QR Code')

@section('content')
<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">QR Code Scanner</h4>
        <p class="text-muted mb-0">Verify customer pickups by scanning or entering QR codes</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="content-card mb-4">
            <div class="content-card-header">
                <h5><i class="bi bi-qr-code-scan me-2"></i>Scan Customer QR Code</h5>
            </div>
            <div class="content-card-body">
                <p class="text-muted mb-4">Enter or scan the customer's pickup QR code to verify and complete their order.</p>

                    <form action="{{ route('vendor.scan.verify') }}" method="POST" id="scanForm">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label">QR Code / Pickup Code</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text"><i class="bi bi-qr-code"></i></span>
                                <input type="text" name="qr_code" id="qrCodeInput" class="form-control form-control-lg" 
                                       placeholder="Enter or scan QR code" autofocus required>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Verify
                                </button>
                            </div>
                            <small class="text-muted">The code looks like: PU-20251221-ABC123</small>
                        </div>
                    </form>

                <hr>

                <div class="text-center">
                    <h6 class="text-muted mb-3">Or use camera to scan</h6>
                    <button type="button" class="btn btn-outline-primary btn-lg" id="startCameraBtn">
                        <i class="bi bi-camera"></i> Open Camera Scanner
                    </button>
                    <div id="cameraContainer" class="d-none mt-3">
                        <video id="cameraPreview" class="w-100 rounded" style="max-height: 300px;"></video>
                        <button type="button" class="btn btn-secondary mt-2" id="stopCameraBtn">
                            <i class="bi bi-x-lg"></i> Close Camera
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ready Orders Quick List -->
    <div class="col-lg-4">
        <div class="content-card">
            <div class="content-card-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                <h5 class="text-white mb-0"><i class="bi bi-clock-history me-2"></i>Ready for Pickup</h5>
            </div>
            <div class="content-card-body p-0">
                    @php
                        $readyOrders = \App\Models\Order::where('vendor_id', $vendor->id)
                            ->where('status', 'ready')
                            ->with(['user', 'pickup'])
                            ->orderBy('updated_at', 'asc')
                            ->get();
                    @endphp

                @if($readyOrders->isEmpty())
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-emoji-smile" style="font-size: 2rem; opacity: 0.3;"></i>
                    <p class="mb-0 mt-2 small">No orders waiting</p>
                </div>
                @else
                    @foreach($readyOrders as $order)
                    <div class="d-flex justify-content-between align-items-center p-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div>
                            <h5 class="mb-0 fw-bold" style="color: #FF6B35;">#{{ $order->pickup->queue_number ?? 'N/A' }}</h5>
                            <small class="text-muted">{{ $order->user->name }}</small>
                            <br>
                            <small class="text-muted">RM {{ number_format((float)$order->total, 2) }}</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="confirmPickupComplete({{ $order->id }})">
                            <i class="bi bi-check2-circle"></i>
                        </button>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('scanForm');
    const input = document.getElementById('qrCodeInput');
    const startCameraBtn = document.getElementById('startCameraBtn');
    const stopCameraBtn = document.getElementById('stopCameraBtn');
    const cameraContainer = document.getElementById('cameraContainer');
    const cameraPreview = document.getElementById('cameraPreview');
    let stream = null;

    // AJAX form submission - Uses Student 4's validatePickupQr API
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // First validate QR using Student 4's API
        fetch('/api/orders/validate-pickup', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + (document.querySelector('meta[name="api-token"]')?.content || '')
            },
            body: JSON.stringify({ qr_code: input.value })
        })
        .then(res => res.json())
        .then(response => {
            // Handle Student 4's API response format
            const data = response.data || response;
            if (response.success && data.valid) {
                Swal.fire({
                    icon: 'success',
                    title: 'Order Found!',
                    html: `
                        <div class="text-start">
                            <p><strong>Order:</strong> #${data.order_number}</p>
                            <p><strong>Customer:</strong> ${data.customer_name}</p>
                            <p><strong>Queue:</strong> <span class="fs-4 fw-bold text-primary">#${data.queue_number}</span></p>
                            <p><strong>Total:</strong> RM ${parseFloat(data.total).toFixed(2)}</p>
                            <p><strong>Items:</strong> ${data.items_count} item(s)</p>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bi bi-check-lg"></i> Complete Pickup',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Complete pickup via AJAX
                        completePickupAjax(data.order_id);
                    }
                });
            } else {
                showToast(data.message, 'error');
            }
            input.value = '';
        })
        .catch(err => {
            showToast('Error verifying QR code. Please try again.', 'error');
        });
    });

    // Camera scanning (basic implementation)
    startCameraBtn.addEventListener('click', async function() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: 'environment' } 
            });
            cameraPreview.srcObject = stream;
            cameraPreview.play();
            cameraContainer.classList.remove('d-none');
            startCameraBtn.classList.add('d-none');
        } catch (err) {
            showToast('Unable to access camera. Please enter the code manually.', 'warning');
        }
    });

    stopCameraBtn.addEventListener('click', function() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
        cameraContainer.classList.add('d-none');
        startCameraBtn.classList.remove('d-none');
    });
});

function confirmPickupComplete(orderId) {
    Swal.fire({
        title: 'Complete Pickup?',
        text: 'Mark this order as collected by the customer?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#34C759',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, complete it',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            completePickupAjax(orderId);
        }
    });
}

// Complete pickup via AJAX
async function completePickupAjax(orderId) {
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
            // Reset scanner UI instead of reload
            const orderDetails = document.getElementById('order-details');
            if (orderDetails) {
                orderDetails.innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-qr-code-scan fs-1 text-primary mb-3 d-block"></i>
                        <h5 class="text-muted">Ready to scan next order</h5>
                        <p class="text-muted small">Scan a QR code to verify pickup</p>
                    </div>
                `;
            }
            // Reset any scanner state if needed
            if (typeof resetScanner === 'function') {
                resetScanner();
            }
            showToast(data.message || 'Pickup completed successfully!', 'success');
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

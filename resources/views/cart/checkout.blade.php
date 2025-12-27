{{--
|==============================================================================
| Checkout Page - Lee Song Yan (Cart, Checkout & Notifications Module)
|==============================================================================
|
| @author     Lee Song Yan
| @module     Cart, Checkout & Notifications Module
| @pattern    Observer Pattern (triggers notifications on order creation)
|
| Secure checkout with payment processing and voucher application.
| Integrates with Lee Kin Hang's VoucherFactory for discount calculations.
|==============================================================================
--}}

@extends('layouts.app')

@section('title', 'Checkout')

@push('styles')
<style>
    .checkout-container {
        max-width: 1100px;
        margin: 0 auto;
    }
    .checkout-step {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: #f8f9fa;
        border-radius: 50px;
        font-weight: 600;
        color: #6c757d;
    }
    .checkout-step.active {
        background: var(--primary-color);
        color: white;
    }
    .checkout-step .step-number {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: rgba(255,255,255,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
    }
    .payment-card {
        border: 2px solid #e9ecef;
        border-radius: 16px;
        padding: 1.25rem;
        cursor: pointer;
        transition: all 0.2s ease;
        background: white;
    }
    .payment-card:hover {
        border-color: #dee2e6;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .payment-card.selected {
        border-color: var(--primary-color);
        background: linear-gradient(135deg, rgba(255,107,53,0.05), rgba(255,149,0,0.08));
        box-shadow: 0 4px 20px rgba(255,107,53,0.15);
    }
    .payment-card input[type="radio"] {
        display: none;
    }
    .payment-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .payment-fields {
        display: none;
        margin-top: 1.25rem;
        padding: 1.25rem;
        background: #f8f9fa;
        border-radius: 12px;
        animation: fadeSlideIn 0.3s ease;
    }
    .payment-fields.show {
        display: block;
    }
    @keyframes fadeSlideIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .order-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 12px;
        margin-bottom: 0.75rem;
    }
    .order-item:last-child {
        margin-bottom: 0;
    }
    .order-item-image {
        width: 56px;
        height: 56px;
        border-radius: 10px;
        object-fit: cover;
    }
    .summary-card {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        border-radius: 20px;
        color: white;
        overflow: hidden;
    }
    .summary-card .card-header {
        background: transparent;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        padding: 1.25rem 1.5rem;
    }
    .summary-card .card-body {
        padding: 1.5rem;
    }
    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.75rem;
        opacity: 0.85;
    }
    .summary-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 1rem;
        margin-top: 1rem;
        border-top: 1px solid rgba(255,255,255,0.15);
    }
    .btn-checkout {
        background: linear-gradient(135deg, var(--primary-color), #ff9500);
        border: none;
        padding: 1rem 2rem;
        font-size: 1.1rem;
        font-weight: 600;
        border-radius: 12px;
        transition: all 0.3s ease;
    }
    .btn-checkout:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(255,107,53,0.35);
    }
    .stripe-element {
        padding: 12px 14px;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        background: white;
        transition: border-color 0.2s;
    }
    .stripe-element.StripeElement--focus {
        border-color: var(--primary-color);
    }
    .stripe-element.StripeElement--invalid {
        border-color: #dc3545;
    }
    .secure-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: rgba(255,255,255,0.1);
        border-radius: 50px;
        font-size: 0.85rem;
    }
</style>
@endpush

@section('content')
<div class="container checkout-container" style="padding-top: 100px; padding-bottom: 50px;">
    <!-- Header -->
    <div class="text-center mb-5">
        <h1 class="fw-bold mb-3">Checkout</h1>
        <div class="d-flex justify-content-center gap-3">
            <div class="checkout-step">
                <span class="step-number">✓</span>
                <span>Cart</span>
            </div>
            <div class="checkout-step active">
                <span class="step-number">2</span>
                <span>Payment</span>
            </div>
            <div class="checkout-step">
                <span class="step-number">3</span>
                <span>Complete</span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column - Payment -->
        <div class="col-md-7">
            <form action="{{ url('/checkout') }}" method="POST" id="checkout-form">
                @csrf
                <input type="hidden" name="stripe_payment_method_id" id="stripe_payment_method_id">
                {{-- Security: Idempotency key for replay attack prevention [OWASP 64] --}}
                <input type="hidden" name="idempotency_key" id="idempotency_key" value="{{ Str::uuid() }}">
                
                <!-- Order Items Preview -->
                <div class="mb-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-bag-check me-2"></i>Your Order ({{ count($cartItems) }} items)</h5>
                    @foreach($cartItems as $item)
                    <div class="order-item">
                        <img src="{{ \App\Helpers\ImageHelper::menuItem($item->menuItem->image) }}" 
                             class="order-item-image"
                             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($item->menuItem->name) }}&background=f3f4f6&color=9ca3af&size=100'">
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-semibold">{{ $item->menuItem->name }}</h6>
                            <small class="text-muted">{{ $item->menuItem->vendor->store_name ?? '' }} • Qty: {{ $item->quantity }}</small>
                        </div>
                        <span class="fw-bold" style="color: var(--primary-color);">RM {{ number_format($item->menuItem->price * $item->quantity, 2) }}</span>
                    </div>
                    @endforeach
                </div>

                <!-- Payment Methods -->
                <div class="mb-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-credit-card me-2"></i>Payment Method</h5>
                    
                    <div class="row g-3">
                        <!-- Credit/Debit Card (Stripe) -->
                        <div class="col-md-6">
                            <label class="payment-card w-100 h-100" id="option-card">
                                <input type="radio" name="payment_method" value="card" checked>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="payment-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                                        <i class="bi bi-credit-card-2-front text-white"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">Card Payment</h6>
                                        <small class="text-muted">Visa, Mastercard, AMEX</small>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <!-- E-Wallet -->
                        <div class="col-md-6">
                            <label class="payment-card w-100 h-100" id="option-ewallet">
                                <input type="radio" name="payment_method" value="ewallet">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="payment-icon" style="background: linear-gradient(135deg, #11998e, #38ef7d);">
                                        <i class="bi bi-wallet2 text-white"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">E-Wallet</h6>
                                        <small class="text-muted">TNG, GrabPay, ShopeePay</small>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <!-- Online Banking -->
                        <div class="col-md-6">
                            <label class="payment-card w-100 h-100" id="option-online_banking">
                                <input type="radio" name="payment_method" value="online_banking">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="payment-icon" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                                        <i class="bi bi-bank text-white"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">Online Banking</h6>
                                        <small class="text-muted">FPX - All Malaysian Banks</small>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <!-- Cash -->
                        <div class="col-md-6">
                            <label class="payment-card w-100 h-100" id="option-cash">
                                <input type="radio" name="payment_method" value="cash">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="payment-icon" style="background: linear-gradient(135deg, #ffecd2, #fcb69f);">
                                        <i class="bi bi-cash" style="color: #e67e22;"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">Cash on Pickup</h6>
                                        <small class="text-muted">Pay when collecting</small>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Card Payment Fields (Stripe) -->
                    <div class="payment-fields show" id="fields-card">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Card Details</label>
                            <div id="card-element" class="stripe-element"></div>
                            <div id="card-errors" class="text-danger small mt-2"></div>
                        </div>
                        <div class="d-flex align-items-center gap-2 text-muted small">
                            <i class="bi bi-shield-lock"></i>
                            <span>Your payment is secured with 256-bit SSL encryption</span>
                        </div>
                    </div>

                    <!-- E-Wallet Fields -->
                    <div class="payment-fields" id="fields-ewallet">
                        <div class="row g-2">
                            <div class="col-4">
                                <div class="border rounded-3 p-3 text-center cursor-pointer ewallet-option" data-wallet="tng" onclick="selectWallet(this)">
                                    <i class="bi bi-wallet fs-2 d-block mb-1" style="color: #0066cc;"></i>
                                    <small class="fw-semibold">Touch n Go</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded-3 p-3 text-center cursor-pointer ewallet-option" data-wallet="grabpay" onclick="selectWallet(this)">
                                    <i class="bi bi-wallet fs-2 d-block mb-1" style="color: #00b14f;"></i>
                                    <small class="fw-semibold">GrabPay</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded-3 p-3 text-center cursor-pointer ewallet-option" data-wallet="shopeepay" onclick="selectWallet(this)">
                                    <i class="bi bi-wallet fs-2 d-block mb-1" style="color: #ee4d2d;"></i>
                                    <small class="fw-semibold">ShopeePay</small>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="ewallet_type" id="ewallet_type">
                    </div>

                    <!-- Online Banking Fields -->
                    <div class="payment-fields" id="fields-online_banking">
                        <div class="row g-2">
                            <div class="col-4">
                                <div class="border rounded-3 p-3 text-center cursor-pointer bank-option" data-bank="maybank" onclick="selectBank(this)">
                                    <i class="bi bi-bank fs-2 d-block mb-1" style="color: #ffc107;"></i>
                                    <small class="fw-semibold">Maybank</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded-3 p-3 text-center cursor-pointer bank-option" data-bank="cimb" onclick="selectBank(this)">
                                    <i class="bi bi-bank fs-2 d-block mb-1" style="color: #dc3545;"></i>
                                    <small class="fw-semibold">CIMB</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded-3 p-3 text-center cursor-pointer bank-option" data-bank="publicbank" onclick="selectBank(this)">
                                    <i class="bi bi-bank fs-2 d-block mb-1" style="color: #e91e63;"></i>
                                    <small class="fw-semibold">Public Bank</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded-3 p-3 text-center cursor-pointer bank-option" data-bank="rhb" onclick="selectBank(this)">
                                    <i class="bi bi-bank fs-2 d-block mb-1" style="color: #0d6efd;"></i>
                                    <small class="fw-semibold">RHB</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded-3 p-3 text-center cursor-pointer bank-option" data-bank="hongleong" onclick="selectBank(this)">
                                    <i class="bi bi-bank fs-2 d-block mb-1" style="color: #198754;"></i>
                                    <small class="fw-semibold">Hong Leong</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded-3 p-3 text-center cursor-pointer bank-option" data-bank="ambank" onclick="selectBank(this)">
                                    <i class="bi bi-bank fs-2 d-block mb-1" style="color: #6f42c1;"></i>
                                    <small class="fw-semibold">AmBank</small>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="bank_code" id="bank_code">
                    </div>

                    <!-- Cash Fields -->
                    <div class="payment-fields" id="fields-cash">
                        <div class="alert alert-warning mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-info-circle fs-5"></i>
                            <span>Please prepare exact amount when collecting your order.</span>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mb-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-chat-left-text me-2"></i>Order Notes (Optional)</h5>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Any special requests or instructions..."></textarea>
                </div>
            </form>
        </div>

        <!-- Right Column - Summary -->
        <div class="col-md-5">
            <div class="summary-card" style="position: sticky; top: 100px;">
                <div class="card-header">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-receipt me-2"></i>Order Summary</h5>
                </div>
                <div class="card-body">
                    @foreach($cartItems as $item)
                    <div class="summary-row">
                        <span>{{ Str::limit($item->menuItem->name, 22) }} × {{ $item->quantity }}</span>
                        <span>RM {{ number_format($item->menuItem->price * $item->quantity, 2) }}</span>
                    </div>
                    @endforeach
                    
                    <hr style="border-color: rgba(255,255,255,0.15); margin: 1rem 0;">
                    
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>RM {{ number_format($summary['subtotal'], 2) }}</span>
                    </div>
                    <div class="summary-row">
                        <span>Service Fee</span>
                        <span>RM {{ number_format($summary['service_fee'], 2) }}</span>
                    </div>
                    @if($summary['discount'] > 0)
                    <div class="summary-row" style="color: #4ade80;">
                        <span><i class="bi bi-ticket-perforated me-1"></i>Voucher</span>
                        <span>-RM {{ number_format($summary['discount'], 2) }}</span>
                    </div>
                    @endif
                    
                    <div class="summary-total">
                        <span class="fs-5">Total</span>
                        <span class="fs-3 fw-bold" style="color: #fbbf24;">RM {{ number_format($summary['total'], 2) }}</span>
                    </div>
                    
                    <button type="button" class="btn btn-checkout btn-primary w-100 mt-4" id="submit-btn" onclick="processPayment()">
                        <i class="bi bi-lock-fill me-2"></i>Pay RM {{ number_format($summary['total'], 2) }}
                    </button>
                    
                    <div class="text-center mt-3">
                        <div class="secure-badge">
                            <i class="bi bi-shield-check"></i>
                            <span>Secure Checkout</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
const stripe = Stripe('{{ config("services.stripe.key") }}');
const elements = stripe.elements();
const cardElement = elements.create('card', {
    style: {
        base: {
            fontSize: '16px',
            color: '#1a1a2e',
            fontFamily: 'system-ui, -apple-system, sans-serif',
            '::placeholder': { color: '#aab7c4' }
        },
        invalid: { color: '#dc3545' }
    }
});
cardElement.mount('#card-element');

cardElement.on('change', function(event) {
    const errorEl = document.getElementById('card-errors');
    errorEl.textContent = event.error ? event.error.message : '';
});

document.addEventListener('DOMContentLoaded', function() {
    const paymentCards = document.querySelectorAll('.payment-card');
    
    paymentCards.forEach(card => {
        card.addEventListener('click', function() {
            paymentCards.forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            this.querySelector('input[type="radio"]').checked = true;
            updatePaymentFields();
        });
    });
    
    // Set initial state
    document.querySelector('.payment-card').classList.add('selected');
    updatePaymentFields();
});

function updatePaymentFields() {
    const selected = document.querySelector('input[name="payment_method"]:checked');
    if (!selected) return;
    
    document.querySelectorAll('.payment-fields').forEach(f => f.classList.remove('show'));
    const fields = document.getElementById('fields-' + selected.value);
    if (fields) fields.classList.add('show');
}

function selectWallet(el) {
    document.querySelectorAll('.ewallet-option').forEach(o => {
        o.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
    });
    el.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
    document.getElementById('ewallet_type').value = el.dataset.wallet;
}

function selectBank(el) {
    document.querySelectorAll('.bank-option').forEach(o => {
        o.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
    });
    el.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
    document.getElementById('bank_code').value = el.dataset.bank;
}

async function processPayment() {
    const form = document.getElementById('checkout-form');
    const paymentMethod = form.querySelector('input[name="payment_method"]:checked');
    const submitBtn = document.getElementById('submit-btn');
    
    if (!paymentMethod) {
        Swal.fire({ title: 'Select Payment', text: 'Please select a payment method.', icon: 'warning', confirmButtonColor: '#FF6B35' });
        return;
    }
    
    // Validate based on payment method
    if (paymentMethod.value === 'online_banking') {
        const bank = document.getElementById('bank_code').value;
        if (!bank) {
            Swal.fire({ title: 'Select Bank', text: 'Please select your bank.', icon: 'warning', confirmButtonColor: '#FF6B35' });
            return;
        }
    }
    
    if (paymentMethod.value === 'ewallet') {
        const wallet = document.getElementById('ewallet_type').value;
        if (!wallet) {
            Swal.fire({ title: 'Select E-Wallet', text: 'Please select an e-wallet.', icon: 'warning', confirmButtonColor: '#FF6B35' });
            return;
        }
    }
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Validating...';
    
    try {
        // Validate vendor availability using Lee Kin Hang's API before processing
        @if(isset($vendor) && $vendor)
        const vendorAvailability = await fetch('/api/vendors/{{ $vendor->id }}/availability', {
            headers: { 'Accept': 'application/json' }
        }).then(r => r.json());
        
        if (!vendorAvailability.success || !vendorAvailability.data?.is_currently_open) {
            const closedReason = vendorAvailability.data?.closed_reason || 'Vendor is currently closed';
            Swal.fire({ 
                title: 'Vendor Closed', 
                text: `${vendorAvailability.data?.store_name || 'The vendor'} is currently unavailable. ${closedReason}`, 
                icon: 'warning', 
                confirmButtonColor: '#FF6B35' 
            });
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-lock-fill me-2"></i>Pay RM {{ number_format($summary["total"], 2) }}';
            return;
        }
        @endif
        
        // Validate cart using Lee Song Yan's Cart Validation API before processing
        const cartValidation = await fetch('/api/cart/validate', {
            headers: {
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + (document.querySelector('meta[name="api-token"]')?.content || '')
            }
        }).then(r => r.json());
        
        if (!cartValidation.success || !cartValidation.data?.valid) {
            const issues = cartValidation.data?.issues || [];
            let issueMsg = 'Some items in your cart are no longer available.';
            if (issues.length > 0) {
                issueMsg = issues.map(i => i.item_name + ': ' + i.type.replace('_', ' ')).join(', ');
            }
            Swal.fire({ title: 'Cart Issue', text: issueMsg, icon: 'warning', confirmButtonColor: '#FF6B35' });
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-lock-fill me-2"></i>Pay RM {{ number_format($summary["total"], 2) }}';
            return;
        }
        
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
        
        if (paymentMethod.value === 'card') {
            // Create Stripe payment method
            const { paymentMethod: stripePaymentMethod, error } = await stripe.createPaymentMethod({
                type: 'card',
                card: cardElement
            });
            
            if (error) {
                document.getElementById('card-errors').textContent = error.message;
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-lock-fill me-2"></i>Pay RM {{ number_format($summary["total"], 2) }}';
                return;
            }
            
            document.getElementById('stripe_payment_method_id').value = stripePaymentMethod.id;
        }
        
        // Use AJAX instead of form.submit()
        const formData = new FormData(form);
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Order Placed!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                if (data.data?.redirect) {
                    window.location.href = data.data.redirect;
                } else {
                    window.location.href = '/orders';
                }
            });
        } else {
            Swal.fire({ title: 'Error', text: data.message || 'Failed to place order.', icon: 'error', confirmButtonColor: '#FF6B35' });
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-lock-fill me-2"></i>Pay RM {{ number_format($summary["total"], 2) }}';
        }
    } catch (err) {
        Swal.fire({ title: 'Error', text: 'Payment failed. Please try again.', icon: 'error', confirmButtonColor: '#FF6B35' });
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-lock-fill me-2"></i>Pay RM {{ number_format($summary["total"], 2) }}';
    }
}
</script>
@endpush

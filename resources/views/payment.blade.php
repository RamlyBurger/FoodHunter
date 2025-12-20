@extends('layouts.app')

@section('title', 'Payment - FoodHunter')

@section('content')
<!-- Page Header -->
<section class="bg-light py-4">
    <div class="container">
        <div class="row">
            <div class="col-12" data-aos="fade-up">
                <h1 class="display-5 fw-bold mb-2">Checkout</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('/cart') }}" class="text-decoration-none">Cart</a></li>
                        <li class="breadcrumb-item active">Checkout</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Checkout Steps -->
<section class="py-3 bg-white border-bottom">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-center">
                    <div class="d-flex align-items-center me-4">
                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-2" 
                             style="width: 30px; height: 30px;">
                            <i class="bi bi-check"></i>
                        </div>
                        <span class="fw-bold">Cart</span>
                    </div>
                    <div class="d-flex align-items-center me-4">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" 
                             style="width: 30px; height: 30px;">
                            2
                        </div>
                        <span class="fw-bold">Payment</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-light text-muted d-flex align-items-center justify-content-center me-2" 
                             style="width: 30px; height: 30px;">
                            3
                        </div>
                        <span class="text-muted">Confirmation</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Payment Content -->
<section class="py-5">
    <div class="container">
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        
        <form action="{{ route('checkout.process') }}" method="POST" id="checkout-form">
            @csrf
            <div class="row g-4">
            <!-- Payment Methods -->
            <div class="col-lg-8">
                <h5 class="fw-bold mb-4" data-aos="fade-right">Select Payment Method</h5>
                
                <!-- Online Payment -->
                <div class="payment-method selected" data-aos="fade-up">
                    <input type="radio" name="payment_method" id="online" value="online" checked>
                    <div class="d-flex align-items-center">
                        <label for="online" class="flex-grow-1 cursor-pointer">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-credit-card fs-3 text-primary me-3"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">Online Banking / Card</h6>
                                    <small class="text-muted">Test Card: 4111111111111111</small>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- E-Wallet -->
                <div class="payment-method" data-aos="fade-up" data-aos-delay="100">
                    <input type="radio" name="payment_method" id="ewallet" value="ewallet">
                    <div class="d-flex align-items-center">
                        <label for="ewallet" class="flex-grow-1 cursor-pointer">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-wallet2 fs-3 text-success me-3"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">Touch 'n Go eWallet</h6>
                                    <small class="text-muted">Pay using TNG eWallet (Auto-approved for testing)</small>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- Cash -->
                <div class="payment-method" data-aos="fade-up" data-aos-delay="200">
                    <input type="radio" name="payment_method" id="cash" value="cash">
                    <div class="d-flex align-items-center">
                        <label for="cash" class="flex-grow-1 cursor-pointer">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-cash-stack fs-3 text-warning me-3"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">Cash on Pickup</h6>
                                    <small class="text-muted">Pay when you collect your order</small>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- Payment Details Form (shown when online payment selected) -->
                <div id="payment-details" class="mt-4" data-aos="fade-up">
                    <div class="card border-0 bg-light">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-4">Enter Payment Details</h6>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Card Number</label>
                                <input type="text" 
                                       name="card_number" 
                                       class="form-control @error('card_number') is-invalid @enderror" 
                                       placeholder="4111 1111 1111 1111" 
                                       maxlength="19" 
                                       autocomplete="off"
                                       value="{{ old('card_number') }}">
                                <small class="text-muted">Test: 4111111111111111, 4242424242424242</small>
                                @error('card_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Expiry Date</label>
                                    <input type="text" 
                                           name="expiry_date" 
                                           class="form-control @error('expiry_date') is-invalid @enderror" 
                                           placeholder="12/25" 
                                           maxlength="5" 
                                           autocomplete="off"
                                           value="{{ old('expiry_date') }}">
                                    @error('expiry_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">CVV</label>
                                    <input type="text" 
                                           name="cvv" 
                                           class="form-control @error('cvv') is-invalid @enderror" 
                                           placeholder="123" 
                                           maxlength="3" 
                                           autocomplete="off"
                                           value="{{ old('cvv') }}">
                                    @error('cvv')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Cardholder Name</label>
                                <input type="text" 
                                       name="card_name" 
                                       class="form-control @error('card_name') is-invalid @enderror" 
                                       placeholder="John Doe" 
                                       autocomplete="off"
                                       value="{{ old('card_name', auth()->user()->name) }}">
                                @error('card_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Pickup Instructions -->
                <div class="mt-4" data-aos="fade-up">
                    <h6 class="fw-bold mb-3">Pickup Instructions (Optional)</h6>
                    <textarea name="pickup_instructions" class="form-control" rows="3" placeholder="Any special instructions for pickup?">{{ old('pickup_instructions') }}</textarea>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="cart-summary" data-aos="fade-left">
                    <h5 class="fw-bold mb-4">Order Summary</h5>
                    
                    <!-- Items -->
                    <div class="mb-3">
                        @foreach($cartItems as $item)
                        <div class="d-flex justify-content-between mb-2">
                            <div class="d-flex align-items-center">
                                @if($item->menuItem->image_path && file_exists(public_path($item->menuItem->image_path)))
                                    <img src="{{ asset($item->menuItem->image_path) }}" class="rounded me-2" width="40" alt="{{ $item->menuItem->name }}">
                                @else
                                    <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="bi bi-image text-muted"></i>
                                    </div>
                                @endif
                                <small>{{ $item->menuItem->name }} x{{ $item->quantity }}</small>
                            </div>
                            <small class="fw-bold">RM {{ number_format($item->menuItem->price * $item->quantity, 2) }}</small>
                        </div>
                        @endforeach
                    </div>
                    
                    <hr>
                    
                    <!-- Price Breakdown -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span class="fw-bold">RM {{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Service Fee</span>
                            <span class="fw-bold">RM {{ number_format($serviceFee, 2) }}</span>
                        </div>
                        @if($appliedVoucher)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-success fw-bold">
                                <i class="bi bi-gift me-1"></i> Voucher Discount
                            </span>
                            <span class="text-success fw-bold">- RM {{ number_format($discount, 2) }}</span>
                        </div>
                        <div class="alert alert-success border-0 py-2 px-3 mb-2">
                            <small>
                                <i class="bi bi-ticket-perforated me-1"></i>
                                <strong>{{ $appliedVoucher->reward->reward_name }}</strong> applied
                                <br>
                                <small class="text-muted">Code: {{ $appliedVoucher->voucher_code }}</small>
                            </small>
                        </div>
                        @endif
                    </div>
                    
                    <hr>
                    
                    <!-- Total -->
                    <div class="d-flex justify-content-between mb-4">
                        <h5 class="fw-bold mb-0">Total</h5>
                        <h5 class="fw-bold mb-0 text-primary">RM {{ number_format($total, 2) }}</h5>
                    </div>
                    
                    <!-- Loyalty Points -->
                    <div class="alert alert-info border-0 mb-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-star-fill text-warning fs-4 me-3"></i>
                            <div>
                                <small class="d-block mb-1">You'll earn</small>
                                <strong>{{ $pointsToEarn }} Loyalty Points</strong>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Terms -->
                    <div class="form-check mb-4">
                        <input class="form-check-input @error('agree_terms') is-invalid @enderror" type="checkbox" name="agree_terms" id="terms" value="1" {{ old('agree_terms') ? 'checked' : '' }}>
                        <label class="form-check-label small" for="terms">
                            I agree to the <a href="#" class="text-decoration-none">Terms & Conditions</a>
                        </label>
                        @error('agree_terms')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Place Order Button -->
                    <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill mb-3" id="place-order-btn">
                        <i class="bi bi-lock me-2"></i> Place Order
                    </button>
                    
                    <!-- Security Badge -->
                    <div class="text-center">
                        <small class="text-muted">
                            <i class="bi bi-shield-check text-success me-1"></i>
                            Your payment is secure & encrypted
                        </small>
                    </div>
                </div>
                
                <!-- Estimated Pickup Time -->
                <div class="card border-0 bg-light mt-4" data-aos="fade-left" data-aos-delay="100">
                    <div class="card-body p-4 text-center">
                        <i class="bi bi-clock-history fs-1 text-primary mb-3"></i>
                        <h6 class="fw-bold mb-2">Estimated Pickup Time</h6>
                        <h4 class="fw-bold text-primary mb-2">20-25 mins</h4>
                        <small class="text-muted">Your order will be ready for pickup</small>
                    </div>
                </div>
            </div>
        </div>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
    // Payment method selection
    document.querySelectorAll('.payment-method').forEach(method => {
        method.addEventListener('click', function() {
            document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
            this.classList.add('selected');
            this.querySelector('input[type="radio"]').checked = true;
            
            // Show/hide payment details form
            const paymentDetails = document.getElementById('payment-details');
            const paymentMethod = this.querySelector('input[type="radio"]').value;
            
            if (paymentMethod === 'online') {
                paymentDetails.style.display = 'block';
                // Make card fields required
                document.querySelectorAll('#payment-details input').forEach(input => {
                    input.setAttribute('required', 'required');
                });
            } else {
                paymentDetails.style.display = 'none';
                // Remove required from card fields
                document.querySelectorAll('#payment-details input').forEach(input => {
                    input.removeAttribute('required');
                });
            }
        });
    });
    
    // Format card number
    document.querySelector('input[name="card_number"]')?.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\s/g, '');
        let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
        e.target.value = formattedValue;
    });
    
    // Format expiry date
    document.querySelector('input[name="expiry_date"]')?.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 2) {
            e.target.value = value.slice(0, 2) + '/' + value.slice(2, 4);
        } else {
            e.target.value = value;
        }
    });
    
    // CVV - numbers only
    document.querySelector('input[name="cvv"]')?.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '');
    });
    
    // Form submission
    document.getElementById('checkout-form').addEventListener('submit', function(e) {
        const btn = document.getElementById('place-order-btn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';
    });
</script>
@endpush

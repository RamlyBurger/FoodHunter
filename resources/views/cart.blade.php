@extends('layouts.app')

@section('title', 'Shopping Cart - FoodHunter')

@section('content')
<!-- Page Header -->
<section class="bg-light py-4">
    <div class="container">
        <div class="row">
            <div class="col-12" data-aos="fade-up">
                <h1 class="display-5 fw-bold mb-2">Shopping Cart</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item active">Cart</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Cart Content -->
<section class="py-5">
    <div class="container">
        @if($cartItems->count() > 0)
        <div class="row g-4">
            <!-- Cart Items -->
            <div class="col-lg-8">
                <!-- Cart Header -->
                <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-right">
                    <h4 class="fw-bold mb-0">Your Items (<span id="cart-item-count">{{ $itemCount }}</span>)</h4>
                    <button class="btn btn-outline-danger btn-sm rounded-pill" id="clear-cart-btn">
                        <i class="bi bi-trash me-2"></i> Clear Cart
                    </button>
                </div>
                
                <div id="cart-items-container">
                    @foreach($cartItems as $index => $cartItem)
                    @if($cartItem->menuItem)
                    <!-- Cart Item -->
                    <div class="cart-item" data-cart-id="{{ $cartItem->cart_id }}" data-aos="fade-up" data-aos-delay="{{ $index * 100 }}">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-2">
                                @if($cartItem->menuItem->image_path && file_exists(public_path($cartItem->menuItem->image_path)))
                                    <img src="{{ asset($cartItem->menuItem->image_path) }}" 
                                         alt="{{ $cartItem->menuItem->name }}" 
                                         class="img-fluid rounded-3">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center rounded-3" style="height: 80px;">
                                        <i class="bi bi-image text-muted"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <h6 class="fw-bold mb-1">{{ $cartItem->menuItem->name }}</h6>
                                <p class="text-muted small mb-2">
                                    <i class="bi bi-shop me-1"></i> {{ $cartItem->menuItem->vendor->name }}
                                </p>
                                <p class="text-muted small mb-0">
                                    <i class="bi bi-tag me-1"></i> {{ $cartItem->menuItem->category->category_name }}
                                </p>
                            </div>
                            <div class="col-md-2">
                                <p class="fw-bold mb-0">RM {{ number_format($cartItem->menuItem->price, 2) }}</p>
                            </div>
                            <div class="col-md-3">
                                <div class="quantity-selector">
                                    <button type="button" class="btn-decrease" data-cart-id="{{ $cartItem->cart_id }}">
                                        <i class="bi bi-dash"></i>
                                    </button>
                                    <input type="number" value="{{ $cartItem->quantity }}" min="1" max="10" readonly id="quantity-{{ $cartItem->cart_id }}">
                                    <button type="button" class="btn-increase" data-cart-id="{{ $cartItem->cart_id }}">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-1 text-end">
                                <button class="btn btn-sm btn-outline-danger rounded-circle remove-item-btn" data-cart-id="{{ $cartItem->cart_id }}">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                        @if($cartItem->special_request)
                        <div class="row mt-2">
                            <div class="col-12">
                                <small class="text-muted">
                                    <i class="bi bi-chat-left-text me-1"></i>
                                    Special Request: {{ $cartItem->special_request }}
                                </small>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif
                    @endforeach
                </div>
                
                <!-- Continue Shopping -->
                <div class="mt-4" data-aos="fade-up">
                    <a href="{{ route('menu') }}" class="btn btn-outline-primary rounded-pill">
                        <i class="bi bi-arrow-left me-2"></i> Continue Shopping
                    </a>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="cart-summary" data-aos="fade-left">
                    <h5 class="fw-bold mb-4">Order Summary</h5>
                    
                    <!-- Voucher Code -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Have a voucher code?</label>
                        @if($appliedVoucher)
                            <div class="alert alert-success border-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-check-circle me-2"></i>
                                    <strong>{{ $appliedVoucher->reward->reward_name }}</strong>
                                    <br>
                                    <small>Code: {{ $appliedVoucher->voucher_code }}</small>
                                </div>
                                <button class="btn btn-sm btn-outline-danger" onclick="removeVoucher()">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        @else
                            <div class="input-group">
                                <input type="text" class="form-control text-uppercase" placeholder="Enter voucher code" id="voucher-code" maxlength="15">
                                <button class="btn btn-primary" type="button" onclick="applyVoucher()">Apply</button>
                            </div>
                            @if($voucherError)
                                <small class="text-danger mt-1 d-block">{{ $voucherError }}</small>
                            @endif
                            <small class="text-muted mt-1 d-block">
                                <i class="bi bi-info-circle me-1"></i>
                                Redeem vouchers from <a href="{{ route('loyalty') }}" class="text-decoration-none">Loyalty Rewards</a>
                            </small>
                        @endif
                    </div>
                    
                    <hr>
                    
                    <!-- Price Breakdown -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal (<span id="summary-item-count">{{ $itemCount }}</span> items)</span>
                            <span class="fw-bold">RM <span id="subtotal-amount">{{ number_format($subtotal, 2) }}</span></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Service Fee</span>
                            <span class="fw-bold">RM <span id="service-fee">{{ number_format($serviceFee, 2) }}</span></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-success fw-bold">
                                <i class="bi bi-gift me-1"></i> Voucher Discount
                            </span>
                            <span class="text-success fw-bold">- RM <span id="discount-amount">{{ number_format($discount, 2) }}</span></span>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Total -->
                    <div class="d-flex justify-content-between mb-4">
                        <h5 class="fw-bold mb-0">Total</h5>
                        <h5 class="fw-bold mb-0 text-primary">RM <span id="total-amount">{{ number_format($total, 2) }}</span></h5>
                    </div>
                    
                    <!-- Loyalty Points -->
                    <div class="alert alert-info border-0 mb-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-star-fill text-warning fs-4 me-3"></i>
                            <div>
                                <small class="d-block mb-1">You'll earn</small>
                                <strong><span id="loyalty-points">{{ floor($total) }}</span> Loyalty Points</strong>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Checkout Button -->
                    <a href="{{ url('/checkout') }}" class="btn btn-primary btn-lg w-100 rounded-pill mb-3">
                        <i class="bi bi-credit-card me-2"></i> Proceed to Checkout
                    </a>
                    
                    <!-- Payment Methods -->
                    <div class="text-center">
                        <small class="text-muted d-block mb-2">We accept</small>
                        <div class="d-flex justify-content-center gap-2">
                            <i class="bi bi-credit-card fs-4 text-muted"></i>
                            <i class="bi bi-wallet2 fs-4 text-muted"></i>
                            <i class="bi bi-phone fs-4 text-muted"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Safe Shopping -->
                <div class="card border-0 bg-light mt-4" data-aos="fade-left" data-aos-delay="100">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-shield-check text-success me-2"></i>
                            Safe & Secure Shopping
                        </h6>
                        <ul class="list-unstyled small text-muted mb-0">
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Secure payment gateway
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                100% money back guarantee
                            </li>
                            <li class="mb-0">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Fresh food quality assured
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        @else
        <!-- Empty Cart -->
        <div class="row">
            <div class="col-12">
                <div class="text-center py-5" data-aos="fade-up">
                    <i class="bi bi-cart-x display-1 text-muted mb-4"></i>
                    <h3 class="fw-bold mb-3">Your cart is empty</h3>
                    <p class="text-muted mb-4">Looks like you haven't added anything to your cart yet.</p>
                    <a href="{{ route('menu') }}" class="btn btn-primary btn-lg rounded-pill">
                        <i class="bi bi-arrow-left me-2"></i> Start Shopping
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</section>

<!-- Recommended Items -->
@if($recommendedItems->count() > 0)
<section class="py-5 bg-light">
    <div class="container">
        <h4 class="fw-bold mb-4" data-aos="fade-up">You Might Also Like</h4>
        
        <div class="row g-4">
            @foreach($recommendedItems as $index => $item)
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="{{ ($index + 1) * 100 }}">
                <div class="card food-card">
                    <div class="position-relative">
                        @if($item->image_path && file_exists(public_path($item->image_path)))
                            <img src="{{ asset($item->image_path) }}" class="card-img-top" alt="{{ $item->name }}">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="bi bi-image fs-1 text-muted"></i>
                            </div>
                        @endif
                    </div>
                    <div class="card-body">
                        <h6 class="fw-bold mb-2">{{ $item->name }}</h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="price-tag">RM {{ number_format($item->price, 2) }}</span>
                            <button class="btn btn-sm btn-primary rounded-pill quick-add-btn" data-item-id="{{ $item->item_id }}" data-item-name="{{ $item->name }}">
                                <i class="bi bi-plus-lg"></i> Add
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection

@push('scripts')
<script>
    // CSRF Token
    const csrfToken = '{{ csrf_token() }}';
    
    // Event listeners for quantity buttons
    document.addEventListener('DOMContentLoaded', function() {
        // Clear cart button
        const clearCartBtn = document.getElementById('clear-cart-btn');
        if (clearCartBtn) {
            clearCartBtn.addEventListener('click', clearCart);
        }
        
        // Decrease buttons
        document.querySelectorAll('.btn-decrease').forEach(button => {
            button.addEventListener('click', function() {
                const cartId = this.getAttribute('data-cart-id');
                const quantityInput = document.getElementById(`quantity-${cartId}`);
                const currentQuantity = parseInt(quantityInput.value);
                
                if (currentQuantity > 1) {
                    updateQuantity(cartId, currentQuantity - 1);
                }
            });
        });
        
        // Increase buttons
        document.querySelectorAll('.btn-increase').forEach(button => {
            button.addEventListener('click', function() {
                const cartId = this.getAttribute('data-cart-id');
                const quantityInput = document.getElementById(`quantity-${cartId}`);
                const currentQuantity = parseInt(quantityInput.value);
                
                if (currentQuantity < 10) {
                    updateQuantity(cartId, currentQuantity + 1);
                }
            });
        });
        
        // Remove buttons
        document.querySelectorAll('.remove-item-btn').forEach(button => {
            button.addEventListener('click', function() {
                const cartId = this.getAttribute('data-cart-id');
                removeItem(cartId);
            });
        });
        
        // Quick add buttons
        document.querySelectorAll('.quick-add-btn').forEach(button => {
            button.addEventListener('click', function() {
                const itemId = this.getAttribute('data-item-id');
                const itemName = this.getAttribute('data-item-name');
                quickAddToCart(itemId, itemName);
            });
        });
    });
    
    // Update quantity
    function updateQuantity(cartId, newQuantity) {
        if (newQuantity < 1 || newQuantity > 10) {
            return;
        }
        
        fetch(`/cart/${cartId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ quantity: newQuantity })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update quantity input
                document.getElementById(`quantity-${cartId}`).value = newQuantity;
                
                // Update totals
                document.getElementById('subtotal-amount').textContent = data.subtotal;
                document.getElementById('total-amount').textContent = data.total;
                document.getElementById('cart-item-count').textContent = data.item_count;
                document.getElementById('summary-item-count').textContent = data.item_count;
                document.getElementById('loyalty-points').textContent = Math.floor(parseFloat(data.total.replace(',', '')));
                
                showNotification('success', data.message);
            } else {
                showNotification('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Failed to update quantity');
        });
    }
    
    // Remove item
    function removeItem(cartId) {
        if (!confirm('Remove this item from cart?')) {
            return;
        }
        
        fetch(`/cart/${cartId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove item from DOM
                const itemElement = document.querySelector(`.cart-item[data-cart-id="${cartId}"]`);
                if (itemElement) {
                    itemElement.remove();
                }
                
                // Update totals
                document.getElementById('subtotal-amount').textContent = data.subtotal;
                document.getElementById('total-amount').textContent = data.total;
                document.getElementById('cart-item-count').textContent = data.item_count;
                document.getElementById('summary-item-count').textContent = data.item_count;
                document.getElementById('loyalty-points').textContent = Math.floor(parseFloat(data.total.replace(',', '')));
                
                showNotification('success', data.message);
                
                // Reload page if cart is empty
                if (data.cart_empty) {
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            } else {
                showNotification('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Failed to remove item');
        });
    }
    
    // Clear cart
    function clearCart() {
        if (!confirm('Are you sure you want to clear your entire cart?')) {
            return;
        }
        
        fetch('/cart/clear', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('success', data.message);
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showNotification('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Failed to clear cart');
        });
    }
    
    // Quick add to cart (for recommended items)
    function quickAddToCart(itemId, itemName) {
        fetch('/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                item_id: itemId,
                quantity: 1,
                special_request: null
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('success', `${itemName} added to cart!`);
                // Reload page to update cart
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showNotification('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Failed to add item');
        });
    }
    
    // Apply promo code (placeholder)
    function applyPromo() {
        const promoCode = document.getElementById('promo-code').value;
        if (!promoCode) {
            showNotification('error', 'Please enter a promo code');
            return;
        }
        showNotification('info', 'Promo code feature coming soon!');
    }
    
    // Apply voucher
    function applyVoucher() {
        const voucherCode = document.getElementById('voucher-code').value.trim().toUpperCase();
        if (!voucherCode) {
            showNotification('error', 'Please enter a voucher code');
            return;
        }
        
        fetch('/cart/apply-voucher', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ voucher_code: voucherCode })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('success', data.message);
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showNotification('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Failed to apply voucher');
        });
    }
    
    // Remove voucher
    function removeVoucher() {
        if (!confirm('Remove this voucher?')) {
            return;
        }
        
        fetch('/cart/remove-voucher', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('success', data.message);
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            } else {
                showNotification('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Failed to remove voucher');
        });
    }
    
    // Show notification
    function showNotification(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'error' ? 'alert-danger' : 'alert-info';
        
        const notification = document.createElement('div');
        notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
        notification.style.zIndex = '9999';
        notification.style.minWidth = '300px';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
</script>
@endpush

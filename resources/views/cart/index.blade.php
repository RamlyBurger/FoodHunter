@extends('layouts.app')

@section('title', 'My Cart')

@push('styles')
    <style>
        .voucher-applied {
            background: rgba(52, 199, 89, 0.1);
            border: 1px dashed var(--success-color);
            border-radius: 12px;
            padding: 12px 16px;
        }

        .voucher-reminder {
            background: linear-gradient(135deg, var(--warning-color), #FF6B00);
            border-radius: 12px;
            padding: 16px;
            color: white;
        }
    </style>
@endpush

@section('content')
    <div class="container" style="padding-top: 100px; padding-bottom: 50px;">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2 class="fw-bold mb-2">
                    <i class="bi bi-cart3 me-2" style="color: var(--primary-color);"></i>
                    My Cart
                </h2>
                <p class="text-muted">Review your items before checkout</p>
            </div>
            @if(count($cartItems) > 0)
                <div class="col-md-4 text-md-end">
                    <span class="badge bg-primary" style="font-size: 0.85rem; padding: 8px 14px;">{{ count($cartItems) }}
                        item(s)</span>
                </div>
            @endif
        </div>

        @if(count($cartItems) > 0)
            <div class="row g-4" id="cart-content-row">
                <div class="col-md-8" id="cart-items-column">
                    <div class="card">
                        <div class="card-body p-0">
                            @foreach($cartItems as $index => $item)
                                <div class="p-4 {{ $index > 0 ? 'border-top' : '' }}">
                                    <div class="d-flex gap-3">
                                        <a href="{{ url('/menu/' . $item->menuItem->id) }}" class="flex-shrink-0">
                                            <img src="{{ \App\Helpers\ImageHelper::menuItem($item->menuItem->image) }}"
                                                class="rounded" style="width: 80px; height: 80px; object-fit: cover;"
                                                alt="{{ $item->menuItem->name }}"
                                                onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($item->menuItem->name) }}&background=f3f4f6&color=9ca3af&size=200&font-size=0.25&bold=true';">
                                        </a>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <a href="{{ url('/menu/' . $item->menuItem->id) }}"
                                                        class="text-decoration-none text-dark">
                                                        <h6 class="mb-1" style="font-weight: 600;">{{ $item->menuItem->name }}</h6>
                                                    </a>
                                                    <small
                                                        class="text-muted">{{ $item->menuItem->vendor->store_name ?? '' }}</small>
                                                    @if($item->special_instructions)
                                                        <div class="mt-1">
                                                            <small class="text-muted"><i class="bi bi-chat-text"></i>
                                                                {{ $item->special_instructions }}</small>
                                                        </div>
                                                    @endif
                                                </div>
                                                <button type="button" class="btn btn-sm text-muted p-1"
                                                    onclick="removeCartItem({{ $item->id }}, this)" style="line-height: 1;">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mt-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <x-quantity-stepper :value="$item->quantity" :cartItemId="$item->id"
                                                        size="sm" />
                                                    <span class="text-muted">× RM
                                                        {{ number_format($item->menuItem->price, 2) }}</span>
                                                </div>
                                                <strong id="item-total-{{ $item->id }}" style="color: var(--primary-color);">RM
                                                    {{ number_format($item->menuItem->price * $item->quantity, 2) }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ url('/menu') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Continue Shopping
                        </a>
                        <button type="button" class="btn btn-outline-danger" id="clear-cart-btn" onclick="confirmClearCart()">
                            <i class="bi bi-trash me-1"></i> Clear Cart
                        </button>
                    </div>
                </div>

                <div class="col-md-4" id="cart-summary-column">
                    <div class="card" style="position: sticky; top: 100px;">
                        <div class="card-body p-4">
                            <h5 class="mb-4" style="font-weight: 700;">Order Summary</h5>

                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted">Subtotal (<span
                                        data-summary="item-count">{{ $summary['item_count'] }}</span> items)</span>
                                <span data-summary="subtotal">RM {{ number_format($summary['subtotal'], 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted">Service Fee</span>
                                <span>RM {{ number_format($summary['service_fee'], 2) }}</span>
                            </div>
                            @if($summary['discount'] > 0)
                                <div class="d-flex justify-content-between mb-3" style="color: var(--success-color);">
                                    <span><i class="bi bi-ticket-perforated me-1"></i>Voucher Discount</span>
                                    <span>-RM {{ number_format($summary['discount'], 2) }}</span>
                                </div>
                            @endif

                            <hr class="my-3">

                            <div class="d-flex justify-content-between mb-4">
                                <strong style="font-size: 1.1rem;">Total</strong>
                                <strong style="font-size: 1.25rem; color: var(--primary-color);" data-summary="total">RM
                                    {{ number_format($summary['total'], 2) }}</strong>
                            </div>

                            <!-- Voucher Section -->
                            @if(session('applied_voucher'))
                                @php $appliedVoucher = session('applied_voucher'); @endphp
                                <div class="voucher-applied mb-4" id="applied-voucher-section">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                                            <strong>{{ $appliedVoucher['code'] ?? 'Voucher Applied' }}</strong>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-link text-danger p-0"
                                            onclick="removeVoucher()">Remove</button>
                                    </div>
                                </div>
                            @else
                                <div id="voucher-form-container" class="mb-4">
                                    <label class="form-label small" style="font-weight: 600;">Have a voucher code?</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent"><i class="bi bi-ticket-perforated"></i></span>
                                        <input type="text" id="voucher-code-input" class="form-control"
                                            placeholder="Enter voucher code">
                                        <button type="button" id="apply-voucher-btn" class="btn btn-outline-primary"
                                            onclick="applyVoucher()">Apply</button>
                                    </div>
                                    <div id="voucher-error" class="text-danger small mt-2" style="display: none;"></div>
                                </div>
                            @endif

                            <a href="{{ url('/checkout') }}" class="btn btn-primary w-100 btn-lg mb-3">
                                <i class="bi bi-lock me-2"></i> Secure Checkout
                            </a>

                            <!-- Trust Badges -->
                            <div class="text-center">
                                <small class="text-muted d-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-shield-check text-success"></i> Safe & Secure Payment
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recommended Items - Popular Items from Student 2's API -->
            @php
                $cartItemIds = $cartItems->pluck('menu_item_id')->toArray();
                $wishlistIds = auth()->check() ? auth()->user()->wishlists()->pluck('menu_item_id')->toArray() : [];
            @endphp
            <section class="mt-5" id="popular-items-section" style="display: none;">
                <h5 class="mb-4" style="font-weight: 700;"><i class="bi bi-fire me-2"
                        style="color: var(--primary-color);"></i>You might also like</h5>
                <div class="row g-4" id="popular-items-container">
                    <!-- Popular items loaded via Student 2's API -->
                </div>
            </section>

            @push('scripts')
                <script>
                    // Cart item IDs to exclude from popular items
                    const cartItemIds = @json($cartItemIds);
                    const wishlistIds = @json($wishlistIds);

                    /**
                     * Load popular items from Student 2's Popular Items API
                     * Consumes: GET /api/menu/popular
                     * This replaces the random items with trending popular items
                     */
                    function loadPopularItems() {
                        const container = document.getElementById('popular-items-container');
                        const section = document.getElementById('popular-items-section');
                        
                        if (!container || !section) return;

                        fetch('/api/menu/popular?limit=8', {
                            headers: { 'Accept': 'application/json' }
                        })
                        .then(res => res.json())
                        .then(response => {
                            const data = response.data || response;
                            if (data.items && data.items.length > 0) {
                                // Filter out items already in cart
                                const filteredItems = data.items.filter(item => !cartItemIds.includes(item.id));
                                
                                if (filteredItems.length > 0) {
                                    // Take only first 4 items
                                    const displayItems = filteredItems.slice(0, 4);
                                    
                                    container.innerHTML = displayItems.map(item => {
                                        const price = parseFloat(item.price).toFixed(2);
                                        const vendorName = item.vendor?.store_name || 'Vendor';
                                        const totalSold = item.total_sold || 0;
                                        const isWishlisted = wishlistIds.includes(item.id);
                                        const fallbackImg = `https://ui-avatars.com/api/?name=${encodeURIComponent(item.name)}&background=f3f4f6&color=9ca3af&size=200&font-size=0.25&bold=true`;
                                        
                                        return `
                                        <div class="col-6 col-md-3">
                                            <div class="card h-100 menu-item-card">
                                                <div class="position-relative">
                                                    <a href="/menu/${item.id}">
                                                        <img src="${item.image}" 
                                                            class="card-img-top" 
                                                            alt="${item.name}"
                                                            style="height: 160px; object-fit: cover;"
                                                            onerror="this.onerror=null; this.src='${fallbackImg}';">
                                                    </a>
                                                    <span class="badge bg-danger position-absolute" style="top: 8px; left: 8px;">
                                                        <i class="bi bi-fire me-1"></i>${totalSold} sold
                                                    </span>
                                                </div>
                                                <div class="card-body p-3">
                                                    <a href="/menu/${item.id}" class="text-decoration-none text-dark">
                                                        <h6 class="card-title mb-1" style="font-weight: 600; font-size: 0.95rem;">${item.name}</h6>
                                                    </a>
                                                    <small class="text-muted d-block mb-2">${vendorName}</small>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="fw-bold" style="color: var(--primary-color);">RM ${price}</span>
                                                        <button type="button" class="btn btn-sm btn-primary" onclick="addToCart(${item.id}, 1, this)">
                                                            <i class="bi bi-cart-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>`;
                                    }).join('');
                                    
                                    section.style.display = 'block';
                                }
                            }
                        })
                        .catch(err => console.error('Failed to load popular items:', err));
                    }

                    // Load popular items on page load
                    document.addEventListener('DOMContentLoaded', loadPopularItems);

                    // Override stepper change to update item totals
                    const originalHandleStepperChange = window.handleStepperChange;
                    window.handleStepperChange = function (wrapper, newQty) {
                        const cartItemId = wrapper.dataset.cartItemId;
                        if (cartItemId) {
                            // Update item total in UI
                            fetch('/cart/' + cartItemId, {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ quantity: newQty })
                            })
                                .then(res => res.json())
                                .then(response => {
                                    if (response.success) {
                                        // Handle standardized API response format
                                        const responseData = response.data || response;
                                        const itemTotal = responseData.item_total;
                                        const summary = responseData.summary;
                                        const cartCount = responseData.cart_count ?? summary?.item_count ?? 0;
                                        
                                        document.getElementById('item-total-' + cartItemId).textContent = 'RM ' + parseFloat(itemTotal).toFixed(2);
                                        updateCartSummary(summary);
                                        
                                        // Update cart badge
                                        const cartBadges = document.querySelectorAll('.cart-count, #cart-count');
                                        cartBadges.forEach(badge => {
                                            badge.textContent = cartCount;
                                            badge.style.display = cartCount > 0 ? 'flex' : 'none';
                                        });
                                        
                                        if (typeof pulseCartBadge === 'function') pulseCartBadge();
                                        if (typeof loadCartDropdown === 'function') loadCartDropdown();
                                    }
                                });
                        }
                    };

                    function removeCartItem(itemId, btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                        fetch('/cart/' + itemId, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            }
                        })
                            .then(res => res.json())
                            .then(response => {
                                if (response.success) {
                                    // Handle standardized API response format
                                    const responseData = response.data || response;
                                    const summary = responseData.summary;
                                    const cartCount = responseData.cart_count ?? summary?.item_count ?? 0;
                                    
                                    // Remove the item row from DOM
                                    const row = btn.closest('.p-4');
                                    row.remove();
                                    updateCartSummary(summary);
                                    
                                    // Update cart badge
                                    const cartBadges = document.querySelectorAll('.cart-count, #cart-count');
                                    cartBadges.forEach(badge => {
                                        badge.textContent = cartCount;
                                        if (cartCount === 0) badge.style.display = 'none';
                                    });
                                    
                                    if (typeof pulseCartBadge === 'function') pulseCartBadge();
                                    if (typeof loadCartDropdown === 'function') loadCartDropdown();
                                    showToast(response.message || 'Item removed', 'success');

                                    if (summary.item_count === 0) {
                                        // Show empty cart state using specific ID
                                        const cartContentRow = document.getElementById('cart-content-row');
                                        if (cartContentRow) {
                                            cartContentRow.innerHTML = `
                                                <div class="col-12">
                                                    <div class="card">
                                                        <div class="card-body text-center py-5">
                                                            <i class="bi bi-cart-x fs-1 text-muted mb-3 d-block"></i>
                                                            <h5 class="text-muted mb-3">Your cart is empty</h5>
                                                            <a href="{{ url('/menu') }}" class="btn btn-primary">
                                                                <i class="bi bi-arrow-left me-1"></i> Browse Menu
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            `;
                                        }
                                    }
                                }
                            })
                            .catch(() => {
                                btn.disabled = false;
                                btn.innerHTML = '<i class="bi bi-x-lg"></i>';
                            });
                    }

                    function updateCartSummary(summary) {
                        const itemCountEl = document.querySelector('[data-summary="item-count"]');
                        const subtotalEl = document.querySelector('[data-summary="subtotal"]');
                        const totalEl = document.querySelector('[data-summary="total"]');

                        if (itemCountEl) itemCountEl.textContent = summary.item_count;
                        if (subtotalEl) subtotalEl.textContent = 'RM ' + summary.subtotal.toFixed(2);
                        if (totalEl) totalEl.textContent = 'RM ' + summary.total.toFixed(2);
                    }

                    function confirmClearCart() {
                        Swal.fire({
                            title: 'Clear Cart?',
                            text: 'This will remove all items from your cart.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#FF3B30',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Yes, clear it',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                clearCart();
                            }
                        });
                    }

                    function clearCart() {
                        const btn = document.getElementById('clear-cart-btn');
                        const originalContent = btn.innerHTML;
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Clearing...';

                        fetch('/cart/clear', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            }
                        })
                            .then(res => res.json())
                            .then(response => {
                                if (response.success) {
                                    // Handle standardized API response format
                                    const data = response.data || response;
                                    
                                    // Hide the entire cart content row and show empty state
                                    const cartContentRow = document.getElementById('cart-content-row');
                                    if (cartContentRow) {
                                        cartContentRow.innerHTML = `
                                            <div class="col-12">
                                                <div class="card">
                                                    <div class="card-body text-center py-5">
                                                        <i class="bi bi-cart-x fs-1 text-muted mb-3 d-block"></i>
                                                        <h5 class="text-muted mb-3">Your cart is empty</h5>
                                                        <a href="{{ url('/menu') }}" class="btn btn-primary">
                                                            <i class="bi bi-arrow-left me-1"></i> Browse Menu
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        `;
                                    }
                                    
                                    // Update cart badge to 0
                                    const cartBadges = document.querySelectorAll('.cart-count, #cart-count');
                                    cartBadges.forEach(badge => {
                                        badge.textContent = '0';
                                        badge.style.display = 'none';
                                    });

                                    if (typeof pulseCartBadge === 'function') pulseCartBadge();
                                    if (typeof loadCartDropdown === 'function') loadCartDropdown();
                                    showToast(response.message || 'Cart cleared', 'success');
                                } else {
                                    btn.disabled = false;
                                    btn.innerHTML = originalContent;
                                    showToast(response.message || 'Failed to clear cart', 'error');
                                }
                            })
                            .catch(err => {
                                btn.disabled = false;
                                btn.innerHTML = originalContent;
                                showToast('An error occurred', 'error');
                            });
                    }

                    function applyVoucher() {
                        const input = document.getElementById('voucher-code-input');
                        const btn = document.getElementById('apply-voucher-btn');
                        const errorDiv = document.getElementById('voucher-error');
                        const code = input.value.trim();

                        if (!code) {
                            errorDiv.textContent = 'Please enter a voucher code.';
                            errorDiv.style.display = 'block';
                            return;
                        }

                        // Disable button and show loading
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
                        errorDiv.style.display = 'none';

                        // First validate voucher using Student 5's API
                        const subtotal = parseFloat(
                            document.querySelector('[data-summary="total"]')
                                ?.textContent
                                ?.replace('RM', '')
                                .trim() || 0
                        );

                        fetch('/api/vouchers/validate', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ code: code, subtotal: subtotal })
                        })
                            .then(res => res.json())
                            .then(validateData => {
                                if (!validateData.success) {
                                    errorDiv.textContent = validateData.message || 'Invalid voucher code.';
                                    errorDiv.style.display = 'block';
                                    btn.disabled = false;
                                    btn.innerHTML = 'Apply';
                                    return Promise.reject('validation_failed');
                                }
                                // Voucher validated via Student 5 API, now apply it
                                return fetch('/vouchers/apply', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken,
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({ voucher_code: code })
                                });
                            })
                            .then(res => res ? res.json() : null)
                            .then(data => {
                                if (!data) return;
                                if (data.success) {
                                    // Handle standardized API response format
                                    const responseData = data.data || data;
                                    const voucher = responseData.voucher || {};

                                    // Replace voucher form with applied voucher display
                                    const formContainer = document.getElementById('voucher-form-container');
                                    if (formContainer) {
                                        formContainer.outerHTML = `
                                    <div class="voucher-applied mb-4" id="applied-voucher-section">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                <strong>${voucher.name || code}</strong>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeVoucher()">Remove</button>
                                        </div>
                                    </div>
                                `;
                                    }

                                    // Recalculate cart totals - need to reload to get server-side calculation
                                    // For now, show success and update UI elements we can
                                    showToast(voucher.description || data.message || 'Voucher applied!', 'success');
                                    btn.disabled = false;
                                    btn.textContent = 'Apply';

                                    // Reload cart dropdown to reflect changes
                                    loadCartDropdown();
                                } else {
                                    errorDiv.textContent = data.message || 'Failed to apply voucher.';
                                    errorDiv.style.display = 'block';
                                    btn.disabled = false;
                                    btn.textContent = 'Apply';
                                }
                            })
                            .catch(err => {
                                errorDiv.textContent = 'An error occurred. Please try again.';
                                errorDiv.style.display = 'block';
                                btn.disabled = false;
                                btn.textContent = 'Apply';
                            });
                    }

                    // Allow Enter key to apply voucher
                    document.getElementById('voucher-code-input')?.addEventListener('keypress', function (e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            applyVoucher();
                        }
                    });

                    function removeVoucher() {
                        fetch('/vouchers/remove', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            }
                        })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    // Replace applied voucher display with form
                                    const appliedSection = document.getElementById('applied-voucher-section');
                                    if (appliedSection) {
                                        appliedSection.outerHTML = `
                                    <div id="voucher-form-container" class="mb-4">
                                        <label class="form-label small" style="font-weight: 600;">Have a voucher code?</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent"><i class="bi bi-ticket-perforated"></i></span>
                                            <input type="text" id="voucher-code-input" class="form-control" placeholder="Enter voucher code">
                                            <button type="button" id="apply-voucher-btn" class="btn btn-primary" onclick="applyVoucher()">Apply</button>
                                        </div>
                                        <div id="voucher-error" class="text-danger small mt-1" style="display: none;"></div>
                                    </div>
                                `;
                                        // Re-attach Enter key listener
                                        document.getElementById('voucher-code-input')?.addEventListener('keypress', function (e) {
                                            if (e.key === 'Enter') {
                                                e.preventDefault();
                                                applyVoucher();
                                            }
                                        });
                                    }

                                    // Remove discount line if exists
                                    const discountLine = document.querySelector('[style*="--success-color"]');
                                    if (discountLine && discountLine.closest('.d-flex')) {
                                        discountLine.closest('.d-flex').remove();
                                    }

                                    showToast('Voucher removed', 'success');
                                    loadCartDropdown();
                                } else {
                                    showToast(data.message || 'Failed to remove voucher', 'error');
                                }
                            })
                            .catch(err => {
                                showToast('An error occurred', 'error');
                            });
                    }
                </script>
            @endpush
        @else
            <div class="text-center py-5">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4"
                    style="width: 120px; height: 120px; background: var(--gray-100);">
                    <i class="bi bi-cart-x" style="font-size: 48px; color: var(--gray-400);"></i>
                </div>
                <h4 style="font-weight: 700;">Your cart is empty</h4>
                <p class="text-muted mb-4">Browse our menu and add some delicious items!</p>
                <a href="{{ url('/menu') }}" class="btn btn-primary btn-lg">
                    <i class="bi bi-search me-1"></i> Browse Menu
                </a>
            </div>
        @endif
    </div>
    </div>
@endsection
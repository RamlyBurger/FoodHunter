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
            <span class="badge bg-primary" style="font-size: 0.85rem; padding: 8px 14px;">{{ count($cartItems) }} item(s)</span>
        </div>
        @endif
    </div>

    @if(count($cartItems) > 0)
    <div class="row g-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body p-0">
                    @foreach($cartItems as $index => $item)
                    <div class="p-4 {{ $index > 0 ? 'border-top' : '' }}">
                        <div class="d-flex gap-3">
                            <a href="{{ url('/menu/' . $item->menuItem->id) }}" class="flex-shrink-0">
                                <img src="{{ \App\Helpers\ImageHelper::menuItem($item->menuItem->image) }}" 
                                     class="rounded" 
                                     style="width: 80px; height: 80px; object-fit: cover;" 
                                     alt="{{ $item->menuItem->name }}"
                                     onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($item->menuItem->name) }}&background=f3f4f6&color=9ca3af&size=200&font-size=0.25&bold=true';">
                            </a>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <a href="{{ url('/menu/' . $item->menuItem->id) }}" class="text-decoration-none text-dark">
                                            <h6 class="mb-1" style="font-weight: 600;">{{ $item->menuItem->name }}</h6>
                                        </a>
                                        <small class="text-muted">{{ $item->menuItem->vendor->store_name ?? '' }}</small>
                                        @if($item->special_instructions)
                                        <div class="mt-1">
                                            <small class="text-muted"><i class="bi bi-chat-text"></i> {{ $item->special_instructions }}</small>
                                        </div>
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-sm text-muted p-1" onclick="removeCartItem({{ $item->id }}, this)" style="line-height: 1;">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <x-quantity-stepper :value="$item->quantity" :cartItemId="$item->id" size="sm" />
                                        <span class="text-muted">× RM {{ number_format($item->menuItem->price, 2) }}</span>
                                    </div>
                                    <strong id="item-total-{{ $item->id }}" style="color: var(--primary-color);">RM {{ number_format($item->menuItem->price * $item->quantity, 2) }}</strong>
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
                <form action="{{ url('/cart/clear') }}" method="POST">
                    @csrf
                    <button type="button" class="btn btn-outline-danger" onclick="confirmClearCart(this.form)">
                        <i class="bi bi-trash me-1"></i> Clear Cart
                    </button>
                </form>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card" style="position: sticky; top: 100px;">
                <div class="card-body p-4">
                    <h5 class="mb-4" style="font-weight: 700;">Order Summary</h5>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Subtotal (<span data-summary="item-count">{{ $summary['item_count'] }}</span> items)</span>
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
                        <strong style="font-size: 1.25rem; color: var(--primary-color);" data-summary="total">RM {{ number_format($summary['total'], 2) }}</strong>
                    </div>

                    <!-- Voucher Section -->
                    @if(session('applied_voucher'))
                    @php $appliedVoucher = session('applied_voucher'); @endphp
                    <div class="voucher-applied mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <strong>{{ $appliedVoucher['code'] ?? 'Voucher Applied' }}</strong>
                            </div>
                            <form action="{{ url('/vouchers/remove') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-link text-danger p-0">Remove</button>
                            </form>
                        </div>
                    </div>
                    @else
                    <form action="{{ url('/vouchers/apply') }}" method="POST" class="mb-4">
                        @csrf
                        <label class="form-label small" style="font-weight: 600;">Have a voucher code?</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent"><i class="bi bi-ticket-perforated"></i></span>
                            <input type="text" name="voucher_code" class="form-control" placeholder="Enter voucher code">
                            <button type="submit" class="btn btn-outline-primary">Apply</button>
                        </div>
                    </form>
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
    
    <!-- Recommended Items -->
    @php
        $recommendedItems = \App\Models\MenuItem::where('is_available', true)
            ->whereNotIn('id', $cartItems->pluck('menu_item_id'))
            ->inRandomOrder()
            ->take(4)
            ->get();
        $wishlistIds = auth()->check() ? auth()->user()->wishlists()->pluck('menu_item_id')->toArray() : [];
    @endphp
    @if($recommendedItems->count() > 0)
    <section class="mt-5">
        <h5 class="mb-4" style="font-weight: 700;"><i class="bi bi-stars me-2" style="color: var(--warning-color);"></i>You might also like</h5>
        <div class="row g-4">
            @foreach($recommendedItems as $recItem)
                <x-menu-item-card :item="$recItem" :wishlistIds="$wishlistIds" />
            @endforeach
        </div>
    </section>
    @endif

    @push('scripts')
    <script>
    // Override stepper change to update item totals
    const originalHandleStepperChange = window.handleStepperChange;
    window.handleStepperChange = function(wrapper, newQty) {
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
            .then(data => {
                if (data.success) {
                    document.getElementById('item-total-' + cartItemId).textContent = 'RM ' + data.item_total.toFixed(2);
                    updateCartSummary(data.summary);
                    pulseCartBadge();
                    loadCartDropdown();
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
        .then(data => {
            if (data.success) {
                const row = btn.closest('.p-4');
                row.remove();
                updateCartSummary(data.summary);
                pulseCartBadge();
                loadCartDropdown();
                
                if (data.summary.item_count === 0) {
                    location.reload();
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
    
    function confirmClearCart(form) {
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
                form.submit();
            }
        });
    }
    </script>
    @endpush
    @else
    <div class="text-center py-5">
        <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 120px; height: 120px; background: var(--gray-100);">
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

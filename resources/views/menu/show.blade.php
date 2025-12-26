@extends('layouts.app')

@section('title', $item->name)

@push('styles')
<style>
    .product-gallery {
        position: relative;
    }
    .product-main-image {
        width: 100%;
        height: 480px;
        object-fit: cover;
        border-radius: 12px;
        background: #f5f5f5;
    }
    .product-badges {
        position: absolute;
        top: 12px;
        left: 12px;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .product-badges .badge {
        padding: 6px 12px;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 6px;
    }
    .wishlist-btn {
        position: absolute;
        top: 12px;
        right: 12px;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #fff;
        border: 1px solid #e5e5e5;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    .wishlist-btn:hover {
        border-color: #FF3B30;
    }
    .wishlist-btn i {
        font-size: 1.1rem;
        color: #FF3B30;
    }
    .product-info {
        padding-left: 24px;
    }
    .product-category {
        font-size: 0.85rem;
        color: #666;
        margin-bottom: 8px;
    }
    .product-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 12px;
        line-height: 1.3;
    }
    .product-price {
        display: flex;
        align-items: baseline;
        gap: 12px;
        margin-bottom: 16px;
    }
    .price-current {
        font-size: 1.5rem;
        font-weight: 700;
        color: #FF9500;
    }
    .price-original {
        font-size: 1rem;
        color: #999;
        text-decoration: line-through;
    }
    .product-rating {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
    }
    .product-rating .stars {
        color: #FFB800;
        font-size: 0.9rem;
    }
    .product-rating .rating-text {
        font-size: 0.9rem;
        color: #666;
    }
    .product-description {
        font-size: 0.95rem;
        line-height: 1.7;
        color: #555;
        margin-bottom: 24px;
    }
    .product-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
        padding-bottom: 24px;
        border-bottom: 1px solid #eee;
    }
    .meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.875rem;
        color: #666;
    }
    .meta-item i {
        color: #999;
    }
    .quantity-section {
        margin-bottom: 20px;
    }
    .quantity-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 10px;
    }
    .quantity-stepper {
        display: inline-flex;
        align-items: center;
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
    }
    .quantity-stepper .stepper-btn {
        width: 44px;
        height: 44px;
        border: none;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.15s;
        color: #333;
        font-size: 1rem;
    }
    .quantity-stepper .stepper-btn:hover {
        background: #f5f5f5;
    }
    .quantity-stepper .stepper-btn:active {
        background: #eee;
    }
    .quantity-stepper input {
        width: 60px;
        height: 44px;
        border: none;
        border-left: 1px solid #ddd;
        border-right: 1px solid #ddd;
        text-align: center;
        font-size: 1rem;
        font-weight: 600;
        color: #333;
        -moz-appearance: textfield;
    }
    .quantity-stepper input:focus {
        outline: none;
        background: #fafafa;
    }
    .quantity-stepper input::-webkit-outer-spin-button,
    .quantity-stepper input::-webkit-inner-spin-button {
        -webkit-appearance: none;
    }
    .add-to-cart-btn {
        width: 100%;
        height: 52px;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        background: #FF9500;
        border: none;
        color: #fff;
        transition: background 0.2s;
        margin-bottom: 16px;
    }
    .add-to-cart-btn:hover {
        background: #E68600;
    }
    .product-guarantees {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        padding: 16px;
        background: #f9f9f9;
        border-radius: 8px;
        margin-bottom: 24px;
    }
    .guarantee-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.8rem;
        color: #666;
    }
    .guarantee-item i {
        color: #34C759;
    }
    .vendor-section {
        padding: 20px;
        background: #fff;
        border: 1px solid #eee;
        border-radius: 12px;
    }
    .vendor-header {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .vendor-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        object-fit: cover;
        background: #f5f5f5;
    }
    .vendor-avatar-placeholder {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #FF9500;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
    }
    .vendor-info h6 {
        font-size: 0.95rem;
        font-weight: 600;
        margin-bottom: 4px;
    }
    .vendor-meta {
        display: flex;
        gap: 12px;
        font-size: 0.8rem;
        color: #666;
    }
    .vendor-status {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .vendor-status.open { color: #34C759; }
    .vendor-status.closed { color: #8E8E93; }
    .view-store-btn {
        margin-left: auto;
        font-size: 0.85rem;
        padding: 8px 16px;
        border-radius: 6px;
    }
    @media (max-width: 991px) {
        .product-info {
            padding-left: 0;
            padding-top: 24px;
        }
        .product-main-image {
            height: 300px;
        }
        .product-title {
            font-size: 1.4rem;
        }
    }
</style>
@endpush

@section('content')
<div class="page-content">
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb" style="font-size: 0.85rem;">
            <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-decoration-none text-muted">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ url('/menu') }}" class="text-decoration-none text-muted">Menu</a></li>
            <li class="breadcrumb-item active text-dark">{{ $item->name }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Product Image -->
        <div class="col-lg-6">
            <div class="product-gallery">
                <img src="{{ \App\Helpers\ImageHelper::menuItem($item->image) }}" 
                     alt="{{ $item->name }}" 
                     class="product-main-image"
                     onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($item->name) }}&background=f3f4f6&color=9ca3af&size=600&font-size=0.2&bold=true';">
                
                <div class="product-badges">
                    @if($item->is_featured)
                    <span class="badge bg-warning text-dark">
                        <i class="bi bi-star-fill me-1"></i> Featured
                    </span>
                    @endif
                    @if($item->original_price && $item->original_price > $item->price)
                    <span class="badge bg-danger">
                        -{{ $item->getDiscountPercentage() }}%
                    </span>
                    @endif
                </div>

                @auth
                <button type="button" class="wishlist-btn" id="wishlist-btn" onclick="toggleWishlistDetail({{ $item->id }}, this)">
                    <i class="bi bi-heart{{ in_array($item->id, $wishlistIds ?? []) ? '-fill' : '' }}" id="wishlist-icon"></i>
                </button>
                @endauth
            </div>
        </div>

        <!-- Product Info -->
        <div class="col-lg-6">
            <div class="product-info">
                @if($item->category)
                <p class="product-category">{{ $item->category->name }}</p>
                @endif

                <h1 class="product-title">{{ $item->name }}</h1>

                <!-- Price -->
                <div class="product-price">
                    <span class="price-current">RM {{ number_format($item->price, 2) }}</span>
                    @if($item->original_price && $item->original_price > $item->price)
                    <span class="price-original">RM {{ number_format($item->original_price, 2) }}</span>
                    @endif
                </div>

                <!-- Description -->
                <p class="product-description">{{ $item->description ?? 'Delicious food item prepared with fresh ingredients.' }}</p>

                <!-- Meta Info -->
                <div class="product-meta">
                    @if($item->prep_time)
                    <div class="meta-item">
                        <i class="bi bi-clock"></i>
                        <span>{{ $item->prep_time }} mins prep</span>
                    </div>
                    @endif
                    @if($item->calories)
                    <div class="meta-item">
                        <i class="bi bi-fire"></i>
                        <span>{{ $item->calories }} calories</span>
                    </div>
                    @endif
                    @if($item->total_sold > 0)
                    <div class="meta-item">
                        <i class="bi bi-bag-check"></i>
                        <span>{{ $item->total_sold }} sold</span>
                    </div>
                    @endif
                </div>

                <!-- Quantity & Add to Cart -->
                @if($item->is_available)
                    @auth
                    <div class="quantity-section">
                        <p class="quantity-label">Quantity</p>
                        <div class="quantity-stepper">
                            <button type="button" class="stepper-btn" id="qty-minus">
                                <i class="bi bi-dash"></i>
                            </button>
                            <input type="number" id="item-quantity" value="1" min="1" max="99">
                            <button type="button" class="stepper-btn" id="qty-plus">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </div>

                    <button type="button" class="btn add-to-cart-btn" id="add-to-cart-btn" onclick="addToCartWithQty({{ $item->id }})">
                        <i class="bi bi-cart-plus me-2"></i> Add to Cart
                    </button>
                    @else
                    <a href="{{ url('/login') }}" class="btn add-to-cart-btn">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Login to Order
                    </a>
                    @endauth
                @else
                <div class="alert alert-warning d-flex align-items-center" style="border-radius: 8px;">
                    <i class="bi bi-exclamation-triangle me-2"></i> This item is currently unavailable.
                </div>
                @endif

                <!-- Guarantees -->
                <div class="product-guarantees">
                    <div class="guarantee-item">
                        <i class="bi bi-shield-check"></i>
                        <span>Fresh ingredients</span>
                    </div>
                    <div class="guarantee-item">
                        <i class="bi bi-clock-history"></i>
                        <span>Fast preparation</span>
                    </div>
                    <div class="guarantee-item">
                        <i class="bi bi-patch-check"></i>
                        <span>Quality assured</span>
                    </div>
                </div>

                <!-- Vendor Section -->
                <div class="vendor-section">
                    <div class="vendor-header">
                        @if($item->vendor->logo)
                        <img src="{{ $item->vendor->logo }}" alt="{{ $item->vendor->store_name }}" class="vendor-avatar">
                        @else
                        <div class="vendor-avatar-placeholder">
                            <i class="bi bi-shop"></i>
                        </div>
                        @endif
                        <div class="vendor-info">
                            <h6>
                                <a href="{{ url('/vendors/' . $item->vendor->id) }}" class="text-decoration-none text-dark">
                                    {{ $item->vendor->store_name }}
                                </a>
                            </h6>
                            <div class="vendor-meta">
                                <span class="vendor-status {{ $item->vendor->is_open ? 'open' : 'closed' }}">
                                    <i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i>
                                    {{ $item->vendor->is_open ? 'Open' : 'Closed' }}
                                </span>
                                @if($item->vendor->rating)
                                <span><i class="bi bi-star-fill text-warning"></i> {{ number_format($item->vendor->rating, 1) }}</span>
                                @endif
                            </div>
                        </div>
                        <a href="{{ url('/vendors/' . $item->vendor->id) }}" class="btn btn-outline-secondary view-store-btn">
                            View Store
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Tick sound for stepper
const tickSound = new Audio('{{ asset("tick.mp3") }}');
tickSound.volume = 0.3;

function playTick() {
    tickSound.currentTime = 0;
    tickSound.play().catch(() => {});
}

// Stepper functionality with hold-to-repeat and manual edit
document.addEventListener('DOMContentLoaded', function() {
    const qtyInput = document.getElementById('item-quantity');
    const minusBtn = document.getElementById('qty-minus');
    const plusBtn = document.getElementById('qty-plus');
    
    if (!qtyInput || !minusBtn || !plusBtn) return;
    
    const minVal = parseInt(qtyInput.min) || 1;
    const maxVal = parseInt(qtyInput.max) || 99;
    
    let holdInterval = null;
    let holdTimeout = null;
    let currentSpeed = 100;
    
    function clampValue(val) {
        if (isNaN(val) || val < minVal) return minVal;
        if (val > maxVal) return maxVal;
        return val;
    }
    
    function updateQty(delta) {
        let val = parseInt(qtyInput.value) || 1;
        val += delta;
        val = clampValue(val);
        qtyInput.value = val;
        playTick();
    }
    
    function startHold(delta) {
        updateQty(delta);
        currentSpeed = 100;
        
        holdTimeout = setTimeout(() => {
            holdInterval = setInterval(() => {
                updateQty(delta);
                if (currentSpeed > 50) {
                    currentSpeed -= 20;
                    clearInterval(holdInterval);
                    holdInterval = setInterval(() => updateQty(delta), currentSpeed);
                }
            }, currentSpeed);
        }, 400);
    }
    
    function stopHold() {
        if (holdTimeout) clearTimeout(holdTimeout);
        if (holdInterval) clearInterval(holdInterval);
        holdTimeout = null;
        holdInterval = null;
    }
    
    // Mouse events for buttons
    minusBtn.addEventListener('mousedown', (e) => { e.preventDefault(); startHold(-1); });
    plusBtn.addEventListener('mousedown', (e) => { e.preventDefault(); startHold(1); });
    document.addEventListener('mouseup', stopHold);
    document.addEventListener('mouseleave', stopHold);
    
    // Touch events for buttons
    minusBtn.addEventListener('touchstart', (e) => { e.preventDefault(); startHold(-1); });
    plusBtn.addEventListener('touchstart', (e) => { e.preventDefault(); startHold(1); });
    document.addEventListener('touchend', stopHold);
    document.addEventListener('touchcancel', stopHold);
    
    // Manual input handling
    qtyInput.addEventListener('focus', function() {
        this.select();
    });
    
    qtyInput.addEventListener('blur', function() {
        let val = parseInt(this.value) || minVal;
        this.value = clampValue(val);
    });
    
    qtyInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            let val = parseInt(this.value) || minVal;
            this.value = clampValue(val);
            this.blur();
        }
    });
    
    qtyInput.addEventListener('input', function() {
        // Allow typing but remove non-numeric
        this.value = this.value.replace(/[^0-9]/g, '');
    });
});

function addToCartWithQty(itemId) {
    const qty = document.getElementById('item-quantity').value;
    const btn = document.getElementById('add-to-cart-btn');
    addToCart(itemId, parseInt(qty), btn);
}

function toggleWishlistDetail(itemId, btn) {
    fetch('{{ route("wishlist.toggle") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ menu_item_id: itemId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const icon = btn.querySelector('i');
            if (data.in_wishlist) {
                icon.className = 'bi bi-heart-fill';
                showToast('Added to wishlist', 'success');
            } else {
                icon.className = 'bi bi-heart';
                showToast('Removed from wishlist', 'success');
            }
            loadWishlistDropdown();
        }
    })
    .catch(err => console.error('Error:', err));
}
</script>
@endpush
</div>
@endsection

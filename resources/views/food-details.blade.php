@extends('layouts.app')

@section('title', $menuItem->name . ' - FoodHunter')

@section('content')
<div data-guest="{{ auth()->guest() ? 'true' : 'false' }}" id="page-data"></div>
<!-- Product Details -->
<section class="py-5">
    <div class="container">
        <nav aria-label="breadcrumb" data-aos="fade-right">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('menu') }}" class="text-decoration-none">Menu</a></li>
                <li class="breadcrumb-item active">{{ $menuItem->name }}</li>
            </ol>
        </nav>
        
        <div class="row g-5 mt-2">
            <!-- Image Gallery -->
            <div class="col-lg-6" data-aos="fade-right">
                <div class="position-sticky" style="top: 100px;">
                    <!-- Main Image -->
                    <div class="gallery-main mb-3">
                        @if($menuItem->image_path && file_exists(public_path($menuItem->image_path)))
                            <img src="{{ asset($menuItem->image_path) }}" 
                                 alt="{{ $menuItem->name }}" 
                                 class="img-fluid w-100 rounded-4" 
                                 id="main-image">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center rounded-4" style="height: 500px;">
                                <i class="bi bi-image display-1 text-muted"></i>
                            </div>
                        @endif
                        @if($menuItem->created_at->diffInDays(now()) < 7)
                        <span class="badge badge-new position-absolute" style="top: 1rem; right: 1rem;">New</span>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Product Info -->
            <div class="col-lg-6" data-aos="fade-left">
                <div class="mb-3">
                    <a href="{{ route('vendor.store', $menuItem->vendor_id) }}" class="text-decoration-none">
                        <span class="badge bg-light text-dark rounded-pill px-3 py-2">
                            <i class="bi bi-shop me-1"></i> {{ $menuItem->vendor->name }}
                        </span>
                    </a>
                    <span class="badge bg-primary rounded-pill px-3 py-2 ms-2">
                        <i class="bi bi-tag me-1"></i> {{ $menuItem->category->category_name }}
                    </span>
                </div>
                
                <h1 class="display-5 fw-bold mb-3">{{ $menuItem->name }}</h1>
                
                <div class="d-flex align-items-center mb-3">
                    <div class="rating me-3">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= floor($averageRating))
                                <i class="bi bi-star-fill text-warning"></i>
                            @elseif($i - $averageRating < 1)
                                <i class="bi bi-star-half text-warning"></i>
                            @else
                                <i class="bi bi-star text-warning"></i>
                            @endif
                        @endfor
                    </div>
                    <span class="text-muted">{{ number_format($averageRating, 1) }} @if($reviewCount > 0)({{ $reviewCount }} reviews)@endif</span>
                </div>
                
                <div class="mb-4">
                    <h2 class="display-6 fw-bold text-primary mb-2">RM {{ number_format($menuItem->price, 2) }}</h2>
                    @if($vendorSettings && $vendorSettings->accepting_orders)
                        <p class="text-success"><i class="bi bi-check-circle me-2"></i> Available for Order</p>
                    @else
                        <p class="text-danger"><i class="bi bi-x-circle me-2"></i> Currently Not Accepting Orders</p>
                    @endif
                    @if($totalOrders > 0)
                    <p class="text-muted small"><i class="bi bi-fire me-2"></i> {{ $totalOrders }} orders</p>
                    @endif
                </div>
                
                <p class="lead text-muted mb-4">
                    {{ $menuItem->description }}
                </p>
                
                <!-- Store Operating Hours -->
                @if($operatingHours && $operatingHours->count() > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="bi bi-clock text-primary me-2"></i>
                            Store Operating Hours
                        </h5>
                        <div class="row g-2">
                            @foreach($operatingHours as $hour)
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <span class="fw-medium text-capitalize">{{ $hour->day }}</span>
                                    @if($hour->is_open)
                                        <span class="text-success">
                                            <i class="bi bi-clock-history me-1"></i>
                                            {{ \Carbon\Carbon::parse($hour->opening_time)->format('g:i A') }} - 
                                            {{ \Carbon\Carbon::parse($hour->closing_time)->format('g:i A') }}
                                        </span>
                                    @else
                                        <span class="text-danger">
                                            <i class="bi bi-x-circle me-1"></i>Closed
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @if($vendorSettings && ($vendorSettings->phone || $vendorSettings->description))
                        <div class="mt-3 pt-3 border-top">
                            @if($vendorSettings->phone)
                            <p class="mb-2 small">
                                <i class="bi bi-telephone text-primary me-2"></i>
                                <a href="tel:{{ $vendorSettings->phone }}" class="text-decoration-none">
                                    {{ $vendorSettings->phone }}
                                </a>
                            </p>
                            @endif
                            <a href="{{ route('vendor.store', $menuItem->vendor_id) }}" class="btn btn-outline-primary btn-sm w-100 mt-2">
                                <i class="bi bi-shop me-2"></i>Visit Store Page
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
                
                <!-- Customization Options -->
                <form id="add-to-cart-form">
                    @csrf
                    <input type="hidden" name="item_id" value="{{ $menuItem->item_id }}">
                    
                    <div class="card border-0 bg-light p-4 mb-4">
                        <h5 class="fw-bold mb-3">Customize Your Order</h5>
                        
                        <!-- Special Request -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Special Request (Optional)</label>
                            <textarea class="form-control" name="special_request" rows="2" placeholder="Any special instructions? (e.g., less spicy, no onions)"></textarea>
                        </div>
                    </div>
                    
                    <!-- Quantity & Add to Cart -->
                    <div class="d-flex gap-3 mb-4">
                        <div class="quantity-selector">
                            <button type="button" class="btn-decrease">
                                <i class="bi bi-dash"></i>
                            </button>
                            <input type="number" name="quantity" value="1" min="1" max="10" id="quantity">
                            <button type="button" class="btn-increase">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                        
                        @auth
                        <button type="submit" class="btn btn-primary btn-lg flex-grow-1 rounded-pill">
                            <i class="bi bi-cart-plus me-2"></i> Add to Cart
                        </button>
                        @else
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg flex-grow-1 rounded-pill">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Login to Order
                        </a>
                        @endauth
                        
                        <button type="button" class="btn btn-outline-danger btn-lg rounded-circle favorite-btn">
                            <i class="bi bi-heart"></i>
                        </button>
                    </div>
                </form>
                
                <!-- Additional Info -->
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-clock text-primary fs-4 me-3"></i>
                            <div>
                                <small class="text-muted d-block">Prep Time</small>
                                <strong>15-20 mins</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-shop text-success fs-4 me-3"></i>
                            <div>
                                <small class="text-muted d-block">Vendor</small>
                                <strong>{{ $menuItem->vendor->name }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Share -->
                <div>
                    <p class="mb-2 fw-bold">Share this item:</p>
                    <div class="d-flex gap-2">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('food.details', $menuItem->item_id)) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-circle">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(route('food.details', $menuItem->item_id)) }}&text={{ urlencode($menuItem->name) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-circle">
                            <i class="bi bi-twitter"></i>
                        </a>
                        <a href="https://wa.me/?text={{ urlencode($menuItem->name . ' - ' . route('food.details', $menuItem->item_id)) }}" target="_blank" class="btn btn-sm btn-outline-success rounded-circle">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Additional Details Tabs -->
        <div class="row mt-5">
            <div class="col-12" data-aos="fade-up">
                <ul class="nav nav-pills mb-4 justify-content-center" id="productTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill px-4" id="description-tab" data-bs-toggle="pill" data-bs-target="#description" type="button">
                            Description
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="productTabsContent">
                    <!-- Description Tab -->
                    <div class="tab-pane fade show active" id="description">
                        <div class="card border-0">
                            <div class="card-body p-5">
                                <h4 class="fw-bold mb-4">About This Item</h4>
                                <p class="text-muted mb-4">{{ $menuItem->description }}</p>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="fw-bold mb-3">Details</h6>
                                        <ul class="list-unstyled">
                                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Category: {{ $menuItem->category->category_name }}</li>
                                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Vendor: {{ $menuItem->vendor->name }}</li>
                                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Price: RM {{ number_format($menuItem->price, 2) }}</li>
                                            @if($totalOrders > 0)
                                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Total Orders: {{ $totalOrders }}</li>
                                            @endif
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="fw-bold mb-3">Information</h6>
                                        <ul class="list-unstyled">
                                            <li class="mb-2"><i class="bi bi-clock text-primary me-2"></i> Preparation: 15-20 minutes</li>
                                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Status: Available</li>
                                            <li class="mb-2"><i class="bi bi-calendar text-info me-2"></i> Added: {{ $menuItem->created_at->format('M d, Y') }}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Related Items -->
        @if($relatedItems->count() > 0)
        <div class="row mt-5">
            <div class="col-12" data-aos="fade-up">
                <h3 class="fw-bold mb-4">You May Also Like</h3>
                
                <div class="row g-4">
                    @foreach($relatedItems as $item)
                    <div class="col-lg-3 col-md-6">
                        <div class="card food-card">
                            @if($item->image_path && file_exists(public_path($item->image_path)))
                                <img src="{{ asset($item->image_path) }}" class="card-img-top" alt="{{ $item->name }}">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="bi bi-image fs-1 text-muted"></i>
                                </div>
                            @endif
                            <div class="card-body">
                                <h6 class="fw-bold mb-2">{{ $item->name }}</h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-primary fw-bold">RM {{ number_format($item->price, 2) }}</span>
                                    <a href="{{ route('food.details', $item->item_id) }}" class="btn btn-sm btn-outline-primary rounded-pill">View</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</section>
@endsection

@push('scripts')
<script>
    const isGuest = document.getElementById('page-data').getAttribute('data-guest') === 'true';
    
    // Quantity selector
    document.querySelector('.btn-decrease').addEventListener('click', function() {
        const input = document.getElementById('quantity');
        if (input.value > 1) {
            input.value = parseInt(input.value) - 1;
        }
    });
    
    document.querySelector('.btn-increase').addEventListener('click', function() {
        const input = document.getElementById('quantity');
        if (input.value < 10) {
            input.value = parseInt(input.value) + 1;
        }
    });
    
    // Favorite button toggle (wishlist)
    document.querySelector('.favorite-btn').addEventListener('click', function(e) {
        e.preventDefault();
        
        if (isGuest) {
            // Redirect to login if not authenticated
            const loginUrl = '/login?redirect=' + encodeURIComponent(window.location.pathname);
            window.location.href = loginUrl;
            return;
        }
        
        const itemId = '{{ $menuItem->item_id }}';
        const icon = this.querySelector('i');
        
        fetch('/wishlist/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                item_id: itemId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.in_wishlist) {
                    icon.classList.remove('bi-heart');
                    icon.classList.add('bi-heart-fill');
                    this.classList.remove('btn-outline-danger');
                    this.classList.add('btn-danger');
                } else {
                    icon.classList.remove('bi-heart-fill');
                    icon.classList.add('bi-heart');
                    this.classList.remove('btn-danger');
                    this.classList.add('btn-outline-danger');
                }
                showNotification('success', data.message);
                
                // Update wishlist count in navbar if exists
                const wishlistBadge = document.querySelector('.wishlist-count');
                if (wishlistBadge && data.wishlist_count > 0) {
                    wishlistBadge.textContent = data.wishlist_count;
                } else if (wishlistBadge && data.wishlist_count === 0) {
                    wishlistBadge.remove();
                }
            } else {
                showNotification('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Failed to update wishlist');
        });
    });
    
    // Add to cart form submission
    document.getElementById('add-to-cart-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            item_id: this.querySelector('input[name="item_id"]').value,
            quantity: this.querySelector('input[name="quantity"]').value,
            special_request: this.querySelector('textarea[name="special_request"]').value
        };
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Adding...';
        
        fetch('/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            
            if (data.success) {
                showNotification('success', data.message);
                
                // Reset quantity to 1
                document.getElementById('quantity').value = 1;
                
                // Clear special request
                this.querySelector('textarea[name="special_request"]').value = '';
                
                // Update cart count in navbar if exists
                const cartBadge = document.querySelector('.cart-count');
                if (cartBadge) {
                    cartBadge.textContent = data.cart_count;
                }
            } else {
                showNotification('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            showNotification('error', 'Failed to add item to cart');
        });
    });
    
    // Show notification helper
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

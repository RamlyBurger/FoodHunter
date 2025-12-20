@extends('layouts.app')

@section('title', 'My Wishlist - FoodHunter')

@section('content')
<!-- Page Header -->
<section class="bg-light py-5">
    <div class="container">
        <div class="row">
            <div class="col-12" data-aos="fade-up">
                <h1 class="display-4 fw-bold mb-2">
                    <i class="bi bi-heart-fill text-danger me-3"></i>My Wishlist
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item active">Wishlist</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Wishlist Content -->
<section class="py-5">
    <div class="container">
        @if($wishlistItems->count() > 0)
        <div class="row g-4">
            @foreach($wishlistItems as $wishlistItem)
            <div class="col-lg-3 col-md-6" data-aos="fade-up">
                <div class="card food-card position-relative">
                    <!-- Remove from Wishlist Button -->
                    <button class="btn btn-danger btn-sm rounded-circle position-absolute remove-wishlist-btn" 
                            style="top: 10px; right: 10px; z-index: 10; width: 40px; height: 40px;"
                            data-wishlist-id="{{ $wishlistItem->wishlist_id }}">
                        <i class="bi bi-heart-fill"></i>
                    </button>

                    <div class="position-relative">
                        @if($wishlistItem->menuItem->image_path && file_exists(public_path($wishlistItem->menuItem->image_path)))
                            <img src="{{ asset($wishlistItem->menuItem->image_path) }}" class="card-img-top" alt="{{ $wishlistItem->menuItem->name }}">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="bi bi-image fs-1 text-muted"></i>
                            </div>
                        @endif
                        
                        <div class="quick-view">
                            <a href="{{ route('food.details', $wishlistItem->menuItem->item_id) }}" class="btn btn-light btn-sm rounded-circle">
                                <i class="bi bi-eye"></i>
                            </a>
                        </div>
                        
                        @if($wishlistItem->menuItem->created_at->diffInDays(now()) < 7)
                        <span class="badge badge-new">New</span>
                        @endif
                    </div>
                    
                    <div class="card-body">
                        <span class="badge bg-light text-dark mb-2">
                            <i class="bi bi-tag me-1"></i> {{ $wishlistItem->menuItem->category->category_name }}
                        </span>
                        <h5 class="card-title fw-bold mb-2">{{ $wishlistItem->menuItem->name }}</h5>
                        <p class="card-text text-muted small mb-3">{{ Str::limit($wishlistItem->menuItem->description, 60) }}</p>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <span class="h5 text-primary fw-bold mb-0">RM {{ number_format($wishlistItem->menuItem->price, 2) }}</span>
                                <p class="text-muted small mb-0">
                                    <i class="bi bi-shop me-1"></i> {{ $wishlistItem->menuItem->vendor->name }}
                                </p>
                            </div>
                        </div>
                        
                        <button class="btn btn-primary w-100 rounded-pill add-to-cart-btn" 
                                data-item-id="{{ $wishlistItem->menuItem->item_id }}" 
                                data-item-name="{{ $wishlistItem->menuItem->name }}">
                            <i class="bi bi-cart-plus me-1"></i> Add to Cart
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <!-- Empty Wishlist State -->
        <div class="row">
            <div class="col-12">
                <div class="text-center py-5" data-aos="fade-up">
                    <i class="bi bi-heart display-1 text-muted mb-4"></i>
                    <h3 class="mb-3">Your Wishlist is Empty</h3>
                    <p class="text-muted mb-4">Start adding items you love to your wishlist!</p>
                    <a href="{{ route('menu') }}" class="btn btn-primary btn-lg rounded-pill">
                        <i class="bi bi-grid-3x3-gap me-2"></i> Browse Menu
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</section>
@endsection

@push('scripts')
<script>
    const csrfToken = '{{ csrf_token() }}';
    
    // Remove from wishlist
    document.querySelectorAll('.remove-wishlist-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const wishlistId = this.getAttribute('data-wishlist-id');
            const card = this.closest('.col-lg-3');
            
            if (!confirm('Remove this item from your wishlist?')) {
                return;
            }
            
            fetch(`/wishlist/${wishlistId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove card with animation
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.8)';
                    card.style.transition = 'all 0.3s ease';
                    
                    setTimeout(() => {
                        card.remove();
                        
                        // Check if wishlist is empty
                        const remainingItems = document.querySelectorAll('.remove-wishlist-btn').length;
                        if (remainingItems === 0) {
                            location.reload();
                        }
                    }, 300);
                    
                    showNotification('success', data.message);
                    
                    // Update wishlist count in navbar if exists
                    const wishlistBadge = document.querySelector('.wishlist-count');
                    if (wishlistBadge && data.wishlist_count > 0) {
                        wishlistBadge.textContent = data.wishlist_count;
                    } else if (wishlistBadge) {
                        wishlistBadge.remove();
                    }
                } else {
                    showNotification('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('error', 'Failed to remove item from wishlist');
            });
        });
    });
    
    // Add to cart from wishlist
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const itemId = this.getAttribute('data-item-id');
            const itemName = this.getAttribute('data-item-name');
            const originalHTML = this.innerHTML;
            
            // Disable button and show loading
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            
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
                this.disabled = false;
                
                if (data.success) {
                    // Show success animation
                    this.innerHTML = '<i class="bi bi-check-lg"></i> Added';
                    this.classList.add('btn-success');
                    this.classList.remove('btn-primary');
                    
                    showNotification('success', data.message);
                    
                    // Update cart count in navbar if exists
                    const cartBadge = document.querySelector('.cart-count');
                    if (cartBadge) {
                        cartBadge.textContent = data.cart_count;
                    }
                    
                    // Reset button after 2 seconds
                    setTimeout(() => {
                        this.innerHTML = originalHTML;
                        this.classList.remove('btn-success');
                        this.classList.add('btn-primary');
                    }, 2000);
                } else {
                    this.innerHTML = originalHTML;
                    showNotification('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.disabled = false;
                this.innerHTML = originalHTML;
                showNotification('error', 'Failed to add item to cart');
            });
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

@extends('layouts.app')

@section('title', 'Home - FoodHunter')

@section('content')
<div data-guest="{{ auth()->guest() ? 'true' : 'false' }}" id="page-data"></div>
<!-- Hero Section -->
<section class="hero-section text-white position-relative">
    <div class="container position-relative" style="z-index: 10;">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <h1 class="display-3 fw-bold mb-4 display-font">
                    Delicious Food,<br>
                    <span class="text-warning">Delivered Fast!</span>
                </h1>
                <p class="lead mb-4">Order your favorite meals from university canteen vendors and skip the queue. Fresh, tasty, and ready when you are!</p>
                <div class="d-flex gap-3 mb-4">
                    <a href="{{ route('menu') }}" class="btn btn-warning btn-lg rounded-pill px-5">
                        <i class="bi bi-grid-3x3-gap me-2"></i> Browse Menu
                    </a>
                    <a href="#how-it-works" class="btn btn-outline-light btn-lg rounded-pill px-4">
                        <i class="bi bi-play-circle me-2"></i> How It Works
                    </a>
                </div>
                <div class="d-flex gap-4 mt-4">
                    <div class="text-center">
                        <h3 class="fw-bold mb-0">{{ $totalMenuItems }}+</h3>
                        <small class="text-white-75">Menu Items</small>
                    </div>
                    <div class="text-center">
                        <h3 class="fw-bold mb-0">{{ $totalVendors }}+</h3>
                        <small class="text-white-75">Vendors</small>
                    </div>
                    <div class="text-center">
                        <h3 class="fw-bold mb-0">{{ number_format($totalOrders) }}+</h3>
                        <small class="text-white-75">Happy Students</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block" data-aos="fade-left">
                <img src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=600&h=600&fit=crop" 
                     alt="Delicious Food" 
                     class="img-fluid rounded-4 shadow-lg"
                     style="border-radius: 30px !important;">
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-5 fw-bold mb-3">Popular Categories</h2>
            <p class="text-muted">Explore diverse cuisines from our campus vendors</p>
        </div>
        
        <div class="row g-4">
            @php
            $categoryIcons = [
                'Main Dishes' => 'bi-egg-fried',
                'Beverages' => 'bi-cup-hot-fill',
                'Desserts' => 'bi-cake2',
                'Snacks' => 'bi-basket',
                'Rice Dishes' => 'bi-bowl-hot-fill',
                'Noodles' => 'bi-cup-straw',
                'Western' => 'bi-shop',
                'Local Cuisine' => 'bi-cup-hot'
            ];
            $iconColors = ['text-primary', 'text-warning', 'text-success', 'text-danger', 'text-info', 'text-secondary', 'text-dark'];
            @endphp
            
            @foreach($categories as $index => $category)
            <div class="col-lg-3 col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="{{ ($index + 1) * 100 }}">
                <div class="card text-center border-0 category-card h-100">
                    <div class="card-body p-4">
                        <div class="category-icon mb-3">
                            <i class="bi {{ $categoryIcons[$category->category_name] ?? 'bi-shop' }} fs-1 {{ $iconColors[$index % count($iconColors)] }}"></i>
                        </div>
                        <h5 class="fw-bold mb-2">{{ $category->category_name }}</h5>
                        <p class="text-muted small mb-3">{{ $category->menu_items_count }}+ items</p>
                        <a href="{{ route('menu', ['category' => $category->category_id]) }}" class="btn btn-sm btn-outline-primary rounded-pill">Explore</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Featured Items Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-5" data-aos="fade-up">
            <div>
                <h2 class="display-5 fw-bold mb-2">Today's Specials</h2>
                <p class="text-muted">Handpicked favorites just for you</p>
            </div>
            <a href="{{ route('menu') }}" class="btn btn-primary rounded-pill">
                View All <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>
        
        <div class="row g-4">
            @foreach($featuredItems->take(4) as $index => $item)
            <!-- Food Card -->
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
                        
                        @if($item->created_at->diffInDays(now()) < 7)
                        <span class="badge badge-new badge-overlay">New</span>
                        @elseif($item->order_items_count > 10)
                        <span class="badge badge-popular badge-overlay">Popular</span>
                        @endif
                        
                        <div class="quick-view">
                            <a href="{{ route('food.details', $item->item_id) }}" class="btn btn-light btn-sm rounded-circle">
                                <i class="bi bi-eye"></i>
                            </a>
                            <button class="btn btn-light btn-sm rounded-circle favorite-btn {{ in_array($item->item_id, $wishlistItemIds) ? 'text-danger' : '' }}" data-item-id="{{ $item->item_id }}">
                                <i class="bi bi-heart{{ in_array($item->item_id, $wishlistItemIds) ? '-fill' : '' }}"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0 fw-bold">{{ Str::limit($item->name, 20) }}</h5>
                            <span class="price-tag">RM{{ number_format($item->price, 0) }}</span>
                        </div>
                        <p class="text-muted small mb-3">{{ Str::limit($item->description, 40) }}</p>
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <small class="text-muted"><i class="bi bi-shop me-1"></i> {{ Str::limit($item->vendor->name, 15) }}</small>
                            <div class="d-flex gap-2">
                                <a href="{{ route('food.details', $item->item_id) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @auth
                                <button class="btn btn-sm btn-primary rounded-pill add-to-cart-btn" data-item-id="{{ $item->item_id }}" data-item-name="{{ $item->name }}">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section id="how-it-works" class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-5 fw-bold mb-3">How It Works</h2>
            <p class="text-muted">Simple steps to satisfy your hunger</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-3 col-md-6 text-center" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-icon primary mx-auto mb-3">
                    <i class="bi bi-search"></i>
                </div>
                <h5 class="fw-bold mb-3">1. Browse Menu</h5>
                <p class="text-muted">Explore hundreds of delicious options from multiple vendors</p>
            </div>
            
            <div class="col-lg-3 col-md-6 text-center" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-icon success mx-auto mb-3">
                    <i class="bi bi-cart-check"></i>
                </div>
                <h5 class="fw-bold mb-3">2. Place Order</h5>
                <p class="text-muted">Add items to cart and customize your order</p>
            </div>
            
            <div class="col-lg-3 col-md-6 text-center" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-icon warning mx-auto mb-3">
                    <i class="bi bi-credit-card"></i>
                </div>
                <h5 class="fw-bold mb-3">3. Make Payment</h5>
                <p class="text-muted">Pay securely using multiple payment options</p>
            </div>
            
            <div class="col-lg-3 col-md-6 text-center" data-aos="fade-up" data-aos-delay="400">
                <div class="stat-icon danger mx-auto mb-3">
                    <i class="bi bi-bag-check"></i>
                </div>
                <h5 class="fw-bold mb-3">4. Pick Up</h5>
                <p class="text-muted">Collect your order using QR code - no waiting!</p>
            </div>
        </div>
    </div>
</section>

<!-- Popular Items Section -->
@if($popularItems->count() > 0)
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-5 fw-bold mb-3">Most Popular</h2>
            <p class="text-muted">Top choices among students</p>
        </div>
        
        <div class="row g-4">
            @foreach($popularItems as $index => $item)
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
                        
                        <span class="badge badge-popular badge-overlay">
                            <i class="bi bi-fire"></i> {{ $item->order_items_count }} orders
                        </span>
                        
                        <div class="quick-view">
                            <a href="{{ route('food.details', $item->item_id) }}" class="btn btn-light btn-sm rounded-circle">
                                <i class="bi bi-eye"></i>
                            </a>
                            <button class="btn btn-light btn-sm rounded-circle favorite-btn {{ in_array($item->item_id, $wishlistItemIds) ? 'text-danger' : '' }}" data-item-id="{{ $item->item_id }}">
                                <i class="bi bi-heart{{ in_array($item->item_id, $wishlistItemIds) ? '-fill' : '' }}"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0 fw-bold">{{ Str::limit($item->name, 20) }}</h5>
                            <span class="price-tag">RM{{ number_format($item->price, 0) }}</span>
                        </div>
                        <p class="text-muted small mb-3">{{ Str::limit($item->description, 40) }}</p>
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <small class="text-muted"><i class="bi bi-shop me-1"></i> {{ Str::limit($item->vendor->name, 15) }}</small>
                            <div class="d-flex gap-2">
                                <a href="{{ route('food.details', $item->item_id) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @auth
                                <button class="btn btn-sm btn-primary rounded-pill add-to-cart-btn" data-item-id="{{ $item->item_id }}" data-item-name="{{ $item->name }}">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Why Choose Us Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                <h2 class="display-5 fw-bold mb-4">Why Choose FoodHunter?</h2>
                
                <div class="d-flex mb-4">
                    <div class="stat-icon success me-3">
                        <i class="bi bi-lightning-charge"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-2">Fast & Convenient</h5>
                        <p class="text-muted">Skip the queue and get your food ready when you arrive</p>
                    </div>
                </div>
                
                <div class="d-flex mb-4">
                    <div class="stat-icon primary me-3">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-2">Safe & Secure</h5>
                        <p class="text-muted">Your payments and data are protected with encryption</p>
                    </div>
                </div>
                
                <div class="d-flex mb-4">
                    <div class="stat-icon warning me-3">
                        <i class="bi bi-gift"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-2">Loyalty Rewards</h5>
                        <p class="text-muted">Earn points with every order and redeem exciting rewards</p>
                    </div>
                </div>
                
                <div class="d-flex">
                    <div class="stat-icon danger me-3">
                        <i class="bi bi-star"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-2">Quality Food</h5>
                        <p class="text-muted">Trusted vendors serving fresh and delicious meals daily</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6" data-aos="fade-left">
                <img src="https://images.unsplash.com/photo-1556910103-1c02745aae4d?w=600&h=800&fit=crop" 
                     alt="Happy Students" 
                     class="img-fluid rounded-4 shadow-lg">
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-5 fw-bold mb-3">What Students Say</h2>
            <p class="text-muted">Real reviews from our happy customers</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="card border-0 h-100">
                    <div class="card-body p-4">
                        <div class="rating mb-3">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="mb-4">"FoodHunter saved me so much time! No more waiting in long queues. I can order between classes and pick up immediately."</p>
                        <div class="d-flex align-items-center">
                            <img src="https://ui-avatars.com/api/?name=Sarah+Lee&background=6366f1&color=fff" 
                                 class="rounded-circle me-3" width="50" height="50" alt="Sarah">
                            <div>
                                <h6 class="mb-0 fw-bold">Sarah Lee</h6>
                                <small class="text-muted">Engineering Student</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="card border-0 h-100">
                    <div class="card-body p-4">
                        <div class="rating mb-3">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="mb-4">"Love the loyalty points system! I've already redeemed two free meals. The app is super easy to use too."</p>
                        <div class="d-flex align-items-center">
                            <img src="https://ui-avatars.com/api/?name=Ahmad+Rahman&background=ec4899&color=fff" 
                                 class="rounded-circle me-3" width="50" height="50" alt="Ahmad">
                            <div>
                                <h6 class="mb-0 fw-bold">Ahmad Rahman</h6>
                                <small class="text-muted">Business Student</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="card border-0 h-100">
                    <div class="card-body p-4">
                        <div class="rating mb-3">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="mb-4">"Great variety of food choices! From local to western cuisine, everything is available. Payment is smooth too!"</p>
                        <div class="d-flex align-items-center">
                            <img src="https://ui-avatars.com/api/?name=Michelle+Tan&background=10b981&color=fff" 
                                 class="rounded-circle me-3" width="50" height="50" alt="Michelle">
                            <div>
                                <h6 class="mb-0 fw-bold">Michelle Tan</h6>
                                <small class="text-muted">Medical Student</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5" style="background: var(--gradient-1);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8" data-aos="fade-right">
                <h2 class="display-5 fw-bold text-white mb-3">Ready to Order?</h2>
                <p class="lead text-white mb-0">Join thousands of students enjoying hassle-free food ordering today!</p>
            </div>
            <div class="col-lg-4 text-lg-end" data-aos="fade-left">
                @guest
                <a href="{{ route('register') }}" class="btn btn-warning btn-lg rounded-pill px-5">
                    Get Started <i class="bi bi-arrow-right ms-2"></i>
                </a>
                @else
                <a href="{{ route('menu') }}" class="btn btn-warning btn-lg rounded-pill px-5">
                    Order Now <i class="bi bi-arrow-right ms-2"></i>
                </a>
                @endguest
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    // CSRF Token
    const csrfToken = '{{ csrf_token() }}';
    const isGuest = document.getElementById('page-data').getAttribute('data-guest') === 'true';
    
    // Favorite button toggle (wishlist)
    document.querySelectorAll('.favorite-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (isGuest) {
                // Redirect to login if not authenticated
                const loginUrl = '/login?redirect=' + encodeURIComponent(window.location.pathname);
                window.location.href = loginUrl;
                return;
            }
            
            const itemId = this.getAttribute('data-item-id');
            const icon = this.querySelector('i');
            const wasInWishlist = icon.classList.contains('bi-heart-fill');
            
            fetch('/wishlist/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
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
                        this.classList.add('text-danger');
                    } else {
                        icon.classList.remove('bi-heart-fill');
                        icon.classList.add('bi-heart');
                        this.classList.remove('text-danger');
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
    });
    
    // Add to cart functionality
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

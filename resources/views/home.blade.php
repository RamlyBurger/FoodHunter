@extends('layouts.app')

@section('title', 'Home')

@push('styles')
<style>
    .hero-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        position: relative;
        overflow: hidden;
        padding-top: 80px !important;
    }
    .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ccircle cx='50' cy='50' r='40' fill='rgba(255,255,255,0.1)'/%3E%3C/svg%3E") repeat;
        background-size: 100px 100px;
        animation: float 20s linear infinite;
    }
    @keyframes float {
        0% { transform: translateY(0) translateX(0); }
        50% { transform: translateY(-20px) translateX(10px); }
        100% { transform: translateY(0) translateX(0); }
    }
    .bubble {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        animation: rise 10s infinite ease-in;
    }
    .bubble:nth-child(1) { width: 80px; height: 80px; left: 10%; animation-duration: 8s; animation-delay: 0s; }
    .bubble:nth-child(2) { width: 60px; height: 60px; left: 20%; animation-duration: 12s; animation-delay: 1s; }
    .bubble:nth-child(3) { width: 100px; height: 100px; left: 35%; animation-duration: 10s; animation-delay: 2s; }
    .bubble:nth-child(4) { width: 40px; height: 40px; left: 50%; animation-duration: 9s; animation-delay: 0.5s; }
    .bubble:nth-child(5) { width: 70px; height: 70px; left: 65%; animation-duration: 11s; animation-delay: 1.5s; }
    .bubble:nth-child(6) { width: 90px; height: 90px; left: 80%; animation-duration: 13s; animation-delay: 3s; }
    .bubble:nth-child(7) { width: 50px; height: 50px; left: 90%; animation-duration: 7s; animation-delay: 2.5s; }
    .bubble:nth-child(8) { width: 120px; height: 120px; left: 5%; animation-duration: 15s; animation-delay: 4s; }
    @keyframes rise {
        0% { bottom: -150px; opacity: 0; transform: translateX(0) scale(0.5); }
        10% { opacity: 0.4; }
        50% { opacity: 0.2; transform: translateX(30px) scale(1); }
        100% { bottom: 110%; opacity: 0; transform: translateX(-20px) scale(0.8); }
    }
    .hero-stats {
        display: flex;
        gap: 2rem;
        margin-top: 2rem;
    }
    .hero-stat {
        text-align: center;
    }
    .hero-stat h3 {
        font-size: 1.5rem;
        font-weight: 800;
        margin-bottom: 0;
    }
    .hero-stat small {
        opacity: 0.75;
        font-size: 0.75rem;
    }
    .how-it-works-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 1.5rem;
    }
    .testimonial-card {
        background: white;
        border-radius: 16px;
        padding: 1.25rem;
        height: 100%;
    }
    .testimonial-card .rating {
        color: var(--warning-color);
        margin-bottom: 0.75rem;
    }
    .feature-icon {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
</style>
@endpush

@section('content')
<!-- Hero Section -->
<div class="hero-section py-5 mb-4">
    <!-- Bubbles -->
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
    
    <div class="container position-relative" style="z-index: 2;">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="text-white mb-3" style="font-size: 2.5rem; font-weight: 800; letter-spacing: -1px; line-height: 1.1;">
                    Hungry?<br>
                    <span class="text-warning">Order Now.</span>
                </h1>
                <p class="text-white mb-4" style="font-size: 1rem; line-height: 1.6; opacity: 0.9;">Order delicious food from TARUMT canteen vendors and skip the queue!</p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="{{ url('/menu') }}" class="btn btn-warning btn-lg px-4 text-dark fw-bold">
                        <i class="bi bi-grid-3x3-gap me-2"></i> Browse Menu
                    </a>
                    <a href="#how-it-works" class="btn btn-outline-light btn-lg px-4">
                        <i class="bi bi-play-circle me-2"></i> How It Works
                    </a>
                </div>
                <!-- Hero Stats -->
                @php
                    $totalMenuItems = \App\Models\MenuItem::where('is_available', true)->count();
                    $totalVendors = \App\Models\Vendor::count();
                    $totalOrders = \App\Models\Order::where('status', 'completed')->count();
                @endphp
                <div class="hero-stats text-white">
                    <div class="hero-stat">
                        <h3>{{ $totalMenuItems }}+</h3>
                        <small>Menu Items</small>
                    </div>
                    <div class="hero-stat">
                        <h3>{{ $totalVendors }}+</h3>
                        <small>Vendors</small>
                    </div>
                    <div class="hero-stat">
                        <h3>{{ number_format($totalOrders) }}+</h3>
                        <small>Happy Students</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 text-center d-none d-lg-block">
                <div class="position-relative">
                    <img src="https://scontent.fkul3-5.fna.fbcdn.net/v/t39.30808-6/515318255_24142182252080761_5900394254721126609_n.jpg?_nc_cat=100&ccb=1-7&_nc_sid=cf85f3&_nc_ohc=dRej-Ul_PbQQ7kNvwGaqgj2&_nc_oc=AdlNN0BoKjFjYeeleaLnr9rqlegwFJzFQKfmUH-ZwM_z3ZeXxewtEZ0A6rznbEumDY2T6PfmvPpN1KrDuGprGE7J&_nc_zt=23&_nc_ht=scontent.fkul3-5.fna&_nc_gid=byyixzzFsMZVL5118ty14g&oh=00_AflamUvLMaLzhKaeruSE4Fh2Vs83PVC2X30zSOKV6vq-jQ&oe=694DA0C0" 
                         alt="Delicious Food" 
                         class="img-fluid rounded-4 shadow-lg"
                         style="max-width: 350px; border-radius: 30px !important;">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container py-3">
    <!-- Categories -->
    <section class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 style="font-weight: 700; letter-spacing: -0.5px;"><i class="bi bi-grid-3x3-gap me-2" style="color: var(--primary-color);"></i>Categories</h3>
            <a href="{{ url('/menu') }}" class="btn btn-sm btn-outline-secondary">View All</a>
        </div>
        <div class="row g-3">
            @forelse($categories as $category)
            <div class="col-6 col-md-2">
                <a href="{{ url('/menu?category=' . $category->id) }}" class="text-decoration-none">
                    <div class="card category-card h-100 text-center p-0 overflow-hidden" style="border: 1px solid var(--gray-200);">
                        <div class="position-relative">
                            <img src="{{ $category->image ?? '' }}" 
                                 alt="{{ $category->name }}" 
                                 class="w-100"
                                 style="height: 80px; object-fit: cover;"
                                 onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($category->name) }}&background=f3f4f6&color=9ca3af&size=200&font-size=0.33&bold=true';">
                            <div class="position-absolute bottom-0 start-0 end-0 p-2" style="background: linear-gradient(transparent, rgba(0,0,0,0.7));">
                                <h6 class="mb-0 text-white" style="font-weight: 600; font-size: 0.85rem;">{{ $category->name }}</h6>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @empty
            <div class="col-12">
                <p class="text-muted">No categories available</p>
            </div>
            @endforelse
        </div>
    </section>

    <!-- Featured Items -->
    <section class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 style="font-weight: 700; letter-spacing: -0.5px;"><i class="bi bi-star-fill me-2" style="color: var(--warning-color);"></i>Featured Items</h3>
            <a href="{{ url('/menu?featured=1') }}" class="btn btn-sm btn-outline-secondary">View All</a>
        </div>
        <div class="row g-4">
            @forelse($featured as $item)
                <x-menu-item-card :item="$item" :wishlistIds="$wishlistIds ?? []" />
            @empty
            <div class="col-12">
                <div class="text-center py-4">
                    <i class="bi bi-star text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">No featured items available</p>
                </div>
            </div>
            @endforelse
        </div>
    </section>

    <!-- Vendors -->
    <section class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 style="font-weight: 700; letter-spacing: -0.5px;"><i class="bi bi-shop me-2" style="color: var(--primary-color);"></i>Our Vendors</h3>
            <a href="{{ url('/vendors') }}" class="btn btn-sm btn-outline-secondary">View All</a>
        </div>
        <div class="row g-4">
            @forelse($vendors as $vendor)
            <div class="col-md-4">
                <a href="{{ url('/vendors/' . $vendor->id) }}" class="text-decoration-none">
                    <div class="card h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                @if($vendor->logo)
                                <img src="{{ $vendor->logo }}" alt="{{ $vendor->store_name }}" class="rounded-circle me-3" style="width: 56px; height: 56px; object-fit: cover;">
                                @else
                                <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 56px; height: 56px; background: var(--gray-100);">
                                    <i class="bi bi-shop-window fs-4" style="color: var(--primary-color);"></i>
                                </div>
                                @endif
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 text-dark" style="font-weight: 600;">{{ $vendor->store_name }}</h6>
                                    <small class="text-muted" style="font-size: 0.85rem;">{{ Str::limit($vendor->description, 40) ?? 'Delicious food awaits!' }}</small>
                                </div>
                            </div>
                            <div class="mt-3 d-flex gap-2">
                                <span class="badge {{ $vendor->is_open ? 'bg-success' : 'bg-secondary' }}" style="font-size: 0.7rem;">
                                    {{ $vendor->is_open ? 'Open' : 'Closed' }}
                                </span>
                                @if($vendor->avg_prep_time)
                                <span class="badge" style="background: var(--gray-200); color: var(--text-primary); font-size: 0.7rem;">
                                    <i class="bi bi-clock"></i> ~{{ $vendor->avg_prep_time }}min
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @empty
            <div class="col-12">
                <div class="text-center py-4">
                    <i class="bi bi-shop text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">No vendors available</p>
                </div>
            </div>
            @endforelse
        </div>
    </section>
</div>

<!-- How It Works Section -->
<section id="how-it-works" class="py-4" style="background: var(--gray-100);">
    <div class="container">
        <div class="text-center mb-4">
            <h2 style="font-weight: 700; letter-spacing: -0.5px;">How It Works</h2>
            <p class="text-muted">Simple steps to satisfy your hunger</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-3 col-md-6 text-center">
                <div class="how-it-works-icon" style="background: rgba(0, 122, 255, 0.15); color: var(--primary-color);">
                    <i class="bi bi-search"></i>
                </div>
                <h5 style="font-weight: 600;">1. Browse Menu</h5>
                <p class="text-muted small">Explore hundreds of delicious options from multiple vendors</p>
            </div>
            
            <div class="col-lg-3 col-md-6 text-center">
                <div class="how-it-works-icon" style="background: rgba(52, 199, 89, 0.15); color: var(--success-color);">
                    <i class="bi bi-cart-check"></i>
                </div>
                <h5 style="font-weight: 600;">2. Place Order</h5>
                <p class="text-muted small">Add items to cart and customize your order</p>
            </div>
            
            <div class="col-lg-3 col-md-6 text-center">
                <div class="how-it-works-icon" style="background: rgba(255, 149, 0, 0.15); color: var(--warning-color);">
                    <i class="bi bi-credit-card"></i>
                </div>
                <h5 style="font-weight: 600;">3. Make Payment</h5>
                <p class="text-muted small">Pay securely using multiple payment options</p>
            </div>
            
            <div class="col-lg-3 col-md-6 text-center">
                <div class="how-it-works-icon" style="background: rgba(255, 59, 48, 0.15); color: var(--danger-color);">
                    <i class="bi bi-bag-check"></i>
                </div>
                <h5 style="font-weight: 600;">4. Pick Up</h5>
                <p class="text-muted small">Collect your order using queue number - no waiting!</p>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="py-4 bg-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h2 style="font-weight: 700; letter-spacing: -0.5px; margin-bottom: 2rem;">Why Choose FoodHunter?</h2>
                
                <div class="d-flex mb-4">
                    <div class="feature-icon me-3" style="background: rgba(52, 199, 89, 0.15); color: var(--success-color);">
                        <i class="bi bi-lightning-charge"></i>
                    </div>
                    <div>
                        <h5 style="font-weight: 600; margin-bottom: 0.5rem;">Fast & Convenient</h5>
                        <p class="text-muted mb-0">Skip the queue and get your food ready when you arrive</p>
                    </div>
                </div>
                
                <div class="d-flex mb-4">
                    <div class="feature-icon me-3" style="background: rgba(0, 122, 255, 0.15); color: var(--primary-color);">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div>
                        <h5 style="font-weight: 600; margin-bottom: 0.5rem;">Safe & Secure</h5>
                        <p class="text-muted mb-0">Your payments and data are protected with encryption</p>
                    </div>
                </div>
                
                <div class="d-flex mb-4">
                    <div class="feature-icon me-3" style="background: rgba(255, 149, 0, 0.15); color: var(--warning-color);">
                        <i class="bi bi-gift"></i>
                    </div>
                    <div>
                        <h5 style="font-weight: 600; margin-bottom: 0.5rem;">Exclusive Vouchers</h5>
                        <p class="text-muted mb-0">Redeem vouchers and enjoy discounts on your favorite meals</p>
                    </div>
                </div>
                
                <div class="d-flex">
                    <div class="feature-icon me-3" style="background: rgba(255, 59, 48, 0.15); color: var(--danger-color);">
                        <i class="bi bi-star"></i>
                    </div>
                    <div>
                        <h5 style="font-weight: 600; margin-bottom: 0.5rem;">Quality Food</h5>
                        <p class="text-muted mb-0">Trusted vendors serving fresh and delicious meals daily</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <img src="https://images.unsplash.com/photo-1556910103-1c02745aae4d?w=600&h=500&fit=crop" 
                     alt="Happy Students" 
                     class="img-fluid rounded-4 shadow-lg">
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-4" style="background: var(--gray-100);">
    <div class="container">
        <div class="text-center mb-4">
            <h2 style="font-weight: 700; letter-spacing: -0.5px;">What Students Say</h2>
            <p class="text-muted">Real reviews from our happy customers</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="testimonial-card">
                    <div class="rating">
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
                            <h6 class="mb-0" style="font-weight: 600;">Sarah Lee</h6>
                            <small class="text-muted">Engineering Student</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="testimonial-card">
                    <div class="rating">
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
                            <h6 class="mb-0" style="font-weight: 600;">Ahmad Rahman</h6>
                            <small class="text-muted">Business Student</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="testimonial-card">
                    <div class="rating">
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
                            <h6 class="mb-0" style="font-weight: 600;">Michelle Tan</h6>
                            <small class="text-muted">Computer Science Student</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-4" style="background: linear-gradient(135deg, var(--primary-color) 0%, #5856D6 100%);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="text-white mb-3" style="font-weight: 700;">Ready to Order?</h2>
                <p class="lead text-white mb-0" style="opacity: 0.9;">Join thousands of students enjoying hassle-free food ordering today!</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                @guest
                <a href="{{ url('/register') }}" class="btn btn-warning btn-lg px-5" style="font-weight: 600;">
                    Get Started <i class="bi bi-arrow-right ms-2"></i>
                </a>
                @else
                <a href="{{ url('/menu') }}" class="btn btn-warning btn-lg px-5" style="font-weight: 600;">
                    Order Now <i class="bi bi-arrow-right ms-2"></i>
                </a>
                @endguest
            </div>
        </div>
    </div>
</section>
@endsection


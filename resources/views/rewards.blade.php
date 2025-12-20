@extends('layouts.app')

@section('title', 'Rewards - FoodHunter')

@section('content')
<!-- Page Header -->
<section class="bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8" data-aos="fade-right">
                <h1 class="display-4 fw-bold mb-3">
                    <i class="bi bi-star-fill text-warning me-3"></i>Loyalty Rewards
                </h1>
                <p class="lead mb-0 text-white">Earn points with every purchase and redeem them for exciting rewards!</p>
            </div>
            <div class="col-lg-4" data-aos="fade-left">
                <div class="card bg-white text-dark border-0 shadow-lg">
                    <div class="card-body p-4 text-center">
                        <small class="text-muted d-block mb-2 fw-bold">Your Points Balance</small>
                        <h1 class="display-3 fw-bold text-primary mb-0">{{ number_format($currentPoints) }}</h1>
                        <small class="text-muted fw-bold">points</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="py-5 bg-light">
    <div class="container">
        <h3 class="fw-bold text-center mb-5" data-aos="fade-up">How It Works</h3>
        <div class="row g-4">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="text-center">
                    <div class="mb-3">
                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="bi bi-cart-check fs-1"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-2">1. Order Food</h5>
                    <p class="text-muted">Place orders from your favorite vendors in the canteen</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="text-center">
                    <div class="mb-3">
                        <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="bi bi-star-fill fs-1"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-2">2. Earn Points</h5>
                    <p class="text-muted">Get 1 point for every RM 1 you spend</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="text-center">
                    <div class="mb-3">
                        <div class="rounded-circle bg-warning text-white d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="bi bi-gift fs-1"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-2">3. Redeem Rewards</h5>
                    <p class="text-muted">Exchange your points for vouchers and free items</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Alerts -->
@if(session('success'))
<section class="py-3">
    <div class="container">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{!! session('success') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
</section>
@endif

@if(session('error'))
<section class="py-3">
    <div class="container">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
</section>
@endif

<!-- Available Rewards -->
<section class="py-5">
    <div class="container">
        <h3 class="fw-bold mb-4" data-aos="fade-up">Available Rewards</h3>
        
        <div class="row g-4">
            @foreach($rewards as $index => $reward)
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="{{ $index * 100 }}">
                <div class="card border-0 shadow-sm h-100 {{ $currentPoints >= $reward->points_required ? '' : 'opacity-75' }}">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                @if($reward->reward_type === 'voucher')
                                    <i class="bi bi-ticket-perforated fs-1 text-primary"></i>
                                @elseif($reward->reward_type === 'free_item')
                                    <i class="bi bi-gift fs-1 text-success"></i>
                                @else
                                    <i class="bi bi-percent fs-1 text-warning"></i>
                                @endif
                            </div>
                            <span class="badge bg-primary px-3 py-2">
                                {{ $reward->points_required }} pts
                            </span>
                        </div>
                        
                        <h5 class="fw-bold mb-2">{{ $reward->reward_name }}</h5>
                        <p class="text-muted small mb-3">{{ $reward->description }}</p>
                        
                        @if($reward->terms_conditions)
                        <div class="mb-3">
                            <button class="btn btn-sm btn-outline-secondary w-100" type="button" data-bs-toggle="collapse" data-bs-target="#terms-{{ $reward->reward_id }}">
                                <i class="bi bi-info-circle me-1"></i> View Terms & Conditions
                            </button>
                            <div class="collapse mt-2" id="terms-{{ $reward->reward_id }}">
                                <div class="card card-body bg-light border-0">
                                    <small class="text-dark">{{ $reward->terms_conditions }}</small>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        @if($reward->stock !== null)
                        <p class="small mb-3">
                            <i class="bi bi-box-seam me-1"></i>
                            <span class="{{ $reward->stock > 0 ? 'text-success' : 'text-danger' }}">
                                {{ $reward->stock > 0 ? $reward->stock . ' left' : 'Out of stock' }}
                            </span>
                        </p>
                        @endif
                        
                        @if($currentPoints >= $reward->points_required && ($reward->stock === null || $reward->stock > 0))
                            <form action="{{ route('rewards.redeem', $reward->reward_id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100 rounded-pill" onclick="return confirm('Redeem this reward for {{ $reward->points_required }} points?')">
                                    <i class="bi bi-check-circle me-2"></i> Redeem Now
                                </button>
                            </form>
                        @elseif($reward->stock !== null && $reward->stock <= 0)
                            <button class="btn btn-outline-secondary w-100 rounded-pill" disabled>
                                <i class="bi bi-x-circle me-2"></i> Out of Stock
                            </button>
                        @else
                            <button class="btn btn-outline-primary w-100 rounded-pill" disabled>
                                <i class="bi bi-lock me-2"></i> Need {{ $reward->points_required - $currentPoints }} more points
                            </button>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Redeemed Rewards -->
@if($redeemedRewards->count() > 0)
<section class="py-5 bg-light">
    <div class="container">
        <h3 class="fw-bold mb-4" data-aos="fade-up">My Redeemed Rewards</h3>
        
        <div class="row g-4">
            @foreach($redeemedRewards as $index => $redeemed)
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="{{ $index * 100 }}">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h6 class="fw-bold mb-0">{{ $redeemed->reward->reward_name }}</h6>
                            <span class="badge {{ $redeemed->is_used ? 'bg-secondary' : 'bg-success' }}">
                                {{ $redeemed->is_used ? 'Used' : 'Active' }}
                            </span>
                        </div>
                        
                        <div class="bg-light rounded p-3 mb-3">
                            <small class="text-muted d-block mb-1">Voucher Code</small>
                            <h5 class="fw-bold mb-0 font-monospace">{{ $redeemed->voucher_code }}</h5>
                        </div>
                        
                        <p class="small text-muted mb-2">
                            <i class="bi bi-calendar3 me-1"></i>
                            Redeemed: {{ $redeemed->redeemed_at->format('M d, Y') }}
                        </p>
                        
                        @if($redeemed->used_at)
                        <p class="small text-muted mb-0">
                            <i class="bi bi-check-circle me-1"></i>
                            Used: {{ \Carbon\Carbon::parse($redeemed->used_at)->format('M d, Y') }}
                        </p>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- CTA Section -->
<section class="py-5 bg-primary text-white">
    <div class="container text-center" data-aos="fade-up">
        <h3 class="fw-bold mb-3">Start Earning More Points!</h3>
        <p class="lead mb-4">Order from your favorite vendors and watch your points grow</p>
        <a href="{{ route('menu') }}" class="btn btn-warning btn-lg rounded-pill">
            <i class="bi bi-arrow-right-circle me-2"></i> Browse Menu
        </a>
    </div>
</section>
@endsection

@extends('layouts.app')

@section('title', $vendor->name . ' - Store')

@section('content')
<!-- Store Header -->
<section class="bg-light py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb" data-aos="fade-right">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('menu') }}" class="text-decoration-none">Menu</a></li>
                        <li class="breadcrumb-item active">{{ $vendor->name }}</li>
                    </ol>
                </nav>
                
                <div class="d-flex align-items-start mt-3" data-aos="fade-up">
                    @if($vendorSettings && $vendorSettings->logo_path)
                    <img src="{{ asset('storage/' . $vendorSettings->logo_path) }}" 
                         alt="{{ $vendor->name }}" 
                         class="rounded-circle me-4 shadow" 
                         style="width: 100px; height: 100px; object-fit: cover;">
                    @else
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-4 shadow" 
                         style="width: 100px; height: 100px;">
                        <i class="bi bi-shop text-white" style="font-size: 2.5rem;"></i>
                    </div>
                    @endif
                    
                    <div>
                        <h1 class="display-5 fw-bold mb-2">{{ $vendorSettings->store_name ?? $vendor->name }}</h1>
                        @if($vendorSettings && $vendorSettings->accepting_orders)
                            <span class="badge bg-success rounded-pill px-3 py-2">
                                <i class="bi bi-check-circle me-1"></i> Open for Orders
                            </span>
                        @else
                            <span class="badge bg-danger rounded-pill px-3 py-2">
                                <i class="bi bi-x-circle me-1"></i> Currently Closed
                            </span>
                        @endif
                        
                        @if($vendorSettings && $vendorSettings->description)
                        <p class="text-muted mt-3 mb-0">{{ $vendorSettings->description }}</p>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 mt-4 mt-lg-0" data-aos="fade-left">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="bi bi-info-circle text-primary me-2"></i>
                            Store Information
                        </h5>
                        
                        @if($vendorSettings && $vendorSettings->phone)
                        <p class="mb-2">
                            <i class="bi bi-telephone text-muted me-2"></i>
                            <a href="tel:{{ $vendorSettings->phone }}" class="text-decoration-none">
                                {{ $vendorSettings->phone }}
                            </a>
                        </p>
                        @endif
                        
                        <p class="mb-2">
                            <i class="bi bi-envelope text-muted me-2"></i>
                            <a href="mailto:{{ $vendor->email }}" class="text-decoration-none">
                                {{ $vendor->email }}
                            </a>
                        </p>
                        
                        <div class="d-flex justify-content-between mt-3 pt-3 border-top">
                            <div class="text-center">
                                <h4 class="fw-bold text-primary mb-0">{{ $totalItems }}</h4>
                                <small class="text-muted">Menu Items</small>
                            </div>
                            <div class="text-center">
                                <h4 class="fw-bold text-primary mb-0">{{ $totalOrders }}</h4>
                                <small class="text-muted">Orders Served</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Operating Hours & Payment Methods -->
<section class="py-4 bg-white border-bottom">
    <div class="container">
        <div class="row g-4">
            <!-- Operating Hours -->
            @if($operatingHours && $operatingHours->count() > 0)
            <div class="col-lg-7" data-aos="fade-up">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="bi bi-clock text-primary me-2"></i>
                            Operating Hours
                        </h5>
                        <div class="row g-2">
                            @foreach($operatingHours as $hour)
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between align-items-center p-2 rounded {{ strtolower($hour->day) == strtolower(now()->format('l')) ? 'bg-light' : '' }}">
                                    <span class="fw-medium text-capitalize {{ strtolower($hour->day) == strtolower(now()->format('l')) ? 'text-primary' : '' }}">
                                        {{ $hour->day }}
                                        @if(strtolower($hour->day) == strtolower(now()->format('l')))
                                            <span class="badge bg-primary badge-sm ms-1">Today</span>
                                        @endif
                                    </span>
                                    @if($hour->is_open)
                                        <span class="text-success small">
                                            {{ \Carbon\Carbon::parse($hour->opening_time)->format('g:i A') }} - 
                                            {{ \Carbon\Carbon::parse($hour->closing_time)->format('g:i A') }}
                                        </span>
                                    @else
                                        <span class="text-danger small">
                                            <i class="bi bi-x-circle me-1"></i>Closed
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Payment Methods -->
            @if($vendorSettings && $vendorSettings->payment_methods)
            <div class="col-lg-5" data-aos="fade-up" data-aos-delay="100">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="bi bi-credit-card text-primary me-2"></i>
                            Accepted Payment Methods
                        </h5>
                        <div class="d-flex flex-column gap-3">
                            @php
                                $paymentMethods = explode(',', $vendorSettings->payment_methods);
                            @endphp
                            
                            @if(in_array('cash', $paymentMethods))
                            <div class="d-flex align-items-center">
                                <div class="bg-success bg-opacity-10 rounded p-2 me-3">
                                    <i class="bi bi-cash-coin text-success fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Cash</h6>
                                    <small class="text-muted">Pay with cash at pickup</small>
                                </div>
                            </div>
                            @endif
                            
                            @if(in_array('online', $paymentMethods))
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 rounded p-2 me-3">
                                    <i class="bi bi-phone text-primary fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Online Payment</h6>
                                    <small class="text-muted">QR code / E-wallet</small>
                                </div>
                            </div>
                            @endif
                            
                            @if(in_array('card', $paymentMethods))
                            <div class="d-flex align-items-center">
                                <div class="bg-warning bg-opacity-10 rounded p-2 me-3">
                                    <i class="bi bi-credit-card text-warning fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Card at Pickup</h6>
                                    <small class="text-muted">Pay with card when collecting</small>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</section>

<!-- Menu Items -->
<section class="py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12" data-aos="fade-up">
                <h2 class="fw-bold mb-3">
                    <i class="bi bi-grid-3x3-gap text-primary me-2"></i>
                    Our Menu
                </h2>
                
                @if($categories->count() > 0)
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <button class="btn btn-outline-primary btn-sm active" data-category="all">
                        All Items
                    </button>
                    @foreach($categories as $category)
                    <button class="btn btn-outline-primary btn-sm" data-category="{{ $category->category_id }}">
                        {{ $category->category_name }}
                    </button>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        
        @if($menuItems->count() > 0)
        <div class="row g-4" id="menu-items-grid">
            @foreach($menuItems as $item)
            <div class="col-lg-3 col-md-4 col-sm-6 menu-item" data-category="{{ $item->category_id }}" data-aos="fade-up">
                <div class="card h-100 border-0 shadow-sm hover-lift">
                    <a href="{{ route('food.details', $item->item_id) }}" class="text-decoration-none">
                        @if($item->image_path && file_exists(public_path($item->image_path)))
                        <img src="{{ asset($item->image_path) }}" 
                             class="card-img-top" 
                             alt="{{ $item->name }}"
                             style="height: 200px; object-fit: cover;">
                        @else
                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                        </div>
                        @endif
                        
                        <div class="card-body">
                            <div class="mb-2">
                                <span class="badge bg-light text-dark small">{{ $item->category->category_name }}</span>
                            </div>
                            <h5 class="card-title text-dark mb-2">{{ $item->name }}</h5>
                            <p class="card-text text-muted small mb-3" style="height: 40px; overflow: hidden;">
                                {{ Str::limit($item->description, 60) }}
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="h5 text-primary fw-bold mb-0">RM {{ number_format($item->price, 2) }}</span>
                                @if($vendorSettings && $vendorSettings->accepting_orders)
                                    <span class="badge bg-success">Available</span>
                                @else
                                    <span class="badge bg-secondary">Closed</span>
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        
        <!-- Pagination -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="d-flex justify-content-center">
                    {{ $menuItems->links() }}
                </div>
            </div>
        </div>
        @else
        <div class="row">
            <div class="col-12 text-center py-5">
                <i class="bi bi-box-seam text-muted" style="font-size: 4rem;"></i>
                <p class="text-muted mt-3">No menu items available at the moment.</p>
            </div>
        </div>
        @endif
    </div>
</section>
@endsection

@push('styles')
<style>
    .hover-lift {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Category filter
    const categoryButtons = document.querySelectorAll('[data-category]');
    const menuItems = document.querySelectorAll('.menu-item');
    
    categoryButtons.forEach(button => {
        button.addEventListener('click', function() {
            const category = this.dataset.category;
            
            // Update active button
            categoryButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Filter items
            menuItems.forEach(item => {
                if (category === 'all' || item.dataset.category === category) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
});
</script>
@endpush

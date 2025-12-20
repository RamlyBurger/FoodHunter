@extends('layouts.app')

@section('title', 'Menu - FoodHunter')

@section('content')
<!-- Page Header -->
<section class="bg-light py-5">
    <div class="container">
        <div class="row">
            <div class="col-12" data-aos="fade-up">
                <h1 class="display-4 fw-bold mb-2">Our Menu</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item active">Menu</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Menu Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Filters Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="card border-0 sticky-top" style="top: 100px;" data-aos="fade-right">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">
                            <i class="bi bi-funnel me-2"></i> Filters
                        </h5>
                        
                        <form method="GET" action="{{ route('menu') }}" id="filter-form">
                            <!-- Search -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Search</label>
                                <div class="search-box">
                                    <input type="text" name="search" class="form-control" placeholder="Search for food..." value="{{ $search }}">
                                    <i class="bi bi-search search-icon"></i>
                                </div>
                            </div>
                            
                            <!-- Categories -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Categories</label>
                                <div class="form-check mb-2">
                                    <input class="form-check-input category-filter" type="radio" name="category" id="cat-all" value="" {{ !$categoryId ? 'checked' : '' }}>
                                    <label class="form-check-label" for="cat-all">
                                        All Items <span class="badge bg-light text-dark ms-2">{{ $totalItems }}</span>
                                    </label>
                                </div>
                                @foreach($categories as $category)
                                <div class="form-check mb-2">
                                    <input class="form-check-input category-filter" type="radio" name="category" id="cat-{{ $category->category_id }}" value="{{ $category->category_id }}" {{ $categoryId == $category->category_id ? 'checked' : '' }}>
                                    <label class="form-check-label" for="cat-{{ $category->category_id }}">
                                        {{ $category->category_name }} <span class="badge bg-light text-dark ms-2">{{ $category->menu_items_count }}</span>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                            
                            <!-- Price Range -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Price Range</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="number" name="min_price" class="form-control form-control-sm" placeholder="Min" step="0.01" value="{{ $minPrice }}">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" name="max_price" class="form-control form-control-sm" placeholder="Max" step="0.01" value="{{ $maxPrice }}">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Vendors -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Vendors</label>
                                <select class="form-select" name="vendor">
                                    <option value="">All Vendors</option>
                                    @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->user_id }}" {{ $vendorId == $vendor->user_id ? 'selected' : '' }}>
                                        {{ $vendor->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Filter Buttons -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary rounded-pill">
                                    <i class="bi bi-funnel me-2"></i> Apply Filters
                                </button>
                                <a href="{{ route('menu') }}" class="btn btn-outline-danger rounded-pill">
                                    <i class="bi bi-x-circle me-2"></i> Reset Filters
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Menu Items -->
            <div class="col-lg-9">
                <!-- Toolbar -->
                <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-left">
                    <div>
                        <p class="mb-0 text-muted">
                            Showing <strong>{{ $menuItems->firstItem() ?? 0 }}</strong> to <strong>{{ $menuItems->lastItem() ?? 0 }}</strong> 
                            of <strong>{{ $menuItems->total() }}</strong> items
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <form method="GET" action="{{ route('menu') }}" class="d-inline">
                            @foreach(request()->except(['sort', 'page']) as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                            <select class="form-select" name="sort" style="width: auto;" onchange="this.form.submit()">
                                <option value="popular" {{ $sortBy === 'popular' ? 'selected' : '' }}>Sort by: Popular</option>
                                <option value="price_low" {{ $sortBy === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                                <option value="price_high" {{ $sortBy === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                                <option value="newest" {{ $sortBy === 'newest' ? 'selected' : '' }}>Newest First</option>
                            </select>
                        </form>
                    </div>
                </div>
                
                <!-- Food Items Grid -->
                <div class="row g-4" id="menu-items">
                    @forelse($menuItems as $item)
                    <div class="col-lg-4 col-md-6" data-aos="fade-up">
                        <div class="card food-card">
                            <div class="position-relative">
                                @if($item->image_path && file_exists(public_path($item->image_path)))
                                    <img src="{{ asset($item->image_path) }}" class="card-img-top" alt="{{ $item->name }}">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <i class="bi bi-image fs-1 text-muted"></i>
                                    </div>
                                @endif
                                
                                <div class="quick-view">
                                    <a href="{{ route('food.details', $item->item_id) }}" class="btn btn-light btn-sm rounded-circle">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button class="btn btn-light btn-sm rounded-circle favorite-btn {{ in_array($item->item_id, $wishlistItemIds) ? 'text-danger' : '' }}" data-item-id="{{ $item->item_id }}">
                                        <i class="bi bi-heart{{ in_array($item->item_id, $wishlistItemIds) ? '-fill' : '' }}"></i>
                                    </button>
                                </div>
                                
                                @if($item->created_at->diffInDays(now()) < 7)
                                <span class="badge badge-new">New</span>
                                @endif
                            </div>
                            <div class="card-body">
                                <span class="badge bg-light text-dark mb-2">
                                    <i class="bi bi-tag me-1"></i> {{ $item->category->category_name }}
                                </span>
                                <h5 class="card-title fw-bold mb-2">{{ $item->name }}</h5>
                                <p class="card-text text-muted small mb-3">{{ Str::limit($item->description, 60) }}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="h5 text-primary fw-bold mb-0">RM {{ number_format($item->price, 2) }}</span>
                                        <p class="text-muted small mb-0">
                                            <i class="bi bi-shop me-1"></i> {{ $item->vendor->name }}
                                        </p>
                                    </div>
                                    <a href="{{ route('food.details', $item->item_id) }}" class="btn btn-primary btn-sm rounded-pill add-to-cart-btn" data-item-id="{{ $item->item_id }}" data-item-name="{{ $item->name }}">
                                        <i class="bi bi-cart-plus me-1"></i> Add
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                            <h4 class="text-muted">No menu items found</h4>
                            <p class="text-muted">Try adjusting your filters or search criteria</p>
                            <a href="{{ route('menu') }}" class="btn btn-primary rounded-pill">
                                <i class="bi bi-arrow-clockwise me-2"></i> Reset Filters
                            </a>
                        </div>
                    </div>
                    @endforelse
                </div>
                
                <!-- Pagination -->
                @if($menuItems->hasPages())
                <nav class="mt-5" data-aos="fade-up">
                    <ul class="pagination justify-content-center">
                        {{-- Previous Page Link --}}
                        @if ($menuItems->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">
                                    <i class="bi bi-chevron-left"></i>
                                </span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $menuItems->previousPageUrl() }}" rel="prev">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                        @endif

                        {{-- Page Numbers --}}
                        @php
                            $start = max($menuItems->currentPage() - 2, 1);
                            $end = min($menuItems->currentPage() + 2, $menuItems->lastPage());
                        @endphp
                        
                        @if($start > 1)
                            <li class="page-item">
                                <a class="page-link" href="{{ $menuItems->url(1) }}">1</a>
                            </li>
                            @if($start > 2)
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                        @endif

                        @for ($i = $start; $i <= $end; $i++)
                            @if ($i == $menuItems->currentPage())
                                <li class="page-item active">
                                    <span class="page-link">{{ $i }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $menuItems->url($i) }}">{{ $i }}</a>
                                </li>
                            @endif
                        @endfor

                        @if($end < $menuItems->lastPage())
                            @if($end < $menuItems->lastPage() - 1)
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                            <li class="page-item">
                                <a class="page-link" href="{{ $menuItems->url($menuItems->lastPage()) }}">{{ $menuItems->lastPage() }}</a>
                            </li>
                        @endif

                        {{-- Next Page Link --}}
                        @if ($menuItems->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $menuItems->nextPageUrl() }}" rel="next">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link">
                                    <i class="bi bi-chevron-right"></i>
                                </span>
                            </li>
                        @endif
                    </ul>
                </nav>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script id="guest-status" type="application/json">{{ auth()->guest() ? 'true' : 'false' }}</script>
<script>
    // Auto-submit form on category change
    document.querySelectorAll('.category-filter').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('filter-form').submit();
        });
    });
    
    // Auto-submit form on vendor change
    document.querySelector('select[name="vendor"]').addEventListener('change', function() {
        document.getElementById('filter-form').submit();
    });
    
    // Favorite button toggle (wishlist)
    document.querySelectorAll('.favorite-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (isGuest) {
                // Redirect to login if not authenticated
                window.location.href = '/login?redirect=' + encodeURIComponent(window.location.pathname);
                return;
            }
            
            const itemId = this.getAttribute('data-item-id');
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
    
    // Add to cart from menu page
    const isGuest = document.getElementById('guest-status').textContent === 'true';
    
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            if (isGuest) {
                // Let the link navigate to food details for guests
                return;
            }
            
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
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
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
                this.innerHTML = originalHTML;
                
                if (data.success) {
                    showNotification('success', data.message);
                    
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

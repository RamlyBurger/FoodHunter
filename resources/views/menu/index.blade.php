{{--
|==============================================================================
| Menu Browsing Page - Haerine Deepak Singh (Menu & Catalog Module)
|==============================================================================
|
| @author     Haerine Deepak Singh
| @module     Menu & Catalog Module
| @pattern    Repository Pattern (EloquentMenuItemRepository)
|
| Customer menu browsing with category/vendor filtering and search.
| Uses AJAX for dynamic filtering without page reload.
|==============================================================================
--}}

@extends('layouts.app')

@section('title', 'Menu')

@push('styles')
<style>
    .badge-new {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
    }
    .badge-popular {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
    }
    .filter-chip {
        display: inline-flex;
        align-items: center;
        padding: 8px 16px;
        border-radius: 25px;
        background: white;
        border: 2px solid var(--gray-200);
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
        color: var(--text-primary);
    }
    .filter-chip:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
    }
    .filter-chip.active {
        background: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
    }
    .filter-chip input {
        display: none;
    }
    .filter-section::-webkit-scrollbar {
        display: none;
    }
    .vendor-avatar {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid white;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .menu-card {
        transition: box-shadow 0.2s ease;
    }
    .menu-card:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.12) !important;
    }
    .menu-card .card-img-top {
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
    }
    .stock-badge {
        font-size: 0.7rem;
        padding: 3px 8px;
    }
    .sold-count {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }
</style>
@endpush

@section('content')
<div class="page-content">
    <!-- Filters Section - Modern Sleek Design -->
    <section class="filter-section" style="position: sticky; top: 56px; z-index: 1040; background: white; border-bottom: 1px solid var(--gray-200);">
        <div class="container py-3">
            <form method="GET" action="{{ url('/menu') }}" id="filter-form">
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <!-- Search -->
                    <div class="position-relative flex-shrink-0">
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search..." style="padding-left: 38px; border-radius: 25px; border: 2px solid var(--gray-200); width: 160px; height: 38px; font-size: 0.875rem;">
                        <i class="bi bi-search position-absolute" style="left: 14px; top: 50%; transform: translateY(-50%); color: var(--gray-400);"></i>
                    </div>
                    
                    <!-- Category Chips - Scrollable on mobile -->
                    <div class="d-flex flex-nowrap gap-2 overflow-auto flex-grow-1 pb-1" style="scrollbar-width: none; -ms-overflow-style: none;">
                        <label class="filter-chip {{ !request('category') ? 'active' : '' }}">
                            <input type="radio" name="category" value="" {{ !request('category') ? 'checked' : '' }}>
                            All
                        </label>
                        @foreach($categories as $category)
                        <label class="filter-chip {{ request('category') == $category->id ? 'active' : '' }}">
                            <input type="radio" name="category" value="{{ $category->id }}" {{ request('category') == $category->id ? 'checked' : '' }}>
                            {{ $category->name }}
                        </label>
                        @endforeach
                    </div>
                    
                    <!-- Dropdowns -->
                    <div class="d-flex gap-2 flex-shrink-0">
                        <select name="vendor" class="form-select" style="width: auto; border-radius: 25px; border: 2px solid var(--gray-200); height: 38px; font-size: 0.875rem; padding: 0 35px 0 12px;">
                            <option value="">All Vendors</option>
                            @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" {{ request('vendor') == $vendor->id ? 'selected' : '' }}>
                                {{ $vendor->store_name }}
                            </option>
                            @endforeach
                        </select>
                        
                        <select name="sort" class="form-select" style="width: auto; border-radius: 25px; border: 2px solid var(--gray-200); height: 38px; font-size: 0.875rem; padding: 0 35px 0 12px;">
                            <option value="popular" {{ request('sort') === 'popular' || !request('sort') ? 'selected' : '' }}>Popular</option>
                            <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Price ↑</option>
                            <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Price ↓</option>
                            <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <div class="container py-4">
        <!-- Results Info -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <p class="mb-0 text-muted" id="results-info">
                Showing <strong>{{ $items->firstItem() ?? 0 }}</strong> to <strong>{{ $items->lastItem() ?? 0 }}</strong> 
                of <strong>{{ $items->total() }}</strong> items
            </p>
        </div>
        
        <!-- Menu Items Grid -->
        <div class="row g-4" id="menu-grid">
            @forelse($items as $item)
                <x-menu-item-card :item="$item" :wishlistIds="$wishlistIds ?? []" />
            @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 100px; height: 100px; background: var(--gray-100);">
                        <i class="bi bi-search" style="font-size: 40px; color: var(--gray-400);"></i>
                    </div>
                    <h5 style="font-weight: 600;">No items found</h5>
                    <p class="text-muted">Try adjusting your filters or search term</p>
                    <a href="{{ url('/menu') }}" class="btn btn-outline-primary">Clear Filters</a>
                </div>
            </div>
            @endforelse
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4" id="pagination-container">
            {{ $items->withQueryString()->links('vendor.pagination.custom') }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// AJAX filtering with JSON response
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filter-form');
    const menuGrid = document.getElementById('menu-grid');
    const paginationContainer = document.getElementById('pagination-container');
    const resultsInfo = document.getElementById('results-info');
    
    // Debounce function
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // Render menu item card HTML
    function renderMenuCard(item) {
        const fallbackImg = `https://ui-avatars.com/api/?name=${encodeURIComponent(item.name)}&background=f3f4f6&color=9ca3af&size=300&font-size=0.33&bold=true`;
        const imageUrl = item.image || fallbackImg;
        const vendorOpen = item.vendor.is_open;
        const inWishlist = item.in_wishlist;
        const vendorLogo = item.vendor.logo || `https://ui-avatars.com/api/?name=${encodeURIComponent(item.vendor.store_name)}&background=667eea&color=fff&size=100&bold=true&rounded=true`;
        
        return `
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card menu-card h-100 border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
                    <a href="/menu/${item.id}" class="text-decoration-none">
                        <div class="position-relative">
                            <img src="${imageUrl}" class="card-img-top" alt="${item.name}" style="height: 160px; object-fit: cover;" onerror="this.src='${fallbackImg}'">
                            ${item.total_sold >= 50 ? '<span class="badge badge-popular position-absolute" style="top: 10px; left: 10px; font-size: 0.7rem;">🔥 Popular</span>' : ''}
                            ${!vendorOpen ? '<div class="position-absolute w-100 h-100 d-flex align-items-center justify-content-center" style="top: 0; left: 0; background: rgba(0,0,0,0.5);"><span class="badge bg-secondary">Store Closed</span></div>' : ''}
                        </div>
                    </a>
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-light text-dark" style="font-size: 0.7rem;">${item.category ? item.category.name : 'Uncategorized'}</span>
                            ${item.total_sold > 0 ? `<span class="sold-count"><i class="bi bi-bag-check"></i> ${item.total_sold} sold</span>` : ''}
                        </div>
                        <a href="/menu/${item.id}" class="text-decoration-none">
                            <h6 class="card-title mb-1" style="font-weight: 600; color: var(--text-primary); font-size: 0.95rem;">${item.name}</h6>
                        </a>
                        <a href="/menu/vendor/${item.vendor.id}" class="d-flex align-items-center gap-2 text-decoration-none mb-2">
                            <img src="${vendorLogo}" alt="${item.vendor.store_name}" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover; border: 2px solid white; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                            <span class="text-muted" style="font-size: 0.8rem;">${item.vendor.store_name}</span>
                        </a>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold" style="color: var(--primary-color);">RM ${item.price.toFixed(2)}</span>
                            <div class="d-flex gap-1">
                                @auth
                                <button class="btn btn-sm ${inWishlist ? 'btn-danger' : 'btn-outline-secondary'}" onclick="toggleWishlist(${item.id}, this)" title="${inWishlist ? 'Remove from wishlist' : 'Add to wishlist'}">
                                    <i class="bi bi-heart${inWishlist ? '-fill' : ''}"></i>
                                </button>
                                @endauth
                                <button class="btn btn-sm btn-primary" onclick="addToCart(${item.id})" ${!vendorOpen ? 'disabled' : ''} title="Add to cart">
                                    <i class="bi bi-cart-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    // Render pagination HTML
    function renderPagination(pagination, baseUrl) {
        if (pagination.last_page <= 1) return '';
        
        let html = '<nav><ul class="pagination justify-content-center">';
        
        // Previous
        if (pagination.current_page > 1) {
            html += `<li class="page-item"><a class="page-link" href="${baseUrl}&page=${pagination.current_page - 1}" data-page="${pagination.current_page - 1}">‹</a></li>`;
        } else {
            html += '<li class="page-item disabled"><span class="page-link">‹</span></li>';
        }
        
        // Page numbers
        for (let i = 1; i <= pagination.last_page; i++) {
            if (i === pagination.current_page) {
                html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
            } else if (i === 1 || i === pagination.last_page || Math.abs(i - pagination.current_page) <= 2) {
                html += `<li class="page-item"><a class="page-link" href="${baseUrl}&page=${i}" data-page="${i}">${i}</a></li>`;
            } else if (Math.abs(i - pagination.current_page) === 3) {
                html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }
        
        // Next
        if (pagination.current_page < pagination.last_page) {
            html += `<li class="page-item"><a class="page-link" href="${baseUrl}&page=${pagination.current_page + 1}" data-page="${pagination.current_page + 1}">›</a></li>`;
        } else {
            html += '<li class="page-item disabled"><span class="page-link">›</span></li>';
        }
        
        html += '</ul></nav>';
        return html;
    }
    
    // AJAX filter function with JSON
    function loadMenuItems(page = 1) {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        params.set('page', page);
        const targetUrl = `{{ url('/menu') }}?${params.toString()}`;
        
        menuGrid.style.opacity = '0.5';
        menuGrid.style.pointerEvents = 'none';
        
        fetch(targetUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(response => {
            if (response.success && response.data) {
                const data = response.data;
                
                // Render items
                if (data.items && data.items.length > 0) {
                    menuGrid.innerHTML = data.items.map(item => renderMenuCard(item)).join('');
                } else {
                    menuGrid.innerHTML = `
                        <div class="col-12">
                            <div class="text-center py-5">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 100px; height: 100px; background: var(--gray-100);">
                                    <i class="bi bi-search" style="font-size: 40px; color: var(--gray-400);"></i>
                                </div>
                                <h5 style="font-weight: 600;">No items found</h5>
                                <p class="text-muted">Try adjusting your filters or search term</p>
                                <a href="{{ url('/menu') }}" class="btn btn-outline-primary">Clear Filters</a>
                            </div>
                        </div>
                    `;
                }
                
                // Update pagination
                if (data.pagination) {
                    const baseUrl = `{{ url('/menu') }}?${new URLSearchParams(formData).toString()}`;
                    paginationContainer.innerHTML = renderPagination(data.pagination, baseUrl);
                    attachPaginationListeners();
                }
                
                // Update results info
                if (data.pagination) {
                    const p = data.pagination;
                    resultsInfo.innerHTML = `Showing <strong>${p.from || 0}</strong> to <strong>${p.to || 0}</strong> of <strong>${p.total}</strong> items`;
                }
                
                // Update URL
                window.history.pushState({}, '', targetUrl);
            }
            
            menuGrid.style.opacity = '1';
            menuGrid.style.pointerEvents = 'auto';
        })
        .catch(err => {
            console.error('Filter error:', err);
            menuGrid.style.opacity = '1';
            menuGrid.style.pointerEvents = 'auto';
            showToast('Error loading items', 'error');
        });
    }
    
    function attachPaginationListeners() {
        document.querySelectorAll('#pagination-container .page-link[data-page]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = this.dataset.page;
                if (page) {
                    loadMenuItems(parseInt(page));
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            });
        });
    }
    
    // Search input with debounce
    const searchInput = filterForm.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function() {
            loadMenuItems(1);
        }, 500));
    }
    
    // Category chips
    const categoryInputs = filterForm.querySelectorAll('input[name="category"]');
    categoryInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Update active state
            document.querySelectorAll('.filter-chip').forEach(chip => chip.classList.remove('active'));
            this.closest('.filter-chip').classList.add('active');
            loadMenuItems(1);
        });
    });
    
    // Vendor dropdown
    const vendorSelect = filterForm.querySelector('select[name="vendor"]');
    if (vendorSelect) {
        vendorSelect.addEventListener('change', function() {
            loadMenuItems(1);
        });
    }
    
    // Sort dropdown
    const sortSelect = filterForm.querySelector('select[name="sort"]');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            loadMenuItems(1);
        });
    }
});
</script>
@endpush

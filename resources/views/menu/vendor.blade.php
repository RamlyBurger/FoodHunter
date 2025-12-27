{{--
|==============================================================================
| Vendor Store Page (Customer View) - Haerine Deepak Singh (Menu & Catalog Module)
|==============================================================================
|
| @author     Haerine Deepak Singh
| @module     Menu & Catalog Module
|
| Customer-facing vendor store page with menu items and AJAX pagination.
| Displays vendor info, top selling items, and full menu catalog.
|==============================================================================
--}}

@extends('layouts.app')

@section('title', $vendor->store_name)

@push('styles')
<style>
    .vendor-header {
        background: #fff;
        border: 1px solid #eee;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 24px;
    }
    .vendor-logo {
        width: 80px;
        height: 80px;
        border-radius: 16px;
        object-fit: cover;
        border: 2px solid #eee;
    }
    .vendor-logo-placeholder {
        width: 80px;
        height: 80px;
        border-radius: 16px;
        background: #FF9500;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 2rem;
    }
    .vendor-name {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 4px;
    }
    .vendor-desc {
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 12px;
    }
    .vendor-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        font-size: 0.85rem;
    }
    .vendor-meta-item {
        display: flex;
        align-items: center;
        gap: 4px;
        color: #666;
    }
    .vendor-status {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .vendor-status.open { background: rgba(52,199,89,0.15); color: #34C759; }
    .vendor-status.closed { background: rgba(142,142,147,0.15); color: #8E8E93; }
    .stat-card {
        background: #fff;
        border: 1px solid #eee;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
    }
    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #333;
    }
    .stat-label {
        font-size: 0.8rem;
        color: #999;
    }
    .section-title {
        font-size: 1rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 16px;
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
            <li class="breadcrumb-item active text-dark">{{ $vendor->store_name }}</li>
        </ol>
    </nav>

    <!-- Vendor Header -->
    <div class="vendor-header">
        <div class="d-flex flex-wrap gap-4 align-items-start">
            @if($vendor->logo)
            <img src="{{ $vendor->logo }}" alt="{{ $vendor->store_name }}" class="vendor-logo">
            @else
            <div class="vendor-logo-placeholder">
                <i class="bi bi-shop"></i>
            </div>
            @endif
            
            <div class="flex-grow-1">
                <h1 class="vendor-name">{{ $vendor->store_name }}</h1>
                <p class="vendor-desc">{{ $vendor->description ?? 'Delicious food awaits!' }}</p>
                <div class="vendor-meta">
                    <span class="vendor-status {{ $vendor->is_open ? 'open' : 'closed' }}">
                        <i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i>
                        {{ $vendor->is_open ? 'Open Now' : 'Closed' }}
                    </span>
                    @if($vendor->avg_prep_time)
                    <span class="vendor-meta-item">
                        <i class="bi bi-clock"></i>
                        ~{{ $vendor->avg_prep_time }} min
                    </span>
                    @endif
                    @if($vendor->total_orders)
                    <span class="vendor-meta-item">
                        <i class="bi bi-bag-check"></i>
                        {{ $vendor->total_orders }} orders
                    </span>
                    @endif
                </div>
            </div>
            
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-value" style="color: #34C759;">{{ $vendor->total_orders ?? 0 }}</div>
                <div class="stat-label">Orders</div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-value">~{{ $vendor->avg_prep_time ?? 15 }}m</div>
                <div class="stat-label">Prep Time</div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="stat-card">
                <div class="stat-value" style="color: #FF9500;">{{ $items->total() }}</div>
                <div class="stat-label">Menu Items</div>
            </div>
        </div>
    </div>

    <!-- Top Selling Items -->
    <div class="mb-4" id="top-selling-section">
        <h6 class="section-title"><i class="bi bi-fire me-1" style="color: #FF3B30;"></i> Top Selling</h6>
        <div class="row g-4" id="top-selling-container">
            @if(isset($topItems) && $topItems->count() > 0)
                @foreach($topItems as $topItem)
                    <x-menu-item-card :item="$topItem" :wishlistIds="$wishlistIds ?? []" :showVendor="false" />
                @endforeach
            @else
                <div class="col-12"><p class="text-muted">No top selling items yet</p></div>
            @endif
        </div>
    </div>

    <!-- Menu Items -->
    <h6 class="section-title"><i class="bi bi-grid-3x3-gap me-1"></i> Menu (<span id="menu-count">{{ $items->total() }}</span> items)</h6>
    
    <div class="row g-4" id="menu-items-container">
        @forelse($items as $item)
            <x-menu-item-card :item="$item" :wishlistIds="$wishlistIds ?? []" :showVendor="false" />
        @empty
        <div class="col-12">
            <div class="text-center py-5">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 80px; height: 80px; background: var(--gray-100);">
                    <i class="bi bi-box-seam" style="font-size: 32px; color: var(--gray-400);"></i>
                </div>
                <h6 style="font-weight: 600;">No menu items available</h6>
                <p class="text-muted">This vendor hasn't added any items yet.</p>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-4" id="pagination-container">
        {{ $items->links('vendor.pagination.custom') }}
    </div>
</div>

</div>

@push('scripts')
<script>
const vendorId = {{ $vendor->id }};
let wishlistIds = @json($wishlistIds ?? []);
let currentPage = {{ $items->currentPage() }};

// Load menu items via AJAX
async function loadMenuItems(page = 1) {
    const container = document.getElementById('menu-items-container');
    const paginationContainer = document.getElementById('pagination-container');
    
    container.innerHTML = `
        <div class="col-12 text-center py-4">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="text-muted mt-2">Loading menu items...</p>
        </div>
    `;
    
    try {
        const res = await fetch(`/menu?vendor=${vendorId}&page=${page}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const response = await res.json();
        
        if (response.success) {
            const data = response.data || response;
            renderMenuItems(data.items || []);
            renderPagination(data.pagination || {});
            document.getElementById('menu-count').textContent = data.pagination?.total || 0;
            currentPage = page;
            
            // Update URL without reload
            const url = new URL(window.location);
            url.searchParams.set('page', page);
            history.pushState({}, '', url);
        }
    } catch (e) {
        console.error('Error loading menu items:', e);
        container.innerHTML = `<div class="col-12"><div class="alert alert-danger">Failed to load menu items</div></div>`;
    }
}

// Render menu items
function renderMenuItems(items) {
    const container = document.getElementById('menu-items-container');
    
    if (!items || items.length === 0) {
        container.innerHTML = `
            <div class="col-12">
                <div class="text-center py-5">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 80px; height: 80px; background: var(--gray-100);">
                        <i class="bi bi-box-seam" style="font-size: 32px; color: var(--gray-400);"></i>
                    </div>
                    <h6 style="font-weight: 600;">No menu items available</h6>
                    <p class="text-muted">This vendor hasn't added any items yet.</p>
                </div>
            </div>
        `;
        return;
    }
    
    container.innerHTML = items.map(item => {
        const isWishlisted = wishlistIds.includes(item.id);
        const fallbackImg = `https://ui-avatars.com/api/?name=${encodeURIComponent(item.name)}&background=f3f4f6&color=9ca3af&size=200`;
        const price = parseFloat(item.price).toFixed(2);
        
        return `
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card h-100 menu-card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
                    <div class="position-relative">
                        <a href="/menu/${item.id}">
                            <img src="${item.image || fallbackImg}" class="card-img-top" alt="${item.name}" 
                                 style="height: 160px; object-fit: cover;"
                                 onerror="this.src='${fallbackImg}'">
                        </a>
                        ${!item.is_available ? '<span class="badge bg-secondary position-absolute top-0 start-0 m-2">Unavailable</span>' : ''}
                        <button type="button" class="btn btn-light btn-sm position-absolute top-0 end-0 m-2 rounded-circle p-2" 
                                onclick="toggleWishlist(${item.id}, this)" style="width: 36px; height: 36px;">
                            <i class="bi bi-heart${isWishlisted ? '-fill text-danger' : ''}"></i>
                        </button>
                    </div>
                    <div class="card-body p-3">
                        <a href="/menu/${item.id}" class="text-decoration-none">
                            <h6 class="card-title mb-1 text-dark" style="font-weight: 600;">${item.name}</h6>
                        </a>
                        ${item.category ? `<small class="text-muted d-block mb-2">${item.category.name}</small>` : ''}
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <span class="fw-bold" style="color: var(--primary-color);">RM ${price}</span>
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" 
                                    onclick="addToCart(${item.id}, 1, this)" ${!item.is_available ? 'disabled' : ''}>
                                <i class="bi bi-cart-plus me-1"></i> Add
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

// Render pagination
function renderPagination(pagination) {
    const container = document.getElementById('pagination-container');
    if (!pagination || pagination.last_page <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '<nav><ul class="pagination mb-0">';
    
    // Previous
    html += `<li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="event.preventDefault(); loadMenuItems(${pagination.current_page - 1})">
            <i class="bi bi-chevron-left"></i>
        </a>
    </li>`;
    
    // Page numbers
    const start = Math.max(1, pagination.current_page - 2);
    const end = Math.min(pagination.last_page, pagination.current_page + 2);
    
    if (start > 1) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); loadMenuItems(1)">1</a></li>`;
        if (start > 2) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
    }
    
    for (let i = start; i <= end; i++) {
        html += `<li class="page-item ${i === pagination.current_page ? 'active' : ''}">
            <a class="page-link" href="#" onclick="event.preventDefault(); loadMenuItems(${i})">${i}</a>
        </li>`;
    }
    
    if (end < pagination.last_page) {
        if (end < pagination.last_page - 1) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        html += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); loadMenuItems(${pagination.last_page})">${pagination.last_page}</a></li>`;
    }
    
    // Next
    html += `<li class="page-item ${pagination.current_page === pagination.last_page ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="event.preventDefault(); loadMenuItems(${pagination.current_page + 1})">
            <i class="bi bi-chevron-right"></i>
        </a>
    </li>`;
    
    html += '</ul></nav>';
    container.innerHTML = html;
}

// Handle browser back/forward
window.addEventListener('popstate', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const page = parseInt(urlParams.get('page')) || 1;
    if (page !== currentPage) {
        loadMenuItems(page);
    }
});
</script>
@endpush
@endsection

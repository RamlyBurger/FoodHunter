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
// AJAX filtering
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
    
    // AJAX filter function
    function loadMenuItems(url = null) {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        const targetUrl = url || `{{ url('/menu') }}?${params.toString()}`;
        
        menuGrid.style.opacity = '0.5';
        menuGrid.style.pointerEvents = 'none';
        
        fetch(targetUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            const newMenuGrid = doc.getElementById('menu-grid');
            if (newMenuGrid) {
                menuGrid.innerHTML = newMenuGrid.innerHTML;
            }
            
            const newPagination = doc.getElementById('pagination-container');
            if (newPagination && paginationContainer) {
                paginationContainer.innerHTML = newPagination.innerHTML;
                attachPaginationListeners();
            }
            
            const newResultsInfo = doc.getElementById('results-info');
            if (newResultsInfo && resultsInfo) {
                resultsInfo.innerHTML = newResultsInfo.innerHTML;
            }
            
            window.history.pushState({}, '', targetUrl);
            
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
        document.querySelectorAll('#pagination-container .page-link').forEach(link => {
            if (!link.closest('.disabled')) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = this.getAttribute('href');
                    if (url) {
                        loadMenuItems(url);
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                });
            }
        });
    }
    
    attachPaginationListeners();
    
    // Search input with debounce
    const searchInput = filterForm.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function() {
            loadMenuItems();
        }, 500));
    }
    
    // Category chips
    const categoryInputs = filterForm.querySelectorAll('input[name="category"]');
    categoryInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Update active state
            document.querySelectorAll('.filter-chip').forEach(chip => chip.classList.remove('active'));
            this.closest('.filter-chip').classList.add('active');
            loadMenuItems();
        });
    });
    
    // Vendor dropdown
    const vendorSelect = filterForm.querySelector('select[name="vendor"]');
    if (vendorSelect) {
        vendorSelect.addEventListener('change', function() {
            loadMenuItems();
        });
    }
    
    // Sort dropdown
    const sortSelect = filterForm.querySelector('select[name="sort"]');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            loadMenuItems();
        });
    }
});
</script>
@endpush

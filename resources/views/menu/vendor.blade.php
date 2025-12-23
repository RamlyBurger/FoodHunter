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
    @if(isset($topItems) && $topItems->count() > 0)
    <div class="mb-4">
        <h6 class="section-title"><i class="bi bi-fire me-1" style="color: #FF3B30;"></i> Top Selling</h6>
        <div class="row g-4">
            @foreach($topItems as $topItem)
                <x-menu-item-card :item="$topItem" :wishlistIds="$wishlistIds ?? []" :showVendor="false" />
            @endforeach
        </div>
    </div>
    @endif

    <!-- Menu Items -->
    <h6 class="section-title"><i class="bi bi-grid-3x3-gap me-1"></i> Menu ({{ $items->total() }} items)</h6>
    
    <div class="row g-4">
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
    <div class="d-flex justify-content-center mt-4">
        {{ $items->links('vendor.pagination.custom') }}
    </div>
</div>

</div>
@endsection

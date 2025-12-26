@props(['item', 'wishlistIds' => [], 'showVendor' => true])

<div class="col-6 col-md-4 col-lg-3">
    <div class="card menu-card h-100 position-relative" style="cursor: pointer;">
        <a href="{{ url('/menu/' . $item->id) }}" class="card-link-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 1;"></a>
        <div class="position-relative">
            <img src="{{ \App\Helpers\ImageHelper::menuItem($item->image) }}" 
                 class="card-img-top" 
                 alt="{{ $item->name }}" 
                 style="height: 160px; object-fit: cover;"
                 onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($item->name) }}&background=f3f4f6&color=9ca3af&size=300&font-size=0.33&bold=true';">
            
            <!-- Badges -->
            @if($item->is_featured)
            <span class="badge position-absolute" style="top: 10px; left: 10px; z-index: 2; font-size: 0.65rem; background: linear-gradient(135deg, #f59e0b, #d97706); color: white;">
                <i class="bi bi-star-fill"></i> Featured
            </span>
            @elseif($item->created_at && $item->created_at->diffInDays(now()) < 7)
            <span class="badge badge-new position-absolute" style="top: 10px; left: 10px; z-index: 2; font-size: 0.65rem; background: linear-gradient(135deg, #10b981, #059669); color: white;">
                <i class="bi bi-sparkles"></i> New
            </span>
            @elseif($item->total_sold > 50)
            <span class="badge badge-popular position-absolute" style="top: 10px; left: 10px; z-index: 2; font-size: 0.65rem; background: linear-gradient(135deg, #f59e0b, #d97706); color: white;">
                <i class="bi bi-fire"></i> Popular
            </span>
            @endif
            
            <!-- Wishlist Button -->
            @auth
            <button type="button" class="btn btn-sm position-absolute wishlist-btn" 
                    style="top: 10px; right: 10px; background: rgba(255,255,255,0.95); border-radius: 50%; width: 32px; height: 32px; border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.1); z-index: 2;"
                    onclick="event.stopPropagation(); event.preventDefault(); toggleWishlist({{ $item->id }}, this)">
                <i class="bi bi-heart{{ in_array($item->id, $wishlistIds) ? '-fill text-danger' : '' }}" style="font-size: 0.85rem;"></i>
            </button>
            @endauth
            
            <!-- Availability Badge -->
            @if(!$item->is_available)
            <span class="badge bg-danger position-absolute" style="bottom: 10px; right: 10px; z-index: 2; font-size: 0.7rem; padding: 3px 8px;">
                Unavailable
            </span>
            @endif
        </div>
        
        <div class="card-body d-flex flex-column p-3">
            <!-- Category -->
            <span class="badge bg-light text-dark mb-2" style="font-size: 0.65rem; width: fit-content;">
                {{ $item->category->name ?? 'Food' }}
            </span>
            
            <!-- Title -->
            <h6 class="card-title mb-1" style="font-weight: 600; font-size: 0.95rem;">{{ Str::limit($item->name, 30) }}</h6>
            
            <!-- Vendor Info with Avatar -->
            @if($showVendor && $item->vendor)
            <div class="d-flex align-items-center gap-2 mb-2">
                <img src="{{ \App\Helpers\ImageHelper::vendorLogo($item->vendor->logo, $item->vendor->store_name) }}" alt="{{ $item->vendor->store_name }}" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover; border: 2px solid white; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <span class="text-muted" style="font-size: 0.75rem;">{{ Str::limit($item->vendor->store_name ?? 'Vendor', 18) }}</span>
            </div>
            @endif
            
            <!-- Stats Row -->
            <div class="d-flex gap-3 mb-2" style="font-size: 0.75rem; color: var(--text-secondary);">
                @if($item->total_sold > 0)
                <span><i class="bi bi-bag-check me-1"></i>{{ $item->total_sold }} sold</span>
                @endif
            </div>
            
            <!-- Price & Cart -->
            <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top" style="position: relative; z-index: 2;">
                <div>
                    @if($item->original_price && $item->original_price > $item->price)
                    <small class="text-decoration-line-through text-muted d-block" style="font-size: 0.7rem;">RM {{ number_format((float)$item->original_price, 2) }}</small>
                    @endif
                    <span class="fw-bold" style="color: var(--primary-color); font-size: 1rem;">RM {{ number_format((float)$item->price, 2) }}</span>
                </div>
                @if($item->is_available)
                    @auth
                    <button type="button" class="btn btn-sm btn-primary" style="padding: 6px 12px; border-radius: 8px;" onclick="event.stopPropagation(); event.preventDefault(); addToCart({{ $item->id }}, 1, this)">
                        <i class="bi bi-cart3"></i>
                    </button>
                    @else
                    <a href="{{ url('/login') }}" class="btn btn-sm btn-outline-primary" style="padding: 6px 12px; border-radius: 8px;" onclick="event.stopPropagation();">
                        <i class="bi bi-cart3"></i>
                    </a>
                    @endauth
                @else
                <span class="badge bg-secondary" style="font-size: 0.65rem;">Unavailable</span>
                @endif
            </div>
        </div>
    </div>
</div>

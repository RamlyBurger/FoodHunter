@extends('layouts.app')

@section('title', 'My Wishlist')

@push('styles')
<style>
    .wishlist-item-card {
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        height: 100%;
    }
    .wishlist-item-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }
    .wishlist-item-image {
        width: 100%;
        height: 180px;
        object-fit: cover;
    }
    .wishlist-item-body {
        padding: 1rem;
    }
    .wishlist-item-name {
        font-weight: 600;
        font-size: 1rem;
        margin-bottom: 0.25rem;
        color: #1c1c1e;
    }
    .wishlist-item-vendor {
        font-size: 0.85rem;
        color: #8e8e93;
        margin-bottom: 0.5rem;
    }
    .wishlist-item-price {
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--primary-color);
    }
    .wishlist-item-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
    }
    .btn-add-cart {
        flex: 1;
        background: var(--primary-color);
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 0.6rem 1rem;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.2s;
    }
    .btn-add-cart:hover {
        background: #e68600;
        color: #fff;
    }
    .btn-add-cart:disabled {
        background: #ccc;
        cursor: not-allowed;
    }
    .btn-remove-wishlist {
        background: rgba(255,59,48,0.1);
        color: #FF3B30;
        border: none;
        border-radius: 10px;
        padding: 0.6rem 0.8rem;
        transition: all 0.2s;
    }
    .btn-remove-wishlist:hover {
        background: #FF3B30;
        color: #fff;
    }
    .unavailable-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        background: rgba(0,0,0,0.7);
        color: #fff;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .store-closed-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(255,59,48,0.9);
        color: #fff;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="container" style="padding-top: 100px; padding-bottom: 50px;">
    <div class="row mb-4">
        <div class="col-lg-8">
            <h2 class="fw-bold mb-2">
                <i class="bi bi-heart-fill text-danger me-2"></i>
                My Wishlist
                <span id="wishlist-count-badge" class="badge bg-danger ms-2" style="font-size: 0.6em; vertical-align: middle;">0</span>
            </h2>
            <p class="text-muted">Your saved favorite items</p>
        </div>
        <div class="col-lg-4 text-lg-end">
            <button type="button" class="btn btn-outline-danger rounded-pill me-2" id="clear-wishlist-btn" onclick="clearWishlist()" style="display: none;">
                <i class="bi bi-trash me-1"></i> Clear All
            </button>
            <a href="{{ route('menu.index') }}" class="btn btn-outline-primary rounded-pill">
                <i class="bi bi-arrow-left me-1"></i> Continue Browsing
            </a>
        </div>
    </div>

    <!-- Wishlist Items Container -->
    <div id="wishlist-container">
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="text-muted mt-2">Loading wishlist...</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Load wishlist items
    window.loadWishlist = async function() {
        const container = document.getElementById('wishlist-container');
        
        try {
            const res = await fetch('/wishlist', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const response = await res.json();
            
            if (response.success) {
                const data = response.data || response;
                renderWishlist(data.items || []);
                updateWishlistCount(data.count || 0);
            } else {
                container.innerHTML = `<div class="alert alert-danger">Failed to load wishlist</div>`;
            }
        } catch (e) {
            console.error('Error loading wishlist:', e);
            container.innerHTML = `<div class="alert alert-danger">An error occurred</div>`;
        }
    };

    function renderWishlist(items) {
        const container = document.getElementById('wishlist-container');
        
        if (!items || items.length === 0) {
            container.innerHTML = `
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-heart display-1 text-muted"></i>
                        <h4 class="mt-3">Your wishlist is empty</h4>
                        <p class="text-muted">Save your favorite items here for later!</p>
                        <a href="{{ route('menu.index') }}" class="btn btn-primary mt-2 rounded-pill">
                            <i class="bi bi-search me-1"></i> Browse Menu
                        </a>
                    </div>
                </div>
            `;
            return;
        }

        let html = '<div class="row g-4">';
        items.forEach(item => {
            const isAvailable = item.is_available;
            const isStoreOpen = item.vendor?.is_open !== false;
            const canOrder = isAvailable && isStoreOpen;
            
            html += `
                <div class="col-md-4 col-lg-3" id="wishlist-item-${item.id}">
                    <div class="wishlist-item-card position-relative">
                        ${!isAvailable ? '<span class="unavailable-badge">Unavailable</span>' : ''}
                        ${!isStoreOpen ? '<span class="store-closed-badge">Store Closed</span>' : ''}
                        <a href="/menu/${item.menu_item_id}">
                            <img src="${item.image}" class="wishlist-item-image" alt="${escapeHtml(item.name)}" 
                                 onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(item.name)}&background=f3f4f6&color=9ca3af&size=200'">
                        </a>
                        <div class="wishlist-item-body">
                            <a href="/menu/${item.menu_item_id}" class="text-decoration-none">
                                <h6 class="wishlist-item-name">${escapeHtml(item.name)}</h6>
                            </a>
                            <p class="wishlist-item-vendor">
                                <i class="bi bi-shop me-1"></i>${item.vendor?.store_name || 'Unknown'}
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="wishlist-item-price">RM ${parseFloat(item.price).toFixed(2)}</span>
                                ${item.category ? `<span class="badge bg-light text-dark">${escapeHtml(item.category.name)}</span>` : ''}
                            </div>
                            <div class="wishlist-item-actions">
                                <button class="btn-add-cart" onclick="addToCart(${item.menu_item_id}, this)" ${!canOrder ? 'disabled' : ''}>
                                    <i class="bi bi-cart-plus me-1"></i> Add to Cart
                                </button>
                                <button class="btn-remove-wishlist" onclick="removeFromWishlist(${item.id}, this)" title="Remove">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    }

    function updateWishlistCount(count) {
        const badge = document.getElementById('wishlist-count-badge');
        const clearBtn = document.getElementById('clear-wishlist-btn');
        if (badge) {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'inline' : 'none';
        }
        if (clearBtn) {
            clearBtn.style.display = count > 0 ? 'inline-block' : 'none';
        }
        // Also update navbar wishlist count
        const navCount = document.querySelector('.wishlist-count');
        if (navCount) navCount.textContent = count;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    // Add to cart
    window.addToCart = async function(menuItemId, btn) {
        const originalContent = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        
        try {
            const res = await fetch('/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ menu_item_id: menuItemId, quantity: 1 })
            });
            const data = await res.json();
            
            if (data.success) {
                showToast(data.message || 'Added to cart!', 'success');
                // Update cart badge
                if (typeof pulseCartBadge === 'function') pulseCartBadge();
                if (typeof loadCartDropdown === 'function') loadCartDropdown();
            } else {
                showToast(data.message || 'Failed to add to cart', 'error');
            }
        } catch (e) {
            showToast('An error occurred', 'error');
        }
        
        btn.disabled = false;
        btn.innerHTML = originalContent;
    };

    // Remove from wishlist
    window.removeFromWishlist = async function(wishlistId, btn) {
        const originalContent = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        
        try {
            const res = await fetch(`/wishlist/${wishlistId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            
            if (data.success) {
                const responseData = data.data || data;
                const newCount = responseData.wishlist_count ?? 0;
                
                // Animate and remove item
                const itemEl = document.getElementById(`wishlist-item-${wishlistId}`);
                if (itemEl) {
                    itemEl.style.transition = 'opacity 0.3s, transform 0.3s';
                    itemEl.style.opacity = '0';
                    itemEl.style.transform = 'scale(0.9)';
                    setTimeout(() => {
                        itemEl.remove();
                        // Check if wishlist is now empty
                        const remaining = document.querySelectorAll('[id^="wishlist-item-"]');
                        if (remaining.length === 0) {
                            renderWishlist([]); // Show empty state directly
                        }
                    }, 300);
                }
                
                // Update count and navbar dropdown
                updateWishlistCount(newCount);
                if (typeof loadWishlistDropdown === 'function') loadWishlistDropdown();
                if (typeof pulseWishlistBadge === 'function') pulseWishlistBadge();
                
                showToast(data.message || 'Removed from wishlist', 'success');
            } else {
                showToast(data.message || 'Failed to remove', 'error');
                btn.disabled = false;
                btn.innerHTML = originalContent;
            }
        } catch (e) {
            showToast('An error occurred', 'error');
            btn.disabled = false;
            btn.innerHTML = originalContent;
        }
    };

    // Clear all wishlist items
    window.clearWishlist = async function() {
        const result = await Swal.fire({
            title: 'Clear Wishlist?',
            text: 'This will remove all items from your wishlist.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, clear all',
            cancelButtonText: 'Cancel'
        });
        
        if (!result.isConfirmed) return;
        
        const btn = document.getElementById('clear-wishlist-btn');
        const originalContent = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Clearing...';
        
        try {
            const res = await fetch('/wishlist/clear', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            
            if (data.success) {
                updateWishlistCount(0);
                loadWishlist(); // Reload to show empty state
                showToast(data.message || 'Wishlist cleared', 'success');
            } else {
                showToast(data.message || 'Failed to clear wishlist', 'error');
                btn.disabled = false;
                btn.innerHTML = originalContent;
            }
        } catch (e) {
            showToast('An error occurred', 'error');
            btn.disabled = false;
            btn.innerHTML = originalContent;
        }
    };

    // Load wishlist on page load
    loadWishlist();
});
</script>
@endpush
@endsection

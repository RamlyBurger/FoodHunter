@extends('layouts.app')

@section('title', 'Vouchers')

@push('styles')
<style>
    .vouchers-container {
        max-width: 1200px;
        margin: 0 auto;
    }
    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .stat-card {
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
    }
    .stat-card .icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-bottom: 12px;
    }
    .stat-card .value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #333;
    }
    .stat-card .label {
        font-size: 0.85rem;
        color: #666;
    }
    .filter-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }
    .filter-tab {
        padding: 8px 16px;
        border-radius: 8px;
        border: 1px solid #e0e0e0;
        background: #fff;
        color: #666;
        font-weight: 500;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }
    .filter-tab:hover {
        background: #f5f5f5;
        color: #333;
    }
    .filter-tab.active {
        background: var(--primary-color);
        color: #fff;
        border-color: var(--primary-color);
    }
    .voucher-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 20px;
    }
    .voucher-card {
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        overflow: hidden;
        position: relative;
        transition: box-shadow 0.2s;
    }
    .voucher-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .voucher-card.redeemed {
        border-color: var(--primary-color);
    }
    .voucher-card.redeemed::before {
        content: 'CLAIMED';
        position: absolute;
        top: 12px;
        right: -30px;
        background: var(--primary-color);
        color: #fff;
        padding: 4px 40px;
        font-size: 0.7rem;
        font-weight: 600;
        transform: rotate(45deg);
    }
    .voucher-header {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        padding: 20px;
        text-align: center;
        border-bottom: 1px dashed #d0d0d0;
        position: relative;
    }
    .voucher-header::before,
    .voucher-header::after {
        content: '';
        position: absolute;
        bottom: -10px;
        width: 20px;
        height: 20px;
        background: #fff;
        border-radius: 50%;
    }
    .voucher-header::before {
        left: -10px;
        box-shadow: inset -2px 0 0 #e0e0e0;
    }
    .voucher-header::after {
        right: -10px;
        box-shadow: inset 2px 0 0 #e0e0e0;
    }
    .voucher-value {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--primary-color);
        line-height: 1;
    }
    .voucher-type {
        font-size: 0.9rem;
        color: #666;
        margin-top: 4px;
    }
    .voucher-body {
        padding: 20px;
    }
    .voucher-name {
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 4px;
    }
    .voucher-vendor {
        font-size: 0.85rem;
        color: #666;
        margin-bottom: 12px;
    }
    .voucher-vendor i {
        color: var(--primary-color);
    }
    .voucher-details {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 16px;
    }
    .voucher-detail {
        font-size: 0.8rem;
        padding: 4px 10px;
        border-radius: 4px;
        background: #f5f5f5;
        color: #666;
    }
    .voucher-expiry {
        font-size: 0.8rem;
        color: #999;
        margin-bottom: 16px;
    }
    .voucher-expiry.expiring-soon {
        color: #FF3B30;
    }
    .voucher-action {
        display: flex;
        gap: 8px;
    }
    .voucher-action .btn {
        flex: 1;
        padding: 10px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
    }
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
    }
    .empty-state .icon {
        font-size: 4rem;
        color: #ddd;
        margin-bottom: 16px;
    }
</style>
@endpush

@section('content')
<div class="page-content">
<div class="container py-4">
    <div class="vouchers-container">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 style="font-weight: 700; letter-spacing: -0.5px;"><i class="bi bi-ticket-perforated me-2" style="color: var(--primary-color);"></i>Vouchers</h2>
                <p class="text-muted mb-0">Redeem vouchers and save on your orders</p>
            </div>
            <a href="{{ route('vouchers.my') }}" class="btn btn-outline-primary">
                <i class="bi bi-wallet2 me-1"></i> My Vouchers
            </a>
        </div>

        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="icon" style="background: rgba(52,199,89,0.15); color: #34C759;">
                    <i class="bi bi-ticket-perforated"></i>
                </div>
                <div class="value">{{ $userVouchers->count() }}</div>
                <div class="label">My Vouchers</div>
            </div>
            <div class="stat-card">
                <div class="icon" style="background: rgba(0,122,255,0.15); color: #007AFF;">
                    <i class="bi bi-gift"></i>
                </div>
                <div class="value">{{ $vouchers->count() }}</div>
                <div class="label">Available Vouchers</div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="filter-tabs" id="voucher-filters">
            <button type="button" class="filter-tab {{ !request('vendor') ? 'active' : '' }}" data-vendor="" onclick="filterVouchers(null, this)">
                <i class="bi bi-grid me-1"></i> All Vouchers
            </button>
            @php
                $vendors = $vouchers->pluck('vendor')->unique('id');
            @endphp
            @foreach($vendors as $vendor)
            @if($vendor)
            <button type="button" class="filter-tab {{ request('vendor') == $vendor->id ? 'active' : '' }}" data-vendor="{{ $vendor->id }}" onclick="filterVouchers({{ $vendor->id }}, this)">
                {{ $vendor->store_name }}
            </button>
            @endif
            @endforeach
        </div>

        <!-- Vouchers Grid -->
        <div id="vouchers-container">
        @if($vouchers->count() > 0)
        <div class="voucher-grid" id="voucher-grid">
            @foreach($vouchers as $voucher)
            @php
                $isRedeemed = isset($userVouchers[$voucher->id]);
                $expiresIn = $voucher->expires_at ? now()->diffInDays($voucher->expires_at, false) : null;
            @endphp
            <div class="voucher-card {{ $isRedeemed ? 'redeemed' : '' }}">
                <div class="voucher-header">
                    @if($voucher->type === 'fixed')
                    <div class="voucher-value">RM{{ number_format((float)$voucher->value, 0) }}</div>
                    <div class="voucher-type">Fixed Discount</div>
                    @else
                    <div class="voucher-value">{{ (int)$voucher->value }}%</div>
                    <div class="voucher-type">Percentage Off</div>
                    @endif
                </div>
                <div class="voucher-body">
                    <div class="voucher-name">{{ $voucher->name }}</div>
                    <div class="voucher-vendor">
                        <i class="bi bi-shop"></i> {{ $voucher->vendor->store_name ?? 'All Vendors' }}
                    </div>
                    
                    <div class="voucher-details">
                        @if($voucher->min_order)
                        <span class="voucher-detail">
                            <i class="bi bi-cart me-1"></i> Min RM{{ number_format((float)$voucher->min_order, 0) }}
                        </span>
                        @endif
                        @if($voucher->max_discount)
                        <span class="voucher-detail">
                            <i class="bi bi-tag me-1"></i> Max RM{{ number_format((float)$voucher->max_discount, 0) }}
                        </span>
                        @endif
                        <span class="voucher-detail">
                            <i class="bi bi-person me-1"></i> {{ $voucher->per_user_limit }}x use
                        </span>
                    </div>

                    @if($voucher->expires_at)
                    <div class="voucher-expiry {{ $expiresIn !== null && $expiresIn <= 3 ? 'expiring-soon' : '' }}">
                        <i class="bi bi-clock me-1"></i>
                        @if($expiresIn !== null && $expiresIn <= 0)
                        Expired
                        @elseif($expiresIn !== null && $expiresIn <= 3)
                        Expires in {{ $expiresIn }} days
                        @else
                        Valid until {{ $voucher->expires_at->format('d M Y') }}
                        @endif
                    </div>
                    @endif

                    @if($voucher->description)
                    <p class="text-muted small mb-3">{{ $voucher->description }}</p>
                    @endif

                    <div class="voucher-action">
                        @if($isRedeemed)
                        <button class="btn btn-success" disabled>
                            <i class="bi bi-check-circle me-1"></i> Claimed
                        </button>
                        <button class="btn btn-outline-secondary" onclick="copyCode('{{ $voucher->code }}')">
                            <i class="bi bi-clipboard"></i>
                        </button>
                        @else
                        <button type="button" class="btn btn-primary flex-grow-1" id="redeem-btn-{{ $voucher->id }}" onclick="confirmRedeemVoucher({{ $voucher->id }}, '{{ $voucher->name }}')">
                            <i class="bi bi-plus-circle me-1"></i> Redeem
                        </button>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="empty-state">
            <div class="icon"><i class="bi bi-ticket-perforated"></i></div>
            <h5 style="font-weight: 600;">No vouchers available</h5>
            <p class="text-muted">Check back later for new vouchers from vendors!</p>
        </div>
        @endif
        </div>

        <!-- How It Works -->
        <div class="mt-5 p-4 bg-white rounded-3 border">
            <h5 class="fw-bold mb-3"><i class="bi bi-info-circle me-2" style="color: var(--primary-color);"></i>How It Works</h5>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="d-flex gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(255,149,0,0.15); flex-shrink: 0;">
                            <span style="color: var(--primary-color); font-weight: 700;">1</span>
                        </div>
                        <div>
                            <div class="fw-semibold">Redeem Voucher</div>
                            <small class="text-muted">Click "Redeem" to add voucher to your account</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(255,149,0,0.15); flex-shrink: 0;">
                            <span style="color: var(--primary-color); font-weight: 700;">2</span>
                        </div>
                        <div>
                            <div class="fw-semibold">Add to Cart</div>
                            <small class="text-muted">Shop from the vendor offering the voucher</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(255,149,0,0.15); flex-shrink: 0;">
                            <span style="color: var(--primary-color); font-weight: 700;">3</span>
                        </div>
                        <div>
                            <div class="fw-semibold">Apply at Checkout</div>
                            <small class="text-muted">Enter voucher code to get discount</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
// csrfToken is already defined in app.blade.php layout
let currentVendorFilter = null;
let isLoading = false;

function copyCode(code) {
    navigator.clipboard.writeText(code).then(function() {
        showToast('Voucher code copied!', 'success');
    });
}

// AJAX filtering for vouchers
function filterVouchers(vendorId, btn) {
    if (isLoading) return;
    
    // Update active state
    document.querySelectorAll('.filter-tab').forEach(tab => tab.classList.remove('active'));
    btn.classList.add('active');
    
    currentVendorFilter = vendorId;
    loadVouchers(vendorId);
    
    // Update URL without reload
    const url = new URL(window.location);
    if (vendorId) {
        url.searchParams.set('vendor', vendorId);
    } else {
        url.searchParams.delete('vendor');
    }
    history.pushState({}, '', url);
}

// Load vouchers via AJAX
async function loadVouchers(vendorId = null) {
    isLoading = true;
    const container = document.getElementById('vouchers-container');
    
    // Show loading state
    container.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="text-muted mt-2">Loading vouchers...</p>
        </div>
    `;
    
    try {
        let url = '/vouchers';
        if (vendorId) url += `?vendor=${vendorId}`;
        
        const res = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const response = await res.json();
        
        if (response.success) {
            const data = response.data || response;
            renderVouchers(data.vouchers || [], data.userVouchers || {});
            updateStats(data.stats || {});
        } else {
            container.innerHTML = `<div class="alert alert-danger">Failed to load vouchers</div>`;
        }
    } catch (e) {
        console.error('Error loading vouchers:', e);
        container.innerHTML = `<div class="alert alert-danger">An error occurred</div>`;
    } finally {
        isLoading = false;
    }
}

// Render vouchers grid
function renderVouchers(vouchers, userVouchers) {
    const container = document.getElementById('vouchers-container');
    
    if (!vouchers || vouchers.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="icon"><i class="bi bi-ticket-perforated"></i></div>
                <h5 style="font-weight: 600;">No vouchers available</h5>
                <p class="text-muted">Check back later for new vouchers from vendors!</p>
            </div>
        `;
        return;
    }
    
    let html = '<div class="voucher-grid" id="voucher-grid">';
    vouchers.forEach(voucher => {
        const isRedeemed = userVouchers && userVouchers[voucher.id];
        const expiresAt = voucher.expires_at ? new Date(voucher.expires_at) : null;
        const now = new Date();
        const expiresIn = expiresAt ? Math.ceil((expiresAt - now) / (1000 * 60 * 60 * 24)) : null;
        
        html += `
            <div class="voucher-card ${isRedeemed ? 'redeemed' : ''}">
                <div class="voucher-header">
                    ${voucher.type === 'fixed' 
                        ? `<div class="voucher-value">RM${parseFloat(voucher.value).toFixed(0)}</div><div class="voucher-type">Fixed Discount</div>`
                        : `<div class="voucher-value">${parseInt(voucher.value)}%</div><div class="voucher-type">Percentage Off</div>`
                    }
                </div>
                <div class="voucher-body">
                    <div class="voucher-name">${escapeHtml(voucher.name)}</div>
                    <div class="voucher-vendor">
                        <i class="bi bi-shop"></i> ${voucher.vendor?.store_name || 'All Vendors'}
                    </div>
                    
                    <div class="voucher-details">
                        ${voucher.min_order ? `<span class="voucher-detail"><i class="bi bi-cart me-1"></i> Min RM${parseFloat(voucher.min_order).toFixed(0)}</span>` : ''}
                        ${voucher.max_discount ? `<span class="voucher-detail"><i class="bi bi-tag me-1"></i> Max RM${parseFloat(voucher.max_discount).toFixed(0)}</span>` : ''}
                        <span class="voucher-detail"><i class="bi bi-person me-1"></i> ${voucher.per_user_limit}x use</span>
                    </div>

                    ${expiresAt ? `
                        <div class="voucher-expiry ${expiresIn !== null && expiresIn <= 3 ? 'expiring-soon' : ''}">
                            <i class="bi bi-clock me-1"></i>
                            ${expiresIn !== null && expiresIn <= 0 ? 'Expired' : (expiresIn !== null && expiresIn <= 3 ? `Expires in ${expiresIn} days` : `Valid until ${expiresAt.toLocaleDateString('en-GB', {day: '2-digit', month: 'short', year: 'numeric'})}`)}
                        </div>
                    ` : ''}

                    ${voucher.description ? `<p class="text-muted small mb-3">${escapeHtml(voucher.description)}</p>` : ''}

                    <div class="voucher-action">
                        ${isRedeemed ? `
                            <button class="btn btn-success" disabled><i class="bi bi-check-circle me-1"></i> Claimed</button>
                            <button class="btn btn-outline-secondary" onclick="copyCode('${voucher.code}')"><i class="bi bi-clipboard"></i></button>
                        ` : `
                            <button type="button" class="btn btn-primary flex-grow-1" id="redeem-btn-${voucher.id}" onclick="confirmRedeemVoucher(${voucher.id}, '${escapeHtml(voucher.name)}')">
                                <i class="bi bi-plus-circle me-1"></i> Redeem
                            </button>
                        `}
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    container.innerHTML = html;
}

// Update stats cards
function updateStats(stats) {
    const statCards = document.querySelectorAll('.stat-card .value');
    if (statCards.length >= 2) {
        if (stats.myVouchersCount !== undefined) statCards[0].textContent = stats.myVouchersCount;
        if (stats.availableCount !== undefined) statCards[1].textContent = stats.availableCount;
    }
}

// Helper function to escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function confirmRedeemVoucher(voucherId, voucherName) {
    Swal.fire({
        title: 'Redeem Voucher?',
        text: `Are you sure you want to redeem "${voucherName}"?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#FF6B35',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, redeem it',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            redeemVoucher(voucherId);
        }
    });
}

function redeemVoucher(voucherId) {
    const btn = document.getElementById('redeem-btn-' + voucherId);
    const originalContent = btn.innerHTML;
    
    // Show loading state
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Redeeming...';
    
    fetch('/vouchers/' + voucherId + '/redeem', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Update the voucher card to show redeemed state
            const card = btn.closest('.voucher-card');
            if (card) {
                // Change button to "Redeemed" state
                btn.className = 'btn btn-sm btn-secondary rounded-pill px-3';
                btn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Redeemed';
                btn.disabled = true;
                btn.removeAttribute('onclick');
                
                // Add redeemed badge if not exists
                const cardHeader = card.querySelector('.card-header') || card.querySelector('.position-relative');
                if (cardHeader && !cardHeader.querySelector('.badge-redeemed')) {
                    const badge = document.createElement('span');
                    badge.className = 'badge bg-success position-absolute top-0 end-0 m-2 badge-redeemed';
                    badge.innerHTML = '<i class="bi bi-check-circle me-1"></i>Redeemed';
                    cardHeader.appendChild(badge);
                }
            }
            showToast(data.message || 'Voucher redeemed successfully!', 'success');
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Failed',
                text: data.message || 'Failed to redeem voucher.'
            });
            btn.disabled = false;
            btn.innerHTML = originalContent;
        }
    })
    .catch(err => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred. Please try again.'
        });
        btn.disabled = false;
        btn.innerHTML = originalContent;
    });
}
</script>
@endpush

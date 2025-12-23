@extends('layouts.app')

@section('title', 'My Vouchers')

@push('styles')
<style>
    .vouchers-container {
        max-width: 900px;
        margin: 0 auto;
    }
    .voucher-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .voucher-item {
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        display: flex;
        overflow: hidden;
        transition: box-shadow 0.2s;
    }
    .voucher-item:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .voucher-item.used {
        opacity: 0.6;
    }
    .voucher-item.expired {
        background: #f9f9f9;
        opacity: 0.7;
    }
    .voucher-left {
        background: linear-gradient(135deg, var(--primary-color), #e67e00);
        color: #fff;
        padding: 20px;
        min-width: 120px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        position: relative;
    }
    .voucher-left::after {
        content: '';
        position: absolute;
        right: -10px;
        top: 50%;
        transform: translateY(-50%);
        width: 20px;
        height: 20px;
        background: #fff;
        border-radius: 50%;
    }
    .voucher-left .value {
        font-size: 1.5rem;
        font-weight: 800;
        line-height: 1;
    }
    .voucher-left .type {
        font-size: 0.75rem;
        opacity: 0.9;
    }
    .voucher-right {
        flex: 1;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }
    .voucher-info {
        flex: 1;
    }
    .voucher-name {
        font-weight: 600;
        font-size: 1rem;
        color: #333;
        margin-bottom: 4px;
    }
    .voucher-vendor {
        font-size: 0.85rem;
        color: #666;
        margin-bottom: 8px;
    }
    .voucher-code {
        display: inline-block;
        background: #f5f5f5;
        padding: 4px 12px;
        border-radius: 4px;
        font-family: monospace;
        font-size: 0.9rem;
        font-weight: 600;
        color: #333;
    }
    .voucher-status {
        text-align: right;
    }
    .voucher-expiry {
        font-size: 0.8rem;
        color: #999;
        margin-bottom: 8px;
    }
    .voucher-expiry.expiring-soon {
        color: #FF3B30;
    }
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .status-badge.available {
        background: rgba(52,199,89,0.15);
        color: #34C759;
    }
    .status-badge.used {
        background: rgba(108,117,125,0.15);
        color: #6c757d;
    }
    .status-badge.expired {
        background: rgba(255,59,48,0.15);
        color: #FF3B30;
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
                <h2 style="font-weight: 700; letter-spacing: -0.5px;"><i class="bi bi-wallet2 me-2" style="color: var(--primary-color);"></i>My Vouchers</h2>
                <p class="text-muted mb-0">Your redeemed vouchers ready to use</p>
            </div>
            <a href="{{ route('vouchers.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-plus-circle me-1"></i> Get More Vouchers
            </a>
        </div>

        <!-- Voucher List -->
        @if($userVouchers->count() > 0)
        <div class="voucher-list">
            @foreach($userVouchers as $userVoucher)
            @php
                $voucher = $userVoucher->voucher;
                $isUsed = $userVoucher->usage_count >= $voucher->per_user_limit;
                $isExpired = $voucher->expires_at && now()->gt($voucher->expires_at);
                $expiresIn = $voucher->expires_at ? now()->diffInDays($voucher->expires_at, false) : null;
            @endphp
            <div class="voucher-item {{ $isUsed ? 'used' : '' }} {{ $isExpired ? 'expired' : '' }}">
                <div class="voucher-left">
                    @if($voucher->type === 'fixed')
                    <div class="value">RM{{ number_format((float)$voucher->value, 0) }}</div>
                    <div class="type">OFF</div>
                    @else
                    <div class="value">{{ (int)$voucher->value }}%</div>
                    <div class="type">OFF</div>
                    @endif
                </div>
                <div class="voucher-right">
                    <div class="voucher-info">
                        <div class="voucher-name">{{ $voucher->name }}</div>
                        <div class="voucher-vendor">
                            <i class="bi bi-shop" style="color: var(--primary-color);"></i> 
                            {{ $voucher->vendor->store_name ?? 'All Vendors' }}
                            @if($voucher->min_order)
                            <span class="ms-2 text-muted">• Min RM{{ number_format((float)$voucher->min_order, 0) }}</span>
                            @endif
                        </div>
                        <div class="voucher-code" onclick="copyCode('{{ $voucher->code }}')" style="cursor: pointer;" title="Click to copy">
                            {{ $voucher->code }} <i class="bi bi-clipboard ms-1"></i>
                        </div>
                    </div>
                    <div class="voucher-status">
                        @if($voucher->expires_at)
                        <div class="voucher-expiry {{ $expiresIn !== null && $expiresIn <= 3 && $expiresIn > 0 ? 'expiring-soon' : '' }}">
                            @if($isExpired)
                            Expired
                            @elseif($expiresIn !== null && $expiresIn <= 3)
                            {{ $expiresIn }} days left
                            @else
                            Until {{ $voucher->expires_at->format('d M') }}
                            @endif
                        </div>
                        @endif
                        @if($isExpired)
                        <span class="status-badge expired">Expired</span>
                        @elseif($isUsed)
                        <span class="status-badge used">Used</span>
                        @else
                        <span class="status-badge available">Available</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="empty-state">
            <div class="icon"><i class="bi bi-wallet2"></i></div>
            <h5 style="font-weight: 600;">No vouchers yet</h5>
            <p class="text-muted mb-4">Redeem vouchers from vendors to get discounts on your orders!</p>
            <a href="{{ route('vouchers.index') }}" class="btn btn-primary">
                <i class="bi bi-ticket-perforated me-1"></i> Browse Vouchers
            </a>
        </div>
        @endif

        <!-- Usage Instructions -->
        <div class="mt-4 p-4 bg-light rounded-3">
            <h6 class="fw-bold mb-2"><i class="bi bi-lightbulb me-1" style="color: var(--primary-color);"></i> How to use your vouchers</h6>
            <p class="text-muted small mb-0">
                Copy the voucher code and paste it at checkout to apply the discount. 
                Make sure your order meets the minimum amount requirement and is from the correct vendor.
            </p>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
function copyCode(code) {
    navigator.clipboard.writeText(code).then(function() {
        showToast('Voucher code copied! Use it at checkout.', 'success');
    });
}
</script>
@endpush

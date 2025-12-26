@extends('layouts.app')

@section('title', 'My Profile')

@push('styles')
<style>
    .profile-sidebar {
        position: sticky;
        top: 90px;
    }
    .profile-card {
        background: #fff;
        border: 1px solid #eee;
        border-radius: 12px;
        padding: 24px;
        text-align: center;
        margin-bottom: 16px;
    }
    .profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #eee;
        margin-bottom: 12px;
    }
    .profile-name {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 4px;
    }
    .profile-email {
        font-size: 0.85rem;
        color: #666;
        margin-bottom: 8px;
    }
    .profile-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        background: #f5f5f5;
        color: #666;
    }
    .profile-badge.vendor { background: rgba(255,149,0,0.15); color: #FF9500; }
    .profile-badge.admin { background: rgba(255,59,48,0.15); color: #FF3B30; }
    .vouchers-card {
        background: linear-gradient(135deg, #FF9500, #e67e00);
        border-radius: 12px;
        padding: 20px;
        color: #fff;
        margin-bottom: 16px;
    }
    .vouchers-value {
        font-size: 2rem;
        font-weight: 700;
    }
    .sidebar-stats {
        background: #fff;
        border: 1px solid #eee;
        border-radius: 12px;
        padding: 16px;
    }
    .stat-row {
        display: flex;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f5f5f5;
    }
    .stat-row:last-child {
        border-bottom: none;
    }
    .stat-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        font-size: 0.9rem;
    }
    .stat-label {
        font-size: 0.8rem;
        color: #999;
    }
    .stat-value {
        font-size: 1rem;
        font-weight: 600;
    }
    .profile-tabs {
        border-bottom: 1px solid #eee;
        margin-bottom: 24px;
    }
    .profile-tabs .nav-link {
        color: #666;
        border: none;
        padding: 12px 0;
        margin-right: 32px;
        font-weight: 500;
        font-size: 0.95rem;
        border-bottom: 2px solid transparent;
        border-radius: 0;
    }
    .profile-tabs .nav-link:hover {
        color: #333;
    }
    .profile-tabs .nav-link.active {
        color: #FF9500;
        border-bottom-color: #FF9500;
        background: transparent;
    }
    .section-card {
        background: #fff;
        border: 1px solid #eee;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 16px;
    }
    .section-title {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 20px;
        color: #333;
    }
    .form-label {
        font-size: 0.85rem;
        font-weight: 500;
        color: #666;
        margin-bottom: 6px;
    }
    .form-control {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 0.95rem;
    }
    .form-control:focus {
        border-color: #FF9500;
        box-shadow: 0 0 0 3px rgba(255,149,0,0.1);
    }
    .stat-card-mini {
        background: #f9f9f9;
        border-radius: 10px;
        padding: 16px;
        text-align: center;
    }
    .stat-card-mini .value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #333;
    }
    .stat-card-mini .label {
        font-size: 0.8rem;
        color: #999;
    }
    .order-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 0;
        border-bottom: 1px solid #f5f5f5;
    }
    .order-item:last-child {
        border-bottom: none;
    }
    .order-number {
        font-weight: 600;
        color: #333;
        margin-bottom: 4px;
    }
    .order-meta {
        font-size: 0.85rem;
        color: #999;
    }
    .order-price {
        font-weight: 600;
        color: #FF9500;
    }
    .wishlist-card {
        background: #fff;
        border: 1px solid #eee;
        border-radius: 10px;
        overflow: hidden;
        transition: box-shadow 0.2s;
    }
    .wishlist-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .wishlist-card img {
        width: 100%;
        height: 120px;
        object-fit: cover;
    }
    .wishlist-card .card-body {
        padding: 12px;
    }
    .wishlist-card .item-name {
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 4px;
    }
    .wishlist-card .item-vendor {
        font-size: 0.8rem;
        color: #999;
        margin-bottom: 8px;
    }
    .wishlist-card .item-price {
        font-weight: 600;
        color: #FF9500;
    }
    .setting-item {
        padding: 16px 0;
        border-bottom: 1px solid #f5f5f5;
    }
    .setting-item:last-child {
        border-bottom: none;
    }
    .setting-label {
        font-weight: 500;
        margin-bottom: 4px;
    }
    .setting-desc {
        font-size: 0.85rem;
        color: #999;
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
            <li class="breadcrumb-item active text-dark">My Profile</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="profile-sidebar">
                <!-- Profile Card -->
                <div class="profile-card">
                    <div class="position-relative d-inline-block">
                        <img src="{{ \App\Helpers\ImageHelper::avatar($user->avatar, $user->name, $user->updated_at) }}" alt="{{ $user->name }}" class="profile-avatar" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=6c757d&color=fff&size=200'">
                        <button type="button" class="btn btn-sm position-absolute" style="bottom: 0; right: 0; width: 28px; height: 28px; padding: 0; background: #FF9500; color: #fff; border-radius: 50%; border: 2px solid #fff;" data-bs-toggle="modal" data-bs-target="#avatarModal">
                            <i class="bi bi-camera" style="font-size: 0.7rem;"></i>
                        </button>
                    </div>
                    <h6 class="profile-name">{{ $user->name }}</h6>
                    <p class="profile-email">{{ $user->email }}</p>
                    <span class="profile-badge {{ $user->role }}">{{ ucfirst($user->role) }}</span>
                    <p class="text-muted mt-2 mb-0" style="font-size: 0.8rem;">
                        Member since {{ $user->created_at->format('M Y') }}
                    </p>
                </div>

                <!-- Vouchers Card -->
                <div class="vouchers-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span style="font-size: 0.85rem;">My Vouchers</span>
                        <i class="bi bi-ticket-perforated"></i>
                    </div>
                    <div class="vouchers-value">{{ $user->vouchers()->count() }}</div>
                    <a href="{{ url('/vouchers') }}" class="btn btn-light btn-sm w-100 mt-2" style="font-weight: 500;">
                        Browse Vouchers
                    </a>
                </div>

                <!-- Stats -->
                @php
                    $wishlistCount = \App\Models\Wishlist::where('user_id', $user->id)->count();
                @endphp
                <div class="sidebar-stats">
                    <div class="stat-row">
                        <div class="stat-icon" style="background: rgba(255,149,0,0.1); color: #FF9500;">
                            <i class="bi bi-bookmark-fill"></i>
                        </div>
                        <div>
                            <div class="stat-label">Wishlist</div>
                            <div class="stat-value">{{ $wishlistCount }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <!-- Tabs -->
            <ul class="nav profile-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#profile-info">Profile</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#order-history">Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#settings-tab">Settings</a>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Profile Info Tab -->
                <div class="tab-pane fade show active" id="profile-info">
                    <div class="section-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="section-title mb-0">Personal Information</h6>
                            <button class="btn btn-sm btn-outline-secondary" id="edit-btn">
                                <i class="bi bi-pencil me-1"></i> Edit
                            </button>
                        </div>
                        
                        <form action="{{ url('/profile') }}" method="POST" id="profile-form">
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="name" class="form-control" value="{{ $user->name }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" value="{{ $user->email }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" name="phone" class="form-control" value="{{ $user->phone ?? 'Not set' }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Role</label>
                                    <input type="text" class="form-control" value="{{ ucfirst($user->role) }}" readonly>
                                </div>
                            </div>
                            
                            <div class="mt-3 d-none" id="save-buttons">
                                <button type="submit" class="btn btn-sm" style="background: #FF9500; color: #fff;">
                                    Save Changes
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary ms-2" id="cancel-btn">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Statistics -->
                    @php
                        $totalOrders = \App\Models\Order::where('user_id', $user->id)->count();
                        $totalSpent = (float) \App\Models\Order::where('user_id', $user->id)->where('status', 'completed')->sum('total');
                        $wishlistTotal = \App\Models\Wishlist::where('user_id', $user->id)->count();
                    @endphp
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="stat-card-mini">
                                <div class="value">{{ $totalOrders }}</div>
                                <div class="label">Total Orders</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card-mini">
                                <div class="value" style="color: #34C759;">RM {{ number_format($totalSpent, 0) }}</div>
                                <div class="label">Total Spent</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card-mini">
                                <div class="value" style="color: #FF9500;">{{ $wishlistTotal }}</div>
                                <div class="label">Wishlist</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order History Tab -->
                <div class="tab-pane fade" id="order-history">
                    <div class="section-card">
                        <h6 class="section-title">Recent Orders</h6>
                        @php
                            $recentOrders = \App\Models\Order::where('user_id', $user->id)
                                ->with(['items.menuItem', 'vendor'])
                                ->orderBy('created_at', 'desc')
                                ->take(5)
                                ->get();
                        @endphp
                        @forelse($recentOrders as $order)
                        <div class="order-item">
                            <div>
                                <div class="order-number">{{ $order->order_number }}</div>
                                <div class="order-meta">
                                    {{ $order->created_at->format('M d, Y') }} · {{ $order->items->count() }} item(s) · 
                                    <span style="color: {{ $order->status === 'completed' ? '#34C759' : ($order->status === 'cancelled' ? '#FF3B30' : '#999') }};">{{ ucfirst($order->status) }}</span>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="order-price mb-1">RM {{ number_format((float)$order->total, 2) }}</div>
                                <a href="{{ url('/orders/' . $order->id) }}" class="btn btn-sm btn-outline-secondary">View</a>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-bag" style="font-size: 2rem;"></i>
                            <p class="mb-0 mt-2">No orders yet</p>
                        </div>
                        @endforelse
                        @if($recentOrders->count() > 0)
                        <div class="text-center mt-3">
                            <a href="{{ url('/orders') }}" class="btn btn-sm" style="background: #FF9500; color: #fff;">View All Orders</a>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Settings Tab -->
                <div class="tab-pane fade" id="settings-tab">
                    <div class="section-card">
                        <h6 class="section-title">Account Settings</h6>
                        
                        <!-- Email Change -->
                        <div class="setting-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="setting-label">Email Address</div>
                                    <div class="setting-desc">{{ $user->email }}</div>
                                    @if($user->pending_email)
                                    <small style="color: #FF9500;"><i class="bi bi-clock me-1"></i>Pending: {{ $user->pending_email }}</small>
                                    @endif
                                </div>
                                <a href="{{ route('auth.change-email') }}" class="btn btn-sm btn-outline-secondary">
                                    Change
                                </a>
                            </div>
                        </div>
                        
                        <!-- Password Change -->
                        <div class="setting-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="setting-label">Password</div>
                                    <div class="setting-desc">••••••••</div>
                                    @if($user->google_id)
                                    <small class="text-muted"><i class="bi bi-google"></i> Google Account - No password needed</small>
                                    @endif
                                </div>
                                @if(!$user->google_id)
                                <a href="{{ route('auth.change-password') }}" class="btn btn-sm btn-outline-secondary">
                                    Update
                                </a>
                                @else
                                <button class="btn btn-sm btn-outline-secondary" disabled title="Google accounts don't use passwords">
                                    N/A
                                </button>
                                @endif  
                            </div>
                        </div>
                        
                        <!-- Notification Preferences -->
                        <div class="setting-item">
                            <div class="setting-label mb-3">Notifications</div>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="notif1" checked style="cursor: pointer;">
                                <label class="form-check-label" for="notif1" style="font-size: 0.9rem;">Order status updates</label>
                            </div>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="notif2" checked style="cursor: pointer;">
                                <label class="form-check-label" for="notif2" style="font-size: 0.9rem;">Promotional offers</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="notif3" style="cursor: pointer;">
                                <label class="form-check-label" for="notif3" style="font-size: 0.9rem;">New menu items</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Danger Zone -->
                    <div class="section-card" style="border-color: rgba(255,59,48,0.3);">
                        <h6 class="section-title" style="color: #FF3B30;">Danger Zone</h6>
                        <p style="font-size: 0.85rem; color: #999; margin-bottom: 16px;">Once you delete your account, there is no going back.</p>
                        <button class="btn btn-sm btn-outline-danger" onclick="confirmDeleteAccount()">
                            Delete Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Avatar Upload Modal -->
<div class="modal fade" id="avatarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-camera"></i> Change Profile Picture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('profile.avatar') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <img src="{{ \App\Helpers\ImageHelper::avatar($user->avatar, $user->name, $user->updated_at) }}" alt="Current" class="rounded-circle mb-2" style="width: 150px; height: 150px; object-fit: cover;" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=6c757d&color=fff&size=200'">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Choose New Picture</label>
                        <input type="file" name="avatar" id="avatarInput" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp" required>
                        <small class="text-muted">Max 2MB. Supported: JPEG, PNG, GIF, WebP</small>
                        <div id="avatarError" class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    @if($user->avatar)
                    <a href="#" class="btn btn-outline-danger me-auto" onclick="confirmRemoveAvatar(event)">
                        <i class="bi bi-trash"></i> Remove
                    </a>
                    @endif
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="avatarSubmit">
                        <i class="bi bi-upload"></i> Upload
                    </button>
                </div>
            </form>
            <script>
                document.getElementById('avatarInput').addEventListener('change', function() {
                    const file = this.files[0];
                    const maxSize = 2 * 1024 * 1024; // 2MB
                    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    const errorDiv = document.getElementById('avatarError');
                    const submitBtn = document.getElementById('avatarSubmit');
                    
                    if (file) {
                        if (file.size > maxSize) {
                            this.classList.add('is-invalid');
                            errorDiv.textContent = 'File is too large! Maximum size is 2MB. Your file is ' + (file.size / 1024 / 1024).toFixed(2) + 'MB.';
                            errorDiv.style.display = 'block';
                            submitBtn.disabled = true;
                            showToast('File is too large! Maximum size is 2MB.', 'error');
                            return;
                        }
                        if (!allowedTypes.includes(file.type)) {
                            this.classList.add('is-invalid');
                            errorDiv.textContent = 'Invalid file type! Only JPEG, PNG, GIF, and WebP are allowed.';
                            errorDiv.style.display = 'block';
                            submitBtn.disabled = true;
                            showToast('Invalid file type!', 'error');
                            return;
                        }
                        this.classList.remove('is-invalid');
                        errorDiv.style.display = 'none';
                        submitBtn.disabled = false;
                    }
                });
            </script>
        </div>
    </div>
</div>

<!-- Password Change Modal -->
<div class="modal fade" id="passwordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-weight: 700;"><i class="bi bi-key me-2"></i>Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ url('/profile/password') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 600;">Current Password</label>
                        <div class="input-group">
                            <input type="password" name="current_password" id="current_password" class="form-control" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePwd('current_password', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 600;">New Password</label>
                        <div class="input-group">
                            <input type="password" name="password" id="new_password" class="form-control" required minlength="8">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePwd('new_password', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength mt-2">
                            <div class="progress" style="height: 5px;">
                                <div id="pwdStrengthBar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                            </div>
                            <small id="pwdStrengthText" class="text-muted">Minimum 8 characters</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 600;">Confirm New Password</label>
                        <div class="input-group">
                            <input type="password" name="password_confirmation" id="confirm_new_password" class="form-control" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePwd('confirm_new_password', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <small id="pwdMatchText" class="text-muted"></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Email Modal -->
<div class="modal fade" id="changeEmailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-envelope-at"></i> Change Email Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('profile.change-email') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="text-muted">A verification code will be sent to your new email address.</p>
                    
                    <div class="mb-3">
                        <label class="form-label">Current Email</label>
                        <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">New Email Address</label>
                        <input type="email" name="new_email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password" class="form-control" required>
                        <small class="text-muted">Enter your current password to confirm</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Send Verification Code
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Edit profile functionality
document.getElementById('edit-btn')?.addEventListener('click', function(e) {
    e.preventDefault();
    const inputs = document.querySelectorAll('#profile-form input[name]');
    inputs.forEach(input => {
        if (input.name !== '' && input.type !== 'hidden') {
            input.removeAttribute('readonly');
        }
    });
    document.getElementById('save-buttons').classList.remove('d-none');
    this.classList.add('d-none');
});

document.getElementById('cancel-btn')?.addEventListener('click', function() {
    const inputs = document.querySelectorAll('#profile-form input[name]');
    inputs.forEach(input => {
        if (input.name !== '' && input.type !== 'hidden') {
            input.setAttribute('readonly', true);
        }
    });
    document.getElementById('save-buttons').classList.add('d-none');
    document.getElementById('edit-btn').classList.remove('d-none');
    document.getElementById('profile-form').reset();
});

function togglePwd(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}

function checkPwdStrength(password) {
    let strength = 0;
    let feedback = [];
    
    if (password.length >= 8) strength += 25;
    else feedback.push('8+ chars');
    
    if (/[a-z]/.test(password)) strength += 25;
    else feedback.push('lowercase');
    
    if (/[A-Z]/.test(password)) strength += 25;
    else feedback.push('uppercase');
    
    if (/[0-9]/.test(password)) strength += 15;
    else feedback.push('number');
    
    if (/[^A-Za-z0-9]/.test(password)) strength += 10;
    else feedback.push('symbol');
    
    return { strength, feedback };
}

document.addEventListener('DOMContentLoaded', function() {
    const newPwd = document.getElementById('new_password');
    const confirmPwd = document.getElementById('confirm_new_password');
    const strengthBar = document.getElementById('pwdStrengthBar');
    const strengthText = document.getElementById('pwdStrengthText');
    const matchText = document.getElementById('pwdMatchText');
    
    if (newPwd) {
        newPwd.addEventListener('input', function() {
            const result = checkPwdStrength(this.value);
            strengthBar.style.width = result.strength + '%';
            
            if (result.strength < 25) {
                strengthBar.className = 'progress-bar bg-danger';
                strengthText.innerHTML = '<span class="text-danger">Very Weak</span>';
            } else if (result.strength < 50) {
                strengthBar.className = 'progress-bar bg-warning';
                strengthText.innerHTML = '<span class="text-warning">Weak</span> - Need: ' + result.feedback.join(', ');
            } else if (result.strength < 75) {
                strengthBar.className = 'progress-bar bg-info';
                strengthText.innerHTML = '<span class="text-info">Fair</span>';
            } else if (result.strength < 100) {
                strengthBar.className = 'progress-bar bg-primary';
                strengthText.innerHTML = '<span class="text-primary">Good</span>';
            } else {
                strengthBar.className = 'progress-bar bg-success';
                strengthText.innerHTML = '<span class="text-success">Strong</span>';
            }
            checkPwdMatch();
        });
    }
    
    if (confirmPwd) {
        confirmPwd.addEventListener('input', checkPwdMatch);
    }
    
    function checkPwdMatch() {
        if (!confirmPwd || confirmPwd.value === '') {
            matchText.innerHTML = '';
            return;
        }
        if (newPwd.value === confirmPwd.value) {
            matchText.innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> Passwords match</span>';
        } else {
            matchText.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle"></i> Passwords do not match</span>';
        }
    }
});

function confirmDeleteAccount() {
    Swal.fire({
        title: 'Delete Account?',
        text: 'Are you sure you want to delete your account? This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#FF3B30',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            showToast('Account deletion is disabled in demo mode', 'info');
        }
    });
}

function confirmRemoveAvatar(event) {
    event.preventDefault();
    Swal.fire({
        title: 'Remove Profile Picture?',
        text: 'Your profile picture will be removed.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#FF3B30',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, remove it',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            removeAvatar();
        }
    });
}

function removeAvatar() {
    fetch('{{ route("profile.avatar.remove") }}', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Update avatar image to default
            const defaultAvatar = 'https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=6c757d&color=fff&size=200';
            document.querySelectorAll('.profile-avatar').forEach(img => img.src = defaultAvatar);
            // Also update navbar avatar if exists
            document.querySelectorAll('.navbar-avatar').forEach(img => img.src = defaultAvatar);
            showToast(data.message || 'Avatar removed successfully', 'success');
        } else {
            showToast(data.message || 'Failed to remove avatar', 'error');
        }
    })
    .catch(err => {
        showToast('An error occurred', 'error');
    });
}

// Profile form AJAX submission
document.getElementById('profile-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
    
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            // Reset form state
            document.querySelectorAll('#profile-form input[name]').forEach(input => {
                if (input.name !== '' && input.type !== 'hidden') {
                    input.setAttribute('readonly', true);
                }
            });
            document.getElementById('save-buttons').classList.add('d-none');
            document.getElementById('edit-btn').classList.remove('d-none');
            
            // Update displayed name
            const nameInputs = document.querySelectorAll('.profile-name');
            nameInputs.forEach(el => el.textContent = data.user.name);
        } else {
            showToast(data.message || 'Failed to update profile', 'error');
        }
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    })
    .catch(err => {
        showToast('An error occurred', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    });
});

// Avatar form AJAX submission
document.querySelector('#avatarModal form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const submitBtn = document.getElementById('avatarSubmit');
    const originalBtnText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Uploading...';
    
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Update avatar images with new URL
            const newAvatarUrl = data.data?.avatar_url || data.avatar_url;
            if (newAvatarUrl) {
                document.querySelectorAll('.profile-avatar').forEach(img => img.src = newAvatarUrl);
                document.querySelectorAll('.navbar-avatar').forEach(img => img.src = newAvatarUrl);
            }
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('avatarModal'));
            if (modal) modal.hide();
            showToast(data.message || 'Avatar updated successfully', 'success');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        } else {
            showToast(data.message || 'Failed to upload avatar', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    })
    .catch(err => {
        showToast('An error occurred', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    });
});

// Load user stats using Student 1's API
function loadUserStats() {
    fetch('/api/auth/user-stats', {
        headers: {
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + (document.querySelector('meta[name="api-token"]')?.content || '')
        }
    })
    .then(res => res.json())
    .then(response => {
        const data = response.data || response;
        if (data.total_orders !== undefined) {
            // Update stats display
            const statsContainer = document.querySelector('.stat-card-mini .value');
            if (statsContainer) {
                document.querySelectorAll('.stat-card-mini').forEach((card, index) => {
                    const valueEl = card.querySelector('.value');
                    if (index === 0 && valueEl) valueEl.textContent = data.total_orders;
                    if (index === 1 && valueEl) valueEl.textContent = 'RM ' + data.total_spent.toFixed(0);
                });
            }
        }
    })
    .catch(err => console.log('Stats API not available'));
}

// Load stats on page load
document.addEventListener('DOMContentLoaded', loadUserStats);
</script>
@endpush
</div>
@endsection

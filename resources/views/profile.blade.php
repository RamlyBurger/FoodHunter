@extends('layouts.app')

@section('title', 'My Profile - FoodHunter')

@section('content')
<!-- Page Header -->
<section class="bg-light py-4">
    <div class="container">
        <h1 class="display-5 fw-bold">My Profile</h1>
    </div>
</section>

<!-- Profile Content -->
<section class="py-5">
    <div class="container">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        
        <div class="row g-4">
            <!-- Sidebar -->
            <div class="col-lg-3">
                <div class="card border-0 text-center" data-aos="fade-right">
                    <div class="card-body p-4">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&size=120&background=6366f1&color=fff" 
                             class="rounded-circle mb-3" width="120" alt="Profile">
                        <h5 class="fw-bold mb-1">{{ auth()->user()->name }}</h5>
                        <p class="text-muted mb-3">{{ ucfirst(auth()->user()->role) }}</p>
                        <span class="badge bg-primary mb-3">Member since {{ auth()->user()->created_at->format('M Y') }}</span>
                    </div>
                </div>
                
                <!-- Loyalty Card (Customer Only) -->
                @if(auth()->user()->role !== 'vendor')
                <div class="loyalty-card mt-4" data-aos="fade-right" data-aos-delay="100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">Loyalty Points</h6>
                        <i class="bi bi-star-fill fs-4"></i>
                    </div>
                    <div class="points-display mb-2">{{ number_format($currentPoints) }}</div>
                    <small class="d-block mb-3">Redeem for rewards!</small>
                    <a href="{{ route('rewards') }}" class="btn btn-warning btn-sm rounded-pill w-100">
                        View Rewards
                    </a>
                </div>
                @endif
            </div>
            
            <!-- Main Content -->
            <div class="col-lg-9">
                <!-- Tabs -->
                <ul class="nav nav-pills mb-4" data-aos="fade-left">
                    <li class="nav-item">
                        <a class="nav-link active rounded-pill" data-bs-toggle="pill" href="#profile-info">
                            <i class="bi bi-person me-2"></i> Profile Info
                        </a>
                    </li>
                    @if(auth()->user()->role !== 'vendor')
                    <li class="nav-item">
                        <a class="nav-link rounded-pill" data-bs-toggle="pill" href="#order-history">
                            <i class="bi bi-receipt me-2"></i> Order History
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill" data-bs-toggle="pill" href="#favorites">
                            <i class="bi bi-heart me-2"></i> Favorites
                        </a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link rounded-pill" data-bs-toggle="pill" href="#settings">
                            <i class="bi bi-gear me-2"></i> Settings
                        </a>
                    </li>
                </ul>
                
                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Profile Info Tab -->
                    <div class="tab-pane fade show active" id="profile-info">
                        <div class="card border-0" data-aos="fade-up">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="fw-bold mb-0">Personal Information</h5>
                                    <button class="btn btn-primary rounded-pill" id="edit-btn">
                                        <i class="bi bi-pencil me-2"></i> Edit Profile
                                    </button>
                                </div>
                                
                                <form action="{{ route('profile.update') }}" method="POST" id="profile-form">
                                    @csrf
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Full Name</label>
                                            <input type="text" name="name" class="form-control" value="{{ auth()->user()->name }}" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Email</label>
                                            <input type="email" name="email" class="form-control" value="{{ auth()->user()->email }}" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Phone</label>
                                            <input type="tel" name="phone" class="form-control" value="{{ auth()->user()->phone ?? 'Not set' }}" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Role</label>
                                            <input type="text" class="form-control" value="{{ ucfirst(auth()->user()->role) }}" readonly>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4 d-none" id="save-buttons">
                                        <button type="submit" class="btn btn-success rounded-pill me-2">
                                            <i class="bi bi-check-lg me-2"></i> Save Changes
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary rounded-pill" id="cancel-btn">
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Statistics -->
                        <div class="row g-4 mt-3">
                            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                                <div class="stat-card">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon primary me-3">
                                            <i class="bi bi-receipt"></i>
                                        </div>
                                        <div>
                                            <h3 class="fw-bold mb-0">{{ $totalOrders }}</h3>
                                            <small class="text-muted">Total Orders</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                                <div class="stat-card">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon success me-3">
                                            <i class="bi bi-cash-stack"></i>
                                        </div>
                                        <div>
                                            <h3 class="fw-bold mb-0">RM {{ number_format($totalSpent, 0) }}</h3>
                                            <small class="text-muted">Total Spent</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                                <div class="stat-card">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon warning me-3">
                                            <i class="bi bi-heart"></i>
                                        </div>
                                        <div>
                                            <h3 class="fw-bold mb-0">{{ $totalFavorites }}</h3>
                                            <small class="text-muted">Favorites</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order History Tab -->
                    <div class="tab-pane fade" id="order-history">
                        <div class="card border-0">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-4">Recent Orders</h5>
                                @forelse($recentOrders as $order)
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1">Order #{{ $order->order_id }}</h6>
                                        <small class="text-muted">{{ $order->created_at->format('M d, Y') }} • {{ $order->orderItems->count() }} item(s) • {{ ucfirst($order->status) }}</small>
                                    </div>
                                    <div class="text-end">
                                        <p class="fw-bold mb-1">RM {{ number_format($order->total_price, 2) }}</p>
                                        <a href="{{ route('order.details', $order->order_id) }}" class="btn btn-sm btn-outline-primary rounded-pill">View</a>
                                    </div>
                                </div>
                                @empty
                                <p class="text-muted text-center py-4">No orders yet</p>
                                @endforelse
                                <div class="text-center mt-3">
                                    <a href="{{ route('orders') }}" class="btn btn-primary rounded-pill">View All Orders</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Favorites Tab -->
                    <div class="tab-pane fade" id="favorites">
                        <div class="row g-4">
                            @forelse($favorites as $wishlist)
                            @if($wishlist->menuItem)
                            <div class="col-md-6 col-lg-4">
                                <div class="card food-card">
                                    @if($wishlist->menuItem->image_path && file_exists(public_path($wishlist->menuItem->image_path)))
                                        <img src="{{ asset($wishlist->menuItem->image_path) }}" 
                                             class="card-img-top" alt="{{ $wishlist->menuItem->name }}">
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                            <i class="bi bi-image fs-1 text-muted"></i>
                                        </div>
                                    @endif
                                    <div class="card-body">
                                        <h6 class="fw-bold">{{ $wishlist->menuItem->name }}</h6>
                                        <small class="text-muted d-block mb-2">
                                            <i class="bi bi-shop me-1"></i>{{ $wishlist->menuItem->vendor->name ?? 'Vendor' }}
                                        </small>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="price-tag">RM {{ number_format($wishlist->menuItem->price, 2) }}</span>
                                            <a href="{{ route('food.details', $wishlist->menuItem->item_id) }}" class="btn btn-sm btn-primary rounded-pill">View</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @empty
                            <div class="col-12">
                                <p class="text-muted text-center py-5">No favorite items yet. Start adding items to your wishlist!</p>
                                <div class="text-center">
                                    <a href="{{ route('menu') }}" class="btn btn-primary rounded-pill">Browse Menu</a>
                                </div>
                            </div>
                            @endforelse
                        </div>
                    </div>
                    
                    <!-- Settings Tab -->
                    <div class="tab-pane fade" id="settings">
                        <div class="card border-0">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-4">Account Settings</h5>
                                
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3">Notifications</h6>
                                    <form action="{{ route('profile.notifications') }}" method="POST">
                                        @csrf
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="notif1" name="order_updates" checked>
                                            <label class="form-check-label" for="notif1">
                                                Order status updates
                                            </label>
                                        </div>
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="notif2" name="promotions" checked>
                                            <label class="form-check-label" for="notif2">
                                                Promotional offers
                                            </label>
                                        </div>
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="notif3" name="new_menu">
                                            <label class="form-check-label" for="notif3">
                                                New menu items
                                            </label>
                                        </div>
                                        <button type="submit" class="btn btn-primary rounded-pill btn-sm">Save Preferences</button>
                                    </form>
                                </div>
                                
                                <hr>
                                
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3">Change Password</h6>
                                    <button class="btn btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#passwordModal">
                                        <i class="bi bi-key me-2"></i> Update Password
                                    </button>
                                </div>
                                
                                <hr>
                                
                                <div>
                                    <h6 class="fw-bold mb-3 text-danger">Danger Zone</h6>
                                    <p class="small text-muted">Once you delete your account, there is no going back. Please be certain.</p>
                                    <button class="btn btn-outline-danger rounded-pill" onclick="return confirm('Are you sure you want to delete your account? This action cannot be undone.')">
                                        <i class="bi bi-trash me-2"></i> Delete Account
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Password Change Modal -->
<div class="modal fade" id="passwordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('profile.password') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">New Password</label>
                        <input type="password" name="new_password" class="form-control" minlength="8" required>
                        <small class="text-muted">Minimum 8 characters</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Edit profile
    document.getElementById('edit-btn')?.addEventListener('click', function(e) {
        e.preventDefault();
        const inputs = document.querySelectorAll('#profile-form input[name]');
        inputs.forEach(input => {
            if (input.name !== '') {
                input.removeAttribute('readonly');
            }
        });
        document.getElementById('save-buttons').classList.remove('d-none');
        this.classList.add('d-none');
    });
    
    document.getElementById('cancel-btn')?.addEventListener('click', function() {
        const inputs = document.querySelectorAll('#profile-form input[name]');
        inputs.forEach(input => {
            if (input.name !== '') {
                input.setAttribute('readonly', true);
            }
        });
        document.getElementById('save-buttons').classList.add('d-none');
        document.getElementById('edit-btn').classList.remove('d-none');
        // Reset form
        document.getElementById('profile-form').reset();
        window.location.reload();
    });
</script>
@endpush
@endsection

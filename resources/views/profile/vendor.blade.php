@extends('layouts.app')

@section('title', 'Vendor Profile - ' . $vendor->store_name)

@section('content')
<style>
    .profile-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 2.5rem 2rem;
        border-radius: 20px;
        color: white;
        position: relative;
        overflow: hidden;
        margin-bottom: 2rem;
    }
    .profile-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 60%;
        height: 200%;
        background: rgba(255,255,255,0.1);
        transform: rotate(30deg);
    }
    .avatar-wrapper {
        position: relative;
        width: 100px;
        height: 100px;
    }
    .avatar-img {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid white;
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    }
    .avatar-upload {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 32px;
        height: 32px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        transition: transform 0.2s;
    }
    .avatar-upload:hover { transform: scale(1.1); }
    .section-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        border: 1px solid #f0f0f0;
        margin-bottom: 1.5rem;
    }
    .section-title {
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .form-label {
        font-weight: 600;
        font-size: 0.85rem;
        margin-bottom: 0.5rem;
    }
    .form-control, .form-select {
        border-radius: 10px;
        padding: 0.75rem 1rem;
        border: 1px solid #e0e0e0;
    }
    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102,126,234,0.15);
    }
    .btn-save {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        padding: 0.75rem 2rem;
        border-radius: 10px;
        font-weight: 600;
        color: white;
        transition: all 0.2s;
    }
    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(102,126,234,0.4);
        color: white;
    }
    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: #666; font-size: 0.9rem; }
    .info-value { font-weight: 600; font-size: 0.9rem; }
    .status-badge {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .status-open { background: rgba(52,199,89,0.15); color: #34C759; }
    .status-closed { background: rgba(255,59,48,0.15); color: #FF3B30; }
    .toggle-switch {
        position: relative;
        width: 56px;
        height: 28px;
        cursor: pointer;
    }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: #ccc;
        border-radius: 28px;
        transition: 0.3s;
    }
    .toggle-slider::before {
        content: '';
        position: absolute;
        width: 22px;
        height: 22px;
        left: 3px;
        bottom: 3px;
        background: white;
        border-radius: 50%;
        transition: 0.3s;
    }
    .toggle-switch input:checked + .toggle-slider { background: #34C759; }
    .toggle-switch input:checked + .toggle-slider::before { transform: translateX(28px); }
    .hours-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        border-radius: 10px;
        background: #f8f9fa;
        margin-bottom: 0.5rem;
    }
    .hours-row .day-name {
        width: 100px;
        font-weight: 600;
        font-size: 0.9rem;
    }
    .hours-row .form-control {
        padding: 0.5rem;
        font-size: 0.85rem;
    }
    .notif-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .notif-item:last-child { border-bottom: none; }
    .notif-info h6 { margin-bottom: 0.25rem; font-weight: 600; }
    .notif-info p { margin: 0; font-size: 0.8rem; color: #888; }
</style>

<div class="container" style="padding-top: 100px; padding-bottom: 50px;">
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="row align-items-center">
            <div class="col-auto">
                <div class="avatar-wrapper">
                    @php
                        $avatarUrl = \App\Helpers\ImageHelper::avatar($user->avatar, $user->name, $user->updated_at);
                    @endphp
                    <img src="{{ $avatarUrl }}" alt="{{ $user->name }}" class="avatar-img" style="object-fit: cover;" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=667eea&color=fff&size=200'">
                    <label for="avatarInput" class="avatar-upload">
                        <i class="bi bi-camera text-primary"></i>
                    </label>
                    <form id="avatarForm" action="{{ route('profile.avatar') }}" method="POST" enctype="multipart/form-data" style="display: none;">
                        @csrf
                        <input type="file" id="avatarInput" name="avatar" accept="image/*" onchange="document.getElementById('avatarForm').submit();">
                    </form>
                </div>
            </div>
            <div class="col">
                <h1 class="mb-1 h3" style="font-weight: 700;">{{ $vendor->store_name }}</h1>
                <p class="mb-2 opacity-75 small">{{ $user->name }} &bull; {{ $user->email }}</p>
                <div class="d-flex gap-2 align-items-center">
                    <span id="storeStatusBadge" class="status-badge {{ $vendor->is_open ? 'status-open' : 'status-closed' }}">
                        <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>
                        <span id="storeStatusText">{{ $vendor->is_open ? 'Open' : 'Closed' }}</span>
                    </span>
                    @if($vendor->is_active)
                        <span class="badge bg-light text-dark">Active</span>
                    @else
                        <span class="badge bg-danger">Inactive</span>
                    @endif
                </div>
            </div>
            <div class="col-auto">
                <a href="{{ route('vendor.dashboard') }}" class="btn btn-light btn-sm">
                    <i class="bi bi-speedometer2 me-1"></i> Dashboard
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column -->
        <div class="col-md-8">
            <!-- Store Status Toggle -->
            <div class="section-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="section-title mb-1">
                            <i class="bi bi-shop-window text-primary"></i> Store Status
                        </h5>
                        <p class="text-muted small mb-0">Toggle your store open/closed status</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="storeToggle" {{ $vendor->is_open ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>

            <!-- Store Information -->
            <div class="section-card">
                <h5 class="section-title">
                    <i class="bi bi-shop text-primary"></i> Store Information
                </h5>
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="is_vendor" value="1">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Store Name</label>
                            <input type="text" name="store_name" class="form-control" value="{{ $vendor->store_name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ $vendor->phone }}" placeholder="+60 12-345 6789">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Store Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Describe your store...">{{ $vendor->description }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Minimum Order Amount (RM)</label>
                            <input type="number" name="min_order_amount" class="form-control" value="{{ $vendor->min_order_amount }}" min="0" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Average Prep Time (minutes)</label>
                            <input type="number" name="avg_prep_time" class="form-control" value="{{ $vendor->avg_prep_time }}" min="1" max="120">
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-save">
                            <i class="bi bi-check-lg me-1"></i> Save Store Info
                        </button>
                    </div>
                </form>
            </div>

            <!-- Operating Hours -->
            <div class="section-card">
                <h5 class="section-title">
                    <i class="bi bi-clock text-primary"></i> Operating Hours
                </h5>
                <form id="hoursForm">
                    @csrf
                    @php
                        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                        $hoursMap = $vendor->operatingHours->keyBy('day_of_week');
                    @endphp
                    @foreach($days as $index => $day)
                        @php
                            $hours = $hoursMap->get($index);
                        @endphp
                        <div class="hours-row">
                            <span class="day-name">{{ $day }}</span>
                            <div class="form-check me-2">
                                <input type="checkbox" class="form-check-input day-closed" 
                                       id="closed_{{ $index }}" 
                                       name="hours[{{ $index }}][is_closed]" 
                                       {{ $hours && $hours->is_closed ? 'checked' : '' }}
                                       onchange="toggleDayInputs({{ $index }})">
                                <label class="form-check-label small" for="closed_{{ $index }}">Closed</label>
                            </div>
                            <input type="time" class="form-control day-input-{{ $index }}" 
                                   name="hours[{{ $index }}][open_time]" 
                                   value="{{ $hours ? substr($hours->open_time, 0, 5) : '09:00' }}"
                                   {{ $hours && $hours->is_closed ? 'disabled' : '' }}
                                   style="width: 120px;">
                            <span class="text-muted">to</span>
                            <input type="time" class="form-control day-input-{{ $index }}" 
                                   name="hours[{{ $index }}][close_time]" 
                                   value="{{ $hours ? substr($hours->close_time, 0, 5) : '21:00' }}"
                                   {{ $hours && $hours->is_closed ? 'disabled' : '' }}
                                   style="width: 120px;">
                        </div>
                    @endforeach
                    <div class="mt-3">
                        <button type="submit" class="btn btn-save">
                            <i class="bi bi-check-lg me-1"></i> Save Operating Hours
                        </button>
                    </div>
                </form>
            </div>

            <!-- Account Settings -->
            <div class="section-card">
                <h5 class="section-title">
                    <i class="bi bi-person text-primary"></i> Account Settings
                </h5>
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Your Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                            <small class="text-muted">
                                <a href="{{ route('auth.change-email') }}" style="color: #667eea; text-decoration: none;">
                                    <i class="bi bi-pencil-square"></i> Change email
                                </a>
                            </small>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-save">
                            <i class="bi bi-check-lg me-1"></i> Update Account
                        </button>
                    </div>
                </form>
            </div>

            <!-- Change Password -->
            <div class="section-card">
                <h5 class="section-title">
                    <i class="bi bi-shield-lock text-primary"></i> Change Password
                </h5>
                @if($user->google_id)
                <p class="text-muted mb-3">
                    <i class="bi bi-google"></i> You're signed in with Google. Google accounts don't use passwords.
                </p>
                <div class="mt-4">
                    <button class="btn btn-outline-secondary" disabled>
                        <i class="bi bi-key me-1"></i> Not Available for Google Accounts
                    </button>
                </div>
                @else
                <p class="text-muted mb-3">For security, you'll be redirected to verify your identity via OTP sent to your email.</p>
                <div class="mt-4">
                    <a href="{{ route('auth.change-password') }}" class="btn btn-outline-primary">
                        <i class="bi bi-key me-1"></i> Change Password
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-md-4">
            <!-- Store Details -->
            <div class="section-card">
                <h5 class="section-title">
                    <i class="bi bi-info-circle text-primary"></i> Store Details
                </h5>
                
                <div class="info-row">
                    <span class="info-label">Store Slug</span>
                    <span class="info-value">{{ $vendor->slug }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Member Since</span>
                    <span class="info-value">{{ $user->created_at->format('M d, Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Total Orders</span>
                    <span class="info-value">{{ $stats['total_orders'] }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Menu Items</span>
                    <span class="info-value">{{ $stats['menu_items'] }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Active Vouchers</span>
                    <span class="info-value">{{ $stats['active_vouchers'] }}</span>
                </div>
            </div>

            <!-- Notification Settings -->
            <div class="section-card">
                <h5 class="section-title">
                    <i class="bi bi-bell text-primary"></i> Notification Settings
                </h5>
                
                <div class="notif-item">
                    <div class="notif-info">
                        <h6>New Orders</h6>
                        <p>Get notified when you receive new orders</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" class="notif-toggle" data-type="new_orders" checked>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                <div class="notif-item">
                    <div class="notif-info">
                        <h6>Order Updates</h6>
                        <p>Notifications for order status changes</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" class="notif-toggle" data-type="order_updates" checked>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                <div class="notif-item">
                    <div class="notif-info">
                        <h6>Promotional Updates</h6>
                        <p>System announcements and promotions</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" class="notif-toggle" data-type="promotions">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="section-card">
                <h5 class="section-title">
                    <i class="bi bi-lightning text-primary"></i> Quick Actions
                </h5>
                
                <div class="d-grid gap-2">
                    <a href="{{ route('vendor.menu') }}" class="btn btn-outline-primary">
                        <i class="bi bi-collection me-1"></i> Manage Menu
                    </a>
                    <a href="{{ route('vendor.vouchers') }}" class="btn btn-outline-primary">
                        <i class="bi bi-ticket-perforated me-1"></i> Manage Vouchers
                    </a>
                    <a href="{{ route('vendor.orders') }}" class="btn btn-outline-primary">
                        <i class="bi bi-receipt me-1"></i> View Orders
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Store Open/Close Toggle
document.getElementById('storeToggle').addEventListener('change', function() {
    const isOpen = this.checked;
    const badge = document.getElementById('storeStatusBadge');
    const text = document.getElementById('storeStatusText');
    
    fetch('{{ route("vendor.toggle-status") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ is_open: isOpen })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            badge.className = 'status-badge ' + (isOpen ? 'status-open' : 'status-closed');
            text.textContent = isOpen ? 'Open' : 'Closed';
            Swal.fire({
                icon: 'success',
                title: isOpen ? 'Store Opened!' : 'Store Closed!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        } else {
            this.checked = !isOpen;
            Swal.fire('Error', data.message || 'Failed to update status', 'error');
        }
    })
    .catch(err => {
        this.checked = !isOpen;
        Swal.fire('Error', 'An error occurred', 'error');
    });
});

// Toggle day inputs based on closed checkbox
function toggleDayInputs(day) {
    const isClosed = document.getElementById('closed_' + day).checked;
    const inputs = document.querySelectorAll('.day-input-' + day);
    inputs.forEach(input => {
        input.disabled = isClosed;
        input.style.opacity = isClosed ? '0.5' : '1';
    });
}

// Operating Hours Form
document.getElementById('hoursForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Collect hours data as proper JSON object
    const hours = {};
    const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    
    for (let i = 0; i < 7; i++) {
        const isClosed = document.getElementById('closed_' + i).checked;
        const openTimeInput = document.querySelector(`input[name="hours[${i}][open_time]"]`);
        const closeTimeInput = document.querySelector(`input[name="hours[${i}][close_time]"]`);
        
        hours[i] = {
            is_closed: isClosed,
            open_time: openTimeInput ? openTimeInput.value : '09:00',
            close_time: closeTimeInput ? closeTimeInput.value : '21:00'
        };
    }
    
    fetch('{{ route("vendor.update-hours") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ hours: hours })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Saved!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            Swal.fire('Error', data.message || 'Failed to save hours', 'error');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        Swal.fire('Error', 'An error occurred while saving', 'error');
    });
});

// Notification toggles
document.querySelectorAll('.notif-toggle').forEach(toggle => {
    toggle.addEventListener('change', function() {
        const type = this.dataset.type;
        const enabled = this.checked;
        
        Swal.fire({
            icon: 'success',
            title: enabled ? 'Enabled' : 'Disabled',
            text: `${type.replace('_', ' ')} notifications ${enabled ? 'enabled' : 'disabled'}`,
            timer: 1500,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    });
});
</script>
@endpush
@endsection

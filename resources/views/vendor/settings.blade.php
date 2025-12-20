@extends('layouts.app')

@section('title', 'Settings - Vendor Dashboard')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-lg-12">
            <h2 class="fw-bold mb-2">
                <i class="bi bi-gear text-primary me-2"></i>
                Vendor Settings
            </h2>
            <p class="text-muted">Manage your store settings and preferences</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row g-4">
        <!-- Settings Sidebar -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="list-group list-group-flush">
                    <a href="#store-info" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                        <i class="bi bi-shop me-2"></i> Store Information
                    </a>
                    <a href="#operating-hours" class="list-group-item list-group-item-action" data-bs-toggle="list">
                        <i class="bi bi-clock me-2"></i> Operating Hours
                    </a>
                    <a href="#notifications" class="list-group-item list-group-item-action" data-bs-toggle="list">
                        <i class="bi bi-bell me-2"></i> Notifications
                        @if($unreadCount > 0)
                            <span class="badge bg-danger ms-auto">{{ $unreadCount }}</span>
                        @endif
                    </a>
                    <a href="#payment-methods" class="list-group-item list-group-item-action" data-bs-toggle="list">
                        <i class="bi bi-credit-card me-2"></i> Payment Methods
                    </a>
                    <a href="#security" class="list-group-item list-group-item-action" data-bs-toggle="list">
                        <i class="bi bi-shield-check me-2"></i> Security
                    </a>
                </div>
            </div>
        </div>

        <!-- Settings Content -->
        <div class="col-lg-9">
            <div class="tab-content">
                <!-- Store Information -->
                <div class="tab-pane fade show active" id="store-info">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">Store Information</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('vendor.settings.store-info') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Store Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="store_name" value="{{ $settings->store_name }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Email</label>
                                        <input type="email" class="form-control" value="{{ auth()->user()->email }}" disabled>
                                        <small class="text-muted">Change in Security section</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Phone</label>
                                        <input type="tel" class="form-control" name="phone" value="{{ $settings->phone }}" placeholder="+60123456789">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Description</label>
                                        <textarea class="form-control" name="description" rows="3" placeholder="Describe your store and specialties...">{{ $settings->description }}</textarea>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Store Logo</label>
                                        @if($settings->logo_path)
                                            <div class="mb-2">
                                                <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="Store Logo" class="img-thumbnail" style="max-width: 150px;">
                                            </div>
                                        @endif
                                        <input type="file" class="form-control" name="logo" accept="image/*">
                                        <small class="text-muted">Recommended size: 200x200px (JPEG, PNG, JPG, Max: 2MB)</small>
                                    </div>
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-lg me-2"></i>Save Changes
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Store Status -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">Store Status</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="mb-1">Currently Accepting Orders</h6>
                                    <small class="text-muted">Toggle to temporarily stop accepting new orders</small>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="acceptingOrdersToggle" 
                                           data-toggle-url="{{ route('vendor.settings.toggle-status') }}"
                                           style="width: 3em; height: 1.5em;" {{ $settings->accepting_orders ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                When disabled, customers won't be able to place new orders from your store.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Operating Hours -->
                <div class="tab-pane fade" id="operating-hours">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">Operating Hours</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('vendor.settings.operating-hours') }}" method="POST" id="operatingHoursForm">
                                @csrf
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Day</th>
                                                <th>Opening</th>
                                                <th>Closing</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                            <tr>
                                                <td class="fw-semibold text-capitalize">{{ $day }}</td>
                                                <td>
                                                    <input type="time" class="form-control form-control-sm" 
                                                           name="hours[{{ $loop->index }}][opening_time]" 
                                                           value="{{ $operatingHours[$day]->opening_time ?? '08:00' }}"
                                                           {{ !($operatingHours[$day]->is_open ?? true) ? 'disabled' : '' }}>
                                                    <input type="hidden" name="hours[{{ $loop->index }}][day]" value="{{ $day }}">
                                                </td>
                                                <td>
                                                    <input type="time" class="form-control form-control-sm" 
                                                           name="hours[{{ $loop->index }}][closing_time]" 
                                                           value="{{ $operatingHours[$day]->closing_time ?? '17:00' }}"
                                                           {{ !($operatingHours[$day]->is_open ?? true) ? 'disabled' : '' }}>
                                                </td>
                                                <td>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input day-toggle" type="checkbox" 
                                                               name="hours[{{ $loop->index }}][is_open]" 
                                                               value="1"
                                                               data-day="{{ $day }}"
                                                               {{ ($operatingHours[$day]->is_open ?? true) ? 'checked' : '' }}>
                                                        <input type="hidden" name="hours[{{ $loop->index }}][is_open]" value="0">
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-2"></i>Update Hours
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Notifications -->
                <div class="tab-pane fade" id="notifications">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Notification Preferences</h5>
                            @if($unreadCount > 0)
                                <button type="button" class="btn btn-sm btn-outline-primary" id="markAllRead"
                                        data-mark-all-url="{{ route('vendor.notifications.read-all') }}">
                                    Mark All Read
                                </button>
                            @endif
                        </div>
                        <div class="card-body">
                            <form action="{{ route('vendor.settings.notifications') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="notify_new_orders" 
                                               name="notify_new_orders" {{ $settings->notify_new_orders ? 'checked' : '' }}>
                                        <label class="form-check-label" for="notify_new_orders">
                                            <strong>New Orders</strong><br>
                                            <small class="text-muted">Receive notifications when customers place orders</small>
                                        </label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="notify_order_updates" 
                                               name="notify_order_updates" {{ $settings->notify_order_updates ? 'checked' : '' }}>
                                        <label class="form-check-label" for="notify_order_updates">
                                            <strong>Order Updates</strong><br>
                                            <small class="text-muted">Get notified about order cancellations and status changes</small>
                                        </label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="notify_email" 
                                               name="notify_email" {{ $settings->notify_email ? 'checked' : '' }}>
                                        <label class="form-check-label" for="notify_email">
                                            <strong>Email Notifications</strong><br>
                                            <small class="text-muted">Send notifications to your email</small>
                                        </label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-2"></i>Save Preferences
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Recent Notifications -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">Recent Notifications</h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                @forelse($notifications as $notification)
                                <div class="list-group-item px-0 {{ !$notification->is_read ? 'bg-light' : '' }}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <i class="bi bi-bell-fill text-primary me-2"></i>
                                                {{ $notification->title }}
                                            </h6>
                                            <p class="mb-1">{{ $notification->message }}</p>
                                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                        </div>
                                        @if(!$notification->is_read)
                                        <button class="btn btn-sm btn-outline-primary mark-read" 
                                                data-id="{{ $notification->notification_id }}">
                                            Mark Read
                                        </button>
                                        @endif
                                    </div>
                                </div>
                                @empty
                                <p class="text-muted text-center py-3">No notifications yet</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="tab-pane fade" id="payment-methods">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">Accepted Payment Methods</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('vendor.settings.payment-methods') }}" method="POST">
                                @csrf
                                <p class="text-muted mb-3">Select the payment methods you accept</p>
                                @php
                                    $currentMethods = $settings->payment_methods_array ?? ['cash'];
                                @endphp
                                <div class="mb-3">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="payment_methods[]" 
                                               value="cash" id="payment_cash" 
                                               {{ in_array('cash', $currentMethods) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="payment_cash">
                                            <i class="bi bi-cash-coin text-success me-2"></i>
                                            <strong>Cash</strong> - Pay on pickup
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="payment_methods[]" 
                                               value="online" id="payment_online" 
                                               {{ in_array('online', $currentMethods) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="payment_online">
                                            <i class="bi bi-credit-card text-primary me-2"></i>
                                            <strong>Online Payment</strong> - FPX, Credit/Debit Card
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="payment_methods[]" 
                                               value="card" id="payment_card" 
                                               {{ in_array('card', $currentMethods) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="payment_card">
                                            <i class="bi bi-credit-card-2-front text-info me-2"></i>
                                            <strong>Card at Pickup</strong> - Pay by card when collecting
                                        </label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-2"></i>Save Payment Methods
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Security -->
                <div class="tab-pane fade" id="security">
                    <!-- Change Password -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">Change Password</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('vendor.settings.password') }}" method="POST">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Current Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" name="current_password" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">New Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" name="new_password" minlength="8" required>
                                        <small class="text-muted">Minimum 8 characters</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Confirm New Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" name="new_password_confirmation" required>
                                    </div>
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-shield-check me-2"></i>Update Password
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Update Email -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">Update Email</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('vendor.settings.profile') }}" method="POST">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" name="email" value="{{ auth()->user()->email }}" required>
                                    </div>
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-envelope me-2"></i>Update Email
                                        </button>
                                    </div>
                                </div>
                            </form>
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
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    
    // Toggle Store Status
    document.getElementById('acceptingOrdersToggle')?.addEventListener('change', function() {
        const accepting = this.checked;
        const toggleUrl = this.dataset.toggleUrl;
        
        fetch(toggleUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ accepting_orders: accepting })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
            } else {
                showAlert('danger', 'Failed to update store status');
                this.checked = !accepting;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'An error occurred');
            this.checked = !accepting;
        });
    });
    
    // Toggle operating hours inputs
    document.querySelectorAll('.day-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const day = this.dataset.day;
            const row = this.closest('tr');
            const timeInputs = row.querySelectorAll('input[type="time"]');
            
            timeInputs.forEach(input => {
                input.disabled = !this.checked;
            });
        });
    });
    
    // Mark notification as read
    document.querySelectorAll('.mark-read').forEach(button => {
        button.addEventListener('click', function() {
            const notificationId = this.dataset.id;
            
            fetch(`/vendor/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.closest('.list-group-item').classList.remove('bg-light');
                    this.remove();
                    updateUnreadCount(-1);
                }
            });
        });
    });
    
    // Mark all as read
    document.getElementById('markAllRead')?.addEventListener('click', function() {
        const markAllUrl = this.dataset.markAllUrl;
        
        fetch(markAllUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelectorAll('.list-group-item.bg-light').forEach(item => {
                    item.classList.remove('bg-light');
                });
                document.querySelectorAll('.mark-read').forEach(btn => btn.remove());
                this.remove();
                document.querySelector('.badge.bg-danger')?.remove();
                showAlert('success', 'All notifications marked as read');
            }
        });
    });
    
    function updateUnreadCount(change) {
        const badge = document.querySelector('.badge.bg-danger');
        if (badge) {
            const current = parseInt(badge.textContent);
            const newCount = current + change;
            if (newCount <= 0) {
                badge.remove();
            } else {
                badge.textContent = newCount;
            }
        }
    }
    
    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
        alertDiv.style.zIndex = '9999';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);
        
        setTimeout(() => alertDiv.remove(), 3000);
    }
});
</script>
@endpush

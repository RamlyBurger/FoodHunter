<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
    <meta name="user-role" content="{{ auth()->user()->role }}">
    @endauth
    <title>@yield('title', 'FoodHunter - University Canteen')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    
    @stack('styles')
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                <i class="bi bi-egg-fried fs-3 text-primary me-2"></i>
                <span class="fw-bold fs-4 gradient-text">FoodHunter</span>
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    @auth
                        @if(auth()->user()->role === 'vendor')
                            <!-- Vendor Navigation -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('vendor/dashboard') ? 'active' : '' }}" href="{{ route('vendor.dashboard') }}">
                                    <i class="bi bi-speedometer2 me-1"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('vendor/menu*') ? 'active' : '' }}" href="{{ route('vendor.menu') }}">
                                    <i class="bi bi-collection me-1"></i> My Menu
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('vendor/orders*') ? 'active' : '' }}" href="{{ route('vendor.orders') }}">
                                    <i class="bi bi-receipt-cutoff me-1"></i> Orders
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('vendor/reports*') ? 'active' : '' }}" href="{{ route('vendor.reports') }}">
                                    <i class="bi bi-graph-up me-1"></i> Reports
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('vendor/settings*') ? 'active' : '' }}" href="{{ route('vendor.settings') }}">
                                    <i class="bi bi-gear me-1"></i> Settings
                                </a>
                            </li>
                        @else
                            <!-- Customer Navigation -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{ url('/') }}">
                                    <i class="bi bi-house-door me-1"></i> Home
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('menu*') ? 'active' : '' }}" href="{{ url('/menu') }}">
                                    <i class="bi bi-grid-3x3-gap me-1"></i> Menu
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('orders*') ? 'active' : '' }}" href="{{ url('/orders') }}">
                                    <i class="bi bi-receipt me-1"></i> Orders
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('contact') ? 'active' : '' }}" href="{{ url('/contact') }}">
                                    <i class="bi bi-envelope me-1"></i> Contact
                                </a>
                            </li>
                            
                            <!-- Cart Badge (Customers Only) -->
                            <li class="nav-item ms-2">
                                <a class="nav-link position-relative" href="{{ url('/cart') }}">
                                    <i class="bi bi-cart3 fs-5"></i>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count" style="display: none;">
                                        0
                                    </span>
                                </a>
                            </li>

                            <!-- Wishlist (Customers Only) -->
                            <li class="nav-item">
                                <a class="nav-link position-relative" href="{{ url('/wishlist') }}">
                                    <i class="bi bi-heart fs-5"></i>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger wishlist-count" style="display: none;">
                                        0
                                    </span>
                                </a>
                            </li>
                        @endif
                        
                        <!-- Notifications (All Authenticated Users) -->
                        <li class="nav-item dropdown ms-2">
                            <a class="nav-link position-relative" href="#" id="notificationDropdown" data-bs-toggle="dropdown">
                                <i class="bi bi-bell fs-5"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary notification-badge" style="display: none;">
                                    0
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end notification-dropdown" style="min-width: 350px; max-height: 400px; overflow-y: auto;">
                                <li class="dropdown-header d-flex justify-content-between align-items-center">
                                    <span>Notifications</span>
                                    <button class="btn btn-sm btn-link text-decoration-none mark-all-read-btn" style="display: none;">Mark all read</button>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <div id="notificationList">
                                    <li class="text-center py-3">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </li>
                                </div>
                            </ul>
                        </li>
                        
                        <!-- User Profile Dropdown -->
                        <li class="nav-item dropdown ms-3">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
                                <img src="https://ui-avatars.com/api/?name={{ auth()->user()->name ?? 'User' }}&background=6366f1&color=fff" 
                                     class="rounded-circle me-2" width="32" height="32" alt="Profile">
                                <span class="d-none d-lg-inline">{{ auth()->user()->name ?? 'User' }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('profile') }}">
                                    <i class="bi bi-person me-2"></i> Profile
                                </a></li>
                                @if(auth()->user()->role !== 'vendor')
                                <li><a class="dropdown-item" href="{{ route('loyalty') }}">
                                    <i class="bi bi-gift me-2"></i> Loyalty Points
                                </a></li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                                </a></li>
                            </ul>
                        </li>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    @else
                        <li class="nav-item ms-3">
                            <a class="btn btn-outline-primary rounded-pill px-4" href="{{ route('login') }}">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Login
                            </a>
                        </li>
                        <li class="nav-item ms-2">
                            <a class="btn btn-primary rounded-pill px-4" href="{{ route('register') }}">
                                <i class="bi bi-person-plus me-1"></i> Sign Up
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <!-- Toast Notifications Container -->
        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
            @if(session('success'))
            <div class="toast align-items-center text-white bg-success border-0 show" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="toast align-items-center text-white bg-danger border-0 show" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
            @endif
        </div>
        
        @yield('content')
    </main>

    <!-- Footer (Customer Only) -->
    @if(!auth()->check() || auth()->user()->role !== 'vendor')
    <footer class="footer bg-dark text-white py-5 mt-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <h5 class="fw-bold mb-3">
                        <i class="bi bi-egg-fried text-primary me-2"></i>
                        FoodHunter
                    </h5>
                    <p class="text-white-50">Your favorite university canteen food ordering platform. Fresh, fast, and delicious meals delivered right to you!</p>
                    <div class="social-links mt-3">
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle me-2">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle me-2">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle me-2">
                            <i class="bi bi-twitter"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <h6 class="fw-bold mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="{{ url('/') }}" class="text-white-50 text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="{{ url('/menu') }}" class="text-white-50 text-decoration-none">Menu</a></li>
                        <li class="mb-2"><a href="{{ url('/about') }}" class="text-white-50 text-decoration-none">About Us</a></li>
                        <li class="mb-2"><a href="{{ url('/contact') }}" class="text-white-50 text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <h6 class="fw-bold mb-3">Support</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">FAQ</a></li>
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Privacy Policy</a></li>
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Terms & Conditions</a></li>
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Help Center</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <h6 class="fw-bold mb-3">Contact Info</h6>
                    <ul class="list-unstyled text-white-50">
                        <li class="mb-2">
                            <i class="bi bi-geo-alt me-2"></i>
                            University Campus, Building A
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-telephone me-2"></i>
                            +60 12-345 6789
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-envelope me-2"></i>
                            info@foodhunter.com
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-clock me-2"></i>
                            Mon - Fri: 7AM - 8PM
                        </li>
                    </ul>
                </div>
            </div>
            
            <hr class="my-4 border-secondary">
            
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0 text-white-50">&copy; 2025 FoodHunter. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="mb-0 text-white-50">Made with <i class="bi bi-heart-fill text-danger"></i> for hungry students</p>
                </div>
            </div>
        </div>
    </footer>
    @endif

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <!-- Custom JS -->
    <script src="{{ asset('js/app.js') }}"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });

        // Auto-hide toast notifications after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const toasts = document.querySelectorAll('.toast');
            toasts.forEach(function(toast) {
                setTimeout(function() {
                    const bsToast = new bootstrap.Toast(toast);
                    bsToast.hide();
                }, 5000);
            });
        });
    </script>
    
    @auth
    <script>
        // Get user role from meta tag to avoid Blade syntax in JavaScript
        const userRole = document.querySelector('meta[name="user-role"]')?.content || 'student';
        const IS_VENDOR = userRole === 'vendor';
        
        // Load cart and wishlist counts for authenticated users
        document.addEventListener('DOMContentLoaded', function() {
            fetch('/cart/count')
                .then(response => response.json())
                .then(data => {
                    const cartBadge = document.querySelector('.cart-count');
                    if (cartBadge && data.count > 0) {
                        cartBadge.textContent = data.count;
                        cartBadge.style.display = 'inline-block';
                    }
                })
                .catch(error => console.error('Error loading cart count:', error));

            fetch('/wishlist/count')
                .then(response => response.json())
                .then(data => {
                    const wishlistBadge = document.querySelector('.wishlist-count');
                    if (wishlistBadge && data.count > 0) {
                        wishlistBadge.textContent = data.count;
                        wishlistBadge.style.display = 'inline-block';
                    }
                })
                .catch(error => console.error('Error loading wishlist count:', error));
            
            // Load notifications
            loadNotifications();
            loadNotificationCount();
            
            // Refresh notifications every 30 seconds
            setInterval(loadNotificationCount, 30000);
            
            // Load notifications when dropdown is opened
            document.getElementById('notificationDropdown')?.addEventListener('click', function() {
                loadNotifications();
            });
        });
        
        function loadNotifications() {
            fetch('/notifications')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayNotifications(data.notifications);
                        updateNotificationBadge(data.unread_count);
                    }
                })
                .catch(error => console.error('Error loading notifications:', error));
        }
        
        function loadNotificationCount() {
            fetch('/notifications/unread-count')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateNotificationBadge(data.count);
                    }
                })
                .catch(error => console.error('Error loading notification count:', error));
        }
        
        function displayNotifications(notifications) {
            const listContainer = document.getElementById('notificationList');
            const markAllBtn = document.querySelector('.mark-all-read-btn');
            
            if (!notifications || notifications.length === 0) {
                listContainer.innerHTML = `
                    <li class="text-center py-3 text-muted">
                        <i class="bi bi-bell-slash fs-3 d-block mb-2"></i>
                        <small>No notifications</small>
                    </li>
                `;
                markAllBtn.style.display = 'none';
                return;
            }
            
            const hasUnread = notifications.some(n => !n.is_read);
            markAllBtn.style.display = hasUnread ? 'inline-block' : 'none';
            
            listContainer.innerHTML = notifications.map(notification => {
                const icon = getNotificationIcon(notification.type);
                const timeAgo = formatTimeAgo(notification.created_at);
                const unreadClass = !notification.is_read ? 'bg-light' : '';
                const orderLink = notification.order_id ? 
                    (IS_VENDOR ? `/vendor/orders` : `/orders/${notification.order_id}`) : '#';
                
                return `
                    <li>
                        <a class="dropdown-item ${unreadClass} notification-item" 
                           href="${orderLink}"
                           data-notification-id="${notification.notification_id}"
                           data-is-read="${notification.is_read}">
                            <div class="d-flex align-items-start">
                                <i class="bi ${icon} fs-5 me-3"></i>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold">${notification.title}</h6>
                                    <p class="mb-1 small text-muted">${notification.message}</p>
                                    <small class="text-muted">${timeAgo}</small>
                                </div>
                                ${!notification.is_read ? '<span class="badge bg-primary ms-2">New</span>' : ''}
                            </div>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider m-0"></li>
                `;
            }).join('');
            
            // Add click handlers for notifications
            document.querySelectorAll('.notification-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    const notificationId = this.dataset.notificationId;
                    const isRead = this.dataset.isRead === 'true';
                    
                    if (!isRead) {
                        markNotificationAsRead(notificationId);
                    }
                });
            });
        }
        
        function getNotificationIcon(type) {
            const icons = {
                'order_accepted': 'bi-check-circle text-success',
                'order_preparing': 'bi-fire text-warning',
                'order_ready': 'bi-bell-fill text-primary',
                'order_completed': 'bi-bag-check text-success',
                'order_cancelled': 'bi-x-circle text-danger',
                'new_order': 'bi-receipt text-primary',
                'order_updated': 'bi-arrow-repeat text-info',
            };
            return icons[type] || 'bi-info-circle text-secondary';
        }
        
        function formatTimeAgo(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const seconds = Math.floor((now - date) / 1000);
            
            if (seconds < 60) return 'Just now';
            if (seconds < 3600) return Math.floor(seconds / 60) + ' minutes ago';
            if (seconds < 86400) return Math.floor(seconds / 3600) + ' hours ago';
            if (seconds < 604800) return Math.floor(seconds / 86400) + ' days ago';
            return date.toLocaleDateString();
        }
        
        function updateNotificationBadge(count) {
            const badge = document.querySelector('.notification-badge');
            if (badge) {
                if (count > 0) {
                    badge.textContent = count > 99 ? '99+' : count;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
            }
        }
        
        function markNotificationAsRead(notificationId) {
            fetch(`/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadNotificationCount();
                }
            })
            .catch(error => console.error('Error marking notification as read:', error));
        }
        
        // Mark all notifications as read
        document.querySelector('.mark-all-read-btn')?.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            fetch('/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadNotifications();
                    updateNotificationBadge(0);
                }
            })
            .catch(error => console.error('Error marking all as read:', error));
        });
    </script>
    @endauth
    
    @stack('scripts')
</body>
</html>

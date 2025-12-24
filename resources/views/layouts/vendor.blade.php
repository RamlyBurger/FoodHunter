<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Vendor Portal') - FoodHunter</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/images/tarIco.ico">
    <link rel="apple-touch-icon" href="/images/tarIco.ico">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-width: 260px;
            --header-height: 60px;
            --primary-color: #FF6B35;
            --primary-dark: #E55A2B;
            --sidebar-bg: #1a1d21;
            --sidebar-hover: #2d3238;
            --sidebar-active: #FF6B35;
            --text-muted: #8b949e;
        }
        
        html {
            font-size: 12.8px;
        }
        
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .vendor-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
        }
        
        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .sidebar-brand img {
            width: 40px;
            height: 40px;
            border-radius: 10px;
        }
        
        .sidebar-brand h5 {
            color: #fff;
            margin: 0;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .sidebar-brand small {
            color: var(--text-muted);
            font-size: 0.75rem;
        }
        
        .sidebar-nav {
            padding: 1rem 0;
            flex: 1;
            overflow-y: auto;
        }
        
        .nav-section {
            padding: 0.5rem 1.5rem;
            color: var(--text-muted);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }
        
        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.75rem 1.5rem;
            color: #c9d1d9;
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
            font-size: 0.9rem;
        }
        
        .sidebar-nav .nav-link:hover {
            background: var(--sidebar-hover);
            color: #fff;
        }
        
        .sidebar-nav .nav-link.active {
            background: rgba(255, 107, 53, 0.15);
            color: var(--sidebar-active);
            border-left-color: var(--sidebar-active);
        }
        
        .sidebar-nav .nav-link i {
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
        }
        
        .sidebar-nav .nav-link .badge {
            margin-left: auto;
            font-size: 0.7rem;
        }
        
        .sidebar-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        
        .store-status {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1rem;
            background: rgba(255,255,255,0.05);
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .store-status-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #c9d1d9;
            font-size: 0.85rem;
        }
        
        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #6c757d;
        }
        
        .status-dot.open {
            background: #28a745;
            box-shadow: 0 0 8px rgba(40, 167, 69, 0.5);
        }
        
        /* Main Content */
        .vendor-main {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }
        
        .vendor-header {
            position: sticky;
            top: 0;
            height: var(--header-height);
            background: #fff;
            border-bottom: 1px solid #e1e4e8;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            z-index: 100;
        }
        
        .header-search {
            position: relative;
            width: 300px;
        }
        
        .header-search input {
            width: 100%;
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            border: 1px solid #e1e4e8;
            border-radius: 8px;
            font-size: 0.9rem;
            background: #f6f8fa;
            transition: all 0.2s;
        }
        
        .header-search input:focus {
            outline: none;
            border-color: var(--primary-color);
            background: #fff;
        }
        
        .header-search i {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .header-btn {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            border: 1px solid #e1e4e8;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #586069;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }
        
        .header-btn:hover {
            background: #f6f8fa;
            color: var(--primary-color);
        }
        
        .header-btn .notification-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            width: 18px;
            height: 18px;
            background: #dc3545;
            color: #fff;
            font-size: 0.65rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        
        /* Notification Dropdown */
        .notification-dropdown {
            width: 360px;
            padding: 0;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            border: 1px solid #e1e4e8;
        }
        
        .notification-dropdown .dropdown-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #e1e4e8;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: transparent;
        }
        
        .notification-dropdown .dropdown-header h6 {
            margin: 0;
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        .notification-items-list {
            max-height: 350px;
            overflow-y: auto;
        }
        
        .notification-item {
            display: flex;
            padding: 1rem 1.25rem;
            gap: 12px;
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.15s;
            text-decoration: none;
            color: inherit;
        }
        
        .notification-item:hover {
            background: #f8f9fa;
        }
        
        .notification-item.unread {
            background: rgba(255, 107, 53, 0.05);
        }
        
        .notification-item .notif-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .notification-item .notif-icon.order {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }
        
        .notification-item .notif-icon.alert {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }
        
        .notification-item .notif-icon.info {
            background: rgba(0, 123, 255, 0.1);
            color: #007bff;
        }
        
        .notification-item .notif-content {
            flex: 1;
            min-width: 0;
        }
        
        .notification-item .notif-title {
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 2px;
            color: #24292f;
        }
        
        .notification-item .notif-message {
            font-size: 0.8rem;
            color: #586069;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .notification-item .notif-time {
            font-size: 0.7rem;
            color: #8b949e;
            margin-top: 4px;
        }
        
        .notification-dropdown .dropdown-footer {
            padding: 0.75rem 1.25rem;
            border-top: 1px solid #e1e4e8;
            text-align: center;
        }
        
        .user-dropdown {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0.5rem;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .user-dropdown:hover {
            background: #f6f8fa;
        }
        
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: var(--primary-color);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .user-info {
            display: flex;
            flex-direction: column;
        }
        
        .user-info .name {
            font-weight: 600;
            font-size: 0.85rem;
            color: #24292f;
        }
        
        .user-info .role {
            font-size: 0.7rem;
            color: var(--text-muted);
        }
        
        .vendor-content {
            padding: 1.5rem;
        }
        
        /* Cards */
        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid #e1e4e8;
            transition: all 0.2s;
        }
        
        .stat-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        
        .stat-card .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
        }
        
        .stat-card .stat-label {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin: 0;
        }
        
        .stat-card .stat-change {
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .stat-change.positive {
            color: #28a745;
        }
        
        .stat-change.negative {
            color: #dc3545;
        }
        
        /* Content Cards */
        .content-card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e1e4e8;
            overflow: hidden;
        }
        
        .content-card-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e1e4e8;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .content-card-header h5 {
            margin: 0;
            font-weight: 600;
            font-size: 1rem;
        }
        
        .content-card-body {
            padding: 1.5rem;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .vendor-sidebar {
                transform: translateX(-100%);
            }
            
            .vendor-sidebar.show {
                transform: translateX(0);
            }
            
            .vendor-main {
                margin-left: 0;
            }
            
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 999;
            }
            
            .sidebar-overlay.show {
                display: block;
            }
        }
        
        /* Custom scrollbar */
        .sidebar-nav::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar-nav::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.1);
            border-radius: 3px;
        }
        
        /* Toggle Switch */
        .toggle-switch {
            position: relative;
            width: 44px;
            height: 24px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #6c757d;
            transition: 0.3s;
            border-radius: 24px;
        }
        
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: 0.3s;
            border-radius: 50%;
        }
        
        input:checked + .toggle-slider {
            background-color: #28a745;
        }
        
        input:checked + .toggle-slider:before {
            transform: translateX(20px);
        }
        
        @stack('styles')
    </style>
</head>
<body>
    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Sidebar -->
    <aside class="vendor-sidebar" id="vendorSidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('foodhunter-logo.png') }}" alt="FoodHunter">
            <div>
                <h5>FoodHunter</h5>
                <small>Vendor Portal</small>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <div class="nav-section">Main Menu</div>
            
            <a href="{{ route('vendor.dashboard') }}" class="nav-link {{ request()->routeIs('vendor.dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill"></i>
                <span>Dashboard</span>
            </a>
            
            <a href="{{ route('vendor.orders') }}" class="nav-link {{ request()->routeIs('vendor.orders*') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i>
                <span>Orders</span>
                @if(isset($pendingOrdersCount) && $pendingOrdersCount > 0)
                    <span class="badge bg-danger">{{ $pendingOrdersCount }}</span>
                @endif
            </a>
            
            <a href="{{ route('vendor.menu') }}" class="nav-link {{ request()->routeIs('vendor.menu') ? 'active' : '' }}">
                <i class="bi bi-menu-button-wide"></i>
                <span>Menu Items</span>
            </a>
            
            <a href="{{ route('vendor.reports') }}" class="nav-link {{ request()->routeIs('vendor.reports') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line"></i>
                <span>Reports</span>
            </a>
            
            <div class="nav-section mt-4">Tools</div>
            
            <a href="{{ route('vendor.scan') }}" class="nav-link {{ request()->routeIs('vendor.scan') ? 'active' : '' }}">
                <i class="bi bi-qr-code-scan"></i>
                <span>Scan QR Code</span>
            </a>
            
            <div class="nav-section mt-4">Account</div>
            
            <a href="{{ url('/') }}" class="nav-link">
                <i class="bi bi-house"></i>
                <span>Back to Store</span>
            </a>
            
            <a href="{{ url('/logout') }}" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="bi bi-box-arrow-left"></i>
                <span>Logout</span>
            </a>
            <form id="logout-form" action="{{ url('/logout') }}" method="POST" class="d-none">@csrf</form>
        </nav>
        
        <div class="sidebar-footer">
            @if(isset($vendor))
            <div class="store-status">
                <div class="store-status-indicator">
                    <span class="status-dot {{ $vendor->is_open ? 'open' : '' }}"></span>
                    <span>{{ $vendor->is_open ? 'Store Open' : 'Store Closed' }}</span>
                </div>
                <form action="{{ url('/vendor/toggle-open') }}" method="POST" class="d-inline" id="toggleStoreForm">
                    @csrf
                    <label class="toggle-switch">
                        <input type="checkbox" {{ $vendor->is_open ? 'checked' : '' }} onchange="document.getElementById('toggleStoreForm').submit()">
                        <span class="toggle-slider"></span>
                    </label>
                </form>
            </div>
            @endif
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="vendor-main">
        <!-- Header -->
        <header class="vendor-header">
            <div class="d-flex align-items-center gap-3">
                <button class="btn d-lg-none" id="sidebarToggle">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <div class="header-search d-none d-md-block">
                    <i class="bi bi-search"></i>
                    <input type="text" placeholder="Search orders, menu items...">
                </div>
            </div>
            
            <div class="header-actions">
                <div class="dropdown">
                    <button class="header-btn" title="Notifications" data-bs-toggle="dropdown" data-bs-auto-close="outside" id="vendorNotificationToggle">
                        <i class="bi bi-bell"></i>
                        <span class="notification-badge" id="vendor-notification-count" style="display: none;">0</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end notification-dropdown" id="vendorNotificationDropdown">
                        <div class="dropdown-header">
                            <h6><i class="bi bi-bell me-2"></i>Notifications</h6>
                            <a href="{{ url('/notifications') }}" class="text-decoration-none small">View All</a>
                        </div>
                        <div class="notification-items-list" id="vendor-notification-items">
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-bell fs-3 d-block mb-2"></i>
                                <span class="small">No new notifications</span>
                            </div>
                        </div>
                        <div class="dropdown-footer">
                            <button class="btn btn-sm btn-outline-secondary w-100" onclick="markAllVendorNotificationsRead()">
                                <i class="bi bi-check-all me-1"></i> Mark All as Read
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="dropdown">
                    <div class="user-dropdown" data-bs-toggle="dropdown">
                        @php
                            $avatarUrl = \App\Helpers\ImageHelper::avatar(Auth::user()->avatar, Auth::user()->name, Auth::user()->updated_at);
                        @endphp
                        <img src="{{ $avatarUrl }}" alt="{{ Auth::user()->name }}" class="user-avatar" style="object-fit: cover;" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=FF6B35&color=fff&size=100'">
                        <div class="user-info d-none d-sm-flex">
                            <span class="name">{{ Auth::user()->name ?? 'Vendor' }}</span>
                            <span class="role">{{ $vendor->store_name ?? 'Store' }}</span>
                        </div>
                        <i class="bi bi-chevron-down ms-1 text-muted"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ url('/profile') }}"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="{{ route('vendor.reports') }}"><i class="bi bi-gear me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="{{ url('/logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="bi bi-box-arrow-left me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </header>
        
        <!-- Page Content -->
        <div class="vendor-content">
            @yield('content')
        </div>
    </main>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 (Local) -->
    <link rel="stylesheet" href="{{ asset('css/sweetalert2.min.css') }}">
    <script src="{{ asset('js/sweetalert2.min.js') }}"></script>
    
    <script>
        // Sidebar toggle for mobile
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('vendorSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            });
        }
        
        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });
        }
        
        // SweetAlert Toast Configuration
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            showCloseButton: true,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
        
        // Toast notification function using SweetAlert
        function showToast(message, type = 'success') {
            const iconMap = {
                'success': 'success',
                'error': 'error',
                'warning': 'warning',
                'info': 'info'
            };
            Toast.fire({
                icon: iconMap[type] || 'success',
                title: message
            });
        }
        
        // Show session flash messages as SweetAlert toasts
        @if(session('success'))
            Toast.fire({
                icon: 'success',
                title: @json(session('success'))
            });
        @endif
        
        @if(session('error'))
            Toast.fire({
                icon: 'error',
                title: @json(session('error'))
            });
        @endif
        
        @if(session('warning'))
            Toast.fire({
                icon: 'warning',
                title: @json(session('warning'))
            });
        @endif
        
        @if(session('info'))
            Toast.fire({
                icon: 'info',
                title: @json(session('info'))
            });
        @endif
        
        // CSRF Token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        
        // Track last notification count
        let lastVendorNotificationCount = null;
        
        // Load vendor notifications
        function loadVendorNotifications() {
            fetch('/api/notifications/dropdown', { 
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                const container = document.getElementById('vendor-notification-items');
                const countEl = document.getElementById('vendor-notification-count');
                
                const unreadCount = data.unread_count || 0;
                countEl.textContent = unreadCount;
                countEl.style.display = unreadCount > 0 ? 'flex' : 'none';
                
                // Show SweetAlert toast if new notifications arrived
                if (lastVendorNotificationCount !== null && unreadCount > lastVendorNotificationCount) {
                    const newNotifications = data.notifications.filter(n => !n.read_at);
                    if (newNotifications.length > 0) {
                        const latestNotif = newNotifications[0];
                        showToast(latestNotif.title + ': ' + latestNotif.message, 'info');
                    }
                }
                lastVendorNotificationCount = unreadCount;
                
                if (!data.notifications || data.notifications.length === 0) {
                    container.innerHTML = `<div class="text-center py-4 text-muted">
                        <i class="bi bi-bell fs-3 d-block mb-2"></i>
                        <span class="small">No new notifications</span>
                    </div>`;
                    return;
                }
                
                container.innerHTML = data.notifications.slice(0, 5).map(notif => {
                    const iconClass = notif.type === 'order' ? 'order' : (notif.type === 'promo' ? 'alert' : 'info');
                    const iconName = notif.type === 'order' ? 'bi-bag-check' : (notif.type === 'promo' ? 'bi-tag' : 'bi-info-circle');
                    const url = notif.url || '/notifications';
                    return `
                    <a href="${url}" class="notification-item ${notif.read_at ? '' : 'unread'}" onclick="markVendorNotificationRead(${notif.id})">
                        <div class="notif-icon ${iconClass}">
                            <i class="bi ${iconName}"></i>
                        </div>
                        <div class="notif-content">
                            <div class="notif-title">${notif.title}</div>
                            <div class="notif-message">${notif.message}</div>
                            <div class="notif-time">${notif.time_ago}</div>
                        </div>
                    </a>`;
                }).join('');
            })
            .catch(() => {});
        }
        
        // Mark notification as read
        function markVendorNotificationRead(notificationId) {
            fetch('/notifications/' + notificationId + '/read', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            }).catch(() => {});
        }
        
        // Mark all notifications as read
        function markAllVendorNotificationsRead() {
            fetch('/notifications/read-all', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('All notifications marked as read', 'success');
                    loadVendorNotifications();
                }
            })
            .catch(() => {});
        }
        
        // Global polling for new orders (works on all pages)
        let lastKnownOrderCount = null;
        
        function checkForNewOrders() {
            fetch('/vendor/dashboard', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && lastKnownOrderCount !== null && data.todayOrders > lastKnownOrderCount) {
                    loadVendorNotifications();
                    showToast('New order received!', 'info');
                }
                lastKnownOrderCount = data.todayOrders;
            })
            .catch(() => {});
        }
        
        // Load notifications on page load and when dropdown opens
        document.addEventListener('DOMContentLoaded', function() {
            loadVendorNotifications();
            checkForNewOrders();
            
            // Refresh every 10 seconds for real-time updates
            setInterval(loadVendorNotifications, 10000);
            setInterval(checkForNewOrders, 15000);
        });
        
        document.getElementById('vendorNotificationToggle')?.addEventListener('show.bs.dropdown', loadVendorNotifications);
    </script>
    
    @stack('scripts')
</body>
</html>

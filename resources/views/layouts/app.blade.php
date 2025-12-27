{{--
|==============================================================================
| Main App Layout - Shared (All Students)
|==============================================================================
|
| @author     Ng Wayne Xiang, Haerine Deepak Singh, Low Nam Lee, Lee Song Yan, Lee Kin Hang
| @module     Shared Infrastructure
|
| Main layout template for all customer-facing pages.
| Includes navbar, footer, and shared scripts/styles.
|==============================================================================
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'FoodHunter') - TARUMT Canteen</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/images/tarIco.ico">
    <link rel="apple-touch-icon" href="/images/tarIco.ico">
    
    <!-- Google Fonts - Montserrat -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Animate.css for SweetAlert animations -->
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #FF9500;
            --primary-hover: #E68600;
            --secondary-color: #FF6B00;
            --success-color: #34C759;
            --warning-color: #FFCC00;
            --danger-color: #FF3B30;
            --dark-color: #1C1C1E;
            --gray-100: #F2F2F7;
            --gray-200: #E5E5EA;
            --gray-300: #D1D1D6;
            --gray-400: #C7C7CC;
            --gray-500: #8E8E93;
            --gray-600: #636366;
            --text-primary: #1C1C1E;
            --text-secondary: #8E8E93;
            --card-bg: #FFFFFF;
            --body-bg: #F2F2F7;
            --nav-active: #FFCC00;
        }
        
        * {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        html {
            font-size: 12.8px;
        }
        
        html, body {
            height: 100%;
            font-family: 'Montserrat', sans-serif;
        }
        
        body {
            font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--body-bg);
            color: var(--text-primary);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        main {
            flex: 1 0 auto;
            min-height: calc(100vh - 200px);
            padding-top: 0 !important;
            margin-top: 0;
        }
        
        .page-content {
            padding-top: 60px;
        }
        
        footer {
            flex-shrink: 0;
        }
        
        .navbar {
            background: #ffffff !important;
            backdrop-filter: saturate(180%) blur(20px);
            -webkit-backdrop-filter: saturate(180%) blur(20px);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1050;
            transition: all 0.3s ease;
            padding: 0.75rem 0;
            border: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        
        .navbar.scrolled {
            background: rgba(255, 255, 255, 0.98) !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: 0.5rem 0;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--primary-color) !important;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .navbar-brand i {
            font-size: 1.3rem;
        }
        
        .nav-link {
            font-weight: 500;
            font-size: 0.9rem;
            padding: 0.5rem 1rem !important;
            border-radius: 8px;
            transition: all 0.2s ease;
            color: var(--text-primary) !important;
        }
        
        .nav-link:hover {
            color: var(--primary-color) !important;
        }
        
        .nav-link.active {
            background: var(--nav-active) !important;
            color: var(--dark-color) !important;
            font-weight: 600;
        }
        
        .navbar-nav .nav-item {
            margin: 0 2px;
        }
        
        .btn {
            font-weight: 600;
            border-radius: 10px;
            padding: 8px 16px;
            font-size: 0.875rem;
            letter-spacing: -0.2px;
        }
        
        .btn-sm {
            padding: 5px 12px;
            font-size: 0.8rem;
            border-radius: 8px;
        }
        
        .btn-lg {
            padding: 10px 20px;
            font-size: 0.95rem;
            border-radius: 12px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }
        
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .btn-outline-secondary {
            border-color: var(--gray-300);
            color: var(--text-primary);
        }
        
        .btn-outline-secondary:hover {
            background-color: var(--gray-200);
            border-color: var(--gray-300);
            color: var(--text-primary);
        }
        
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 2px 8px rgba(0,0,0,0.04);
            background: var(--card-bg);
        }
        
        .card:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.08), 0 4px 16px rgba(0,0,0,0.06);
        }
        
        .food-card img {
            height: 150px;
            object-fit: cover;
            border-radius: 16px 16px 0 0;
        }
        
        .card-body {
            padding: 1rem;
        }
        
        .card-title {
            font-weight: 600;
            letter-spacing: -0.3px;
        }
        
        .badge {
            font-weight: 600;
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 0.75rem;
            letter-spacing: -0.1px;
        }
        
        .badge-cart {
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 10px;
            min-width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            transition: transform 0.15s ease;
        }
        
        .badge-cart.pulse {
            animation: badgePulse 0.4s ease;
        }
        
        @keyframes badgePulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.5); }
            100% { transform: scale(1); }
        }
        
        .wishlist-btn i.pulse {
            animation: heartPulse 0.4s ease;
        }
        
        @keyframes heartPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.3); }
            100% { transform: scale(1); }
        }
        
        /* Quantity Stepper */
        .quantity-stepper {
            display: inline-flex;
            align-items: center;
            background: #f8f9fa;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e9ecef;
        }
        .stepper-btn {
            width: 36px;
            height: 36px;
            border: none;
            background: transparent;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-size: 1rem;
            transition: all 0.15s;
        }
        .stepper-btn:hover {
            background: var(--primary-color);
            color: white;
        }
        .stepper-btn:active {
            transform: scale(0.95);
        }
        .stepper-input {
            width: 50px;
            text-align: center;
            border: none;
            background: transparent;
            font-weight: 600;
            font-size: 1rem;
            -moz-appearance: textfield;
        }
        .stepper-input::-webkit-outer-spin-button,
        .stepper-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        /* Small size */
        .stepper-sm .stepper-btn { width: 28px; height: 28px; font-size: 0.85rem; }
        .stepper-sm .stepper-input { width: 36px; font-size: 0.85rem; }
        /* Large size */
        .stepper-lg .stepper-btn { width: 44px; height: 44px; font-size: 1.1rem; }
        .stepper-lg .stepper-input { width: 60px; font-size: 1.1rem; }
        
        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid var(--gray-300);
            padding: 8px 12px;
            font-size: 0.875rem;
            background-color: var(--card-bg);
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.15);
        }
        
        .form-label {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        
        .table {
            border-radius: 12px;
            overflow: hidden;
        }
        
        .table thead th {
            background-color: var(--gray-100);
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: var(--text-secondary);
            border: none;
            padding: 14px 16px;
        }
        
        .table tbody td {
            padding: 14px 16px;
            vertical-align: middle;
            border-color: var(--gray-200);
        }
        
        .dropdown-menu {
            border: none;
            border-radius: 14px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.12), 0 0 1px rgba(0,0,0,0.08);
            padding: 8px;
        }
        
        .dropdown-item {
            border-radius: 10px;
            padding: 10px 14px;
            font-weight: 500;
        }
        
        .dropdown-item:hover {
            background-color: var(--gray-100);
        }
        
        .alert {
            border: none;
            border-radius: 14px;
            font-weight: 500;
        }
        
        .category-card {
            cursor: pointer;
            text-align: center;
            padding: 24px 20px;
            border-radius: 16px;
        }
        
        .category-card i {
            font-size: 2rem;
            color: var(--primary-color);
        }
        
        .text-primary { color: var(--primary-color) !important; }
        .text-danger { color: var(--danger-color) !important; }
        .text-success { color: var(--success-color) !important; }
        .text-warning { color: var(--warning-color) !important; }
        .text-muted { color: var(--text-secondary) !important; }
        
        .bg-primary { background-color: var(--primary-color) !important; }
        .bg-danger { background-color: var(--danger-color) !important; }
        .bg-success { background-color: var(--success-color) !important; }
        .bg-warning { background-color: var(--warning-color) !important; }
        
        .status-pending { color: var(--warning-color); }
        .status-confirmed { color: var(--primary-color); }
        .status-preparing { color: #FF9500; }
        .status-ready { color: var(--success-color); }
        .status-completed { color: var(--gray-500); }
        .status-cancelled { color: var(--danger-color); }
        
        /* Modern Dropdown Styling (shadcn-inspired) */
        .dropdown-menu {
            border: 1px solid var(--gray-200);
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.12), 0 2px 6px rgba(0,0,0,0.04);
            padding: 6px;
            z-index: 1060 !important;
            min-width: 180px;
            animation: dropdownFadeIn 0.15s ease-out;
        }
        
        @keyframes dropdownFadeIn {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .dropdown-menu .dropdown-item {
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-primary);
            transition: all 0.15s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .dropdown-menu .dropdown-item:hover,
        .dropdown-menu .dropdown-item:focus {
            background: var(--gray-100);
            color: var(--text-primary);
        }
        
        .dropdown-menu .dropdown-item:active {
            background: var(--gray-200);
        }
        
        .dropdown-menu .dropdown-item i {
            font-size: 1rem;
            opacity: 0.7;
        }
        
        .dropdown-menu .dropdown-divider {
            margin: 6px 0;
            border-color: var(--gray-200);
        }
        
        .dropdown-menu .dropdown-header {
            padding: 10px 14px;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .navbar .dropdown-menu {
            margin-top: 8px;
        }
        
        /* Cart, Wishlist & Notification Dropdown */
        .cart-dropdown, .wishlist-dropdown, .notification-dropdown {
            width: 340px;
            padding: 0;
            z-index: 1060 !important;
            position: absolute !important;
            border-radius: 16px;
        }
        
        .cart-dropdown .dropdown-header, .wishlist-dropdown .dropdown-header, .notification-dropdown .dropdown-header {
            background: transparent;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 16px;
            border-bottom: 1px solid var(--gray-200);
            text-transform: none;
            letter-spacing: 0;
            color: var(--text-primary);
        }
        .cart-items-list, .wishlist-items-list, .notification-items-list {
            max-height: 320px;
            overflow-y: auto;
            padding: 8px;
        }
        .cart-item, .wishlist-item {
            display: flex;
            align-items: center;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 4px;
            transition: background 0.15s ease;
        }
        .cart-item:hover, .wishlist-item:hover {
            background: var(--gray-100);
        }
        .cart-item img, .wishlist-item img {
            width: 52px;
            height: 52px;
            object-fit: cover;
            border-radius: 10px;
            margin-right: 12px;
        }
        .cart-item-info, .wishlist-item-info {
            flex: 1;
            min-width: 0;
        }
        .cart-item-name, .wishlist-item-name {
            font-size: 0.875rem;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: var(--text-primary);
        }
        .cart-item-price {
            font-size: 0.8rem;
            color: var(--primary-color);
            font-weight: 600;
        }
        .cart-item-qty {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }
        .dropdown-footer {
            padding: 16px;
            background: transparent;
            border-top: 1px solid var(--gray-200);
        }
        
        /* Notification Item */
        .notification-item {
            display: flex;
            align-items: flex-start;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 4px;
            transition: background 0.15s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }
        .notification-item:hover {
            background: var(--gray-100);
        }
        .notification-item.unread {
            background: rgba(255, 107, 53, 0.05);
        }
        .notification-item .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 1.1rem;
        }
        .notification-item .notification-icon.order {
            background: rgba(52, 199, 89, 0.1);
            color: #34C759;
        }
        .notification-item .notification-icon.promo {
            background: rgba(255, 107, 53, 0.1);
            color: var(--primary-color);
        }
        .notification-item .notification-icon.info {
            background: rgba(0, 122, 255, 0.1);
            color: #007AFF;
        }
        .notification-item-info {
            flex: 1;
            min-width: 0;
        }
        .notification-item-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 2px;
        }
        .notification-item-message {
            font-size: 0.8rem;
            color: var(--text-secondary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .notification-item-time {
            font-size: 0.7rem;
            color: var(--text-secondary);
            margin-top: 4px;
        }
        
        /* Fix pagination SVG icons */
        .pagination svg {
            width: 1rem;
            height: 1rem;
        }
        
        /* Clickable card overlay */
        .card-link-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1;
        }
        .card-link-overlay ~ * {
            position: relative;
        }
        .food-card .btn, .food-card .wishlist-btn {
            z-index: 2;
            position: relative;
        }
        
        /* Fix favorite button centering */
        .wishlist-btn, .favorite-btn {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0 !important;
            line-height: 1 !important;
        }
        
        .wishlist-btn i, .favorite-btn i {
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }
        
        /* Food card improvements */
        .food-card {
            display: flex;
            flex-direction: column;
            height: 100%;
            transition: box-shadow 0.2s ease;
        }
        
        .food-card:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.12) !important;
        }
        
        .food-card .card-img-top {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        }
        
        .food-card .card-body {
            display: flex;
            flex-direction: column;
            flex: 1;
            padding: 1rem;
        }
        
        .food-card .card-content {
            flex: 1;
        }
        
        .food-card .card-footer-area {
            margin-top: auto;
            padding-top: 1rem;
            border-top: 1px solid var(--gray-200);
        }
        
        footer {
            background-color: var(--dark-color);
            color: white;
            padding: 24px 0 16px;
            margin-top: auto;
        }
        
        footer h5, footer h6 {
            font-weight: 600;
            letter-spacing: -0.3px;
        }
        
        /* Page header styles */
        .page-header {
            margin-bottom: 1.5rem;
        }
        
        .page-header h2, .page-header h3 {
            font-weight: 700;
            letter-spacing: -0.5px;
            font-size: 1.5rem;
        }
        
        h1 { font-size: 2rem; }
        h2 { font-size: 1.5rem; }
        h3 { font-size: 1.25rem; }
        h4 { font-size: 1.1rem; }
        h5 { font-size: 1rem; }
        h6 { font-size: 0.875rem; }
        
        /* Shadow utilities */
        .shadow-sm {
            box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 2px 8px rgba(0,0,0,0.04) !important;
        }
        
        /* Container max widths */
        .container {
            max-width: 1400px;
            padding-left: 20px;
            padding-right: 20px;
        }
        
        @media (min-width: 1400px) {
            .container {
                max-width: 1600px;
                padding-left: 30px;
                padding-right: 30px;
            }
        }
        
        @media (max-width: 768px) {
            .container {
                padding-left: 15px;
                padding-right: 15px;
            }
        }
        
        /* Breadcrumb styling */
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 1rem;
        }
        
        .breadcrumb-item a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .breadcrumb-item.active {
            color: var(--text-secondary);
        }
        
        /* Image styling */
        img {
            border-radius: 12px;
        }
        
        .rounded-circle {
            border-radius: 50% !important;
        }
        
        /* Modal styling */
        .modal-content {
            border: none;
            border-radius: 20px;
            overflow: hidden;
        }
        
        .modal-header {
            border-bottom: 1px solid var(--gray-200);
            padding: 20px 24px;
        }
        
        .modal-body {
            padding: 24px;
        }
        
        .modal-footer {
            border-top: 1px solid var(--gray-200);
            padding: 16px 24px;
        }
        
        .modal-title {
            font-weight: 600;
            letter-spacing: -0.3px;
        }
        
        /* List group styling */
        .list-group-item {
            border-color: var(--gray-200);
            padding: 14px 18px;
        }
        
        /* Pagination */
        .pagination .page-link {
            border-radius: 10px;
            margin: 0 2px;
            border: none;
            color: var(--text-primary);
            font-weight: 500;
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
        }
        
        /* Progress bar */
        .progress {
            border-radius: 10px;
            height: 8px;
            background-color: var(--gray-200);
        }
        
        .progress-bar {
            border-radius: 10px;
        }
        
        /* Stars rating */
        .stars {
            color: var(--warning-color);
        }
        
        /* Global Page Loading Overlay */
        .page-loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        .page-loading-overlay.hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }
        .page-loader {
            text-align: center;
        }
        .page-loader-spinner {
            width: 48px;
            height: 48px;
            border: 4px solid var(--gray-200);
            border-top-color: var(--primary-color);
            border-radius: 50%;
            animation: pageLoaderSpin 0.8s linear infinite;
            margin: 0 auto 1rem;
        }
        .page-loader-text {
            color: var(--text-secondary);
            font-size: 0.875rem;
            font-weight: 500;
        }
        @keyframes pageLoaderSpin {
            to { transform: rotate(360deg); }
        }
        
        /* Action Loading Overlay (for AJAX operations) */
        .action-loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 99998;
        }
        .action-loading-overlay.show {
            display: flex;
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Page Loading Overlay -->
    <div id="pageLoadingOverlay" class="page-loading-overlay">
        <div class="page-loader">
            <div class="page-loader-spinner"></div>
            <div class="page-loader-text">Loading...</div>
        </div>
    </div>
    
    <!-- Action Loading Overlay (for AJAX operations) -->
    <div id="actionLoadingOverlay" class="action-loading-overlay">
        <div class="page-loader">
            <div class="page-loader-spinner"></div>
            <div class="page-loader-text">Please wait...</div>
        </div>
    </div>
    <!-- Navigation -->
    <nav class="navbar navbar-expand navbar-light">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                <img src="{{ asset('foodhunter-logo.png') }}" alt="FoodHunter" style="height: 32px; margin-right: 0;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    @auth
                        @if(Auth::user()->isVendor())
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
                                <a class="nav-link {{ request()->is('vendor/vouchers*') ? 'active' : '' }}" href="{{ route('vendor.vouchers') }}">
                                    <i class="bi bi-ticket-perforated me-1"></i> Vouchers
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('vendor/reports*') ? 'active' : '' }}" href="{{ route('vendor.reports') }}">
                                    <i class="bi bi-graph-up me-1"></i> Reports
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
                                    <i class="bi bi-receipt me-1"></i> My Orders
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('vouchers*') ? 'active' : '' }}" href="{{ url('/vouchers') }}">
                                    <i class="bi bi-ticket-perforated me-1"></i> Vouchers
                                </a>
                            </li>
                        @endif
                    @else
                        <!-- Guest Navigation -->
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
                    @endauth
                </ul>
                <ul class="navbar-nav align-items-center">
                    @auth
                    <!-- Notification Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link position-relative" href="#" data-bs-toggle="dropdown" data-bs-auto-close="outside" id="notificationDropdownToggle">
                            <i class="bi bi-bell"></i>
                            <span class="badge bg-danger badge-cart" id="notification-count" style="display: none;">0</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end notification-dropdown" id="notificationDropdown">
                            <div class="dropdown-header d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-bell me-1"></i> Notifications</span>
                                <a href="{{ url('/notifications') }}" class="text-decoration-none small">View All</a>
                            </div>
                            <div class="notification-items-list" id="notification-items">
                                <div class="text-center py-4 text-muted">
                                    <i class="bi bi-bell fs-3"></i>
                                    <p class="mb-0 small">No new notifications</p>
                                </div>
                            </div>
                            <div class="dropdown-footer">
                                <button class="btn btn-outline-secondary btn-sm w-100" onclick="markAllNotificationsRead()">
                                    <i class="bi bi-check-all me-1"></i> Mark All as Read
                                </button>
                            </div>
                        </div>
                    </li>
                    @if(!Auth::user()->isVendor())
                    <!-- Wishlist Dropdown (Customer Only) -->
                    <li class="nav-item dropdown">
                        <a class="nav-link position-relative" href="#" data-bs-toggle="dropdown" data-bs-auto-close="outside" id="wishlistDropdownToggle">
                            <i class="bi bi-heart"></i>
                            <span class="badge bg-danger badge-cart" id="wishlist-count">0</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end wishlist-dropdown" id="wishlistDropdown">
                            <div class="dropdown-header d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-heart me-1"></i> Wishlist</span>
                                <a href="{{ route('wishlist.index') }}" class="text-decoration-none small">View All</a>
                            </div>
                            <div class="wishlist-items-list" id="wishlist-items">
                                <div class="text-center py-4 text-muted">
                                    <i class="bi bi-heart fs-3"></i>
                                    <p class="mb-0 small">Your wishlist is empty</p>
                                </div>
                            </div>
                        </div>
                    </li>
                    <!-- Cart Dropdown (Customer Only) -->
                    <li class="nav-item dropdown">
                        <a class="nav-link position-relative" href="#" data-bs-toggle="dropdown" data-bs-auto-close="outside" id="cartDropdownToggle">
                            <i class="bi bi-cart3"></i>
                            <span class="badge bg-danger badge-cart" id="cart-count">0</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end cart-dropdown" id="cartDropdown">
                            <div class="dropdown-header d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-cart3 me-1"></i> Shopping Cart</span>
                                <span class="text-muted small" id="cart-total-header">RM 0.00</span>
                            </div>
                            <div class="cart-items-list" id="cart-items">
                                <div class="text-center py-4 text-muted">
                                    <i class="bi bi-cart fs-3"></i>
                                    <p class="mb-0 small">Your cart is empty</p>
                                </div>
                            </div>
                            <div class="dropdown-footer">
                                <a href="{{ route('cart.index') }}" class="btn btn-outline-secondary btn-sm w-100 mb-2">View Cart</a>
                                <a href="{{ route('checkout') }}" class="btn btn-primary btn-sm w-100">Checkout</a>
                            </div>
                        </div>
                    </li>
                    @endif
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
                            <img src="{{ \App\Helpers\ImageHelper::avatar(Auth::user()->avatar, Auth::user()->name, Auth::user()->updated_at) }}" alt="{{ Auth::user()->name }}" class="rounded-circle me-2" style="width: 28px; height: 28px; object-fit: cover;" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=6c757d&color=fff&size=100'">
                            {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ url('/profile') }}"><i class="bi bi-person"></i> Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ url('/logout') }}" method="POST" id="logout-form">
                                    @csrf
                                    <button type="button" class="dropdown-item" onclick="confirmLogout()"><i class="bi bi-box-arrow-right"></i> Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                    @else
                    <li class="nav-item ms-2">
                        <a class="btn btn-outline-primary rounded-pill px-4" href="{{ url('/login') }}">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Login
                        </a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-primary rounded-pill px-4" href="{{ url('/register') }}">
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
        @yield('content')
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <h5 class="mb-3 d-flex align-items-center"><img src="/images/tarIco.ico" alt="FoodHunter" style="height: 24px; width: 24px; margin-right: 8px; border-radius: 4px;"> FoodHunter</h5>
                    <p class="small text-light opacity-75 mb-3">TARUMT University Canteen ordering system. Order food, skip the queue, and save with vouchers!</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-light opacity-75 hover-opacity-100"><i class="bi bi-facebook fs-5"></i></a>
                        <a href="#" class="text-light opacity-75 hover-opacity-100"><i class="bi bi-instagram fs-5"></i></a>
                        <a href="#" class="text-light opacity-75 hover-opacity-100"><i class="bi bi-twitter-x fs-5"></i></a>
                        <a href="#" class="text-light opacity-75 hover-opacity-100"><i class="bi bi-tiktok fs-5"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="mb-3" style="font-weight: 600;">Explore</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="{{ url('/') }}" class="text-light text-decoration-none opacity-75">Home</a></li>
                        <li class="mb-2"><a href="{{ url('/menu') }}" class="text-light text-decoration-none opacity-75">Menu</a></li>
                        <li class="mb-2"><a href="{{ url('/vendors') }}" class="text-light text-decoration-none opacity-75">Vendors</a></li>
                        <li class="mb-2"><a href="{{ url('/vouchers') }}" class="text-light text-decoration-none opacity-75">Vouchers</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="mb-3" style="font-weight: 600;">Account</h6>
                    <ul class="list-unstyled small">
                        @auth
                        <li class="mb-2"><a href="{{ url('/profile') }}" class="text-light text-decoration-none opacity-75">My Profile</a></li>
                        <li class="mb-2"><a href="{{ url('/orders') }}" class="text-light text-decoration-none opacity-75">My Orders</a></li>
                        <li class="mb-2"><a href="{{ url('/cart') }}" class="text-light text-decoration-none opacity-75">Cart</a></li>
                        <li class="mb-2"><a href="{{ route('wishlist.index') }}" class="text-light text-decoration-none opacity-75">Wishlist</a></li>
                        @else
                        <li class="mb-2"><a href="{{ url('/login') }}" class="text-light text-decoration-none opacity-75">Login</a></li>
                        <li class="mb-2"><a href="{{ url('/register') }}" class="text-light text-decoration-none opacity-75">Register</a></li>
                        @endauth
                    </ul>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h6 class="mb-3" style="font-weight: 600;">Contact Us</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2 d-flex align-items-center gap-2 opacity-75">
                            <i class="bi bi-geo-alt"></i> TARUMT, Jalan Genting Kelang, 53300 KL
                        </li>
                        <li class="mb-2 d-flex align-items-center gap-2 opacity-75">
                            <i class="bi bi-envelope"></i> support@foodhunter.edu.my
                        </li>
                        <li class="mb-2 d-flex align-items-center gap-2 opacity-75">
                            <i class="bi bi-telephone"></i> +60 3-4145 0123
                        </li>
                        <li class="mb-2 d-flex align-items-center gap-2 opacity-75">
                            <i class="bi bi-clock"></i> Mon-Fri: 7AM - 9PM
                        </li>
                    </ul>
                </div>
            </div>
            <hr class="my-4 opacity-25">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <small class="opacity-75">&copy; {{ date('Y') }} FoodHunter - Built with Laravel MVC</small>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <small class="opacity-75">
                        <a href="#" class="text-light text-decoration-none">Privacy Policy</a>
                        <span class="mx-2">|</span>
                        <a href="#" class="text-light text-decoration-none">Terms of Service</a>
                    </small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 (Local) -->
    <link rel="stylesheet" href="{{ asset('css/sweetalert2.min.css') }}">
    <script src="{{ asset('js/sweetalert2.min.js') }}"></script>
    
    <script>
        // Hide page loading overlay when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            const overlay = document.getElementById('pageLoadingOverlay');
            if (overlay) {
                // Small delay to ensure styles are applied
                setTimeout(function() {
                    overlay.classList.add('hidden');
                }, 100);
            }
        });
        
        // Also hide on window load (for images/resources)
        window.addEventListener('load', function() {
            const overlay = document.getElementById('pageLoadingOverlay');
            if (overlay) {
                overlay.classList.add('hidden');
            }
        });
        
        // Global functions for action loading overlay
        function showActionLoading(text = 'Please wait...') {
            const overlay = document.getElementById('actionLoadingOverlay');
            if (overlay) {
                const textEl = overlay.querySelector('.page-loader-text');
                if (textEl) textEl.textContent = text;
                overlay.classList.add('show');
            }
        }
        
        function hideActionLoading() {
            const overlay = document.getElementById('actionLoadingOverlay');
            if (overlay) {
                overlay.classList.remove('show');
            }
        }
        
        // CSRF Token for AJAX
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 10) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
        
        // Global image error handler - replace broken images with placeholder
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('img').forEach(function(img) {
                img.addEventListener('error', function() {
                    if (!this.dataset.errorHandled) {
                        this.dataset.errorHandled = 'true';
                        const name = this.alt || 'Image';
                        const initials = name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
                        this.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(initials) + '&background=dee2e6&color=6c757d&size=400';
                    }
                });
            });
        });
        
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
        
        // Show toast notification using SweetAlert
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
            Toast.fire({ icon: 'success', title: @json(session('success')) });
        @endif
        @if(session('error'))
            Toast.fire({ icon: 'error', title: @json(session('error')) });
        @endif
        @if(session('warning'))
            Toast.fire({ icon: 'warning', title: @json(session('warning')) });
        @endif
        @if(session('info'))
            Toast.fire({ icon: 'info', title: @json(session('info')) });
        @endif
        
        // Confirm action with SweetAlert
        function confirmAction(message, formOrCallback) {
            Swal.fire({
                title: 'Are you sure?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#FF9500',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, proceed!'
            }).then((result) => {
                if (result.isConfirmed) {
                    if (typeof formOrCallback === 'function') {
                        formOrCallback();
                    } else if (formOrCallback instanceof HTMLFormElement) {
                        formOrCallback.submit();
                    }
                }
            });
            return false;
        }
        
        // Submit form via AJAX with toast feedback
        function submitFormAjax(form, successMsg, errorMsg) {
            const formData = new FormData(form);
            const url = form.action;
            const method = form.method.toUpperCase();
            
            fetch(url, {
                method: method === 'GET' ? 'POST' : method,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || successMsg || 'Action completed!', 'success');
                    if (data.redirect) window.location.href = data.redirect;
                    else if (data.reload) window.location.reload();
                } else {
                    showToast(data.message || errorMsg || 'Action failed', 'error');
                }
            })
            .catch(() => showToast(errorMsg || 'Something went wrong', 'error'));
        }
        
        // Copy to clipboard with toast
        function copyToClipboard(text, successMsg = 'Copied to clipboard!') {
            navigator.clipboard.writeText(text).then(() => {
                showToast(successMsg, 'success');
            }).catch(() => {
                showToast('Failed to copy', 'error');
            });
        }
        
        // Add to cart via AJAX (consumes Haerine Deepak Singh's Item Availability API first)
        function addToCart(itemId, quantity = 1, btn = null) {
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            }
            
            // First check item availability using Haerine Deepak Singh's API
            fetch('/api/menu/' + itemId + '/availability', {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(availData => {
                const data = availData.data || availData;
                if (!data.available || !data.is_available) {
                    showToast('Sorry, this item is currently unavailable', 'error');
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-cart3"></i>';
                    }
                    return Promise.reject('unavailable');
                }
                
                // Item is available, proceed to add to cart
                return fetch('{{ route("cart.add") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ menu_item_id: itemId, quantity: quantity })
                });
            })
            .then(res => res ? res.json() : null)
            .then(response => {
                if (response && response.success) {
                    // Handle standardized API response format
                    const data = response.data || response;
                    
                    // Update cart count badge
                    if (typeof data.cart_count !== 'undefined') {
                        const cartBadges = document.querySelectorAll('.cart-count, #cart-count');
                        cartBadges.forEach(badge => {
                            badge.textContent = data.cart_count;
                            badge.style.display = data.cart_count > 0 ? 'flex' : 'none';
                        });
                    }
                    
                    // Pulse animation on cart badge
                    pulseCartBadge();
                    loadCartDropdown();
                    
                    // Show success toast
                    showToast(response.message || 'Added to cart!', 'success');
                } else if (response) {
                    showToast(response.message || 'Failed to add to cart', 'error');
                }
            })
            .catch(err => {
                if (err !== 'unavailable') {
                    showToast('Failed to add to cart', 'error');
                }
            })
            .finally(() => { 
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-cart3"></i>';
                }
            });
        }
        
        // Pulse animation for cart badge
        function pulseCartBadge() {
            const badge = document.getElementById('cart-count');
            if (badge) {
                badge.classList.remove('pulse');
                void badge.offsetWidth; // Trigger reflow
                badge.classList.add('pulse');
                setTimeout(() => badge.classList.remove('pulse'), 400);
            }
        }
        
        // Pulse animation for wishlist badge
        function pulseWishlistBadge() {
            const badge = document.getElementById('wishlist-count');
            if (badge) {
                badge.classList.remove('pulse');
                void badge.offsetWidth;
                badge.classList.add('pulse');
                setTimeout(() => badge.classList.remove('pulse'), 400);
            }
        }
        
        // Toggle wishlist via AJAX (global function)
        function toggleWishlist(itemId, btn) {
            const icon = btn.querySelector('i');
            
            fetch('{{ route("wishlist.toggle") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ menu_item_id: itemId })
            })
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    // Handle standardized API response format
                    const data = response.data || response;
                    const inWishlist = data.in_wishlist;
                    
                    if (inWishlist) {
                        icon.className = 'bi bi-heart-fill text-danger';
                        btn.classList.add('btn-danger');
                        btn.classList.remove('btn-outline-secondary');
                    } else {
                        icon.className = 'bi bi-heart';
                        btn.classList.remove('btn-danger');
                        btn.classList.add('btn-outline-secondary');
                    }
                    // Pulse animation on icon and badge
                    icon.classList.add('pulse');
                    setTimeout(() => icon.classList.remove('pulse'), 400);
                    pulseWishlistBadge();
                    loadWishlistDropdown();
                    
                    // Update wishlist count in navbar
                    const wishlistCount = data.wishlist_count;
                    if (typeof wishlistCount !== 'undefined') {
                        const countEls = document.querySelectorAll('.wishlist-count');
                        countEls.forEach(el => el.textContent = wishlistCount);
                    }
                    
                    // Show toast feedback
                    if (typeof showToast === 'function') {
                        showToast(response.message || (inWishlist ? 'Added to wishlist' : 'Removed from wishlist'), 'success');
                    }
                }
            })
            .catch(() => {});
        }
        
        // Quantity stepper functions
        function stepperMinus(btn) {
            const wrapper = btn.closest('.quantity-stepper');
            const input = wrapper.querySelector('.stepper-input');
            const min = parseInt(input.min) || 1;
            let val = parseInt(input.value) || 1;
            if (val > min) {
                input.value = val - 1;
                handleStepperChange(wrapper, val - 1);
            }
        }
        
        function stepperPlus(btn) {
            const wrapper = btn.closest('.quantity-stepper');
            const input = wrapper.querySelector('.stepper-input');
            const max = parseInt(input.max) || 99;
            let val = parseInt(input.value) || 1;
            if (val < max) {
                input.value = val + 1;
                handleStepperChange(wrapper, val + 1);
            }
        }
        
        function handleStepperChange(wrapper, newQty) {
            const cartItemId = wrapper.dataset.cartItemId;
            if (cartItemId) {
                updateCartItemQuantity(cartItemId, newQty);
            }
        }
        
        function updateCartItemQuantity(cartItemId, quantity) {
            fetch('/cart/' + cartItemId, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ quantity: quantity })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    pulseCartBadge();
                    if (typeof updateCartSummary === 'function') {
                        updateCartSummary();
                    }
                    loadCartDropdown();
                }
            })
            .catch(() => {});
        }
        
        // Load cart dropdown items
        function loadCartDropdown() {
            fetch('/api/cart/dropdown', { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(response => {
                // Handle standardized API response format (data nested under response.data)
                const data = response.data || response;
                const container = document.getElementById('cart-items');
                const countEl = document.getElementById('cart-count');
                const totalEl = document.getElementById('cart-total-header');
                
                countEl.textContent = data.count || 0;
                totalEl.textContent = 'RM ' + (data.total || 0).toFixed(2);
                
                if (!data.items || data.items.length === 0) {
                    container.innerHTML = `<div class="text-center py-4 text-muted">
                        <i class="bi bi-cart fs-3"></i>
                        <p class="mb-0 small">Your cart is empty</p>
                    </div>`;
                    return;
                }
                
                container.innerHTML = data.items.map(item => {
                    const fallback = `https://ui-avatars.com/api/?name=${encodeURIComponent(item.name)}&background=f3f4f6&color=9ca3af&size=200`;
                    return `
                    <div class="cart-item">
                        <a href="/menu/${item.menu_item_id}">
                            <img src="${item.image || fallback}" alt="${item.name}" onerror="this.src='${fallback}'">
                        </a>
                        <div class="cart-item-info">
                            <a href="/menu/${item.menu_item_id}" class="text-decoration-none text-dark">
                                <div class="cart-item-name">${item.name}</div>
                            </a>
                            <div class="cart-item-price">RM ${parseFloat(item.price).toFixed(2)}</div>
                            <div class="cart-item-qty">Qty: ${item.quantity}</div>
                        </div>
                        <button class="btn btn-sm btn-link text-danger p-0" onclick="removeFromCart(${item.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>`;
                }).join('');
            })
            .catch(() => {});
        }
        
        // Remove from cart
        function removeFromCart(cartItemId) {
            fetch('/cart/' + cartItemId, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('Item removed from cart', 'success');
                    loadCartDropdown();
                }
            })
            .catch(() => {});
        }
        
        // Load wishlist dropdown items
        function loadWishlistDropdown() {
            fetch('/api/wishlist/dropdown', { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(response => {
                // Handle standardized API response format (data nested under response.data)
                const data = response.data || response;
                const container = document.getElementById('wishlist-items');
                const countEl = document.getElementById('wishlist-count');
                
                countEl.textContent = data.count || 0;
                
                if (!data.items || data.items.length === 0) {
                    container.innerHTML = `<div class="text-center py-4 text-muted">
                        <i class="bi bi-heart fs-3"></i>
                        <p class="mb-0 small">Your wishlist is empty</p>
                    </div>`;
                    return;
                }
                
                container.innerHTML = data.items.map(item => {
                    const fallback = `https://ui-avatars.com/api/?name=${encodeURIComponent(item.name)}&background=f3f4f6&color=9ca3af&size=200`;
                    return `
                    <div class="wishlist-item">
                        <a href="/menu/${item.menu_item_id}">
                            <img src="${item.image || fallback}" alt="${item.name}" onerror="this.src='${fallback}'">
                        </a>
                        <div class="wishlist-item-info">
                            <a href="/menu/${item.menu_item_id}" class="text-decoration-none text-dark">
                                <div class="wishlist-item-name">${item.name}</div>
                            </a>
                            <div class="cart-item-price">RM ${parseFloat(item.price).toFixed(2)}</div>
                        </div>
                        <button class="btn btn-sm btn-primary me-1" onclick="addToCart(${item.menu_item_id})" title="Add to Cart">
                            <i class="bi bi-cart-plus"></i>
                        </button>
                        <button class="btn btn-sm btn-link text-danger p-0" onclick="removeFromWishlistDropdown(${item.id})" title="Remove">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>`;
                }).join('');
            })
            .catch(() => {});
        }
        
        // Remove from wishlist (dropdown)
        function removeFromWishlistDropdown(wishlistId) {
            fetch('/wishlist/' + wishlistId, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('Removed from wishlist', 'success');
                    loadWishlistDropdown();
                }
            })
            .catch(() => {});
        }
        
        // Track last notification count
        let lastCustomerNotificationCount = null;
        
        // Load notification dropdown items
        function loadNotificationDropdown() {
            fetch('/api/notifications/dropdown', { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(response => {
                const container = document.getElementById('notification-items');
                const countEl = document.getElementById('notification-count');
                
                // Handle standardized API response format
                const data = response.data || response;
                const unreadCount = data.unread_count || 0;
                countEl.textContent = unreadCount;
                countEl.style.display = unreadCount > 0 ? 'flex' : 'none';
                
                // Show SweetAlert toast if new notifications arrived
                if (lastCustomerNotificationCount !== null && unreadCount > lastCustomerNotificationCount) {
                    const newNotifications = (data.notifications || []).filter(n => !n.read_at);
                    if (newNotifications.length > 0) {
                        const latestNotif = newNotifications[0];
                        showToast(latestNotif.title + ': ' + latestNotif.message, 'info');
                    }
                }
                lastCustomerNotificationCount = unreadCount;
                
                if (!data.notifications || data.notifications.length === 0) {
                    container.innerHTML = `<div class="text-center py-4 text-muted">
                        <i class="bi bi-bell fs-3"></i>
                        <p class="mb-0 small">No new notifications</p>
                    </div>`;
                    return;
                }
                
                container.innerHTML = data.notifications.slice(0, 5).map(notif => {
                    const iconClass = notif.type === 'order' ? 'order' : (notif.type === 'promo' ? 'promo' : 'info');
                    const iconName = notif.type === 'order' ? 'bi-bag-check' : (notif.type === 'promo' ? 'bi-tag' : 'bi-info-circle');
                    return `
                    <a href="${notif.url || '/notifications'}" class="notification-item ${notif.read_at ? '' : 'unread'}" onclick="markNotificationRead(${notif.id})">
                        <div class="notification-icon ${iconClass}">
                            <i class="bi ${iconName}"></i>
                        </div>
                        <div class="notification-item-info">
                            <div class="notification-item-title">${notif.title}</div>
                            <div class="notification-item-message">${notif.message}</div>
                            <div class="notification-item-time">${notif.time_ago}</div>
                        </div>
                    </a>`;
                }).join('');
            })
            .catch(() => {});
        }
        
        // Mark notification as read
        function markNotificationRead(notificationId) {
            fetch('/notifications/' + notificationId + '/read', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            }).catch(() => {});
        }
        
        // Mark all notifications as read
        function markAllNotificationsRead() {
            fetch('/notifications/read-all', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('All notifications marked as read', 'success');
                    loadNotificationDropdown();
                }
            })
            .catch(() => {});
        }
        
        // Load dropdowns on page load
        @auth
        document.addEventListener('DOMContentLoaded', function() {
            @if(!Auth::user()->isVendor())
            // Customer-only: load cart and wishlist
            loadCartDropdown();
            loadWishlistDropdown();
            @endif
            loadNotificationDropdown();
            startSessionCheck();
            
            // Auto-refresh notifications every 10 seconds for real-time updates
            setInterval(loadNotificationDropdown, 10000);
        });
        
        // Refresh dropdowns when opened
        @if(!Auth::user()->isVendor())
        document.getElementById('cartDropdownToggle')?.addEventListener('show.bs.dropdown', loadCartDropdown);
        document.getElementById('wishlistDropdownToggle')?.addEventListener('show.bs.dropdown', loadWishlistDropdown);
        @endif
        document.getElementById('notificationDropdownToggle')?.addEventListener('show.bs.dropdown', loadNotificationDropdown);
        
        // OWASP [66-67]: Single-device login - check session validity every 10 seconds
        // If user logs in from another device, this session becomes invalid
        let sessionCheckInterval;
        let sessionCheckActive = true;
        
        function startSessionCheck() {
            // Check immediately on page load
            checkSessionValidity();
            // Then check every 10 seconds
            sessionCheckInterval = setInterval(checkSessionValidity, 10000);
        }
        
        function checkSessionValidity() {
            if (!sessionCheckActive) return;
            
            fetch('/api/auth/session-check', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (response.status === 401 || response.status === 419) {
                    // Session is invalid - user was logged out (possibly from another device)
                    sessionCheckActive = false;
                    clearInterval(sessionCheckInterval);
                    showToast('You have been logged out from another device.', 'error');
                    setTimeout(() => {
                        window.location.href = '/login?logged_out_other_device=1';
                    }, 1500);
                    return null;
                }
                return response.json();
            })
            .then(data => {
                if (data && data.valid === false) {
                    sessionCheckActive = false;
                    clearInterval(sessionCheckInterval);
                    showToast('You have been logged out from another device.', 'error');
                    setTimeout(() => {
                        window.location.href = '/login?logged_out_other_device=1';
                    }, 1500);
                }
            })
            .catch(() => {
                // Network error - don't logout, just skip this check
            });
        }
        
        // Logout confirmation
        function confirmLogout() {
            Swal.fire({
                title: 'Logout?',
                text: 'Are you sure you want to logout?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#FF6B35',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, logout',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit();
                }
            });
        }
        @endauth
    </script>
    @stack('scripts')
</body>
</html>

{{--
|==============================================================================
| API Tester Page - Shared (All Students)
|==============================================================================
|
| @author     Ng Wayne Xiang, Haerine Deepak Singh, Low Nam Lee, Lee Song Yan, Lee Kin Hang
| @module     Development Tools
|
| Interactive API testing interface for development and debugging.
| Allows testing all API endpoints with custom parameters.
|==============================================================================
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>FoodHunter API Tester</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            color: white;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .main-grid {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 20px;
            align-items: start;
        }
        
        .auth-panel {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .auth-panel h2 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 1.5em;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
        }
        
        .status-authenticated {
            background: #10b981;
            color: white;
        }
        
        .status-unauthenticated {
            background: #ef4444;
            color: white;
        }
        
        .token-display {
            margin-top: 15px;
            padding: 10px;
            background: #f3f4f6;
            border-radius: 8px;
            word-break: break-all;
            font-family: monospace;
            font-size: 12px;
            color: #4b5563;
        }
        
        .quick-accounts {
            margin-top: 20px;
            padding: 15px;
            background: #fef3c7;
            border-radius: 8px;
        }
        
        .quick-accounts h3 {
            font-size: 14px;
            color: #92400e;
            margin-bottom: 10px;
        }
        
        .account-btn {
            padding: 8px 12px;
            margin: 5px 5px 5px 0;
            border: none;
            border-radius: 6px;
            background: #fbbf24;
            color: #92400e;
            font-size: 12px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .account-btn:hover {
            background: #f59e0b;
        }
        
        .api-panel {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .api-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .tab-btn {
            padding: 10px 20px;
            border: 2px solid #e0e0e0;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .tab-btn:hover {
            border-color: #667eea;
            color: #667eea;
        }
        
        .tab-btn.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .endpoint-group {
            margin-bottom: 30px;
        }
        
        .endpoint-group h3 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 1.2em;
        }
        
        .endpoint-item {
            background: #f9fafb;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .endpoint-item:hover {
            border-color: #667eea;
            transform: translateX(5px);
        }
        
        .endpoint-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .method-badge {
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 700;
            margin-right: 10px;
        }
        
        .method-get { background: #10b981; color: white; }
        .method-post { background: #3b82f6; color: white; }
        .method-put { background: #f59e0b; color: white; }
        .method-delete { background: #ef4444; color: white; }
        
        .endpoint-path {
            flex: 1;
            font-family: monospace;
            font-weight: 500;
            color: #374151;
        }
        
        .test-btn {
            padding: 6px 15px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .test-btn:hover {
            background: #5568d3;
        }
        
        .response-panel {
            margin-top: 20px;
            padding: 20px;
            background: #1f2937;
            border-radius: 8px;
            display: none;
        }
        
        .response-panel.show {
            display: block;
        }
        
        .response-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .response-header h4 {
            color: #10b981;
            font-size: 1.1em;
        }
        
        .copy-btn {
            padding: 6px 12px;
            background: #374151;
            color: white;
            border: 1px solid #4b5563;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .copy-btn:hover {
            background: #4b5563;
        }
        
        .response-content {
            background: #111827;
            padding: 15px;
            border-radius: 6px;
            overflow-x: auto;
        }
        
        .response-content pre {
            color: #e5e7eb;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.6;
            margin: 0;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.show {
            display: flex;
        }
        
        .modal-content {
            background: white;
            border-radius: 15px;
            padding: 30px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-header {
            margin-bottom: 20px;
        }
        
        .modal-header h3 {
            color: #667eea;
            font-size: 1.5em;
        }
        
        .close-btn {
            float: right;
            font-size: 28px;
            font-weight: bold;
            color: #9ca3af;
            cursor: pointer;
        }
        
        .close-btn:hover {
            color: #374151;
        }
        
        .param-group {
            margin-bottom: 15px;
        }
        
        .param-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #374151;
        }
        
        .param-group input, .param-group textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            font-family: monospace;
        }
        
        textarea {
            min-height: 100px;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🍕 FoodHunter API Tester</h1>
            <p>Test all REST APIs with authentication</p>
        </div>
        
        <div class="main-grid">
            <!-- Authentication Panel -->
            <div class="auth-panel">
                <h2>🔐 Authentication</h2>
                
                <!-- Session Auth Form -->
                <form id="loginForm" onsubmit="login(event)">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="email" placeholder="john@example.com" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" id="password" placeholder="password123" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Login (Session)</button>
                </form>
                
                <button class="btn btn-danger" onclick="logout()" style="margin-top: 5px;">Logout</button>
                
                <!-- Google Login -->
                <a href="{{ route('auth.google') }}" class="btn" style="display: flex; align-items: center; justify-content: center; gap: 10px; margin-top: 10px; background: #fff; border: 2px solid #e0e0e0; color: #333; text-decoration: none;">
                    <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" width="20" alt="Google">
                    <span>Sign in with Google</span>
                </a>
                
                <div id="authStatus">
                    <span class="status-badge status-unauthenticated">Not Authenticated</span>
                </div>
                
                <div id="userInfo" style="display: none; margin-top: 15px; padding: 12px; background: #e8f5e9; border-radius: 8px;">
                    <div style="font-weight: 600; color: #2e7d32;">👤 Logged In User</div>
                    <div id="userName" style="margin-top: 5px; font-size: 14px;"></div>
                    <div id="userEmail" style="font-size: 13px; color: #666;"></div>
                    <div id="userRole" style="font-size: 12px; color: #888; margin-top: 3px;"></div>
                </div>
                
                <div id="tokenDisplay" style="display: none;">
                    <div class="token-display"></div>
                </div>
                
                <div class="quick-accounts">
                    <h3>Quick Login</h3>
                    <button class="account-btn" onclick="quickLogin('john@example.com', 'password123')">Customer</button>
                    <button class="account-btn" onclick="quickLogin('lownl-jm22@student.tarc.edu.my', 'password123')">Vendor</button>
                </div>
            </div>
            
            <!-- API Testing Panel -->
            <div class="api-panel">
                <h2>📡 API Endpoints</h2>
                
                <div class="api-tabs">
                    <button class="tab-btn" onclick="showTab('student', this)" style="background: #fbbf24; color: #92400e; border-color: #fbbf24;">⭐ Student APIs</button>
                    <button class="tab-btn active" id="authTabBtn" onclick="showTab('auth', this)">Auth</button>
                    <button class="tab-btn" onclick="showTab('menu', this)">Menu</button>
                    <button class="tab-btn" onclick="showTab('cart', this)">Cart</button>
                    <button class="tab-btn" onclick="showTab('orders', this)">Orders</button>
                    <button class="tab-btn" onclick="showTab('notifications', this)">Notifications</button>
                    <button class="tab-btn" onclick="showTab('vouchers', this)">Vouchers</button>
                    <button class="tab-btn" onclick="showTab('vendor', this)">Vendor</button>
                    <button class="tab-btn" onclick="showTab('custom', this)">Custom</button>
                </div>
                
                <div id="endpointsContainer"></div>
                
                <div id="responsePanel" class="response-panel">
                    <div class="response-header">
                        <h4>Response</h4>
                        <button class="copy-btn" onclick="copyResponse()">Copy</button>
                    </div>
                    <div class="response-content">
                        <pre id="responseContent"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Parameter Modal -->
    <div id="paramModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close-btn" onclick="closeModal()">&times;</span>
                <h3 id="modalTitle">API Parameters</h3>
            </div>
            <div id="modalBody"></div>
            <button class="btn btn-primary" onclick="executeWithParams()">Execute</button>
        </div>
    </div>

    <script>
        let authToken = localStorage.getItem('foodhunter_token') || null;
        let csrfToken = '{{ csrf_token() }}';
        @if(auth()->check())
        let sessionUser = {
            id: {{ auth()->user()->id }},
            name: "{{ auth()->user()->name }}",
            email: "{{ auth()->user()->email }}",
            role: "{{ auth()->user()->role }}"
        };
        @else
        let sessionUser = null;
        @endif
        const baseUrl = window.location.origin;
        let currentEndpoint = null;
        
        const endpoints = {
            auth: [
                { method: 'POST', path: '/api/auth/login', name: 'Login', auth: false, params: { email: 'john@example.com', password: 'password123' } },
                { method: 'POST', path: '/api/auth/register', name: 'Register', auth: false, params: { name: 'John Doe', email: 'test@example.com', password: 'password123', password_confirmation: 'password123', phone: '0123456789' } },
                { method: 'GET', path: '/api/auth/user', name: 'Get User', auth: true },
                { method: 'POST', path: '/api/auth/logout', name: 'Logout', auth: true },
                { method: 'POST', path: '/api/auth/validate-token', name: '⭐ Validate Token (Ng Wayne Xiang API #1)', auth: true },
                { method: 'GET', path: '/api/auth/user-stats', name: '⭐ User Stats (Ng Wayne Xiang API #2)', auth: true }
            ],
            menu: [
                { method: 'GET', path: '/api/categories', name: 'List Categories', auth: false },
                { method: 'GET', path: '/api/vendors', name: 'List Vendors', auth: false },
                { method: 'GET', path: '/api/menu/featured', name: 'Featured Items', auth: false },
                { method: 'GET', path: '/api/menu/search?q=nasi', name: 'Search Menu', auth: false },
                { method: 'GET', path: '/api/menu/1', name: 'Get Menu Item', auth: false, pathParam: true },
                { method: 'GET', path: '/api/menu/1/availability', name: '⭐ Check Availability (Haerine Deepak Singh API #1)', auth: false, pathParam: true },
                { method: 'GET', path: '/api/menu/1/related', name: 'Related Items', auth: false, pathParam: true },
                { method: 'GET', path: '/api/menu/popular', name: '⭐ Popular Items (Haerine Deepak Singh API #2)', auth: false, params: { category_id: '', vendor_id: '', limit: 10 } }
            ],
            cart: [
                { method: 'GET', path: '/api/cart', name: 'Get Cart', auth: true },
                { method: 'POST', path: '/api/cart', name: 'Add to Cart', auth: true, params: { menu_item_id: 1, quantity: 2, special_instructions: 'No onions' } },
                { method: 'PUT', path: '/api/cart/1', name: 'Update Cart Item', auth: true, pathParam: true, params: { quantity: 3 } },
                { method: 'DELETE', path: '/api/cart/1', name: 'Remove Cart Item', auth: true, pathParam: true },
                { method: 'GET', path: '/api/cart/summary', name: '⭐ Cart Summary (Lee Song Yan API #1)', auth: true },
                { method: 'GET', path: '/api/cart/count', name: 'Cart Item Count', auth: true },
                { method: 'GET', path: '/api/cart/validate', name: 'Validate Cart', auth: true },
                { method: 'GET', path: '/api/cart/recommendations', name: 'Cart Recommendations', auth: true },
                { method: 'DELETE', path: '/api/cart', name: 'Clear Cart', auth: true }
            ],
            orders: [
                { method: 'GET', path: '/api/orders', name: 'List Orders', auth: true },
                { method: 'POST', path: '/api/orders', name: 'Create Order', auth: true, params: { payment_method: 'cash', notes: 'Please deliver fast' } },
                { method: 'GET', path: '/api/orders/active', name: 'Active Orders', auth: true },
                { method: 'GET', path: '/api/orders/history', name: 'Order History', auth: true },
                { method: 'GET', path: '/api/orders/1', name: 'Get Order', auth: true, pathParam: true },
                { method: 'GET', path: '/api/orders/1/status', name: '⭐ Order Status (Low Nam Lee API #1)', auth: true, pathParam: true },
                { method: 'POST', path: '/api/orders/validate-pickup', name: '⭐ Validate Pickup QR (Low Nam Lee API #2)', auth: true, params: { qr_code: 'PU-20251222-ABC123' } },
                { method: 'POST', path: '/api/orders/1/reorder', name: 'Reorder', auth: true, pathParam: true },
                { method: 'POST', path: '/api/orders/1/cancel', name: 'Cancel Order', auth: true, pathParam: true }
            ],
            notifications: [
                { method: 'GET', path: '/api/notifications', name: 'List Notifications', auth: true },
                { method: 'GET', path: '/api/notifications/dropdown', name: 'Dropdown Notifications', auth: true },
                { method: 'GET', path: '/api/notifications/unread-count', name: 'Unread Count', auth: true },
                { method: 'POST', path: '/api/notifications/1/read', name: 'Mark As Read', auth: true, pathParam: true },
                { method: 'POST', path: '/api/notifications/read-all', name: 'Mark All Read', auth: true },
                { method: 'POST', path: '/api/notifications/send', name: '⭐ Send Notification (Lee Song Yan API #2)', auth: true, params: { user_id: 1, type: 'order', title: 'Test Notification', message: 'This is a test notification message', data: { order_id: 123 } } },
                { method: 'DELETE', path: '/api/notifications/1', name: 'Delete Notification', auth: true, pathParam: true }
            ],
            vouchers: [
                { method: 'POST', path: '/api/vouchers/validate', name: '⭐ Validate Voucher (Lee Kin Hang API #1)', auth: true, params: { code: 'MAKC10OFF', subtotal: 50.00 } },
                { method: 'GET', path: '/api/vendors/1/availability', name: '⭐ Vendor Availability (Lee Kin Hang API #2)', auth: false, pathParam: true }
            ],
            vendor: [
                { method: 'GET', path: '/api/vendor/dashboard', name: 'Dashboard', auth: true },
                { method: 'POST', path: '/api/vendor/toggle-open', name: 'Toggle Store Open/Closed', auth: true },
                { method: 'GET', path: '/api/vendor/dashboard/top-items', name: 'Top Selling Items', auth: true },
                { method: 'GET', path: '/api/vendor/dashboard/recent-orders', name: 'Recent Orders', auth: true },
                { method: 'GET', path: '/api/vendor/reports/revenue?period=week', name: 'Revenue Report', auth: true },
                { method: 'GET', path: '/api/vendor/menu', name: 'Menu Items', auth: true },
                { method: 'GET', path: '/api/vendor/menu/1', name: 'Get Menu Item', auth: true, pathParam: true },
                { method: 'POST', path: '/api/vendor/menu', name: 'Create Menu Item', auth: true, params: { name: 'New Item', price: 10.00, category_id: 1, description: 'Delicious item' } },
                { method: 'PUT', path: '/api/vendor/menu/1', name: 'Update Menu Item', auth: true, pathParam: true, params: { name: 'Updated Item', price: 12.00 } },
                { method: 'DELETE', path: '/api/vendor/menu/1', name: 'Delete Menu Item', auth: true, pathParam: true },
                { method: 'POST', path: '/api/vendor/menu/1/toggle', name: 'Toggle Item Availability', auth: true, pathParam: true },
                { method: 'GET', path: '/api/vendor/categories', name: 'Menu Categories', auth: true },
                { method: 'GET', path: '/api/vendor/orders', name: 'Orders', auth: true },
                { method: 'GET', path: '/api/vendor/orders/pending', name: 'Pending Orders', auth: true },
                { method: 'GET', path: '/api/vendor/orders/1', name: 'Get Order Details', auth: true, pathParam: true },
                { method: 'PUT', path: '/api/vendor/orders/1/status', name: 'Update Order Status', auth: true, pathParam: true, params: { status: 'preparing' } },
                { method: 'GET', path: '/api/vendor/vouchers', name: 'Vouchers', auth: true },
                { method: 'POST', path: '/api/vendor/vouchers', name: 'Create Voucher', auth: true, params: { code: 'TEST20', name: 'Test Voucher', type: 'percentage', value: 20, min_order: 30 } },
                { method: 'PUT', path: '/api/vendor/vouchers/1', name: 'Update Voucher', auth: true, pathParam: true, params: { name: 'Updated Voucher', value: 25 } },
                { method: 'DELETE', path: '/api/vendor/vouchers/1', name: 'Delete Voucher', auth: true, pathParam: true }
            ],
            student: [
                // Ng Wayne Xiang: User & Authentication Module
                { method: 'POST', path: '/api/auth/validate-token', name: '⭐ Ng Wayne Xiang: Validate Token API', auth: true, description: 'Validates API token and returns user info' },
                { method: 'GET', path: '/api/auth/user-stats', name: '⭐ Ng Wayne Xiang: User Stats API', auth: true, description: 'Returns user statistics (orders, spending)' },
                // Haerine Deepak Singh: Menu & Catalog Module
                { method: 'GET', path: '/api/menu/1/availability', name: '⭐ Haerine Deepak Singh: Item Availability API', auth: false, pathParam: true, description: 'Checks if menu item is available' },
                { method: 'GET', path: '/api/menu/popular', name: '⭐ Haerine Deepak Singh: Popular Items API', auth: false, params: { category_id: '', vendor_id: '', limit: 10 }, description: 'Returns popular items by sales' },
                // Low Nam Lee: Order & Pickup Module
                { method: 'GET', path: '/api/orders/1/status', name: '⭐ Low Nam Lee: Order Status API', auth: true, pathParam: true, description: 'Returns real-time order status and pickup info' },
                { method: 'POST', path: '/api/orders/validate-pickup', name: '⭐ Low Nam Lee: Pickup QR Validation API', auth: true, params: { qr_code: 'PU-20251222-ABC123' }, description: 'Validates pickup QR code for order collection' },
                // Lee Song Yan: Cart, Checkout & Notifications Module
                { method: 'GET', path: '/api/cart/summary', name: '⭐ Lee Song Yan: Cart Summary API', auth: true, description: 'Returns cart totals and item count' },
                { method: 'POST', path: '/api/notifications/send', name: '⭐ Lee Song Yan: Send Notification API', auth: true, params: { user_id: 1, type: 'order', title: 'Test Notification', message: 'This is a test notification', data: { order_id: 123 } }, description: 'Sends in-app notification to user' },
                // Lee Kin Hang: Vendor Management Module
                { method: 'POST', path: '/api/vouchers/validate', name: '⭐ Lee Kin Hang: Voucher Validation API', auth: true, params: { code: 'MAKC10OFF', subtotal: 50.00 }, description: 'Validates voucher and calculates discount' },
                { method: 'GET', path: '/api/vendors/1/availability', name: '⭐ Lee Kin Hang: Vendor Availability API', auth: false, pathParam: true, description: 'Returns vendor open/closed status and hours' }
            ],
            custom: []
        };
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Check if user is already logged in via session
            if (sessionUser) {
                updateAuthStatus(true, sessionUser);
            } else if (authToken) {
                updateAuthStatus(true);
            }
            // Show default tab
            showTab('auth', document.getElementById('authTabBtn'));
        });
        
        function showTab(tab, btnElement) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            if (btnElement) {
                btnElement.classList.add('active');
            }
            
            const container = document.getElementById('endpointsContainer');
            container.innerHTML = '';
            
            if (tab === 'custom') {
                showCustomRequestForm();
                return;
            }
            
            const tabEndpoints = endpoints[tab] || [];
            if (tabEndpoints.length === 0) {
                container.innerHTML = '<p>No endpoints available</p>';
                return;
            }
            
            const group = document.createElement('div');
            group.className = 'endpoint-group';
            group.innerHTML = `<h3>${tab.charAt(0).toUpperCase() + tab.slice(1)} APIs</h3>`;
            
            tabEndpoints.forEach(endpoint => {
                const item = document.createElement('div');
                item.className = 'endpoint-item';
                const isStudentApi = endpoint.name.includes('⭐');
                item.innerHTML = `
                    <div class="endpoint-header">
                        <div style="flex: 1;">
                            <span class="method-badge method-${endpoint.method.toLowerCase()}">${endpoint.method}</span>
                            <span class="endpoint-path">${endpoint.path}</span>
                            <div style="margin-top: 5px; font-size: 13px; color: ${isStudentApi ? '#d97706' : '#6b7280'}; font-weight: ${isStudentApi ? '600' : '400'};">${endpoint.name}</div>
                        </div>
                        <button class="test-btn" onclick='testEndpoint(${JSON.stringify(endpoint)})'>Test</button>
                    </div>
                `;
                group.appendChild(item);
            });
            
            container.appendChild(group);
        }
        
        function showCustomRequestForm() {
            const container = document.getElementById('endpointsContainer');
            container.innerHTML = `
                <div class="endpoint-group">
                    <h3>🔧 Custom API Request</h3>
                    <div style="background: #f9fafb; border: 2px solid #e5e7eb; border-radius: 8px; padding: 20px;">
                        <div class="param-group">
                            <label>HTTP Method</label>
                            <select id="customMethod" style="width: 100%; padding: 10px; border: 2px solid #e5e7eb; border-radius: 6px;">
                                <option value="GET">GET</option>
                                <option value="POST">POST</option>
                                <option value="PUT">PUT</option>
                                <option value="DELETE">DELETE</option>
                            </select>
                        </div>
                        
                        <div class="param-group">
                            <label>URL (Full URL including http://)</label>
                            <input type="text" id="customUrl" placeholder="http://127.0.0.1:8000/api/vendor/dashboard" style="width: 100%; padding: 10px; border: 2px solid #e5e7eb; border-radius: 6px; font-family: monospace;" />
                        </div>
                        
                        <div class="param-group">
                            <label>Request Body (JSON) - Optional</label>
                            <textarea id="customBody" placeholder='{"key": "value"}' style="width: 100%; min-height: 100px; padding: 10px; border: 2px solid #e5e7eb; border-radius: 6px; font-family: monospace;"></textarea>
                        </div>
                        
                        <div class="param-group">
                            <label style="display: flex; align-items: center; gap: 10px;">
                                <input type="checkbox" id="customAuth" checked />
                                <span>Use Authentication Token</span>
                            </label>
                        </div>
                        
                        <button class="btn btn-primary" onclick="executeCustomRequest()" style="margin-top: 10px;">Execute Request</button>
                    </div>
                </div>
            `;
        }
        
        async function executeCustomRequest() {
            const method = document.getElementById('customMethod').value;
            const url = document.getElementById('customUrl').value;
            const bodyText = document.getElementById('customBody').value;
            const useAuth = document.getElementById('customAuth').checked;
            
            if (!url) {
                alert('Please enter a URL');
                return;
            }
            
            try {
                const options = {
                    method: method,
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'include'
                };
                
                if (useAuth && authToken) {
                    options.headers['Authorization'] = `Bearer ${authToken}`;
                }
                
                if (method === 'POST' || method === 'PUT' || method === 'DELETE') {
                    options.headers['Content-Type'] = 'application/json';
                    if (bodyText) {
                        options.body = bodyText;
                    }
                }
                
                const response = await fetch(url, options);
                const data = await response.json();
                
                showResponse(data, response.status);
            } catch (error) {
                showResponse({ error: error.message }, 500);
            }
        }
        
        async function login(event) {
            if (event) event.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                alert('Please enter email and password');
                return;
            }
            
            try {
                // Use session-based web login with CSRF token
                const response = await fetch(`${baseUrl}/login`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'include',
                    body: JSON.stringify({ email, password })
                });
                
                const data = await response.json();
                
                if (response.ok && (data.success || data.redirect)) {
                    sessionUser = data.user;
                    updateAuthStatus(true, data.user);
                    showResponse({ success: true, message: 'Logged in successfully', user: data.user });
                    
                    // Reload page to get fresh CSRF token
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    alert(data.message || data.errors?.email?.[0] || 'Login failed');
                    showResponse(data);
                }
            } catch (error) {
                alert('Login error: ' + error.message);
            }
        }
        
        function quickLogin(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
            login(null);
        }
        
        async function logout() {
            // Logout from session
            try {
                await fetch(`${baseUrl}/logout`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'include'
                });
            } catch (e) {
                console.error('Logout error:', e);
            }
            
            authToken = null;
            sessionUser = null;
            localStorage.removeItem('foodhunter_token');
            updateAuthStatus(false);
            document.getElementById('responsePanel').classList.remove('show');
            
            // Reload page to get fresh state
            window.location.reload();
        }
        
        function updateAuthStatus(authenticated, user = null) {
            const statusEl = document.getElementById('authStatus');
            const tokenEl = document.getElementById('tokenDisplay');
            const userInfoEl = document.getElementById('userInfo');
            
            if (authenticated) {
                // Show different status for token vs session auth
                const authType = authToken ? 'Token' : 'Session';
                statusEl.innerHTML = `<span class="status-badge status-authenticated">✓ Authenticated (${authType})</span>`;
                
                if (user) {
                    userInfoEl.style.display = 'block';
                    document.getElementById('userName').textContent = user.name;
                    document.getElementById('userEmail').textContent = user.email;
                    document.getElementById('userRole').textContent = 'Role: ' + (user.role || 'customer');
                } else {
                    userInfoEl.style.display = 'none';
                }
                
                if (authToken) {
                    tokenEl.style.display = 'block';
                    tokenEl.querySelector('.token-display').textContent = 'API Token: ' + authToken.substring(0, 50) + '...';
                } else {
                    tokenEl.style.display = 'none';
                }
            } else {
                statusEl.innerHTML = '<span class="status-badge status-unauthenticated">Not Authenticated</span>';
                tokenEl.style.display = 'none';
                userInfoEl.style.display = 'none';
            }
        }
        
        async function testEndpoint(endpoint) {
            if (endpoint.auth && !authToken && !sessionUser) {
                alert('Please login first to test this endpoint');
                return;
            }
            
            if (endpoint.params || endpoint.pathParam) {
                currentEndpoint = endpoint;
                showParamModal(endpoint);
                return;
            }
            
            await executeEndpoint(endpoint);
        }
        
        function showParamModal(endpoint) {
            const modal = document.getElementById('paramModal');
            const modalBody = document.getElementById('modalBody');
            const modalTitle = document.getElementById('modalTitle');
            
            modalTitle.textContent = `${endpoint.method} ${endpoint.path}`;
            modalBody.innerHTML = '';
            
            if (endpoint.pathParam) {
                const idMatch = endpoint.path.match(/\/(\d+)/);
                if (idMatch) {
                    modalBody.innerHTML += `
                        <div class="param-group">
                            <label>ID (in path)</label>
                            <input type="number" id="pathId" value="${idMatch[1]}" />
                        </div>
                    `;
                }
            }
            
            if (endpoint.params) {
                modalBody.innerHTML += `
                    <div class="param-group">
                        <label>Request Body (JSON)</label>
                        <textarea id="requestBody">${JSON.stringify(endpoint.params, null, 2)}</textarea>
                    </div>
                `;
            }
            
            modal.classList.add('show');
        }
        
        function closeModal() {
            document.getElementById('paramModal').classList.remove('show');
        }
        
        async function executeWithParams() {
            if (!currentEndpoint) return;
            
            let endpoint = { ...currentEndpoint };
            
            if (endpoint.pathParam) {
                const pathId = document.getElementById('pathId')?.value;
                if (pathId) {
                    endpoint.path = endpoint.path.replace(/\/\d+/, `/${pathId}`);
                }
            }
            
            if (endpoint.params) {
                try {
                    const bodyText = document.getElementById('requestBody').value;
                    endpoint.params = JSON.parse(bodyText);
                } catch (e) {
                    alert('Invalid JSON in request body');
                    return;
                }
            }
            
            closeModal();
            await executeEndpoint(endpoint);
        }
        
        async function executeEndpoint(endpoint) {
            try {
                const options = {
                    method: endpoint.method,
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'include'
                };
                
                if (endpoint.auth && authToken) {
                    options.headers['Authorization'] = `Bearer ${authToken}`;
                }
                
                if (endpoint.method === 'POST' || endpoint.method === 'PUT' || endpoint.method === 'DELETE') {
                    options.headers['Content-Type'] = 'application/json';
                    if (endpoint.params) {
                        options.body = JSON.stringify(endpoint.params);
                    }
                }
                
                const response = await fetch(`${baseUrl}${endpoint.path}`, options);
                const data = await response.json();
                
                // Handle login response - store token and update auth status
                if (endpoint.path === '/api/auth/login' && data.success && data.data?.token) {
                    authToken = data.data.token;
                    localStorage.setItem('foodhunter_token', authToken);
                    sessionUser = data.data.user;
                    updateAuthStatus(true, data.data.user);
                }
                
                // Handle logout response - clear token and auth status
                if (endpoint.path === '/api/auth/logout' && data.success) {
                    authToken = null;
                    sessionUser = null;
                    localStorage.removeItem('foodhunter_token');
                    updateAuthStatus(false);
                }
                
                showResponse(data, response.status);
            } catch (error) {
                showResponse({ error: error.message }, 500);
            }
        }
        
        function showResponse(data, status = 200) {
            const panel = document.getElementById('responsePanel');
            const content = document.getElementById('responseContent');
            
            const statusColor = status >= 200 && status < 300 ? '#10b981' : '#ef4444';
            const formatted = JSON.stringify(data, null, 2);
            
            content.innerHTML = `<span style="color: ${statusColor}">Status: ${status}</span>\n\n${formatted}`;
            panel.classList.add('show');
            
            // Scroll to response
            panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
        
        function copyResponse() {
            const content = document.getElementById('responseContent').textContent;
            navigator.clipboard.writeText(content);
            alert('Response copied to clipboard!');
        }
    </script>
</body>
</html>

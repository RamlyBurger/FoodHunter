{{--
|==============================================================================
| Change Password Page - Ng Wayne Xiang (User & Authentication Module)
|==============================================================================
|
| @author     Ng Wayne Xiang
| @module     User & Authentication Module
|
| Password change with current password verification.
| Enforces password complexity per OWASP guidelines.
|==============================================================================
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Change Password - FoodHunter</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/images/tarIco.ico">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Open Sans', sans-serif;
        }
        body.login-layout {
            font-family: 'Open Sans', sans-serif;
            font-size: 13px;
            color: #393939;
            line-height: 1.5;
        }
        body.login-layout.light-login {
            background-image: url('https://web.tarc.edu.my/portal/assets/css/images/bg3.jpg');
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            min-height: 100vh;
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
        .navbar-brand {
            font-weight: 700;
            font-size: 1.25rem;
            color: #007AFF !important;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .navbar-brand img {
            height: 32px;
        }
        .nav-link {
            font-weight: 500;
            font-size: 0.9rem;
            padding: 0.5rem 1rem !important;
            border-radius: 8px;
            transition: all 0.2s ease;
            color: #1C1C1E !important;
        }
        .nav-link:hover {
            color: #007AFF !important;
        }
        .navbar-nav .nav-item {
            margin: 0 2px;
        }
        .main-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            padding-top: 80px;
        }
        .main-content {
            width: 100%;
            max-width: 460px;
        }
        .login-container {
            margin-top: 20px;
        }
        .login-box {
            font-family: 'Open Sans', sans-serif;
            font-size: 13px;
            color: #393939;
            line-height: 1.5;
            margin: 3px 0;
            border-width: 0;
            padding: 1px 1px 0;
            box-shadow: 0 0 2px 1px rgba(0, 0, 0, 0.12);
            border-bottom: 1px solid rgba(50, 50, 50, 0.33);
            position: relative;
            background-color: rgba(100, 110, 120, 0.4);
        }
        .widget-box {
            border: none;
        }
        .widget-body {
            font-family: 'Open Sans', sans-serif;
            font-size: 13px;
            color: #393939;
            line-height: 1.5;
            background-color: #FFF;
        }
        .widget-main {
            font-family: 'Open Sans', sans-serif;
            font-size: 13px;
            color: #393939;
            line-height: 1.5;
            padding: 16px 36px 36px;
            background: #F7F7F7;
            padding-bottom: 16px;
        }
        .header.blue.lighter.bigger {
            font-family: 'Open Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-weight: lighter;
            color: #478fca !important;
            line-height: 28px;
            margin-bottom: 16px;
            margin-top: 18px;
            padding-bottom: 4px;
            border-bottom: 1px solid #d5e3ef;
            font-size: 19px;
        }
        .header.blue.lighter.bigger i {
            color: #87b87f;
            margin-right: 8px;
        }
        .block.clearfix {
            display: block;
            margin-bottom: 15px;
        }
        .block.input-icon.input-icon-right {
            position: relative;
            display: block;
        }
        .block.input-icon.input-icon-right input {
            display: block;
            width: 100%;
            height: 34px;
            line-height: 1.42857143;
            background-image: none;
            transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
            border-radius: 0 !important;
            color: #858585;
            background-color: #ffffff;
            border: 1px solid #d5d5d5;
            padding: 5px 4px 6px;
            font-size: 14px;
            font-family: inherit;
            box-shadow: none !important;
            transition-duration: 0.1s;
            padding-left: 6px;
            padding-right: 24px;
        }
        .block.input-icon.input-icon-right input:focus {
            border-color: #f59942;
            outline: none;
            box-shadow: none !important;
        }
        .block.input-icon.input-icon-right input::placeholder {
            color: #858585;
        }
        .block.input-icon.input-icon-right i {
            font: normal normal normal 14px/1 FontAwesome;
            text-rendering: auto;
            -webkit-font-smoothing: antialiased;
            text-align: center;
            padding: 0 3px;
            z-index: 2;
            position: absolute;
            top: 1px;
            bottom: 1px;
            line-height: 30px;
            display: inline-block;
            color: #909090;
            font-size: 16px;
            left: auto;
            right: 3px;
        }
        .space-6 { height: 6px; }
        .space-4 { height: 4px; }
        .space { height: 18px; }
        .btn-primary, .btn.btn-sm.btn-primary {
            font-weight: normal;
            text-align: center;
            white-space: nowrap;
            float: right !important;
            display: inline-block;
            color: #FFF !important;
            text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
            background-image: none !important;
            border: 4px solid #FFF;
            border-radius: 0;
            box-shadow: none !important;
            cursor: pointer;
            font-size: 13px;
            padding: 4px 9px;
            line-height: 1.38;
            background-color: #428bca !important;
            border-color: #428bca;
        }
        .btn-primary:hover {
            background-color: #3276b1 !important;
            border-color: #285e8e;
        }
        .width-35 { width: 35%; }
        .bigger-110 { font-size: 110%; }
        .toolbar {
            background: #5090C1;
            padding: 9px 18px;
            border-top: none;
        }
        .toolbar a {
            font-family: 'Open Sans', sans-serif;
            line-height: 1.5;
            text-align: left;
            background-color: transparent;
            outline: 0;
            text-decoration: none;
            color: #FE9;
            margin-left: 11px;
            font-size: 15px;
            font-weight: 400;
            text-shadow: 1px 0px 1px rgba(0, 0, 0, 0.25);
        }
        .toolbar a:hover {
            text-decoration: underline;
            color: #FE9;
        }
        .toolbar a i {
            color: #FE9;
        }
    </style>
</head>
<body class="login-layout light-login">
    <div class="main-container">
        <div class="main-content">
            <div class="login-container">
                <div class="login-box widget-box no-border">
                    <div class="widget-body">
                        <div class="widget-main">
                            <h4 class="header blue lighter bigger">
                                <i class="fa fa-lock"></i>
                                Change Password
                            </h4>

                            <div class="space-6"></div>

                            <p style="color: #666; margin-bottom: 20px;">
                                Enter your current password and choose a new password. An OTP will be sent to your email for verification.
                            </p>

                            <form id="changePasswordForm">
                                @csrf
                                
                                <label class="block clearfix">
                                    <span class="block input-icon input-icon-right">
                                        <input type="password" name="current_password" id="current_password" placeholder="Current Password" required />
                                        <i class="fa fa-lock"></i>
                                    </span>
                                </label>

                                <div class="space-4"></div>

                                <label class="block clearfix">
                                    <span class="block input-icon input-icon-right">
                                        <input type="password" name="new_password" id="new_password" placeholder="New Password (min 8 characters)" required minlength="8" />
                                        <i class="fa fa-key"></i>
                                    </span>
                                </label>

                                <div class="space-4"></div>

                                <label class="block clearfix">
                                    <span class="block input-icon input-icon-right">
                                        <input type="password" name="new_password_confirmation" id="new_password_confirmation" placeholder="Confirm New Password" required minlength="8" />
                                        <i class="fa fa-key"></i>
                                    </span>
                                </label>

                                <div class="space"></div>

                                <div class="clearfix">
                                    <button type="submit" class="width-35 btn btn-sm btn-primary" id="submitBtn">
                                        <i class="fa fa-arrow-right"></i>
                                        <span class="bigger-110">Next</span>
                                    </button>
                                </div>

                                <div class="space-4"></div>
                            </form>
                        </div>

                        <div class="toolbar clearfix">
                            <div>
                                <a href="javascript:void(0)" onclick="goBackToProfile()">
                                    <i class="fa fa-arrow-left"></i>
                                    Back to Profile
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        function goBackToProfile() {
            // Use window.history.back() to avoid CSRF token issues
            window.history.back();
        }

        document.getElementById('changePasswordForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const currentPassword = document.getElementById('current_password').value;
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('new_password_confirmation').value;
            const submitBtn = document.getElementById('submitBtn');

            // Frontend validation
            if (newPassword !== confirmPassword) {
                Swal.fire({
                    icon: 'error',
                    title: 'Password Mismatch',
                    text: 'New password and confirmation do not match.',
                    confirmButtonColor: '#428bca'
                });
                return;
            }

            if (newPassword.length < 8) {
                Swal.fire({
                    icon: 'error',
                    title: 'Password Too Short',
                    text: 'New password must be at least 8 characters long.',
                    confirmButtonColor: '#428bca'
                });
                return;
            }

            if (currentPassword === newPassword) {
                Swal.fire({
                    icon: 'error',
                    title: 'Same Password',
                    text: 'New password must be different from current password.',
                    confirmButtonColor: '#428bca'
                });
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> <span class="bigger-110">Processing...</span>';

            try {
                const response = await fetch('/auth/change-password/request', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        current_password: currentPassword,
                        new_password: newPassword,
                        new_password_confirmation: confirmPassword
                    })
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'OTP Sent!',
                        text: 'An OTP has been sent to your email. Redirecting...',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '/auth/change-password/verify';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to process request.',
                        confirmButtonColor: '#428bca'
                    });
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fa fa-arrow-right"></i> <span class="bigger-110">Next</span>';
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred. Please try again.',
                    confirmButtonColor: '#428bca'
                });
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa fa-arrow-right"></i> <span class="bigger-110">Next</span>';
            }
        });
    </script>
</body>
</html>

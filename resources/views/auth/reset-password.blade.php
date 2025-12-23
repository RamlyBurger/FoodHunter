<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password - FoodHunter</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('foodhunter-logo.png') }}">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    
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
        .center { text-align: center; }
        .light.black, #id-text2 {
            line-height: 1.1;
            font-size: 32px;
            font-weight: normal;
            font-family: 'Open Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: rgb(51, 51, 51);
            white-space: nowrap;
        }
        .ace-icon { margin-right: 5px; }
        .green { color: #87b87f; }
        h1 { margin-bottom: 0; }
        h1 img { margin-bottom: 0; }
        .small { font-size: 0.85rem; }
        .black { color: #333; }
        a { color: #0088cc; }
        .disclaimer-links {
            font-family: 'Open Sans', sans-serif;
            color: #393939;
            line-height: 1.5;
            text-align: center !important;
            font-size: 85%;
        }
        .disclaimer-links a {
            background-color: transparent;
            outline: 0;
            color: #23527c;
            text-decoration: none;
        }
        .disclaimer-links a:hover {
            text-decoration: underline;
        }
        .info-text {
            color: #666;
            font-size: 13px;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        .password-requirements {
            background: #f0f4f7;
            border-radius: 4px;
            padding: 12px;
            margin-bottom: 15px;
            font-size: 12px;
        }
        .password-requirements ul {
            margin: 0;
            padding-left: 20px;
            color: #666;
        }
        .password-requirements li {
            margin-bottom: 4px;
        }
        .password-strength {
            margin-bottom: 15px;
        }
        .password-strength .progress {
            height: 8px;
            margin-bottom: 5px;
            background-color: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
        }
        .password-strength .progress-bar {
            transition: width 0.3s ease, background-color 0.3s ease;
            height: 100%;
        }
        .password-strength .progress-bar.weak {
            background-color: #dc3545;
        }
        .password-strength .progress-bar.medium {
            background-color: #ffc107;
        }
        .password-strength .progress-bar.strong {
            background-color: #28a745;
        }
    </style>
</head>
<body class="login-layout light-login">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                <img src="{{ asset('foodhunter-logo.png') }}" alt="FoodHunter">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/menu') }}">Menu</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/login') }}">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/register') }}">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <div class="main-content">
            <div class="row">
                <div class="col-sm-10 col-sm-offset-1">
                    <h1 align="center">
                        <a href="{{ url('/') }}"><img src="https://web.tarc.edu.my/portal/images/tarumt-logo.png" width="250" alt="TARUMT"></a><br>
                        <span class="light black" id="id-text2">Canteen Ordering System</span>
                    </h1>

                    <div class="login-container">
                        <div class="space-6"></div>

                        <div class="position-relative">
                            <div id="reset-box" class="login-box visible widget-box no-border">
                                <div class="widget-body">
                                    <div class="widget-main">
                                        <h4 class="header blue lighter bigger">
                                            <i class="ace-icon fa fa-lock green"></i>
                                            Create New Password
                                        </h4>

                                        <div class="space-6"></div>

                                        <p class="info-text">
                                            Please enter your new password below.
                                        </p>

                                        <div class="password-requirements">
                                            <strong>Password Requirements:</strong>
                                            <ul>
                                                <li>At least 8 characters long</li>
                                                <li>Both passwords must match</li>
                                            </ul>
                                        </div>

                                        <form action="{{ url('/reset-password') }}" method="POST" autocomplete="off">
                                            @csrf
                                            <input type="hidden" name="token" value="{{ $token }}">
                                            <input type="hidden" name="email" value="{{ $email }}">
                                            
                                            <fieldset>
                                                <label class="block clearfix">
                                                    <span class="block input-icon input-icon-right">
                                                        <input type="password" name="password" id="password" class="form-control" placeholder="New Password" required minlength="8">
                                                        <i class="ace-icon fa fa-lock"></i>
                                                    </span>
                                                </label>
                                                <div class="password-strength">
                                                    <div class="progress">
                                                        <div id="strengthBar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                                                    </div>
                                                    <small id="strengthText" class="text-muted">Enter a password</small>
                                                </div>

                                                <div class="space-6"></div>

                                                <label class="block clearfix">
                                                    <span class="block input-icon input-icon-right">
                                                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Confirm New Password" required minlength="8">
                                                        <i class="ace-icon fa fa-lock"></i>
                                                    </span>
                                                </label>
                                                <small id="matchText" class="text-muted"></small>

                                                <div class="space"></div>

                                                <div class="clearfix">
                                                    <button type="submit" class="width-45 pull-right btn btn-sm btn-primary">
                                                        <i class="ace-icon fa fa-check"></i>
                                                        <span class="bigger-110">Reset Password</span>
                                                    </button>
                                                </div>

                                                <div class="space-4"></div>
                                            </fieldset>
                                        </form>
                                    </div>

                                    <div class="toolbar clearfix">
                                        <div>
                                            <a href="{{ url('/login') }}">
                                                <i class="ace-icon fa fa-arrow-left"></i>
                                                Back to Login
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="center disclaimer-links" style="margin-top: 20px;">
                        <span>
                            <a href="{{ url('/') }}">HOME</a> | 
                            <a href="{{ url('/menu') }}">MENU</a> | 
                            COPYRIGHT © {{ date('Y') }} FOODHUNTER. ALL RIGHTS RESERVED
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="{{ asset('css/sweetalert2.min.css') }}">
    <script src="{{ asset('js/sweetalert2.min.js') }}"></script>
    <script>
    function checkPasswordStrength(password) {
        let strength = 0;
        let feedback = [];
        
        if (password.length >= 8) strength += 25;
        else feedback.push('8+ characters');
        
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
        const password = document.getElementById('password');
        const confirm = document.getElementById('password_confirmation');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');
        const matchText = document.getElementById('matchText');
        
        password.addEventListener('input', function() {
            const value = this.value;
            const result = checkPasswordStrength(value);
            
            strengthBar.style.width = result.strength + '%';
            strengthBar.className = 'progress-bar';
            
            if (result.strength < 50) {
                strengthBar.classList.add('weak');
                strengthText.textContent = 'Weak - Need: ' + result.feedback.join(', ');
                strengthText.style.color = '#dc3545';
            } else if (result.strength < 80) {
                strengthBar.classList.add('medium');
                strengthText.textContent = 'Medium - Add: ' + result.feedback.join(', ');
                strengthText.style.color = '#ffc107';
            } else {
                strengthBar.classList.add('strong');
                strengthText.textContent = 'Strong password!';
                strengthText.style.color = '#28a745';
            }
        });
        
        confirm.addEventListener('input', function() {
            if (this.value === '') {
                matchText.textContent = '';
                return;
            }
            
            if (this.value === password.value) {
                matchText.textContent = '✓ Passwords match';
                matchText.style.color = '#28a745';
            } else {
                matchText.textContent = '✗ Passwords do not match';
                matchText.style.color = '#dc3545';
            }
        });

        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Error',
                html: '{!! implode("<br>", $errors->all()) !!}',
                confirmButtonColor: '#FF9500'
            });
        @endif
    });
    </script>
</body>
</html>

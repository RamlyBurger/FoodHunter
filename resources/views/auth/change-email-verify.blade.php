<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify Email Change - FoodHunter</title>
    
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
        .width-40 { width: 40%; }
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
        .otp-input {
            width: 50px;
            height: 50px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin: 0 5px;
            border: 2px solid #d5d5d5;
            border-radius: 8px;
        }
        .otp-input:focus {
            border-color: #f59942;
            outline: none;
        }
        .otp-container {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }
        .resend-link {
            color: #428bca;
            cursor: pointer;
            text-decoration: none;
        }
        .resend-link:hover {
            text-decoration: underline;
        }
        .resend-link.disabled {
            color: #999;
            cursor: not-allowed;
            pointer-events: none;
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
                                <i class="fa fa-shield"></i>
                                Verify Email Change
                            </h4>

                            <div class="space-6"></div>

                            <p style="color: #666; margin-bottom: 20px; text-align: center;">
                                We've sent a 6-digit code to your new email.<br>
                                Please enter it below to confirm the change.
                            </p>

                            <form id="verifyOtpForm">
                                @csrf
                                
                                <div class="otp-container">
                                    <input type="text" class="otp-input" maxlength="1" id="otp1" required>
                                    <input type="text" class="otp-input" maxlength="1" id="otp2" required>
                                    <input type="text" class="otp-input" maxlength="1" id="otp3" required>
                                    <input type="text" class="otp-input" maxlength="1" id="otp4" required>
                                    <input type="text" class="otp-input" maxlength="1" id="otp5" required>
                                    <input type="text" class="otp-input" maxlength="1" id="otp6" required>
                                </div>

                                <div class="space"></div>

                                <div style="text-align: center; margin-bottom: 20px;">
                                    <span style="color: #666;">Didn't receive the code? </span>
                                    <a href="#" class="resend-link" id="resendLink">Resend OTP</a>
                                    <span id="countdown" style="color: #999; display: none;"></span>
                                </div>

                                <div class="clearfix">
                                    <button type="submit" class="width-40 btn btn-sm btn-primary" id="submitBtn">
                                        <i class="fa fa-check"></i>
                                        <span class="bigger-110">Verify</span>
                                    </button>
                                </div>

                                <div class="space-4"></div>
                            </form>
                        </div>

                        <div class="toolbar clearfix">
                            <div>
                                <a href="/auth/change-email">
                                    <i class="fa fa-arrow-left"></i>
                                    Back
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
    <!-- Supabase -->
    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
    
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const SUPABASE_URL = '{{ config('services.supabase.url') }}';
        const SUPABASE_ANON_KEY = '{{ config('services.supabase.anon_key') }}';
        const supabaseClient = window.supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);
        const newEmail = '{{ $email }}';
        let resendCountdown = 60;
        let countdownInterval;

        // Auto-focus and auto-advance OTP inputs
        const otpInputs = document.querySelectorAll('.otp-input');
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', function(e) {
                if (this.value.length === 1 && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && this.value === '' && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });

            // Only allow numbers
            input.addEventListener('keypress', function(e) {
                if (!/[0-9]/.test(e.key)) {
                    e.preventDefault();
                }
            });
        });

        // Auto-focus first input
        otpInputs[0].focus();

        // Start countdown
        function startCountdown() {
            const resendLink = document.getElementById('resendLink');
            const countdown = document.getElementById('countdown');
            
            resendLink.classList.add('disabled');
            resendLink.style.display = 'none';
            countdown.style.display = 'inline';
            
            resendCountdown = 60;
            countdown.textContent = `(Resend in ${resendCountdown}s)`;
            
            countdownInterval = setInterval(() => {
                resendCountdown--;
                countdown.textContent = `(Resend in ${resendCountdown}s)`;
                
                if (resendCountdown <= 0) {
                    clearInterval(countdownInterval);
                    resendLink.classList.remove('disabled');
                    resendLink.style.display = 'inline';
                    countdown.style.display = 'none';
                }
            }, 1000);
        }

        // Send OTP via Supabase
        async function sendOtpEmail(isResend = false) {
            try {
                const { error } = await supabaseClient.auth.signInWithOtp({ email: newEmail });
                
                if (error) {
                    console.error('Supabase OTP error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Failed to send OTP.',
                        confirmButtonColor: '#428bca'
                    });
                } else {
                    console.log('Supabase OTP sent successfully');
                    if (isResend) {
                        Swal.fire({
                            icon: 'success',
                            title: 'OTP Resent',
                            text: 'A new OTP has been sent to your new email.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                    startCountdown();
                }
            } catch (err) {
                console.error('Supabase OTP exception:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to send OTP. Please try again.',
                    confirmButtonColor: '#428bca'
                });
            }
        }

        // Auto-send OTP on page load
        sendOtpEmail(false);

        // Resend OTP
        document.getElementById('resendLink').addEventListener('click', async function(e) {
            e.preventDefault();
            
            if (this.classList.contains('disabled')) return;

            // Update session expiry on backend
            try {
                const response = await fetch('/auth/change-email/resend-otp', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                if (data.success) {
                    // Send OTP via Supabase
                    sendOtpEmail(true);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Session expired.',
                        confirmButtonColor: '#428bca'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred. Please try again.',
                    confirmButtonColor: '#428bca'
                });
            }
        });

        // Verify OTP
        document.getElementById('verifyOtpForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const otp = Array.from(otpInputs).map(input => input.value).join('');
            const submitBtn = document.getElementById('submitBtn');

            if (otp.length !== 6) {
                Swal.fire({
                    icon: 'error',
                    title: 'Incomplete OTP',
                    text: 'Please enter all 6 digits.',
                    confirmButtonColor: '#428bca'
                });
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> <span class="bigger-110">Verifying...</span>';

            try {
                const response = await fetch('/auth/change-email/verify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ otp: otp })
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Email Changed!',
                        text: 'Your email has been successfully changed. Redirecting...',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = data.redirect || '/profile';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid OTP',
                        text: data.message || 'The OTP you entered is incorrect.',
                        confirmButtonColor: '#428bca'
                    });
                    
                    // Clear OTP inputs
                    otpInputs.forEach(input => input.value = '');
                    otpInputs[0].focus();
                    
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fa fa-check"></i> <span class="bigger-110">Verify</span>';
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
                submitBtn.innerHTML = '<i class="fa fa-check"></i> <span class="bigger-110">Verify</span>';
            }
        });
    </script>
</body>
</html>

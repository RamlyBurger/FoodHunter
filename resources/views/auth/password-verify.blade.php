{{--
|==============================================================================
| Password Verify Page - Ng Wayne Xiang (User & Authentication Module)
|==============================================================================
|
| @author     Ng Wayne Xiang
| @module     User & Authentication Module
|
| OTP verification for password reset flow.
| Uses Supabase for secure OTP verification.
|==============================================================================
--}}

@extends('layouts.app')

@section('title', 'Verify Email')

@section('content')
<div class="page-content">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary-color), #007AFF);">
                    <i class="bi bi-shield-lock text-white fs-2"></i>
                </div>
                <h2 style="font-weight: 700; letter-spacing: -0.5px;">Verify Your Email</h2>
                <p class="text-muted">Enter the 6-digit code sent to<br><strong>{{ $email }}</strong></p>
            </div>

            <div id="email-status" class="alert alert-info mb-3 d-none">
                <span id="email-status-text">Sending verification code...</span>
            </div>

            <div class="card">
                <div class="card-body p-4">
                    <form action="{{ route('password.verify.submit') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label">Verification Code</label>
                            <input type="text" name="code" class="form-control form-control-lg text-center @error('code') is-invalid @enderror" 
                                   maxlength="6" placeholder="000000" style="letter-spacing: 10px; font-size: 28px; font-weight: 600;" 
                                   autofocus autocomplete="one-time-code" inputmode="numeric">
                            @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 mb-4">
                            <i class="bi bi-check-circle"></i> Verify & Continue
                        </button>
                    </form>

                    <div class="text-center">
                        <p class="text-muted mb-2" style="font-size: 0.9rem;">Didn't receive the code?</p>
                        <button type="button" class="btn btn-link p-0 fw-semibold" id="resend-btn" onclick="resendCode()">Resend Code</button>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="{{ route('password.request') }}" class="text-muted text-decoration-none d-inline-flex align-items-center gap-1">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>

            <div class="text-center mt-3">
                <small class="text-muted">
                    <i class="bi bi-shield-check"></i> Code expires in 15 minutes
                </small>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
<script>
(function() {
    const SUPABASE_URL = '{{ config('services.supabase.url') }}';
    const SUPABASE_ANON_KEY = '{{ config('services.supabase.anon_key') }}';
    const supabaseClient = window.supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);
    const userEmail = '{{ $email }}';
    let otpSent = false;
    let countdownInterval = null;
    const COOLDOWN_SECONDS = 60;

    function startCountdown() {
        const btn = document.getElementById('resend-btn');
        let seconds = COOLDOWN_SECONDS;
        
        btn.disabled = true;
        btn.innerHTML = `Resend in ${seconds}s`;
        
        countdownInterval = setInterval(() => {
            seconds--;
            if (seconds <= 0) {
                clearInterval(countdownInterval);
                btn.disabled = false;
                btn.innerHTML = 'Resend Code';
            } else {
                btn.innerHTML = `Resend in ${seconds}s`;
            }
        }, 1000);
    }

    async function sendOtpEmail(email) {
        const statusEl = document.getElementById('email-status');
        const statusText = document.getElementById('email-status-text');
        
        statusEl.classList.remove('d-none', 'alert-success', 'alert-danger');
        statusEl.classList.add('alert-info');
        statusText.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Sending verification code...';
        
        try {
            const { error } = await supabaseClient.auth.signInWithOtp({ email: email });
            
            if (error) {
                statusEl.classList.remove('alert-info');
                statusEl.classList.add('alert-danger');
                statusText.innerHTML = '<i class="bi bi-exclamation-circle me-2"></i> ' + error.message;
            } else {
                statusEl.classList.remove('alert-info');
                statusEl.classList.add('alert-success');
                statusText.innerHTML = '<i class="bi bi-check-circle me-2"></i> Verification code sent!';
                otpSent = true;
                startCountdown();
                setTimeout(() => statusEl.classList.add('d-none'), 5000);
            }
        } catch (err) {
            statusEl.classList.remove('alert-info');
            statusEl.classList.add('alert-danger');
            statusText.innerHTML = '<i class="bi bi-exclamation-circle me-2"></i> Failed to send. Click Resend.';
        }
    }

    window.resendCode = function() {
        const btn = document.getElementById('resend-btn');
        if (btn.disabled) return;
        
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Sending...';
        
        sendOtpEmail(userEmail);
    };

    document.addEventListener('DOMContentLoaded', function() {
        if (!otpSent) {
            sendOtpEmail(userEmail);
        }
    });
})();
</script>
@endpush

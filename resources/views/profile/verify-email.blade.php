@extends('layouts.app')

@section('title', 'Verify New Email')

@section('content')
<div class="page-content">
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-envelope-at text-info" style="font-size: 36px;"></i>
                        </div>
                        <h3>Verify New Email</h3>
                        <p class="text-muted">Enter the 6-digit code sent to<br><strong>{{ $email }}</strong></p>
                    </div>

                    <form action="{{ route('profile.verify-email.submit') }}" method="POST" id="verify-form">
                        @csrf

                        <div id="verify-error" class="alert alert-danger" style="display: none;"></div>

                        <div class="mb-4">
                            <label class="form-label">Verification Code</label>
                            <input type="text" name="code" id="verify-code" class="form-control form-control-lg text-center @error('code') is-invalid @enderror" 
                                   maxlength="6" placeholder="000000" style="letter-spacing: 8px; font-size: 24px;" 
                                   autofocus autocomplete="one-time-code" inputmode="numeric">
                            @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 mb-3" id="verify-btn">
                            <i class="bi bi-check-circle"></i> Verify & Update Email
                        </button>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <button type="button" class="btn btn-link text-danger p-0" id="cancel-btn" onclick="cancelEmailChange()">
                            <i class="bi bi-x-circle"></i> Cancel Email Change
                        </button>
                    </div>
                </div>
            </div>

            <div id="email-status" class="alert alert-info mt-3 d-none">
                <span id="email-status-text">Sending verification code...</span>
            </div>

            <div class="text-center mt-4">
                <button type="button" class="btn btn-link p-0" id="resend-btn" onclick="resendCode()">
                    <i class="bi bi-arrow-clockwise"></i> Resend Code
                </button>
            </div>

            <div class="text-center mt-2">
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
                btn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Resend Code';
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

    // Verify form AJAX submission
    document.getElementById('verify-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        const submitBtn = document.getElementById('verify-btn');
        const errorEl = document.getElementById('verify-error');
        const codeInput = document.getElementById('verify-code');
        const originalBtnText = submitBtn.innerHTML;
        
        errorEl.style.display = 'none';
        codeInput.classList.remove('is-invalid');
        
        if (!codeInput.value || codeInput.value.length !== 6) {
            codeInput.classList.add('is-invalid');
            errorEl.textContent = 'Please enter a valid 6-digit code';
            errorEl.style.display = 'block';
            return;
        }
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Verifying...';
        
        const formData = new FormData(form);
        
        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Show success and redirect
                Swal.fire({
                    icon: 'success',
                    title: 'Email Updated!',
                    text: data.message || 'Your email has been updated successfully',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = '{{ route("profile.index") }}';
                });
            } else {
                errorEl.textContent = data.message || 'Invalid verification code';
                errorEl.style.display = 'block';
                codeInput.classList.add('is-invalid');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        })
        .catch(err => {
            errorEl.textContent = 'An error occurred. Please try again.';
            errorEl.style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        });
    });

    // Cancel email change via AJAX
    window.cancelEmailChange = async function() {
        const result = await Swal.fire({
            title: 'Cancel Email Change?',
            text: 'Are you sure you want to cancel the email change?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, cancel it',
            cancelButtonText: 'No, continue'
        });
        
        if (!result.isConfirmed) return;
        
        const btn = document.getElementById('cancel-btn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Cancelling...';
        
        try {
            const res = await fetch('{{ route("profile.cancel-email-change") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Cancelled',
                    text: data.message || 'Email change cancelled',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = '{{ route("profile.index") }}';
                });
            } else {
                Swal.fire('Error', data.message || 'Failed to cancel', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-x-circle"></i> Cancel Email Change';
            }
        } catch (e) {
            Swal.fire('Error', 'An error occurred', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-x-circle"></i> Cancel Email Change';
        }
    };
})();
</script>
@endpush

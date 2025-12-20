@extends('layouts.app')

@section('title', 'Login - FoodHunter')

@section('content')
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 shadow-lg overflow-hidden">
                    <div class="row g-0">
                        <!-- Left Side - Image -->
                        <div class="col-lg-6 d-none d-lg-block" style="background: var(--gradient-1);">
                            <div class="h-100 d-flex align-items-center justify-content-center p-5 text-white">
                                <div class="text-center">
                                    <i class="bi bi-egg-fried" style="font-size: 5rem;"></i>
                                    <h2 class="display-5 fw-bold mt-4 mb-3">Welcome Back!</h2>
                                    <p class="lead mb-4">Login to access your favorite meals and track your orders</p>
                                    <div class="row g-3 text-center">
                                        <div class="col-6">
                                            <i class="bi bi-lightning-charge fs-1"></i>
                                            <p class="small mt-2">Fast Ordering</p>
                                        </div>
                                        <div class="col-6">
                                            <i class="bi bi-gift fs-1"></i>
                                            <p class="small mt-2">Earn Rewards</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Side - Form -->
                        <div class="col-lg-6">
                            <div class="p-5">
                                <div class="text-center mb-4">
                                    <h3 class="fw-bold mb-2">Login to Your Account</h3>
                                    <p class="text-muted">Enter your credentials to continue</p>
                                </div>
                                
                                <!-- Display Success Message -->
                                @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                                @endif

                                <!-- Display Error Message -->
                                @if(session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                                @endif
                                
                                <form method="POST" action="{{ route('login') }}">
                                    @csrf
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Email Address</label>
                                        <input type="email" 
                                               name="email" 
                                               class="form-control @error('email') is-invalid @enderror" 
                                               placeholder="your.email@university.edu"
                                               value="{{ old('email') }}"
                                               required>
                                        @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Password</label>
                                        <div class="position-relative">
                                            <input type="password" 
                                                   name="password" 
                                                   class="form-control @error('password') is-invalid @enderror" 
                                                   placeholder="Enter your password" 
                                                   id="password"
                                                   required>
                                            <button type="button" class="btn btn-sm position-absolute end-0 top-50 translate-middle-y me-2" 
                                                    onclick="togglePassword()">
                                                <i class="bi bi-eye" id="toggleIcon"></i>
                                            </button>
                                            @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                            <label class="form-check-label" for="remember">
                                                Remember me
                                            </label>
                                        </div>
                                        <a href="#" class="text-decoration-none small">Forgot Password?</a>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill mb-3">
                                        <i class="bi bi-box-arrow-in-right me-2"></i> Login
                                    </button>
                                    
                                    <div class="text-center">
                                        <span class="text-muted">Don't have an account?</span>
                                        <a href="{{ route('register') }}" class="text-decoration-none fw-bold">Sign Up</a>
                                    </div>
                                    
                                    <div class="position-relative my-4">
                                        <hr>
                                        <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted small">
                                            OR
                                        </span>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn btn-outline-secondary rounded-pill">
                                            <i class="bi bi-google me-2"></i> Continue with Google
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary rounded-pill">
                                            <i class="bi bi-facebook me-2"></i> Continue with Facebook
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
    function togglePassword() {
        const password = document.getElementById('password');
        const icon = document.getElementById('toggleIcon');
        
        if (password.type === 'password') {
            password.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            password.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }
</script>
@endpush
@endsection

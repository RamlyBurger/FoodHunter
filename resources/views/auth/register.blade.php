@extends('layouts.app')

@section('title', 'Register - FoodHunter')

@section('content')
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 shadow-lg overflow-hidden">
                    <div class="row g-0">
                        <!-- Left Side - Form -->
                        <div class="col-lg-7">
                            <div class="p-5">
                                <div class="text-center mb-4">
                                    <h3 class="fw-bold mb-2">Create Your Account</h3>
                                    <p class="text-muted">Join FoodHunter and start ordering!</p>
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
                                
                                <form method="POST" action="{{ route('register') }}">
                                    @csrf
                                    
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Full Name</label>
                                            <input type="text" 
                                                   name="name" 
                                                   class="form-control @error('name') is-invalid @enderror" 
                                                   placeholder="John Doe"
                                                   value="{{ old('name') }}"
                                                   required>
                                            @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="col-md-6">
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
                                        
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Phone Number</label>
                                            <input type="tel" 
                                                   name="phone" 
                                                   class="form-control @error('phone') is-invalid @enderror" 
                                                   placeholder="+60 12-345 6789"
                                                   value="{{ old('phone') }}">
                                            @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Role</label>
                                            <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                                <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Student</option>
                                                <option value="staff" {{ old('role') == 'staff' ? 'selected' : '' }}>Staff</option>
                                                <option value="vendor" {{ old('role') == 'vendor' ? 'selected' : '' }}>Vendor</option>
                                            </select>
                                            @error('role')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Password</label>
                                            <input type="password" 
                                                   name="password" 
                                                   class="form-control @error('password') is-invalid @enderror" 
                                                   placeholder="Min. 8 characters" 
                                                   id="reg-password"
                                                   required>
                                            @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">Must contain uppercase, lowercase, and number</small>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Confirm Password</label>
                                            <input type="password" 
                                                   name="password_confirmation" 
                                                   class="form-control" 
                                                   placeholder="Re-enter password"
                                                   required>
                                        </div>
                                    </div>
                                    
                                    <div class="form-check mt-4 mb-4">
                                        <input class="form-check-input" type="checkbox" id="agree" required>
                                        <label class="form-check-label small" for="agree">
                                            I agree to the <a href="#" class="text-decoration-none">Terms & Conditions</a> and 
                                            <a href="#" class="text-decoration-none">Privacy Policy</a>
                                        </label>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill mb-3">
                                        <i class="bi bi-person-plus me-2"></i> Create Account
                                    </button>
                                    
                                    <div class="text-center">
                                        <span class="text-muted">Already have an account?</span>
                                        <a href="{{ route('login') }}" class="text-decoration-none fw-bold">Login</a>
                                    </div>
                                    
                                    <div class="position-relative my-4">
                                        <hr>
                                        <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted small">
                                            OR
                                        </span>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn btn-outline-secondary rounded-pill">
                                            <i class="bi bi-google me-2"></i> Sign up with Google
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Right Side - Benefits -->
                        <div class="col-lg-5 d-none d-lg-block" style="background: var(--gradient-2);">
                            <div class="h-100 d-flex align-items-center justify-content-center p-5 text-white">
                                <div>
                                    <h4 class="fw-bold mb-4">Why Join FoodHunter?</h4>
                                    <div class="mb-4">
                                        <div class="d-flex align-items-start mb-3">
                                            <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                                            <div>
                                                <h6 class="fw-bold mb-1">Skip the Queue</h6>
                                                <small>Order ahead and collect without waiting</small>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-start mb-3">
                                            <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                                            <div>
                                                <h6 class="fw-bold mb-1">Earn Rewards</h6>
                                                <small>Get loyalty points with every order</small>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-start mb-3">
                                            <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                                            <div>
                                                <h6 class="fw-bold mb-1">Track Orders</h6>
                                                <small>Real-time updates on your food status</small>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-start">
                                            <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                                            <div>
                                                <h6 class="fw-bold mb-1">Multiple Vendors</h6>
                                                <small>Access 20+ food vendors in one app</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

{{--
|==============================================================================
| Contact Page - Shared (All Students)
|==============================================================================
|
| @author     Ng Wayne Xiang, Haerine Deepak Singh, Low Nam Lee, Lee Song Yan, Lee Kin Hang
| @module     Shared Infrastructure
|
| Contact information page for FoodHunter system.
| Displays contact details and support information.
|==============================================================================
--}}

@extends('layouts.app')

@section('title', 'Contact Us')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-5">
                <h1 class="fw-bold"><i class="bi bi-envelope-fill text-primary me-2"></i>Contact Us</h1>
                <p class="text-muted">Have questions? We'd love to hear from you.</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 text-center">
                        <div class="card-body py-4">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                                <i class="bi bi-geo-alt fs-3 text-primary"></i>
                            </div>
                            <h5>Visit Us</h5>
                            <p class="text-muted mb-0">
                                University Canteen<br>
                                Main Campus<br>
                                Kuala Lumpur, Malaysia
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 text-center">
                        <div class="card-body py-4">
                            <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                                <i class="bi bi-telephone fs-3 text-success"></i>
                            </div>
                            <h5>Call Us</h5>
                            <p class="text-muted mb-0">
                                +60 3-1234 5678<br>
                                Mon - Fri: 8AM - 6PM
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 text-center">
                        <div class="card-body py-4">
                            <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                                <i class="bi bi-envelope fs-3 text-info"></i>
                            </div>
                            <h5>Email Us</h5>
                            <p class="text-muted mb-0">
                                support@foodhunter.my<br>
                                info@foodhunter.my
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body p-4">
                    <h4 class="mb-4">Send us a Message</h4>
                    <form method="POST" action="{{ route('contact.send') }}" id="contact-form">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Your Name</label>
                                <input type="text" name="name" class="form-control" required 
                                       value="{{ auth()->user()->name ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" required
                                       value="{{ auth()->user()->email ?? '' }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Subject</label>
                                <select name="subject" class="form-select" required>
                                    <option value="">Select a subject</option>
                                    <option value="general">General Inquiry</option>
                                    <option value="order">Order Issue</option>
                                    <option value="vendor">Vendor Application</option>
                                    <option value="feedback">Feedback</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Message</label>
                                <textarea name="message" class="form-control" rows="5" required 
                                          placeholder="How can we help you?"></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary" id="contact-submit-btn">
                                    <i class="bi bi-send me-2"></i>Send Message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('contact-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const submitBtn = document.getElementById('contact-submit-btn');
    const originalBtnText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
    
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Message Sent!',
                text: data.message,
                confirmButtonColor: '#FF6B35'
            });
            form.reset();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Failed',
                text: data.message || 'Failed to send message.',
                confirmButtonColor: '#FF6B35'
            });
        }
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    })
    .catch(err => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred. Please try again.',
            confirmButtonColor: '#FF6B35'
        });
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    });
});
</script>
@endpush
@endsection

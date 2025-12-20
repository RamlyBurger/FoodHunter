@extends('layouts.app')

@section('title', 'Contact Us - FoodHunter')

@section('content')
<!-- Page Header -->
<section class="bg-light py-5">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3" data-aos="fade-up">Get In Touch</h1>
        <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">
            Have questions? We'd love to hear from you!
        </p>
    </div>
</section>

<!-- Contact Content -->
<section class="py-5">
    <div class="container">
        <div class="row g-5">
            <!-- Contact Form -->
            <div class="col-lg-7" data-aos="fade-right">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-5">
                        <h4 class="fw-bold mb-4">Send Us a Message</h4>
                        
                        <form>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Your Name</label>
                                    <input type="text" class="form-control" placeholder="John Doe">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Email Address</label>
                                    <input type="email" class="form-control" placeholder="your.email@university.edu">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Phone Number</label>
                                    <input type="tel" class="form-control" placeholder="+60 12-345 6789">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Subject</label>
                                    <select class="form-select">
                                        <option selected>General Inquiry</option>
                                        <option>Order Support</option>
                                        <option>Payment Issue</option>
                                        <option>Vendor Partnership</option>
                                        <option>Feedback</option>
                                        <option>Other</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Message</label>
                                    <textarea class="form-control" rows="6" placeholder="Tell us how we can help you..."></textarea>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill mt-4 px-5">
                                <i class="bi bi-send me-2"></i> Send Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Contact Info -->
            <div class="col-lg-5">
                <!-- Contact Cards -->
                <div class="card border-0 bg-light mb-4" data-aos="fade-left">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <div class="stat-icon primary me-3">
                                <i class="bi bi-geo-alt"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-2">Visit Us</h6>
                                <p class="text-muted mb-0">
                                    University Campus<br>
                                    Building A, Ground Floor<br>
                                    Canteen Area
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 bg-light mb-4" data-aos="fade-left" data-aos-delay="100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <div class="stat-icon success me-3">
                                <i class="bi bi-telephone"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-2">Call Us</h6>
                                <p class="text-muted mb-0">
                                    +60 12-345 6789<br>
                                    Mon - Fri: 7:00 AM - 8:00 PM<br>
                                    Sat - Sun: 8:00 AM - 6:00 PM
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 bg-light mb-4" data-aos="fade-left" data-aos-delay="200">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <div class="stat-icon warning me-3">
                                <i class="bi bi-envelope"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-2">Email Us</h6>
                                <p class="text-muted mb-0">
                                    info@foodhunter.com<br>
                                    support@foodhunter.com
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Social Media -->
                <div class="card border-0" style="background: var(--gradient-1);" data-aos="fade-left" data-aos-delay="300">
                    <div class="card-body p-4 text-white">
                        <h6 class="fw-bold mb-3">Follow Us</h6>
                        <div class="d-flex gap-3">
                            <a href="#" class="btn btn-light rounded-circle">
                                <i class="bi bi-facebook text-primary"></i>
                            </a>
                            <a href="#" class="btn btn-light rounded-circle">
                                <i class="bi bi-instagram text-danger"></i>
                            </a>
                            <a href="#" class="btn btn-light rounded-circle">
                                <i class="bi bi-twitter text-info"></i>
                            </a>
                            <a href="#" class="btn btn-light rounded-circle">
                                <i class="bi bi-youtube text-danger"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-5 fw-bold mb-3">Frequently Asked Questions</h2>
            <p class="text-muted">Quick answers to common questions</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item border-0 mb-3" data-aos="fade-up">
                        <h2 class="accordion-header">
                            <button class="accordion-button rounded-3 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                How do I place an order?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Browse our menu, select your items, add them to cart, and proceed to checkout. You'll receive a queue number and QR code for pickup.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border-0 mb-3" data-aos="fade-up" data-aos-delay="100">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed rounded-3 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                What payment methods are accepted?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We accept online banking, credit/debit cards, e-wallets (Touch 'n Go, GrabPay, Boost), and cash on pickup.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border-0 mb-3" data-aos="fade-up" data-aos-delay="200">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed rounded-3 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                How does the loyalty program work?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Earn 1 point for every RM1 spent. Redeem points for discounts and free items. Check your profile for your current points balance.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border-0" data-aos="fade-up" data-aos-delay="300">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed rounded-3 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Can I cancel my order?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Orders can be cancelled within 2 minutes of placement. After that, please contact the vendor directly or our support team.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

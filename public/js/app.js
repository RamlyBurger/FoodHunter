// ==========================================
// FOODHUNTER - Custom JavaScript
// Modern, Interactive UI Enhancements
// ==========================================

document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize toast notifications
    const toastElList = [].slice.call(document.querySelectorAll('.toast'));
    toastElList.map(function (toastEl) {
        return new bootstrap.Toast(toastEl);
    });
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#' && document.querySelector(href)) {
                e.preventDefault();
                document.querySelector(href).scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Update cart count (placeholder for dynamic update)
    function updateCartCount(count) {
        const cartCountElement = document.getElementById('cart-count');
        if (cartCountElement) {
            cartCountElement.textContent = count;
            
            // Add animation
            cartCountElement.parentElement.classList.add('scale-up');
            setTimeout(() => {
                cartCountElement.parentElement.classList.remove('scale-up');
            }, 300);
        }
    }
    
    // Add loading state to buttons
    function addLoadingState(button, text = 'Loading...') {
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status"></span>${text}`;
        
        return {
            stop: function() {
                button.disabled = false;
                button.innerHTML = originalText;
            }
        };
    }
    
    // Show toast notification
    function showToast(title, message, type = 'success') {
        const toastHTML = `
            <div class="toast align-items-center text-white bg-${type} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <strong>${title}</strong><br>${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        const toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        toastContainer.innerHTML = toastHTML;
        document.body.appendChild(toastContainer);
        
        const toast = new bootstrap.Toast(toastContainer.querySelector('.toast'));
        toast.show();
        
        setTimeout(() => {
            toastContainer.remove();
        }, 5000);
    }
    
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
    
    // Lazy load images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img.lazy').forEach(img => {
            imageObserver.observe(img);
        });
    }
    
    // Debounce function for search
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // Search functionality
    const searchInputs = document.querySelectorAll('.search-box input');
    searchInputs.forEach(input => {
        input.addEventListener('input', debounce(function(e) {
            const searchTerm = e.target.value.toLowerCase();
            console.log('Searching for:', searchTerm);
            // Implement search logic here
        }, 500));
    });
    
    // Add to favorites
    function toggleFavorite(itemId) {
        // Placeholder for favorite toggle
        console.log('Toggling favorite for item:', itemId);
        showToast('Success', 'Added to favorites!', 'success');
    }
    
    // Back to top button
    const backToTop = document.createElement('button');
    backToTop.innerHTML = '<i class="bi bi-arrow-up"></i>';
    backToTop.className = 'btn btn-primary rounded-circle position-fixed bottom-0 end-0 m-4';
    backToTop.style.cssText = 'width: 50px; height: 50px; display: none; z-index: 1000;';
    backToTop.onclick = () => window.scrollTo({ top: 0, behavior: 'smooth' });
    document.body.appendChild(backToTop);
    
    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            backToTop.style.display = 'block';
        } else {
            backToTop.style.display = 'none';
        }
    });
    
    // Navbar scroll effect
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('shadow-sm');
            } else {
                navbar.classList.remove('shadow-sm');
            }
        });
    }
    
    // Print order receipt
    function printReceipt(orderId) {
        window.print();
    }
    
    // Export functions for global use
    window.FoodHunter = {
        updateCartCount,
        addLoadingState,
        showToast,
        toggleFavorite,
        printReceipt
    };
});

// Add CSS animation classes
const style = document.createElement('style');
style.textContent = `
    @keyframes scaleUp {
        0% { transform: scale(1); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }
    
    .scale-up {
        animation: scaleUp 0.3s ease;
    }
    
    .lazy {
        opacity: 0;
        transition: opacity 0.3s;
    }
    
    .lazy.loaded {
        opacity: 1;
    }
`;
document.head.appendChild(style);

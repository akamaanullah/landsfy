/**
 * Landsfy Website - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', () => {
    
    // Abstract shapes parallax on mouse move (Login page flair)
    const shapes = document.querySelectorAll('.auth-shape');
    if (shapes.length > 0) {
        document.addEventListener('mousemove', (e) => {
            const x = e.clientX / window.innerWidth;
            const y = e.clientY / window.innerHeight;
            
            shapes[0].style.transform = `translate(${x * 30}px, ${y * 30}px)`;
            if (shapes[1]) {
                shapes[1].style.transform = `translate(${x * -40}px, ${y * -40}px)`;
            }
        });
    }

    // Password Visibility Toggle
    const togglePasswordBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('passwordInput');
    
    if (togglePasswordBtn && passwordInput) {
        togglePasswordBtn.addEventListener('click', function() {
            // Toggle the type attribute
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle the icon
            if (type === 'text') {
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
                this.style.color = 'var(--primary)';
            } else {
                this.classList.remove('fa-eye-slash');
                this.classList.add('fa-eye');
                this.style.color = 'var(--text-muted)';
            }
        });
    }

    // Scroll to Top action
    const scrollToTopBtn = document.getElementById('scrollToTop');
    if (scrollToTopBtn) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                scrollToTopBtn.style.opacity = '1';
                scrollToTopBtn.style.pointerEvents = 'auto';
            } else {
                scrollToTopBtn.style.opacity = '0';
                scrollToTopBtn.style.pointerEvents = 'none';
            }
        });

        scrollToTopBtn.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Initial state
        scrollToTopBtn.style.opacity = '0';
        scrollToTopBtn.style.pointerEvents = 'none';
        scrollToTopBtn.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
    }
});

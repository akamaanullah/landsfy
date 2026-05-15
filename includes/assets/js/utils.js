/**
 * Landsfy Central Utility Library
 * Standardizes common logic across all portals (Admin, Agent, Seller, Buyer, Website)
 */

window.Landsfy = {
    /**
     * Escape HTML special characters to prevent XSS
     */
    escapeHtml: function(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    },

    /**
     * Standardize Price Formatting (PKR)
     * Handles Lakh, Crore, Thousand conversions consistently
     */
    formatPrice: function(price) {
        const num = parseFloat(price);
        if (isNaN(num)) return 'N/A';
        
        let formatted = '';
        if (num >= 10000000) { // 1 Crore+
            formatted = (num / 10000000).toFixed(2).replace(/\.00$/, '') + ' Crore';
        } else if (num >= 100000) { // 1 Lakh+
            formatted = (num / 100000).toFixed(2).replace(/\.00$/, '') + ' Lakh';
        } else if (num >= 1000) {
            formatted = (num / 1000).toFixed(2).replace(/\.00$/, '') + ' Thousand';
        } else {
            formatted = num.toLocaleString();
        }
        return formatted;
    },
    /**
     * Initialize Dynamic Price in Words for a specific input
     */
    initPriceInWords: function(inputSelector, displaySelector) {
        const input = document.querySelector(inputSelector);
        const display = document.querySelector(displaySelector);
        
        if (!input || !display) return;

        const updateDisplay = () => {
            const val = input.value;
            if (val && val > 0) {
                const words = this.formatPrice(val);
                display.innerHTML = `<i class="fa-solid fa-check-circle"></i> Amount in words: ${words}`;
                display.classList.add('active');
            } else {
                display.classList.remove('active');
                display.innerHTML = '';
            }
        };

        input.addEventListener('input', updateDisplay);
        // Also run once in case of edit mode
        if (input.value) updateDisplay();
    },

    /**
     * Save Property to Recently Viewed (LocalStorage)
     */
    saveRecentlyViewed: function(property) {
        if (!property || !property.id) return;
        
        let viewed = JSON.parse(localStorage.getItem('landsfy_recently_viewed') || '[]');
        
        // Remove if already exists (to move to front)
        viewed = viewed.filter(p => p.id !== property.id);
        
        // Add to front
        viewed.unshift({
            id: property.id,
            title: property.title,
            slug: property.slug,
            price: property.price,
            image: property.images && property.images.length > 0 ? property.images[0].image_url : null,
            location: property.location_name,
            city: property.city_name,
            purpose: property.purpose,
            beds: property.beds || property.bedrooms,
            baths: property.baths || property.bathrooms,
            area: property.area_size + ' ' + property.area_unit
        });
        
        // Limit to 8 items
        if (viewed.length > 8) viewed.pop();
        
        localStorage.setItem('landsfy_recently_viewed', JSON.stringify(viewed));
    },

    /**
     * Get Recently Viewed Properties
     */
    getRecentlyViewed: function() {
        return JSON.parse(localStorage.getItem('landsfy_recently_viewed') || '[]');
    },

    /**
     * Clear Recently Viewed
     */
    clearRecentlyViewed: function() {
        localStorage.removeItem('landsfy_recently_viewed');
    },

    /**
     * Standardize Date Formatting
     */
    formatDate: function(dateStr, options = {}) {
        if (!dateStr) return 'N/A';
        const date = new Date(dateStr);
        if (isNaN(date.getTime())) return dateStr;

        const defaultOptions = { month: 'short', day: 'numeric', year: 'numeric' };
        return date.toLocaleDateString('en-US', { ...defaultOptions, ...options });
    },

    /**
     * Standardize DateTime Formatting
     */
    formatDateTime: function(dateStr) {
        if (!dateStr) return 'N/A';
        const date = new Date(dateStr);
        if (isNaN(date.getTime())) return dateStr;

        return date.toLocaleString('en-US', { 
            month: 'short', 
            day: 'numeric', 
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    /**
     * SweetAlert2 Toast Wrapper
     */
    showToast: function(title, icon = 'success', timer = 3000) {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: timer,
            timerProgressBar: true,
            background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1a1d21' : '#fff',
            color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#fff' : '#000',
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        Toast.fire({
            icon: icon,
            title: title
        });
    },

    /**
     * Shared Property Save (Favorite) Logic
     */
    toggleSaveProperty: async function(btn, propertyId) {
        const icon = btn.querySelector('i');
        const isSaved = icon.classList.contains('fa-solid');
        
        // Optimistic UI update
        if (isSaved) {
            icon.classList.replace('fa-solid', 'fa-regular');
            btn.classList.remove('active');
        } else {
            icon.classList.replace('fa-regular', 'fa-solid');
            btn.classList.add('active');
        }

        try {
            const formData = new FormData();
            formData.append('property_id', propertyId);
            formData.append('action', isSaved ? 'remove' : 'add');

            const response = await fetch(window.BASE_PATH + 'includes/api/buyer/toggle_save_property.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (!result.success) {
                // Rollback UI on failure
                if (isSaved) {
                    icon.classList.replace('fa-regular', 'fa-solid');
                    btn.classList.add('active');
                } else {
                    icon.classList.replace('fa-solid', 'fa-regular');
                    btn.classList.remove('active');
                }
                this.showToast(result.message || 'Login required to save properties', 'error');
            } else {
                this.showToast(isSaved ? 'Removed from favorites' : 'Saved to favorites');
            }
        } catch (error) {
            console.error('Save toggle failed', error);
            this.showToast('Something went wrong', 'error');
        }
    },

    /**
     * Standardize Image Pathing
     */
    getImageUrl: function(path) {
        if (!path) return '';
        if (path.startsWith('http')) return path;
        // Use global BASE_PATH or default to root
        const base = window.BASE_PATH || '../';
        return base + path;
    },

    /**
     * Standardize Image Error Handling
     */
    handleImageError: function(img, type = 'property') {
        img.onerror = null;
        img.src = '';
        let icon = 'fa-solid fa-house';
        if (type === 'user' || type === 'agent') icon = 'fa-solid fa-user';
        if (type === 'agency') icon = 'fa-solid fa-building';
        
        // Check if we are in a grid or detail view for styling
        const isCard = img.classList.contains('property-img') || img.closest('.property-card');
        const placeholderClass = isCard ? 'card-img-placeholder' : 'generic-img-placeholder';
        
        img.outerHTML = `<div class="${placeholderClass}"><i class="${icon}"></i></div>`;
    },

    /**
     * Initialize lazy loading for images and fade-in animations
     */
    initLazyLoading: function() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    el.classList.add('visible');
                    // If it's an image with data-src, load it
                    if (el.tagName === 'IMG' && el.dataset.src) {
                        el.src = el.dataset.src;
                        el.removeAttribute('data-src');
                    }
                    observer.unobserve(el);
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.lazy-reveal').forEach(el => observer.observe(el));
    },

    /**
     * Standardize Compact Number Formatting (e.g. 1.2k)
     */
    formatNumber: function(num) {
        if (!num) return '0';
        const n = parseFloat(num);
        if (n >= 1000000) return (n / 1000000).toFixed(1) + 'm';
        if (n >= 1000) return (n / 1000).toFixed(1) + 'k';
        return n.toString();
    },

    /**
     * Standardized Debounce Helper
     */
    debounce: function(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }
};

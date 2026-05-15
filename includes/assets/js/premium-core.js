document.addEventListener('DOMContentLoaded', () => {
    // Header Scroll Effect
    const header = document.querySelector('.header');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });

    // Search Tabs Switch
    const tabs = document.querySelectorAll('.search-tab');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
        });
    });

    // Advanced Search Toggle
    const advancedToggle = document.getElementById('advancedToggle');
    const advancedFilters = document.getElementById('advancedFilters');
    
    if (advancedToggle && advancedFilters) {
        advancedToggle.addEventListener('click', () => {
            const isVisible = advancedFilters.style.display === 'grid';
            advancedFilters.style.display = isVisible ? 'none' : 'grid';
            advancedToggle.innerHTML = isVisible ? 
                'Advanced <i class="fa-solid fa-chevron-down"></i>' : 
                'Simple <i class="fa-solid fa-chevron-up"></i>';
        });
    }

    // Scroll To Top
    const scrollTop = document.getElementById('scrollTop');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 500) {
            scrollTop.classList.add('show');
        } else {
            scrollTop.classList.remove('show');
        }
    });

    scrollTop.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // Browse Section Tab Switching
    const innerTabs = document.querySelectorAll('.inner-tab');
    innerTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const container = tab.closest('.category-card');
            const targetId = tab.getAttribute('data-target');

            // Remove active class from sibling tabs in the same container
            container.querySelectorAll('.inner-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            // Hide all grids in the same container and show the target one
            container.querySelectorAll('.link-grid-content').forEach(grid => {
                grid.classList.remove('active');
            });
            
            const targetGrid = document.getElementById(targetId);
            if (targetGrid) {
                targetGrid.classList.add('active');
            }
        });
    });

    // Initialize Animations
    const observerOptions = {
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    document.querySelectorAll('.property-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'all 0.6s ease-out';
        observer.observe(card);
    });

    // Popular Locations Tab Switching
    document.querySelectorAll('.location-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.location-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            const target = tab.getAttribute('data-loc-target');
            document.querySelectorAll('.locations-tab-content').forEach(c => c.classList.remove('active'));
            const el = document.getElementById(target);
            if (el) el.classList.add('active');
        });
    });

    // Mobile Menu Toggle
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileNav = document.getElementById('mobileNav');
    const mobileNavClose = document.getElementById('mobileNavClose');

    if (mobileMenuBtn && mobileNav) {
        mobileMenuBtn.addEventListener('click', () => {
            mobileNav.classList.add('open');
            document.body.style.overflow = 'hidden';
        });

        mobileNavClose.addEventListener('click', () => {
            mobileNav.classList.remove('open');
            document.body.style.overflow = '';
        });
    }

    // Mobile Category Tabs Switching
    const mobileCatTabs = document.querySelectorAll('.m-cat-tab');
    const categoryCards = document.querySelectorAll('.category-card');

    mobileCatTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const targetCat = tab.getAttribute('data-cat');

            // Update mobile tabs
            mobileCatTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            // Update category cards visibility
            categoryCards.forEach(card => {
                card.classList.remove('active');
                if (card.id === `cat-${targetCat}`) {
                    card.classList.add('active');
                }
            });
        });
    });

    // Grid / List View Switching
    const btnGrid = document.querySelector('.btn-grid-view');
    const btnList = document.querySelector('.btn-list-view');
    const listingGrid = document.getElementById('listing-grid');

    if (btnGrid && btnList && listingGrid) {
        btnGrid.addEventListener('click', () => {
            btnGrid.classList.add('active');
            btnList.classList.remove('active');
            listingGrid.classList.remove('view-list');
        });

        btnList.addEventListener('click', () => {
            btnList.classList.add('active');
            btnGrid.classList.remove('active');
            listingGrid.classList.add('view-list');
        });
    }

    // Mobile Filter Sidebar Toggle
    const btnOpenFilters = document.getElementById('open-filters-mobile');
    const btnCloseFilters = document.getElementById('close-filters-mobile');
    const filterSidebar = document.getElementById('filterSidebar');

    if (btnOpenFilters && filterSidebar) {
        btnOpenFilters.addEventListener('click', () => {
            filterSidebar.classList.add('open');
            document.body.style.overflow = 'hidden';
        });

        btnCloseFilters.addEventListener('click', () => {
            filterSidebar.classList.remove('open');
            document.body.style.overflow = '';
        });
    }

    // Property Detail Slider logic has been moved to property_detail.js for better page-specific control.


    // Agency Detail Tabs
    const tabLinks = document.querySelectorAll('.tabs-list li a');
    const sections = document.querySelectorAll('.content-section');

    if (tabLinks.length > 0) {
        tabLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const targetId = link.getAttribute('href').substring(1);
                
                // Update active tab
                tabLinks.forEach(l => l.parentElement.classList.remove('active'));
                link.parentElement.classList.add('active');

                // Show target section, hide others
                sections.forEach(sec => {
                    if (sec.id === targetId) {
                        sec.style.display = 'block';
                    } else {
                        sec.style.display = 'none';
                    }
                });
            });
        });

        // Initialize: Show only the first section
        const initialTab = document.querySelector('.tabs-list li.active a');
        if (initialTab) {
            const initialId = initialTab.getAttribute('href').substring(1);
            sections.forEach(sec => {
                sec.style.display = (sec.id === initialId) ? 'block' : 'none';
            });
        }
    }
    // Agent Detail Tabs Switching
    const agentTabLinks = document.querySelectorAll('.agent-tabs-nav .tabs-list li a');
    const agentTabPanes = document.querySelectorAll('.tab-pane');

    if (agentTabLinks.length > 0) {
        agentTabLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const targetId = link.getAttribute('href').substring(1);

                // Update active link
                agentTabLinks.forEach(l => l.parentElement.classList.remove('active'));
                link.parentElement.classList.add('active');

                // Update active pane
                agentTabPanes.forEach(pane => {
                    pane.classList.remove('active');
                    if (pane.id === targetId) {
                        pane.classList.add('active');
                    }
                });
            });
        });
    }
});

/**
 * Global Image Utilities for Landsfy
 */
window.getImageUrl = function(path) {
    if (!path) return '';
    if (path.startsWith('http')) return path;
    // Use global BASE_PATH if available to ensure absolute pathing
    const base = window.BASE_PATH || '/';
    return base + path;
};

window.handleImageError = function(img, type = 'property') {
    img.onerror = null;
    img.src = '';
    let icon = 'fa-solid fa-house';
    if (type === 'user' || type === 'agent') icon = 'fa-solid fa-user';
    if (type === 'agency') icon = 'fa-solid fa-building';
    
    // Check if we are in a grid or detail view for styling
    const isCard = img.classList.contains('property-img') || img.closest('.property-card');
    const placeholderClass = isCard ? 'card-img-placeholder' : 'generic-img-placeholder';
    
    img.outerHTML = `<div class="${placeholderClass}"><i class="${icon}"></i></div>`;
};

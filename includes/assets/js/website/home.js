/**
 * Landsfy Home Page Logic
 * Handles dynamic data fetching and rendering for the home page.
 */

document.addEventListener('DOMContentLoaded', () => {
    initHome();
});

async function initHome() {
    initHeroSlider();
    initAgencySlider();
    
    // Search Button Click
    const searchBtn = document.querySelector('.btn-search');
    if (searchBtn) {
        searchBtn.addEventListener('click', () => {
            const keyword = document.querySelector('.search-input[type="text"]').value;
            const city = document.querySelector('.search-main select.search-input').value;
            let purpose = document.querySelector('.search-tab.active').textContent.toLowerCase();
            if (purpose === 'buy') purpose = 'sell';
            
            let url = `properties.php?purpose=${purpose}`;
            if (city) url += `&city=${city}`;
            if (keyword) url += `&q=${encodeURIComponent(keyword)}`;
            
            // Advanced Filters
            const cat = document.getElementById('filterCategory').value;
            const sub = document.getElementById('filterSubtype').value;
            if (cat) url += `&category_id=${cat}`;
            if (sub) url += `&subtype_id=${sub}`;
            
            window.location.href = url;
        });
    }

    // Purpose Tab Switch
    document.querySelectorAll('.search-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.search-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
        });
    });

    try {
        const response = await fetch(window.BASE_PATH + 'includes/api/website/home_data.php');
        const result = await response.json();

        if (result.success) {
            const data = result.data;
            
            populateCities(result.data.cities);
            populateFilters(result.data.categories, result.data.subtypes);
            renderFeaturedProperties(result.data.featured_properties);
            renderPremiumAgencies(data.premium_agencies);
            renderLatestBlogs(data.latest_blogs);
            renderCounts(data.counts);
            renderPopularLocations(data.popular_locations);
            renderBrowseSection(data.browse_data);
            renderRecentlyViewed();
        }
    } catch (error) {
        console.error('Failed to load home data:', error);
    }
}

function renderBrowseSection(data) {
    const categories = ['home', 'plots', 'commercial'];
    const catMap = { 'home': 1, 'plots': 2, 'commercial': 3 };

    categories.forEach(cat => {
        const catData = data[cat];
        if (!catData) return;
        const catId = catMap[cat];

        // 1. Popular
        const popularGrid = document.getElementById(`popular-${cat}`);
        if (popularGrid) {
            popularGrid.innerHTML = catData.popular.map(item => `
                <div class="link-box" onclick="location.href='properties.php?${item.query}'">
                    <span class="link-label">${item.name}</span>
                </div>
            `).join('') || '<p class="no-data">Loading filters...</p>';
        }

        // 2. Types
        const typeGrid = document.getElementById(`type-${cat}`);
        if (typeGrid) {
            typeGrid.innerHTML = catData.types.map(item => `
                <div class="link-box" onclick="location.href='properties.php?category_id=${catId}&subtype_id=${item.id}'">
                    <span class="link-label">${item.name}</span>
                </div>
            `).join('') || '<p class="no-data">Loading filters...</p>';
        }

        // 3. Sizes
        const areaGrid = document.getElementById(`area-${cat}`);
        if (areaGrid) {
            areaGrid.innerHTML = catData.sizes.map(item => `
                <div class="link-box" onclick="location.href='properties.php?${item.query}'">
                    <span class="link-label">${item.name}</span>
                </div>
            `).join('') || '<p class="no-data">Loading filters...</p>';
        }
    });

    // Handle Tab Switching
    document.querySelectorAll('.inner-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            const parent = tab.closest('.category-card');
            const targetId = tab.dataset.target;

            // Remove active from all tabs in this card
            parent.querySelectorAll('.inner-tab').forEach(t => t.classList.remove('active'));
            // Add active to clicked tab
            tab.classList.add('active');

            // Hide all grids in this card
            parent.querySelectorAll('.link-grid-content').forEach(grid => grid.classList.remove('active'));
            // Show target grid
            const target = document.getElementById(targetId);
            if (target) target.classList.add('active');
        });
    });
}

function populateCities(cities) {
    const citySelect = document.querySelector('.search-main select.search-input'); 
    if (!citySelect) return;

    let html = '<option value="">Select City</option>';
    cities.forEach(city => {
        html += `<option value="${city.slug}">${city.name}</option>`;
    });
    citySelect.innerHTML = html;
}

function populateFilters(categories, subtypes) {
    const catSelect = document.getElementById('filterCategory');
    const subSelect = document.getElementById('filterSubtype');
    
    if (!catSelect || !subSelect) return;

    // Populate Categories
    let catHtml = '<option value="">-- Select --</option>';
    categories.forEach(cat => {
        catHtml += `<option value="${cat.id}">${cat.name}</option>`;
    });
    catSelect.innerHTML = catHtml;

    // Dependent Dropdown Logic
    catSelect.addEventListener('change', () => {
        const catId = catSelect.value;
        let subHtml = '<option value="">-- Select --</option>';
        
        const filtered = catId 
            ? subtypes.filter(s => s.category_id == catId)
            : subtypes;

        filtered.forEach(sub => {
            subHtml += `<option value="${sub.id}">${sub.name}</option>`;
        });
        subSelect.innerHTML = subHtml;
    });

    // Initial populate of subtypes
    let subHtml = '<option value="">-- Select --</option>';
    subtypes.forEach(sub => {
        subHtml += `<option value="${sub.id}">${sub.name}</option>`;
    });
    subSelect.innerHTML = subHtml;
}

function renderFeaturedProperties(properties) {
    const grid = document.getElementById('featuredPropertiesGrid');
    if (!grid) return;

    if (properties.length === 0) {
        grid.innerHTML = `
            <div class="no-data-full">
                <i class="fa-solid fa-house-chimney"></i>
                <p>No featured properties available at the moment.</p>
            </div>
        `;
        return;
    }

    let html = '';
    properties.forEach(prop => {
        const thumb = Landsfy.getImageUrl(prop.thumbnail);
        
        // Save Button Logic
        let saveBtnHtml = '';
        if (window.USER_ROLE === 'buyer') {
            const heartIcon = prop.is_saved > 0 ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
            const activeClass = prop.is_saved > 0 ? 'active' : '';
            saveBtnHtml = `
                <button class="save-property-btn ${activeClass}" onclick="toggleSave(event, ${prop.id}, this)">
                    <i class="${heartIcon}"></i>
                </button>
            `;
        }

        // Badge Logic
        let badgeHtml = '';
        if (prop.priority === 'diamond') {
            badgeHtml = '<div class="property-badge diamond-badge"><i class="fa-solid fa-gem"></i> Diamond</div>';
        } else if (prop.priority === 'platinum') {
            badgeHtml = '<div class="property-badge platinum-badge"><i class="fa-solid fa-diamond"></i> Platinum</div>';
        } else if (prop.is_featured == 1) {
            badgeHtml = '<div class="property-badge">Featured</div>';
        } else {
            badgeHtml = `<div class="property-badge">${prop.subtype_name}</div>`;
        }

        html += `
            <div class="property-card" onclick="window.location.href='${window.BASE_PATH}properties/${prop.slug}'" style="cursor:pointer;">
                <div class="property-img-wrapper">
                    <img src="${thumb}" class="property-img" alt="${prop.title}" onerror="Landsfy.handleImageError(this, 'property')">
                    ${badgeHtml}
                    ${saveBtnHtml}
                </div>
                <div class="property-content">
                    <h3 class="property-title">${prop.title}</h3>
                    <div class="property-location"><i class="fa-solid fa-location-dot"></i> ${prop.city_name}, ${prop.location_name || ''}</div>
                    <div class="property-price">${Landsfy.formatPrice(prop.price)}</div>
                    <div class="property-specs">
                        <div class="spec-item"><i class="fa-solid fa-bed"></i> ${prop.beds} beds</div>
                        <div class="spec-item"><i class="fa-solid fa-bath"></i> ${prop.baths} baths</div>
                        <div class="spec-item"><i class="fa-solid fa-vector-square"></i> ${prop.area_size} ${prop.area_unit}</div>
                    </div>
                </div>
            </div>
        `;
    });
    grid.innerHTML = html;
}

async function toggleSave(event, propertyId, btn) {
    event.stopPropagation();
    if (!window.IS_LOGGED_IN) {
        window.location.href = window.BASE_PATH + 'login';
        return;
    }
    Landsfy.toggleSaveProperty(btn, propertyId);
}

function renderPremiumAgencies(agencies) {
    const grid = document.getElementById('premiumAgenciesGrid');
    if (!grid) return;
    
    if (agencies.length === 0) {
        grid.innerHTML = `
            <div class="no-data-full">
                <i class="fa-solid fa-city"></i>
                <p>No premium agencies listed yet. Stay tuned!</p>
            </div>
        `;
        return;
    }

    let html = '';
    agencies.forEach(agency => {
        const logo = agency.logo_url || 'includes/assets/images/logo.png';
        html += `
            <div class="agency-card" onclick="window.location.href='${window.BASE_PATH}agencies/${agency.slug}'" style="cursor:pointer;">
                <div class="agency-logo-box">
                    <img src="${logo}" alt="${agency.name}" onerror="this.src='includes/assets/images/logo.png'">
                </div>
                <div class="agency-info">
                    <span class="agency-name">${agency.name}</span>
                    <div class="agency-location"><i class="fa-solid fa-location-dot"></i> ${agency.city_name || 'Pakistan'}</div>
                </div>
            </div>
        `;
    });
    grid.innerHTML = html;
}

function renderLatestBlogs(blogs) {
    const grid = document.getElementById('homeBlogGrid');
    if (!grid) return;

    if (blogs.length === 0) {
        grid.innerHTML = `
            <div class="no-data-full">
                <i class="fa-solid fa-newspaper"></i>
                <p>New articles are coming soon. Check back later!</p>
            </div>
        `;
        return;
    }

    let html = '';
    blogs.forEach(blog => {
        const img = blog.image_url || 'includes/assets/images/website/blog-placeholder.jpg';
        const date = new Date(blog.created_at).toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric', 
            year: 'numeric' 
        });
        
        html += `
            <div class="blog-card" onclick="window.location.href='${window.BASE_PATH}blog/${blog.slug}'" style="cursor:pointer;">
                <div class="blog-img-wrapper">
                    <img src="${img}" class="blog-img" alt="${blog.title}">
                    <div class="blog-category">${blog.category}</div>
                </div>
                <div class="blog-content">
                    <div class="blog-meta">
                        <span><i class="fa-solid fa-calendar-days"></i> ${date}</span>
                    </div>
                    <h3 class="blog-title">${blog.title}</h3>
                    <p class="blog-excerpt">${blog.excerpt}</p>
                    <span class="blog-read-more">Read More <i class="fa-solid fa-arrow-right"></i></span>
                </div>
            </div>
        `;
    });
    grid.innerHTML = html;
}

/**
 * Hero Slider Logic
 */
function initHeroSlider() {
    const slides = document.querySelectorAll('.hero-slide');
    if (slides.length <= 1) return;

    let currentSlide = 0;
    setInterval(() => {
        slides[currentSlide].classList.remove('active');
        currentSlide = (currentSlide + 1) % slides.length;
        slides[currentSlide].classList.add('active');
    }, 5000); // Change slide every 5 seconds
}

function initAgencySlider() {
    const grid = document.getElementById('premiumAgenciesGrid');
    const nextBtn = document.querySelector('.agency-nav.next');
    const prevBtn = document.querySelector('.agency-nav.prev');

    if (!grid || !nextBtn || !prevBtn) return;

    nextBtn.addEventListener('click', () => {
        grid.scrollBy({ left: 300, behavior: 'smooth' });
    });

    prevBtn.addEventListener('click', () => {
        grid.scrollBy({ left: -300, behavior: 'smooth' });
    });
}

function renderCounts(counts) {
    // Update category card subtexts if needed
}

function renderPopularLocations(locations) {
    const saleGrid = document.getElementById('popularLocationsSaleGrid');
    const rentGrid = document.getElementById('popularLocationsRentGrid');
    
    if (!saleGrid || !rentGrid) return;

    // Grouping helper
    const groupByCity = (list) => {
        return list.reduce((acc, loc) => {
            if (!acc[loc.city_name]) acc[loc.city_name] = [];
            acc[loc.city_name].push(loc);
            return acc;
        }, {});
    };

    // Filter locations
    const housesForSale = locations.filter(l => l.category_id == 1); // 1 = Homes/Houses
    const plotsForSale = locations.filter(l => l.category_id == 2); // 2 = Plots

    const generateCityGrid = (groupedData, categoryLabel, prefix) => {
        let html = `<div class="location-category-title">${categoryLabel}</div>`;
        html += `<div class="locations-grid">`;
        
        for (const city in groupedData) {
            html += `
                <div>
                    <div class="location-city-title">${city}</div>
                    <ul class="location-links">
                        ${groupedData[city].map(l => `
                            <li class="location-link-item" onclick="window.location.href='${window.BASE_PATH}properties.php?location=${l.location_slug}'" style="cursor:pointer;">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                <span class="link-text">${l.category_name} for ${prefix} in ${l.location_name}</span>
                                <span class="link-count">(${l.prop_count})</span>
                            </li>
                        `).join('')}
                    </ul>
                </div>
            `;
        }
        html += `</div>`;
        return html;
    };

    // Render Sale Tab
    const groupedPlots = groupByCity(plotsForSale);
    const groupedHouses = groupByCity(housesForSale);
    
    if (Object.keys(groupedPlots).length === 0 && Object.keys(groupedHouses).length === 0) {
        saleGrid.innerHTML = `
            <div class="no-data-full" style="padding: 40px 0;">
                <i class="fa-solid fa-location-dot"></i>
                <p>Popular locations will appear here once more properties are listed.</p>
            </div>
        `;
    } else {
        saleGrid.innerHTML = generateCityGrid(groupedPlots, 'Most Popular Locations for Plots', 'sale') + 
                            generateCityGrid(groupedHouses, 'Most Popular Locations for Houses', 'sale');
    }

    // Render Rent Tab
    if (Object.keys(groupedHouses).length === 0) {
        rentGrid.innerHTML = `
            <div class="no-data-full" style="padding: 40px 0;">
                <i class="fa-solid fa-house-chimney"></i>
                <p>No rental locations available at the moment.</p>
            </div>
        `;
    } else {
        rentGrid.innerHTML = generateCityGrid(groupedHouses, 'Most Popular Locations for Rent', 'rent');
    }
}

async function toggleSave(event, propertyId, btn) {
    event.stopPropagation();
    if (!window.IS_LOGGED_IN) {
        window.location.href = window.BASE_PATH + 'login';
        return;
    }
    Landsfy.toggleSaveProperty(btn, propertyId);
}

function renderPremiumAgencies(agencies) {
    const grid = document.getElementById('premiumAgenciesGrid');
    if (!grid) return;
    
    if (agencies.length === 0) {
        grid.innerHTML = `
            <div class="no-data-full">
                <i class="fa-solid fa-city"></i>
                <p>No premium agencies listed yet. Stay tuned!</p>
            </div>
        `;
        return;
    }

    let html = '';
    agencies.forEach(agency => {
        const logo = agency.logo_url || 'includes/assets/images/logo.png';
        html += `
            <div class="agency-card" onclick="window.location.href='${window.BASE_PATH}agencies/${agency.slug}'" style="cursor:pointer;">
                <div class="agency-logo-box">
                    <img src="${logo}" alt="${agency.name}" onerror="this.src='includes/assets/images/logo.png'">
                </div>
                <div class="agency-info">
                    <span class="agency-name">${agency.name}</span>
                    <div class="agency-location"><i class="fa-solid fa-location-dot"></i> ${agency.city_name || 'Pakistan'}</div>
                </div>
            </div>
        `;
    });
    grid.innerHTML = html;
}

function renderLatestBlogs(blogs) {
    const grid = document.getElementById('homeBlogGrid');
    if (!grid) return;

    if (blogs.length === 0) {
        grid.innerHTML = `
            <div class="no-data-full">
                <i class="fa-solid fa-newspaper"></i>
                <p>New articles are coming soon. Check back later!</p>
            </div>
        `;
        return;
    }

    let html = '';
    blogs.forEach(blog => {
        const img = blog.image_url || 'includes/assets/images/website/blog-placeholder.jpg';
        const date = new Date(blog.created_at).toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric', 
            year: 'numeric' 
        });
        
        html += `
            <div class="blog-card" onclick="window.location.href='${window.BASE_PATH}blog/${blog.slug}'" style="cursor:pointer;">
                <div class="blog-img-wrapper">
                    <img src="${img}" class="blog-img" alt="${blog.title}">
                    <div class="blog-category">${blog.category}</div>
                </div>
                <div class="blog-content">
                    <div class="blog-meta">
                        <span><i class="fa-solid fa-calendar-days"></i> ${date}</span>
                    </div>
                    <h3 class="blog-title">${blog.title}</h3>
                    <p class="blog-excerpt">${blog.excerpt}</p>
                    <span class="blog-read-more">Read More <i class="fa-solid fa-arrow-right"></i></span>
                </div>
            </div>
        `;
    });
    grid.innerHTML = html;
}

/**
 * Hero Slider Logic
 */
function initHeroSlider() {
    const slides = document.querySelectorAll('.hero-slide');
    if (slides.length <= 1) return;

    let currentSlide = 0;
    setInterval(() => {
        slides[currentSlide].classList.remove('active');
        currentSlide = (currentSlide + 1) % slides.length;
        slides[currentSlide].classList.add('active');
    }, 5000); // Change slide every 5 seconds
}

function initAgencySlider() {
    const grid = document.getElementById('premiumAgenciesGrid');
    const nextBtn = document.querySelector('.agency-nav.next');
    const prevBtn = document.querySelector('.agency-nav.prev');

    if (!grid || !nextBtn || !prevBtn) return;

    nextBtn.addEventListener('click', () => {
        grid.scrollBy({ left: 300, behavior: 'smooth' });
    });

    prevBtn.addEventListener('click', () => {
        grid.scrollBy({ left: -300, behavior: 'smooth' });
    });
}

/**
 * Render Recently Viewed Properties from LocalStorage
 */
function renderRecentlyViewed() {
    const grid = document.getElementById('recentlyViewedGrid');
    const section = document.getElementById('recentlyViewedSection');
    const clearBtn = document.getElementById('btnClearRecent');
    
    if (!grid || !section) return;

    const viewed = Landsfy.getRecentlyViewed();

    if (viewed.length === 0) {
        section.style.display = 'none';
        return;
    }

    section.style.display = 'block';

    let html = '';
    viewed.forEach(prop => {
        const thumb = Landsfy.getImageUrl(prop.image);
        
        html += `
            <div class="property-card" onclick="window.location.href='${window.BASE_PATH}properties/${prop.slug}'" style="cursor:pointer;">
                <div class="property-img-wrapper">
                    <img src="${thumb}" class="property-img" alt="${prop.title}" onerror="Landsfy.handleImageError(this, 'property')">
                    <div class="property-badge">${prop.purpose === 'rent' ? 'For Rent' : 'For Sale'}</div>
                </div>
                <div class="property-content">
                    <h3 class="property-title">${prop.title}</h3>
                    <div class="property-location"><i class="fa-solid fa-location-dot"></i> ${prop.city}, ${prop.location || ''}</div>
                    <div class="property-price">${Landsfy.formatPrice(prop.price)} ${prop.purpose === 'rent' ? '<small>/mo</small>' : ''}</div>
                    <div class="property-specs">
                        <div class="spec-item"><i class="fa-solid fa-bed"></i> ${prop.beds || '--'} beds</div>
                        <div class="spec-item"><i class="fa-solid fa-bath"></i> ${prop.baths || '--'} baths</div>
                        <div class="spec-item"><i class="fa-solid fa-vector-square"></i> ${prop.area}</div>
                    </div>
                </div>
            </div>
        `;
    });
    grid.innerHTML = html;

    // Clear History Action
    if (clearBtn) {
        clearBtn.onclick = () => {
            Swal.fire({
                title: 'Clear History?',
                text: "This will remove your recently viewed properties from this browser.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                confirmButtonText: 'Yes, clear it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Landsfy.clearRecentlyViewed();
                    renderRecentlyViewed();
                    Swal.fire('Cleared!', 'Your history has been removed.', 'success');
                }
            });
        };
    }

    // Slider Navigation
    const nextBtn = document.getElementById('recentNext');
    const prevBtn = document.getElementById('recentPrev');
    if (nextBtn && prevBtn && grid) {
        nextBtn.onclick = () => grid.scrollBy({ left: 350, behavior: 'smooth' });
        prevBtn.onclick = () => grid.scrollBy({ left: -350, behavior: 'smooth' });
    }
}

function renderCounts(counts) {
    // Update category card subtexts if needed
}


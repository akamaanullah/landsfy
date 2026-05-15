/**
 * Landsfy Properties Listing Logic
 * Handles filtering, pagination, and real-time data updates.
 */

let currentFilters = {
    page: 1,
    limit: 12
};

document.addEventListener('DOMContentLoaded', () => {
    initProperties();
});

async function initProperties() {
    // 1. Initial Load from URL
    syncFiltersFromURL();
    
    // 2. Load Categories/Types for Dropdowns
    await loadFilterMetadata();
    
    // 3. Load Properties
    fetchProperties();

    // 4. Set up Event Listeners
    setupFilterListeners();
}

function syncFiltersFromURL() {
    const urlParams = new URLSearchParams(window.location.search);
    const paramsObj = Object.fromEntries(urlParams.entries());
    currentFilters = { ...currentFilters, ...paramsObj };
    
    // Update UI to match URL
    if (paramsObj.q || paramsObj.search) {
        const val = paramsObj.q || paramsObj.search;
        document.getElementById('filterSearch').value = val;
        currentFilters.q = val;
    }
    if (paramsObj.purpose) {
        const pVal = paramsObj.purpose.toLowerCase() === 'buy' || paramsObj.purpose.toLowerCase() === 'sale' ? 'sell' : paramsObj.purpose;
        document.getElementById('filterPurpose').value = pVal;
        currentFilters.purpose = pVal;
    }
    if (paramsObj.min_price) document.getElementById('minPrice').value = paramsObj.min_price;
    if (paramsObj.max_price) document.getElementById('maxPrice').value = paramsObj.max_price;
    if (paramsObj.size) {
        const parts = paramsObj.size.split('-');
        document.getElementById('areaSize').value = parts[0];
        document.getElementById('areaUnit').value = parts[1].charAt(0).toUpperCase() + parts[1].slice(1);
    }
}

async function loadFilterMetadata() {
    try {
        const response = await fetch(window.BASE_PATH + 'includes/api/website/home_data.php'); // Reuse home data for cats/subtypes
        const result = await response.json();
        
        if (result.success) {
            const catSelect = document.getElementById('filterCategory');
            const typeSelect = document.getElementById('filterType');
            
            // Populate Categories
            result.data.categories.forEach(cat => {
                const opt = document.createElement('option');
                opt.value = cat.id;
                opt.textContent = cat.name;
                catSelect.appendChild(opt);
            });
            if (currentFilters.category_id) catSelect.value = currentFilters.category_id;
            if (currentFilters.cat_id) catSelect.value = currentFilters.cat_id;

            // Populate Types
            result.data.subtypes.forEach(type => {
                const opt = document.createElement('option');
                opt.value = type.id;
                opt.textContent = type.name;
                typeSelect.appendChild(opt);
            });
            if (currentFilters.subtype_id) typeSelect.value = currentFilters.subtype_id;
            if (currentFilters.type_id) typeSelect.value = currentFilters.type_id;
        }
    } catch (error) {
        console.error('Failed to load metadata:', error);
    }
}

async function fetchProperties() {
    const grid = document.getElementById('listing-grid');
    grid.innerHTML = '<div class="property-loading"><p>Loading verified properties...</p></div>';

    // Build Query String
    const queryString = new URLSearchParams(currentFilters).toString();
    
    try {
        const response = await fetch(`${window.BASE_PATH}includes/api/website/properties_data.php?${queryString}`);
        const result = await response.json();
        
        if (result.success) {
            renderProperties(result.data);
            renderPagination(result.meta);
            updateResultCount(result.meta.total);
            renderActiveFilterTags();
            updatePageHeading();
            
            // Update URL without reload
            const newURL = window.location.pathname + '?' + queryString;
            window.history.pushState({ path: newURL }, '', newURL);
        }
    } catch (error) {
        grid.innerHTML = '<p class="error-msg">Something went wrong. Please try again.</p>';
    }
}

function renderActiveFilterTags() {
    const bar = document.getElementById('activeFiltersBar');
    const list = document.getElementById('activeTagsList');
    list.innerHTML = '';
    let hasFilters = false;

    const addTag = (key, value, label) => {
        hasFilters = true;
        const chip = document.createElement('div');
        chip.className = 'filter-chip';
        chip.innerHTML = `${label}: ${value} <i class="fa-solid fa-circle-xmark" onclick="removeFilterTag('${key}')"></i>`;
        list.appendChild(chip);
    };

    if (currentFilters.q || currentFilters.search) addTag('q', currentFilters.q || currentFilters.search, 'Search');
    if (currentFilters.purpose) addTag('purpose', currentFilters.purpose === 'sell' ? 'For Sale' : 'For Rent', 'Purpose');
    if (currentFilters.city) addTag('city', currentFilters.city, 'City');
    if (currentFilters.location) addTag('location', currentFilters.location, 'Location');
    
    // Handle both cat_id and category_id
    const catId = currentFilters.cat_id || currentFilters.category_id;
    if (catId) {
        const catName = document.querySelector(`#filterCategory option[value="${catId}"]`)?.textContent;
        if (catName) addTag('cat_id', catName, 'Category');
    }

    const typeId = currentFilters.type_id || currentFilters.subtype_id;
    if (typeId) {
        const typeName = document.querySelector(`#filterType option[value="${typeId}"]`)?.textContent;
        if (typeName) addTag('type_id', typeName, 'Type');
    }

    if (currentFilters.size) addTag('size', currentFilters.size.replace('-', ' '), 'Area');
    if (currentFilters.min_price) addTag('min_price', Landsfy.formatPrice(currentFilters.min_price), 'Min');
    if (currentFilters.max_price) addTag('max_price', Landsfy.formatPrice(currentFilters.max_price), 'Max');

    bar.style.display = hasFilters ? 'flex' : 'none';
}

function removeFilterTag(key) {
    if (key === 'q') {
        delete currentFilters.q;
        delete currentFilters.search;
        document.getElementById('filterSearch').value = '';
    } else if (key === 'cat_id' || key === 'category_id') {
        delete currentFilters.cat_id;
        delete currentFilters.category_id;
        document.getElementById('filterCategory').value = '';
    } else if (key === 'type_id' || key === 'subtype_id') {
        delete currentFilters.type_id;
        delete currentFilters.subtype_id;
        document.getElementById('filterType').value = '';
    } else if (key === 'size' || key === 'areaSize') {
        delete currentFilters.size;
        delete currentFilters.areaSize;
        document.getElementById('areaSize').value = '';
    } else {
        delete currentFilters[key];
        // Reset specific UI elements if needed
        if (key === 'min_price') document.getElementById('minPrice').value = '';
        if (key === 'max_price') document.getElementById('maxPrice').value = '';
    }
    
    currentFilters.page = 1;
    fetchProperties();
}

function updatePageHeading() {
    const titleEl = document.querySelector('.properties-page-title');
    if (!titleEl) return;

    let purpose = currentFilters.purpose || 'Sale';
    let title = `Properties for ${purpose.charAt(0).toUpperCase() + purpose.slice(1)} in `;
    let loc = currentFilters.city || currentFilters.location || 'Pakistan';
    
    // Capitalize location
    loc = loc.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
    
    titleEl.innerHTML = `${title} <span class="text-primary">${loc}</span>`;
}

function renderProperties(properties) {
    const grid = document.getElementById('listing-grid');
    if (properties.length === 0) {
        grid.innerHTML = `
            <div class="no-results-state">
                <div class="no-results">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <h3>No properties found</h3>
                    <p>Try adjusting your filters to find what you're looking for.</p>
                    <button class="btn-reset-filters" onclick="resetAllFilters()">
                        <i class="fa-solid fa-rotate-left"></i> Reset All Filters
                    </button>
                </div>
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
        }

        html += `
            <div class="property-card" onclick="window.location.href='${window.BASE_PATH}properties/${prop.slug}'" style="cursor:pointer;">
                <div class="property-img-wrapper">
                    <img src="${thumb}" class="property-img" loading="lazy" onerror="Landsfy.handleImageError(this, 'property')">
                    ${badgeHtml}
                    <div class="property-sale-badge">For ${prop.purpose || 'Sale'}</div>
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

function renderPagination(meta) {
    const container = document.getElementById('pagination-container');
    const totalPages = Math.ceil(meta.total / meta.limit);
    if (totalPages <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = `
        <a href="#" class="page-link prev ${meta.page === 1 ? 'disabled' : ''}" data-page="${meta.page - 1}"><i class="fa-solid fa-chevron-left"></i></a>
    `;

    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= meta.page - 1 && i <= meta.page + 1)) {
            html += `<a href="#" class="page-link ${i === meta.page ? 'active' : ''}" data-page="${i}">${i}</a>`;
        } else if (i === meta.page - 2 || i === meta.page + 2) {
            html += `<span class="page-dots">...</span>`;
        }
    }

    html += `
        <a href="#" class="page-link next ${meta.page === totalPages ? 'disabled' : ''}" data-page="${meta.page + 1}"><i class="fa-solid fa-chevron-right"></i></a>
    `;
    
    container.innerHTML = html;

    // Add events
    container.querySelectorAll('.page-link:not(.disabled)').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            currentFilters.page = link.dataset.page;
            fetchProperties();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
}

function updateResultCount(total) {
    const countEl = document.querySelector('.properties-count');
    if (countEl) countEl.textContent = `Showing ${total.toLocaleString()} verified results`;
}

function setupFilterListeners() {
    const applyBtn = document.getElementById('applyFilters');
    const resetBtn = document.getElementById('resetFilters');
    const sortSelect = document.getElementById('sortResults');

    applyBtn.addEventListener('click', () => {
        currentFilters.purpose = document.getElementById('filterPurpose').value;
        currentFilters.q = document.getElementById('filterSearch').value;
        currentFilters.cat_id = document.getElementById('filterCategory').value;
        currentFilters.type_id = document.getElementById('filterType').value;
        currentFilters.min_price = document.getElementById('minPrice').value;
        currentFilters.max_price = document.getElementById('maxPrice').value;
        
        const sizeValue = document.getElementById('areaSize').value;
        if (sizeValue) {
            const unit = document.getElementById('areaUnit').value.toLowerCase();
            currentFilters.size = `${sizeValue}-${unit}`;
        } else {
            delete currentFilters.size;
        }

        currentFilters.page = 1;
        fetchProperties();
    });

    resetBtn.addEventListener('click', () => {
        document.getElementById('filterPurpose').value = '';
        document.getElementById('filterSearch').value = '';
        document.getElementById('filterCategory').value = '';
        document.getElementById('filterType').value = '';
        document.getElementById('minPrice').value = '';
        document.getElementById('maxPrice').value = '';
        document.getElementById('areaSize').value = '';
        
        currentFilters = { page: 1, limit: 12 };
        fetchProperties();
    });

    sortSelect.addEventListener('change', () => {
        currentFilters.sort = sortSelect.value;
        fetchProperties();
    });

    // Bed Pills
    document.querySelectorAll('#bedFilters .pill-item').forEach(pill => {
        pill.addEventListener('click', () => {
            document.querySelectorAll('#bedFilters .pill-item').forEach(p => p.classList.remove('active'));
            pill.classList.add('active');
            currentFilters.beds = pill.dataset.value;
        });
    });
}

function resetAllFilters() {
    document.getElementById('filterPurpose').value = '';
    document.getElementById('filterSearch').value = '';
    document.getElementById('filterCategory').value = '';
    document.getElementById('filterType').value = '';
    document.getElementById('minPrice').value = '';
    document.getElementById('maxPrice').value = '';
    document.getElementById('areaSize').value = '';
    
    // Reset Bed Pills
    document.querySelectorAll('#bedFilters .pill-item').forEach(p => p.classList.remove('active'));
    document.querySelector('#bedFilters .pill-item[data-value=""]').classList.add('active');

    currentFilters = { page: 1, limit: 12 };
    fetchProperties();
}

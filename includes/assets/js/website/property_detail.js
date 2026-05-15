    /**
 * Landsfy Property Detail Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    initPropertyDetail();
});

async function initPropertyDetail() {
    const urlParams = new URLSearchParams(window.location.search);
    let slug = urlParams.get('slug');

    // Fallback for SEO friendly URLs (/properties/slug)
    if (!slug) {
        const pathParts = window.location.pathname.split('/');
        // If the URL is /properties/some-slug, the slug is the last part
        if (pathParts.includes('properties')) {
            slug = pathParts[pathParts.indexOf('properties') + 1];
        }
    }

    if (!slug) {
        window.location.href = window.BASE_PATH + 'properties';
        return;
    }

    try {
        const response = await fetch(`${window.BASE_PATH}includes/api/website/property_detail_data.php?slug=${slug}`);
        const result = await response.json();

        if (result.success) {
            renderDetail(result.data);
            renderSimilar(result.similar);
            initSlider();

            // Save to Recently Viewed
            if (window.Landsfy && window.Landsfy.saveRecentlyViewed) {
                Landsfy.saveRecentlyViewed(result.data);
            }
        } else {
            document.body.innerHTML = `<div class="error-container"><h1>Error</h1><p>${result.message}</p><a href="properties">Back to Search</a></div>`;
        }
    } catch (error) {
        console.error('Failed to load property details:', error);
    }
}

function renderDetail(prop) {
    // 1. Breadcrumb & Title
    const locationName = (prop.location_name && prop.location_name !== 'null' && prop.location_name !== null) ? prop.location_name : '';
    const cityText = (prop.city_name && prop.city_name !== 'null' && prop.city_name !== null) ? (locationName ? `, ${prop.city_name}` : prop.city_name) : '';
    const finalLocation = (locationName || cityText) ? `${locationName}${cityText}, Pakistan` : 'Pakistan';
    
    document.getElementById('breadcrumbTitle').textContent = prop.title;
    document.getElementById('propTitle').textContent = prop.title;
    document.getElementById('propLocation').innerHTML = `<i class="fa-solid fa-location-dot"></i> ${finalLocation}`;
    document.getElementById('propPrice').innerHTML = `${Landsfy.formatPrice(prop.price)} ${prop.purpose === 'rent' ? '<small>/mo</small>' : ''}`;

    // 2. Badges
    const badges = document.getElementById('propBadges');
    badges.innerHTML = `
        <span class="badge-purpose">For ${prop.purpose || 'Sale'}</span>
        <span class="badge-status">${prop.status || 'Active'}</span>
        ${(prop.is_featured && prop.is_featured != '0') ? '<span class="badge-featured"><i class="fa-solid fa-crown"></i> Featured</span>' : ''}
        <span class="badge-views"><i class="fa-solid fa-eye"></i> ${prop.views_total || 0} Views</span>
    `;

    // 3. Gallery
    const track = document.getElementById('galleryTrack');
    const thumbs = document.getElementById('sliderThumbs');
    track.innerHTML = '';
    thumbs.innerHTML = '';

    if (prop.images && prop.images.length > 0) {
        prop.images.forEach((img, index) => {
            const url = Landsfy.getImageUrl(img.image_url);
            track.innerHTML += `<div class="slide"><img src="${url}" alt="Property Image" onerror="Landsfy.handleImageError(this, 'property')"></div>`;
            thumbs.innerHTML += `<div class="thumb-item ${index === 0 ? 'active' : ''}" data-index="${index}"><img src="${url}" alt="Thumb" onerror="Landsfy.handleImageError(this, 'property')"></div>`;
        });
    }

    // 4. Quick Specs (Area, Bed, Bath, Year)
    const specsGrid = document.getElementById('quickSpecsGrid');
    const areaSize = prop.area_size || '---';
    const areaUnit = prop.area_unit || '';
    const beds = prop.beds || '---';
    const baths = prop.baths || '---';
    const addedDate = prop.created_at ? new Date(prop.created_at).toLocaleDateString() : '---';

    specsGrid.innerHTML = `
        <div class="spec-box">
            <i class="fa-solid fa-vector-square"></i>
            <div class="spec-info">
                <span>Area Size</span>
                <strong>${areaSize} ${areaUnit}</strong>
            </div>
        </div>
        <div class="spec-box">
            <i class="fa-solid fa-bed"></i>
            <div class="spec-info">
                <span>Bedrooms</span>
                <strong>${beds} Beds</strong>
            </div>
        </div>
        <div class="spec-box">
            <i class="fa-solid fa-bath"></i>
            <div class="spec-info">
                <span>Bathrooms</span>
                <strong>${baths} Baths</strong>
            </div>
        </div>
        <div class="spec-box">
            <i class="fa-solid fa-calendar-days"></i>
            <div class="spec-info">
                <span>Added On</span>
                <strong>${addedDate}</strong>
            </div>
        </div>
    `;

    // 5. Description
    if (prop.description) {
        const lines = prop.description.split(/\n/);
        let descHtml = '';
        
        lines.forEach(line => {
            const trimmed = line.trim();
            if (!trimmed) return;

            // Detect list items (starting with bullet, dot, or emoji)
            if (trimmed.startsWith('•') || trimmed.startsWith('-') || trimmed.startsWith('*') || /^[^\x00-\x7F]/.test(trimmed)) {
                descHtml += `<div class="desc-list-item">${trimmed}</div>`;
            } else {
                descHtml += `<p>${trimmed}</p>`;
            }
        });
        
        document.getElementById('propDescription').innerHTML = descHtml || '<p>No description available.</p>';
    } else {
        document.getElementById('propDescription').innerHTML = '<p>No description available.</p>';
    }

    // 6. Amenities
    const amenitiesGrid = document.getElementById('amenitiesGrid');
    amenitiesGrid.innerHTML = '';
    
    let addedCount = 0;
    if (prop.amenities && prop.amenities.length > 0) {
        prop.amenities.forEach(a => {
            // Skip common specs already shown in quickSpecsGrid to avoid duplication
            if (['Bedrooms', 'Bathrooms', 'Built Year', 'Built in year'].includes(a.label)) return;

            const displayValue = (a.value === '1' || a.value === 'true' || a.value === 'yes') ? '' : `: ${a.value}`;
            const icon = a.icon_class ? `fa-solid ${a.icon_class}` : 'fa-solid fa-circle-check';

            amenitiesGrid.innerHTML += `
                <div class="amenity-item">
                    <i class="${icon}"></i> ${a.label}${displayValue}
                </div>
            `;
            addedCount++;
        });
    }

    if (addedCount === 0) {
        amenitiesGrid.innerHTML = '<div class="no-amenities-info"><i class="fa-solid fa-circle-info"></i> No additional features listed for this property.</div>';
    }

    // 7. Agent Sidebar
    const agentName = prop.agency_name || prop.owner_name;
    const agentAvatar = prop.agency_logo || prop.owner_avatar;
    document.getElementById('agentName').textContent = agentName;
    
    if (agentAvatar) {
        const avatarUrl = Landsfy.getImageUrl(agentAvatar);
        document.getElementById('agentAvatar').innerHTML = `<img src="${avatarUrl}" alt="${agentName}" onerror="Landsfy.handleImageError(this, 'agent')">`;
    } else {
        document.getElementById('agentAvatar').innerHTML = `<i class="fa-solid fa-circle-user" style="font-size: 70px; color: #94a3b8;"></i>`;
    }
    
    if (prop.owner_phone) {
        document.getElementById('btnCall').onclick = () => {
            trackInteraction(prop.id, 'call_reveal');
            window.location.href = `tel:${prop.owner_phone}`;
        };
        document.getElementById('btnWhatsapp').onclick = () => {
            trackInteraction(prop.id, 'whatsapp_click');
            const waPhone = prop.owner_phone.replace(/[^0-9]/g, '');
            window.open(`https://wa.me/${waPhone}`, '_blank');
        };
    } else {
        document.getElementById('btnCall').style.display = 'none';
        document.getElementById('btnWhatsapp').style.display = 'none';
    }

    // 8. Save Button Logic
    const saveAction = document.getElementById('detailSaveAction');
    if (window.USER_ROLE === 'buyer') {
        const heartIcon = prop.is_saved > 0 ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
        const activeClass = prop.is_saved > 0 ? 'active' : '';
        const btnLabel = prop.is_saved > 0 ? 'Saved to Favorites' : 'Save to Favorites';
        
        saveAction.innerHTML = `
            <button class="btn-save-detail ${activeClass}" onclick="toggleSaveDetail(${prop.id}, this)">
                <i class="${heartIcon}"></i> <span>${btnLabel}</span>
            </button>
        `;
    }
}

async function toggleSaveDetail(propertyId, btn) {
    if (!window.IS_LOGGED_IN) {
        window.location.href = window.BASE_PATH + 'login';
        return;
    }

    if (window.USER_ROLE !== 'buyer') {
        Swal.fire('Note', 'Only buyers can save properties to their favorites.', 'info');
        return;
    }

    const formData = new FormData();
    formData.append('property_id', propertyId);

    try {
        const response = await fetch(window.BASE_PATH + 'includes/api/buyer/toggle_save_property.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.success) {
            const icon = btn.querySelector('i');
            const span = btn.querySelector('span');
            if (data.status === 'saved') {
                icon.className = 'fa-solid fa-heart';
                btn.classList.add('active');
                span.textContent = 'Saved to Favorites';
                Swal.fire({ icon: 'success', title: 'Saved!', text: 'Added to favorites', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
            } else {
                icon.className = 'fa-regular fa-heart';
                btn.classList.remove('active');
                span.textContent = 'Save to Favorites';
                Swal.fire({ icon: 'info', title: 'Removed', text: 'Removed from favorites', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
            }
        }
    } catch (error) {
        console.error('Error toggling save:', error);
    }
}

async function trackInteraction(propId, type) {
    try {
        const formData = new FormData();
        formData.append('property_id', propId);
        formData.append('type', type);
        
        await fetch(window.BASE_PATH + 'includes/api/website/track_interaction.php', {
            method: 'POST',
            body: formData
        });
    } catch (e) {
        console.error('Tracking failed:', e);
    }
}

function renderSimilar(properties) {
    const grid = document.getElementById('similarPropertiesGrid');
    if (!properties || properties.length === 0) {
        grid.closest('.similar-properties-section').style.display = 'none';
        return;
    }

    let html = '';
    properties.forEach(prop => {
        const thumb = Landsfy.getImageUrl(prop.thumbnail);
        html += `
            <div class="property-card" onclick="window.location.href='${window.BASE_PATH}properties/${prop.slug}'" style="cursor:pointer;">
                <div class="property-img-wrapper">
                    <img src="${thumb}" class="property-img" loading="lazy" onerror="Landsfy.handleImageError(this, 'property')">
                    <div class="property-sale-badge">For ${prop.purpose || 'Sale'}</div>
                </div>
                <div class="property-content">
                    <h3 class="property-title">${prop.title}</h3>
                    <div class="property-location"><i class="fa-solid fa-location-dot"></i> ${prop.city_name}</div>
                    <div class="property-price">${Landsfy.formatPrice(prop.price)}</div>
                </div>
            </div>
        `;
    });
    grid.innerHTML = html;

    // Initialize Slider Navigation
    const nextBtn = document.getElementById('similarNext');
    const prevBtn = document.getElementById('similarPrev');
    
    if (nextBtn && prevBtn) {
        nextBtn.onclick = () => {
            const cardWidth = grid.querySelector('.property-card').offsetWidth + 24;
            grid.scrollBy({ left: cardWidth, behavior: 'smooth' });
        };
        prevBtn.onclick = () => {
            const cardWidth = grid.querySelector('.property-card').offsetWidth + 24;
            grid.scrollBy({ left: -cardWidth, behavior: 'smooth' });
        };
    }
}

function initSlider() {
    const track = document.getElementById('galleryTrack');
    const slides = track.querySelectorAll('.slide');
    const nextBtn = document.getElementById('sliderNext');
    const prevBtn = document.getElementById('sliderPrev');
    const thumbs = document.querySelectorAll('.thumb-item');
    
    if (slides.length <= 1) {
        nextBtn.style.display = 'none';
        prevBtn.style.display = 'none';
        return;
    }

    let currentIndex = 0;

    function goToSlide(index) {
        currentIndex = index;
        const offset = -currentIndex * 100;
        track.style.transform = `translateX(${offset}%)`;
        
        // Update thumbs
        thumbs.forEach(t => t.classList.remove('active'));
        thumbs[currentIndex].classList.add('active');
    }

    nextBtn.onclick = () => {
        currentIndex = (currentIndex + 1) % slides.length;
        goToSlide(currentIndex);
    };

    prevBtn.onclick = () => {
        currentIndex = (currentIndex - 1 + slides.length) % slides.length;
        goToSlide(currentIndex);
    };

    thumbs.forEach(thumb => {
        thumb.onclick = () => goToSlide(parseInt(thumb.dataset.index));
    });
}


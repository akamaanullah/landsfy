document.addEventListener('DOMContentLoaded', () => {
    fetchListings();

    // Listeners for filters
    const searchInput = document.getElementById('listingSearch');
    
    if (searchInput) {
        searchInput.addEventListener('input', Landsfy.debounce(fetchListings, 500));
    }
    
    // Custom dropdown logic is handled in script.js, but we need to trigger fetch on change
    setupFilterTriggers();

    // Handle initial success toast from URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('status') === 'success') {
        if (typeof Landsfy.showToast === 'function') {
            Landsfy.showToast('listing posted successfully!', 'success');
        }
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});

function setupFilterTriggers() {
    document.querySelectorAll('.dropdown-item').forEach(item => {
        item.addEventListener('click', function() {
            // Trigger fetch with a slight delay to allow hidden inputs to update if needed
            setTimeout(fetchListings, 50);
        });
    });
}

async function fetchListings() {
    const search = document.getElementById('listingSearch')?.value || '';
    const status = document.querySelector('#statusFilter .dropdown-item.active')?.dataset.value || 'all';
    const type = document.querySelector('#typeFilter .dropdown-item.active')?.dataset.value || 'all';
    const purpose = document.querySelector('#purposeFilter .dropdown-item.active')?.dataset.value || 'all';

    const grid = document.getElementById('listingsContainer');
    if (grid) grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 60px;"><i class="fa-solid fa-circle-notch fa-spin spinner" style="font-size: 32px; color: var(--primary);"></i></div>';

    const url = `../includes/api/seller/get_listings.php?search=${search}&status=${status}&type=${type}&purpose=${purpose}`;
    
    try {
        const response = await fetch(url);
        const result = await response.json();

        if (result.success) {
            renderListings(result.data);
        }
    } catch (error) {
        console.error('Error fetching listings:', error);
    }
}

function renderListings(listings) {
    const grid = document.getElementById('listingsContainer');
    if (!grid) return;

    if (listings.length === 0) {
        grid.innerHTML = `<div style="text-align: center; width: 100%; padding: 60px; grid-column: 1/-1;">
                            <i class="fa-solid fa-house-chimney" style="font-size: 48px; opacity: 0.2; margin-bottom: 16px;"></i>
                            <p style="color: var(--text-secondary);">No properties found matching your filters.</p>
                          </div>`;
        return;
    }

    grid.innerHTML = listings.map(prop => {
        const currentStatus = prop.status || 'under_review';
        const badgeClass = `badge-${currentStatus}`;
        const displayStatus = currentStatus.replace('_', ' ').toUpperCase();

        return `
            <div class="property-card glass lazy-reveal">
                <div class="card-image" style="position: relative; height: 210px; overflow: hidden; border-radius: 20px 20px 0 0;">
                    <img src="${(prop.image_url.startsWith('http') || prop.image_url.startsWith('../')) ? prop.image_url : '../' + prop.image_url}" 
                         loading="lazy"
                         alt="${prop.title}" 
                         style="width: 100%; height: 100%; object-fit: cover; transition: 0.5s transform;">
                    <div class="card-badge ${badgeClass}">${displayStatus}</div>
                    <div class="card-overlay-actions">
                        <button class="overlay-btn" title="View Details" onclick="window.location.href='view-property.php?id=${prop.id}'"><i class="fa-solid fa-eye"></i></button>
                    </div>
                </div>
                <div class="card-content" style="padding: 24px;">
                    <div class="card-price" style="font-size: 24px; font-weight: 800; color: var(--primary); margin-bottom: 4px; font-family: 'Outfit';">
                        PKR ${Landsfy.formatPrice(prop.price)}
                    </div>
                    <div class="card-title" style="font-size: 18px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        ${prop.title}
                    </div>
                    <div class="card-location" style="font-size: 13px; color: var(--text-secondary); margin-bottom: 20px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-map-pin" style="color: var(--primary);"></i> ${prop.city_name || 'N/A'}
                    </div>
                    
                    <div class="card-metrics-row" style="display: flex; gap: 24px; border-top: 1px solid var(--glass-border); padding-top: 20px; margin-bottom: 24px;">
                        <div class="metric-item" style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(107, 0, 182, 0.05); display: flex; align-items: center; justify-content: center; color: var(--primary);">
                                <i class="fa-solid fa-eye"></i>
                            </div>
                            <div>
                                <div style="font-size: 14px; font-weight: 700; color: var(--text-primary);">${Landsfy.formatNumber(prop.views_total)}</div>
                                <div style="font-size: 10px; color: var(--text-secondary); text-transform: uppercase;">Views</div>
                            </div>
                        </div>
                        <div class="metric-item" style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(16, 185, 129, 0.05); display: flex; align-items: center; justify-content: center; color: var(--success);">
                                <i class="fa-solid fa-comments"></i>
                            </div>
                            <div>
                                <div style="font-size: 14px; font-weight: 700; color: var(--text-primary);">${prop.total_clicks || 0}</div>
                                <div style="font-size: 10px; color: var(--text-secondary); text-transform: uppercase;">Leads</div>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 12px;">
                        <button class="btn-primary" onclick="window.location.href='add-listing.php?id=${prop.id}'" style="flex: 1; justify-content: center; background: rgba(59, 130, 246, 0.1); color: var(--info); border: 1px solid rgba(59, 130, 246, 0.2); box-shadow: none;">
                            <i class="fa-solid fa-pencil"></i> Edit
                        </button>
                        <div class="action-dropdown-wrapper" style="position: relative;">
                            <button class="icon-btn action-menu" onclick="toggleActionMenu(event, ${prop.id})" style="border-color: var(--glass-border); background: var(--glass-bg);">
                                <i class="fa-solid fa-circles-three-vertical"></i>
                            </button>
                            <div class="action-menu-dropdown glass" id="menu-${prop.id}" style="display: none; position: absolute; right: 0; bottom: 100%; margin-bottom: 10px; min-width: 160px; z-index: 1000; padding: 8px; border-radius: 12px; border: 1px solid var(--glass-border);">
                                <div class="menu-item" onclick="updateStatus(${prop.id}, 'sold')" style="padding: 10px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 600; color: var(--success);"><i class="fa-solid fa-circle-check"></i> Mark as Sold</div>
                                <div class="menu-item" onclick="updateStatus(${prop.id}, 'delete')" style="padding: 10px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 600; color: var(--danger);"><i class="fa-solid fa-trash-can"></i> Delete Listing</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');

    // Initialize premium reveals
    Landsfy.initLazyLoading();
}

function toggleActionMenu(event, id) {
    event.stopPropagation();
    const allMenus = document.querySelectorAll('.action-menu-dropdown');
    const targetMenu = document.getElementById(`menu-${id}`);
    
    allMenus.forEach(m => {
        if (m !== targetMenu) m.style.display = 'none';
    });
    
    if (targetMenu) {
        targetMenu.style.display = targetMenu.style.display === 'none' ? 'block' : 'none';
    }
}

// Close menus on click outside
document.addEventListener('click', () => {
    document.querySelectorAll('.action-menu-dropdown').forEach(m => m.style.display = 'none');
});

function updateStatus(id, action) {
    const actionLabel = action === 'sold' ? 'MARK AS SOLD' : (action === 'delete' ? 'DELETE' : action);
    const confirmColor = action === 'delete' ? '#ef4444' : '#6b00b6';
    
    Swal.fire({
        title: 'Are you sure?',
        text: `Do you want to ${actionLabel} this listing?`,
        icon: action === 'delete' ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonColor: confirmColor,
        cancelButtonColor: '#94a3b8',
        confirmButtonText: `Yes, ${action}!`,
        background: 'rgba(15, 12, 33, 0.95)',
        color: '#ffffff',
        backdrop: `rgba(0, 0, 0, 0.6) blur(6px)`,
        didOpen: () => {
            const popup = Swal.getPopup();
            popup.style.border = '1px solid rgba(255, 255, 255, 0.1)';
            popup.style.borderRadius = '24px';
            popup.style.boxShadow = '0 25px 50px -12px rgba(0, 0, 0, 0.5)';
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('property_id', id);
            formData.append('action', action);

            fetch('../includes/api/agent/update_listing_status.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if(data.success) {
                        if (typeof Landsfy.showToast === 'function') {
                            Landsfy.showToast(data.message || 'Listing updated successfully!', 'success');
                        } else {
                            Swal.fire('Updated!', data.message, 'success');
                        }
                        fetchListings();
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(() => Swal.fire('Error', "Could not update listing at this time.", 'error'));
        }
    });
}


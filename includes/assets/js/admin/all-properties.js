/**
 * Admin Global Inventory Logic
 */

let currentPage = 1;
const limit = 12;
let totalFiltered = 0;

document.addEventListener('DOMContentLoaded', function() {
    fetchInventoryData();
    
    // Handle Filter Form Submission
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            currentPage = 1; // Reset to first page
            fetchInventoryData('', true); // true = replaceMode
        });

        // Add Debounced Search
        const searchInput = filterForm.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.addEventListener('input', Landsfy.debounce(() => {
                currentPage = 1;
                fetchInventoryData('', true);
            }, 500));
        }
    }

    // Handle Load More Button
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', () => {
            currentPage++;
            fetchInventoryData('', false); // false = appendMode
        });
    }
});

async function fetchInventoryData(queryString = '', replaceMode = true) {
    const gridContainer = document.getElementById('propertyGrid');
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    
    // If replaceMode, show loading spinner in the grid
    if (replaceMode) {
        gridContainer.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 80px;"><i class="fa-solid fa-circle-notch fa-spin fa-spin" style="font-size: 48px; opacity: 0.5;"></i><p>Searching inventory...</p></div>';
        currentPage = 1;
    }

    // Update Load More Button State
    if (loadMoreBtn) {
        loadMoreBtn.disabled = true;
        loadMoreBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin fa-spin"></i> Loading...';
    }

    try {
        const filterForm = document.getElementById('filterForm');
        const formData = new FormData(filterForm);
        formData.append('page', currentPage);
        formData.append('limit', limit);
        const params = new URLSearchParams(formData).toString();

        const response = await fetch(`../includes/api/admin/get_all_properties.php?${params}`);
        const result = await response.json();
        
        if (result.success) {
            updateStats(result.data.stats);
            renderListings(result.data.listings, replaceMode);
            
            if (loadMoreBtn) {
                if (result.data.listings.length < limit) {
                    loadMoreBtn.style.display = 'none';
                } else {
                    loadMoreBtn.style.display = 'block';
                    loadMoreBtn.disabled = false;
                    loadMoreBtn.innerHTML = 'Load More Properties';
                }
            }
        } else {
            Landsfy.showToast(result.message || 'Failed to search inventory', 'error');
            if (loadMoreBtn) {
                loadMoreBtn.disabled = false;
                loadMoreBtn.innerHTML = 'Load More Properties';
            }
        }
    } catch (error) {
        console.error('Inventory Fetch Error:', error);
        Landsfy.showToast('Network error while searching', 'error');
        if (loadMoreBtn) {
            loadMoreBtn.disabled = false;
            loadMoreBtn.innerHTML = 'Load More Properties';
        }
    }
}

function updateStats(stats) {
    if (document.getElementById('statTotal')) {
        document.getElementById('statTotal').innerText = (stats.total || 0).toLocaleString();
        document.getElementById('statPublished').innerText = (stats.published || 0).toLocaleString();
        document.getElementById('statReview').innerText = (stats.pending || 0).toLocaleString();
        document.getElementById('statSold').innerText = (stats.sold || 0).toLocaleString();
    }
}

function renderListings(listings, replaceMode) {
    const container = document.getElementById('propertyGrid');
    if (!container) return;

    if (listings.length === 0 && replaceMode) {
        container.innerHTML = `
            <div class="empty-state glass" style="grid-column: 1/-1; width: 100%; padding: 80px; text-align: center;">
                <i class="fa-solid fa-magnifying-glass-minus" style="font-size: 64px; opacity: 0.3; margin-bottom: 20px;"></i>
                <h3>No Properties Found</h3>
                <p style="color: var(--text-secondary);">Try adjusting your search filters if needed.</p>
            </div>`;
        return;
    }

    const html = listings.map(item => {
        // Premium Badge Logic
        let premiumBadge = '';
        if (item.premium_type && item.premium_type !== 'none') {
            const tierColor = item.premium_type === 'diamond' ? '#3B82F6' : '#10B981';
            const tierIcon = item.premium_type === 'diamond' ? 'fa-solid fa-gem' : 'fa-solid fa-diamond';
            
            let daysLeft = '';
            if (item.premium_status === 'active' && item.premium_expiry) {
                const diff = Math.ceil((new Date(item.premium_expiry) - new Date()) / (1000 * 60 * 60 * 24));
                daysLeft = `<span style="font-size: 10px; opacity: 0.8; margin-left: 5px;">(${Math.max(0, diff)}d left)</span>`;
            } else if (item.premium_status === 'pending') {
                daysLeft = `<span style="font-size: 10px; opacity: 0.8; margin-left: 5px;">(Pending)</span>`;
            }

            premiumBadge = `
                <div class="premium-tier-tag" style="position: absolute; bottom: 10px; left: 10px; background: ${tierColor}; color: white; padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: 800; display: flex; align-items: center; gap: 5px; box-shadow: 0 4px 12px ${tierColor}44; z-index: 10;">
                    <i class="${tierIcon}"></i> ${item.premium_type.toUpperCase()} ${daysLeft}
                </div>
            `;
        }

        return `
            <div class="property-card glass lazy-reveal">
                <div class="card-image-wrapper">
                    <div class="card-badge ${getStatusBadgeClass(item.status)}">
                        ${(item.status || '').replace('_', ' ').toUpperCase()}
                    </div>
                    <img src="${Landsfy.getImageUrl(item.featured_image)}" 
                         loading="lazy"
                         alt="Property" class="card-image"
                         onerror="Landsfy.handleImageError(this, 'property')">
                    ${premiumBadge}
                </div>
                <div class="card-content">
                    <div class="card-price">${item.currency || 'PKR'} ${Landsfy.formatPrice(item.price)}</div>
                    <h4 class="card-title">${Landsfy.escapeHtml(item.title)}</h4>
                    <div class="card-location"><i class="fa-solid fa-location-dot"></i> ${Landsfy.escapeHtml(item.location_name || 'Location N/A')}</div>
                    <div class="card-features">
                        <div class="feature-item" title="Category"><i class="fa-solid fa-building"></i> ${item.cat_name}</div>
                        <div class="feature-item" title="Area"><i class="fa-solid fa-vector-square"></i> ${item.area_size} ${item.area_unit}</div>
                    </div>
                </div>
                <div class="card-actions">
                    <div class="card-meta"><i class="fa-solid fa-user"></i> ${Landsfy.escapeHtml(item.author_name)}</div>
                    <div class="action-btns">
                        <button class="card-action-btn" title="View" onclick="window.location.href='property-detail.php?id=${item.id}'"><i class="fa-solid fa-eye"></i></button>
                        <button class="card-action-btn" title="Edit" onclick="window.location.href='add-property.php?id=${item.id}'"><i class="fa-solid fa-pencil"></i></button>
                        <button class="card-action-btn action-delete" onclick="handleDelete(${item.id})" title="Delete"><i class="fa-solid fa-trash-can"></i></button>
                    </div>
                </div>
            </div>
        `;
    }).join('');

    if (replaceMode) {
        container.innerHTML = html;
    } else {
        container.insertAdjacentHTML('beforeend', html);
    }

    // Initialize premium reveals
    Landsfy.initLazyLoading();
}

function getStatusBadgeClass(status) {
    switch(status) {
        case 'active': return 'badge-active';
        case 'under_review': return 'badge-pending';
        case 'sold': return 'badge-sold';
        default: return '';
    }
}

function handleDelete(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This listing will be permanently removed.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff4757',
        confirmButtonText: 'Yes, Delete'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const formData = new FormData();
                formData.append('action', 'listing_delete');
                formData.append('id', id);
                const res = await fetch('../includes/api/admin/update_status.php', { method: 'POST', body: formData });
                const resultData = await res.json();
                
                if (resultData.success) {
                    Swal.fire('Deleted!', 'Listing has been removed.', 'success').then(() => {
                        currentPage = 1;
                        fetchInventoryData('', true);
                    });
                } else {
                    Landsfy.showToast(resultData.message || 'Failed to delete listing', 'error');
                }
            } catch (err) {
                console.error('Delete Error:', err);
                Landsfy.showToast('Network error during deletion', 'error');
            }
        }
    });
}



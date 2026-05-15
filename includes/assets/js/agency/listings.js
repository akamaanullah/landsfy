document.addEventListener('DOMContentLoaded', function() {
    let currentStatus = '';

    fetchListings();

    // Stats Cards Filtering
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach(card => {
        card.addEventListener('click', function() {
            statCards.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            
            currentStatus = this.getAttribute('data-filter') || '';
            fetchListings(currentStatus);
        });
    });

    // Search Logic
    const searchInput = document.getElementById('propertySearch');
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                fetchListings(currentStatus);
            }, 500);
        });
    }
});

async function fetchListings(statusFilter = '') {
    const grid = document.getElementById('listingsGrid');
    if (!grid) return;

    grid.innerHTML = `
        <div style="grid-column: 1/-1; text-align: center; padding: 40px; opacity: 0.5;">
            <i class="fa-solid fa-circle-notch fa-spin spinner" style="font-size: 32px; color: var(--primary);"></i>
            <p style="margin-top: 12px;">Fetching inventory...</p>
        </div>
    `;

    try {
        const search = document.getElementById('propertySearch')?.value || '';
        const params = new URLSearchParams();
        if (statusFilter) params.append('status', statusFilter);
        if (search) params.append('search', search);

        const response = await fetch(`../includes/api/agency/get_agency_listings.php?${params.toString()}`);
        const data = await response.json();
        
        if (data.success) {
            if (data.listings.length === 0) {
                grid.innerHTML = `
                    <div style="grid-column: 1/-1; text-align: center; padding: 100px 40px; opacity: 0.5;">
                        <i class="fa-solid fa-house-chimney" style="font-size: 64px;"></i>
                        <h3 style="margin-top: 20px;">No properties found</h3>
                        <p>Your team hasn't listed any properties matching this filter yet.</p>
                    </div>
                `;
                return;
            }

            grid.innerHTML = data.listings.map(listing => `
                <div class="property-card glass">
                    <div class="card-header">
                        <img src="${Landsfy.getImageUrl(listing.image_url)}" alt="${listing.title}" class="property-img" onerror="Landsfy.handleImageError(this, 'property')">
                        <div class="card-badges">
                            <span class="badge-type">${listing.purpose.toUpperCase()}</span>
                            <span class="badge-status status-${listing.status}">${listing.status.replace('_', ' ').toUpperCase()}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="agent-tag" style="font-size: 11px; opacity: 0.6; margin-bottom: 8px; display: flex; align-items: center; gap: 5px;">
                            <i class="fa-solid fa-user"></i> Listed by: ${listing.agent_name || 'Owner'}
                        </div>
                        <h4 class="property-title">${listing.title}</h4>
                        <p class="property-location"><i class="fa-solid fa-location-dot"></i> ${listing.location_name || 'Location Not Set'}</p>
                        
                        <div class="property-features" style="margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border-color);">
                            <span><i class="fa-solid fa-selection-square"></i> ${listing.area_size} ${listing.area_unit}</span>
                            <span><i class="fa-solid fa-calendar-days"></i> ${new Date(listing.created_at).toLocaleDateString()}</span>
                        </div>
                        
                        <div class="card-footer" style="margin-top: 15px; display: flex; justify-content: space-between; align-items: center;">
                             <div class="property-price" style="font-size: 16px; font-weight: 700; color: var(--primary);">PKR ${Landsfy.formatPrice(listing.price)}</div>
                            <div class="card-actions" style="display: flex; gap: 8px;">
                                <a href="edit-property.php?id=${listing.id}" class="icon-btn-small" title="Edit Listing"><i class="fa-solid fa-pencil"></i></a>
                                <button class="icon-btn-small delete-btn" style="color: #ff4757" onclick="deleteProperty(${listing.id})" title="Delete Listing"><i class="fa-solid fa-trash-can"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Error fetching listings:', error);
    }
}

function deleteProperty(id) {
    Swal.fire({
        title: 'Delete Property?',
        text: "This will move the property to archived/deleted. This cannot be undone.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff4757',
        confirmButtonText: 'Yes, Delete it!',
        background: '#1a1d21',
        color: '#fff'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const formData = new FormData();
                formData.append('id', id);
                const response = await fetch('../includes/api/agency/delete_agency_property.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    Swal.fire({
                        title: 'Deleted!',
                        icon: 'success',
                        background: '#1a1d21',
                        color: '#fff'
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'Failed to delete property', 'error');
            }
        }
    });
}


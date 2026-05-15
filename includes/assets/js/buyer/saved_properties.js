document.addEventListener('DOMContentLoaded', function() {
    fetchSavedProperties();
});

async function fetchSavedProperties() {
    const grid = document.querySelector('.property-grid');
    try {
        const response = await fetch('../includes/api/buyer/get_saved_properties.php');
        const data = await response.json();
        
        if (data.success) {
            if (data.properties.length === 0) {
                grid.innerHTML = `
                    <div class="empty-state-container">
                        <div class="empty-state-icon">
                            <i class="fa-regular fa-heart-break"></i>
                        </div>
                        <h3 class="empty-state-title">No Saved Properties</h3>
                        <p class="empty-state-desc">You haven't saved any listings yet. Start exploring to build your favorites list.</p>
                        <a href="../properties.php" class="btn-primary" style="margin-top: 10px;">Explore Properties</a>
                    </div>
                `;
                return;
            }

            grid.innerHTML = data.properties.map(prop => `
                <div class="property-card glass">
                    <div class="card-image" style="position: relative;">
                        <img src="${Landsfy.getImageUrl(prop.main_image)}" alt="Property" style="width: 100%; height: 200px; object-fit: cover; border-radius: 16px;" onerror="Landsfy.handleImageError(this, 'property')">
                        <button class="heart-btn" onclick="toggleSave(${prop.id})" style="position: absolute; top: 12px; right: 12px; background: white; border: none; width: 36px; height: 36px; border-radius: 50%; color: var(--danger); display: flex; align-items: center; justify-content: center; font-size: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);"><i class="fa-solid fa-heart"></i></button>
                        <div class="status-tag" style="position: absolute; bottom: 12px; left: 12px; background: ${prop.purpose === 'sell' ? 'var(--info)' : 'var(--primary-gradient)'}; color: white; padding: 4px 12px; border-radius: 8px; font-size: 12px; font-weight: 700;">FOR ${prop.purpose.toUpperCase()}</div>
                    </div>
                    <div class="card-content" style="padding: 20px;">
                        <div class="card-price" style="font-size: 20px; font-weight: 800; color: var(--primary); margin-bottom: 4px;">PKR ${new Intl.NumberFormat().format(prop.price)} ${prop.purpose === 'rent' ? '<span style="font-size: 12px; color: var(--text-secondary);">/ mon</span>' : ''}</div>
                        <div class="card-title" style="font-weight: 700; font-size: 16px; margin-bottom: 8px; color: var(--text-primary);">${prop.title}</div>
                        <div class="card-location" style="font-size: 13px; color: var(--text-secondary); margin-bottom: 16px;"><i class="fa-solid fa-location-dot"></i> ${prop.city_name}, ${prop.location_name || 'N/A'}</div>
                        
                        <div style="display: flex; gap: 10px;">
                            <a href="../property-detail.php?slug=${prop.slug}" class="btn-primary" style="flex: 1; justify-content: center; text-decoration: none;">View Detail</a>
                        </div>
                    </div>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Error fetching properties:', error);
    }
}

async function toggleSave(propertyId) {
    const formData = new FormData();
    formData.append('property_id', propertyId);

    try {
        const response = await fetch('../includes/api/buyer/toggle_save_property.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Removed!',
                text: 'Property removed from favorites.',
                timer: 2000,
                showConfirmButton: false
            });
            fetchSavedProperties();
        }
    } catch (error) {
        console.error('Error toggling save:', error);
    }
}

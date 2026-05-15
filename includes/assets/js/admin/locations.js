/**
 * Admin Location Management Logic
 * Handles City/Area CRUD and active filters
 */

let activeCityId = null;

document.addEventListener('DOMContentLoaded', function() {
    // Check if there's a city pre-selected or just wait for interaction
});

window.selectCity = async function(id, name, el) {
    activeCityId = id;
    
    // UI feedback for selection
    document.querySelectorAll('.city-item').forEach(i => {
        i.style.background = 'transparent';
        i.style.borderColor = 'var(--border-color)';
    });
    
    el.style.background = 'rgba(108, 93, 211, 0.1)';
    el.style.borderColor = 'var(--primary)';

    document.getElementById('locationsTitle').innerText = `2. Locations in ${name}`;
    document.getElementById('addLocationBtn').style.display = 'flex';
    document.getElementById('locationsPlaceholder').style.display = 'none';
    const grid = document.getElementById('locationsGrid');
    grid.style.display = 'grid';

    fetchLocations();
};

async function fetchLocations() {
    const grid = document.getElementById('locationsGrid');
    grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 40px;"><i class="fa-solid fa-circle-notch fa-spin fa-spin" style="font-size: 32px; color: var(--primary);"></i></div>';

    try {
        const res = await fetch(`../includes/api/admin/get_locations.php?city_id=${activeCityId}`);
        const result = await res.json();
        
        if (result.success) {
            if (result.data.length === 0) {
                grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 40px; opacity: 0.5;">No locations added in this city yet.</div>';
                return;
            }
            
            grid.innerHTML = result.data.map(loc => `
                <div class="glass" style="padding: 12px 16px; border-radius: 12px; display: flex; align-items: center; justify-content: space-between; border: 1px solid var(--border-color);">
                    <span style="font-size: 13px; font-weight: 500;">${loc.name}</span>
                    <div style="display: flex; gap: 8px;">
                        <i class="fa-solid fa-pencil" style="font-size: 14px; opacity: 0.5; cursor: pointer;" onclick="editLocation(${JSON.stringify(loc).replace(/"/g, '&quot;')})"></i>
                        <i class="fa-solid fa-trash-can" style="font-size: 14px; color: #ff4757; opacity: 0.5; cursor: pointer;" onclick="deleteLocation(${loc.id})"></i>
                    </div>
                </div>
            `).join('');
        }
    } catch (e) { 
        grid.innerHTML = '<div style="color: #ff4757; padding: 20px;">Error loading locations.</div>'; 
    }
}

// Modal Toggle Helpers
window.openCityModal = () => { 
    document.getElementById('cityModalTitle').innerText = 'Add New City';
    document.getElementById('cityId').value = '';
    document.getElementById('cityName').value = '';
    document.getElementById('cityPopular').checked = false;
    showModal('cityModal');
};

window.openLocationModal = () => { 
    document.getElementById('locModalTitle').innerText = 'Add Area / Sector';
    document.getElementById('locId').value = '';
    document.getElementById('locName').value = '';
    showModal('locationModal');
};

const showModal = (id) => {
    const el = document.getElementById(id);
    el.style.display = 'flex';
    setTimeout(() => {
        el.style.opacity = '1'; 
        el.style.pointerEvents = 'all'; 
    }, 10);
};

window.closeModal = (id) => { 
    const el = document.getElementById(id);
    el.style.opacity = '0'; 
    el.style.pointerEvents = 'none'; 
    setTimeout(() => { el.style.display = 'none'; }, 300);
};

window.editCity = (city) => {
    document.getElementById('cityModalTitle').innerText = 'Edit City';
    document.getElementById('cityId').value = city.id;
    document.getElementById('cityName').value = city.name;
    document.getElementById('cityPopular').checked = city.is_popular == 1;
    showModal('cityModal');
};

window.saveCity = async function() {
    const id = document.getElementById('cityId').value;
    const name = document.getElementById('cityName').value;
    const popular = document.getElementById('cityPopular').checked;

    if (!name) return showToast("City name is required", "error");

    const formData = new FormData();
    formData.append('action', id ? 'edit_city' : 'add_city');
    if (id) formData.append('id', id);
    formData.append('name', name);
    formData.append('is_popular', popular ? '1' : '0');

    try {
        const res = await fetch('../includes/api/admin/manage_locations.php', { method: 'POST', body: formData });
        const result = await res.json();
        if (result.success) {
            closeModal('cityModal');
            Swal.fire('Success', 'City data updated.', 'success').then(() => location.reload());
        } else {
            showToast(result.message, "error");
        }
    } catch (e) {
        showToast("Request failed", "error");
    }
};

window.deleteCity = async function(id) {
    const result = await Swal.fire({
        title: 'Delete City?',
        text: "This will remove all locations in this city forever!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff4757',
        confirmButtonText: 'Yes, Delete All'
    });

    if (result.isConfirmed) {
        const formData = new FormData();
        formData.append('action', 'delete_city');
        formData.append('id', id);
        try {
            const res = await fetch('../includes/api/admin/manage_locations.php', { method: 'POST', body: formData });
            if ((await res.json()).success) {
                Swal.fire('Deleted', 'City and locations removed.', 'success').then(() => location.reload());
            }
        } catch (e) { showToast("Error deleting city", "error"); }
    }
};

window.editLocation = (loc) => {
    document.getElementById('locModalTitle').innerText = 'Edit Location';
    document.getElementById('locId').value = loc.id;
    document.getElementById('locName').value = loc.name;
    showModal('locationModal');
};

window.saveLocation = async function() {
    const id = document.getElementById('locId').value;
    const name = document.getElementById('locName').value;

    if (!name || !activeCityId) return showToast("Location name required", "error");

    const formData = new FormData();
    formData.append('action', id ? 'edit_location' : 'add_location');
    if (id) formData.append('id', id);
    formData.append('city_id', activeCityId);
    formData.append('name', name);

    try {
        const res = await fetch('../includes/api/admin/manage_locations.php', { method: 'POST', body: formData });
        const result = await res.json();
        if (result.success) {
            closeModal('locationModal');
            showToast("Location saved", "success");
            fetchLocations();
        } else {
            showToast(result.message, "error");
        }
    } catch (e) { showToast("Request failed", "error"); }
};

window.deleteLocation = async function(id) {
    const result = await Swal.fire({
        title: 'Delete Location?',
        text: "Remove this sector/society from the platform?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff4757',
        confirmButtonText: 'Yes, Delete'
    });

    if (result.isConfirmed) {
        const formData = new FormData();
        formData.append('action', 'delete_location');
        formData.append('id', id);
        try {
            const res = await fetch('../includes/api/admin/manage_locations.php', { method: 'POST', body: formData });
            if ((await res.json()).success) {
                showToast("Location removed", "success");
                fetchLocations();
            }
        } catch (e) { showToast("Error deleting location", "error"); }
    }
};

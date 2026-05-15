/**
 * Admin Agency Management Logic
 */

document.addEventListener('DOMContentLoaded', function() {
    fetchAgenciesData();

    const addBtn = document.getElementById('addAgencyBtn');
    if (addBtn) {
        addBtn.addEventListener('click', showAddAgencyModal);
    }

    setupSearch();
});

let agenciesData = [];

async function fetchAgenciesData() {
    const listContainer = document.getElementById('agenciesContainer');
    listContainer.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 40px;"><i class="fa-solid fa-circle-notch fa-spin fa-spin" style="font-size: 32px;"></i><p>Loading agencies...</p></div>';

    try {
        const response = await fetch('../includes/api/admin/get_agencies.php');
        const result = await response.json();
        
        if (result.success) {
            agenciesData = result.data.agencies;
            updateStats(result.data.stats);
            renderAgencies(agenciesData);
        } else {
            Landsfy.showToast(result.message || 'Failed to load agencies', 'error');
        }
    } catch (error) {
        console.error('Fetch Error:', error);
        Landsfy.showToast('Network error while loading agencies', 'error');
    }
}

function updateStats(stats) {
    if (document.getElementById('statTotal')) {
        document.getElementById('statTotal').innerText = (stats.total || 0).toLocaleString();
        document.getElementById('statVerified').innerText = (stats.verified || 0).toLocaleString();
        document.getElementById('statPending').innerText = (stats.pending || 0).toLocaleString();
        
        // Update pending card emphasis with robust ID-based search
        const pendingCard = document.getElementById('statPending').closest('.stat-card');
        if (pendingCard) {
            if (stats.pending > 0) pendingCard.classList.add('active');
            else pendingCard.classList.remove('active');
        }
    }
}

function renderAgencies(agencies) {
    const container = document.getElementById('agenciesContainer');
    if (!container) return;

    if (agencies.length === 0) {
        container.innerHTML = `
            <div class="card-panel glass" style="grid-column: 1/-1; padding: 60px; text-align: center;">
                <i class="fa-solid fa-building" style="font-size: 64px; opacity: 0.2; margin-bottom: 20px;"></i>
                <h3>No Agencies Registered</h3>
                <p style="color: var(--text-secondary);">Currently there are no agencies on the platform.</p>
            </div>`;
        return;
    }

    container.innerHTML = agencies.map(agency => `
        <div class="agency-card glass" data-id="${agency.id}">
            <div class="agency-header">
                <img src="${Landsfy.getImageUrl(agency.logo_url)}" 
                     alt="logo" class="agency-logo"
                     onerror="Landsfy.handleImageError(this, 'agency')">
                <div class="agency-info">
                    <h3 class="agency-name">${Landsfy.escapeHtml(agency.name)}</h3>
                    <div class="agency-owner">Owner: <span>${Landsfy.escapeHtml(agency.owner_name)}</span></div>
                </div>
                ${agency.is_verified == 1 ? '<div class="verify-badge" title="Verified Agency"><i class="fa-solid fa-circle-check"></i></div>' : ''}
            </div>
            <div class="agency-stats">
                <div class="stat-item">
                    <div class="stat-val">${agency.listing_count}</div>
                    <div class="stat-lbl">Listings</div>
                </div>
                <div class="stat-item">
                    <div class="stat-val">${agency.agent_count}</div>
                    <div class="stat-lbl">Agents</div>
                </div>
                <div class="stat-item">
                    <div class="stat-val">${Landsfy.formatDate(agency.created_at)}</div>
                    <div class="stat-lbl">Joined</div>
                </div>
            </div>
            <div class="agency-actions">
                <button class="btn-preview" onclick="window.location.href='agency-detail.php?id=${agency.id}'"><i class="fa-solid fa-eye"></i> View</button>
                <div style="display: flex; gap: 8px;">
                    ${agency.is_verified == 0 ? `
                        <button class="card-action-btn" style="color: var(--success);" onclick="verifyAgency(${agency.id})" title="Verify">
                            <i class="fa-solid fa-check"></i>
                        </button>` : ''}
                    <button class="card-action-btn" title="Edit"><i class="fa-solid fa-pencil"></i></button>
                    <button class="card-action-btn action-delete" onclick="deleteAgency(${agency.id})" title="Delete">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

function verifyAgency(id) {
    Swal.fire({
        title: 'Verify Agency?',
        text: "This will mark the agency as verified and active.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: 'var(--primary)',
        cancelButtonColor: '#ff4757',
        confirmButtonText: 'Yes, Verify!'
    }).then(async (result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'agency_verify');
            formData.append('id', id);
            const res = await fetch('../includes/api/admin/update_status.php', { method: 'POST', body: formData });
            if ((await res.json()).success) {
                Swal.fire('Verified!', 'Agency has been successfully verified.', 'success').then(() => fetchAgenciesData());
            }
        }
    });
}

async function showAddAgencyModal() {
    // 1. Fetch Users to find Potential Owners
    Swal.fire({
        title: 'Loading owners...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    try {
        const res = await fetch('../includes/api/admin/get_users.php');
        const userData = await res.json();
        Swal.close();

        if (!userData.success) throw new Error(userData.message);

        const owners = userData.data.users.filter(u => u.role_name === 'agency_owner');

        if (owners.length === 0) {
            Swal.fire({
                title: 'No Agency Owners Found',
                text: 'Please create a user with the "Agency Owner" role first.',
                icon: 'warning',
                background: '#1a1d21',
                color: '#fff'
            });
            return;
        }

        const ownerOptions = owners.map(o => `<option value="${o.id}">${o.full_name} (${o.email})</option>`).join('');

        const { value: formValues } = await Swal.fire({
            title: 'Add New Agency',
            html: `
                <div class="premium-modal-form" style="text-align: left; padding: 10px 5px;">
                    <div class="form-group-swal" style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-size: 13px; color: #fff; opacity: 0.8;">Agency Name</label>
                        <input id="swal-agency-name" class="glass-input" style="width: 100%; height: 45px; border-radius: 10px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 0 15px;" placeholder="Enter agency name">
                    </div>
                    <div class="form-group-swal" style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-size: 13px; color: #fff; opacity: 0.8;">Assign Owner</label>
                        <select id="swal-owner-id" class="glass-input" style="width: 100%; height: 45px; border-radius: 10px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 0 15px; appearance: none;">
                            <option value="">Select an owner...</option>
                            ${ownerOptions}
                        </select>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-size: 13px; color: #fff; opacity: 0.8;">Phone</label>
                            <input id="swal-phone" class="glass-input" style="width: 100%; height: 45px; border-radius: 10px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 0 15px;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-size: 13px; color: #fff; opacity: 0.8;">Email</label>
                            <input id="swal-email" class="glass-input" style="width: 100%; height: 45px; border-radius: 10px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 0 15px;">
                        </div>
                    </div>
                    <div class="form-group-swal">
                        <label style="display: block; margin-bottom: 5px; font-size: 13px; color: #fff; opacity: 0.8;">Office Address</label>
                        <textarea id="swal-address" class="glass-input" style="width: 100%; height: 80px; border-radius: 10px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 10px 15px; resize: none;"></textarea>
                    </div>
                </div>
            `,
            background: '#1a1d21',
            color: '#fff',
            showCancelButton: true,
            confirmButtonText: 'Create Agency',
            confirmButtonColor: '#6c5dd3',
            preConfirm: () => {
                const name = document.getElementById('swal-agency-name').value;
                const ownerId = document.getElementById('swal-owner-id').value;
                if (!name || !ownerId) {
                    Swal.showValidationMessage('Name and Owner are required');
                    return false;
                }
                return {
                    name: name,
                    owner_id: ownerId,
                    phone: document.getElementById('swal-phone').value,
                    email: document.getElementById('swal-email').value,
                    address: document.getElementById('swal-address').value
                }
            }
        });

        if (formValues) {
            const formData = new FormData();
            Object.entries(formValues).forEach(([key, val]) => formData.append(key, val));

            const response = await fetch('../includes/api/admin/add_agency.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                Swal.fire({
                    title: 'Success!',
                    text: 'Agency has been listed.',
                    icon: 'success',
                    background: '#1a1d21',
                    color: '#fff'
                }).then(() => fetchAgenciesData());
            } else {
                Swal.fire('Error', result.message, 'error');
            }
        }

    } catch (error) {
        Swal.fire('Error', error.message, 'error');
    }
}

function deleteAgency(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "All associated agents and data will be affected!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff4757',
        cancelButtonColor: 'var(--primary)',
        confirmButtonText: 'Yes, Delete it!'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const formData = new FormData();
                formData.append('action', 'agency_delete');
                formData.append('id', id);
                const res = await fetch('../includes/api/admin/update_status.php', { method: 'POST', body: formData });
                const resultData = await res.json();
                
                if (resultData.success) {
                    Swal.fire('Deleted!', 'Agency has been removed.', 'success').then(() => fetchAgenciesData());
                } else {
                    Landsfy.showToast(resultData.message || 'Failed to delete agency', 'error');
                }
            } catch (err) {
                console.error('Delete Error:', err);
                Landsfy.showToast('Network error during deletion', 'error');
            }
        }
    });
}

function setupSearch() {
    const searchInput = document.querySelector('.search-bar input');
    if (!searchInput) return;

    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase();
        const filtered = agenciesData.filter(agency => 
            agency.name.toLowerCase().includes(query) ||
            agency.id.toString().includes(query) ||
            (agency.owner_name || '').toLowerCase().includes(query)
        );
        renderAgencies(filtered);
    });
}


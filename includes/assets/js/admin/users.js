let usersState = [];
let filteredUsers = [];
let agenciesState = [];

document.addEventListener('DOMContentLoaded', function() {
    fetchUsersData();
    setupSearch();
    
    const addUserBtn = document.getElementById('addUserBtn');
    if (addUserBtn) addUserBtn.onclick = showAddUserModal;
});

async function fetchUsersData() {
    const tableBody = document.getElementById('usersTableBody');
    tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 40px;"><i class="fa-solid fa-circle-notch fa-spin fa-spin" style="font-size: 32px;"></i><p>Loading users...</p></td></tr>';

    try {
        const response = await fetch('../includes/api/admin/get_users.php');
        const result = await response.json();
        
        if (result.success) {
            usersState = result.data.users;
            filteredUsers = [...usersState];
            updateStats(result.data.stats);
            renderUsers(filteredUsers);
            
            // Fetch Agencies as well for modals
            const agRes = await fetch('../includes/api/admin/get_agencies.php');
            const agResult = await agRes.json();
            if (agResult.success) {
                agenciesState = agResult.data.agencies;
            }
        } else {
            console.error('API Error:', result.message);
        }
    } catch (error) {
        console.error('Fetch Error:', error);
    }
}

function setupSearch() {
    const searchInput = document.querySelector('.search-bar input');
    if (!searchInput) return;

    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase();
        filteredUsers = usersState.filter(user => 
            (user.full_name || '').toLowerCase().includes(query) ||
            user.username.toLowerCase().includes(query) ||
            user.email.toLowerCase().includes(query) ||
            (user.role_name || '').toLowerCase().includes(query)
        );
        renderUsers(filteredUsers);
    });
}

function updateStats(stats) {
    const statValues = document.querySelectorAll('.stat-value');
    if (statValues.length >= 3) {
        statValues[0].textContent = (stats.agent || 0).toLocaleString();
        statValues[1].textContent = (stats.buyer || 0).toLocaleString();
        statValues[2].textContent = (stats.seller || 0).toLocaleString();
    }
}

function renderUsers(users) {
    const tableBody = document.getElementById('usersTableBody');
    if (!tableBody) return;

    if (users.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 40px;">No users found.</td></tr>';
        return;
    }

    tableBody.innerHTML = users.map(user => `
        <tr style="border-bottom: 1px solid var(--border-color); transition: all 0.2s ease;" class="user-row">
            <td style="padding: 16px 24px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <img src="${Landsfy.getImageUrl(user.avatar_url)}" 
                         style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;"
                         alt="user"
                         onerror="Landsfy.handleImageError(this, 'user')">
                    <div>
                        <div style="font-weight: 600; font-size: 14px;">${Landsfy.escapeHtml(user.full_name || 'No Name')}</div>
                        <div style="font-size: 12px; color: var(--text-secondary);">@${Landsfy.escapeHtml(user.username)} | ${Landsfy.escapeHtml(user.email)}</div>
                    </div>
                </div>
            </td>
            <td style="padding: 16px 24px;">
                <div class="badge-tag status-info" style="text-transform: capitalize; font-size: 11px;">
                    ${(user.role_name || 'Guest').replace('_', ' ')}
                </div>
                ${user.agency_name ? `<div style="font-size: 10px; color: var(--text-secondary); margin-top: 4px; font-weight: 600;"><i class="fa-solid fa-building"></i> ${Landsfy.escapeHtml(user.agency_name)}</div>` : ''}
            </td>
            <td style="padding: 16px 24px;">
                <span class="status-indicator ${user.status === 'active' ? 'active' : 'inactive'}"></span>
                <span style="font-size: 13px; font-weight: 500; text-transform: capitalize;">${user.status}</span>
            </td>
            <td style="padding: 16px 24px; font-size: 13px; color: var(--text-secondary);">
                ${Landsfy.formatDate(user.created_at)}
            </td>
            <td style="padding: 16px 24px; text-align: right;">
                <div style="display: flex; gap: 12px; justify-content: flex-end; align-items: center;">
                    <button class="card-action-btn" title="View Profile" onclick="window.location.href='user-detail.php?id=${user.id}'">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                    
                    ${(user.role_name && (user.role_name.toLowerCase().includes('agent') || user.role_name.toLowerCase().includes('agency'))) ? `
                        <button class="card-action-btn" style="color: var(--primary);" title="Premium History" onclick="showPremiumHistory(${user.id})">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </button>
                        <button class="card-action-btn" style="color: var(--success);" title="Manage Quota" onclick="showQuotaModal(${user.id})">
                            <i class="fa-solid fa-sketch-logo"></i>
                        </button>
                    ` : ''}
                    
                    <button class="card-action-btn" title="Edit Settings" onclick="showEditUserModal(${user.id})">
                        <i class="fa-solid fa-pencil"></i>
                    </button>
                    
                    ${user.status === 'active' ? `
                        <button class="card-action-btn action-delete" onclick="toggleUserStatus(${user.id}, 'suspend')" title="Suspend Account">
                            <i class="fa-solid fa-hand-palm"></i>
                        </button>` : `
                        <button class="card-action-btn" style="color: var(--success);" onclick="toggleUserStatus(${user.id}, 'activate')" title="Activate Account">
                            <i class="fa-solid fa-circle-check"></i>
                        </button>`}
                </div>
            </td>
        </tr>
    `).join('');
}

function showEditUserModal(id) {
    const user = usersState.find(u => u.id == id);
    if (!user) return;

    Swal.fire({
        title: 'Edit User Profile',
        html: `
            <div class="premium-modal-form" style="text-align: left; padding: 10px 5px;">
                <div class="form-group-swal" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 500; color: #fff; opacity: 0.9;">Full Name</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-user" style="position: absolute; left: 15px; top: 12px; color: var(--primary); font-size: 18px;"></i>
                        <input id="swal-name" class="glass-input" style="width: 100%; padding-left: 45px; height: 45px; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff;" value="${user.full_name || ''}" placeholder="Enter full name">
                    </div>
                </div>
                <div class="form-group-swal" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 500; color: #fff; opacity: 0.9;">Email Address</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-envelope-simple" style="position: absolute; left: 15px; top: 12px; color: var(--primary); font-size: 18px;"></i>
                        <input id="swal-email" class="glass-input" style="width: 100%; padding-left: 45px; height: 45px; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff;" value="${user.email}" placeholder="Enter email address">
                    </div>
                </div>
                <div class="form-group-swal" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 500; color: #fff; opacity: 0.9;">Account Role</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-circle-check" style="position: absolute; left: 15px; top: 12px; color: var(--primary); font-size: 18px;"></i>
                        <select id="swal-role" class="glass-input" onchange="toggleSwalAgency(this.value, 'swal-agency-group')" style="width: 100%; padding-left: 45px; height: 45px; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; appearance: none;">
                            <option value="agent" ${user.role_name === 'agent' ? 'selected' : ''}>Agent</option>
                            <option value="agency_owner" ${user.role_name === 'agency_owner' ? 'selected' : ''}>Agency Owner</option>
                            <option value="seller" ${user.role_name === 'seller' ? 'selected' : ''}>Seller</option>
                            <option value="buyer" ${user.role_name === 'buyer' ? 'selected' : ''}>Buyer</option>
                        </select>
                    </div>
                </div>
                <div class="form-group-swal" id="swal-agency-group" style="margin-bottom: 20px; display: ${user.role_name === 'agent' ? 'block' : 'none'};">
                    <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 500; color: #fff; opacity: 0.9;">Related Agency</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-building" style="position: absolute; left: 15px; top: 12px; color: var(--info); font-size: 18px;"></i>
                        <select id="swal-agency" class="glass-input" style="width: 100%; padding-left: 45px; height: 45px; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; appearance: none;">
                            <option value="">Select Agency...</option>
                            ${agenciesState.map(ag => `<option value="${ag.id}" ${user.agency_id == ag.id ? 'selected' : ''}>${Landsfy.escapeHtml(ag.name)}</option>`).join('')}
                        </select>
                    </div>
                </div>
                <div class="form-group-swal" style="margin-bottom: 10px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 500; color: #fff; opacity: 0.9;">New Password (Optional)</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-lock" style="position: absolute; left: 15px; top: 12px; color: var(--primary); font-size: 18px;"></i>
                        <input id="swal-password" type="password" class="glass-input" style="width: 100%; padding-left: 45px; height: 45px; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff;" placeholder="Leave blank to keep current">
                    </div>
                </div>
            </div>
        `,
        background: '#1a1d21',
        color: '#fff',
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-floppy-disk"></i> Update Profile',
        cancelButtonText: 'Dismiss',
        confirmButtonColor: '#6c5dd3',
        cancelButtonColor: 'rgba(255,255,255,0.1)',
        padding: '2em',
        customClass: {
            popup: 'glass-modal-popup',
            confirmButton: 'premium-swal-btn',
            cancelButton: 'premium-swal-btn-cancel'
        },
        preConfirm: () => {
            return {
                id: id,
                full_name: document.getElementById('swal-name').value,
                email: document.getElementById('swal-email').value,
                role: document.getElementById('swal-role').value,
                agency_id: document.getElementById('swal-agency').value,
                password: document.getElementById('swal-password').value
            }
        }
    }).then(async (result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('id', result.value.id);
            formData.append('full_name', result.value.full_name);
            formData.append('email', result.value.email);
            formData.append('role', result.value.role);
            formData.append('agency_id', result.value.agency_id);
            formData.append('password', result.value.password);

            const res = await fetch('../includes/api/admin/update_user.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                Swal.fire({
                    title: 'Success!',
                    text: 'Profile updated successfully.',
                    icon: 'success',
                    background: '#1a1d21',
                    color: '#fff',
                    confirmButtonColor: '#6c5dd3'
                }).then(() => fetchUsersData());
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.message,
                    icon: 'error',
                    background: '#1a1d21',
                    color: '#fff'
                });
            }
        }
    });
}

function toggleUserStatus(id, action) {
    const title = action === 'suspend' ? 'Suspend User?' : 'Reactivate User?';
    const text = action === 'suspend' ? "This user will not be able to log in or manage listings." : "The user will regain access to their dashboard.";
    const btnText = action === 'suspend' ? "Yes, Suspend" : "Yes, Activate";
    const btnColor = action === 'suspend' ? '#ff4757' : 'var(--success)';

    Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: btnColor,
        cancelButtonColor: '#8C98A4',
        confirmButtonText: btnText
    }).then(async (result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', action === 'suspend' ? 'user_suspend' : 'user_activate');
            formData.append('id', id);
            const res = await fetch('../includes/api/admin/update_status.php', { method: 'POST', body: formData });
            if ((await res.json()).success) {
                Swal.fire('Updated!', 'User status has been changed.', 'success').then(() => fetchUsersData());
            }
        }
    });
}
 
function toggleSwalAgency(role, groupId) {
    const group = document.getElementById(groupId);
    if (!group) return;
    group.style.display = (role === 'agent') ? 'block' : 'none';
}


function showAddUserModal() {
    Swal.fire({
        title: 'Add New User',
        html: `
            <div class="premium-modal-form" style="text-align: left; padding: 10px 5px;">
                <div class="form-group-swal" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 500; color: #fff; opacity: 0.9;">Full Name</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-user" style="position: absolute; left: 15px; top: 12px; color: var(--primary); font-size: 18px;"></i>
                        <input id="add-name" class="glass-input" style="width: 100%; padding-left: 45px; height: 45px; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff;" placeholder="e.g. John Doe">
                    </div>
                </div>
                <div class="form-group-swal" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 500; color: #fff; opacity: 0.9;">Username</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-at" style="position: absolute; left: 15px; top: 12px; color: var(--primary); font-size: 18px;"></i>
                        <input id="add-username" class="glass-input" style="width: 100%; padding-left: 45px; height: 45px; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff;" placeholder="e.g. johndoe">
                    </div>
                </div>
                <div class="form-group-swal" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 500; color: #fff; opacity: 0.9;">Email Address</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-envelope-simple" style="position: absolute; left: 15px; top: 12px; color: var(--primary); font-size: 18px;"></i>
                        <input id="add-email" class="glass-input" style="width: 100%; padding-left: 45px; height: 45px; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff;" placeholder="e.g. john@example.com">
                    </div>
                </div>
                <div class="form-group-swal" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 500; color: #fff; opacity: 0.9;">Password</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-lock" style="position: absolute; left: 15px; top: 12px; color: var(--primary); font-size: 18px;"></i>
                        <input id="add-password" type="password" class="glass-input" style="width: 100%; padding-left: 45px; height: 45px; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff;" placeholder="Create secure password">
                    </div>
                </div>
                <div class="form-group-swal" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 500; color: #fff; opacity: 0.9;">Account Role</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-circle-check" style="position: absolute; left: 15px; top: 12px; color: var(--primary); font-size: 18px;"></i>
                        <select id="add-role" class="glass-input" onchange="toggleSwalAgency(this.value, 'add-agency-group')" style="width: 100%; padding-left: 45px; height: 45px; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; appearance: none;">
                            <option value="seller">Seller</option>
                            <option value="agent">Agent</option>
                            <option value="buyer">Buyer</option>
                            <option value="agency_owner">Agency Owner</option>
                        </select>
                    </div>
                </div>
                <div class="form-group-swal" id="add-agency-group" style="margin-bottom: 10px; display: none;">
                    <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 500; color: #fff; opacity: 0.9;">Assign to Agency</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-building" style="position: absolute; left: 15px; top: 12px; color: var(--info); font-size: 18px;"></i>
                        <select id="add-agency" class="glass-input" style="width: 100%; padding-left: 45px; height: 45px; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; appearance: none;">
                            <option value="">Select Agency...</option>
                            ${agenciesState.map(ag => `<option value="${ag.id}">${Landsfy.escapeHtml(ag.name)}</option>`).join('')}
                        </select>
                    </div>
                </div>
            </div>
        `,
        background: '#1a1d21',
        color: '#fff',
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-user-plus"></i> Create Account',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#6c5dd3',
        cancelButtonColor: 'rgba(255,255,255,0.1)',
        padding: '2em',
        customClass: {
            popup: 'glass-modal-popup',
            confirmButton: 'premium-swal-btn',
            cancelButton: 'premium-swal-btn-cancel'
        },
        preConfirm: () => {
            const fullName = document.getElementById('add-name').value.trim();
            const username = document.getElementById('add-username').value.trim();
            const email = document.getElementById('add-email').value.trim();
            const password = document.getElementById('add-password').value;
            const role = document.getElementById('add-role').value;
            const agencyId = document.getElementById('add-agency').value;
 
            if (!fullName || !username || !email || !password) {
                Swal.showValidationMessage('Please fill in all fields');
                return false;
            }
            if (role === 'agent' && !agencyId) {
                Swal.showValidationMessage('Please select an agency for this agent');
                return false;
            }
            return { full_name: fullName, username: username, email: email, password: password, role: role, agency_id: agencyId };
        }
    }).then(async (result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            Object.entries(result.value).forEach(([key, val]) => formData.append(key, val));

            try {
                const res = await fetch('../includes/api/admin/add_user.php', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) {
                    Swal.fire({
                        title: 'Perfect!',
                        text: 'User account created successfully.',
                        icon: 'success',
                        background: '#1a1d21',
                        color: '#fff',
                        confirmButtonColor: '#6c5dd3'
                    }).then(() => fetchUsersData());
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message,
                        icon: 'error',
                        background: '#1a1d21',
                        color: '#fff'
                    });
                }
            } catch (e) {
                Swal.fire({
                    title: 'Error',
                    text: 'Connection failed',
                    icon: 'error',
                    background: '#1a1d21',
                    color: '#fff'
                });
            }
        }
    });
}

async function showQuotaModal(id) {
    const user = usersState.find(u => u.id == id);
    if (!user) return;

    Swal.fire({
        title: `Manage Quota: ${user.full_name}`,
        html: `
            <div class="premium-modal-form" style="text-align: left; padding: 10px 5px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px;">
                    <div class="quota-stat-card" style="background: rgba(16, 185, 129, 0.1); padding: 15px; border-radius: 12px; border: 1px solid rgba(16, 185, 129, 0.2); text-align: center;">
                        <div style="font-size: 11px; font-weight: 700; color: var(--success); text-transform: uppercase;">Platinum</div>
                        <div style="font-size: 24px; font-weight: 800; color: #fff;">${user.platinum_quota - user.platinum_used} / ${user.platinum_quota}</div>
                    </div>
                    <div class="quota-stat-card" style="background: rgba(59, 130, 246, 0.1); padding: 15px; border-radius: 12px; border: 1px solid rgba(59, 130, 246, 0.2); text-align: center;">
                        <div style="font-size: 11px; font-weight: 700; color: var(--info); text-transform: uppercase;">Diamond</div>
                        <div style="font-size: 24px; font-weight: 800; color: #fff;">${user.diamond_quota - user.diamond_used} / ${user.diamond_quota}</div>
                    </div>
                </div>

                <div class="form-group-swal" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 500; color: #fff; opacity: 0.9;">Platinum Credits to Add</label>
                    <input type="number" id="quota-plat" class="glass-input" style="width: 100%; height: 45px; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 0 15px;" value="0">
                </div>
                <div class="form-group-swal" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 500; color: #fff; opacity: 0.9;">Diamond Credits to Add</label>
                    <input type="number" id="quota-diam" class="glass-input" style="width: 100%; height: 45px; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 0 15px;" value="0">
                </div>
                <div style="font-size: 12px; color: var(--text-secondary); font-style: italic;">Note: This will ADD to the user's existing quota.</div>
            </div>
        `,
        background: '#1a1d21',
        color: '#fff',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-circle-plus"></i> Add Quota',
        confirmButtonColor: 'var(--success)',
        preConfirm: () => {
            return {
                user_id: id,
                platinum_quota: document.getElementById('quota-plat').value,
                diamond_quota: document.getElementById('quota-diam').value,
                mode: 'add'
            }
        }
    }).then(async (result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            Object.entries(result.value).forEach(([k, v]) => formData.append(k, v));
            const res = await fetch('../includes/api/admin/update_agent_quota.php', { method: 'POST', body: formData });
            if ((await res.json()).success) {
                Swal.fire('Updated!', 'Quota has been successfully added.', 'success').then(() => fetchUsersData());
            }
        }
    });
}

async function showPremiumHistory(id) {
    const user = usersState.find(u => u.id == id);
    if (!user) return;

    Swal.fire({
        title: `Premium History: ${user.full_name}`,
        width: '600px',
        html: `<div id="swal-history-list" style="text-align: left; max-height: 400px; overflow-y: auto; padding: 10px;">
                <i class="fa-solid fa-circle-notch fa-spin fa-spin"></i> Loading history...
               </div>`,
        background: '#1a1d21',
        color: '#fff',
        showConfirmButton: false,
        showCloseButton: true,
        didOpen: async () => {
            try {
                const res = await fetch(`../includes/api/admin/get_premium_history.php?user_id=${id}`);
                const result = await res.json();
                const container = document.getElementById('swal-history-list');
                
                if (result.success && result.data.history.length > 0) {
                    container.innerHTML = result.data.history.map(log => `
                        <div style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; gap: 15px; align-items: flex-start;">
                            <div style="width: 32px; height: 32px; border-radius: 8px; background: ${log.log_type === 'quota_update' ? 'rgba(16,185,129,0.1)' : 'rgba(59,130,246,0.1)'}; color: ${log.log_type === 'quota_update' ? 'var(--success)' : 'var(--info)'}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="${log.log_type === 'quota_update' ? 'fa-solid fa-circle-plus' : 'fa-solid fa-circle-check'}"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-size: 13px; font-weight: 600; color: #fff;">${Landsfy.escapeHtml(log.description)}</div>
                                <div style="font-size: 11px; opacity: 0.6; margin-top: 4px;">${Landsfy.formatDate(log.created_at)}</div>
                            </div>
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = '<div style="text-align: center; padding: 20px; opacity: 0.5;">No premium history found.</div>';
                }
            } catch (e) {
                document.getElementById('swal-history-list').innerHTML = 'Failed to load history.';
            }
        }
    });
}


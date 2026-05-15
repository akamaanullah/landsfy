let teamState = [];

document.addEventListener('DOMContentLoaded', function() {
    fetchTeam();

    // Add Team Member Logic
    const addMemberBtn = document.getElementById('addTeamMemberBtn');
    if (addMemberBtn) {
        addMemberBtn.addEventListener('click', showAddMemberModal);
    }

    // Search Logic
    const searchInput = document.querySelector('.search-bar input');
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                fetchTeam(searchInput.value);
            }, 500);
        });
    }
});

async function fetchTeam(search = '') {
    const grid = document.getElementById('agentsGrid');
    if (!grid) return;

    grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 40px;"><i class="fa-solid fa-circle-notch fa-spin spinner" style="font-size: 32px; color: var(--primary);"></i><p style="margin-top: 12px; opacity: 0.7;">Loading team...</p></div>';

    try {
        const params = new URLSearchParams();
        if (search) params.append('search', search);

        const response = await fetch(`../includes/api/agency/get_agency_team.php?${params.toString()}`);
        const data = await response.json();

        if (data.success) {
            teamState = data.team;
            if (teamState.length === 0) {
                grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 60px;"><i class="fa-solid fa-users-three" style="font-size: 48px; opacity: 0.3;"></i><p style="margin-top: 16px; font-size: 18px; font-weight: 500;">No team members found.</p></div>';
                return;
            }

            grid.innerHTML = teamState.map(member => `
                <div class="agent-card glass">
                    <div class="agent-avatar-wrapper">
                        <img src="${member.avatar_url ? '../' + member.avatar_url : 'https://i.pravatar.cc/150?u=' + member.user_id}" class="agent-avatar-full" alt="${member.full_name}">
                        <div class="status-indicator ${member.status}"></div>
                    </div>
                    
                    <div class="agent-details">
                        <h4 class="agent-name">${member.full_name}</h4>
                        <span class="agent-role">${member.specialization || 'Real Estate Agent'}</span>
                    </div>

                    <div class="agent-stats-mini">
                        <div class="agent-stat-item">
                            <span class="stat-num">${member.listing_count}</span>
                            <span class="stat-label">Listings</span>
                        </div>
                        <div class="agent-stat-item">
                            <span class="stat-num">${member.experience_years}</span>
                            <span class="stat-label">Years Exp.</span>
                        </div>
                    </div>

                    <div class="agent-contact-actions">
                        <button class="btn-ghost" title="Edit Agent" onclick="editAgent(${member.user_id})">
                            <i class="fa-solid fa-pencil"></i>
                        </button>
                        <a href="mailto:${member.email}" class="btn-ghost" title="Email Agent">
                            <i class="fa-solid fa-envelope"></i>
                        </a>
                        <a href="tel:${member.phone}" class="btn-ghost" title="Call Agent">
                            <i class="fa-solid fa-phone"></i>
                        </a>
                        <button class="btn-ghost" style="color: #ef4444;" title="Remove Agent" onclick="removeAgent(${member.agent_id})">
                            <i class="fa-solid fa-user-minus"></i>
                        </button>
                    </div>
                </div>
            `).join('');

        } else {
            console.error('API Error:', data.message);
        }
    } catch (error) {
        console.error('Error fetching team:', error);
    }
}

function showAddMemberModal() {
    Swal.fire({
        title: 'Add New Team Member',
        html: `
            <div class="premium-modal-form" style="text-align: left;">
                <div class="form-group-swal" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 500; color: #fff; opacity: 0.9;">Full Name</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-user" style="position: absolute; left: 15px; top: 12px; color: var(--primary); font-size: 18px;"></i>
                        <input id="add-name" class="glass-input" style="width: 100%; padding-left: 45px; height: 45px; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff;" placeholder="e.g. Ahmad Hassan">
                    </div>
                </div>
                <div class="form-group-swal" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 500; color: #fff; opacity: 0.9;">Username</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-at" style="position: absolute; left: 15px; top: 12px; color: var(--primary); font-size: 18px;"></i>
                        <input id="add-username" class="glass-input" style="width: 100%; padding-left: 45px; height: 45px; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff;" placeholder="e.g. ahmad_hassan">
                    </div>
                </div>
                <div class="form-group-swal" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 500; color: #fff; opacity: 0.9;">Email Address</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-envelope-simple" style="position: absolute; left: 15px; top: 12px; color: var(--primary); font-size: 18px;"></i>
                        <input id="add-email" class="glass-input" style="width: 100%; padding-left: 45px; height: 45px; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff;" placeholder="e.g. ahmad@landsfy.com">
                    </div>
                </div>
                <div class="form-group-swal" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 500; color: #fff; opacity: 0.9;">Contact Number</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-phone" style="position: absolute; left: 15px; top: 12px; color: var(--primary); font-size: 18px;"></i>
                        <input id="add-phone" class="glass-input" style="width: 100%; padding-left: 45px; height: 45px; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff;" placeholder="e.g. 0300-1234567">
                    </div>
                </div>
                <div class="form-group-swal" style="margin-bottom: 10px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 500; color: #fff; opacity: 0.9;">Password</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-lock" style="position: absolute; left: 15px; top: 12px; color: var(--primary); font-size: 18px;"></i>
                        <input id="add-password" type="password" class="glass-input" style="width: 100%; padding-left: 45px; height: 45px; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff;" placeholder="Set temporary password">
                    </div>
                </div>
                <p style="font-size: 11px; opacity: 0.6; color: #fff; margin-top: 10px;"><i class="fa-solid fa-circle-info"></i> The new agent will be automatically linked to your agency.</p>
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
        customClass: {
            popup: 'glass-modal-popup',
            confirmButton: 'premium-swal-btn',
            cancelButton: 'premium-swal-btn-cancel'
        },
        preConfirm: () => {
            const fullName = document.getElementById('add-name').value.trim();
            const username = document.getElementById('add-username').value.trim();
            const email = document.getElementById('add-email').value.trim();
            const phone = document.getElementById('add-phone').value.trim();
            const password = document.getElementById('add-password').value;

            if (!fullName || !username || !email || !password) {
                Swal.showValidationMessage('Please fill in all required fields');
                return false;
            }
            return { full_name: fullName, username: username, email: email, phone: phone, password: password };
        }
    }).then(async (result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Processing...',
                background: '#1a1d21',
                color: '#fff',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const formData = new FormData();
            Object.entries(result.value).forEach(([key, val]) => formData.append(key, val));

            try {
                const res = await fetch('../includes/api/agency/add_team_member.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'New agent has been added to your team.',
                        icon: 'success',
                        background: '#1a1d21',
                        color: '#fff',
                        confirmButtonColor: '#6c5dd3',
                        customClass: {
                            popup: 'glass-modal-popup',
                            confirmButton: 'premium-swal-btn'
                        }
                    }).then(() => location.reload());
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message,
                        icon: 'error',
                        background: '#1a1d21',
                        color: '#fff',
                        customClass: {
                            popup: 'glass-modal-popup',
                            confirmButton: 'premium-swal-btn'
                        }
                    });
                }
            } catch (e) {
                Swal.fire('Error', 'Connection failed', 'error');
            }
        }
    });
}

function editAgent(userId) {
    const member = teamState.find(m => m.user_id == userId);
    if (!member) return;

    Swal.fire({
        title: 'Edit Team Member',
        html: `
            <div class="premium-modal-form" style="text-align: left;">
                <div class="form-group-swal" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 500; color: #fff; opacity: 0.9;">Full Name</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-user" style="position: absolute; left: 15px; top: 12px; color: var(--primary); font-size: 18px;"></i>
                        <input id="edit-name" class="glass-input" style="width: 100%; padding-left: 45px; height: 45px; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff;" value="${member.full_name}" placeholder="Full Name">
                    </div>
                </div>
                <div class="form-group-swal" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 500; color: #fff; opacity: 0.9;">Username</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-at" style="position: absolute; left: 15px; top: 12px; color: var(--primary); font-size: 18px;"></i>
                        <input id="edit-username" class="glass-input" style="width: 100%; padding-left: 45px; height: 45px; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff;" value="${member.username}" placeholder="Username">
                    </div>
                </div>
                <div class="form-group-swal" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 500; color: #fff; opacity: 0.9;">Email Address</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-envelope-simple" style="position: absolute; left: 15px; top: 12px; color: var(--primary); font-size: 18px;"></i>
                        <input id="edit-email" class="glass-input" style="width: 100%; padding-left: 45px; height: 45px; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff;" value="${member.email}" placeholder="Email">
                    </div>
                </div>
                <div class="form-group-swal" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 500; color: #fff; opacity: 0.9;">Contact Number</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-phone" style="position: absolute; left: 15px; top: 12px; color: var(--primary); font-size: 18px;"></i>
                        <input id="edit-phone" class="glass-input" style="width: 100%; padding-left: 45px; height: 45px; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff;" value="${member.phone || ''}" placeholder="Contact Number">
                    </div>
                </div>
                <div class="form-group-swal" style="margin-bottom: 10px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 500; color: #fff; opacity: 0.9;">New Password (Optional)</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-lock" style="position: absolute; left: 15px; top: 12px; color: var(--primary); font-size: 18px;"></i>
                        <input id="edit-password" type="password" class="glass-input" style="width: 100%; padding-left: 45px; height: 45px; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff;" placeholder="Leave blank to keep current">
                    </div>
                </div>
            </div>
        `,
        background: '#1a1d21',
        color: '#fff',
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-floppy-disk"></i> Save Changes',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#6c5dd3',
        cancelButtonColor: 'rgba(255,255,255,0.1)',
        customClass: {
            popup: 'glass-modal-popup',
            confirmButton: 'premium-swal-btn',
            cancelButton: 'premium-swal-btn-cancel'
        },
        preConfirm: () => {
            return {
                user_id: userId,
                full_name: document.getElementById('edit-name').value.trim(),
                username: document.getElementById('edit-username').value.trim(),
                email: document.getElementById('edit-email').value.trim(),
                phone: document.getElementById('edit-phone').value.trim(),
                password: document.getElementById('edit-password').value
            }
        }
    }).then(async (result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            Object.entries(result.value).forEach(([key, val]) => formData.append(key, val));

            try {
                const res = await fetch('../includes/api/agency/update_team_member.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    Swal.fire({
                        title: 'Updated!',
                        text: 'Agent information has been saved.',
                        icon: 'success',
                        background: '#1a1d21',
                        color: '#fff',
                        confirmButtonColor: '#6c5dd3',
                        customClass: {
                            popup: 'glass-modal-popup',
                            confirmButton: 'premium-swal-btn'
                        }
                    }).then(() => fetchTeam());
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            } catch (e) {
                Swal.fire('Error', 'Update failed', 'error');
            }
        }
    });
}

async function removeAgent(agentId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This agent will be detached from your agency. Their user account will remain active but they will no longer appear in your team.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: 'rgba(255,255,255,0.1)',
        confirmButtonText: 'Yes, remove them',
        cancelButtonText: 'Cancel',
        background: '#1a1d21',
        color: '#fff',
        customClass: {
            popup: 'glass-modal-popup',
            confirmButton: 'premium-swal-btn',
            cancelButton: 'premium-swal-btn-cancel'
        }
    }).then(async (result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('agent_id', agentId);

            try {
                const res = await fetch('../includes/api/agency/remove_team_member.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    Swal.fire({
                        title: 'Removed!',
                        text: 'Agent has been removed from your team.',
                        icon: 'success',
                        background: '#1a1d21',
                        color: '#fff',
                        confirmButtonColor: '#6c5dd3',
                        customClass: {
                            popup: 'glass-modal-popup',
                            confirmButton: 'premium-swal-btn'
                        }
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            } catch (e) {
                Swal.fire('Error', 'Removal failed', 'error');
            }
        }
    });
}

function showToast(title, message, type) {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    toast.className = `toast toast-${type} glass`;
    toast.innerHTML = `
        <div class="toast-content">
            <div class="toast-title">${title}</div>
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close"><i class="fa-solid fa-xmark"></i></button>
    `;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s forwards';
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

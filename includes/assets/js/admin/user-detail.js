/**
 * User Detail Logic
 */

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const userId = urlParams.get('id');

    if (!userId) {
        window.location.href = 'user-management.php';
        return;
    }

    fetchUserDetails(userId);
    setupTabSwitching();
    setupQuotaForm(userId);
});

async function fetchUserDetails(id) {
    try {
        const response = await fetch(`../includes/api/admin/get_user_details.php?id=${id}`);
        const result = await response.json();

        if (result.success) {
            renderUserInfo(result.data.user, result.data.stats);
            renderActivityLogs(result.data.activity);
            renderProperties(result.data.properties);
            
            if (result.data.user.role_name === 'agent') {
                const reviewTab = document.getElementById('reviewsTabTitle');
                if (reviewTab) {
                    reviewTab.style.display = 'block';
                    reviewTab.textContent = `Reviews (${result.data.reviews.length})`;
                }
                renderReviews(result.data.reviews);

                const walletTab = document.getElementById('walletTabTitle');
                if (walletTab) {
                    walletTab.style.display = 'block';
                    renderQuotaInfo(result.data.quota);
                }
            }
            
            setupActionButtons(result.data.user);
        } else {
            Landsfy.showToast(result.message || 'Failed to load user details', 'error');
        }
    } catch (error) {
        console.error('Fetch Error:', error);
        Landsfy.showToast('Network error while loading user', 'error');
    }
}

function renderUserInfo(user, stats) {
    document.getElementById('headerUserName').textContent = user.full_name;
    document.getElementById('profileName').textContent = user.full_name;
    document.getElementById('profileRoleBadge').textContent = (user.role_name || 'Guest').replace('_', ' ');
    document.getElementById('profileEmail').textContent = user.email;
    document.getElementById('profilePhone').textContent = user.phone || 'N/A';
    document.getElementById('profileJoined').textContent = Landsfy.formatDate(user.created_at);
    document.getElementById('propertiesTabTitle').textContent = `Properties (${stats.property_count})`;
    
    // Status
    const statusEl = document.getElementById('profileStatus');
    statusEl.textContent = user.status.charAt(0).toUpperCase() + user.status.slice(1);
    statusEl.style.color = user.status === 'active' ? 'var(--success)' : 'var(--danger)';

    if (user.avatar_url) {
        document.getElementById('profileImage').src = Landsfy.getImageUrl(user.avatar_url);
        document.getElementById('profileImage').onerror = function() {
            this.onerror = null;
            this.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(user.full_name)}&background=6c5dd3&color=fff&bold=true`;
        };
    }
}

function renderQuotaInfo(quota) {
    document.getElementById('platinumTotalSpan').textContent = quota.platinum.total;
    document.getElementById('platinumUsedSpan').textContent = quota.platinum.used;
    document.getElementById('diamondTotalSpan').textContent = quota.diamond.total;
    document.getElementById('diamondUsedSpan').textContent = quota.diamond.used;
}

function setupQuotaForm(userId) {
    const btn = document.getElementById('openQuotaModalBtn');
    if (!btn) return;

    btn.onclick = () => {
        const platTotal = document.getElementById('platinumTotalSpan').textContent;
        const diamTotal = document.getElementById('diamondTotalSpan').textContent;
        const platUsed = document.getElementById('platinumUsedSpan').textContent;
        const diamUsed = document.getElementById('diamondUsedSpan').textContent;

        Swal.fire({
            title: 'Add Quota Balance',
            html: `
                <div class="premium-modal-form" style="text-align: left; padding: 10px 5px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px;">
                        <div style="background: rgba(16, 185, 129, 0.1); padding: 15px; border-radius: 12px; border: 1px solid rgba(16, 185, 129, 0.2); text-align: center;">
                            <div style="font-size: 11px; font-weight: 700; color: var(--success); text-transform: uppercase;">Platinum</div>
                            <div style="font-size: 20px; font-weight: 800; color: var(--text-primary);">${platTotal - platUsed} Available</div>
                        </div>
                        <div style="background: rgba(59, 130, 246, 0.1); padding: 15px; border-radius: 12px; border: 1px solid rgba(59, 130, 246, 0.2); text-align: center;">
                            <div style="font-size: 11px; font-weight: 700; color: var(--info); text-transform: uppercase;">Diamond</div>
                            <div style="font-size: 20px; font-weight: 800; color: var(--text-primary);">${diamTotal - diamUsed} Available</div>
                        </div>
                    </div>

                    <div class="form-group-swal" style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 700; color: var(--text-primary);">Platinum Credits to Add</label>
                        <input type="number" id="swal-plat" class="glass-input" style="width: 100%; height: 45px; border-radius: 12px; background: rgba(0,0,0,0.05); border: 1px solid var(--border-color); color: var(--text-primary); padding: 0 15px;" value="0">
                    </div>
                    <div class="form-group-swal" style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 700; color: var(--text-primary);">Diamond Credits to Add</label>
                        <input type="number" id="swal-diam" class="glass-input" style="width: 100%; height: 45px; border-radius: 12px; background: rgba(0,0,0,0.05); border: 1px solid var(--border-color); color: var(--text-primary); padding: 0 15px;" value="0">
                    </div>
                    <div style="font-size: 12px; color: var(--text-secondary); font-style: italic;">Credits will be added to existing balance.</div>
                </div>
            `,
            background: 'var(--glass-bg)',
            color: 'var(--text-primary)',
            backdrop: `rgba(0,0,0,0.4) blur(4px)`,
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-circle-plus"></i> Update Balance',
            confirmButtonColor: 'var(--success)',
            preConfirm: () => {
                return {
                    user_id: userId,
                    platinum_quota: document.getElementById('swal-plat').value,
                    diamond_quota: document.getElementById('swal-diam').value,
                    mode: 'add'
                }
            }
        }).then(async (result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                Object.entries(result.value).forEach(([k, v]) => formData.append(k, v));
                
                const res = await fetch('../includes/api/admin/update_agent_quota.php', { method: 'POST', body: formData });
                if ((await res.json()).success) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Quota has been added successfully.',
                        icon: 'success',
                        background: '#1a1d21',
                        color: '#fff'
                    }).then(() => fetchUserDetails(userId));
                }
            }
        });
    };
}

function renderActivityLogs(logs) {
    const container = document.getElementById('activityLogContainer');
    if (logs.length === 0) {
        container.innerHTML = '<div style="padding: 40px; text-align: center; color: var(--text-secondary);">No recent activity found.</div>';
        return;
    }

    container.innerHTML = logs.map(log => {
        let icon = 'fa-clock';
        let color = 'var(--primary)';
        let bg = 'rgba(108, 93, 211, 0.1)';

        if (log.action_type.includes('add')) {
            icon = 'fa-circle-plus';
            color = 'var(--success)';
            bg = 'rgba(16, 185, 129, 0.1)';
        } else if (log.action_type.includes('update') || log.action_type.includes('edit')) {
            icon = 'fa-pencil';
            color = 'var(--primary)';
            bg = 'rgba(108, 93, 211, 0.1)';
        } else if (log.action_type.includes('reject') || log.action_type.includes('delete')) {
            icon = 'fa-trash';
            color = 'var(--danger)';
            bg = 'rgba(239, 68, 68, 0.1)';
        }

        return `
            <div class="activity-item">
                <div class="activity-icon" style="background: ${bg}; color: ${color};">
                    <i class="fa-solid ${icon}"></i>
                </div>
                <div style="flex: 1;">
                    <div style="font-weight: 600; font-size: 14px;">${log.description}</div>
                    <div style="font-size: 12px; color: var(--text-secondary);">${Landsfy.formatDateTime(log.created_at)}</div>
                </div>
            </div>
        `;
    }).join('');
}

function renderProperties(properties) {
    const grid = document.getElementById('userPropertyGrid');
    if (properties.length === 0) {
        grid.innerHTML = '<div style="grid-column: 1/-1; padding: 40px; text-align: center; color: var(--text-secondary);">This user has no properties.</div>';
        return;
    }

    grid.innerHTML = properties.map(prop => `
        <div class="property-card glass" onclick="window.location.href='property-detail.php?id=${prop.id}'" style="cursor: pointer;">
            <div class="card-image-wrapper">
                <img src="${Landsfy.getImageUrl(prop.image_url)}" 
                     alt="${prop.title}"
                     onerror="Landsfy.handleImageError(this, 'property')">
                <span class="card-badge">${prop.status}</span>
            </div>
            <div class="card-content">
                <div class="card-price">PKR ${Landsfy.formatPrice(prop.price)}</div>
                <div class="card-title">${prop.title}</div>
                <div class="card-location"><i class="fa-solid fa-location-dot"></i> ${prop.location_name}</div>
            </div>
        </div>
    `).join('');
}

function renderReviews(reviews) {
    const container = document.getElementById('userReviewsContainer');
    if (!container) return;

    if (!reviews || reviews.length === 0) {
        container.innerHTML = '<div style="padding: 40px; text-align: center; color: var(--text-secondary);">No reviews found for this agent yet.</div>';
        return;
    }

    container.innerHTML = `
        <div style="display: flex; flex-direction: column; gap: 20px;">
            ${reviews.map(rev => `
                <div class="review-card glass" style="padding: 20px; border-radius: 16px; border: 1px solid var(--glass-border);">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                        <div style="display: flex; gap: 12px; align-items: center;">
                            <img src="../${rev.reviewer_avatar || 'includes/assets/images/user-placeholder.jpg'}" style="width: 40px; height: 40px; border-radius: 10px; object-fit: cover;">
                            <div>
                                <div style="font-weight: 700; font-size: 14px;">${rev.reviewer_name}</div>
                                <div style="font-size: 11px; opacity: 0.5;">${new Date(rev.created_at).toLocaleDateString()}</div>
                            </div>
                        </div>
                        <div style="color: #ffb800; font-size: 14px;">
                            ${Array(5).fill(0).map((_, i) => `<i class="${i < rev.rating ? 'fa-fill' : 'ph'} ph-star"></i>`).join('')}
                        </div>
                    </div>
                    <p style="font-size: 14px; line-height: 1.6; color: var(--text-secondary); margin-bottom: 0;">
                        ${rev.review_text || 'No comment provided.'}
                    </p>
                </div>
            `).join('')}
        </div>
    `;
}

function setupActionButtons(user) {
    const editBtn = document.getElementById('editProfileBtn');
    const suspendBtn = document.getElementById('suspendUserBtn');

    if (editBtn) {
        editBtn.onclick = () => showEditUserModal(user);
    }

    if (suspendBtn) {
        if (user.status === 'suspended') {
            suspendBtn.innerHTML = '<i class="fa-solid fa-circle-check"></i> Reactivate User';
            suspendBtn.style.background = 'var(--success)';
            suspendBtn.onclick = () => toggleUserStatus(user.id, 'activate');
        } else {
            suspendBtn.onclick = () => toggleUserStatus(user.id, 'suspend');
        }
    }
}

// Re-using modal logic from users.js but adapted if needed
async function showEditUserModal(user) {
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
                        <select id="swal-role" class="glass-input" style="width: 100%; padding-left: 45px; height: 45px; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; appearance: none;">
                            <option value="agent" ${user.role_name === 'agent' ? 'selected' : ''}>Agent</option>
                            <option value="agency_owner" ${user.role_name === 'agency_owner' ? 'selected' : ''}>Agency Owner</option>
                            <option value="seller" ${user.role_name === 'seller' ? 'selected' : ''}>Seller</option>
                            <option value="buyer" ${user.role_name === 'buyer' ? 'selected' : ''}>Buyer</option>
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
            popup: 'glass-modal-popup'
        },
        preConfirm: () => {
            return {
                id: user.id,
                full_name: document.getElementById('swal-name').value,
                email: document.getElementById('swal-email').value,
                role: document.getElementById('swal-role').value,
                password: document.getElementById('swal-password').value
            }
        }
    }).then(async (result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            Object.entries(result.value).forEach(([key, val]) => formData.append(key, val));

            const res = await fetch('../includes/api/admin/update_user.php', { method: 'POST', body: formData });
            if ((await res.json()).success) {
                Swal.fire({
                    title: 'Updated!',
                    icon: 'success',
                    background: '#1a1d21',
                    color: '#fff'
                }).then(() => fetchUserDetails(user.id));
            }
        }
    });
}

function toggleUserStatus(id, action) {
    const title = action === 'suspend' ? 'Suspend User?' : 'Reactivate User?';
    Swal.fire({
        title: title,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: action === 'suspend' ? '#ff4757' : 'var(--success)',
        background: '#1a1d21',
        color: '#fff'
    }).then(async (result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', action === 'suspend' ? 'user_suspend' : 'user_activate');
            formData.append('id', id);
            const res = await fetch('../includes/api/admin/update_status.php', { method: 'POST', body: formData });
            if ((await res.json()).success) {
                fetchUserDetails(id);
            }
        }
    });
}

function setupTabSwitching() {
    document.querySelectorAll('.user-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.user-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            const target = tab.getAttribute('data-target');
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.getElementById(target).classList.add('active');
        });
    });
}


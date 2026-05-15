/**
 * Agency Detail Logic
 */

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const agencyId = urlParams.get('id');

    if (!agencyId) {
        window.location.href = 'agencies.php';
        return;
    }

    fetchAgencyDetails(agencyId);
    setupTabSwitching();
});

async function fetchAgencyDetails(id) {
    try {
        const response = await fetch(`../includes/api/admin/get_agency_details.php?id=${id}`);
        const result = await response.json();

        if (result.success) {
            const data = result.data;
            renderAgencyInfo(data.agency, data.stats);
            renderTeam(data.team);
            renderProperties(data.properties, data.stats.property_count);
            renderDocuments(data.documents);
            renderReviews(data.reviews);
            setupActionButtons(data.agency);
        } else {
            Swal.fire('Error', result.message, 'error');
        }
    } catch (error) {
        console.error('Fetch Error:', error);
    }
}

function renderAgencyInfo(agency, stats) {
    document.getElementById('headerAgencyName').textContent = agency.name;
    document.getElementById('agencyName').textContent = agency.name;
    document.getElementById('agencyLocation').textContent = agency.address || 'Address not listed';
    document.getElementById('agencyJoined').textContent = Landsfy.formatDate(agency.created_at);
    document.getElementById('agencyAgentCount').textContent = stats.agent_count;
    
    document.getElementById('statListings').textContent = stats.property_count;
    document.getElementById('statSold').textContent = stats.sold_count;
    document.getElementById('statLeads').textContent = stats.leads_count || '0';
    document.getElementById('statRating').textContent = `${stats.rating}/5.0`;

    // Logo with fallback
    const logoEl = document.getElementById('agencyLogo');
    if (agency.logo_url) {
        logoEl.src = '../' + agency.logo_url;
    } else {
        logoEl.src = '../includes/assets/images/agency-placeholder.png';
        logoEl.onerror = () => { logoEl.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(agency.name) + '&background=6c5dd3&color=fff&bold=true'; };
    }

    // Banner with fallback
    const bannerEl = document.getElementById('agencyBanner');
    if (agency.banner_url) {
        bannerEl.innerHTML = `<img src="../${agency.banner_url}" alt="banner" style="width: 100%; height: 100%; object-fit: cover;">`;
    } else {
        bannerEl.style.background = 'linear-gradient(135deg, #6c5dd3 0%, #a445b2 100%)';
        bannerEl.innerHTML = `
            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; opacity: 0.1;">
                <i class="fa-solid fa-building" style="font-size: 120px; color: #fff;"></i>
            </div>
        `;
    }

    if (agency.is_verified == 1) {
        document.getElementById('verifiedBadge').style.display = 'inline-flex';
        const verifyBtn = document.getElementById('verifyAgencyBtn');
        if (verifyBtn) verifyBtn.style.display = 'none';
    } else {
        const verifyBtn = document.getElementById('verifyAgencyBtn');
        if (verifyBtn) verifyBtn.style.display = 'inline-block';
    }
}

function renderTeam(team) {
    const grid = document.getElementById('teamGrid');
    if (team.length === 0) {
        grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--text-secondary);">No agents found for this agency.</div>';
        return;
    }

    grid.innerHTML = team.map(agent => `
        <div class="agent-card glass">
            <img src="${Landsfy.getImageUrl(agent.avatar_url) || 'https://i.pravatar.cc/150?u=' + agent.user_id}" 
                 alt="Agent" class="agent-avatar-large"
                 onerror="Landsfy.handleImageError(this, 'agent')">
            <div class="contact-name" style="font-size: 18px;">${agent.full_name}</div>
            <div class="contact-type">${agent.specialization || 'Agent'}</div>
            <div style="margin-top: 15px; display: flex; flex-direction: column; gap: 8px; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 15px;">
                <div style="font-size: 13px; color: var(--text-secondary); display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-envelope"></i> ${agent.email}
                </div>
                <div style="font-size: 13px; color: var(--text-secondary); display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-phone"></i> ${agent.phone || 'No Phone'}
                </div>
            </div>
            <div class="agent-perf-bar"><div class="agent-perf-fill" style="width: 80%;"></div></div>
            <div style="display: flex; justify-content: space-between; font-size: 12px; color: var(--text-secondary); width: 100%;">
                <span>Perf: 80%</span>
                <button class="btn-preview" onclick="window.location.href='user-detail.php?id=${agent.user_id}'" style="padding: 2px 8px; font-size: 11px;">Profile</button>
            </div>
        </div>
    `).join('');
}

function renderProperties(properties, totalCount) {
    const grid = document.getElementById('agencyPropertyGrid');
    document.getElementById('propsTabTitle').textContent = `Properties (${totalCount})`;

    if (properties.length === 0) {
        grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--text-secondary);">No properties listed by this agency.</div>';
        return;
    }

    grid.innerHTML = properties.map(prop => `
        <div class="property-card glass">
            <div class="card-image-wrapper">
                <div class="card-badge ${prop.status === 'active' ? 'badge-active' : ''}">${prop.status}</div>
                <img src="${Landsfy.getImageUrl(prop.image_url)}" 
                     alt="property" class="card-image"
                     onerror="Landsfy.handleImageError(this, 'property')">
            </div>
            <div class="card-content">
                <div class="card-price">PKR ${Landsfy.formatPrice(prop.price)}</div>
                <h4 class="card-title">${prop.title}</h4>
                <div class="card-location"><i class="fa-solid fa-location-dot"></i> ${prop.location_name}</div>
                <div class="card-features">
                    <div class="feature-item"><i class="fa-solid fa-selection-all"></i> ${prop.area_size} ${prop.area_unit}</div>
                    <div class="feature-item"><i class="fa-solid fa-building"></i> ${prop.property_type || 'Type N/A'}</div>
                </div>
            </div>
            <div class="card-actions">
                <div class="card-meta"><i class="fa-solid fa-clock"></i> ${Landsfy.formatDate(prop.created_at)}</div>
                <div class="action-btns">
                    <button class="card-action-btn" title="View" onclick="window.location.href='property-detail.php?id=${prop.id}'"><i class="fa-solid fa-eye"></i></button>
                </div>
            </div>
        </div>
    `).join('');
}

function renderDocuments(docs) {
    const container = document.getElementById('docsContainer');
    if (docs.length === 0) {
        container.innerHTML = '<div style="text-align: center; color: var(--text-secondary); padding: 40px;">No verification documents uploaded.</div>';
        return;
    }

    container.innerHTML = `
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px;">
            ${docs.map(doc => `
                <div class="doc-card glass" style="padding: 15px; border-radius: 12px; border: 1px solid var(--border-color); text-align: center;">
                    <i class="fa-solid fa-file-pdf" style="font-size: 40px; color: var(--primary); margin-bottom: 10px;"></i>
                    <div style="font-weight: 600; font-size: 14px; margin-bottom: 5px;">${doc.document_type}</div>
                    <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 15px;">Status: <span style="color: var(--success);">${doc.status}</span></div>
                    <a href="../${doc.document_url}" target="_blank" class="btn-preview" style="display: inline-block;">View Document</a>
                </div>
            `).join('')}
        </div>
    `;
}

function renderReviews(reviews) {
    const container = document.getElementById('reviewsList');
    if (!container) return;

    if (!reviews || reviews.length === 0) {
        container.innerHTML = '<div style="text-align: center; padding: 40px; opacity: 0.5;">No reviews found for this agency yet.</div>';
        return;
    }

    container.innerHTML = `
        <div style="display: flex; flex-direction: column; gap: 20px;">
            ${reviews.map(rev => `
                <div class="review-item glass" style="padding: 20px; border-radius: 16px; border: 1px solid var(--glass-border);">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                        <div style="display: flex; gap: 12px; align-items: center;">
                            <img src="../${rev.reviewer_avatar || 'includes/assets/images/user-placeholder.jpg'}" style="width: 44px; height: 44px; border-radius: 12px; object-fit: cover;">
                            <div>
                                <div style="font-weight: 700; font-size: 15px;">${rev.reviewer_name}</div>
                                <div style="font-size: 12px; opacity: 0.6;">Reviewed <span style="color: var(--primary); font-weight: 600;">${rev.agent_name}</span></div>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="color: #ffb800; font-size: 16px; margin-bottom: 4px;">
                                ${Array(5).fill(0).map((_, i) => `<i class="${i < rev.rating ? 'fa-fill' : 'ph'} ph-star"></i>`).join('')}
                            </div>
                            <div style="font-size: 11px; opacity: 0.5;">${new Date(rev.created_at).toLocaleDateString()}</div>
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

function setupActionButtons(agency) {
    const verifyBtn = document.getElementById('verifyAgencyBtn');
    if (verifyBtn) {
        verifyBtn.onclick = () => {
            Swal.fire({
                title: 'Verify Agency?',
                text: "This will mark the agency as verified and visible to all users.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: 'var(--success)',
                confirmButtonText: 'Yes, Verify!'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('action', 'agency_verify');
                    formData.append('id', agency.id);
                    const res = await fetch('../includes/api/admin/update_status.php', { method: 'POST', body: formData });
                    if ((await res.json()).success) {
                        Swal.fire('Verified!', 'Agency Status Updated.', 'success').then(() => location.reload());
                    }
                }
            });
        };
    }

    const editBtn = document.querySelector('.btn-primary[id="editProfileBtn"]') || document.querySelector('button[onclick*="Edit Profile"]') || document.getElementsByClassName('btn-primary')[0];
    if (editBtn) {
        editBtn.removeAttribute('onclick');
        editBtn.id = 'editAgencyBtn';
        editBtn.onclick = () => showEditAgencyModal(agency);
    }
}

async function showEditAgencyModal(agency) {
    const { value: formValues } = await Swal.fire({
        title: 'Edit Agency Profile',
        html: `
            <div class="premium-modal-form" style="text-align: left; padding: 10px 5px;">
                <div class="form-group-swal" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 500; color: #fff; opacity: 0.9;">Agency Name</label>
                    <input id="edit-agency-name" class="glass-input" style="width: 100%; height: 45px; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 0 15px;" value="${agency.name || ''}">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 500; color: #fff; opacity: 0.9;">Public Email</label>
                        <input id="edit-agency-email" class="glass-input" style="width: 100%; height: 45px; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 0 15px;" value="${agency.email || ''}">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 500; color: #fff; opacity: 0.9;">Phone Number</label>
                        <input id="edit-agency-phone" class="glass-input" style="width: 100%; height: 45px; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 0 15px;" value="${agency.phone || ''}">
                    </div>
                </div>
                <div class="form-group-swal" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 500; color: #fff; opacity: 0.9;">Website URL</label>
                    <input id="edit-agency-website" class="glass-input" style="width: 100%; height: 45px; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 0 15px;" value="${agency.website || ''}" placeholder="https://...">
                </div>
                <div class="form-group-swal" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 500; color: #fff; opacity: 0.9;">Office Address</label>
                    <textarea id="edit-agency-address" class="glass-input" style="width: 100%; height: 80px; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 10px 15px; resize: none;">${agency.address || ''}</textarea>
                </div>
                <div class="form-group-swal">
                    <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 500; color: #fff; opacity: 0.9;">Agency Bio</label>
                    <textarea id="edit-agency-bio" class="glass-input" style="width: 100%; height: 100px; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 10px 15px; resize: none;">${agency.bio || ''}</textarea>
                </div>
            </div>
        `,
        background: '#1a1d21',
        color: '#fff',
        showCancelButton: true,
        confirmButtonText: 'Update Profile',
        confirmButtonColor: '#6c5dd3',
        preConfirm: () => {
            return {
                id: agency.id,
                name: document.getElementById('edit-agency-name').value,
                email: document.getElementById('edit-agency-email').value,
                phone: document.getElementById('edit-agency-phone').value,
                website: document.getElementById('edit-agency-website').value,
                address: document.getElementById('edit-agency-address').value,
                bio: document.getElementById('edit-agency-bio').value
            }
        }
    });

    if (formValues) {
        const formData = new FormData();
        Object.entries(formValues).forEach(([key, val]) => formData.append(key, val));

        const res = await fetch('../includes/api/admin/update_agency.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            Swal.fire('Updated!', 'Agency profile has been updated.', 'success').then(() => location.reload());
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    }
}

function setupTabSwitching() {
    document.querySelectorAll('.agency-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.agency-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            const target = tab.getAttribute('data-target');
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('active');
            });
            document.getElementById(target).classList.add('active');
        });
    });
}


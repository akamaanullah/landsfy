let approvalsState = {
    listings: [],
    agencies: [],
    history: []
};

document.addEventListener('DOMContentLoaded', function() {
    fetchApprovalsData();
    setupTabs();
});

async function fetchApprovalsData() {
    const queueContainer = document.getElementById('pendingQueue');
    queueContainer.innerHTML = '<div style="width: 100%; text-align: center; padding: 60px;"><i class="fa-solid fa-circle-notch fa-spin fa-spin" style="font-size: 32px;"></i><p>Loading queue...</p></div>';

    try {
        const response = await fetch('../includes/api/admin/get_approvals.php');
        const result = await response.json();
        
        if (result.success) {
            approvalsState.listings = result.data.pending_listings;
            approvalsState.agencies = result.data.pending_agencies;
            approvalsState.history = result.data.recent_actions;
            
            updateStats(result.data.stats);
            
            const activeTab = document.querySelector('.approval-tab-btn.active').dataset.tab;
            if (activeTab === 'listings') renderQueue(approvalsState.listings);
            else if (activeTab === 'agencies') renderAgencies(approvalsState.agencies);
            else renderHistory(approvalsState.history);
            
        } else {
            Landsfy.showToast(result.message || 'Failed to load approvals', 'error');
        }
    } catch (error) {
        console.error('Fetch Error:', error);
        Landsfy.showToast('Network error while loading queue', 'error');
    }
}

function setupTabs() {
    const tabs = document.querySelectorAll('.approval-tab-btn');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            
            const target = tab.dataset.tab;
            if (target === 'listings') renderQueue(approvalsState.listings);
            else if (target === 'agencies') renderAgencies(approvalsState.agencies);
            else renderHistory(approvalsState.history);
        });
    });
}

function updateStats(stats) {
    if (document.getElementById('statPending')) {
        document.getElementById('statPending').innerText = (stats.pending || 0).toLocaleString();
        document.getElementById('statApproved').innerText = (stats.approved_today || 0).toLocaleString();
        document.getElementById('statRejected').innerText = (stats.rejected_today || 0).toLocaleString();
    }
    
    // Update tab counts
    const listingsTab = document.getElementById('listingsCount');
    const agenciesTab = document.getElementById('agenciesCount');
    if (listingsTab) listingsTab.textContent = (stats.pending_listings || 0).toLocaleString();
    if (agenciesTab) agenciesTab.textContent = (stats.pending_agencies || 0).toLocaleString();
}

function renderQueue(listings) {
    const container = document.getElementById('pendingQueue');
    if (!container) return;

    if (listings.length === 0) {
        container.innerHTML = `
            <div class="empty-state glass" style="width: 100%; padding: 80px; text-align: center;">
                <i class="fa-solid fa-house-chimney" style="font-size: 64px; opacity: 0.3; margin-bottom: 20px;"></i>
                <h3>No Listings Pending</h3>
                <p style="color: var(--text-secondary);">There are no properties currently waiting for review.</p>
            </div>`;
        return;
    }

    container.innerHTML = listings.map(listing => `
        <div class="approval-card glass" data-id="${listing.id}" style="padding: 16px; margin-bottom: 16px; display: flex; align-items: center; gap: 20px;">
            <img src="${Landsfy.getImageUrl(listing.featured_image)}" 
                 alt="Property" class="approval-thumb" 
                 style="width: 120px; height: 85px; border-radius: 12px; object-fit: cover;"
                 onerror="Landsfy.handleImageError(this, 'property')">
            <div class="approval-info" style="flex: 1;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                    <h3 class="approval-title" style="font-size: 15px; font-weight: 700;">${Landsfy.escapeHtml(listing.title)}</h3>
                    ${listing.is_featured == 1 ? '<span class="approval-priority priority-high" style="font-size: 10px; padding: 2px 8px;">Featured</span>' : ''}
                </div>
                <div class="approval-meta-row" style="display: flex; gap: 15px; font-size: 12px; color: var(--text-secondary); margin-bottom: 10px;">
                    <span><i class="fa-solid fa-location-dot"></i> ${Landsfy.escapeHtml(listing.location_name || 'Location N/A')}</span>
                    <span><i class="fa-solid fa-circle-dollar-to-slot"></i> ${listing.currency || 'PKR'} ${parseInt(listing.price).toLocaleString()}</span>
                    <span><i class="fa-solid fa-tag"></i> For ${listing.purpose} · ${listing.category_name}</span>
                    <span><i class="fa-solid fa-calendar-days-blank"></i> ${Landsfy.formatDate(listing.created_at)}</span>
                </div>
                <div class="approval-agent" style="display: flex; align-items: center; gap: 8px;">
                    <img src="${Landsfy.getImageUrl(listing.agent_avatar)}" 
                         style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover;"
                         alt="agent"
                         onerror="Landsfy.handleImageError(this, 'agent')">
                    <span style="font-size: 12px; font-weight: 600; opacity: 0.8;">${Landsfy.escapeHtml(listing.agent_name)}</span>
                </div>
            </div>
            <div style="display: flex; gap: 10px; align-items: center; flex-shrink: 0;">
                <button class="btn-preview" style="padding: 8px 16px; font-size: 13px;" onclick="window.location.href='property-detail.php?id=${listing.id}'">
                    <i class="fa-solid fa-eye"></i> Preview
                </button>
                <div class="approval-actions" style="display: flex; gap: 8px;">
                    <button class="btn-approve" style="padding: 8px 16px; font-size: 13px; background: var(--success); color: white; border: none; border-radius: 8px; cursor: pointer;" onclick="actionListing(${listing.id}, 'approve')">
                        <i class="fa-solid fa-circle-check"></i> Approve
                    </button>
                    <button class="btn-reject" style="padding: 8px 16px; font-size: 13px; background: rgba(239, 68, 68, 0.1); color: var(--danger); border: 1px solid var(--danger); border-radius: 8px; cursor: pointer;" onclick="actionListing(${listing.id}, 'reject')">
                        <i class="fa-solid fa-circle-xmark"></i> Reject
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

function renderAgencies(agencies) {
    const container = document.getElementById('pendingQueue');
    if (!container) return;

    if (agencies.length === 0) {
        container.innerHTML = `
            <div class="empty-state glass" style="width: 100%; padding: 80px; text-align: center;">
                <i class="fa-solid fa-building" style="font-size: 64px; opacity: 0.3; margin-bottom: 20px;"></i>
                <h3>No Agency Applications</h3>
                <p style="color: var(--text-secondary);">There are no agency registration requests waiting.</p>
            </div>`;
        return;
    }

    container.innerHTML = agencies.map(agency => `
        <div class="approval-card glass" data-id="${agency.id}" style="padding: 16px; margin-bottom: 16px; display: flex; align-items: center; gap: 20px;">
            <img src="${Landsfy.getImageUrl(agency.logo_url)}" 
                 alt="Logo" class="approval-thumb" 
                 style="width: 85px; height: 85px; border-radius: 12px; object-fit: cover;"
                 onerror="Landsfy.handleImageError(this, 'agency')">
            <div class="approval-info" style="flex: 1;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                    <h3 class="approval-title" style="font-size: 15px; font-weight: 700;">${Landsfy.escapeHtml(agency.name)}</h3>
                    <span class="approval-priority" style="background: rgba(108, 93, 211, 0.1); color: var(--primary); font-size: 10px; padding: 2px 8px; border-radius: 4px;">Agency Account</span>
                </div>
                <div class="approval-meta-row" style="display: flex; gap: 15px; font-size: 12px; color: var(--text-secondary); margin-bottom: 10px;">
                    <span><i class="fa-solid fa-circle-user"></i> Owner: ${Landsfy.escapeHtml(agency.owner_name)}</span>
                    <span><i class="fa-solid fa-phone"></i> ${Landsfy.escapeHtml(agency.phone || 'No Phone')}</span>
                    <span><i class="fa-solid fa-location-dot"></i> ${Landsfy.escapeHtml(agency.address || 'No Address')}</span>
                    <span><i class="fa-solid fa-calendar-days-blank"></i> Joined: ${Landsfy.formatDate(agency.created_at)}</span>
                </div>
            </div>
            <div style="display: flex; gap: 10px; align-items: center; flex-shrink: 0;">
                <button class="btn-preview" style="padding: 8px 16px; font-size: 13px;" onclick="window.location.href='agency-detail.php?id=${agency.id}'">
                    <i class="fa-solid fa-eye"></i> View Profile
                </button>
                <div class="approval-actions" style="display: flex; gap: 8px;">
                    <button class="btn-approve" style="padding: 8px 16px; font-size: 13px; background: var(--success); color: white; border: none; border-radius: 8px; cursor: pointer;" onclick="actionAgency(${agency.id}, 'approve')">
                        <i class="fa-solid fa-circle-check"></i> Verify Agency
                    </button>
                    <button class="btn-reject" style="padding: 8px 16px; font-size: 13px; background: rgba(239, 68, 68, 0.1); color: var(--danger); border: 1px solid var(--danger); border-radius: 8px; cursor: pointer;" onclick="actionAgency(${agency.id}, 'reject')">
                        <i class="fa-solid fa-trash-can"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

function actionAgency(id, action) {
    const title = action === 'approve' ? 'Verify Agency?' : 'Delete Agency Request?';
    const confirmBtn = action === 'approve' ? 'Yes, Verify' : 'Yes, Delete';
    const confirmColor = action === 'approve' ? 'var(--success)' : '#ff4757';

    Swal.fire({
        title: title,
        text: action === 'approve' ? "This agency will be activated and marked as verified." : "This will permanently remove the agency registration request.",
        icon: action === 'approve' ? 'question' : 'warning',
        showCancelButton: true,
        confirmButtonColor: confirmColor,
        cancelButtonColor: '#8C98A4',
        confirmButtonText: confirmBtn
    }).then(async (result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', action === 'approve' ? 'agency_verify' : 'agency_delete');
            formData.append('id', id);

            try {
                const res = await fetch('../includes/api/admin/update_status.php', { method: 'POST', body: formData });
                const result = await res.json();
                
                if (result.success) {
                    Swal.fire({
                        title: action === 'approve' ? 'Verified!' : 'Deleted!',
                        text: result.message || 'Agency status has been updated successfully.',
                        icon: 'success',
                        background: 'var(--glass-bg)',
                        color: 'var(--text-primary)',
                        confirmButtonColor: 'var(--primary)',
                        backdrop: `rgba(0,0,0,0.4) blur(4px)`
                    }).then(() => fetchApprovalsData());
                } else {
                    Landsfy.showToast(result.message || 'Failed to update agency', 'error');
                }
            } catch (err) {
                console.error('Action Error:', err);
                Landsfy.showToast('Network error during action', 'error');
            }
        }
    });
}

function actionListing(id, action) {
    const title = action === 'approve' ? 'Approve Listing?' : 'Reject Listing?';
    const confirmBtn = action === 'approve' ? 'Yes, Approve' : 'Yes, Reject';
    const confirmColor = action === 'approve' ? 'var(--success)' : '#ff4757';

    Swal.fire({
        title: title,
        text: action === 'approve' ? "This property will become live on the platform." : "Please specify a reason for rejection.",
        input: action === 'reject' ? 'textarea' : null,
        inputPlaceholder: 'Type rejection reason here...',
        icon: action === 'approve' ? 'question' : 'warning',
        showCancelButton: true,
        confirmButtonColor: confirmColor,
        cancelButtonColor: '#8C98A4',
        confirmButtonText: confirmBtn,
        background: 'var(--glass-bg)',
        color: 'var(--text-primary)',
        backdrop: `rgba(0,0,0,0.4) blur(4px)`,
        customClass: {
            popup: 'premium-swal-popup'
        }
    }).then(async (result) => {
        if (result.isConfirmed) {
            const reason = result.value || '';
            const formData = new FormData();
            formData.append('action', action === 'approve' ? 'listing_approve' : 'listing_reject');
            formData.append('id', id);
            formData.append('reason', reason);

            try {
                const res = await fetch('../includes/api/admin/update_status.php', { method: 'POST', body: formData });
                const result = await res.json();
                
                if (result.success) {
                    Swal.fire({
                        title: action === 'approve' ? 'Approved!' : 'Rejected!',
                        text: result.message || 'Listing status has been updated.',
                        icon: 'success',
                        background: 'var(--glass-bg)',
                        color: 'var(--text-primary)',
                        confirmButtonColor: 'var(--primary)',
                        backdrop: `rgba(0,0,0,0.4) blur(4px)`
                    }).then(() => fetchApprovalsData());
                } else {
                    Landsfy.showToast(result.message || 'Failed to update listing', 'error');
                }
            } catch (err) {
                console.error('Action Error:', err);
                Landsfy.showToast('Network error during action', 'error');
            }
        }
    });
}

function renderHistory(history) {
    const container = document.getElementById('pendingQueue');
    if (!container) return;
 
    if (history.length === 0) {
        container.innerHTML = `
            <div class="empty-state glass" style="width: 100%; padding: 80px; text-align: center;">
                <i class="fa-solid fa-clock-rotate-left" style="font-size: 64px; opacity: 0.3; margin-bottom: 20px;"></i>
                <h3>No Recent Actions</h3>
                <p style="color: var(--text-secondary);">Your approval and rejection history will appear here.</p>
            </div>`;
        return;
    }
 
    container.innerHTML = history.map(log => {
        const isApproved = log.action_type === 'property_approved';
        const typeClass = isApproved ? 'status-active' : 'status-rejected';
        const icon = isApproved ? 'fa-circle-check' : 'fa-xmark-circle';
        
        return `
            <div class="approval-card glass" style="opacity: 0.85;">
                <div class="history-badge ${typeClass}">
                    <i class="fa-solid ${icon}"></i>
                    ${isApproved ? 'Approved' : 'Rejected'}
                </div>
                <div class="approval-info" style="margin-left: 0;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                        <h3 class="approval-title">${Landsfy.escapeHtml(log.property_title || 'Unknown Property')}</h3>
                        <span style="font-size: 11px; opacity: 0.6;">(ID: #${log.target_id})</span>
                    </div>
                    <p style="font-size: 13px; opacity: 0.8; margin-bottom: 8px;">${Landsfy.escapeHtml(log.description)}</p>
                    <div class="approval-meta-row">
                        <span><i class="fa-solid fa-user"></i> Action by: ${Landsfy.escapeHtml(log.actor_name || 'System')}</span>
                        <span><i class="fa-solid fa-clock"></i> ${Landsfy.formatDate(log.created_at)}</span>
                    </div>
                </div>
                <div style="flex-shrink: 0;">
                    <button class="btn-preview" onclick="window.location.href='property-detail.php?id=${log.target_id}'">
                        <i class="fa-solid fa-eye"></i> View
                    </button>
                </div>
            </div>
        `;
    }).join('');
}
 


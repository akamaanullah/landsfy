/**
 * Admin Dashboard Logic
 * Fetches stats and populates UI components
 */

document.addEventListener('DOMContentLoaded', function() {
    fetchDashboardData();
    
    // Tab switching logic for recent submissions
    const tabBtns = document.querySelectorAll('.tab-btn');
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.dataset.target;
            tabBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            document.querySelectorAll('.property-item[data-type]').forEach(item => {
                if(target === 'all' || item.dataset.type === target) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
});

async function fetchDashboardData() {
    try {
        const response = await fetch('../includes/api/admin/get_dashboard_stats.php');
        const result = await response.json();
        
        if (result.success) {
            updateStatsCards(result.data.stats);
            renderRecentProperties(result.data.recent_properties);
            renderNewUsers(result.data.new_users);
            updateChart(result.data.chart_data);
        } else {
            console.error('API Error:', result.message);
        }
    } catch (error) {
        console.error('Fetch Error:', error);
    }
}

function updateStatsCards(stats) {
    const values = document.querySelectorAll('.stat-value');
    if (values.length >= 4) {
        values[0].textContent = stats.total.toLocaleString();
        values[1].textContent = stats.active.toLocaleString();
        values[2].textContent = stats.pending.toLocaleString();
        values[3].textContent = stats.users.toLocaleString();
        
        // Update pending card state
        const pendingCard = document.querySelector('.stat-card:nth-child(3)');
        if (stats.pending > 0) {
            pendingCard.classList.add('active');
            pendingCard.querySelector('.stat-trend').innerHTML = '<i class="fa-solid fa-pulse"></i> Action Reqd';
            pendingCard.querySelector('.stat-trend').className = 'stat-trend trend-down';
        } else {
            pendingCard.classList.remove('active');
            pendingCard.querySelector('.stat-trend').innerHTML = '<i class="fa-solid fa-pulse"></i> Optimal';
            pendingCard.querySelector('.stat-trend').className = 'stat-trend trend-up';
        }
    }
}

function renderRecentProperties(properties) {
    const listContainer = document.querySelector('.property-list');
    if (!listContainer) return;
    
    if (properties.length === 0) {
        listContainer.innerHTML = `
            <div class="empty-state" style="padding: 40px; text-align: center; color: var(--text-secondary);">
                <i class="fa-solid fa-ghost" style="font-size: 48px; opacity: 0.5;"></i>
                <p>No listings submitted yet.</p>
            </div>`;
        return;
    }
    
    listContainer.innerHTML = properties.map(prop => `
        <div class="property-item" data-type="${prop.purpose}">
            <img src="${Landsfy.getImageUrl(prop.featured_image)}" 
                 alt="Property" class="property-img"
                 onerror="Landsfy.handleImageError(this, 'property')">
            <div class="property-details">
                <div class="property-title" onclick="window.location.href='property-detail.php?id=${prop.id}'" style="cursor:pointer;font-size:12px;font-weight:600;">
                    ${Landsfy.escapeHtml(prop.title)}
                </div>
                <div class="property-meta">
                    <span><i class="fa-solid fa-user"></i> ${Landsfy.escapeHtml(prop.author_name)}</span>
                    <span><i class="fa-solid fa-map-pin"></i> ${Landsfy.escapeHtml(prop.location_name || 'N/A')}</span>
                    <span><i class="fa-solid fa-calendar"></i> ${Landsfy.formatDate(prop.created_at)}</span>
                </div>
            </div>
            <div class="status-badge status-${prop.purpose}" style="text-transform: capitalize;font-size:11px;padding:4px 10px;">
                ${prop.purpose}
            </div>
            <div class="property-price" style="font-size:13px;font-weight:600;">${prop.currency || 'PKR'} ${parseInt(prop.price).toLocaleString()}</div>
            <div class="property-actions">
                <button class="action-btn action-edit" onclick="window.location.href='add-property.php?id=${prop.id}'" title="Edit">
                    <i class="fa-solid fa-pencil"></i>
                </button>
                <button class="action-btn action-delete" onclick="handleDelete(${prop.id})" title="Delete"><i class="fa-solid fa-trash-can"></i></button>
            </div>
        </div>
    `).join('');
}

function renderNewUsers(users) {
    const containers = document.querySelectorAll('.property-list');
    if (containers.length < 2) return;
    
    const container = containers[1];
    container.innerHTML = users.map(user => `
        <div class="property-item" style="padding: 10px 14px; gap: 12px;">
            <img src="${Landsfy.getImageUrl(user.avatar_url)}" 
                 style="width: 36px; height:36px; border-radius: 50%; object-fit: cover;" 
                 alt="user"
                 onerror="Landsfy.handleImageError(this, 'user')">
            <div class="property-details">
                <div class="property-title" style="font-size: 13px; font-weight: 600;">${Landsfy.escapeHtml(user.username)}</div>
                <div class="property-meta" style="font-size: 11px;">Joined ${Landsfy.formatDate(user.created_at, true)}</div>
            </div>
            <button class="action-btn action-edit" style="width: 32px; height: 32px; font-size: 16px;" onclick="window.location.href='user-detail.php?id=${user.id}'"><i class="fa-solid fa-eye"></i></button>
        </div>
    `).join('');
}

function updateChart(data) {
    const doughnut = document.getElementById('dashboardDoughnut');
    const legend = document.getElementById('dashboardLegend');
    if (!doughnut || !legend) return;
    
    // Clear and build dynamic legend
    const colors = ['var(--primary)', 'var(--info)', 'var(--warning)', 'var(--success)', '#ff4757', '#ffa502'];
    let legendHtml = '';
    let totalCount = Object.values(data).reduce((a, b) => a + b, 0);
    
    let index = 0;
    for (const [cat, count] of Object.entries(data)) {
        const color = colors[index % colors.length];
        const percent = totalCount > 0 ? (count / totalCount) * 100 : 0;
        
        // Update doughnut variables (we'll use a conic-gradient for truly dynamic n-slices if needed, 
        // but for now let's just update the 3 main ones or use a simpler visual)
        if (index === 0) doughnut.style.setProperty('--res', count);
        if (index === 1) doughnut.style.setProperty('--com', count);
        if (index === 2) doughnut.style.setProperty('--plt', count);
        
        legendHtml += `
            <div class="legend-item">
                <div class="legend-dot" style="background: ${color}"></div> 
                <span style="text-transform: capitalize;">${cat}</span> (${count})
            </div>`;
        index++;
    }
    
    legend.innerHTML = legendHtml;
}


function handleDelete(id) {
    Swal.fire({
        title: 'Delete Property?',
        text: "This action cannot be undone.",
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

                const response = await fetch('../includes/api/admin/update_status.php', {
                    method: 'POST',
                    body: formData
                });
                const resData = await response.json();

                if (resData.success) {
                    Swal.fire('Deleted!', 'Listing has been removed.', 'success');
                    // Remove from UI
                    document.querySelectorAll(`.property-item`).forEach(item => {
                        if (item.querySelector(`[onclick*="handleDelete(${id})"]`)) {
                            item.style.opacity = '0';
                            item.style.transform = 'translateX(-20px)';
                            setTimeout(() => item.remove(), 300);
                        }
                    });
                } else {
                    Swal.fire('Error', resData.message || 'Deletion failed', 'error');
                }
            } catch (e) {
                Swal.fire('Error', 'Server Communication failed', 'error');
            }
        }
    });
}

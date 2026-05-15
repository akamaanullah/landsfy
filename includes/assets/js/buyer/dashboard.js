document.addEventListener('DOMContentLoaded', function() {
    fetchDashboardStats();
    fetchRecentlySaved();
    fetchRecentTimeline();

    // Theme Toggle Logic
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    
    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            if (themeIcon) themeIcon.className = newTheme === 'light' ? 'fa-solid fa-moon' : 'fa-solid fa-sun';
            localStorage.setItem('theme', newTheme);
            
            // Save to DB (reusing site settings helper if possible)
            saveThemeSetting(newTheme);
        });
    }
});

async function fetchDashboardStats() {
    try {
        const response = await fetch('../includes/api/buyer/get_dashboard_stats.php');
        const data = await response.json();
        
        if (data.success) {
            const stats = data.stats;
            const elSaved = document.getElementById('statSaved');
            const elInq = document.getElementById('statInquiries');
            const elViews = document.getElementById('statViews');

            if (elSaved) elSaved.textContent = stats.total_saved;
            if (elInq) elInq.textContent = stats.total_inquiries;
            if (elViews) elViews.textContent = stats.viewed_today;
        }
    } catch (error) {
        console.error('Error fetching stats:', error);
    }
}

async function fetchRecentlySaved() {
    const propertyList = document.querySelector('.property-list');
    if (!propertyList) return;

    try {
        const response = await fetch('../includes/api/buyer/get_saved_properties.php');
        const data = await response.json();
        
        if (data.success) {
            if (data.properties.length === 0) {
                propertyList.innerHTML = '<div style="padding: 40px; text-align: center; color: var(--text-secondary);"><i class="fa-regular fa-heart" style="font-size: 32px; opacity: 0.1; display: block; margin-bottom: 12px;"></i>No properties saved yet.</div>';
                return;
            }

            propertyList.innerHTML = data.properties.slice(0, 3).map(prop => `
                <div class="property-item" style="padding: 12px; border: 1px solid var(--glass-border); border-radius: 16px; margin-bottom: 12px; display: flex; align-items: center; gap: 16px;">
                    <img src="${Landsfy.getImageUrl(prop.main_image)}" style="width: 50px; height: 50px; border-radius: 10px; object-fit: cover;" onerror="Landsfy.handleImageError(this, 'property')">
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-weight: 700; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${prop.title}</div>
                        <div style="font-size: 12px; color: var(--text-secondary); display: flex; align-items: center; gap: 4px;">
                            <i class="fa-solid fa-location-dot"></i> ${prop.city_name}
                        </div>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button class="icon-btn" onclick="toggleSave(${prop.id})" style="color: var(--danger); background: rgba(239, 68, 68, 0.05); width: 32px; height: 32px;"><i class="fa-regular fa-heart-break"></i></button>
                        <a href="../property-detail.php?slug=${prop.slug}" class="icon-btn" style="width: 32px; height: 32px; background: rgba(107, 0, 182, 0.05); color: var(--primary);"><i class="fa-solid fa-eye"></i></a>
                    </div>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Error fetching properties:', error);
    }
}

async function fetchRecentTimeline() {
    const timeline = document.getElementById('recentTimeline');
    if (!timeline) return;

    try {
        const response = await fetch('../includes/api/buyer/get_inquiries.php');
        const data = await response.json();
        
        if (data.success && data.inquiries.length > 0) {
            timeline.innerHTML = data.inquiries.slice(0, 3).map(inq => {
                let iconClass = 'icon-chat';
                let iconName = 'fa-chat-circle';
                let actionText = 'Inquired about';

                if (inq.message === 'whatsapp_click') {
                    iconClass = 'icon-whatsapp';
                    iconName = 'fa-whatsapp';
                    actionText = 'WhatsApp click on';
                } else if (inq.message === 'call_reveal') {
                    iconClass = 'icon-call';
                    iconName = 'fa-phone';
                    actionText = 'Call reveal on';
                }

                return `
                    <div class="adb-timeline-item">
                        <div class="adb-timeline-icon ${iconClass}">
                            <i class="fa-solid ${iconName}"></i>
                        </div>
                        <div class="adb-timeline-content">
                            <div class="timeline-title">
                                ${actionText} <strong>${inq.property_title}</strong>
                            </div>
                            <div class="timeline-time">
                                <i class="fa-solid fa-clock"></i> ${formatTime(inq.created_at)}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }
 else {
            timeline.innerHTML = '<div style="text-align: center; color: var(--text-secondary); padding: 20px;">No recent activity.</div>';
        }
    } catch (error) {
        console.error('Error fetching timeline:', error);
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
                title: data.status === 'saved' ? 'Saved!' : 'Removed!',
                text: data.status === 'saved' ? 'Property added to your favorites.' : 'Property removed from favorites.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
            fetchRecentlySaved();
            fetchDashboardStats();
        }
    } catch (error) {
        console.error('Error toggling save:', error);
    }
}

async function saveThemeSetting(theme) {
    const formData = new FormData();
    formData.append('key', 'buyer_theme');
    formData.append('value', theme);
    
    fetch('../includes/api/save_user_setting.php', { method: 'POST', body: formData });
}

function formatTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);

    if (diffInSeconds < 60) return 'Just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
    return date.toLocaleDateString();
}

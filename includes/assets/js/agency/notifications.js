document.addEventListener('DOMContentLoaded', function() {
    fetchNotifications();

    const markReadBtn = document.getElementById('markAllReadBtn');
    if (markReadBtn) {
        markReadBtn.addEventListener('click', markAllAsRead);
    }

    // Refresh every 30 seconds
    setInterval(fetchNotifications, 30000);
});

async function fetchNotifications() {
    const list = document.getElementById('notificationsList');
    if (!list) return;

    try {
        const response = await fetch('../includes/api/agency/get_notifications.php');
        const data = await response.json();

        if (data.success) {
            if (data.notifications.length === 0) {
                list.innerHTML = `
                    <div class="empty-state">
                        <i class="fa-solid fa-bell-slash"></i>
                        <h3>No activity yet</h3>
                        <p>When your agents add or modify properties, notifications will appear here.</p>
                    </div>
                `;
                return;
            }

            renderNotifications(data.notifications);
            updateBadge(data.notifications);
        }
    } catch (error) {
        console.error('Error fetching notifications:', error);
    }
}

function renderNotifications(notifications) {
    const list = document.getElementById('notificationsList');
    
    list.innerHTML = notifications.map(notif => {
        const icon = getIconForType(notif.type);
        const color = getColorForType(notif.type);
        const isUnread = notif.is_read == 0;

        return `
            <div class="activity-item glass ${isUnread ? 'unread' : ''}">
                <div class="activity-icon-wrapper" style="background: ${color.bg}; color: ${color.text};">
                    <i class="fa-solid ${icon}"></i>
                </div>
                <div class="activity-content">
                    <div class="activity-header">
                        <div class="activity-title">${notif.title}</div>
                        <div class="activity-time">${formatTimeAgo(notif.created_at)}</div>
                    </div>
                    <div class="activity-message">${notif.message}</div>
                    <div class="agent-meta">
                        <img src="${notif.sender_avatar ? '../' + notif.sender_avatar : 'https://i.pravatar.cc/150?u=' + notif.sender_id}" class="agent-avatar-small">
                        <span>Action performed by <strong>${notif.sender_name || 'Agent'}</strong></span>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

function getIconForType(type) {
    switch(type) {
        case 'property_added': return 'fa-circle-plus';
        case 'property_updated': return 'fa-pencil';
        case 'property_deleted': return 'fa-trash';
        default: return 'fa-bell';
    }
}

function getColorForType(type) {
    switch(type) {
        case 'property_added': return { bg: 'rgba(16, 185, 129, 0.1)', text: '#10b981' };
        case 'property_updated': return { bg: 'rgba(245, 158, 11, 0.1)', text: '#f59e0b' };
        case 'property_deleted': return { bg: 'rgba(239, 68, 68, 0.1)', text: '#ef4444' };
        default: return { bg: 'rgba(107, 0, 182, 0.1)', text: 'var(--primary)' };
    }
}

function formatTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);

    if (seconds < 60) return 'Just now';
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return `${minutes}m ago`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours}h ago`;
    const days = Math.floor(hours / 24);
    if (days < 7) return `${days}d ago`;
    return date.toLocaleDateString();
}

function updateBadge(notifications) {
    const unreadCount = notifications.filter(n => n.is_read == 0).length;
    const badges = document.querySelectorAll('.nav-badge, #notifBadge');
    
    badges.forEach(badge => {
        if (unreadCount > 0) {
            badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    });
}

async function markAllAsRead() {
    try {
        const response = await fetch('../includes/api/agency/mark_notifications_read.php', {
            method: 'POST'
        });
        const data = await response.json();
        if (data.success) {
            fetchNotifications();
        }
    } catch (error) {
        console.error('Error marking read:', error);
    }
}

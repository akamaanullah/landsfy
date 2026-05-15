document.addEventListener('DOMContentLoaded', () => {
    const notifBell = document.getElementById('notifBell');
    const notifDropdown = document.getElementById('notifDropdown');
    const pulseDot = document.querySelector('.pulse-dot');
    
    // Initial fetch
    fetchNotifications();

    // Poll every 30 seconds
    setInterval(fetchNotifications, 30000);

    // Toggle dropdown
    if (notifBell) {
        notifBell.addEventListener('click', (e) => {
            e.stopPropagation();
            notifDropdown.style.display = notifDropdown.style.display === 'block' ? 'none' : 'block';
        });
    }

    // Close dropdown on click outside
    document.addEventListener('click', () => {
        if (notifDropdown) notifDropdown.style.display = 'none';
    });

    async function fetchNotifications() {
        try {
            const response = await fetch('../includes/api/seller/get_notifications.php');
            const data = await response.json();

            if (data.success) {
                renderNotifications(data.notifications);
            }
        } catch (error) {
            console.error('Error fetching notifications:', error);
        }
    }

    function renderNotifications(notifications) {
        const unreadCount = notifications.filter(n => !n.is_read).length;
        
        // Update Pulse Dot
        if (pulseDot) {
            pulseDot.style.display = unreadCount > 0 ? 'block' : 'none';
        }

        const container = notifDropdown.querySelector('div:last-child');
        if (!container) return;

        if (notifications.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; padding: 32px 0;">
                    <div style="width: 48px; height: 48px; background: rgba(107, 0, 182, 0.1); color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                        <i class="fa-solid fa-bell-slash" style="font-size: 24px;"></i>
                    </div>
                    <p style="font-size: 13px; color: var(--text-secondary); margin: 0;">You're all caught up!</p>
                </div>
            `;
            return;
        }

        container.innerHTML = notifications.map(notif => `
            <div class="notif-item ${!notif.is_read ? 'unread' : ''}" style="padding: 12px; border-radius: 12px; cursor: pointer; transition: all 0.2s; display: flex; gap: 12px; margin-bottom: 8px; border: 1px solid ${!notif.is_read ? 'rgba(107, 0, 182, 0.1)' : 'transparent'}; background: ${!notif.is_read ? 'rgba(107, 0, 182, 0.05)' : 'transparent'};">
                <img src="${notif.sender_avatar ? '../' + notif.sender_avatar : 'https://i.pravatar.cc/150'}" style="width: 36px; height: 36px; border-radius: 10px; object-fit: cover;">
                <div style="flex: 1;">
                    <div style="font-size: 13px; font-weight: 700; color: var(--text-primary);">${notif.title}</div>
                    <div style="font-size: 11px; color: var(--text-secondary); margin-top: 2px;">${notif.message}</div>
                    <div style="font-size: 10px; opacity: 0.5; margin-top: 4px;">${formatTime(notif.created_at)}</div>
                </div>
                ${!notif.is_read ? '<div style="width: 8px; height: 8px; background: var(--primary); border-radius: 50%; margin-top: 6px;"></div>' : ''}
            </div>
        `).join('');
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
});

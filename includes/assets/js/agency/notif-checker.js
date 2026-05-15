document.addEventListener('DOMContentLoaded', function() {
    updateNotificationBadge();
    // Check every 60 seconds
    setInterval(updateNotificationBadge, 60000);
});

async function updateNotificationBadge() {
    try {
        const response = await fetch('../includes/api/agency/get_notifications.php');
        const data = await response.json();

        if (data.success) {
            const unreadCount = data.notifications.filter(n => n.is_read == 0).length;
            const badges = document.querySelectorAll('.nav-badge');
            
            badges.forEach(badge => {
                if (unreadCount > 0) {
                    badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                    badge.style.display = 'flex';
                } else {
                    badge.style.display = 'none';
                }
            });
        }
    } catch (error) {
        // Silent fail for badge checker
    }
}

<?php 
include 'header.php';
?>
        <!-- Main Content -->
        <main class="main-content">
            <header class="header glass">
                <div class="header-left">
                    <div class="page-title">Notifications</div>
                    <div class="breadcrumb">Keep track of your latest activities</div>
                </div>
                
                <div class="header-actions">
                    <button class="icon-btn" id="themeToggle">
                        <i class="fa-solid fa-moon" id="themeIcon"></i>
                    </button>
                    <button class="btn-ghost" id="markAllReadBtn" style="border: 1px solid var(--glass-border);">
                        <i class="fa-solid fa-checks"></i> Mark All as Read
                    </button>
                </div>
            </header>

            <div class="view-container">
                <div class="notifications-container glass">
                    <div id="notificationsList" class="notifications-list">
                        <!-- Notifications will be loaded here -->
                        <div class="loading-state" style="text-align: center; padding: 40px;">
                            <i class="fa-solid fa-circle-notch fa-spin" style="font-size: 32px; color: var(--primary);"></i>
                            <p style="margin-top: 10px; color: var(--text-secondary);">Syncing your alerts...</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <style>
        .notifications-container {
            padding: 24px;
            min-height: 400px;
        }
        .notifications-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .notification-item {
            display: flex;
            gap: 20px;
            padding: 20px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        [data-theme="dark"] .notification-item {
            background: rgba(255, 255, 255, 0.02);
        }
        .notification-item:hover {
            background: rgba(107, 0, 182, 0.05);
            transform: translateX(5px);
            border-color: var(--primary);
        }
        .notification-item.unread {
            background: rgba(107, 0, 182, 0.08);
            border-left: 4px solid var(--primary);
        }
        .notif-icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }
        .type-inquiry { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
        .type-system { background: rgba(107, 0, 182, 0.1); color: var(--primary); }
        .type-approval { background: rgba(16, 185, 129, 0.1); color: #10b981; }

        .notif-content {
            flex: 1;
        }
        .notif-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 4px;
        }
        .notif-title {
            font-weight: 700;
            font-size: 16px;
            color: var(--text-primary);
        }
        .notif-time {
            font-size: 12px;
            color: var(--text-secondary);
        }
        .notif-message {
            font-size: 14px;
            color: var(--text-secondary);
            line-height: 1.5;
        }
        .unread-dot {
            width: 8px;
            height: 8px;
            background: var(--primary);
            border-radius: 50%;
            display: inline-block;
            margin-left: 8px;
        }
        .no-notifications {
            text-align: center;
            padding: 60px 20px;
        }
        .no-notifications i {
            font-size: 64px;
            color: var(--text-secondary);
            opacity: 0.3;
            margin-bottom: 16px;
        }
    </style>

    <!-- External JS -->
    <script src="../includes/assets/js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const list = document.getElementById('notificationsList');
            const markBtn = document.getElementById('markAllReadBtn');

            function loadNotifications() {
                fetch('../includes/api/agent/get_notifications.php')
                    .then(r => r.json())
                    .then(data => {
                        if (data.success && data.data.length > 0) {
                            list.innerHTML = data.data.map(n => `
                                <div class="notification-item ${n.is_read == 0 ? 'unread' : ''}" onclick="markAsRead(${n.id}, '${n.link}')">
                                    <div class="notif-icon-box type-${n.type}">
                                        <i class="${getIcon(n.type)}"></i>
                                    </div>
                                    <div class="notif-content">
                                        <div class="notif-header">
                                            <div class="notif-title">${n.title} ${n.is_read == 0 ? '<span class="unread-dot"></span>' : ''}</div>
                                            <div class="notif-time">${formatTime(n.created_at)}</div>
                                        </div>
                                        <div class="notif-message">${n.message}</div>
                                    </div>
                                </div>
                            `).join('');
                        } else {
                            list.innerHTML = `
                                <div class="no-notifications">
                                    <i class="fa-solid fa-bell-slash"></i>
                                    <h3>No notifications yet</h3>
                                    <p>We'll notify you when something important happens.</p>
                                </div>
                            `;
                        }
                    });
            }

            function getIcon(type) {
                switch(type) {
                    case 'inquiry': return 'fa-solid fa-comments';
                    case 'approval': return 'fa-solid fa-circle-check';
                    default: return 'fa-solid fa-circle-info';
                }
            }

            function formatTime(timestamp) {
                const date = new Date(timestamp);
                return date.toLocaleString();
            }

            window.markAsRead = function(id, link) {
                fetch('../includes/api/agent/mark_notifications_read.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                }).then(() => {
                    if (link && link !== 'null') window.location.href = link;
                    else loadNotifications();
                });
            }

            markBtn.addEventListener('click', () => {
                fetch('../includes/api/agent/mark_notifications_read.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'mark_all' })
                }).then(() => loadNotifications());
            });

            loadNotifications();
        });
    </script>
</body>
</html>

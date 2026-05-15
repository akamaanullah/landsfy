<?php
require_once '../includes/auth_check.php';
require_once '../includes/database/db.php';
require_once '../includes/helpers/image_helper.php';

// Fetch Agency Name
try {
    $stmt = $pdo->prepare("SELECT a.name FROM agencies a JOIN agents ag ON a.id = ag.agency_id WHERE ag.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $agency = $stmt->fetch();
    if ($agency) {
        $agency_name = $agency->name;
    } else {
        // Enforce: without agency there is no agent
        header("Location: ../index.php?error=no_agency");
        exit;
    }
} catch (PDOException $e) {
    header("Location: ../index.php?error=db_error");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<?php include_once '../includes/helpers/notification_helper.php'; ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Dashboard | Landsfy</title>
    <link rel="icon" type="image/png" href="../includes/assets/images/favicon.png">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- External CSS -->
    <link rel="stylesheet" href="../includes/assets/css/style.css">
    <link rel="stylesheet" href="../includes/assets/css/agency-style.css">
    <script src="../includes/assets/js/utils.js"></script>
</head>

<body>
    <!-- Background Blurs -->
    <div class="bg-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
    </div>

    <!-- Toast Notifications -->
    <div id="toast-container"></div>
    
    <!-- Notification Styles -->
    <style>
        .bell-btn { position: relative; }
        .notif-badge { 
            position: absolute; 
            top: -5px; 
            right: -5px; 
            background: #ff4757; 
            color: white; 
            font-size: 10px; 
            font-weight: 800; 
            padding: 2px 6px; 
            border-radius: 10px; 
            border: 2px solid var(--glass-bg);
            display: none;
        }
    </style>

    <div class="app-layout">
        <!-- Sidebar -->
        <aside class="sidebar glass">
            <a href="index.php" class="brand">
                <div class="brand-icon" style="background: transparent; box-shadow: none;">
                    <img src="../includes/assets/images/favicon.png" alt="Landsfy"
                        style="width: 40px; height: 40px; object-fit: contain;">
                </div>
                <div class="brand-text">LANDSFY</div>
            </a>

            <ul class="nav-menu">
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <a href="index.php"><i class="fa-solid fa-table-cells-large"></i> Dashboard</a>
                </li>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'my-listings.php' ? 'active' : ''; ?>">
                    <a href="my-listings.php"><i class="fa-solid fa-house-chimney"></i> My Listings</a>
                </li>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'wallets.php' ? 'active' : ''; ?>">
                    <a href="wallets.php"><i class="fa-solid fa-wallet"></i> My Wallet</a>
                </li>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'inquiries.php' ? 'active' : ''; ?>">
                    <a href="inquiries.php"><i class="fa-solid fa-comments"></i> Inquiries</a>
                </li>
                <li
                    class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'add-property.php' ? 'active' : ''; ?>">
                    <a href="add-property.php"><i class="fa-solid fa-circle-plus"></i> Add New</a>
                </li>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                    <a href="profile.php"><i class="fa-solid fa-circle-user"></i> Profile</a>
                </li>
            </ul>

            <div class="sidebar-bottom">
                <div class="agency-badge-card">
                    <div class="badge-icon"><i class="fa-solid fa-circle-check"></i></div>
                    <div class="badge-info">
                        <div class="badge-title">Official Agent</div>
                        <div class="badge-desc"><?php echo htmlspecialchars($agency_name); ?></div>
                    </div>
                </div>

                <div class="user-card" onclick="window.location.href='profile.php'">
                    <img src="<?php echo getImageUrl($_SESSION['avatar_url'] ?? ''); ?>"
                        alt="Agent" class="user-avatar"
                        onerror="handleImageError(this, 'user')">
                    <div class="user-info">
                        <div class="user-name"
                            style="text-overflow: ellipsis; overflow: hidden; white-space: nowrap; max-width: 130px;">
                            <?php echo htmlspecialchars($_SESSION['username']); ?></div>
                        <div class="user-role" style="text-transform: capitalize;">
                            <?php echo htmlspecialchars(str_replace('_', ' ', $_SESSION['role_name'])); ?></div>
                    </div>
                    <i class="fa-solid fa-chevron-right" style="color: var(--text-secondary)"></i>
                </div>

                <a href="../logout.php" class="nav-item-logout" style="margin-top: 16px; display: flex; align-items: center; gap: 12px; padding: 12px; color: #ef4444; text-decoration: none; font-size: 14px; font-weight: 600; transition: all 0.3s ease;">
                    <i class="fa-solid fa-right-from-bracket" style="font-size: 20px;"></i> Logout Account
                </a>
            </div>
        </aside>

        <script>
            function updateNotifBadge() {
                fetch('../includes/api/agent/get_notifications.php')
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            const unread = data.data.filter(n => n.is_read == 0).length;
                            const badge = document.getElementById('headerNotifBadge');
                            if (badge) {
                                if (unread > 0) {
                                    badge.innerText = unread > 9 ? '9+' : unread;
                                    badge.style.display = 'block';
                                } else {
                                    badge.style.display = 'none';
                                }
                            }
                        }
                    });
            }

            document.addEventListener('DOMContentLoaded', () => {
                // Find all bell icons and enhance them
                document.querySelectorAll('.fa-bell').forEach(icon => {
                    const parent = icon.closest('.icon-btn');
                    if (parent && !parent.classList.contains('bell-btn')) {
                        parent.classList.add('bell-btn');
                        parent.style.position = 'relative';
                        parent.onclick = () => window.location.href = 'notifications.php';
                        if (!parent.querySelector('.notif-badge')) {
                            parent.innerHTML += '<span class="notif-badge" id="headerNotifBadge">0</span>';
                        }
                    }
                });

                // Initial load
                updateNotifBadge();
                // Periodic check (every 30s)
                setInterval(updateNotifBadge, 30000);
            });
        </script>

<?php
require_once '../includes/auth_check.php';
require_once '../includes/database/db.php';

if ($_SESSION['role_name'] !== 'agency_owner' && $_SESSION['role_name'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch basic agency info
$stmt = $pdo->prepare("SELECT a.* FROM agencies a WHERE a.owner_id = ?");
$stmt->execute([$user_id]);
$agency = $stmt->fetch();

$agency_name = $agency ? $agency->name : 'My Agency';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Activity | Landsfy Agency</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <!-- External CSS -->
    <link rel="stylesheet" href="../includes/assets/css/style.css">
    <link rel="stylesheet" href="../includes/assets/css/agency-style.css">
    <style>
        .notifications-container {
            max-width: 900px;
            margin: 0 auto;
        }
        .activity-timeline {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .activity-item {
            display: flex;
            gap: 20px;
            padding: 20px;
            border-radius: 20px;
            transition: all 0.3s ease;
            position: relative;
        }
        .activity-item:hover {
            transform: translateX(8px);
            background: rgba(255,255,255,0.05);
        }
        .activity-item.unread::before {
            content: '';
            position: absolute;
            left: 0;
            top: 20px;
            bottom: 20px;
            width: 4px;
            background: var(--primary);
            border-radius: 0 4px 4px 0;
        }
        .activity-icon-wrapper {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 24px;
        }
        .activity-content {
            flex: 1;
        }
        .activity-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 4px;
        }
        .activity-title {
            font-weight: 700;
            color: var(--text-primary);
            font-size: 16px;
        }
        .activity-time {
            font-size: 12px;
            color: var(--text-secondary);
            font-weight: 500;
        }
        .activity-message {
            font-size: 14px;
            color: var(--text-secondary);
            line-height: 1.5;
        }
        .agent-meta {
            margin-top: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
        }
        .agent-avatar-small {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            object-fit: cover;
        }
        .empty-state {
            text-align: center;
            padding: 100px 40px;
            opacity: 0.5;
        }
        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Background Blurs -->
    <div class="bg-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
    </div>

    <div class="app-layout">
        <!-- Sidebar -->
        <aside class="sidebar glass">
            <a href="index.php" class="brand">
                <div class="brand-icon" style="background: transparent; box-shadow: none;">
                    <img src="../includes/assets/images/favicon.png" alt="Landsfy" style="width: 40px; height: 40px; object-fit: contain;">
                </div>
                <div class="brand-text">LANDSFY</div>
            </a>

            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="index.php"><i class="fa-solid fa-table-cells-large"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="agency-listings.php"><i class="fa-solid fa-house-chimney"></i> Agency Inventory</a>
                </li>
                <li class="nav-item">
                    <a href="my-agents.php"><i class="fa-solid fa-users"></i> Our Team</a>
                </li>
                <li class="nav-item active">
                    <a href="notifications.php">
                        <i class="fa-solid fa-bell"></i> Notifications 
                        <span class="nav-badge" id="notifBadge" style="display: none;"></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="settings.php"><i class="fa-solid fa-gear"></i> Agency Profile</a>
                </li>
            </ul>

            <div class="sidebar-bottom">
                <div class="agency-badge-card">
                    <div class="badge-icon"><i class="fa-solid fa-circle-check"></i></div>
                    <div class="badge-title">Verified Agency</div>
                    <div class="badge-desc">Premium Member</div>
                </div>

                <div class="user-card">
                    <img src="<?php echo !empty($_SESSION['avatar_url']) ? '../' . $_SESSION['avatar_url'] : 'https://i.pravatar.cc/150?img=32'; ?>" alt="Agency Admin" class="user-avatar">
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($agency_name); ?></div>
                        <div class="user-role">Agency Admin</div>
                    </div>
                </div>
                
                <a href="../logout.php" class="nav-item-logout" style="margin-top: 16px; display: flex; align-items: center; gap: 12px; padding: 12px; color: #ef4444; text-decoration: none; font-size: 14px; font-weight: 600; transition: all 0.3s ease;">
                    <i class="fa-solid fa-right-from-bracket" style="font-size: 20px;"></i> Logout
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="header glass">
                <div class="header-left">
                    <div class="page-title">Team Activity & Notifications</div>
                    <div class="breadcrumb"><?php echo htmlspecialchars($agency_name); ?> / Notifications</div>
                </div>
                
                <div class="header-actions">
                    <button class="btn-ghost" id="markAllReadBtn">
                        <i class="fa-solid fa-circle-check"></i> Mark all as read
                    </button>
                    <button class="icon-btn" id="themeToggle">
                        <i class="fa-solid fa-moon" id="themeIcon"></i>
                    </button>
                </div>
            </header>

            <div class="view-container">
                <div class="notifications-container">
                    <div class="activity-timeline" id="notificationsList">
                        <!-- Dynamic Content -->
                        <div style="text-align: center; padding: 100px;">
                            <i class="fa-solid fa-circle-notch fa-spin spinner" style="font-size: 40px; color: var(--primary);"></i>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- External JS -->
    <script src="../includes/assets/js/script.js"></script>
    <script src="../includes/assets/js/agency/notifications.js"></script>
</body>
</html>

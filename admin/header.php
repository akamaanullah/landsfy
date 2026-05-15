<?php
require_once '../includes/auth_check.php';
require_once '../includes/database/db.php';

// Enforce Super Admin only
if ($_SESSION['role_name'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Fetch Admin Profile
$admin_name = $_SESSION['username'] ?? "Administrator";

// Fetch Dynamic Theme Setting
$theme_stmt = $pdo->prepare("SELECT setting_value FROM user_settings WHERE user_id = ? AND setting_key = 'admin_theme'");
$theme_stmt->execute([$_SESSION['user_id']]);
$user_theme = $theme_stmt->fetchColumn() ?: 'light';
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $user_theme; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landsfy Admin | Super Command Center</title>
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
    <link rel="stylesheet" href="style.css">
    <script src="../includes/assets/js/utils.js"></script>
    
    <style>
        .nav-badge {
            background: var(--primary);
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 700;
            margin-left: auto;
        }
        .logout-nav-item a {
            color: #ff4757 !important;
        }
        .logout-nav-item i {
            color: #ff4757 !important;
        }
    </style>
</head>

<body>
    <!-- Background Blurs -->
    <div class="bg-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
    </div>

    <!-- Toast Notifications -->
    <div id="toast-container"></div>

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
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'all-properties.php' ? 'active' : ''; ?>">
                    <a href="all-properties.php"><i class="fa-solid fa-list-ul"></i> All Property</a>
                </li>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'approvals.php' ? 'active' : ''; ?>">
                    <?php
                    // Fetch pending count for badge
                    $p_stmt = $pdo->query("SELECT COUNT(*) FROM properties WHERE status = 'under_review'");
                    $pending_count = $p_stmt->fetchColumn();
                    ?>
                    <a href="approvals.php">
                        <i class="fa-solid fa-circle-check"></i> Approvals 
                        <?php if($pending_count > 0): ?>
                            <span class="nav-badge"><?php echo $pending_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'premium-requests.php' ? 'active' : ''; ?>">
                    <?php
                    $pr_stmt = $pdo->query("SELECT COUNT(*) FROM premium_requests WHERE status = 'pending'");
                    $pr_count = $pr_stmt->fetchColumn();
                    ?>
                    <a href="premium-requests.php">
                        <i class="fa-solid fa-crown"></i> Premium Req.
                        <?php if($pr_count > 0): ?>
                            <span class="nav-badge" style="background: var(--primary);"><?php echo $pr_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'user-management.php' ? 'active' : ''; ?>">
                    <a href="user-management.php"><i class="fa-solid fa-users"></i> Users</a>
                </li>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'agencies.php' ? 'active' : ''; ?>">
                    <a href="agencies.php"><i class="fa-solid fa-building"></i> Agencies</a>
                </li>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'property-config.php' ? 'active' : ''; ?>">
                    <a href="property-config.php"><i class="fa-solid fa-gear"></i> Config</a>
                </li>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'locations.php' ? 'active' : ''; ?>">
                    <a href="locations.php"><i class="fa-solid fa-location-dot"></i> Locations</a>
                </li>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                    <a href="settings.php"><i class="fa-solid fa-sliders"></i> Settings</a>
                </li>
                <li class="nav-item logout-nav-item">
                    <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                </li>
            </ul>

            <div class="user-card" onclick="window.location.href='profile.php'">
                <img src="<?php echo !empty($_SESSION['avatar_url']) ? '../' . htmlspecialchars($_SESSION['avatar_url']) : 'https://i.pravatar.cc/150?img=11'; ?>"
                    alt="admin" class="user-avatar"
                    onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($admin_name); ?>&background=6c5dd3&color=fff&bold=true'">
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($admin_name); ?></div>
                    <div class="user-role">Main Admin</div>
                </div>
                <i class="fa-solid fa-chevron-right" style="color: var(--text-secondary)"></i>
            </div>
        </aside>

        <!-- Main Content Area is handled by individual pages -->
    <script>
        // Immediate theme application to prevent flash
        (function() {
            const currentTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', currentTheme);
        })();
    </script>
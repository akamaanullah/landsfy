<?php
require_once dirname(__DIR__) . '/includes/auth_check.php';
require_once dirname(__DIR__) . '/includes/database/db.php';
require_once dirname(__DIR__) . '/includes/helpers/user_meta.php';
require_once dirname(__DIR__) . '/includes/helpers/image_helper.php';

// Enforce Buyer role
if ($_SESSION['role_name'] !== 'buyer' && $_SESSION['role_name'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'] ?? 'Buyer';
$username = $_SESSION['username'] ?? 'User';
$avatar_url = !empty($_SESSION['avatar_url']) ? '../' . $_SESSION['avatar_url'] : 'https://i.pravatar.cc/150?img=5';

// Fetch Theme Setting
$theme_stmt = $pdo->prepare("SELECT setting_value FROM user_settings WHERE user_id = ? AND setting_key = 'buyer_theme'");
$theme_stmt->execute([$user_id]);
$user_theme = $theme_stmt->fetchColumn() ?: 'light';

// Dynamic Badge Title (Example: House Hunter based on saved properties)
$save_count_stmt = $pdo->prepare("SELECT COUNT(*) FROM saved_properties WHERE user_id = ?");
$save_count_stmt->execute([$user_id]);
$save_count = $save_count_stmt->fetchColumn();

$badge_title = "Active Explorer";
if ($save_count > 5) $badge_title = "House Hunter";
if ($save_count > 15) $badge_title = "Property Pro";
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $user_theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Buyer Dashboard'; ?> | Landsfy</title>
    <link rel="icon" type="image/png" href="../includes/assets/images/favicon.png">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-minimal/minimal.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../includes/assets/js/utils.js"></script>
    
    <!-- External CSS -->
    <link rel="stylesheet" href="../includes/assets/css/style.css">
    <link rel="stylesheet" href="../includes/assets/css/agency-style.css">
    <style>
        .nav-item a { text-decoration: none; display: flex; align-items: center; gap: 12px; }
        .logout-item a { color: var(--danger) !important; }
        .logout-item i { color: var(--danger) !important; }
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
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <a href="index.php"><i class="fa-solid fa-table-cells-large"></i> Dashboard</a>
                </li>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'saved-properties.php' ? 'active' : ''; ?>">
                    <a href="saved-properties.php"><i class="fa-regular fa-heart"></i> Saved Properties</a>
                </li>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'inquiries.php' ? 'active' : ''; ?>">
                    <a href="inquiries.php"><i class="fa-solid fa-comments"></i> My Inquiries</a>
                </li>
                <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                    <a href="profile.php"><i class="fa-solid fa-circle-user"></i> Profile</a>
                </li>
                <li class="nav-item logout-item" style="margin-top: auto;">
                    <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                </li>
            </ul>

            <div class="sidebar-bottom">
                <div class="agency-badge-card">
                    <div class="badge-icon"><i class="fa-solid fa-house-chimney"></i></div>
                    <div class="badge-info">
                        <div class="badge-title"><?php echo $badge_title; ?></div>
                        <div class="badge-desc"><?php echo $save_count; ?> saved listings</div>
                    </div>
                </div>

                <div class="user-card" onclick="window.location.href='profile.php'">
                    <img src="<?php echo getImageUrl($_SESSION['avatar_url'] ?? ''); ?>" alt="Buyer" class="user-avatar" onerror="handleImageError(this, 'user')">
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($username); ?></div>
                        <div class="user-role">Home Buyer</div>
                    </div>
                    <i class="fa-solid fa-chevron-right" style="color: var(--text-secondary)"></i>
                </div>
            </div>
        </aside>

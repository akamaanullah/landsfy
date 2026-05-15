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
$agency_id = $agency ? $agency->id : 0;
$is_verified = $agency ? $agency->is_verified : false;

// Fetch Dynamic Stats
$stats_stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN status = 'under_review' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'sold' OR status = 'inactive' THEN 1 ELSE 0 END) as sold
    FROM properties 
    WHERE agency_id = ? AND status != 'deleted'
");
$stats_stmt->execute([$agency_id]);
$stats = $stats_stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agency Inventory | Landsfy</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <!-- External CSS -->
    <link rel="stylesheet" href="../includes/assets/css/style.css">
    <link rel="stylesheet" href="../includes/assets/css/agency-style.css">
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
                <li class="nav-item active">
                    <a href="agency-listings.php"><i class="fa-solid fa-house-chimney"></i> Agency Inventory</a>
                </li>
                <li class="nav-item">
                    <a href="my-agents.php"><i class="fa-solid fa-users"></i> Our Team</a>
                </li>
                <li class="nav-item">
                    <a href="settings.php"><i class="fa-solid fa-gear"></i> Agency Profile</a>
                </li>
                <li class="nav-item">
                    <a href="notifications.php">
                        <i class="fa-solid fa-bell"></i> Notifications
                        <span class="nav-badge" style="display: none;"></span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-bottom">
                <div class="agency-badge-card <?php echo $is_verified ? '' : 'pending'; ?>" style="<?php echo !$is_verified ? 'background: rgba(245, 158, 11, 0.1); border-color: rgba(245, 158, 11, 0.2);' : ''; ?>">
                    <div class="badge-icon" style="<?php echo !$is_verified ? 'background: var(--warning); color: white;' : ''; ?>">
                        <i class="fa-solid <?php echo $is_verified ? 'fa-seal-check' : 'fa-clock'; ?>"></i>
                    </div>
                    <div class="badge-info">
                        <div class="badge-title" style="<?php echo !$is_verified ? 'color: var(--text-primary);' : ''; ?>">
                            <?php echo $is_verified ? 'Verified Agency' : 'Pending Verification'; ?>
                        </div>
                        <div class="badge-desc"><?php echo $is_verified ? 'Premium Member' : 'Under Review'; ?></div>
                    </div>
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
                    <div class="page-title">Agency Inventory</div>
                    <div class="breadcrumb"><?php echo htmlspecialchars($agency_name); ?> / Property Management</div>
                </div>
                
                <div class="header-actions">
                    <button class="icon-btn" id="themeToggle" title="Toggle Light/Dark Mode">
                        <i class="fa-solid fa-moon" id="themeIcon"></i>
                    </button>
                    <a href="add-property.php" class="btn-primary">
                        <i class="fa-solid fa-plus"></i> Add Property
                    </a>
                </div>
            </header>

            <div class="view-container">
                <?php if (!$is_verified): ?>
                    <div class="verification-alert glass" style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.2); border-radius: var(--border-radius-md); padding: 20px; margin-bottom: 24px; display: flex; align-items: center; gap: 20px;">
                        <div class="alert-icon" style="width: 48px; height: 48px; background: var(--warning); color: white; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0;">
                            <i class="fa-solid fa-warning-octagon"></i>
                        </div>
                        <div class="alert-content" style="flex: 1;">
                            <h4 style="font-size: 16px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px;">Agency Verification Pending</h4>
                            <p style="font-size: 13px; color: var(--text-secondary); line-height: 1.5;">
                                Your agency isn't verified yet! Please <strong>complete your profile</strong> and <strong>add necessary documents</strong> to get verified. 
                                <span style="display: block; margin-top: 4px; font-weight: 600; color: var(--warning);">Note: Your agency will not be verified until all documents are complete.</span>
                            </p>
                        </div>
                        <a href="settings.php" class="btn-primary" style="background: var(--warning); box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2); white-space: nowrap;">
                            Complete Profile
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card glass active" data-filter="">
                        <div class="stat-icon" style="background: rgba(107,0,182,0.1); color: var(--primary);">
                            <i class="fa-solid fa-buildings"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Total Listings</span>
                            <h2 class="stat-value"><?php echo number_format($stats->total); ?></h2>
                        </div>
                    </div>
                    <div class="stat-card glass" data-filter="active">
                        <div class="stat-icon" style="background: rgba(16,185,129,0.1); color: var(--success);">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Published</span>
                            <h2 class="stat-value"><?php echo number_format($stats->active); ?></h2>
                        </div>
                    </div>
                    <div class="stat-card glass" data-filter="under_review">
                        <div class="stat-icon" style="background: rgba(245,158,11,0.1); color: var(--warning);">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Under Review</span>
                            <h2 class="stat-value"><?php echo number_format($stats->pending); ?></h2>
                        </div>
                    </div>
                    <div class="stat-card glass" data-filter="sold">
                        <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: var(--danger);">
                            <i class="fa-solid fa-tag"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Sold/Archived</span>
                            <h2 class="stat-value"><?php echo number_format($stats->sold); ?></h2>
                        </div>
                    </div>
                </div>

                <!-- Filter Bar -->
                <div class="management-bar glass all-props-bar">
                    <div class="search-box" style="width: 100%;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" placeholder="Search by property title, assigned agent, or location..." id="propertySearch">
                    </div>

                    </div>
                </div>

                <!-- Listings Grid -->
                <div class="listings-grid" id="listingsGrid">
                    <!-- Dynamic Content -->
                </div>
            </div>
        </main>
    </div>

    <!-- External JS -->
    <script src="../includes/assets/js/script.js"></script>
    <script src="../includes/assets/js/agency/listings.js"></script>
    <script src="../includes/assets/js/agency/notif-checker.js"></script>
</body>
</html>
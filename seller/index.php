<?php
require_once '../includes/auth_check.php';
require_once '../includes/database/db.php';
require_once '../includes/helpers/user_meta.php';

// Ensure user is a Seller (Role ID: 4)
if ($_SESSION['role_name'] != 'seller' && $_SESSION['role_name'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$is_verified = is_user_verified($user_id);
$is_premium = is_user_premium($user_id);
$role_title = $is_premium ? 'Elite Seller' : 'Individual Seller';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard | Landsfy</title>
    <link rel="icon" type="image/png" href="../includes/assets/images/favicon.png">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
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
                <li class="nav-item active">
                    <a href="index.php"><i class="fa-solid fa-table-cells-large"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="my-listings.php"><i class="fa-solid fa-house-chimney"></i> My Listings</a>
                </li>
                <li class="nav-item">
                    <a href="leads.php"><i class="fa-solid fa-users"></i> Buyer Leads</a>
                </li>
                <li class="nav-item">
                    <a href="add-listing.php"><i class="fa-solid fa-circle-plus"></i> Post New</a>
                </li>
                <li class="nav-item">
                    <a href="profile.php"><i class="fa-solid fa-circle-user"></i> Profile</a>
                </li>
                <li class="nav-item logout-nav-item" style="margin-top: auto;">
                    <a href="../logout.php" style="color: #ff4757;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                </li>
            </ul>

            <div class="sidebar-bottom">
                <?php if ($is_verified): ?>
                <div class="agency-badge-card" style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.2);">
                    <div class="badge-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--success);"><i class="fa-solid fa-circle-check"></i></div>
                    <div class="badge-info">
                        <div class="badge-title">Verified Seller</div>
                        <div class="badge-desc">Official Partner</div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="user-card" onclick="window.location.href='profile.php'">
                    <img src="<?php echo !empty($_SESSION['avatar_url']) ? '../' . htmlspecialchars($_SESSION['avatar_url']) : 'https://i.pravatar.cc/150?img=12'; ?>" alt="Seller" class="user-avatar">
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                        <div class="user-role"><?php echo $role_title; ?></div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="header glass">
                <div class="header-left">
                    <div class="page-title">Seller Overview</div>
                    <div class="breadcrumb">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>! 👋</div>
                </div>
                
                <div class="header-actions">
                    <form action="my-listings.php" method="GET" class="search-bar glass">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" name="search" placeholder="Search my listings..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                        <button type="submit" style="display: none;"></button>
                    </form>
                    <button class="icon-btn" id="themeToggle">
                        <i class="fa-solid fa-moon" id="themeIcon"></i>
                    </button>
                    <div class="notification-wrapper" style="position: relative;">
                        <button class="icon-btn" id="notifBell">
                            <i class="fa-solid fa-bell"></i>
                            <span class="pulse-dot" style="position: absolute; top: 8px; right: 8px; width: 8px; height: 8px; background: var(--danger); border-radius: 50%; border: 2px solid var(--glass-bg); display: none;"></span>
                        </button>
                        <div class="dropdown-menu glass" id="notifDropdown" style="display: none; position: absolute; top: 100%; right: 0; margin-top: 15px; width: 320px; padding: 20px; z-index: 1000; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                <h4 style="font-size: 16px; font-weight: 800; margin: 0;">Notifications</h4>
                                <span style="font-size: 11px; font-weight: 700; color: var(--primary); cursor: pointer;">Mark all as read</span>
                            </div>
                            <div style="text-align: center; padding: 32px 0;">
                                <div style="width: 48px; height: 48px; background: rgba(107, 0, 182, 0.1); color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                                    <i class="fa-solid fa-bell-slash" style="font-size: 24px;"></i>
                                </div>
                                <p style="font-size: 13px; color: var(--text-secondary); margin: 0;">You're all caught up!</p>
                                <p style="font-size: 11px; opacity: 0.5; margin-top: 4px;">No new notifications at the moment.</p>
                            </div>
                        </div>
                    </div>
                    <a href="add-listing.php" class="btn-primary">
                        <i class="fa-solid fa-plus"></i> New Property
                    </a>
                </div>
            </header>

            <div class="view-container">
                <!-- Seller Stats Grid -->
                <div class="stats-summary-grid">
                    <div class="stat-card glass" style="background: linear-gradient(135deg, rgba(107, 0, 182, 0.05), rgba(107, 0, 182, 0.1));">
                        <div class="stat-icon" style="background: var(--primary); color: white; box-shadow: 0 8px 16px rgba(107,0,182,0.2);">
                            <i class="fa-solid fa-house-chimney"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Listed Properties</span>
                            <span class="stat-value">...</span>
                        </div>
                    </div>
                    <div class="stat-card glass" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(59, 130, 246, 0.1));">
                        <div class="stat-icon" style="background: var(--info); color: white; box-shadow: 0 8px 16px rgba(59,130,246,0.2);">
                            <i class="fa-solid fa-eye"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Total Views</span>
                            <span class="stat-value">...</span>
                        </div>
                    </div>
                    <div class="stat-card glass" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.05), rgba(16, 185, 129, 0.1));">
                        <div class="stat-icon" style="background: var(--success); color: white; box-shadow: 0 8px 16px rgba(16,185,129,0.2);">
                            <i class="fa-brands fa-whatsapp"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Buyer Inquiries</span>
                            <span class="stat-value">...</span>
                        </div>
                    </div>
                    <div class="stat-card glass" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.05), rgba(245, 158, 11, 0.1));">
                        <div class="stat-icon" style="background: var(--warning); color: white; box-shadow: 0 8px 16px rgba(245,158,11,0.2);">
                            <i class="fa-solid fa-handshake"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Closed Deals</span>
                            <span class="stat-value">...</span>
                        </div>
                    </div>
                </div>

                <div class="adb-grid" style="display: grid; grid-template-columns: 1fr 340px; gap: 24px; margin-top: 24px;">
                    <!-- Recent Buyer Leads -->
                    <div class="card-panel glass" style="padding: 24px;">
                        <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                            <h3 class="panel-title" style="font-size: 20px; font-weight: 800;">Recent Buyer Leads</h3>
                            <a href="leads.php" class="view-all" style="color: var(--primary); font-size: 14px; font-weight: 700; text-decoration: none; display: flex; align-items: center; gap: 6px;">
                                View All <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table class="data-table" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th style="padding-left: 0;">Buyer Name</th>
                                        <th>Property</th>
                                        <th style="text-align: center;">Inquiry Date</th>
                                        <th style="text-align: center;">Channel</th>
                                        <th style="text-align: center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="recentLeadsBody">
                                     <tr><td colspan="5" style="text-align: center; padding: 40px; color: var(--text-secondary);">Loading inquiries...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Performance Chart -->
                    <div class="card-panel glass" style="padding: 24px;">
                        <h3 class="panel-title" style="font-size: 18px; font-weight: 800; margin-bottom: 24px;">Views Share</h3>
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 32px;">
                            <div class="doughnut" id="viewsShareChart" style="width: 180px; height: 180px; border-radius: 50%; background: conic-gradient(var(--glass-border) 0% 100%); position: relative; box-shadow: 0 12px 30px rgba(107,0,182,0.15);">
                                <div style="position: absolute; inset: 15px; background: var(--glass-bg); backdrop-filter: blur(20px); border-radius: 50%; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                    <span id="totalViewsChartText" style="font-size: 28px; font-weight: 800;">...</span>
                                    <span style="font-size: 11px; color: var(--text-secondary); text-transform: uppercase;">Total Views</span>
                                </div>
                            </div>
                            <div id="viewsShareLegend" style="width: 100%; display: grid; gap: 12px;">
                                <div style="text-align: center; font-size: 12px; color: var(--text-secondary);">Calculating stats...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- External JS -->
    <script src="../includes/assets/js/utils.js"></script>
    <script src="../includes/assets/js/script.js"></script>
    <script src="../includes/assets/js/seller/dashboard.js"></script>
    <script src="../includes/assets/js/seller/notif-checker.js"></script>
</body>
</html>


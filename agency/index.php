<?php
require_once '../includes/auth_check.php';
require_once '../includes/database/db.php';

if ($_SESSION['role_name'] !== 'agency_owner' && $_SESSION['role_name'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Agency Admin';

// Fetch basic agency info
$stmt = $pdo->prepare("SELECT a.* FROM agencies a WHERE a.owner_id = ?");
$stmt->execute([$user_id]);
$agency = $stmt->fetch();

$agency_name = $agency ? $agency->name : 'My Agency';
$is_verified = $agency ? $agency->is_verified : false;
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agency Dashboard | Landsfy</title>
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
                        <i class="fa-solid <?php echo $is_verified ? 'fa-circle-check' : 'fa-clock'; ?>"></i>
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
                    <div class="page-title">Agency Overview</div>
                    <div class="breadcrumb">Welcome back, <?php echo htmlspecialchars($agency_name); ?> Admin</div>
                </div>
                
                <div class="header-actions">
                    <button class="icon-btn" id="themeToggle">
                        <i class="fa-solid fa-moon" id="themeIcon"></i>
                    </button>
                    <a href="add-property.php" class="btn-primary">
                        <i class="fa-solid fa-plus"></i> New Listing
                    </a>
                </div>
            </header>

            <div class="view-container">
                <?php if (!$is_verified): ?>
                    <div class="verification-alert glass" style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.2); border-radius: var(--border-radius-md); padding: 20px; margin-bottom: 24px; display: flex; align-items: center; gap: 20px;">
                        <div class="alert-icon" style="width: 48px; height: 48px; background: var(--warning); color: white; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0;">
                            <i class="fa-solid fa-circle-exclamation"></i>
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

                <!-- Agency Stats Grid -->
                <div class="stats-summary-grid">
                    <div class="stat-card glass">
                        <div class="stat-icon" style="background: rgba(107, 0, 182, 0.1); color: var(--primary);">
                            <i class="fa-solid fa-house-chimney"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Total Listings</span>
                            <span class="stat-value">...</span>
                        </div>
                    </div>
                    <div class="stat-card glass">
                        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--success);">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Active Agents</span>
                            <span class="stat-value">...</span>
                        </div>
                    </div>
                    <div class="stat-card glass">
                        <div class="stat-icon" style="background: rgba(107, 0, 182, 0.1); color: var(--primary);">
                            <i class="fa-solid fa-comments"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Monthly Leads</span>
                            <span class="stat-value">...</span>
                        </div>
                    </div>
                    <div class="stat-card glass">
                        <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: var(--warning);">
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Customer Rating</span>
                            <span class="stat-value">...</span>
                        </div>
                    </div>
                </div>

                <div class="adb-grid">
                    <!-- Agent Performance -->
                    <div class="card-panel glass">
                        <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                            <h3 class="panel-title" style="font-size: 18px; font-weight: 700;">Top Performing Agents</h3>
                            <a href="my-agents.php" class="view-all" style="color: var(--primary); font-size: 13px; font-weight: 600; text-decoration: none;">View Team</a>
                        </div>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Agent Name</th>
                                        <th>Listings</th>
                                        <th>Leads</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="4" style="text-align: center; padding: 40px; opacity: 0.5;">
                                            <i class="fa-solid fa-circle-notch fa-spin" style="font-size: 24px;"></i>
                                            <p style="margin-top: 10px; font-size: 13px;">Fetching team data...</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="card-panel glass">
                        <div class="panel-header" style="margin-bottom: 20px;">
                            <h3 class="panel-title" style="font-size: 18px; font-weight: 700;">Inquiry Timeline</h3>
                        </div>
                        <div class="adb-timeline">
                            <div style="text-align: center; padding: 40px; opacity: 0.5;">
                                <i class="fa-solid fa-circle-notch fa-spin spinner" style="font-size: 24px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- External JS -->
    <script src="../includes/assets/js/script.js"></script>
    <script src="../includes/assets/js/agency/dashboard.js"></script>
    <script src="../includes/assets/js/agency/notif-checker.js"></script>
</body>
</html>

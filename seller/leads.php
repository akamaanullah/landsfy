<?php
require_once '../includes/auth_check.php';
require_once '../includes/database/db.php';
require_once '../includes/helpers/user_meta.php';

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
    <title>Buyer Leads | Landsfy Seller</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- External CSS -->
    <link rel="stylesheet" href="../includes/assets/css/style.css">
    <link rel="stylesheet" href="../includes/assets/css/agency-style.css">
    <style>
        .data-table thead th {
            background: rgba(107, 0, 182, 0.03);
            color: var(--primary);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1px;
            padding: 16px 20px;
            border-bottom: 2px solid rgba(107, 0, 182, 0.1);
        }
        .data-table tbody tr {
            border-bottom: 1px solid var(--glass-border);
            transition: all 0.3s ease;
        }
        .data-table tbody tr:hover {
            background: rgba(107, 0, 182, 0.02);
            transform: scale(1.002);
        }
        .data-table td {
            padding: 20px;
        }
        .status-info {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }
        .search-bar.glass {
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(107, 0, 182, 0.15);
            width: 280px; /* Reduced width to prevent overflow */
        }
        .search-bar.glass input {
            color: var(--text-primary);
        }
        .search-bar.glass i {
            color: var(--primary);
        }
        [data-theme="dark"] .search-bar.glass {
            background: rgba(255, 255, 255, 0.05);
        }
        .btn-primary.dropdown-trigger {
            background: var(--primary-gradient) !important;
            color: white !important;
            border: none !important;
            box-shadow: 0 4px 15px rgba(107, 0, 182, 0.2) !important;
        }
        .header-left {
            flex: 1;
            min-width: 250px;
        }
        .page-title {
            white-space: nowrap;
            font-size: 24px;
            font-weight: 800;
        }
        .breadcrumb {
            white-space: nowrap;
            font-size: 13px;
            color: var(--text-secondary);
            margin-top: 4px;
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
                    <img src="../includes/assets/images/favicon.png" alt="Landsfy"
                        style="width: 40px; height: 40px; object-fit: contain;">
                </div>
                <div class="brand-text">LANDSFY</div>
            </a>

            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="index.php"><i class="fa-solid fa-table-cells-large"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="my-listings.php"><i class="fa-solid fa-house-chimney"></i> My Listings</a>
                </li>
                <li class="nav-item active">
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
                    <div class="agency-badge-card"
                        style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.2);">
                        <div class="badge-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--success);"><i
                                class="fa-solid fa-circle-check"></i></div>
                        <div class="badge-info">
                            <div class="badge-title">Verified Seller</div>
                            <div class="badge-desc">Official Partner</div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="user-card" onclick="window.location.href='profile.php'">
                    <img src="<?php echo !empty($_SESSION['avatar_url']) ? '../' . htmlspecialchars($_SESSION['avatar_url']) : 'https://i.pravatar.cc/150?img=12'; ?>"
                        alt="Seller" class="user-avatar">
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
                    <div class="page-title">Buyer Inquiries</div>
                    <div class="breadcrumb">Manage and respond to buyer leads</div>
                </div>

                <div class="header-actions">
                    <div class="search-bar glass">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" placeholder="Search inquiries..." id="leadSearch">
                    </div>
                    <button class="icon-btn" id="themeToggle">
                        <i class="fa-solid fa-moon" id="themeIcon"></i>
                    </button>
                    <div class="notification-wrapper" style="position: relative;">
                        <button class="icon-btn" id="notifBell">
                            <i class="fa-solid fa-bell"></i>
                            <span class="pulse-dot"
                                style="position: absolute; top: 8px; right: 8px; width: 8px; height: 8px; background: var(--danger); border-radius: 50%; border: 2px solid var(--glass-bg); display: none;"></span>
                        </button>
                        <div class="dropdown-menu glass" id="notifDropdown"
                            style="display: none; position: absolute; top: 100%; right: 0; margin-top: 15px; width: 320px; padding: 20px; z-index: 1000; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
                            <div
                                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                <h4 style="font-size: 16px; font-weight: 800; margin: 0;">Notifications</h4>
                                <span
                                    style="font-size: 11px; font-weight: 700; color: var(--primary); cursor: pointer;">Mark
                                    all as read</span>
                            </div>
                            <div style="text-align: center; padding: 32px 0;">
                                <div
                                    style="width: 48px; height: 48px; background: rgba(107, 0, 182, 0.1); color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                                    <i class="fa-solid fa-bell-slash" style="font-size: 24px;"></i>
                                </div>
                                <p style="font-size: 13px; color: var(--text-secondary); margin: 0;">You're all caught
                                    up!</p>
                                <p style="font-size: 11px; opacity: 0.5; margin-top: 4px;">No new notifications at the
                                    moment.</p>
                            </div>
                        </div>
                    </div>
                    <div class="custom-dropdown" id="leadFilter" style="position: relative;">
                        <button class="btn-primary dropdown-trigger" style="gap: 8px;">
                            <i class="fa-solid fa-filter"></i> Filters <i class="fa-solid fa-caret-down"></i>
                        </button>
                        <div class="dropdown-menu glass" style="display: none; position: absolute; top: 100%; right: 0; margin-top: 10px; min-width: 200px; padding: 12px; border-radius: 16px; z-index: 1000; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                            <div class="dropdown-item active" data-value="all" style="padding: 10px 16px; border-radius: 8px; cursor: pointer; transition: all 0.2s;">All Inquiries</div>
                            <div class="dropdown-item" data-value="active" style="padding: 10px 16px; border-radius: 8px; cursor: pointer; transition: all 0.2s;">Recent Only</div>
                            <div class="dropdown-item" data-value="contacted" style="padding: 10px 16px; border-radius: 8px; cursor: pointer; transition: all 0.2s;">Already Contacted</div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="view-container">
                <div class="card-panel glass" style="margin-top: 0;">
                    <div class="table-responsive">
                        <table class="data-table" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Buyer Details</th>
                                    <th>Property Interest</th>
                                    <th style="text-align: center;">Received At</th>
                                    <th style="text-align: center;">Inquiry Status</th>
                                    <th style="text-align: center;">Quick Actions</th>
                                </tr>
                            </thead>
                            <tbody id="leadsTableBody">
                                <tr>
                                    <td colspan="5"
                                        style="text-align: center; padding: 60px; color: var(--text-secondary);">
                                        <i class="fa-solid fa-circle-notch fa-spin spinner"
                                            style="font-size: 32px; margin-bottom: 16px;"></i>
                                        <p>Fetching your inquiries...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- External JS -->
    <script src="../includes/assets/js/utils.js"></script>
    <script src="../includes/assets/js/script.js"></script>
    <script src="../includes/assets/js/seller/leads.js"></script>
    <script src="../includes/assets/js/seller/notif-checker.js"></script>
</body>

</html>

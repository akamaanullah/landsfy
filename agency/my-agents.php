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
$agency_id = $agency->id;

// Fetch Team Stats
$stats_stmt = $pdo->prepare("
    SELECT 
        COUNT(a.id) as total_members,
        SUM(CASE WHEN u.status = 'active' THEN 1 ELSE 0 END) as active_agents,
        (
            SELECT COUNT(*) FROM (
                SELECT a2.id 
                FROM agents a2 
                JOIN users u2 ON a2.user_id = u2.id 
                WHERE a2.agency_id = ? 
                AND (SELECT COUNT(*) FROM properties WHERE author_id = u2.id AND status != 'deleted') >= 5
            ) as performers
        ) as top_performers
    FROM agents a
    JOIN users u ON a.user_id = u.id
    WHERE a.agency_id = ?
");
$stats_stmt->execute([$agency_id, $agency_id]);
$stats = $stats_stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Team | Landsfy Agency</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
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
                <li class="nav-item">
                    <a href="agency-listings.php"><i class="fa-solid fa-house-chimney"></i> Agency Inventory</a>
                </li>
                <li class="nav-item active">
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

        <!-- Main Content Area -->
        <main class="main-content">
            
            <!-- Top Header -->
            <header class="header glass">
                <div class="header-left">
                    <div class="page-title">Agency Team Management</div>
                    <div class="breadcrumb"><?php echo htmlspecialchars($agency_name); ?> / Our Agents</div>
                </div>
                
                <div class="header-actions">
                    <div class="search-bar">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" placeholder="Search by agent name or ID...">
                    </div>
                    <button class="icon-btn" id="themeToggle" title="Toggle Light/Dark Mode">
                        <i class="fa-solid fa-moon" id="themeIcon"></i>
                    </button>
                    <button class="btn-primary" id="addTeamMemberBtn">
                        <i class="fa-solid fa-user-plus-bold"></i> Add Team Member
                    </button>
                </div>
            </header>

            <div class="view-container">
                <!-- Team Stats Grid -->
                <div class="stats-summary-grid">
                    <div class="stat-card glass">
                        <div class="stat-icon" style="background: rgba(107, 0, 182, 0.1); color: var(--primary);">
                            <i class="fa-solid fa-users-three"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Total Members</span>
                            <span class="stat-value"><?php echo number_format($stats->total_members); ?></span>
                        </div>
                    </div>
                    <div class="stat-card glass">
                        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--success);">
                            <i class="fa-solid fa-user-check"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Active Agents</span>
                            <span class="stat-value"><?php echo number_format($stats->active_agents); ?></span>
                        </div>
                    </div>
                    <div class="stat-card glass">
                        <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: var(--warning);">
                            <i class="fa-solid fa-medal"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Top Performers</span>
                            <span class="stat-value"><?php echo number_format($stats->top_performers); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Management & Filter Bar -->
                <div class="management-bar glass" style="margin-top: 24px; padding: 0; border: none; background: transparent;">
                    <!-- Only Search and Add button in header now -->
                </div>

                <!-- Agents Grid -->
                <div class="agents-grid" id="agentsGrid" style="margin-top: 32px;">
                    <!-- Dynamic Content -->
                </div>
            </div>
        </main>
    </div>

    <!-- External JS -->
    <script src="../includes/assets/js/script.js"></script>
    <script src="../includes/assets/js/agency/team.js"></script>
    <script src="../includes/assets/js/agency/notif-checker.js"></script>
</body>
</html>

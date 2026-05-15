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
    <title>Profile Settings | Landsfy Agency</title>
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
        <aside class="sidebar glass" style="z-index: 100;">
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
            <header class="header glass" style="margin-bottom: 24px;">
                <div class="header-left">
                    <div class="page-title">Agency Profile Settings</div>
                    <div class="breadcrumb"><?php echo htmlspecialchars($agency_name); ?> / Settings</div>
                </div>
                
                <div class="header-actions">
                    <button class="icon-btn" id="themeToggle" title="Toggle Light/Dark Mode">
                        <i class="fa-solid fa-moon" id="themeIcon"></i>
                    </button>
                    <button class="btn-primary" id="saveSettingsBtn">
                        <i class="fa-solid fa-floppy-disk"></i> Save Changes
                    </button>
                </div>
            </header>

            <form id="agencySettingsForm" class="view-container">
                <!-- Profile Header Card -->
                <div class="card-panel glass" style="padding: 0; overflow: hidden; margin-bottom: 32px; border-radius: 24px;">
                    <div class="settings-banner" id="bannerPreview" style="background: <?php echo $agency && $agency->banner_url ? "url('../".$agency->banner_url."')" : 'linear-gradient(135deg, #6b00b6 0%, #4c0082 100%)'; ?>; background-size: cover; background-position: center; height: 200px; position: relative;">
                        <input type="file" name="banner" id="bannerInput" style="display: none;" accept="image/*">
                        <button type="button" class="icon-btn" onclick="document.getElementById('bannerInput').click()" style="position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white;">
                            <i class="fa-solid fa-camera"></i>
                        </button>
                    </div>
                    <div style="padding: 0 40px 40px;">
                        <div style="display: flex; align-items: center; gap: 32px; margin-top: -60px;">
                            <div class="settings-logo-wrapper" style="position: relative;">
                                <input type="file" name="logo" id="logoInput" style="display: none;" accept="image/*">
                                <div id="logoPreview" style="width: 140px; height: 140px; background: var(--surface-bg); border-radius: 28px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); display: flex; align-items: center; justify-content: center; border: 6px solid var(--glass-bg); overflow: hidden;">
                                    <?php if($agency && $agency->logo_url): ?>
                                        <img src="../<?php echo $agency->logo_url; ?>" style="width: 100%; height: 100%; object-fit: contain;">
                                    <?php else: ?>
                                        <div style="background: var(--primary); color: white; width: 90px; height: 90px; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 44px; box-shadow: inset 0 0 20px rgba(0,0,0,0.1);"><?php echo strtoupper(substr($agency_name, 0, 1)); ?></div>
                                    <?php endif; ?>
                                </div>
                                <button type="button" class="edit-pencil-btn" onclick="document.getElementById('logoInput').click()">
                                    <i class="fa-solid fa-pencil"></i>
                                </button>
                            </div>
                            <div style="padding-top: 40px;">
                                <h1 style="font-size: 32px; font-weight: 800; color: var(--text-primary); margin-bottom: 6px; letter-spacing: -1px;"><?php echo htmlspecialchars($agency_name); ?></h1>
                                <div style="display: flex; gap: 16px; align-items: center;">
                                    <span class="badge-tag status-active" style="padding: 6px 14px; font-size: 12px; font-weight: 700; background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);"><i class="fa-solid fa-circle-check"></i> Verified Agency</span>
                                    <span style="font-size: 14px; color: var(--text-secondary); font-weight: 500;"><i class="fa-solid fa-location-dot" style="color: var(--primary);"></i> <?php echo htmlspecialchars($agency && $agency->address ? explode(',', $agency->address)[0] : 'Pakistan'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-layout-grid">
                    <!-- Nav Tabs -->
                    <div class="form-sidebar-col">
                        <div class="card-panel glass settings-nav-container" style="padding: 12px; position: sticky; top: 124px;">
                            <div class="settings-nav-item active" data-target="general-pane">
                                <i class="fa-solid fa-user-focus"></i> Agency Information
                            </div>
                            <div class="settings-nav-item" data-target="contact-pane">
                                <i class="fa-solid fa-location-dot-line"></i> Contact & Location
                            </div>
                            <div class="settings-nav-item" data-target="security-pane">
                                <i class="fa-solid fa-key"></i> Security
                            </div>
                        </div>
                    </div>

                    <!-- Main Form Section -->
                    <div class="form-main-col">
                        <!-- General Pane -->
                        <div class="settings-pane active" id="general-pane">
                            <div class="card-panel glass form-section">
                                <div class="section-badge"><i class="fa-solid fa-user-focus"></i></div>
                                <h3 class="section-title">General Information</h3>
                                <p class="section-desc" style="margin-top: -12px; margin-bottom: 24px; opacity: 0.7;">Manage your agency's core identification and bio.</p>

                                    <div class="form-group">
                                        <label class="form-label">Agency Name</label>
                                        <input type="text" name="name" class="glass-input" value="<?php echo htmlspecialchars($agency_name); ?>">
                                    </div>

                                <div class="form-row">
                                    <div class="form-group half">
                                        <label class="form-label">Phone</label>
                                        <input type="text" name="phone" class="glass-input" value="<?php echo htmlspecialchars($agency ? $agency->phone : ''); ?>">
                                    </div>
                                    <div class="form-group half">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="glass-input" value="<?php echo htmlspecialchars($agency ? $agency->email : ''); ?>">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Company Bio</label>
                                    <textarea name="description" class="glass-input" rows="5"><?php echo htmlspecialchars($agency ? $agency->description : ''); ?></textarea>
                                    <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px; text-align: right;">Short bio for your portal's about section.</div>
                                </div>
                            </div>
                        </div>


                        <!-- Contact Pane -->
                        <div class="settings-pane" id="contact-pane" style="display: none;">
                            <div class="card-panel glass form-section">
                                <div class="section-badge"><i class="fa-solid fa-map-pin-line"></i></div>
                                <h3 class="section-title">Contact & Location</h3>
                                <p class="section-desc" style="margin-top: -12px; margin-bottom: 24px; opacity: 0.7;">Manage your public contact information and office location.</p>
                                
                                <div class="form-group">
                                    <label class="form-label">Website URL</label>
                                    <div class="input-wrapper">
                                        <i class="fa-solid fa-globe"></i>
                                        <input type="url" name="website" class="glass-input" value="<?php echo htmlspecialchars($agency ? $agency->website : ''); ?>" placeholder="https://agency.com">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Physical Address</label>
                                    <div class="input-wrapper">
                                        <i class="fa-solid fa-location-dot"></i>
                                        <input type="text" name="address" class="glass-input" value="<?php echo htmlspecialchars($agency ? $agency->address : ''); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- Security Pane -->
                        <div class="settings-pane" id="security-pane" style="display: none;">
                            <div class="card-panel glass form-section">
                                <div class="section-badge"><i class="fa-solid fa-key"></i></div>
                                <h3 class="section-title">Security Settings</h3>
                                <p class="section-desc" style="margin-top: -12px; margin-bottom: 24px; opacity: 0.7;">Update your password and manage session security.</p>
                                
                                <div class="form-group">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" name="current_password" class="glass-input" placeholder="••••••••">
                                </div>
                                <div class="form-row">
                                    <div class="form-group half">
                                        <label class="form-label">New Password</label>
                                        <input type="password" name="new_password" class="glass-input" placeholder="••••••••">
                                    </div>
                                    <div class="form-group half">
                                        <label class="form-label">Confirm Password</label>
                                        <input type="password" name="confirm_password" class="glass-input" placeholder="••••••••">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>

    <!-- External JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://kit.fontawesome.com/your-kit-id.js" crossorigin="anonymous"></script> <!-- Note: Usually it's a specific kit, I'll use a standard CDN if I can find one or just FontAwesome 6 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js"></script>
    <script src="../includes/assets/js/utils.js"></script>
    <script src="../includes/assets/js/script.js"></script>
    <script src="../includes/assets/js/agency/settings.js"></script>
    <script src="../includes/assets/js/agency/notif-checker.js"></script>
</body>
</html>

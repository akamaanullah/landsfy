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

try {
    // 1. Fetch Basic User Info
    $u_stmt = $pdo->prepare("SELECT full_name, email, phone, created_at, avatar_url FROM users WHERE id = ?");
    $u_stmt->execute([$user_id]);
    $user = $u_stmt->fetch();

    if (!$user) {
        header("Location: ../logout.php");
        exit;
    }

    // 2. Fetch Property Stats
    $stat_stmt = $pdo->prepare("SELECT 
        COUNT(*) as total_properties,
        SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) as sold_properties
    FROM properties 
    WHERE author_id = ? AND status != 'deleted'");
    $stat_stmt->execute([$user_id]);
    $stats = $stat_stmt->fetch();

    $total_listings = $stats->total_properties ?: 0;
    $sold_listings = $stats->sold_properties ?: 0;
    $join_year = date('Y', strtotime($user->created_at ?: 'today'));

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Profile | Landsfy</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
                <li class="nav-item">
                    <a href="leads.php"><i class="fa-solid fa-users"></i> Buyer Leads</a>
                </li>
                <li class="nav-item">
                    <a href="add-listing.php"><i class="fa-solid fa-circle-plus"></i> Post New</a>
                </li>
                <li class="nav-item active">
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
                    <div class="page-title">My Profile</div>
                    <div class="breadcrumb">Account settings and preferences</div>
                </div>

                <div class="header-actions">
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
                    <button class="btn-primary" style="gap: 10px; padding: 12px 24px;">
                        <i class="fa-solid fa-check"></i> Save Changes
                    </button>
                </div>
            </header>

            <div class="view-container">
                <div class="form-layout-grid"
                    style="display: grid; grid-template-columns: 1fr 340px; gap: 32px; align-items: start;">
                    <div class="form-main-col">
                        <!-- Profile Card -->
                        <div class="card-panel glass"
                            style="padding: 40px; overflow: hidden; position: relative; margin-bottom: 32px;">
                            <div
                                style="position: absolute; right: -20px; top: -20px; font-size: 160px; color: var(--primary); opacity: 0.03; font-style: italic; font-weight: 800; transform: rotate(-10deg);">
                                PROFILE</div>

                            <div style="display: flex; align-items: center; gap: 32px; position: relative; z-index: 1;">
                                <div class="profile-avatar-wrapper" style="position: relative;">
                                    <img id="avatarPreview"
                                        src="<?php echo !empty($user->avatar_url) ? '../' . htmlspecialchars($user->avatar_url) : 'https://i.pravatar.cc/150?img=12'; ?>"
                                        alt="User"
                                        style="width: 140px; height: 140px; border-radius: 50%; border: 4px solid var(--glass-border); padding: 4px; background: var(--glass-bg); box-shadow: 0 10px 30px rgba(107,0,182,0.1); object-fit: cover;">
                                    <button class="icon-btn" onclick="document.getElementById('avatarInput').click()"
                                        style="position: absolute; bottom: 4px; right: 4px; width: 44px; height: 44px; background: var(--primary); color: white; border: 4px solid var(--glass-bg); border-radius: 50%; box-shadow: var(--shadow-lg);">
                                        <i class="fa-solid fa-camera"></i>
                                    </button>
                                    <input type="file" id="avatarInput" style="display: none;" accept="image/*">
                                </div>
                                <div style="flex: 1;">
                                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                                        <h2
                                            style="font-size: 32px; font-weight: 800; color: var(--text-primary); font-family: 'Outfit';">
                                            <?php echo htmlspecialchars($user->full_name); ?></h2>
                                        <span class="badge-tag status-active"
                                            style="display: inline-flex; align-items: center; gap: 6px; border-radius: 30px; padding: 6px 14px; font-weight: 700; font-size: 11px;">
                                            <i class="fa-solid fa-circle-check"></i> VERIFIED
                                        </span>
                                    </div>
                                    <p
                                        style="color: var(--text-secondary); font-weight: 500; font-size: 15px; margin-bottom: 12px;">
                                        Premium Individual Seller Since <?php echo $join_year; ?></p>
                                    <div style="display: flex; gap: 24px;">
                                        <div style="text-align: center;">
                                            <div style="font-size: 18px; font-weight: 800; color: var(--primary);">
                                                <?php echo sprintf("%02d", $total_listings); ?></div>
                                            <div
                                                style="font-size: 11px; color: var(--text-secondary); font-weight: 700; text-transform: uppercase;">
                                                Listings</div>
                                        </div>
                                        <div
                                            style="height: 30px; width: 1px; background: var(--glass-border); align-self: center;">
                                        </div>
                                        <div style="text-align: center;">
                                            <div style="font-size: 18px; font-weight: 800; color: var(--success);">
                                                <?php echo sprintf("%02d", $sold_listings); ?></div>
                                            <div
                                                style="font-size: 11px; color: var(--text-secondary); font-weight: 700; text-transform: uppercase;">
                                                Sold</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Personal Info -->
                        <div class="card-panel glass form-section" style="padding: 32px;">
                            <div class="section-badge"
                                style="background: rgba(107, 0, 182, 0.1); color: var(--primary);"><i
                                    class="fa-solid fa-circle-user"></i></div>
                            <h3 class="section-title" style="font-size: 20px; font-weight: 800; margin-bottom: 24px;">
                                Identity & Contact</h3>

                            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                                <div class="form-group">
                                    <label class="form-label" style="font-weight: 700; margin-bottom: 10px;">Display
                                        Name</label>
                                    <div class="input-wrapper" style="position: relative;">
                                        <i class="fa-solid fa-identification-card"
                                            style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                                        <input type="text" id="profileFullName" class="glass-input"
                                            value="<?php echo htmlspecialchars($user->full_name); ?>"
                                            style="padding-left: 48px; width: 100%; height: 52px; border-radius: 12px;">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" style="font-weight: 700; margin-bottom: 10px;">Email
                                        Address</label>
                                    <div class="input-wrapper" style="position: relative;">
                                        <i class="fa-solid fa-envelope-simple"
                                            style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                                        <input type="email" id="profileEmail" class="glass-input"
                                            value="<?php echo htmlspecialchars($user->email); ?>"
                                            style="padding-left: 48px; width: 100%; height: 52px; border-radius: 12px;">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" style="font-weight: 700; margin-bottom: 10px;">Phone
                                        Number</label>
                                    <div class="input-wrapper" style="position: relative;">
                                        <i class="fa-solid fa-phone"
                                            style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                                        <input type="tel" id="profilePhone" class="glass-input"
                                            value="<?php echo htmlspecialchars($user->phone ?: '+92 '); ?>"
                                            style="padding-left: 48px; width: 100%; height: 52px; border-radius: 12px;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Preferences -->
                        
                    </div>

                    <div class="form-sidebar-col">
                        <!-- Security Status -->
                        <div class="card-panel glass" style="padding: 24px; margin-bottom: 32px;">
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
                                <div
                                    style="width: 40px; height: 40px; border-radius: 10px; background: rgba(239, 68, 68, 0.1); color: var(--danger); display: flex; align-items: center; justify-content: center;">
                                    <i class="fa-solid fa-shield-check"></i></div>
                                <h3 style="font-size: 18px; font-weight: 800; color: var(--text-primary);">Security</h3>
                            </div>

                            <div style="display: grid; gap: 12px;">
                                <button id="openPasswordModal" class="btn-ghost"
                                    style="width: 100%; padding: 14px; border-radius: 12px; border: 1px solid var(--glass-border); text-align: left; font-weight: 700; display: flex; align-items: center; gap: 12px; font-size: 14px; background: var(--glass-bg); color: var(--text-primary);">
                                    <i class="fa-solid fa-password"></i> Update Password
                                </button>
                                <button id="openDeactivateModal" class="btn-ghost"
                                    style="width: 100%; padding: 14px; border-radius: 12px; border: 1px solid var(--glass-border); text-align: left; font-weight: 700; display: flex; align-items: center; gap: 12px; font-size: 14px; color: var(--danger); background: var(--glass-bg);">
                                    <i class="fa-solid fa-user-minus"></i> Deactivate Profile
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Update Password Modal -->
    <div id="passwordModal" class="modal-overlay" style="z-index: 10000 !important; align-items: center; justify-content: center; padding: 20px;">
        <div class="modal-content glass-modal card-panel glass" style="max-width: 450px; width: 100%; height: auto !important; min-height: unset !important; padding: 32px; position: relative; border: none;">
            <button class="close-modal-btn" onclick="closeModal('passwordModal')" style="position: absolute; top: 16px; right: 16px; background: none; border: none; font-size: 24px; color: var(--text-secondary); cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
            
            <div style="margin-bottom: 24px;">
                <h2 style="font-size: 24px; font-weight: 800; font-family: 'Outfit'; color: var(--text-primary); margin-bottom: 8px;">Update Password</h2>
                <p style="color: var(--text-secondary); font-size: 14px;">Ensure your account is using a long, random password to stay secure.</p>
            </div>

            <div style="display: grid; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Current Password</label>
                    <input type="password" id="currentPass" class="glass-input" style="width: 100%;" placeholder="••••••••">
                </div>
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" id="newPass" class="glass-input" style="width: 100%;" placeholder="Min. 6 characters">
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" id="confirmPass" class="glass-input" style="width: 100%;" placeholder="Match new password">
                </div>
                <button id="savePasswordBtn" class="btn-primary" style="width: 100%; padding: 14px; margin-top: 8px;">
                    <i class="fa-solid fa-lock-key"></i> Update Password
                </button>
            </div>
        </div>
    </div>

    <!-- Deactivate Account Modal -->
    <div id="deactivateModal" class="modal-overlay" style="z-index: 10000 !important; align-items: center; justify-content: center; padding: 20px;">
        <div class="modal-content glass-modal card-panel glass" style="max-width: 400px; width: 100%; height: auto !important; min-height: unset !important; padding: 32px; text-align: center; border: none;">
            <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(239, 68, 68, 0.1); color: var(--danger); display: flex; align-items: center; justify-content: center; font-size: 32px; margin: 0 auto 20px;">
                <i class="fa-solid fa-warning-octagon"></i>
            </div>
            <h2 style="font-size: 22px; font-weight: 800; font-family: 'Outfit'; color: var(--text-primary); margin-bottom: 12px;">Deactivate Profile?</h2>
            <p style="color: var(--text-secondary); font-size: 14px; margin-bottom: 32px; line-height: 1.6;">Are you sure you want to deactivate your profile? You will be logged out immediately and your listings will be hidden.</p>
            
            <div style="display: flex; gap: 12px;">
                <button onclick="closeModal('deactivateModal')" class="btn-ghost" style="flex: 1; padding: 12px; border: 1px solid var(--glass-border); border-radius: 12px; font-weight: 700;">Cancel</button>
                <button id="confirmDeactivateBtn" class="btn-primary" style="flex: 1; padding: 12px; border-radius: 12px; background: var(--danger); font-weight: 700;">Yes, Deactivate</button>
            </div>
        </div>
    </div>

    <style>
        .modal-overlay { z-index: 10000 !important; }
        @keyframes modalPop {
            from { opacity: 0; transform: scale(0.9) translateY(20px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
    </style>

    <!-- External JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../includes/assets/js/utils.js"></script>
    <script src="../includes/assets/js/script.js"></script>
    <script src="../includes/assets/js/seller/notif-checker.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const avatarInput = document.getElementById('avatarInput');
            const avatarPreview = document.getElementById('avatarPreview');
            const saveBtn = document.querySelector('.btn-primary');

            // 1. Image Preview
            if (avatarInput) {
                avatarInput.addEventListener('change', function () {
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            avatarPreview.src = e.target.result;
                        };
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            }

            // 2. Save Profile Changes
            if (saveBtn) {
                saveBtn.addEventListener('click', async () => {
                    const fullName = document.getElementById('profileFullName').value;
                    const email = document.getElementById('profileEmail').value;
                    const phone = document.getElementById('profilePhone').value;
                    const avatarFile = avatarInput.files[0];

                    if (!fullName || !email) {
                        if (window.showToast) showToast('Name and Email are required', 'error');
                        return;
                    }

                    // Loading State
                    const originalContent = saveBtn.innerHTML;
                    saveBtn.disabled = true;
                    saveBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin spinner"></i> Saving...';

                    const formData = new FormData();
                    formData.append('full_name', fullName);
                    formData.append('email', email);
                    formData.append('phone', phone);
                    if (avatarFile) formData.append('avatar', avatarFile);

                    try {
                        const response = await fetch('../includes/api/seller/update_profile.php', {
                            method: 'POST',
                            body: formData
                        });
                        const result = await response.json();

                        if (result.success) {
                            if (window.showToast) showToast(result.message, 'success');

                            // Update sidebar name and avatar if changed
                            const sidebarName = document.querySelector('.user-name');
                            const sidebarAvatar = document.querySelector('.user-avatar');

                            if (sidebarName) sidebarName.textContent = result.full_name;
                            if (sidebarAvatar && result.avatar_url) sidebarAvatar.src = result.avatar_url;

                            // Reload slightly after success to sync everything if needed
                            setTimeout(() => window.location.reload(), 1500);
                        } else {
                            if (window.showToast) showToast(result.message, 'error');
                        }
                    } catch (error) {
                        console.error('Update error:', error);
                        if (window.showToast) showToast('An error occurred during update', 'error');
                    } finally {
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = originalContent;
                    }
                });
            }

            // --- ACCOUNT SECURITY LOGIC ---

            // Modal Control Functions (Globally accessible via buttons)
            window.openModal = (id) => {
                const modal = document.getElementById(id);
                if (modal) {
                    modal.classList.add('active');
                    document.body.style.overflow = 'hidden'; // Prevent background scrolling
                }
            };

            window.closeModal = (id) => {
                const modal = document.getElementById(id);
                if (modal) {
                    modal.classList.remove('active');
                    document.body.style.overflow = '';
                }
            };

            // 1. Open Modals
            document.getElementById('openPasswordModal').addEventListener('click', () => openModal('passwordModal'));
            document.getElementById('openDeactivateModal').addEventListener('click', () => openModal('deactivateModal'));

            // 2. Handle Password Update
            const savePasswordBtn = document.getElementById('savePasswordBtn');
            if (savePasswordBtn) {
                savePasswordBtn.addEventListener('click', async () => {
                    const currentPass = document.getElementById('currentPass').value;
                    const newPass = document.getElementById('newPass').value;
                    const confirmPass = document.getElementById('confirmPass').value;

                    if (!currentPass || !newPass || !confirmPass) {
                        if (window.showToast) showToast('Please fill all password fields', 'error');
                        return;
                    }

                    if (newPass !== confirmPass) {
                        if (window.showToast) showToast('New passwords do not match', 'error');
                        return;
                    }

                    // Loading
                    const originalBtn = savePasswordBtn.innerHTML;
                    savePasswordBtn.disabled = true;
                    savePasswordBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin spinner"></i> Updating...';

                    const formData = new FormData();
                    formData.append('current_password', currentPass);
                    formData.append('new_password', newPass);
                    formData.append('confirm_password', confirmPass);

                    try {
                        const res = await fetch('../includes/api/seller/change_password.php', {
                            method: 'POST',
                            body: formData
                        });
                        const data = await res.json();

                        if (data.success) {
                            if (window.showToast) showToast(data.message, 'success');
                            closeModal('passwordModal');
                            // Clear fields
                            document.getElementById('currentPass').value = '';
                            document.getElementById('newPass').value = '';
                            document.getElementById('confirmPass').value = '';
                        } else {
                            if (window.showToast) showToast(data.message, 'error');
                        }
                    } catch (e) {
                        if (window.showToast) showToast('Failed to update password', 'error');
                    } finally {
                        savePasswordBtn.disabled = false;
                        savePasswordBtn.innerHTML = originalBtn;
                    }
                });
            }

            // 3. Handle Deactivation
            const confirmDeactivateBtn = document.getElementById('confirmDeactivateBtn');
            if (confirmDeactivateBtn) {
                confirmDeactivateBtn.addEventListener('click', async () => {
                    confirmDeactivateBtn.disabled = true;
                    confirmDeactivateBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin spinner"></i> Deactivating...';

                    try {
                        const res = await fetch('../includes/api/seller/deactivate_account.php', {
                            method: 'POST'
                        });
                        const data = await res.json();

                        if (data.success) {
                            if (window.showToast) showToast(data.message, 'success');
                            setTimeout(() => window.location.href = '../login.php', 1500);
                        } else {
                            if (window.showToast) showToast(data.message, 'error');
                            confirmDeactivateBtn.disabled = false;
                            confirmDeactivateBtn.innerHTML = 'Yes, Deactivate';
                        }
                    } catch (e) {
                         if (window.showToast) showToast('Deactivation failed', 'error');
                         confirmDeactivateBtn.disabled = false;
                         confirmDeactivateBtn.innerHTML = 'Yes, Deactivate';
                    }
                });
            }

            // Close modals on overlay click
            document.querySelectorAll('.modal-overlay').forEach(overlay => {
                overlay.addEventListener('click', (e) => {
                    if (e.target === overlay) closeModal(overlay.id);
                });
            });
        });
    </script>
</body>

</html>
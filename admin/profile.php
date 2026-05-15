<?php include 'header.php'; ?>

<main class="main-content">
    <header class="header glass" style="margin-bottom: 30px;">
        <div class="header-left">
            <div class="breadcrumb" style="font-size: 13px; color: var(--text-secondary); margin-bottom: 5px;">
                Account / System Admin
            </div>
            <div class="page-title">Admin Profile</div>
        </div>
        <div class="header-actions">
            <button class="icon-btn" id="themeToggle" style="margin-right: 12px;"><i class="fa-solid fa-moon" id="themeIcon"></i></button>
            <button class="btn-primary" id="saveProfileBtn"><i class="fa-solid fa-cloud-arrow-up"></i> Save Changes</button>
        </div>
    </header>

    <div class="view-container">
        <div style="display: grid; grid-template-columns: 320px 1fr; gap: 30px;">
            
            <!-- Left: Sidebar Branding -->
            <div style="display: flex; flex-direction: column; gap: 24px;">
                <div class="card-panel glass" style="text-align: center; padding: 40px 24px;">
                    <div style="position: relative; width: 120px; height: 120px; margin: 0 auto 20px;">
                        <img src="<?php echo !empty($_SESSION['avatar_url']) ? '../' . htmlspecialchars($_SESSION['avatar_url']) : 'https://i.pravatar.cc/150?img=11'; ?>" 
                             alt="admin" id="profileAvatarPreview" class="user-avatar" style="width: 100%; height: 100%; border-radius: 30px; object-fit: cover; border: 3px solid var(--primary);">
                        <label for="avatarInput" style="position: absolute; bottom: -10px; right: -10px; width: 36px; height: 36px; background: var(--primary); color: white; border-radius: 12px; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 3px solid var(--bg-color); transition: all 0.3s ease;">
                            <i class="fa-solid fa-camera"></i>
                        </label>
                        <input type="file" id="avatarInput" style="display: none;" accept="image/*">
                    </div>
                    <h3 style="font-family: 'Outfit', sans-serif; font-size: 20px; font-weight: 700; margin-bottom: 5px;"><?php echo htmlspecialchars($_SESSION['username']); ?></h3>
                    <div class="badge-tag status-info" style="display: inline-block;">Super Administrator</div>
                    
                    <div style="margin-top: 30px; border-top: 1px solid var(--glass-border); padding-top: 20px; text-align: left;">
                        <div style="font-size: 13px; color: var(--text-secondary); margin-bottom: 12px;">
                            <i class="fa-solid fa-circle-check" style="color: var(--success); margin-right: 8px;"></i> System Security: High
                        </div>
                        <div style="font-size: 13px; color: var(--text-secondary);">
                            <i class="fa-solid fa-clock" style="margin-right: 8px;"></i> Last Login: <?php echo date('M d, Y H:i'); ?>
                        </div>
                    </div>
                </div>

                <div class="card-panel glass" style="padding: 24px;">
                    <h4 style="font-size: 15px; font-weight: 700; margin-bottom: 15px;">Theme Preference</h4>
                    <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 20px;">Settings are synced to your account.</p>
                    <div style="display: flex; gap: 10px;">
                        <button class="btn-preview" style="flex: 1; padding: 12px;" onclick="setTheme('light')"><i class="fa-solid fa-sun"></i> Light</button>
                        <button class="btn-primary" style="flex: 1; padding: 12px;" onclick="setTheme('dark')"><i class="fa-solid fa-moon"></i> Dark</button>
                    </div>
                </div>
            </div>

            <!-- Right: Content Fields -->
            <div class="card-panel glass" style="padding: 40px;">
                <h3 class="section-title" style="margin-bottom: 30px;">Account Settings</h3>

                <form id="adminProfileForm">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <div class="input-wrapper">
                                <i class="fa-solid fa-user"></i>
                                <input type="text" name="full_name" class="glass-input" value="<?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <div class="input-wrapper">
                                <i class="fa-solid fa-envelope"></i>
                                <input type="email" name="email" class="glass-input" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 40px;">
                        <div class="form-group">
                            <label class="form-label">Username</label>
                            <div class="input-wrapper">
                                <i class="fa-solid fa-at"></i>
                                <input type="text" name="username" class="glass-input" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" disabled title="Contact support to change username">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Contact Number</label>
                            <div class="input-wrapper">
                                <i class="fa-solid fa-phone"></i>
                                <input type="tel" name="phone" class="glass-input" value="<?php echo htmlspecialchars($_SESSION['phone'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <div style="border-top: 1px solid var(--glass-border); padding-top: 30px;">
                        <h3 class="section-title" style="margin-bottom: 24px; font-size: 16px;">Change Password</h3>
                        <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 20px;">Leave blank to keep your current password.</p>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                            <div class="form-group">
                                <label class="form-label">New Password</label>
                                <div class="input-wrapper">
                                    <i class="fa-solid fa-lock"></i>
                                    <input type="password" name="new_password" class="glass-input" placeholder="Min. 8 characters">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Confirm New Password</label>
                                <div class="input-wrapper">
                                    <i class="fa-solid fa-lock"></i>
                                    <input type="password" name="confirm_password" class="glass-input" placeholder="Must match exactly">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</main>

<script src="../includes/assets/js/script.js"></script>
<script src="../includes/assets/js/admin/profile.js"></script>
</body>
</html>

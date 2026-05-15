<?php 
$page_title = "My Profile";
include 'header.php'; 

// Fetch detailed user profile for the form
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch joined date
$joined_date = date('F Y', strtotime($user_data['created_at']));
$is_verified = is_user_verified($user_id);
$is_premium = is_user_premium($user_id);
?>
        <!-- Main Content -->
        <main class="main-content">
            <header class="header glass">
                <div class="header-left">
                    <div class="page-title">My Profile</div>
                    <div class="breadcrumb">Manage your account and preferences</div>
                </div>
                
                <div class="header-actions">
                    <button class="icon-btn" id="themeToggle">
                        <i class="fa-solid <?php echo $user_theme == 'light' ? 'fa-moon' : 'fa-sun'; ?>" id="themeIcon"></i>
                    </button>
                    <button class="btn-primary" id="saveProfileBtn">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Save Profile
                    </button>
                </div>
            </header>

            <div class="view-container">
                <div class="form-layout-grid">
                    <div class="form-main-col">
                        <!-- Profile Header -->
                        <div class="card-panel glass profile-card-header">
                            <div class="profile-avatar-wrapper">
                                <img src="<?php echo getImageUrl($_SESSION['avatar_url'] ?? ''); ?>" id="profilePreview" alt="User" onerror="handleImageError(this, 'user')">
                                <input type="file" id="avatarInput" hidden accept="image/*">
                                <button class="avatar-edit-btn" onclick="document.getElementById('avatarInput').click()"><i class="fa-solid fa-camera"></i></button>
                            </div>
                            <div class="profile-info">
                                <h2><?php echo htmlspecialchars($full_name); ?></h2>
                                <p>Member since <?php echo $joined_date; ?></p>
                                <div style="display: flex; gap: 8px; margin-top: 12px;">
                                    <?php if($is_verified): ?>
                                        <span class="badge-tag status-active" style="display: flex; align-items: center; gap: 4px; border-radius: 20px;"><i class="fa-solid fa-circle-check"></i> Account Verified</span>
                                    <?php endif; ?>
                                    <?php if($is_premium): ?>
                                        <span class="badge-tag status-info" style="border-radius: 20px;">Premium Buyer</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Personal Info -->
                        <form id="profileForm" class="card-panel glass form-section">
                            <div class="section-badge" style="width: 40px; height: 40px; background: var(--primary-light); color: var(--primary); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;"><i class="fa-solid fa-user"></i></div>
                            <h3 class="section-title" style="font-size: 18px; font-weight: 700; margin-bottom: 24px;">Personal Information</h3>
                            
                            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                                <div class="form-group">
                                    <label class="form-label" style="display: block; font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px;">Full Name</label>
                                    <div class="input-wrapper" style="position: relative;">
                                        <i class="fa-solid fa-user" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                                        <input type="text" name="full_name" class="glass-input" value="<?php echo htmlspecialchars($user_data['full_name']); ?>" style="width: 100%; padding: 12px 16px 12px 48px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: var(--text-primary);">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" style="display: block; font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px;">Email Address</label>
                                    <div class="input-wrapper" style="position: relative;">
                                        <i class="fa-solid fa-envelope" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                                        <input type="email" name="email" class="glass-input" value="<?php echo htmlspecialchars($user_data['email']); ?>" style="width: 100%; padding: 12px 16px 12px 48px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: var(--text-primary);">
                                    </div>
                                </div>
                            </div>

                            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div class="form-group">
                                    <label class="form-label" style="display: block; font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px;">Phone Number</label>
                                    <div class="input-wrapper" style="position: relative;">
                                        <i class="fa-solid fa-phone" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                                        <input type="tel" name="phone" class="glass-input" value="<?php echo htmlspecialchars($user_data['phone']); ?>" style="width: 100%; padding: 12px 16px 12px 48px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: var(--text-primary);">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" style="display: block; font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px;">Username</label>
                                    <div class="input-wrapper" style="position: relative;">
                                        <i class="fa-solid fa-identification-card" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                                        <input type="text" name="username" class="glass-input" value="<?php echo htmlspecialchars($user_data['username']); ?>" readonly style="width: 100%; padding: 12px 16px 12px 48px; border-radius: 12px; border: 1px solid var(--glass-border); background: rgba(0,0,0,0.05); color: var(--text-secondary); cursor: not-allowed;">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Sidebar Info -->
                    <div class="form-sidebar-col">
                        <div class="card-panel glass" style="padding: 24px;">
                            <h3 class="section-title" style="margin-bottom: 20px; font-size: 16px; font-weight: 700; color: var(--text-primary);">Account Security</h3>
                            <button class="btn-ghost" id="changePasswordTrigger" onclick="document.getElementById('passwordModal').style.display='flex'" style="width: 100%; border: 1px solid var(--glass-border); padding: 12px; border-radius: 12px; display: flex; align-items: center; gap: 12px; margin-bottom: 12px; color: var(--text-primary); cursor: pointer;">
                                <i class="fa-solid fa-key" style="font-size: 20px;"></i> Change Password
                            </button>
                            <button class="btn-ghost" onclick="window.location.href='../logout.php'" style="width: 100%; border: 1px solid var(--glass-border); padding: 12px; border-radius: 12px; display: flex; align-items: center; gap: 12px; color: var(--danger); cursor: pointer;">
                                <i class="fa-solid fa-right-from-bracket" style="font-size: 20px;"></i> Logout Account
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Change Password Modal -->
    <div id="passwordModal" class="modal-overlay">
        <div class="modal-content-panel">
            <div class="modal-header">
                <h3>Change Password</h3>
                <button class="modal-close-btn" onclick="closePasswordModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            <form id="passwordForm">
                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="old_password" required class="glass-input" placeholder="••••••••">
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" required class="glass-input" placeholder="••••••••">
                </div>
                <div class="form-group" style="margin-bottom: 28px;">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_password" required class="glass-input" placeholder="••••••••">
                </div>
                <button type="submit" class="btn-primary" style="width: 100%; padding: 14px; border-radius: 12px; font-weight: 700;">Update Password</button>
            </form>
        </div>
    </div>

    <!-- External JS -->
    <script src="../includes/assets/js/script.js"></script>
    <script src="../includes/assets/js/buyer/notif-checker.js"></script>
    <script>
        // Modal Controls
        const passModal = document.getElementById('passwordModal');
        const trigger = document.getElementById('changePasswordTrigger');
        
        if (trigger) {
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Change Password Trigger Clicked');
                passModal.style.display = 'flex';
            });
        }
        
        function closePasswordModal() { 
            passModal.style.display = 'none'; 
        }
        
        // Handle Password Change
        document.getElementById('passwordForm').onsubmit = async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            if(formData.get('new_password') !== formData.get('confirm_password')) {
                return Swal.fire('Error', 'New passwords do not match!', 'error');
            }

            try {
                const response = await fetch('../includes/api/buyer/change_password.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.success) {
                    Swal.fire('Success', 'Password updated successfully', 'success');
                    closePasswordModal();
                    this.reset();
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            } catch (error) {
                console.error('Error changing password:', error);
            }
        };

        document.getElementById('saveProfileBtn').addEventListener('click', async function() {
            const form = document.getElementById('profileForm');
            const formData = new FormData(form);
            formData.append('action', 'update_profile');

            try {
                const response = await fetch('../includes/api/buyer/update_profile.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Profile Updated!',
                        text: 'Your personal information has been saved.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            } catch (error) {
                console.error('Error updating profile:', error);
            }
        });

        // Avatar Upload Logic
        document.getElementById('avatarInput').addEventListener('change', async function(e) {
            if (e.target.files && e.target.files[0]) {
                const formData = new FormData();
                formData.append('avatar', e.target.files[0]);
                formData.append('action', 'update_avatar');

                try {
                    const response = await fetch('../includes/api/buyer/update_profile.php', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();
                    if (data.success) {
                        document.getElementById('profilePreview').src = data.avatar_url;
                        Swal.fire('Success', 'Avatar updated successfully', 'success');
                        location.reload(); // To update sidebar avatar too
                    }
                } catch (error) {
                    console.error('Error uploading avatar:', error);
                }
            }
        });
    </script>
</body>
</html>

<?php 
include 'header.php';

// Fetch detailed profile info
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("
    SELECT u.*, a.bio, a.specialization, a.license_number, a.experience_years, 
           ag.name as agency_name, ag.id as agency_uid, ag.address as agency_address, ag.is_verified as agency_verified,
           (SELECT COUNT(*) FROM agents WHERE agency_id = ag.id) as team_count
    FROM users u
    LEFT JOIN agents a ON u.id = a.user_id
    LEFT JOIN agencies ag ON a.agency_id = ag.id
    WHERE u.id = ?
");
$stmt->execute([$user_id]);
$profile = $stmt->fetch();

if (!$profile) {
    die("Profile not found.");
}

$avatar = getImageUrl($profile->avatar_url ?? '');
?>
        <!-- Main Content Area -->
        <main class="main-content">
            <form id="profileForm" enctype="multipart/form-data">
                <!-- Top Header -->
                <header class="header glass" style="margin-bottom: 24px;">
                    <div class="header-left">
                        <div class="page-title">Personal Profile</div>
                        <div class="breadcrumb"><?php echo htmlspecialchars($profile->username); ?> / Profile</div>
                    </div>
                    
                    <div class="header-actions">
                        <button type="button" class="icon-btn" id="themeToggle" title="Toggle Light/Dark Mode">
                            <i class="fa-solid fa-moon" id="themeIcon"></i>
                        </button>
                        <a href="notifications.php" class="icon-btn">
                            <i class="fa-solid fa-bell"></i>
                        </a>
                        <button type="submit" class="btn-primary" id="saveProfileBtn">
                            <i class="fa-solid fa-floppy-disk"></i> Save Changes
                        </button>
                    </div>
                </header>

                <div class="view-container">
                    <!-- Profile Header Card -->
                    <div class="card-panel glass" style="padding: 0; overflow: hidden; margin-bottom: 32px; border-radius: 24px;">
                        <div class="settings-banner" style="background: linear-gradient(135deg, #6b00b6 0%, #4c0082 100%);">
                            <button type="button" class="icon-btn" style="position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white;">
                                <i class="fa-solid fa-camera"></i>
                            </button>
                        </div>
                        <div style="padding: 0 40px 40px;">
                            <div style="display: flex; align-items: center; gap: 32px; margin-top: -60px;">
                                <div class="settings-logo-wrapper" style="position: relative;">
                                    <div style="width: 140px; height: 140px; overflow: hidden; background: var(--surface-bg); border-radius: 50%; box-shadow: 0 10px 30px rgba(0,0,0,0.2); border: 6px solid var(--glass-bg);">
                                        <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Agent" id="profileAvatarPreview" style="width: 100%; height: 100%; object-fit: cover;" onerror="handleImageError(this, 'user')">
                                    </div>
                                    <input type="file" name="avatar" id="avatarInput" style="display: none;" accept="image/*">
                                    <button type="button" class="edit-pencil-btn" style="right: 5px; bottom: 10px;" onclick="document.getElementById('avatarInput').click()">
                                        <i class="fa-solid fa-pencil"></i>
                                    </button>
                                </div>
                                <div style="padding-top: 40px;">
                                    <h1 style="font-size: 32px; font-weight: 800; color: var(--text-primary); margin-bottom: 6px; letter-spacing: -1px;"><?php echo htmlspecialchars($profile->full_name); ?></h1>
                                    <div style="display: flex; gap: 16px; align-items: center;">
                                        <span class="badge-tag status-active" style="padding: 6px 14px; font-size: 12px; font-weight: 700;"><i class="fa-solid fa-circle-check"></i> Official Agent</span>
                                        <span style="font-size: 14px; color: var(--text-secondary); font-weight: 500;"><i class="fa-solid fa-building" style="color: var(--primary);"></i> <?php echo htmlspecialchars($profile->agency_name ?? 'Independent'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-layout-grid">
                        <!-- Nav Tabs (Vertical-ish Sidebar for Grid) -->
                        <div class="form-sidebar-col">
                            <div class="card-panel glass settings-nav-container" style="padding: 12px; position: sticky; top: 124px;">
                                <div class="settings-nav-item active" data-target="general-pane">
                                    <i class="fa-solid fa-user-focus"></i> Personal Details
                                </div>
                                <div class="settings-nav-item" data-target="contact-pane">
                                    <i class="fa-solid fa-location-dot-line"></i> Contact Info
                                </div>

                                <div class="settings-nav-item" data-target="agency-pane">
                                    <i class="fa-solid fa-building"></i> Agency Info
                                </div>
                                <div class="settings-nav-item" data-target="security-pane">
                                    <i class="fa-solid fa-key"></i> Security
                                </div>
                            </div>
                        </div>

                        <!-- Main Form Section -->
                        <div class="form-main-col">
                            <div class="settings-pane active" id="general-pane">
                                <div class="card-panel glass form-section">
                                    <div class="section-badge"><i class="fa-solid fa-user-focus"></i></div>
                                    <h3 class="section-title">Personal Details</h3>
                                    <p class="section-desc" style="margin-top: -12px; margin-bottom: 24px; opacity: 0.7;">Manage your professional public profile.</p>

                                    <div class="form-group">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="full_name" class="glass-input" value="<?php echo htmlspecialchars($profile->full_name); ?>" required>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group half">
                                            <label class="form-label">Specialization</label>
                                            <input type="text" name="specialization" class="glass-input" value="<?php echo htmlspecialchars($profile->specialization ?? ''); ?>" placeholder="e.g. Luxury Villas">
                                        </div>
                                        <div class="form-group half">
                                            <label class="form-label">Experience (Years)</label>
                                            <input type="number" name="experience_years" class="glass-input" value="<?php echo htmlspecialchars($profile->experience_years ?? 0); ?>">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Agent Bio</label>
                                        <textarea name="bio" class="glass-input" rows="5"><?php echo htmlspecialchars($profile->bio ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Pane -->
                            <div class="settings-pane" id="contact-pane" style="display: none;">
                                <div class="card-panel glass form-section">
                                    <div class="section-badge"><i class="fa-solid fa-map-pin-line"></i></div>
                                    <h3 class="section-title">Contact & Location</h3>
                                    <p class="section-desc" style="margin-top: -12px; margin-bottom: 24px; opacity: 0.7;">Manage your public contact information and office location.</p>
                                    
                                    <div class="form-row">
                                        <div class="form-group half">
                                            <label class="form-label">Public Email</label>
                                            <div class="input-wrapper">
                                                <i class="fa-solid fa-envelope"></i>
                                                <input type="email" name="email" class="glass-input" value="<?php echo htmlspecialchars($profile->email); ?>" required>
                                            </div>
                                        </div>
                                        <div class="form-group half">
                                            <label class="form-label">Phone Number</label>
                                            <div class="input-wrapper">
                                                <i class="fa-solid fa-phone"></i>
                                                <input type="tel" name="phone" class="glass-input" value="<?php echo htmlspecialchars($profile->phone ?? ''); ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Physical Address</label>
                                        <div class="input-wrapper">
                                            <i class="fa-solid fa-location-dot"></i>
                                            <input type="text" name="address" class="glass-input" value="<?php echo htmlspecialchars($profile->agency_address ?? ''); ?>" readonly title="Managed by Agency Admin">
                                        </div>
                                    </div>
                                </div>
                            </div>



                            <!-- Agency Pane -->
                            <div class="settings-pane" id="agency-pane" style="display: none;">
                                <div class="card-panel glass form-section">
                                    <div class="section-badge"><i class="fa-solid fa-buildings"></i></div>
                                    <h3 class="section-title">Agency Information</h3>
                                    <p class="section-desc" style="margin-top: -12px; margin-bottom: 24px; opacity: 0.7;">View and verify the agency you are connected with.</p>
                                    
                                    <div style="background: rgba(107, 0, 182, 0.03); border: 1px solid var(--glass-border); border-radius: 20px; padding: 24px; margin-bottom: 32px; display: flex; align-items: center; gap: 24px;">
                                        <div style="width: 80px; height: 80px; border-radius: 16px; background: white; padding: 10px; box-shadow: var(--glass-shadow); border: 1px solid var(--glass-border); display: flex; align-items: center; justify-content: center;">
                                            <img src="../includes/assets/images/favicon.png" alt="Agency Logo" style="width: 100%; height: 100%; object-fit: contain;">
                                        </div>
                                        <div style="flex: 1;">
                                            <h4 style="font-size: 20px; font-weight: 800; color: var(--text-primary); margin-bottom: 4px;"><?php echo htmlspecialchars($profile->agency_name ?? 'Independent'); ?></h4>
                                            <div style="display: flex; gap: 12px; align-items: center;">
                                                <?php if($profile->agency_verified): ?>
                                                    <span class="badge-tag status-active" style="padding: 4px 10px; font-size: 11px;"><i class="fa-solid fa-circle-check"></i> Verified Agency</span>
                                                <?php endif; ?>
                                                <span style="font-size: 13px; color: var(--text-secondary); font-weight: 500;"><i class="fa-solid fa-users"></i> <?php echo $profile->team_count ?? 1; ?> Team Members</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group half">
                                            <label class="form-label">Company Name</label>
                                            <input type="text" class="glass-input readonly-field" value="<?php echo htmlspecialchars($profile->agency_name ?? 'N/A'); ?>" readonly title="Managed by Agency Admin">
                                        </div>
                                        <div class="form-group half">
                                            <label class="form-label">Agency ID</label>
                                            <input type="text" class="glass-input readonly-field" value="<?php echo htmlspecialchars($profile->agency_uid ?? 'N/A'); ?>" readonly title="Managed by Agency Admin">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Headquarters Address</label>
                                        <div class="input-wrapper">
                                            <i class="fa-solid fa-location-dot"></i>
                                            <input type="text" class="glass-input readonly-field" value="<?php echo htmlspecialchars($profile->agency_address ?? 'N/A'); ?>" readonly title="Managed by Agency Admin">
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group half">
                                            <label class="form-label">Status</label>
                                            <input type="text" class="glass-input readonly-field" value="<?php echo $profile->agency_verified ? 'Verified' : 'Pending'; ?>" readonly title="Managed by Agency Admin">
                                        </div>
                                        <div class="form-group half">
                                            <label class="form-label">Official Portal</label>
                                            <input type="text" class="glass-input readonly-field" value="https://landsfy.com/agency/<?php echo urlencode($profile->agency_name ?? 'independent'); ?>" readonly title="Managed by Agency Admin">
                                        </div>
                                    </div>

                                    <div style="background: rgba(245, 158, 11, 0.05); border: 1px solid rgba(245, 158, 11, 0.1); border-radius: 16px; padding: 16px; display: flex; gap: 12px; align-items: center;">
                                        <i class="fa-solid fa-lock" style="color: var(--warning); font-size: 20px;"></i>
                                        <p style="font-size: 13px; color: var(--text-secondary); margin: 0;">Agency information is <strong>Read-Only</strong>. Updates are managed by your Administrator.</p>
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
                </div>
            </form>
        </main>
    </div>

    <!-- External JS -->
    <script src="../includes/assets/js/script.js"></script>
    <script src="../includes/assets/js/agent/profile.js"></script>
</body>
</html>

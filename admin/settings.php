<?php
include 'header.php';

// Fetch current settings
$settings_stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
$raw_settings = $settings_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Default settings if empty
$defaults = [
    'site_name' => 'Landsfy Properties',
    'site_email' => 'support@landsfy.com',
    'site_phone' => '+92 300 1234567',
    'site_address' => 'DHA Phase 6, Lahore, Pakistan',
    'currency_code' => 'PKR',
    'facebook_url' => 'https://facebook.com/landsfy',
    'instagram_url' => 'https://instagram.com/landsfy',
    'linkedin_url' => 'https://linkedin.com/company/landsfy',
    'meta_description' => 'Landsfy is your ultimate destination for buying, selling, and renting properties in Pakistan.',
    'meta_keywords' => 'real estate, pakistan, lahore, karachi, islamabad, property for sale, rent property'
];

$settings = array_merge($defaults, $raw_settings);
?>

        <main class="main-content">
            <header class="header glass" style="margin-bottom: 30px;">
                <div class="header-left">
                    <div class="breadcrumb" style="font-size: 13px; color: var(--text-secondary); margin-bottom: 5px;">
                        Administration / Platform Settings
                    </div>
                    <div class="page-title">General Settings</div>
                </div>
                <div class="header-actions">
                    <button class="icon-btn" id="themeToggle" style="margin-right: 12px;"><i class="fa-solid fa-moon" id="themeIcon"></i></button>
                    <button class="btn-primary" id="saveSettingsBtn"><i class="fa-solid fa-floppy-disk"></i> Save All Changes</button>
                </div>
            </header>

            <div class="view-container">
                <form id="settingsForm">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: start;">
                        
                        <!-- Branding & Identity -->
                        <div class="card-panel glass" style="padding: 24px;">
                            <h3 class="section-title" style="font-size: 16px; margin-bottom: 20px;"><i class="fa-solid fa-identification-card" style="margin-right: 8px; color: var(--primary);"></i> Branding & Identity</h3>
                            
                            <div class="form-group">
                                <label class="form-label">Platform Name</label>
                                <input type="text" name="site_name" class="glass-input" value="<?php echo htmlspecialchars($settings['site_name']); ?>" placeholder="e.g. Landsfy Real Estate">
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                <div class="form-group">
                                    <label class="form-label">Primary Currency</label>
                                    <input type="text" name="currency_code" class="glass-input" value="<?php echo htmlspecialchars($settings['currency_code']); ?>" placeholder="e.g. PKR">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Support Email</label>
                                    <input type="email" name="site_email" class="glass-input" value="<?php echo htmlspecialchars($settings['site_email']); ?>" placeholder="support@site.com">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Support Phone</label>
                                <input type="text" name="site_phone" class="glass-input" value="<?php echo htmlspecialchars($settings['site_phone']); ?>" placeholder="+92 3XX XXXXXXX">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Office Address</label>
                                <textarea name="site_address" class="glass-input" rows="3" placeholder="Enter physical office address..."><?php echo htmlspecialchars($settings['site_address']); ?></textarea>
                            </div>
                        </div>

                        <!-- SEO & Social Media -->
                        <div style="display: flex; flex-direction: column; gap: 24px;">
                            
                            <!-- SEO Metadata -->
                            <div class="card-panel glass" style="padding: 24px;">
                                <h3 class="section-title" style="font-size: 16px; margin-bottom: 20px;"><i class="fa-solid fa-google-logo" style="margin-right: 8px; color: var(--success);"></i> SEO Metadata</h3>
                                
                                <div class="form-group">
                                    <label class="form-label">Meta Description</label>
                                    <textarea name="meta_description" class="glass-input" rows="3" placeholder="Platform description for search engines..."><?php echo htmlspecialchars($settings['meta_description']); ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Keywords (Comma separated)</label>
                                    <input type="text" name="meta_keywords" class="glass-input" value="<?php echo htmlspecialchars($settings['meta_keywords']); ?>" placeholder="real estate, property, homes...">
                                </div>
                            </div>

                            <!-- Social Links -->
                            <div class="card-panel glass" style="padding: 24px;">
                                <h3 class="section-title" style="font-size: 16px; margin-bottom: 20px;"><i class="fa-solid fa-share-nodes" style="margin-right: 8px; color: var(--info);"></i> Social Presence</h3>
                                
                                <div class="form-group">
                                    <div class="input-wrapper">
                                        <i class="fa-solid fa-facebook-logo" style="color: #1877F2;"></i>
                                        <input type="text" name="facebook_url" class="glass-input" value="<?php echo htmlspecialchars($settings['facebook_url']); ?>" placeholder="Facebook URL">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="input-wrapper">
                                        <i class="fa-solid fa-instagram-logo" style="color: #E4405F;"></i>
                                        <input type="text" name="instagram_url" class="glass-input" value="<?php echo htmlspecialchars($settings['instagram_url']); ?>" placeholder="Instagram URL">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="input-wrapper">
                                        <i class="fa-solid fa-linkedin-logo" style="color: #0A66C2;"></i>
                                        <input type="text" name="linkedin_url" class="glass-input" value="<?php echo htmlspecialchars($settings['linkedin_url']); ?>" placeholder="LinkedIn Company URL">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="../includes/assets/js/script.js"></script>
    <script>
        document.getElementById('saveSettingsBtn').onclick = async function() {
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin fa-spin"></i> Saving...';

            const form = document.getElementById('settingsForm');
            const formData = new FormData(form);

            try {
                const response = await fetch('../includes/api/admin/save_site_settings.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Site settings have been updated.',
                        icon: 'success',
                        background: '#1a1d21',
                        color: '#fff',
                        confirmButtonColor: '#6c5dd3'
                    });
                } else {
                    Swal.fire('Error', result.message, 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'Connection failed', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Save All Changes';
            }
        };
    </script>
</body>
</html>

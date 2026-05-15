document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.settings-nav-item');
    const panes = document.querySelectorAll('.settings-pane');
    const profileForm = document.getElementById('profileForm');
    const saveBtn = document.getElementById('saveProfileBtn');
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('profileAvatarPreview');

    // --- 1. Tab Switching Logic ---
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            
            // Update tabs
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Update panes
            panes.forEach(pane => {
                if (pane.id === targetId) {
                    pane.style.display = 'block';
                    pane.classList.add('active');
                } else {
                    pane.style.display = 'none';
                    pane.classList.remove('active');
                }
            });
        });
    });

    // --- 2. Avatar Preview Logic ---
    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    avatarPreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // --- 3. Form Submission Logic ---
    if (profileForm) {
        profileForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Prevent double submission
            if (saveBtn.disabled) return;

            // Simple validation
            const newPass = profileForm.querySelector('[name="new_password"]').value;
            const confirmPass = profileForm.querySelector('[name="confirm_password"]').value;
            if (newPass && newPass !== confirmPass) {
                showToast('New passwords do not match!', 'error');
                return;
            }

            // Prepare Data
            const formData = new FormData(this);
            const originalBtnContent = saveBtn.innerHTML;

            // UI Loading State
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin spinner"></i> Saving...';

            try {
                const response = await fetch('../includes/api/agent/update_profile.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Success Feedback
                    showToast(result.message || 'Profile updated successfully!', 'success');
                    
                    // If avatar changed, ensure sidebar avatar also updates (if present)
                    const sidebarAvatar = document.querySelector('.user-avatar');
                    if (sidebarAvatar && result.avatar_url) {
                        sidebarAvatar.src = result.avatar_url;
                    }
                    
                    // Reset password fields
                    profileForm.querySelector('[name="current_password"]').value = '';
                    profileForm.querySelector('[name="new_password"]').value = '';
                    profileForm.querySelector('[name="confirm_password"]').value = '';

                } else {
                    showToast('Error: ' + result.message, 'error');
                }
            } catch (error) {
                console.error('Update profile error:', error);
                showToast('An unexpected error occurred. Please try again.', 'error');
            } finally {
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalBtnContent;
            }
        });
    }
});

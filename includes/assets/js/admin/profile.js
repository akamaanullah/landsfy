/**
 * Admin Profile Logic
 */

document.addEventListener('DOMContentLoaded', function() {
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('profileAvatarPreview');
    const saveBtn = document.getElementById('saveProfileBtn');
    const profileForm = document.getElementById('adminProfileForm');

    // Avatar preview logic
    if (avatarInput) {
        avatarInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => avatarPreview.src = e.target.result;
                reader.readAsDataURL(file);
            }
        });
    }

    // Save Profile Logic
    if (saveBtn) {
        saveBtn.addEventListener('click', async () => {
            const formData = new FormData(profileForm);
            
            // Password validation
            const newPass = formData.get('new_password');
            const confirmPass = formData.get('confirm_password');
            if (newPass && newPass !== confirmPass) {
                return showToast('Passwords do not match!', 'error');
            }

            // Append avatar if selected
            if (avatarInput.files[0]) {
                formData.append('avatar', avatarInput.files[0]);
            }

            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin fa-spin"></i> Saving...';

            try {
                const response = await fetch('../includes/api/admin/update_profile.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    showToast('Profile updated successfully!', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(result.message || 'Failed to update profile', 'error');
                }
            } catch (error) {
                console.error(error);
                showToast('Something went wrong. Please try again.', 'error');
            } finally {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i> Save Changes';
            }
        });
    }
});

/**
 * Global theme setter that also syncs to the DB
 */
window.setTheme = async function(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    
    // Update active UI state on profile page
    const btns = document.querySelectorAll('.card-panel .btn-primary, .card-panel .btn-preview');
    btns.forEach(b => {
        if (b.innerText.toLowerCase().includes(theme)) {
            b.className = 'btn-primary';
        } else {
            b.className = 'btn-preview';
        }
    });

    // Sync to database
    try {
        const formData = new FormData();
        formData.append('setting_key', 'admin_theme');
        formData.append('setting_value', theme);
        await fetch('../includes/api/admin/save_setting.php', {
            method: 'POST',
            body: formData
        });
    } catch (e) {
        console.error('Theme sync failed', e);
    }
};

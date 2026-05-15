document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('addPropertyForm');
    const submitBtn = document.getElementById('submitPropertyBtn');

    // Agent Selection Logic
    const agentGroup = document.getElementById('agentSelectionGroup');
    const agentInput = document.getElementById('assignedAgentId');
    if (agentGroup && agentInput) {
        agentGroup.addEventListener('click', (e) => {
            const btn = e.target.closest('.chip-btn');
            if (btn) {
                agentGroup.querySelectorAll('.chip-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                agentInput.value = btn.getAttribute('data-value');
            }
        });
    }

    // Purpose Selection
    const purposeGroup = document.getElementById('purposeGroup');
    const purposeInput = document.getElementById('propertyPurpose');
    if (purposeGroup && purposeInput) {
        purposeGroup.addEventListener('click', (e) => {
            const btn = e.target.closest('.pill-btn');
            if (btn) {
                purposeGroup.querySelectorAll('.pill-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                purposeInput.value = btn.getAttribute('data-value');
            }
        });
    }

    // Type Tabs
    const typeGroup = document.getElementById('typeGroupMain');
    const subTypeHomes = document.getElementById('subTypeHome');
    const subTypePlots = document.getElementById('subTypePlot');
    const subTypeCommercial = document.getElementById('subTypeCommercial');

    if (typeGroup) {
        typeGroup.addEventListener('click', (e) => {
            const btn = e.target.closest('.type-tab-btn');
            if (btn) {
                typeGroup.querySelectorAll('.type-tab-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                const value = btn.getAttribute('data-value');
                [subTypeHomes, subTypePlots, subTypeCommercial].forEach(pane => {
                    if (pane) pane.style.display = 'none';
                });
                
                if (value === 'home') subTypeHomes.style.display = 'block';
                if (value === 'plot') subTypePlots.style.display = 'block';
                if (value === 'commercial') subTypeCommercial.style.display = 'block';
            }
        });
    }

    // Form Submission
    if (form && submitBtn) {
        submitBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            
            // Validate basic fields
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin spinner"></i> Posting...';

            const formData = new FormData(form);
            
            try {
                const response = await fetch('../includes/api/agency/add_agency_property.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast('Success', 'Property listed successfully!', 'success');
                    setTimeout(() => {
                        window.location.href = 'agency-listings.php';
                    }, 1500);
                } else {
                    showToast('Error', data.message || 'Failed to add property', 'error');
                }
            } catch (error) {
                console.error('Submission error:', error);
                showToast('Error', 'An unexpected error occurred.', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Post Property Listing';
            }
        });
    }
});

function showToast(title, message, type) {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast toast-${type} glass`;
    toast.innerHTML = `
        <div class="toast-content">
            <div class="toast-title">${title}</div>
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close"><i class="fa-solid fa-xmark"></i></button>
    `;

    container.appendChild(toast);
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s forwards';
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editPropertyForm');
    const updateBtn = document.getElementById('updatePropertyBtn');
    const removedImagesInput = document.getElementById('removedImagesInput');
    let removedImages = [];

    // Initialize Amenities
    const amenitiesInput = document.getElementById('propertyAmenitiesInput');
    const amenitiesDisplay = document.getElementById('selectedAmenitiesDisplay');
    let currentAmenities = JSON.parse(amenitiesInput.value || '{}');

    // Agent Selection
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

    // Circle Chips (Beds & Baths)
    function setupCircleChips(groupId, fieldId) {
        const group = document.getElementById(groupId);
        if (group) {
            group.addEventListener('click', (e) => {
                const btn = e.target.closest('.chip-btn');
                if (btn) {
                    group.querySelectorAll('.chip-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    const val = btn.getAttribute('data-value');
                    currentAmenities[fieldId] = val;
                    syncAmenities();
                }
            });
        }
    }
    setupCircleChips('bedsChipGroup', 3);
    setupCircleChips('bathsChipGroup', 4);

    // Amenities Modal Logic
    const amenitiesModal = document.getElementById('amenitiesModal');
    const openAmenitiesBtn = document.getElementById('openAmenitiesModalBtn');
    const closeAmenitiesBtn = document.getElementById('closeAmenitiesModalBtn');
    const cancelAmenitiesBtn = document.getElementById('cancelAmenitiesBtn');
    const saveAmenitiesBtn = document.getElementById('saveAmenitiesBtn');

    if (openAmenitiesBtn && amenitiesModal) {
        openAmenitiesBtn.addEventListener('click', () => {
            amenitiesModal.classList.add('active');
            // Sync inputs in modal with currentAmenities
            amenitiesModal.querySelectorAll('.amenity-checkbox').forEach(cb => {
                const id = cb.getAttribute('data-id');
                cb.checked = !!currentAmenities[id];
            });
            amenitiesModal.querySelectorAll('.amenity-input').forEach(input => {
                const id = input.getAttribute('data-id');
                input.value = currentAmenities[id] || '';
            });
        });

        const closeModal = () => amenitiesModal.classList.remove('active');
        closeAmenitiesBtn.addEventListener('click', closeModal);
        cancelAmenitiesBtn.addEventListener('click', closeModal);

        saveAmenitiesBtn.addEventListener('click', () => {
            // Gather values from modal
            amenitiesModal.querySelectorAll('.amenity-checkbox').forEach(cb => {
                const id = cb.getAttribute('data-id');
                if (cb.checked) {
                    currentAmenities[id] = true;
                } else {
                    delete currentAmenities[id];
                }
            });
            amenitiesModal.querySelectorAll('.amenity-input').forEach(input => {
                const id = input.getAttribute('data-id');
                if (input.value.trim() !== '') {
                    currentAmenities[id] = input.value.trim();
                } else if (id != 3 && id != 4) { // Don't delete beds/baths if empty here
                    delete currentAmenities[id];
                }
            });

            syncAmenities();
            closeModal();
            showToast('Success', 'Selection saved!', 'success');
        });

        // Tab Switching
        const tabBtns = document.querySelectorAll('.modal-tab-btn');
        const panes = document.querySelectorAll('.tab-pane');
        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                tabBtns.forEach(b => b.classList.remove('active'));
                panes.forEach(p => p.classList.remove('active'));
                btn.classList.add('active');
                document.getElementById(btn.getAttribute('data-target')).classList.add('active');
            });
        });
    }

    function syncAmenities() {
        amenitiesInput.value = JSON.stringify(currentAmenities);
        renderAmenitiesChips();
    }

    function renderAmenitiesChips() {
        if (!amenitiesDisplay) return;
        amenitiesDisplay.innerHTML = '';
        
        // We need labels. For now, we can use a lookup if we want, or just get from the checkboxes/inputs labels.
        // Actually, we can just find any amenity-item whose ID is in currentAmenities (excluding 3, 4)
        const labelsMap = {};
        document.querySelectorAll('.amenity-item').forEach(item => {
            const name = item.querySelector('.amenity-name').textContent;
            const input = item.querySelector('[data-id]');
            if (input) labelsMap[input.getAttribute('data-id')] = name;
        });

        let count = 0;
        for (const [id, val] of Object.entries(currentAmenities)) {
            if (id == 3 || id == 4) continue; // Skip beds/baths
            count++;
            const label = labelsMap[id] || `Amenity ${id}`;
            const displayVal = (val === true) ? '' : `: ${val}`;
            
            const chip = document.createElement('div');
            chip.className = 'chip-btn active';
            chip.style.pointerEvents = 'none';
            chip.innerHTML = `${label}${displayVal}`;
            amenitiesDisplay.appendChild(chip);
        }

        if (count === 0) {
            amenitiesDisplay.innerHTML = '<span style="opacity: 0.5; font-size: 13px;">No additional amenities selected.</span>';
        }
    }

    // Initial render
    renderAmenitiesChips();

    // Type Tabs
    const typeGroup = document.getElementById('typeGroupMain');
    const categoryInput = document.getElementById('categoryIdInput');
    const subtypeGroup = document.getElementById('subTypeGroup');
    const subtypeInput = document.getElementById('subtypeIdInput');

    if (typeGroup) {
        typeGroup.addEventListener('click', (e) => {
            const btn = e.target.closest('.type-tab-btn');
            if (btn) {
                typeGroup.querySelectorAll('.type-tab-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                // Category logic would go here
            }
        });
    }

    if (subtypeGroup) {
        subtypeGroup.addEventListener('click', (e) => {
            const btn = e.target.closest('.chip-btn');
            if (btn) {
                subtypeGroup.querySelectorAll('.chip-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                subtypeInput.value = btn.getAttribute('data-value');
            }
        });
    }

    // Image Removal
    document.querySelectorAll('.remove-existing-img').forEach(btn => {
        btn.addEventListener('click', function() {
            const item = this.closest('.image-preview-item');
            const id = item.getAttribute('data-id');
            removedImages.push(id);
            removedImagesInput.value = JSON.stringify(removedImages);
            item.style.opacity = '0.3';
            item.style.pointerEvents = 'none';
        });
    });

    // Form Submission
    if (form && updateBtn) {
        updateBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            updateBtn.disabled = true;
            updateBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin spinner"></i> Saving...';

            const formData = new FormData(form);
            
            const fileInput = document.getElementById('fileInput');
            if (fileInput && fileInput.files.length > 0) {
                for (let i = 0; i < fileInput.files.length; i++) {
                    formData.append('images[]', fileInput.files[i]);
                }
            }

            try {
                const response = await fetch('../includes/api/agency/update_agency_property.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast('Success', 'Property updated successfully!', 'success');
                    setTimeout(() => {
                        window.location.href = 'agency-listings.php';
                    }, 1500);
                } else {
                    showToast('Error', data.message || 'Failed to update property', 'error');
                }
            } catch (error) {
                console.error('Update error:', error);
                showToast('Error', 'An unexpected error occurred.', 'error');
            } finally {
                updateBtn.disabled = false;
                updateBtn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Save Changes';
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

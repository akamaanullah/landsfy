document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('addPropertyForm');
    const imageDropzone = document.getElementById('imageDropzone');
    const fileInput = document.getElementById('fileInput');
    const selectedFiles = new Set();
    const submitBtn = document.getElementById('submitPropertyBtn');

    // --- Dynamic UI Synchronization ---
    const categoryIdInput = document.getElementById('categoryIdInput');
    const subtypeIdInput = document.getElementById('subtypeIdInput');
    const typeGroupMain = document.getElementById('typeGroupMain');
    const bedsBathsSection = document.getElementById('bedsBathsSection');
    
    // Initialize price in words display
    if (window.Landsfy && window.Landsfy.initPriceInWords) {
        Landsfy.initPriceInWords('#propertyPriceInput', '#priceInWords');
    }

    const toggleBedsBaths = (categorySlug) => {
        if (bedsBathsSection) {
            if (categorySlug === 'plots') {
            if (bedsBathsSection) bedsBathsSection.style.display = 'none';
                document.getElementById('bedsInput').value = '0';
                document.getElementById('bathsInput').value = '0';
            } else {
            if (bedsBathsSection) {
                bedsBathsSection.style.display = 'flex';
                bedsBathsSection.style.opacity = '1';
            }
                if (document.getElementById('bedsInput').value == '0') {
                    document.getElementById('bedsInput').value = '3';
                    document.getElementById('bathsInput').value = '2';
                }
            }
        }
    };

    if (typeGroupMain) {
        typeGroupMain.addEventListener('click', (e) => {
            const btn = e.target.closest('.type-tab-btn');
            if (btn) {
                const categorySlug = btn.dataset.value;
                categoryIdInput.value = btn.dataset.id;
                
                document.querySelectorAll('.hidden-sub-type').forEach(c => c.style.display = 'none');
                const targetSubTypeContainer = document.getElementById('subType' + categorySlug.charAt(0).toUpperCase() + categorySlug.slice(1));
                if (targetSubTypeContainer) {
                    targetSubTypeContainer.style.display = 'block';
                    const firstChip = targetSubTypeContainer.querySelector('.chip-btn');
                    if (firstChip) {
                        targetSubTypeContainer.querySelectorAll('.chip-btn').forEach(b => b.classList.remove('active'));
                        firstChip.classList.add('active');
                        subtypeIdInput.value = firstChip.dataset.id;
                    }
                }
                toggleBedsBaths(categorySlug);
            }
        });
    }

    document.querySelectorAll('.hidden-sub-type').forEach(container => {
        container.addEventListener('click', (e) => {
            const btn = e.target.closest('.chip-btn');
            if (btn) {
                container.querySelectorAll('.chip-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                subtypeIdInput.value = btn.dataset.id;
            }
        });
    });

    // --- Listing Tier Interaction ---
    const tierCards = document.querySelectorAll('.tier-card');
    const premiumTypeInput = document.getElementById('premiumTypeInput');

    tierCards.forEach(card => {
        card.addEventListener('click', function() {
            tierCards.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            premiumTypeInput.value = this.dataset.value;
        });
    });

    // --- Image Gallery & Dropzone Logic ---
    const renderGallery = () => {
        const content = imageDropzone.querySelector('.dropzone-content');
        if (selectedFiles.size === 0) {
            content.innerHTML = `
                <i class="fa-solid fa-cloud-arrow-up upload-icon"></i>
                <h4 class="upload-text">Drag & Drop Property Images</h4>
                <p class="upload-hint">Support JPEG, PNG, WEBP (Max 5MB each)</p>
            `;
            return;
        }

        content.innerHTML = '<div class="gallery-grid"></div>';
        const grid = content.querySelector('.gallery-grid');

        selectedFiles.forEach(file => {
            const reader = new FileReader();
            const item = document.createElement('div');
            item.className = 'gallery-item';
            
            reader.onload = (e) => {
                item.innerHTML = `
                    <img src="${e.target.result}" alt="Preview">
                    <button type="button" class="remove-img-btn"><i class="fa-solid fa-xmark"></i></button>
                `;
                item.querySelector('.remove-img-btn').addEventListener('click', (evt) => {
                    evt.stopPropagation();
                    selectedFiles.delete(file);
                    renderGallery();
                });
            };
            reader.readAsDataURL(file);
            grid.appendChild(item);
        });
    };

    imageDropzone.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', function() {
        Array.from(this.files).forEach(file => selectedFiles.add(file));
        renderGallery();
        this.value = '';
    });

    // --- Global Helper ---
    window.copyToClipboard = (text, label) => {
        navigator.clipboard.writeText(text).then(() => {
            Swal.showValidationMessage(`${label} copied to clipboard!`);
            setTimeout(() => Swal.resetValidationMessage(), 2000);
        });
    };

    // --- Payment Details Modal WITH Upload ---
    const showPaymentModal = (tier) => {
        const pricing = tier === 'platinum' ? '4,999' : '12,999';
        const rawAmount = tier === 'platinum' ? 4999 : 12999;
        const title = tier.charAt(0).toUpperCase() + tier.slice(1);
        
        Swal.fire({
            title: `<div style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 24px; color: var(--primary);">Purchase ${title} Quota</div>`,
            html: `
                <div class="payment-modal-content" style="text-align: left; font-family: 'Outfit', sans-serif;">
                    <p style="margin-bottom: 24px; color: #64748b; font-size: 14px; line-height: 1.6;">Transfer the amount to the bank below, then upload the screenshot to get your credit.</p>
                    
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 20px; padding: 20px; margin-bottom: 24px;">
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <div style="display: flex; justify-content: space-between;">
                                <span style="font-size: 12px; color: #94a3b8;">Bank</span>
                                <span style="font-weight: 700; color: #1e293b;">UBL</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="font-size: 12px; color: #94a3b8;">Account #</span>
                                <span style="font-weight: 800; color: #1e293b; cursor: pointer;" onclick="copyToClipboard('340804771', 'Account Number')">340804771 <i class="fa-solid fa-copy" style="font-size: 12px;"></i></span>
                            </div>
                             <div style="display: flex; justify-content: space-between;">
                                <span style="font-size: 12px; color: #94a3b8;">IBAN</span>
                                <span style="font-weight: 800; color: #1e293b; font-size: 12px; cursor: pointer;" onclick="copyToClipboard('PK91UNIL0109000340804771', 'IBAN')">PK91...4771 <i class="fa-solid fa-copy" style="font-size: 12px;"></i></span>
                            </div>
                             <div style="display: flex; justify-content: space-between; border-top: 1px solid #e2e8f0; padding-top: 10px;">
                                <span style="font-size: 12px; color: #94a3b8;">Amount</span>
                                <span style="font-weight: 900; color: var(--primary);">PKR ${pricing}/-</span>
                            </div>
                        </div>
                    </div>

                    <div class="upload-section" style="margin-bottom: 24px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: #1e293b; margin-bottom: 8px;">UPLOAD PAYMENT SCREENSHOT</label>
                        <div id="swalDropzone" style="border: 2px dashed #cbd5e1; border-radius: 12px; padding: 20px; text-align: center; cursor: pointer; transition: all 0.2s;">
                            <i class="fa-solid fa-cloud-arrow-up" style="font-size: 32px; color: #94a3b8;"></i>
                            <p id="swalFileLabel" style="font-size: 12px; color: #64748b; margin-top: 5px;">Click or drag receipt here</p>
                            <input type="file" id="swalFileInput" accept="image/*" style="display: none;">
                        </div>
                    </div>

                    <div style="background: #ecfdf5; border: 1px solid #10b98133; border-radius: 16px; padding: 12px; display: flex; align-items: center; gap: 12px;">
                        <i class="fa-brands fa-whatsapp" style="font-size: 24px; color: #10b981;"></i>
                        <div style="flex: 1;">
                            <div style="font-size: 10px; color: #065f46; font-weight: 700;">SUPPORT WHATSAPP</div>
                            <div style="font-weight: 800; color: #065f46; font-size: 14px;">0318 2923525</div>
                        </div>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Submit Proof',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#6b00b6',
            reverseButtons: true,
            didOpen: () => {
                const dz = document.getElementById('swalDropzone');
                const fi = document.getElementById('swalFileInput');
                const lb = document.getElementById('swalFileLabel');

                dz.onclick = () => fi.click();
                fi.onchange = () => {
                    if (fi.files[0]) {
                        lb.innerText = fi.files[0].name;
                        lb.style.color = 'var(--primary)';
                        dz.style.borderColor = 'var(--primary)';
                    }
                };
            },
            preConfirm: () => {
                const file = document.getElementById('swalFileInput').files[0];
                if (!file) {
                    Swal.showValidationMessage('Please upload the payment screenshot first');
                    return false;
                }
                return { file, tier, amount: rawAmount };
            }
        }).then(async (result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('screenshot', result.value.file);
                formData.append('tier', result.value.tier);
                formData.append('amount', result.value.amount);

                Swal.fire({ title: 'Submitting...', didOpen: () => Swal.showLoading() });

                try {
                    const res = await fetch('../includes/api/agent/submit_premium_request.php', { method: 'POST', body: formData });
                    const data = await res.json();
                    if (data.success) {
                        Swal.fire({ 
                            icon: 'success', 
                            title: '<span style="color: #10B981;">Request Received</span>', 
                            html: '<div style="color: #64748b; font-size: 14px;">Your payment proof has been submitted for verification. Your credits will be added once approved.</div>',
                            confirmButtonColor: '#6b00b6',
                            background: '#fff'
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Failed', text: data.message });
                    }
                } catch (err) {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Connection error. Please try again.' });
                }
            }
        });
    };

    // --- Form Submission ---
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (submitBtn.classList.contains('loading')) return;

        const premiumType = premiumTypeInput.value;
        
        // Quota Check
        if (premiumType !== 'none') {
            try {
                submitBtn.classList.add('loading');
                const qResponse = await fetch('../includes/api/agent/get_quota.php');
                const qData = await qResponse.json();
                submitBtn.classList.remove('loading');

                if (qData.success) {
                    const available = qData.data[premiumType].available;
                    if (available <= 0) {
                        showPaymentModal(premiumType);
                        return;
                    }
                }
            } catch (err) {
                console.error("Quota check failed", err);
            }
        }

        const formData = new FormData(this);
        selectedFiles.forEach(file => formData.append('property_images[]', file));
        
        submitBtn.classList.add('loading');
        submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin spinner"></i> Submitting...';

        try {
            const r = await fetch('../includes/api/agent/add_property.php', { method: 'POST', body: formData });
            const res = await r.json();
            if (res.success) {
                window.location.href = 'my-listings.php?msg=success';
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: res.message, confirmButtonColor: '#6b00b6' });
                submitBtn.classList.remove('loading');
                submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Submit Property';
            }
        } catch (err) {
            console.error(err);
            submitBtn.classList.remove('loading');
            submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Submit Property';
        }
    });

    // --- Location Autosuggestion Logic ---
    const locationInput = document.getElementById('locationInput');
    const suggestionsList = document.getElementById('locationSuggestions');
    const citySelect = document.querySelector('select[name="city_id"]');

    if (locationInput && suggestionsList) {
        let debounceTimer;

        locationInput.addEventListener('input', function() {
            const query = this.value.trim();
            const cityId = citySelect ? citySelect.value : null;

            clearTimeout(debounceTimer);
            if (query.length < 2 || !cityId) {
                suggestionsList.style.display = 'none';
                return;
            }

            debounceTimer = setTimeout(async () => {
                try {
                    const response = await fetch(`../includes/api/agent/get_locations.php?city_id=${cityId}&search=${encodeURIComponent(query)}`);
                    const result = await response.json();

                    if (result.success && result.data.length > 0) {
                        suggestionsList.innerHTML = result.data.map(loc => `
                            <div class="suggestion-item" data-id="${loc.id}" data-name="${loc.name}">
                                <i class="fa-solid fa-location-dot"></i> ${loc.name}
                            </div>
                        `).join('');
                        suggestionsList.style.display = 'block';
                    } else {
                        suggestionsList.style.display = 'none';
                    }
                } catch (error) {
                    console.error('Location search failed:', error);
                }
            }, 300);
        });

        // Selection Logic
        suggestionsList.addEventListener('click', function(e) {
            const item = e.target.closest('.suggestion-item');
            if (item) {
                locationInput.value = item.dataset.name;
                const locIdInput = document.getElementById('locationIdInput');
                if (locIdInput) locIdInput.value = item.dataset.id;
                suggestionsList.style.display = 'none';
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!locationInput.contains(e.target) && !suggestionsList.contains(e.target)) {
                suggestionsList.style.display = 'none';
            }
        });
    }

    // --- Edit Mode Initialization ---
    const initializeEditMode = () => {
        if (!window.editModeData || !window.editModeData.isEdit) return;

        const { property, images, amenities, contacts } = window.editModeData;
        const imageGallery = document.getElementById('imageGallery');
        const deletedImagesInput = document.getElementById('deletedImagesInput');
        let deletedImageIds = [];

        // 1. Render Existing Images
        if (images && images.length > 0 && imageGallery) {
            images.forEach(img => {
                const item = document.createElement('div');
                item.className = 'gallery-item';
                const fullPath = img.image_url.startsWith('http') ? img.image_url : '../' + img.image_url;
                
                item.innerHTML = `
                    <img src="${fullPath}" alt="Existing Image">
                    <button type="button" class="remove-img-btn" data-id="${img.id}"><i class="fa-solid fa-xmark"></i></button>
                `;

                item.querySelector('.remove-img-btn').addEventListener('click', function() {
                    const id = this.dataset.id;
                    deletedImageIds.push(id);
                    if (deletedImagesInput) deletedImagesInput.value = JSON.stringify(deletedImageIds);
                    item.remove();
                });

                imageGallery.appendChild(item);
            });
        }

        // 2. Populate Amenities (Already handled by PHP but we can sync JS state if needed)
        // ... (Usually amenities are pre-checked in PHP)

        // 3. Handle Other Edit State Syncs if necessary
    };

    initializeEditMode();
});

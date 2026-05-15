document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('addPropertyForm');
    const imageDropzone = document.getElementById('imageDropzone');
    const fileInput = document.getElementById('fileInput');
    const selectedFiles = new Set();
    const submitBtn = document.getElementById('submitPropertyBtn');

    // --- 1. Category & Subtype Logic ---
    const typeGroupMain = document.getElementById('typeGroupMain');
    const categoryIdInput = document.getElementById('categoryIdInput');
    const subtypeIdInput = document.getElementById('subtypeIdInput');

    if (typeGroupMain) {
        typeGroupMain.addEventListener('click', (e) => {
            const btn = e.target.closest('.type-tab-btn');
            if (btn) {
                typeGroupMain.querySelectorAll('.type-tab-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                categoryIdInput.value = btn.dataset.id;

                // Show Correct SubType Container
                document.querySelectorAll('.hidden-sub-type').forEach(c => c.style.display = 'none');
                const subTypeContainer = document.getElementById('subType' + btn.dataset.value.charAt(0).toUpperCase() + btn.dataset.value.slice(1));
                if (subTypeContainer) {
                    subTypeContainer.style.display = 'flex';
                    const firstChip = subTypeContainer.querySelector('.chip-btn');
                    if (firstChip) {
                        subTypeContainer.querySelectorAll('.chip-btn').forEach(b => b.classList.remove('active'));
                        firstChip.classList.add('active');
                        subtypeIdInput.value = firstChip.dataset.id;
                    }
                }
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

    // --- 2. Purpose Toggle ---
    const purposeGroup = document.getElementById('purposeGroup');
    const purposeInput = document.getElementById('propertyPurpose');
    if (purposeGroup) {
        purposeGroup.addEventListener('click', (e) => {
            const btn = e.target.closest('.pill-btn');
            if (btn) {
                purposeGroup.querySelectorAll('.pill-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                purposeInput.value = btn.dataset.value;
            }
        });
    }

    // --- 3. Location Auto-Suggest ---
    const locationInput = document.getElementById('locationInput');
    const citySelect = document.querySelector('[name="city_id"]');
    const suggestionsList = document.getElementById('locationSuggestions');
    let suggestionTimeout;

    if (locationInput) {
        locationInput.addEventListener('input', function() {
            clearTimeout(suggestionTimeout);
            const query = this.value.trim();
            const cityId = citySelect.value;
            if (query.length < 2) { suggestionsList.style.display = 'none'; return; }

            suggestionTimeout = setTimeout(() => {
                fetch(`../includes/api/agent/get_locations.php?city_id=${cityId}&search=${encodeURIComponent(query)}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.success && data.data.length > 0) {
                            suggestionsList.innerHTML = data.data.map(loc => `
                                <div class="suggestion-item"><i class="fa-solid fa-location-dot"></i> ${loc.name}</div>
                            `).join('');
                            suggestionsList.style.display = 'block';
                        } else { suggestionsList.style.display = 'none'; }
                    });
            }, 300);
        });

        suggestionsList.addEventListener('click', (e) => {
            const item = e.target.closest('.suggestion-item');
            if (item) {
                locationInput.value = item.innerText.trim();
                suggestionsList.style.display = 'none';
            }
        });

        document.addEventListener('click', (e) => {
            if (!locationInput.contains(e.target)) suggestionsList.style.display = 'none';
        });
    }

    // --- 4. Amenities Modal Logic ---
    const modal = document.getElementById('amenitiesModal');
    const openBtn = document.getElementById('openAmenitiesModalBtn');
    const closeBtn = document.getElementById('closeAmenitiesModalBtn');
    const saveBtn = document.getElementById('saveAmenitiesBtn');
    const selectedDisplay = document.getElementById('selectedAmenitiesDisplay');
    const amenitiesInput = document.getElementById('propertyAmenitiesInput');

    if (openBtn) {
        openBtn.addEventListener('click', () => modal.style.display = 'flex');
        closeBtn.addEventListener('click', () => modal.style.display = 'none');
        
        // Tab switching
        modal.querySelectorAll('.modal-tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                modal.querySelectorAll('.modal-tab-btn').forEach(b => b.classList.remove('active'));
                modal.querySelectorAll('.tab-pane').forEach(p => p.style.display = 'none');
                btn.classList.add('active');
                document.getElementById(btn.dataset.target).style.display = 'grid';
            });
        });

        saveBtn.addEventListener('click', () => {
            const selected = [];
            modal.querySelectorAll('.amenity-checkbox:checked').forEach(cb => {
                selected.push({ id: cb.dataset.id, label: cb.dataset.label });
            });
            amenitiesInput.value = JSON.stringify(selected);
            selectedDisplay.innerHTML = selected.map(s => `<div class="amenity-chip" style="background: rgba(107,0,182,0.05); padding: 6px 12px; border-radius: 20px; font-size: 13px; color: var(--primary);"><i class="fa-solid fa-circle-check"></i> ${s.label}</div>`).join('');
            modal.style.display = 'none';
        });
    }

    // --- 5. Image & Gallery Logic ---
    const gallery = document.getElementById('imageGallery');
    const renderGallery = () => {
        gallery.innerHTML = '';
        selectedFiles.forEach((file, idx) => {
            const reader = new FileReader();
            const item = document.createElement('div');
            item.className = 'gallery-item';
            reader.onload = (e) => {
                item.innerHTML = `
                    <img src="${e.target.result}">
                    <button type="button" class="remove-img-btn" data-idx="${idx}"><i class="fa-solid fa-xmark"></i></button>
                `;
                item.querySelector('.remove-img-btn').addEventListener('click', () => {
                    selectedFiles.delete(file);
                    renderGallery();
                });
            };
            reader.readAsDataURL(file);
            gallery.appendChild(item);
        });
    };

    imageDropzone.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', function() {
        Array.from(this.files).forEach(f => selectedFiles.add(f));
        renderGallery();
    });

    // --- 6. Form Submission ---
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (submitBtn.classList.contains('loading')) return;

        const formData = new FormData(this);
        selectedFiles.forEach(f => formData.append('property_images[]', f));

        submitBtn.classList.add('loading');
        submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin spinner"></i> Posting...';

        try {
            const res = await fetch('../includes/api/agent/add_property.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                window.location.href = 'listings.php?status=success';
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#6b00b6' });
                submitBtn.classList.remove('loading');
                submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> POST AD';
            }
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Oops!', text: 'Something went wrong. Please try again.', confirmButtonColor: '#6b00b6' });
            submitBtn.classList.remove('loading');
        }
    });

    // --- 7. Edit Mode Loaders ---
    if (window.editModeData && window.editModeData.isEdit) {
        const p = window.editModeData.property;
        // Pre-fill complex logic here if needed (Category, Subtype, etc)
    }
});

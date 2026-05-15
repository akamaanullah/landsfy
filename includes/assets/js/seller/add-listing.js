document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const imageDropzone = document.getElementById('imageDropzone');
    const fileInput = document.getElementById('fileInput');
    const selectedFiles = new Set();
    const submitBtn = document.getElementById('submitPropertyBtn');

    // --- 1. Dynamic UI Synchronization ---

    // Sync Bedrooms/Bathrooms
    document.querySelectorAll('.circle-chips').forEach(container => {
        container.addEventListener('click', (e) => {
            const btn = e.target.closest('.chip-btn');
            if (btn) {
                const label = container.previousElementSibling.innerText.trim();
                if (label === 'Bedrooms') document.getElementById('bedsInput').value = btn.innerText.trim();
                if (label === 'Bathrooms') document.getElementById('bathsInput').value = btn.innerText.trim();
            }
        });
    });

    // Sync Category ID & Subtype UI
    const typeGroupMain = document.getElementById('typeGroupMain');
    const categoryIdInput = document.getElementById('categoryIdInput');
    const subtypeIdInput = document.getElementById('subtypeIdInput');

    if (typeGroupMain) {
        typeGroupMain.addEventListener('click', (e) => {
            const btn = e.target.closest('.type-tab-btn');
            if (btn) {
                const type = btn.dataset.value;
                categoryIdInput.value = btn.dataset.id;
                
                // Toggle Subtype Containers
                document.querySelectorAll('.hidden-sub-type').forEach(c => c.style.display = 'none');
                const targetContainer = document.getElementById('subType' + type.charAt(0).toUpperCase() + type.slice(1));
                if (targetContainer) {
                    targetContainer.style.display = 'flex';
                    // Auto-select first chip
                    const firstChip = targetContainer.querySelector('.chip-btn');
                    if (firstChip) {
                        targetContainer.querySelectorAll('.chip-btn').forEach(b => b.classList.remove('active'));
                        firstChip.classList.add('active');
                        subtypeIdInput.value = firstChip.dataset.id;
                    }
                }
            }
        });
    }

    // Sync Subtype ID Selection
    document.querySelectorAll('.hidden-sub-type').forEach(container => {
        container.addEventListener('click', (e) => {
            const btn = e.target.closest('.chip-btn');
            if (btn) {
                subtypeIdInput.value = btn.dataset.id;
            }
        });
    });

    // --- 2. Image Gallery & Dropzone Logic ---

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

        content.innerHTML = '<div class="gallery-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 12px; width: 100%;"></div>';
        const grid = content.querySelector('.gallery-grid');

        selectedFiles.forEach(file => {
            const reader = new FileReader();
            const item = document.createElement('div');
            item.className = 'gallery-item';
            item.style.position = 'relative';
            
            reader.onload = (e) => {
                item.innerHTML = `
                    <img src="${e.target.result}" style="width: 100%; height: 100px; object-fit: cover; border-radius: 12px;">
                    <button type="button" class="remove-img-btn" style="position: absolute; top: 4px; right: 4px; background: rgba(239, 68, 68, 0.9); color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-xmark"></i></button>
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

    // --- 3. Location Auto-Suggest ---
    const locationInput = document.getElementById('locationInput');
    const citySelect = document.querySelector('[name="property_city"]');
    const suggestionsList = document.getElementById('locationSuggestions');
    let suggestionTimeout;

    if (locationInput) {
        locationInput.addEventListener('input', function() {
            clearTimeout(suggestionTimeout);
            const query = this.value.trim();
            const cityId = citySelect.value;

            if (query.length < 2 || !cityId) {
                suggestionsList.style.display = 'none';
                return;
            }

            suggestionTimeout = setTimeout(() => {
                fetch(`../includes/api/agent/get_locations.php?city_id=${cityId}&search=${encodeURIComponent(query)}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.success && data.data.length > 0) {
                            suggestionsList.innerHTML = data.data.map(loc => `
                                <div class="suggestion-item" data-id="${loc.id}" style="padding: 12px 16px; cursor: pointer; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid var(--glass-border);">
                                    <i class="fa-solid fa-location-dot"></i> ${loc.name}
                                </div>
                            `).join('');
                            suggestionsList.style.display = 'block';
                        } else {
                            suggestionsList.style.display = 'none';
                        }
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
    }

    // --- 4. Form Submission ---
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        if (submitBtn.classList.contains('loading')) return;

        const formData = new FormData(this);
        selectedFiles.forEach(file => formData.append('property_images[]', file));

        submitBtn.classList.add('loading');
        const originalContent = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin spinner"></i> Posting...';

        try {
            const response = await fetch('../includes/api/agent/add_property.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                window.location.href = 'listings.php?status=success';
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: result.message, confirmButtonColor: '#6b00b6' });
                submitBtn.classList.remove('loading');
                submitBtn.innerHTML = originalContent;
            }
        } catch (error) {
            console.error('Submission Error:', error);
            Swal.fire({ icon: 'error', title: 'Oops!', text: 'Something went wrong. Please try again.', confirmButtonColor: '#6b00b6' });
            submitBtn.classList.remove('loading');
            submitBtn.innerHTML = originalContent;
        }
    });

    // Amenity Modal Logic (Generic)
    const openBtn = document.getElementById('openAmenitiesModalBtn');
    const closeBtn = document.getElementById('closeAmenitiesModalBtn');
    const modal = document.getElementById('amenitiesModal');

    if (openBtn && modal) {
        openBtn.onclick = () => modal.style.display = 'flex';
    }
    if (closeBtn && modal) {
        closeBtn.onclick = () => modal.style.display = 'none';
    }
});

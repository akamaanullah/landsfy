/**
 * Admin Add Property - Dynamic Listing Logic
 */

let requirementData = null;
let activeCategoryId = null;
let selectedAmenities = {}; // {field_id: value}
let selectedFiles = [];

document.addEventListener('DOMContentLoaded', async function() {
    await fetchRequirements();
    setupEventListeners();
    setupLocationAutocomplete();

    if (typeof EDIT_PROPERTY_ID !== 'undefined' && EDIT_PROPERTY_ID) {
        await initEditMode(EDIT_PROPERTY_ID);
    }

    // Initialize price in words display
    if (window.Landsfy && window.Landsfy.initPriceInWords) {
        Landsfy.initPriceInWords('#propertyPriceInput', '#priceInWords');
    }
});

async function fetchRequirements() {
    try {
        const res = await fetch('../includes/api/admin/get_form_requirements.php');
        const result = await res.json();
        if (result.success) {
            requirementData = result.data;
            renderCategories();
            renderCities();
            // Set default category ONLY if not in edit mode
            if (!requirementData.categories.length > 0) return;
            if (typeof EDIT_PROPERTY_ID === 'undefined' || !EDIT_PROPERTY_ID) {
                switchCategory(requirementData.categories[0].id);
            }
        }
    } catch (e) {
        console.error("Failed to fetch requirements", e);
    }
}

function renderCategories() {
    const container = document.getElementById('categoryTabs');
    if (!container) return;

    container.innerHTML = requirementData.categories.map(cat => `
        <button type="button" class="type-tab-btn ${activeCategoryId == cat.id ? 'active' : ''}" 
                onclick="switchCategory(${cat.id})" data-slug="${cat.slug}">
            ${cat.name}
        </button>
    `).join('');
}

window.switchCategory = function(catId) {
    activeCategoryId = catId;
    document.getElementById('categoryId').value = catId;
    
    // Update active UI
    document.querySelectorAll('.type-tab-btn').forEach(btn => {
        btn.classList.toggle('active', btn.getAttribute('onclick').includes(catId));
    });

    renderSubTypes(catId);
    renderAmenitiesModal(); // Pre-load modal content for this context
    renderMainDynamicFields(); // Beds/Baths if Home
};

function renderSubTypes(catId) {
    const list = document.getElementById('subTypeList');
    if (!list) return;

    const subTypes = requirementData.subtypes[catId] || [];
    list.innerHTML = subTypes.map(type => `
        <button type="button" class="chip-btn" onclick="selectSubType(${type.id}, this)">
            <i class="fa-solid ${type.icon_class}"></i> ${type.name}
        </button>
    `).join('');
    
    if (subTypes.length > 0) selectSubType(subTypes[0].id, list.querySelector('.chip-btn'));
}

window.selectSubType = function(id, btn) {
    document.getElementById('subtypeId').value = id;
    document.querySelectorAll('#subTypeList .chip-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
};

function renderCities() {
    const select = document.getElementById('citySelect');
    if (!select) return;

    requirementData.cities.forEach(city => {
        const opt = document.createElement('option');
        opt.value = city.id;
        opt.textContent = city.name;
        select.appendChild(opt);
    });
}

function setupLocationAutocomplete() {
    const locationInput = document.getElementById('locationInput');
    const citySelect = document.getElementById('citySelect');
    const suggestionsList = document.getElementById('locationSuggestions');
    const locationIdInput = document.getElementById('locationId');
    let suggestionTimeout;

    if (!locationInput) return;

    locationInput.addEventListener('input', function() {
        clearTimeout(suggestionTimeout);
        const query = this.value.trim();
        const cityId = citySelect.value;

        if (query.length < 1 || !cityId) {
            suggestionsList.style.display = 'none';
            return;
        }

        suggestionTimeout = setTimeout(() => {
            fetch(`../includes/api/admin/get_locations.php?city_id=${cityId}&search=${encodeURIComponent(query)}`)
                .then(r => r.json())
                .then(result => {
                    if (result.success && result.data.length > 0) {
                        suggestionsList.innerHTML = result.data.map(loc => `
                            <div class="suggestion-item" data-id="${loc.id}" data-name="${loc.name}">
                                <i class="fa-solid fa-location-dot"></i> ${loc.name}
                            </div>
                        `).join('');
                        suggestionsList.style.display = 'block';
                    } else {
                        suggestionsList.innerHTML = '<div class="suggestion-item" style="pointer-events:none; opacity:0.6;">No locations found</div>';
                        suggestionsList.style.display = 'block';
                    }
                });
        }, 300);
    });

    // Handle Selection
    suggestionsList.addEventListener('click', (e) => {
        const item = e.target.closest('.suggestion-item');
        if (item && item.dataset.name) {
            locationInput.value = item.dataset.name;
            locationIdInput.value = item.dataset.id || '';
            suggestionsList.style.display = 'none';
        }
    });

    // Close on blur/outside
    document.addEventListener('click', (e) => {
        if (!locationInput.contains(e.target) && !suggestionsList.contains(e.target)) {
            suggestionsList.style.display = 'none';
        }
    });

    // Clear location if city changes
    citySelect.addEventListener('change', () => {
        locationInput.value = '';
        locationIdInput.value = '';
        suggestionsList.style.display = 'none';
    });
}

function renderMainDynamicFields() {
    const container = document.getElementById('dynamicMainFields');
    const cat = requirementData.categories.find(c => c.id == activeCategoryId);
    
    if (!cat) return;
    
    // If category is 'home', show Beds/Baths from number_group types found in requirementData.amenity_fields
    if (cat.slug === 'home') {
        const beds = requirementData.amenity_fields.find(f => f.label.toLowerCase() === 'bedrooms');
        const baths = requirementData.amenity_fields.find(f => f.label.toLowerCase() === 'bathrooms');
        
        let html = '<div class="form-row">';
        if (beds) html += renderChoiceGroup(beds);
        if (baths) html += renderChoiceGroup(baths);
        html += '</div>';
        container.innerHTML = html;
    } else {
        container.innerHTML = '';
    }
}

function renderChoiceGroup(field) {
    const options = JSON.parse(field.options || '[]');
    const currentValue = selectedAmenities[field.id]; // Get currently selected value
    
    return `
        <div class="form-group half" style="flex: 1;">
            <label class="form-label">${field.label}</label>
            <div class="chip-group circle-chips" style="flex-wrap: wrap; gap: 8px;">
                ${options.map(opt => `
                    <button type="button" 
                            class="chip-btn circle ${currentValue == opt ? 'active' : ''}" 
                            onclick="setAmenityValue(${field.id}, '${opt}', this)">
                        ${opt}
                    </button>
                `).join('')}
            </div>
        </div>
    `;
}

window.setAmenityValue = function(fieldId, value, btn) {
    selectedAmenities[fieldId] = value;
    // UI feedback
    const parent = btn.parentElement;
    parent.querySelectorAll('.chip-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    updateAmenityInput();
};

function updateAmenityInput() {
    document.getElementById('propertyAmenitiesInput').value = JSON.stringify(selectedAmenities);
}

// Modal Logic
function renderAmenitiesModal() {
    const cat = requirementData.categories.find(c => c.id == activeCategoryId);
    const context = cat ? cat.slug : 'all';
    
    const nav = document.getElementById('amenityGroupsNav');
    const fieldsGrid = document.getElementById('amenityFieldsGrid');
    
    // Filter relevant fields
    const relevantFields = requirementData.amenity_fields.filter(f => f.context === context || f.context === 'all');
    
    // Filter groups that have fields in this context
    const fieldGroupIds = [...new Set(relevantFields.map(f => f.group_id))];
    const relevantGroups = requirementData.amenity_groups.filter(g => fieldGroupIds.includes(g.id));

    nav.innerHTML = relevantGroups.map((group, idx) => `
        <button type="button" class="modal-tab-btn ${idx === 0 ? 'active' : ''}" 
                onclick="switchAmenityGroup(${group.id}, this)">
            ${group.name}
        </button>
    `).join('');

    if (relevantGroups.length > 0) {
        showAmenityFields(relevantGroups[0].id, relevantFields);
    }
}

window.switchAmenityGroup = function(groupId, btn) {
    document.querySelectorAll('.modal-tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    
    const cat = requirementData.categories.find(c => c.id == activeCategoryId);
    const context = cat ? cat.slug : 'all';
    const relevantFields = requirementData.amenity_fields.filter(f => f.context === context || f.context === 'all');
    
    showAmenityFields(groupId, relevantFields);
};

function showAmenityFields(groupId, fields) {
    const grid = document.getElementById('amenityFieldsGrid');
    const groupFields = fields.filter(f => f.group_id == groupId);
    
    grid.innerHTML = groupFields.map(field => {
        if (field.field_type === 'switch') {
            const isChecked = selectedAmenities[field.id] == 'true';
            return `
                <div class="amenity-item toggle-style">
                    <span class="amenity-name">${field.label}</span>
                    <label class="toggle-switch small">
                        <input type="checkbox" onchange="selectedAmenities[${field.id}] = this.checked ? 'true' : 'false'; updateAmenityInput();" ${isChecked ? 'checked' : ''}>
                        <span class="slider"></span>
                    </label>
                </div>
            `;
        } else {
            const val = selectedAmenities[field.id] || '';
            return `
                <div class="amenity-item">
                    <span class="amenity-name">${field.label}</span>
                    <input type="${field.field_type === 'number' ? 'number' : 'text'}" 
                           class="glass-input amenity-input" 
                           placeholder="Value..." 
                           value="${val}"
                           onchange="selectedAmenities[${field.id}] = this.value; updateAmenityInput();"
                           style="width: 120px; padding: 8px; font-size: 14px;">
                </div>
            `;
        }
    }).join('');
}

// Modal Toggle
window.openAmenitiesModal = (e) => { 
    if(e) e.preventDefault();
    const modal = document.getElementById('amenitiesModal');
    if(modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    } else {
        console.error('Amenities Modal not found');
    }
};
window.closeAmenitiesModal = () => { 
    const modal = document.getElementById('amenitiesModal');
    if(modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
};
window.applyAmenities = () => {
    closeAmenitiesModal();
    renderSelectedAmenities();
};

function renderSelectedAmenities() {
    const container = document.getElementById('selectedAmenitiesDisplay');
    const activeEntries = Object.entries(selectedAmenities).filter(([id, val]) => val && val !== 'false');
    
    if (activeEntries.length === 0) {
        container.innerHTML = '<div class="empty-state-text">No additional amenities selected.</div>';
        return;
    }

    container.innerHTML = activeEntries.map(([id, val]) => {
        const field = requirementData.amenity_fields.find(f => f.id == id);
        if (!field) return '';
        const displayVal = val === 'true' ? '' : `: ${val}`;
        return `<div class="badge-tag status-info" style="font-size: 11px; padding: 5px 10px;">${field.label}${displayVal}</div>`;
    }).join('');
}

// Form Events
function setupEventListeners() {
    const form = document.getElementById('addPropertyForm');
    const purposeBtns = document.querySelectorAll('#purposeGroup .pill-btn');
    
    purposeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            purposeBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('propertyPurpose').value = btn.dataset.value;
        });
    });

    document.getElementById('openAmenitiesModalBtn').onclick = openAmenitiesModal;

    // Image Upload Logic
    const fileInput = document.getElementById('fileInput');
    const previewContainer = document.getElementById('imagePreviewContainer');

    fileInput.onchange = (e) => {
        const files = Array.from(e.target.files);
        files.forEach(file => {
            if (selectedFiles.length < 10) {
                selectedFiles.push(file);
                const reader = new FileReader();
                reader.onload = (re) => {
                    const div = document.createElement('div');
                    div.style = "position: relative; aspect-ratio: 1/1; border-radius: 8px; overflow: hidden;";
                    div.innerHTML = `
                        <img src="${re.target.result}" style="width: 100%; height: 100%; object-fit: cover;">
                        <button type="button" style="position: absolute; top: 2px; right: 2px; border: none; background: rgba(0,0,0,0.5); color: white; border-radius: 50%; width: 20px; height: 20px; font-size: 12px; cursor: pointer;">&times;</button>
                    `;
                    div.querySelector('button').onclick = () => {
                        div.remove();
                        selectedFiles = selectedFiles.filter(f => f !== file);
                    };
                    previewContainer.appendChild(div);
                };
                reader.readAsDataURL(file);
            }
        });
    };

    form.onsubmit = async (e) => {
        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin spin"></i> Listing Property...';

        const formData = new FormData(form);
        // Add images manually to handle selection vs actual input state
        formData.delete('property_images[]');
        selectedFiles.forEach(file => formData.append('property_images[]', file));

        try {
            const res = await fetch('../includes/api/admin/save_property.php', {
                method: 'POST',
                body: formData
            });
            const result = await res.json();
            if (result.success) {
                Swal.fire('Success!', result.message, 'success').then(() => {
                    const nextUrl = typeof EDIT_PROPERTY_ID !== 'undefined' && EDIT_PROPERTY_ID 
                                  ? `property-detail.php?id=${EDIT_PROPERTY_ID}` 
                                  : 'all-properties.php';
                    window.location.href = nextUrl;
                });
            } else {
                Swal.fire('Error', result.message, 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Something went wrong', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = typeof EDIT_PROPERTY_ID !== 'undefined' && EDIT_PROPERTY_ID 
                                ? '<i class="fa-solid fa-floppy-disk"></i> Update Property' 
                                : '<i class="fa-solid fa-paper-plane"></i> Submit Property';
        }
    };
}

async function initEditMode(id) {
    try {
        const res = await fetch(`../includes/api/admin/get_property_details.php?id=${id}`);
        const result = await res.json();
        if (result.success) {
            const p = result.data.property;
            
            // Basic Fields
            document.querySelector('[name="title"]').value = p.title;
            document.querySelector('[name="description"]').value = p.description;
            document.querySelector('[name="price"]').value = p.price;
            document.querySelector('[name="area_size"]').value = p.area_size;
            document.querySelector('[name="area_unit"]').value = p.area_unit;
            document.querySelector('[name="city_id"]').value = p.city_id;
            document.querySelector('[name="location_name"]').value = p.location_name || p.location_name_ref;
            document.getElementById('locationId').value = p.location_id;
            document.querySelector('[name="contact_email"]').value = p.contact_email;
            
            if (result.data.contacts.length > 0) {
                document.querySelector('[name="phone_number"]').value = result.data.contacts[0].phone_number;
            }

            // Flags
            document.querySelector('[name="is_installment_available"]').checked = !!parseInt(p.is_installment_available);
            document.querySelector('[name="is_ready_for_possession"]').checked = !!parseInt(p.is_ready_for_possession);

            // Purpose
            document.getElementById('propertyPurpose').value = p.purpose;
            document.querySelectorAll('#purposeGroup .pill-btn').forEach(btn => {
                btn.classList.toggle('active', btn.dataset.value === p.purpose);
            });

            // Category & Subtype
            window.switchCategory(p.category_id);
            // Delay subtype selection to let switchCategory render it
            setTimeout(() => {
                const subBtn = Array.from(document.querySelectorAll('#subTypeList .chip-btn'))
                              .find(b => b.innerText.includes(p.subtype_name));
                if (subBtn) window.selectSubType(p.subtype_id, subBtn);
            }, 100);

            // Amenities
            if (result.data.amenities) {
                result.data.amenities.forEach(a => {
                    selectedAmenities[a.id] = a.value;
                });
                updateAmenityInput();
                renderSelectedAmenities();
                renderMainDynamicFields(); // Re-render to show active status on Beds/Baths chips
            }

            // Images
            const previewContainer = document.getElementById('imagePreviewContainer');
            result.data.images.forEach(img => {
                const div = document.createElement('div');
                div.style = "position: relative; aspect-ratio: 1/1; border-radius: 12px; overflow: hidden; border: 1px solid var(--glass-border);";
                div.innerHTML = `
                    <img src="../${img.image_url}" style="width: 100%; height: 100%; object-fit: cover;">
                    <div style="position: absolute; bottom: 0; left: 0; background: rgba(0,0,0,0.5); color: white; width: 100%; font-size: 10px; padding: 2px; text-align: center;">Existing</div>
                `;
                previewContainer.appendChild(div);
            });

            // Update Submit Button Text
            const submitBtn = document.querySelector('#addPropertyForm button[type="submit"]');
            submitBtn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Update Property';
        }
    } catch (e) {
        console.error("Failed to init edit mode", e);
    }
}

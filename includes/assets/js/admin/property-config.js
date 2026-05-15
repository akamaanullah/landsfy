/**
 * Admin Property Configuration Logic
 */

let masterData = null;
let activeCategory = null; 
let activeSubTypeId = null;
let activePurpose = 'sell';

document.addEventListener('DOMContentLoaded', function() {
    fetchConfigData();
});

async function fetchConfigData() {
    try {
        const response = await fetch('../includes/api/admin/get_config_data.php');
        const result = await response.json();
        
        if (result.success) {
            masterData = result.data;
            // Set initial state
            if (masterData.categories.length > 0) {
                activeCategory = masterData.categories[0]; 
                const categorySubtypes = masterData.subtypes[activeCategory.id] || [];
                if (categorySubtypes.length > 0) {
                    activeSubTypeId = categorySubtypes[0].id;
                }
            }
            renderUI();
        }
    } catch (error) {
        console.error('Fetch Error:', error);
    }
}

function renderUI() {
    if (!masterData || !activeCategory) return;

    renderPurpose();
    renderCategories();
    renderSubTypes();
    renderAmenities();
}

function renderPurpose() {
    const btnSell = document.getElementById('btnPurposeSell');
    const btnRent = document.getElementById('btnPurposeRent');
    if (!btnSell || !btnRent) return;

    if (activePurpose === 'sell') {
        btnSell.className = 'btn-primary';
        btnRent.className = 'btn-preview';
    } else {
        btnSell.className = 'btn-preview';
        btnRent.className = 'btn-primary';
    }
}

window.switchPurpose = function(p) {
    activePurpose = p;
    renderUI();
};

function renderCategories() {
    const container = document.getElementById('mainCategoryList');
    if (!container) return;

    container.innerHTML = masterData.categories.map(cat => {
        const isActive = activeCategory.id === cat.id;
        return `
            <div class="glass ${isActive ? 'active' : ''}" 
                 style="padding: 12px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; gap: 10px; 
                        border: 1px solid ${isActive ? 'var(--primary)' : 'var(--border-color)'};
                        background: ${isActive ? 'rgba(108, 93, 211, 0.1)' : 'transparent'};"
                 onclick="switchCategory(${cat.id})">
                <i class="${cat.icon_class}" style="font-size: 18px; color: ${isActive ? 'var(--primary)' : 'var(--text-secondary)'};"></i>
                <span style="font-weight: 600; font-size: 14px; color: ${isActive ? 'var(--text-primary)' : 'var(--text-secondary)'};">${cat.name}</span>
                <div style="margin-left: auto; display: flex; gap: 5px;">
                    <i class="fa-solid fa-pencil" style="font-size: 13px; opacity: 0.5;" onclick="event.stopPropagation(); editCategory(${cat.id})"></i>
                    <i class="fa-solid fa-trash-can" style="font-size: 13px; color: #ff4757; opacity: 0.5;" onclick="event.stopPropagation(); deleteCategory(${cat.id})"></i>
                </div>
            </div>
        `;
    }).join('');
}

window.switchCategory = function(catId) {
    const category = masterData.categories.find(c => c.id == catId);
    if (!category) return;

    activeCategory = category;
    const categorySubtypes = masterData.subtypes[activeCategory.id] || [];
    activeSubTypeId = categorySubtypes.length > 0 ? categorySubtypes[0].id : null;
    renderUI();
}

function renderSubTypes() {
    const container = document.getElementById('subTypeContainer');
    const title = document.getElementById('subTypesTitle');
    if (!container || !title) return;

    title.innerText = `3. Types (${activeCategory.name})`;
    const subTypes = masterData.subtypes[activeCategory.id] || [];
    
    container.innerHTML = subTypes.map(type => {
        const isActive = activeSubTypeId == type.id;
        return `
            <div class="badge-tag ${isActive ? 'status-info' : ''}" 
                 style="padding: 10px; border-radius: 10px; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; border: 1px solid ${isActive ? 'var(--primary)' : 'var(--border-color)'}; font-size: 13px; ${!isActive ? 'color: var(--text-secondary); background: var(--surface-bg);' : ''}"
                 onclick="activeSubTypeId = ${type.id}; renderUI();">
                <i class="${type.icon_class}"></i> ${type.name} 
                <i class="fa-solid fa-trash-can" style="font-size: 11px; margin-left: 5px; opacity: 0.5;" onclick="event.stopPropagation(); deleteSubType(${type.id})"></i>
            </div>
        `;
    }).join('');
}

function renderAmenities() {
    const tbody = document.getElementById('amenitiesTableBody');
    const title = document.getElementById('amenitiesTitle');
    if (!tbody || !title) return;

    const categorySlug = activeCategory.slug.toLowerCase();
    const amenities = [
        ...(masterData.amenities['all'] || []),
        ...(masterData.amenities[categorySlug] || [])
    ];

    const subTypes = masterData.subtypes[activeCategory.id] || [];
    const currentSubType = subTypes.find(t => t.id == activeSubTypeId);
    title.innerText = `4. Amenities (${currentSubType ? currentSubType.name : activeCategory.name})`;

    tbody.innerHTML = amenities.map(field => {
        const contexts = field.context.split(',').map(c => `<span class="badge-tag status-active" style="font-size: 10px; margin-right: 2px;">${c}</span>`).join('');
        
        return `
        <tr class="glass" style="border-radius: 10px;">
            <td style="padding: 15px; font-weight: 600; font-size: 14px;">
                <i class="${field.icon_class || 'fa-solid fa-circle'}" style="margin-right: 8px; color: var(--primary); font-size: 18px;"></i>
                ${field.label}
            </td>
            <td style="padding: 15px;">${contexts}</td>
            <td style="padding: 15px;"><span class="badge-tag status-info" style="font-size: 11px; text-transform: capitalize;">${field.field_type.replace('_', ' ')}</span></td>
            <td style="padding: 15px;"><i class="fa-solid fa-${field.is_required == 1 ? 'circle-check' : 'circle-minus'}" style="color: ${field.is_required == 1 ? 'var(--success)' : 'var(--text-secondary)'}; font-size: 18px;"></i></td>
            <td style="padding: 15px; text-align: right;">
                <button class="card-action-btn action-delete" onclick="deleteAmenity(${field.id})" style="color: #ff4757; background: rgba(255,71,87,0.1); border: none; padding: 6px; border-radius: 8px; cursor: pointer;"><i class="fa-solid fa-trash-can"></i></button>
            </td>
        </tr>
    `;}).join('');
}

function formatOptions(optionsJson) {
    try {
        const opts = JSON.parse(optionsJson);
        return Array.isArray(opts) ? opts.join(', ') : optionsJson;
    } catch (e) { return optionsJson; }
}

window.toggleOptionsVisibility = function() {
    const type = document.getElementById('amenityFieldType').value;
    const group = document.getElementById('optionsGroup');
    if (type === 'switch' || type === 'text_input') {
        group.style.display = 'none';
    } else {
        group.style.display = 'block';
    }
};

window.saveSubType = async function() {
    const id = document.getElementById('subtypeIdField').value;
    const name = document.getElementById('subTypeName').value;
    const icon = document.getElementById('subTypeIcon').value;
    
    if (!name) return Swal.fire('Error', 'Name is required', 'error');

    const formData = new FormData();
    formData.append('action', id ? 'edit_subtype' : 'add_subtype');
    if (id) formData.append('id', id);
    formData.append('category_id', activeCategory.id);
    formData.append('name', name);
    formData.append('icon', icon);

    const res = await fetch('../includes/api/admin/manage_property_config.php', { method: 'POST', body: formData });
    const result = await res.json();
    if (result.success) {
        closeModal('typeModal');
        Swal.fire('Saved!', 'Sub-type updated.', 'success').then(() => fetchConfigData());
    }
};

window.saveCategory = async function() {
    const id = document.getElementById('categoryIdField').value;
    const name = document.getElementById('categoryName').value;
    const icon = document.getElementById('categoryIcon').value;
    
    if (!name) return Swal.fire('Error', 'Name is required', 'error');

    const formData = new FormData();
    formData.append('action', id ? 'edit_category' : 'add_category');
    if (id) formData.append('id', id);
    formData.append('name', name);
    formData.append('icon', icon);

    const res = await fetch('../includes/api/admin/manage_property_config.php', { method: 'POST', body: formData });
    const result = await res.json();
    if (result.success) {
        closeModal('categoryModal');
        Swal.fire('Saved!', 'Category updated.', 'success').then(() => fetchConfigData());
    }
};

window.editCategory = function(id) {
    const cat = masterData.categories.find(c => c.id == id);
    if (!cat) return;
    document.getElementById('catModalTitle').innerText = 'Edit Category';
    document.getElementById('categoryIdField').value = cat.id;
    document.getElementById('categoryName').value = cat.name;
    document.getElementById('categoryIcon').value = cat.icon_class;
    openCategoryModal();
};

window.deleteCategory = function(id) {
    Swal.fire({
        title: 'Delete Category?',
        text: "This will remove all sub-types and amenities for this category!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff4757',
        confirmButtonText: 'Yes, Delete'
    }).then(async (result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'delete_category');
            formData.append('id', id);
            const res = await fetch('../includes/api/admin/manage_property_config.php', { method: 'POST', body: formData });
            if ((await res.json()).success) {
                Swal.fire('Deleted!', '', 'success').then(() => fetchConfigData());
            }
        }
    });
};

window.deleteSubType = function(id) {
    Swal.fire({
        title: 'Delete Sub-type?',
        text: "This may affect existing listings.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff4757',
        confirmButtonText: 'Yes, Delete'
    }).then(async (result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'delete_subtype');
            formData.append('id', id);
            const res = await fetch('../includes/api/admin/manage_property_config.php', { method: 'POST', body: formData });
            if ((await res.json()).success) {
                Swal.fire('Deleted!', '', 'success').then(() => fetchConfigData());
            }
        }
    });
};

window.saveAmenity = async function() {
    const label = document.getElementById('amenityLabel').value;
    const type = document.getElementById('amenityFieldType').value;
    const options = document.getElementById('amenityOptions').value;
    const required = document.getElementById('reqCheck').checked;
    const group_id = document.getElementById('amenityGroup').value;
    const icon = document.getElementById('amenityIcon').value;
    
    // Get context from checkboxes
    const contexts = [];
    document.querySelectorAll('.ctx-check:checked').forEach(cb => contexts.push(cb.value));
    const contextStr = contexts.length > 0 ? (contexts.length === 3 ? 'all' : contexts.join(',')) : 'all';

    if (!label) return Swal.fire('Error', 'Label is required', 'error');

    const formData = new FormData();
    formData.append('action', 'add_amenity');
    formData.append('group_id', group_id);
    formData.append('context', contextStr);
    formData.append('label', label);
    formData.append('field_type', type);
    formData.append('options', options);
    formData.append('is_required', required);
    formData.append('icon', icon);

    const res = await fetch('../includes/api/admin/manage_property_config.php', { method: 'POST', body: formData });
    const result = await res.json();
    if (result.success) {
        closeModal('amenityModal');
        Swal.fire('Saved!', 'Amenity field added.', 'success').then(() => fetchConfigData());
    }
};

window.deleteAmenity = function(id) {
    Swal.fire({
        title: 'Delete Amenity?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff4757',
        confirmButtonText: 'Yes, Delete'
    }).then(async (result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'delete_amenity');
            formData.append('id', id);
            const res = await fetch('../includes/api/admin/manage_property_config.php', { method: 'POST', body: formData });
            if ((await res.json()).success) {
                Swal.fire('Deleted!', '', 'success').then(() => fetchConfigData());
            }
        }
    });
};

window.openCategoryModal = () => { 
    document.getElementById('catModalTitle').innerText = 'Add New Category';
    document.getElementById('categoryIdField').value = '';
    document.getElementById('categoryName').value = '';
    document.getElementById('categoryIcon').value = 'fa-house';
    document.getElementById('categoryModal').style.opacity = '1'; 
    document.getElementById('categoryModal').style.pointerEvents = 'all'; 
};
window.openTypeModal = () => { 
    document.getElementById('typeModalTitle').innerText = 'Add New Sub-type';
    document.getElementById('subtypeIdField').value = '';
    document.getElementById('subTypeName').value = '';
    document.getElementById('subTypeIcon').value = 'fa-house-chimney';
    document.getElementById('typeModal').style.opacity = '1'; 
    document.getElementById('typeModal').style.pointerEvents = 'all'; 
};
window.openAmenityModal = () => { document.getElementById('amenityModal').style.opacity = '1'; document.getElementById('amenityModal').style.pointerEvents = 'all'; };
window.closeModal = (id) => { document.getElementById(id).style.opacity = '0'; document.getElementById(id).style.pointerEvents = 'none'; };

/**
 * View JSON Metadata logic
 */
window.viewJsonConfig = function() {
    if (!masterData) return;
    
    Swal.fire({
        title: 'Property Metadata (JSON)',
        html: `<div style="text-align: left;"><pre style="background: rgba(0,0,0,0.3); padding: 15px; border-radius: 12px; font-size: 11px; max-height: 400px; overflow-y: auto; color: #6c5dd3; font-family: monospace;">${JSON.stringify(masterData, null, 4)}</pre></div>`,
        width: '600px',
        background: '#1a1d21',
        color: '#fff',
        confirmButtonText: 'Copy to Clipboard',
        confirmButtonColor: '#6c5dd3',
        showCancelButton: true,
        cancelButtonText: 'Close'
    }).then((result) => {
        if (result.isConfirmed) {
            navigator.clipboard.writeText(JSON.stringify(masterData, null, 4));
            showToast('JSON copied to clipboard!', 'success');
        }
    });
};

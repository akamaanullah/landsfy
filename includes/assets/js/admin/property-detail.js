document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const propertyId = urlParams.get('id');

    if (!propertyId) {
        Landsfy.showToast('Property ID missing from URL', 'error');
        return;
    }

    fetchPropertyDetails(propertyId);

    async function fetchPropertyDetails(id) {
        try {
            const response = await fetch(`../includes/api/admin/get_property_details.php?id=${id}`);
            const result = await response.json();

            if (result.success) {
                renderDetails(result.data);
                setupSlider(result.data.images);
                setupAdminActions(id, result.data.property.status);
            } else {
                Landsfy.showToast(result.message, 'error');
            }
        } catch (error) {
            console.error('Fetch Error:', error);
            Landsfy.showToast('Failed to load property details', 'error');
        }
    }

    function renderDetails(data) {
        const p = data.property;

        // Header & Metadata
        document.getElementById('breadcrumbTitle').innerText = p.title;
        document.getElementById('propertyTitle').innerText = p.title;
        document.getElementById('propertyPrice').innerText = `PKR ${Landsfy.formatPrice(p.price)}`;
        document.getElementById('propertyLocation').innerText = `${p.location_name || p.location_name_ref || 'N/A'}, ${p.city_name}`;
        document.getElementById('propertyIDDisplay').innerText = `#${p.id}`;
        document.getElementById('propertySlugDisplay').innerText = p.slug;
        document.getElementById('propertyDescription').innerHTML = (p.description || '').replace(/\n/g, '<br>');

        // Insights
        document.getElementById('totalViews').innerText = p.views_total || 0;
        document.getElementById('totalLeads').innerText = p.leads_total || 0;
        document.getElementById('listedDate').innerText = new Date(p.created_at).toLocaleDateString();
        document.getElementById('propPurpose').innerText = p.purpose;

        // Status Badges
        const statusContainer = document.getElementById('statusBadges');
        statusContainer.innerHTML = `<span class="status-pill badge-${p.status}">${p.status.replace('_', ' ')}</span>`;
        if (p.is_featured) {
            statusContainer.innerHTML += `<span class="status-pill badge-featured"><i class="fa-solid fa-star"></i> Featured</span>`;
        }

        // Main Features (Beds, Baths, Area)
        const featuresContainer = document.getElementById('mainFeatures');
        const beds = data.amenities.find(a => a.label.toLowerCase() === 'bedrooms')?.value || '-';
        const baths = data.amenities.find(a => a.label.toLowerCase() === 'bathrooms')?.value || '-';
        
        featuresContainer.innerHTML = `
            <div class="feature-card">
                <i class="fa-solid fa-bed"></i>
                <div class="feat-info">
                    <span class="feat-label">Bedrooms</span>
                    <span class="feat-val">${beds}</span>
                </div>
            </div>
            <div class="feature-card">
                <i class="fa-solid fa-bath"></i>
                <div class="feat-info">
                    <span class="feat-label">Bathrooms</span>
                    <span class="feat-val">${baths}</span>
                </div>
            </div>
            <div class="feature-card">
                <i class="fa-solid fa-vector-square"></i>
                <div class="feat-info">
                    <span class="feat-label">Area</span>
                    <span class="feat-val">${p.area_size} ${(p.area_unit || '').toUpperCase()}</span>
                </div>
            </div>
            <div class="feature-card">
                <i class="fa-solid fa-building"></i>
                <div class="feat-info">
                    <span class="feat-label">Type</span>
                    <span class="feat-val">${p.subtype_name}</span>
                </div>
            </div>
        `;

        // Amenities Grid
        const amenGrid = document.getElementById('amenitiesGrid');
        if (data.amenities.length > 0) {
            amenGrid.innerHTML = data.amenities.map(a => {
                let valStr = a.value === '1' ? '' : `: ${a.value}`;
                if (a.field_type === 'switch' && a.value === '1') valStr = '';
                
                return `
                    <div class="amenity-pill">
                        <i class="fa-solid ${a.icon_class || 'fa-circle-check'}"></i> 
                        ${a.label}${valStr}
                    </div>
                `;
            }).join('');
        } else {
            amenGrid.innerHTML = '<p style="opacity:0.5; font-style:italic;">No amenities specified.</p>';
        }

        // Author Card
        document.getElementById('authorName').innerText = p.author_name;
        document.getElementById('authorRole').innerText = p.agency_name || 'Individual Seller';
        if (p.author_avatar) {
            document.getElementById('authorAvatar').src = `../${p.author_avatar}`;
            document.getElementById('authorAvatar').onerror = function() {
                this.onerror = null;
                this.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(p.author_name)}&background=6c5dd3&color=fff&bold=true`;
            };
        }

        // Contacts
        const contactList = document.getElementById('contactList');
        contactList.innerHTML = '';
        
        // Add Email from property first
        if (p.contact_email) {
            contactList.innerHTML += `
                <div class="contact-info-row">
                    <i class="fa-solid fa-envelope"></i>
                    <span>${p.contact_email}</span>
                </div>
            `;
        }
        
        // Add Phone numbers
        data.contacts.forEach(c => {
            contactList.innerHTML += `
                <div class="contact-info-row">
                    <i class="fa-solid fa-phone"></i>
                    <span>${c.phone_number} (${c.label})</span>
                </div>
            `;
        });
    }

    function setupSlider(images) {
        const mainSlide = document.getElementById('mainSlide');
        const thumbStrip = document.getElementById('thumbStrip');
        const counter = document.getElementById('imageCounter');
        const prevBtn = document.getElementById('prevSlide');
        const nextBtn = document.getElementById('nextSlide');

        if (!images || images.length === 0) {
            counter.innerText = '0 / 0';
            return;
        }

        let currentIndex = 0;
        counter.innerText = `1 / ${images.length}`;
        mainSlide.src = `../${images[0].image_url}`;
        mainSlide.onerror = function() {
            this.onerror = null;
            this.outerHTML = '<div class=\'card-img-placeholder\' style=\'height:500px\'><i class=\'fa-solid fa-image-broken\'></i></div>';
        };

        // Render Thumbnails
        thumbStrip.innerHTML = images.map((img, idx) => `
            <img src="../${img.image_url}" 
                 class="thumb ${idx === 0 ? 'active' : ''}" 
                 data-index="${idx}"
                 onerror="this.onerror=null;this.src='../includes/assets/images/placeholder.png'">
        `).join('');

        const updateSlider = (index) => {
            currentIndex = index;
            mainSlide.style.opacity = '0';
            setTimeout(() => {
                mainSlide.src = `../${images[currentIndex].image_url}`;
                mainSlide.style.opacity = '1';
            }, 200);

            counter.innerText = `${currentIndex + 1} / ${images.length}`;
            
            // Update thumbnails
            document.querySelectorAll('.thumb').forEach((t, i) => {
                const isActive = i === currentIndex;
                t.classList.toggle('active', isActive);
                if (isActive) {
                    t.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                }
            });
        };

        nextBtn.onclick = () => {
            let next = (currentIndex + 1) % images.length;
            updateSlider(next);
        };

        prevBtn.onclick = () => {
            let prev = (currentIndex - 1 + images.length) % images.length;
            updateSlider(prev);
        };

        thumbStrip.onclick = (e) => {
            if (e.target.classList.contains('thumb')) {
                updateSlider(parseInt(e.target.dataset.index));
            }
        };

        // Thumbnail Navigation Scrolling
        const prevThumb = document.getElementById('prevThumb');
        const nextThumb = document.getElementById('nextThumb');

        if (prevThumb && nextThumb) {
            nextThumb.onclick = () => {
                thumbStrip.scrollBy({ left: 200, behavior: 'smooth' });
            };
            prevThumb.onclick = () => {
                thumbStrip.scrollBy({ left: -200, behavior: 'smooth' });
            };
        }
    }

    function setupAdminActions(id, currentStatus) {
        const approvalBox = document.getElementById('approvalButtons');
        const editBtn = document.getElementById('editPropBtn');
        const approveBtn = document.getElementById('approveBtn');
        const rejectBtn = document.getElementById('rejectBtn');
        const deleteBtn = document.getElementById('deletePropBtn');

        // Show approval buttons only if pending
        if (currentStatus === 'under_review') {
            approvalBox.style.display = 'flex';
        }

        editBtn.onclick = () => {
            window.location.href = `add-property.php?id=${id}`;
        };

        approveBtn.onclick = async () => {
            const confirmed = await Swal.fire({
                title: 'Approve Listing?',
                text: "This will make the property live on the platform.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2ed573',
                confirmButtonText: 'Yes, Approve it'
            });

            if (confirmed.isConfirmed) {
                updateStatus(id, 'active');
            }
        };

        rejectBtn.onclick = async () => {
            const { value: reason } = await Swal.fire({
                title: 'Reject Listing',
                input: 'textarea',
                inputLabel: 'Reason for Rejection',
                inputPlaceholder: 'e.g. Blurry images, invalid contact...',
                showCancelButton: true,
                confirmButtonColor: '#ff4757',
                confirmButtonText: 'Reject Now'
            });

            if (reason) {
                updateStatus(id, 'rejected', reason);
            }
        };

        deleteBtn.onclick = async () => {
            const confirmed = await Swal.fire({
                title: 'Are you sure?',
                text: "This action is permanent and cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff4757',
                confirmButtonText: 'Yes, Delete it'
            });

            if (confirmed.isConfirmed) {
                // For simplicity, we can use the same updateStatus or a dedicated delete
                // I'll use status 'deleted' or a dedicated delete API if exists.
                // Let's use status 'deleted'
                updateStatus(id, 'deleted');
            }
        };
    }

    async function updateStatus(id, newStatus, reason = '') {
        try {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('status', newStatus);
            if (reason) formData.append('rejection_reason', reason);

            const res = await fetch('../includes/api/admin/update_property_status.php', {
                method: 'POST',
                body: formData
            });
            const result = await res.json();

            if (result.success) {
                Swal.fire('Success', result.message, 'success').then(() => {
                    if (newStatus === 'deleted') window.location.href = 'all-properties.php';
                    else window.location.reload();
                });
            } else {
                Swal.fire('Error', result.message, 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Communication failed', 'error');
        }
    }


});

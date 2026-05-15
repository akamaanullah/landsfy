document.addEventListener('DOMContentLoaded', () => {
    const stats = {
        total: document.querySelector('.stat-card[data-filter="all"] h2'),
        published: document.querySelector('.stat-card[data-filter="active"] h2'),
        pending: document.querySelector('.stat-card[data-filter="pending"] h2'),
        sold: document.querySelector('.stat-card[data-filter="sold"] h2')
    };

    function loadStats() {
        fetch('../includes/api/agent/my_listings_stats.php')
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    stats.total.innerText = data.data.total;
                    stats.published.innerText = data.data.published;
                    stats.pending.innerText = data.data.pending;
                    stats.sold.innerText = data.data.sold;
                }
            });
    }

    const searchInput = document.getElementById('propertySearch');
    let searchTimeout;

    function loadListings() {
        const search = searchInput.value;
        const status = document.querySelector('input[name="status"]')?.value || 'all';
        const type = document.querySelector('input[name="type"]')?.value || 'all';
        const purpose = document.querySelector('input[name="purpose"]')?.value || 'all';
        
        document.getElementById('propertyGrid').innerHTML = '<div style="text-align: center; width: 100%; padding: 40px; grid-column: 1/-1;"><i class="fa-solid fa-circle-notch fa-spin" style="font-size: 32px; color: var(--primary);"></i></div>';

        const params = new URLSearchParams({ search, status, type, purpose });

        fetch(`../includes/api/agent/get_listings.php?${params}`)
            .then(r => r.json())
            .then(data => {
                const grid = document.getElementById('propertyGrid');
                if (data.success && data.data.length > 0) {
                    grid.innerHTML = data.data.map(p => {
                        // Standardize status for UI
                        const currentStatus = p.status || 'under_review';
                        let badgeClass = currentStatus === 'active' ? 'badge-active' : (currentStatus === 'under_review' ? 'badge-pending' : (currentStatus === 'sold' ? 'badge-sold' : 'badge-danger'));
                        let displayStatus = currentStatus === 'under_review' ? 'Under Review' : currentStatus.charAt(0).toUpperCase() + currentStatus.slice(1);
                        
                        // Premium Info
                        let premiumBadge = '';
                        if (p.premium_type && p.premium_type !== 'none') {
                            const tierColor = p.premium_type === 'diamond' ? '#3B82F6' : '#10B981';
                            const tierIcon = p.premium_type === 'diamond' ? 'fa-solid fa-gem' : 'fa-solid fa-diamond';
                            
                            let premiumSubText = '';
                            if (p.premium_status === 'active' && p.days_left !== null) {
                                premiumSubText = `<span style="font-size: 10px; opacity: 0.8; margin-left: 5px;">(${p.days_left}d left)</span>`;
                            } else if (p.premium_status === 'pending') {
                                premiumSubText = `<span style="font-size: 10px; opacity: 0.8; margin-left: 5px;">(Awaiting Approval)</span>`;
                            }

                            premiumBadge = `
                                <div class="premium-tier-tag" style="position: absolute; bottom: 10px; left: 10px; background: ${tierColor}; color: white; padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: 800; display: flex; align-items: center; gap: 5px; box-shadow: 0 4px 12px ${tierColor}44;">
                                    <i class="${tierIcon}"></i> ${p.premium_type.toUpperCase()} ${premiumSubText}
                                </div>
                            `;
                        }

                        return `
                            <div class="property-card glass" data-status="${currentStatus}">
                                <div class="card-image-wrapper" style="position: relative;">
                                    <div class="card-badge ${badgeClass}">${displayStatus}</div>
                                    <img src="${Landsfy.getImageUrl(p.image_url)}" class="card-image" style="height: 200px; width: 100%; object-fit: cover;" onerror="Landsfy.handleImageError(this, 'property')">
                                    ${premiumBadge}
                                </div>
                                <div class="card-content">
                                    <div class="card-price">PKR ${Number(p.price).toLocaleString()}</div>
                                    <h4 class="card-title">${p.title}</h4>
                                    <div class="card-location"><i class="fa-solid fa-location-dot"></i> ${p.city_name || 'N/A'}</div>
                                    
                                    <div class="agent-stats-strip" style="display: flex; margin-top: 16px; background: rgba(0,0,0,0.02); border-radius: 12px; padding: 10px;">
                                        <div style="flex: 1; text-align: center; border-right: 1px solid var(--glass-border);">
                                            <div style="font-size: 10px; text-transform: uppercase; color: var(--text-secondary);">Views</div>
                                            <div style="font-weight: 700; font-size: 13px;">${p.views_total || 0}</div>
                                        </div>
                                        <div style="flex: 1; text-align: center;">
                                            <div style="font-size: 10px; text-transform: uppercase; color: var(--text-secondary);">Clicks</div>
                                            <div style="font-weight: 700; font-size: 13px; color: var(--primary);">${p.total_clicks || 0}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-actions" style="margin-top: 15px; border-top: 1px solid var(--glass-border); padding-top: 15px; display: flex; justify-content: space-between; align-items: center;">
                                    <div class="card-meta" style="font-size: 12px; color: var(--text-secondary);"><i class="fa-solid fa-clock"></i> ${new Date(p.created_at).toLocaleDateString()}</div>
                                        <div class="action-btns" style="display: flex; gap: 8px;">
                                        <a href="view-property.php?id=${p.id}" class="icon-btn" title="View" target="_blank"><i class="fa-solid fa-eye"></i></a>
                                        ${currentStatus !== 'sold' ? `<button class="icon-btn" onclick="updateStatus(${p.id}, 'sold')" title="Mark as Sold" style="color: var(--success);"><i class="fa-solid fa-circle-check"></i></button>` : ''}
                                        <button class="icon-btn" onclick="updateStatus(${p.id}, 'delete')" title="Delete" style="color: var(--danger);"><i class="fa-solid fa-trash-can"></i></button>
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('');
                } else {
                    grid.innerHTML = '<div style="text-align: center; width: 100%; padding: 40px; color: var(--text-secondary); grid-column: 1/-1;">No properties found for these filters.</div>';
                }
            });
    }

    // Debounce Text Search
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(loadListings, 300);
    });

    // Handle Dropdown Logic
    document.querySelectorAll('.dropdown-item').forEach(item => {
        item.addEventListener('click', function() {
            const dropdown = this.closest('.custom-dropdown');
            const value = this.getAttribute('data-value');
            const text = this.innerText;
            
            dropdown.querySelector('input[type="hidden"]').value = value;
            dropdown.querySelector('.selected-text').innerText = text;

            dropdown.querySelectorAll('.dropdown-item').forEach(li => li.classList.remove('active'));
            this.classList.add('active');
            dropdown.classList.remove('open');
            
            loadListings();
        });
    });

    // Manage Dropdown Toggle
    document.querySelectorAll('.dropdown-trigger').forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            const parent = this.closest('.custom-dropdown');
            document.querySelectorAll('.custom-dropdown.open').forEach(d => {
                if (d !== parent) d.classList.remove('open');
            });
            parent.classList.toggle('open');
        });
    });

    // Close dropdowns on outside click
    document.addEventListener('click', () => {
        document.querySelectorAll('.custom-dropdown.open').forEach(d => d.classList.remove('open'));
    });

    // Stat Cards toggle pre-filter
    document.querySelectorAll('.stat-card').forEach(card => {
        card.addEventListener('click', function() {
            document.querySelectorAll('.stat-card').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            
            const filter = this.getAttribute('data-filter');
            const statusInput = document.querySelector('input[name="status"]');
            statusInput.value = filter;
            
            const dropdownItems = document.querySelectorAll('#statusFilter .dropdown-item');
            dropdownItems.forEach(item => {
                if (item.getAttribute('data-value') === filter) {
                    item.classList.add('active');
                    document.querySelector('#statusFilter .selected-text').innerText = item.innerText;
                } else {
                    item.classList.remove('active');
                }
            });

            loadListings();
        });
    });

    // Make updateStatus global
    window.updateStatus = function(id, action) {
        const actionLabel = action === 'sold' ? 'MARK AS SOLD' : (action === 'delete' ? 'DELETE' : action);
        const confirmColor = action === 'delete' ? '#ef4444' : '#6b00b6';
        
        Swal.fire({
            title: 'Are you sure?',
            text: `Do you want to ${actionLabel} this listing?`,
            icon: action === 'delete' ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonColor: confirmColor,
            cancelButtonColor: '#94a3b8',
            confirmButtonText: `Yes, ${action}!`,
            background: 'rgba(15, 12, 33, 0.95)',
            color: '#ffffff',
            backdrop: `rgba(0, 0, 0, 0.6) blur(6px)`,
            customClass: {
                popup: 'premium-swal-popup'
            },
            didOpen: () => {
                const popup = Swal.getPopup();
                popup.style.border = '1px solid rgba(255, 255, 255, 0.1)';
                popup.style.borderRadius = '24px';
                popup.style.boxShadow = '0 25px 50px -12px rgba(0, 0, 0, 0.5)';
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('property_id', id);
                formData.append('action', action);

                fetch('../includes/api/agent/update_listing_status.php', { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(data => {
                        if(data.success) {
                            showToast(data.message || 'Listing updated successfully!', 'success');
                            loadListings();
                            loadStats();
                        } else {
                            showToast('Error: ' + data.message, 'error');
                        }
                    })
                    .catch(() => showToast("Could not update listing at this time.", 'error'));
            }
        });
    }

    // Initialize
    loadStats();
    loadListings();

    // Check for success message in URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('msg') === 'success') {
        showToast('Property listing processed successfully!', 'success');
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});

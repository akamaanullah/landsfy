document.addEventListener('DOMContentLoaded', () => {
    fetchLeads();

    const leadSearch = document.getElementById('leadSearch');
    if (leadSearch) leadSearch.addEventListener('input', debounce(fetchLeads, 500));

    // Dropdown Logic
    const filterDropdown = document.getElementById('leadFilter');
    if (filterDropdown) {
        const trigger = filterDropdown.querySelector('.dropdown-trigger');
        const menu = filterDropdown.querySelector('.dropdown-menu');
        
        if (trigger && menu) {
            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                const isVisible = menu.style.display === 'block';
                menu.style.display = isVisible ? 'none' : 'block';
            });

            document.addEventListener('click', (e) => {
                if (!filterDropdown.contains(e.target)) {
                    menu.style.display = 'none';
                }
            });

            // Handle filter selection
            const items = menu.querySelectorAll('.dropdown-item');
            items.forEach(item => {
                item.addEventListener('click', () => {
                    items.forEach(i => i.classList.remove('active'));
                    item.classList.add('active');
                    menu.style.display = 'none';
                    // Trigger filter fetch logic here if needed
                });
            });
        }
    }
});

async function fetchLeads() {
    const search = document.getElementById('leadSearch')?.value || '';
    const url = `../includes/api/seller/get_leads.php?search=${search}`;
    
    try {
        const response = await fetch(url);
        const result = await response.json();

        if (result.success) {
            renderLeads(result.data);
        }
    } catch (error) {
        console.error('Error fetching leads:', error);
    }
}

function renderLeads(leads) {
    const tbody = document.querySelector('.data-table tbody');
    if (!tbody) return;

    if (leads.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" style="text-align: center; padding: 80px 20px;">
                    <div style="width: 80px; height: 80px; background: rgba(107, 0, 182, 0.05); color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;">
                        <i class="fa-solid fa-envelope-open-text" style="font-size: 32px; opacity: 0.5;"></i>
                    </div>
                    <h3 style="font-size: 18px; font-weight: 800; color: var(--text-primary); margin-bottom: 8px;">No buyer inquiries found</h3>
                    <p style="font-size: 14px; color: var(--text-secondary); max-width: 300px; margin: 0 auto;">When buyers express interest in your properties, their details will appear here.</p>
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = leads.map(lead => `
        <tr style="transition: all 0.3s ease;">
            <td>
                <div class="adb-agent-info">
                    <img src="${lead.buyer_avatar ? '../' + lead.buyer_avatar : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(lead.buyer_name || 'U') + '&background=6B00B6&color=fff'}" class="adb-agent-avatar" style="border-radius: 12px; width: 44px; height: 44px; border: 1px solid var(--glass-border);">
                    <div>
                        <div class="adb-agent-name" style="font-size: 14px; font-weight: 700; color: var(--text-primary);">${lead.buyer_name || 'Anonymous User'}</div>
                        <div style="font-size: 11px; color: var(--text-secondary); display: flex; align-items: center; gap: 6px; margin-top: 2px;">
                            <i class="fa-solid fa-envelope" style="font-size: 10px; opacity: 0.7;"></i> ${lead.buyer_email || 'No email provided'}
                        </div>
                    </div>
                </div>
            </td>
            <td>
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <div style="font-size: 13px; font-weight: 700; color: var(--primary);">${lead.property_title}</div>
                    <div style="font-size: 11px; color: var(--text-secondary); display: flex; align-items: center; gap: 4px;">
                        <i class="fa-solid fa-location-dot" style="font-size: 10px; opacity: 0.7;"></i> ${lead.location_name || 'Premium Location'}
                    </div>
                </div>
            </td>
            <td style="text-align: center;">
                <div style="font-size: 13px; font-weight: 700; color: var(--text-primary);">${new Date(lead.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</div>
                <div style="font-size: 11px; color: var(--text-secondary); margin-top: 2px;">${new Date(lead.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
            </td>
            <td style="text-align: center;">
                <span class="badge-tag ${lead.interaction_type === 'whatsapp_click' ? 'status-active' : 'status-info'}" style="padding: 6px 14px; border-radius: 10px; font-size: 10px; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase;">
                    <i class="fa-solid ${lead.interaction_type === 'whatsapp_click' ? 'fa-whatsapp' : 'fa-phone'}" style="margin-right: 4px;"></i>
                    ${lead.interaction_type === 'whatsapp_click' ? 'WhatsApp' : 'Call Reveal'}
                </span>
            </td>
            <td style="text-align: center;">
                <div style="display: flex; justify-content: center; gap: 8px;">
                    ${lead.buyer_phone ? `
                        <button class="icon-btn-small" onclick="window.open('https://wa.me/${lead.buyer_phone.replace(/\D/g,'')}')" style="color: #22c55e; background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.2);" title="WhatsApp">
                            <i class="fa-brands fa-whatsapp"></i>
                        </button>
                        <button class="icon-btn-small" onclick="window.location.href='tel:${lead.buyer_phone}'" style="color: #3b82f6; background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2);" title="Call Buyer">
                            <i class="fa-solid fa-phone"></i>
                        </button>
                    ` : ''}
                    <button class="icon-btn-small" style="color: var(--primary); background: rgba(107, 0, 182, 0.1); border: 1px solid rgba(107, 0, 182, 0.2);" title="View Property">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function debounce(func, wait) {
    let timeout;
    return function() {
        clearTimeout(timeout);
        timeout = setTimeout(func, wait);
    };
}

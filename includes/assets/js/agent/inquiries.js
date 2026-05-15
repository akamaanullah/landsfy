document.addEventListener('DOMContentLoaded', () => {
    const tbody = document.querySelector('.data-table tbody');
    const searchInput = document.getElementById('inquirySearch');
    const actionFilterInput = document.querySelector('input[name="action"]');
    let searchTimeout;

    function loadInquiries() {
        const search = searchInput.value;
        const action = actionFilterInput.value;

        tbody.innerHTML = `
            <tr>
                <td colspan="5" style="text-align: center; padding: 40px;">
                    <i class="fa-solid fa-circle-notch fa-spin" style="font-size: 32px; color: var(--primary);"></i>
                </td>
            </tr>
        `;

        const params = new URLSearchParams({ search, action });

        fetch(`../includes/api/agent/get_all_inquiries.php?${params}`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    tbody.innerHTML = data.data.map(inquiry => {
                        const date = new Date(inquiry.created_at);
                        const timeStr = date.toLocaleString('en-US', { 
                            weekday: 'short', 
                            month: 'short', 
                            day: 'numeric', 
                            hour: '2-digit', 
                            minute: '2-digit' 
                        });

                        const isGuest = !inquiry.user_name;
                        const userDisplay = isGuest ? `Guest_${inquiry.id.toString().slice(-4)}` : (inquiry.user_full_name || inquiry.user_name);
                        const userType = isGuest ? 'Unregistered Visitor' : 'Logged-in User';
                        const userIcon = isGuest ? 'fa-solid fa-ghost' : 'fa-solid fa-user';
                        const userIconBg = isGuest ? 'rgba(0,0,0,0.05)' : 'rgba(107, 0, 182, 0.1)';
                        const userIconColor = isGuest ? 'var(--text-secondary)' : 'var(--primary)';

                        const actionLabel = inquiry.interaction_type === 'whatsapp_click' ? 'WhatsApp' : 'Call Button';
                        const actionIcon = inquiry.interaction_type === 'whatsapp_click' ? 'fa-solid fa-whatsapp' : 'fa-solid fa-phone-call';
                        const actionColor = inquiry.interaction_type === 'whatsapp_click' ? '#25D366' : 'var(--primary)';

                        return `
                            <tr data-action="${inquiry.interaction_type}">
                                <td style="padding: 20px 16px;"><span style="font-size: 13px; color: var(--text-secondary); font-weight: 500;">${timeStr}</span></td>
                                <td style="padding: 20px 16px;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="width: 36px; height: 36px; background: ${userIconBg}; color: ${userIconColor}; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px;"><i class="${userIcon}"></i></div>
                                        <div>
                                            <div style="font-weight: 700; font-size: 14px; color: var(--text-primary);">${userDisplay}</div>
                                            <div style="font-size: 11px; color: var(--text-secondary);">${userType}</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 20px 16px;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span style="font-weight: 600; color: var(--text-primary);">${inquiry.property_title}</span>
                                        <a href="../property.php?id=${inquiry.property_id}" target="_blank" class="view-link" title="View Property" style="color: var(--primary); font-size: 16px;"><i class="fa-solid fa-arrow-square-out"></i></a>
                                    </div>
                                </td>
                                <td style="padding: 20px 16px;"><span style="color: ${actionColor}; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;"><i class="${actionIcon}"></i> ${actionLabel}</span></td>
                                <td style="padding: 20px 16px;"><span class="badge-tag status-active" style="background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);"><i class="fa-solid fa-flame" style="margin-right: 4px;"></i> Hot Inquiry</span></td>
                            </tr>
                        `;
                    }).join('');
                } else {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 60px; color: var(--text-secondary);">
                                <i class="fa-solid fa-magnifying-glass" style="font-size: 48px; opacity: 0.2; margin-bottom: 16px; display: block;"></i>
                                No inquiries found matching your criteria.
                            </td>
                        </tr>
                    `;
                }
            })
            .catch(err => {
                console.error(err);
                tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: var(--danger); padding: 40px;">Failed to load inquiries.</td></tr>`;
            });
    }

    // Debounce Search
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(loadInquiries, 300);
    });

    // Handle Dropdown Filter
    document.querySelectorAll('#inquiryActionFilter .dropdown-item').forEach(item => {
        item.addEventListener('click', function() {
            const dropdown = this.closest('.custom-dropdown');
            const value = this.getAttribute('data-value');
            const text = this.innerText;
            
            dropdown.querySelector('input[type="hidden"]').value = value;
            dropdown.querySelector('.selected-text').innerText = text;

            dropdown.querySelectorAll('.dropdown-item').forEach(li => li.classList.remove('active'));
            this.classList.add('active');
            dropdown.classList.remove('open');
            
            loadInquiries();
        });
    });

    // Custom Dropdown Trigger Logic (If not handled by global script.js)
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

    document.addEventListener('click', () => {
        document.querySelectorAll('.custom-dropdown.open').forEach(d => d.classList.remove('open'));
    });

    // Initial Load
    loadInquiries();
});

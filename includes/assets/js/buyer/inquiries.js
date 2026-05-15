document.addEventListener('DOMContentLoaded', function() {
    fetchInquiries();
});

async function fetchInquiries() {
    const tableBody = document.querySelector('.data-table tbody');
    try {
        const response = await fetch('../includes/api/buyer/get_inquiries.php');
        const data = await response.json();
        
        if (data.success) {
            if (data.inquiries.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 40px; color: var(--text-secondary);">No inquiries found.</td></tr>';
                return;
            }

            tableBody.innerHTML = data.inquiries.map(inq => `
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 40px; height: 40px; background: rgba(0,0,0,0.05); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-house" style="font-size: 20px;"></i>
                            </div>
                            <div>
                                <div style="font-weight: 700; font-size: 14px;">${inq.property_title}</div>
                                <div style="font-size: 11px; color: var(--text-secondary);">Property ID: #${inq.id}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <img src="${inq.author_avatar ? '../' + inq.author_avatar : 'https://i.pravatar.cc/150?img=1'}" style="width: 30px; height: 30px; border-radius: 50%;">
                            <div style="font-weight: 600; font-size: 13px;">${inq.author_name}</div>
                        </div>
                    </td>
                    <td style="text-align: center;">
                        <span style="color: ${inq.message === 'whatsapp_click' ? '#25D366' : 'var(--primary)'}; font-size: 18px;">
                            <i class="fa-solid ${inq.message === 'whatsapp_click' ? 'fa-whatsapp' : inq.message === 'call_reveal' ? 'fa-phone' : 'fa-chat-circle'}"></i>
                        </span>
                    </td>
                    <td style="text-align: center; font-size: 13px; color: var(--text-secondary);">
                        ${new Date(inq.created_at).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' })}
                    </td>
                    <td style="text-align: center;">
                        <span class="badge-tag status-${inq.status === 'new' ? 'info' : inq.status === 'active' ? 'active' : 'secondary'}" style="font-size: 11px;">
                            ${inq.status.charAt(0).toUpperCase() + inq.status.slice(1)}
                        </span>
                    </td>
                    <td style="text-align: center;">
                        <a href="../property-detail.php?slug=${inq.property_slug}" class="icon-btn"><i class="fa-solid fa-eye"></i></a>
                    </td>
                </tr>
            `).join('');
        }
    } catch (error) {
        console.error('Error fetching inquiries:', error);
    }
}

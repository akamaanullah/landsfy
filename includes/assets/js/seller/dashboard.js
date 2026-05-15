document.addEventListener('DOMContentLoaded', () => {
    fetchDashboardStats();
});

async function fetchDashboardStats() {
    try {
        const response = await fetch('../includes/api/seller/dashboard_stats.php');
        const result = await response.json();

        if (result.success) {
            updateStatsUI(result.stats);
            renderRecentLeads(result.recent_leads);
            renderViewsShare(result.views_share, result.stats.total_views);
        }
    } catch (error) {
        console.error('Error fetching dashboard stats:', error);
    }
}

function updateStatsUI(stats) {
    const cards = document.querySelectorAll('.stat-card .stat-value');
    if (cards.length >= 4) {
        cards[0].innerText = stats.total_properties.toString().padStart(2, '0');
        cards[1].innerText = Landsfy.formatNumber(stats.total_views);
        cards[2].innerText = stats.total_leads.toString().padStart(2, '0');
        cards[3].innerText = stats.total_sold.toString().padStart(2, '0');
    }
}


function renderRecentLeads(leads) {
    const tbody = document.querySelector('.data-table tbody');
    if (!tbody) return;

    if (leads.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; padding: 40px; color: var(--text-secondary);">No recent inquiries found.</td></tr>`;
        return;
    }

    tbody.innerHTML = leads.map(lead => `
        <tr style="border-bottom: 1px solid var(--glass-border);">
            <td style="padding: 16px 0;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <img src="${lead.buyer_avatar ? '../' + lead.buyer_avatar : 'https://i.pravatar.cc/150?u=' + lead.id}" style="width: 40px; height: 40px; border-radius: 12px; object-fit: cover;">
                    <div>
                        <div style="font-weight: 800; font-size: 14px;">${lead.buyer_name || 'Anonymous User'}</div>
                        <div style="font-size: 12px; color: var(--text-secondary);">Inquiry ID: #${lead.id}</div>
                    </div>
                </div>
            </td>
            <td style="padding: 16px 0;">
                <div style="font-weight: 700; font-size: 13px;">${lead.property_title}</div>
                <div style="font-size: 11px; color: var(--text-secondary);">PKR ${parseFloat(lead.price).toLocaleString()}</div>
            </td>
            <td style="text-align: center; font-size: 13px; color: var(--text-secondary);">
                ${new Date(lead.created_at).toLocaleDateString()}
            </td>
            <td style="text-align: center;">
                <div style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 20px; background: ${lead.interaction_type === 'whatsapp_click' ? 'rgba(37, 211, 102, 0.1)' : 'rgba(59, 130, 246, 0.1)'}; color: ${lead.interaction_type === 'whatsapp_click' ? '#25D366' : 'var(--info)'}; font-size: 12px; font-weight: 700;">
                    <i class="fa-solid ${lead.interaction_type === 'whatsapp_click' ? 'fa-whatsapp' : 'fa-phone'}"></i> 
                    ${lead.interaction_type === 'whatsapp_click' ? 'WhatsApp' : 'Call Reveal'}
                </div>
            </td>
            <td style="text-align: center;">
                <button class="icon-btn" onclick="window.location.href='leads.php'" style="background: var(--primary); color: white; width: 32px; height: 32px; border-radius: 10px;">
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function renderViewsShare(data, totalViews) {
    const chart = document.getElementById('viewsShareChart');
    const legend = document.getElementById('viewsShareLegend');
    const chartText = document.getElementById('totalViewsChartText');
    
    if (!chart || !legend) return;

    chartText.innerText = Landsfy.formatNumber(totalViews);

    if (!data || data.length === 0) {
        legend.innerHTML = `<div style="text-align: center; font-size: 12px; color: var(--text-secondary);">No view data yet.</div>`;
        chart.style.background = 'var(--glass-border)';
        return;
    }

    const colors = ['var(--primary)', 'var(--info)', 'var(--warning)'];
    let gradientParts = [];
    let currentPercentage = 0;

    legend.innerHTML = '';

    data.forEach((item, index) => {
        const percentage = totalViews > 0 ? Math.round((item.views_total / totalViews) * 100) : 0;
        const color = colors[index % colors.length];

        // Add to legend
        legend.innerHTML += `
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: rgba(0,0,0,0.02); border-radius: 12px;">
                <span style="display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;">
                    <i class="fa-solid fa-circle" style="color: ${color};"></i> ${item.title}
                </span>
                <span style="font-weight: 800;">${percentage}%</span>
            </div>
        `;

        // Add to chart gradient
        gradientParts.push(`${color} ${currentPercentage}% ${currentPercentage + percentage}%`);
        currentPercentage += percentage;
    });

    // Fill remaining space if any
    if (currentPercentage < 100) {
        gradientParts.push(`var(--glass-border) ${currentPercentage}% 100%`);
    }

    chart.style.background = `conic-gradient(${gradientParts.join(', ')})`;
}

document.addEventListener('DOMContentLoaded', () => {

    // 1. Fetch Stats Grid
    fetch('../includes/api/agent/dashboard_stats.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('statListings').textContent = data.data.total_listings;
                document.getElementById('statWhatsapp').textContent = data.data.whatsapp_clicks;
                document.getElementById('statCalls').textContent = data.data.call_inquiries;
                document.getElementById('statRating').textContent = data.data.agent_rating;
            }
        })
        .catch(console.error);

    // 2. Fetch Hot Listings
    fetch('../includes/api/agent/hot_listings.php')
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById('hotListingsTbody');
            if (data.success && data.data.length > 0) {
                tbody.innerHTML = data.data.map(listing => {
                    let engagementRatio = (parseInt(listing.whatsapp_clicks) + parseInt(listing.call_inquiries)) / (parseInt(listing.views_total) || 1);
                    let tag = engagementRatio > 0.1
                        ? `<span class="badge-tag status-active" style="display: inline-flex; align-items: center; gap: 4px;"><i class="fa-solid fa-trend-up"></i> High</span>`
                        : `<span class="badge-tag status-info" style="display: inline-flex; align-items: center; gap: 4px;"><i class="fa-solid fa-minus"></i> Medium</span>`;

                    return `
                        <tr>
                            <td>
                                <div style="font-weight: 700; color: var(--text-primary);">${listing.title}</div>
                                <div style="font-size: 11px; color: var(--text-secondary);"><i class="fa-solid fa-location-dot"></i> ${listing.city_name || 'N/A'}</div>
                            </td>
                            <td style="text-align: center; font-weight: 600;">${listing.views_total}</td>
                            <td style="text-align: center; font-weight: 600; color: #25D366;">${listing.whatsapp_clicks}</td>
                            <td style="text-align: center; font-weight: 600; color: var(--primary);">${listing.call_inquiries}</td>
                            <td style="text-align: center;">${tag}</td>
                        </tr>
                    `;
                }).join('');
            } else {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; padding: 20px; color: var(--text-secondary);">No active listings found or no engagement data yet.</td></tr>`;
            }
        })
        .catch(console.error);

    // 3. Fetch Recent Inquiries Timeline
    fetch('../includes/api/agent/recent_inquiries.php')
        .then(res => res.json())
        .then(data => {
            const timeline = document.getElementById('recentTimeline');
            if (data.success && data.data.length > 0) {
                timeline.innerHTML = data.data.map(act => {
                    let isGuest = !act.user_name;
                    let userDisplay = isGuest ? 'Guest User' : act.user_name;
                    let actionVerb = act.interaction_type === 'whatsapp_click' ? 'clicked WhatsApp' : 'revealed phone number';
                    let iconObj = act.interaction_type === 'whatsapp_click'
                        ? `<div class="adb-timeline-icon" style="background: rgba(37, 211, 102, 0.1); color: #25D366;"><i class="fa-brands fa-whatsapp"></i></div>`
                        : `<div class="adb-timeline-icon" style="background: rgba(107, 0, 182, 0.1); color: var(--primary);"><i class="fa-solid fa-phone"></i></div>`;

                    return `
                        <div class="adb-timeline-item">
                            ${iconObj}
                            <div class="adb-timeline-content">
                                <div class="adb-timeline-title"><strong>${userDisplay}</strong> ${actionVerb} for <strong>${act.property_title}</strong></div>
                                <div class="adb-timeline-time">${new Date(act.created_at).toLocaleDateString()}</div>
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                timeline.innerHTML = `<div style="text-align: center; padding: 20px; color: var(--text-secondary);">No recent activity tracked yet.</div>`;
            }
        })
        .catch(console.error);
});

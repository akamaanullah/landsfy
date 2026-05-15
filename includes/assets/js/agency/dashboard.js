document.addEventListener('DOMContentLoaded', function() {
    fetchDashboardData();

    // Theme Toggle Logic
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    
    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            themeIcon.className = newTheme === 'light' ? 'fa-solid fa-moon' : 'fa-solid fa-sun';
            localStorage.setItem('theme', newTheme);
            
            // Save to DB
            saveThemeSetting(newTheme);
        });
    }
});

async function fetchDashboardData() {
    try {
        const response = await fetch('../includes/api/agency/get_dashboard_data.php');
        const data = await response.json();
        
        if (data.success) {
            // Update Stats
            const statCards = document.querySelectorAll('.stat-card .stat-value');
            if (statCards.length >= 4) {
                statCards[0].textContent = data.stats.total_listings;
                statCards[1].textContent = data.stats.total_agents;
                statCards[2].textContent = data.stats.monthly_leads;
                statCards[3].textContent = data.stats.avg_rating;
            }

            // Update Top Agents Table
            const agentsTableBody = document.querySelector('.data-table tbody');
            if (agentsTableBody) {
                if (data.top_agents.length === 0) {
                    agentsTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px;">No agents found.</td></tr>';
                } else {
                    agentsTableBody.innerHTML = data.top_agents.map(agent => `
                        <tr>
                            <td data-label="Agent Name">
                                <div class="adb-agent-info">
                                    <img src="${agent.avatar_url ? '../' + agent.avatar_url : 'https://i.pravatar.cc/150?img=1'}" class="adb-agent-avatar">
                                    <span class="adb-agent-name">${agent.full_name}</span>
                                </div>
                            </td>
                            <td data-label="Listings">${agent.listings_count}</td>
                            <td data-label="Leads">${agent.leads_count}</td>
                            <td data-label="Status"><span class="badge-tag status-active">Active</span></td>
                        </tr>
                    `).join('');
                }
            }

            // Update Recent Activity (Leads)
            const timeline = document.querySelector('.adb-timeline');
            if (timeline) {
                if (data.recent_leads.length === 0) {
                    timeline.innerHTML = '<div style="padding: 20px; text-align: center; color: var(--text-secondary);">No recent activity.</div>';
                } else {
                    timeline.innerHTML = data.recent_leads.map(lead => `
                        <div class="adb-timeline-item">
                            <div class="adb-timeline-icon" style="background: rgba(107, 0, 182, 0.1); color: var(--primary);"><i class="fa-solid fa-chat-circle"></i></div>
                            <div class="adb-timeline-content">
                                <div class="adb-timeline-title">New lead from <strong>${lead.sender_name}</strong> for <strong>${lead.property_title}</strong></div>
                                <div class="adb-timeline-time">${formatTimeAgo(lead.created_at)}</div>
                            </div>
                        </div>
                    `).join('');
                }
            }

        } else {
            console.error('API Error:', data.message);
        }
    } catch (error) {
        console.error('Error fetching dashboard data:', error);
    }
}

function formatTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);
    
    if (seconds < 60) return 'Just now';
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return `${minutes}m ago`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours}h ago`;
    const days = Math.floor(hours / 24);
    if (days < 7) return `${days}d ago`;
    return date.toLocaleDateString();
}

async function saveThemeSetting(theme) {
    const formData = new FormData();
    formData.append('action', 'save_theme');
    formData.append('theme', theme);
    formData.append('key', 'agency_theme');
    
    fetch('../includes/api/admin/save_site_settings.php', {
        method: 'POST',
        body: formData
    });
}

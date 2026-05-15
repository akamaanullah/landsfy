/**
 * Landsfy Agents Directory Logic
 */

let currentAgentPage = 1;
let agentSearchQuery = '';
let selectedAgentCity = '';

document.addEventListener('DOMContentLoaded', () => {
    initAgents();
});

async function initAgents() {
    loadAgentCities();
    fetchAgents();

    // Search Click
    document.getElementById('btnSearchAgents').onclick = () => {
        agentSearchQuery = document.getElementById('agentSearchInput').value;
        selectedAgentCity = document.getElementById('agentCityFilter').value;
        currentAgentPage = 1;
        fetchAgents();
    };

    // Sort Change
    document.getElementById('agentSortOrder').onchange = () => {
        currentAgentPage = 1;
        fetchAgents();
    };
}

async function loadAgentCities() {
    try {
        const response = await fetch(window.BASE_PATH + 'includes/api/website/home_data.php');
        const result = await response.json();
        
        if (result.success) {
            const citySelect = document.getElementById('agentCityFilter');
            result.data.cities.forEach(city => {
                const opt = document.createElement('option');
                opt.value = city.id;
                opt.textContent = city.name;
                citySelect.appendChild(opt);
            });
        }
    } catch (e) {
        console.error("City load failed", e);
    }
}

async function fetchAgents() {
    const grid = document.getElementById('agentsGrid');
    grid.innerHTML = '<div class="loading-state"><div class="spinner"></div><p>Searching professionals...</p></div>';

    try {
        const response = await fetch(`${window.BASE_PATH}includes/api/website/agents_data.php?search=${agentSearchQuery}&city=${selectedAgentCity}&page=${currentAgentPage}`);
        const result = await response.json();

        if (result.success) {
            renderAgents(result.data);
            renderAgentPagination(result.meta);
            document.getElementById('agentsCountText').textContent = `Showing ${result.meta.total} agents`;
        }
    } catch (error) {
        grid.innerHTML = '<p class="error-msg">Failed to load agents. Please try again.</p>';
    }
}

function renderAgents(agents) {
    const grid = document.getElementById('agentsGrid');
    if (agents.length === 0) {
        grid.innerHTML = `
            <div class="no-results-full">
                <i class="fa-solid fa-users"></i>
                <h3>No Agents Found</h3>
                <p>We couldn't find any real estate professionals matching your search. Try adjusting your filters or search keywords.</p>
                <button class="btn btn-outline-sm mt-3" onclick="location.reload()">Reset All</button>
            </div>
        `;
        return;
    }

    let html = '';
    agents.forEach(a => {
        const avatar = a.avatar_url || 'includes/assets/images/website/placeholder-avatar.jpg';
        const rating = parseFloat(a.avg_rating || 0).toFixed(1);
        
        html += `
            <div class="agent-card">
                <div class="agent-card-inner">
                    <div class="agent-top-info">
                        <div class="agent-photo-wrapper">
                            <img src="${avatar}" alt="${a.full_name}" onerror="this.src='includes/assets/images/website/placeholder-avatar.jpg'">
                            ${rating >= 4.5 ? '<span class="agent-badge-gold"><i class="fa-solid fa-crown"></i></span>' : ''}
                        </div>
                        <div class="agent-main-details">
                            <h3 class="agent-name">${a.full_name}</h3>
                            <p class="agent-specialty">${a.specialization || 'Real Estate Expert'}</p>
                            <div class="agent-agency-link">
                                <i class="fa-solid fa-building"></i>
                                <span>${a.agency_name || 'Independent Agent'}</span>
                            </div>
                        </div>
                    </div>

                    <div class="agent-stats-row">
                        <div class="a-stat">
                            <strong>${a.property_count}</strong>
                            <span>Listings</span>
                        </div>
                        <div class="a-stat">
                            <strong>${a.experience_years || 0}</strong>
                            <span>Years Exp.</span>
                        </div>
                        <div class="a-stat">
                            <strong>${rating}</strong>
                            <span>Rating</span>
                        </div>
                    </div>

                    <div class="agent-actions">
                        <a href="agents/${a.slug}" class="btn-view-profile">View Profile</a>
                        ${a.phone ? `<button class="btn-agent-whatsapp-link" onclick="window.location.href='https://wa.me/${a.phone.replace(/\+/g, '')}'"><i class="fa-brands fa-whatsapp"></i></button>` : ''}
                    </div>
                </div>
            </div>
        `;
    });
    grid.innerHTML = html;
}

function renderAgentPagination(meta) {
    const container = document.getElementById('agentsPagination');
    if (meta.total_pages <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = '';
    for (let i = 1; i <= meta.total_pages; i++) {
        html += `<a href="javascript:void(0)" class="page-link ${i === meta.page ? 'active' : ''}" onclick="changeAgentPage(${i})">${i}</a>`;
    }
    container.innerHTML = html;
}

function changeAgentPage(page) {
    currentAgentPage = page;
    fetchAgents();
    window.scrollTo({ top: 300, behavior: 'smooth' });
}

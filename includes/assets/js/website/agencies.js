/**
 * Landsfy Agencies Logic
 */

let currentAgencyPage = 1;
let selectedAgencyCity = '';
let agencySearchQuery = '';

document.addEventListener('DOMContentLoaded', () => {
    initAgencies();
});

function initAgencies() {
    fetchAgencies();
    loadAgencyFilters();

    // Search Trigger
    document.getElementById('btnSearchAgencies').onclick = () => {
        agencySearchQuery = document.getElementById('agencyNameSearch').value;
        currentAgencyPage = 1;
        fetchAgencies();
    };

    // Clear Filters
    document.getElementById('btnClearAgencies').onclick = () => {
        document.getElementById('agencyNameSearch').value = '';
        agencySearchQuery = '';
        selectedAgencyCity = '';
        currentAgencyPage = 1;
        
        // Uncheck all radio buttons
        const radios = document.querySelectorAll('input[name="city_filter"]');
        radios.forEach(r => r.checked = false);
        
        fetchAgencies();
    };
}

async function fetchAgencies() {
    const grid = document.getElementById('agenciesGrid');
    grid.innerHTML = '<div class="loading-state"><div class="spinner"></div><p>Searching agencies...</p></div>';

    try {
        const response = await fetch(`${window.BASE_PATH}includes/api/website/agencies_data.php?search=${agencySearchQuery}&city=${selectedAgencyCity}&page=${currentAgencyPage}`);
        const result = await response.json();

        if (result.success) {
            renderAgencies(result.data);
            renderAgencyPagination(result.meta);
            document.getElementById('agenciesCountText').textContent = `Showing ${result.meta.total} agencies`;
        }
    } catch (error) {
        grid.innerHTML = '<p class="error-msg">Failed to load agencies. Please try again.</p>';
    }
}

function renderAgencies(agencies) {
    const grid = document.getElementById('agenciesGrid');
    if (agencies.length === 0) {
        grid.innerHTML = `
            <div class="no-results-full">
                <i class="fa-solid fa-city"></i>
                <h3>No Agencies Found</h3>
                <p>We couldn't find any agencies matching your current search or filters. Try checking your spelling or broadening your search.</p>
                <button class="btn btn-outline-sm mt-3" onclick="document.getElementById('btnClearAgencies').click()">Clear All Filters</button>
            </div>
        `;
        return;
    }

    let html = '';
    agencies.forEach(a => {
        const logoHtml = a.logo_url ? `<img src="${a.logo_url}" alt="${a.name}">` : `<i class="fa-solid fa-city"></i>`;
        const rating = parseFloat(a.avg_rating).toFixed(1);
        
        html += `
            <div class="agency-card">
                <div class="agency-card-inner">
                    <div class="agency-header">
                        <div class="agency-logo-wrapper">
                            ${logoHtml}
                            ${a.is_verified == 1 ? '<span class="verified-badge" title="Verified Agency"><i class="fa-solid fa-circle-check"></i></span>' : ''}
                        </div>
                        <div class="agency-info">
                            <h3>${a.name}</h3>
                            <p><i class="fa-solid fa-location-dot"></i> ${a.address || 'Pakistan'}</p>
                        </div>
                    </div>
                    <div class="agency-stats">
                        <div class="stat-item">
                            <strong>${a.property_count}</strong>
                            <span>Properties</span>
                        </div>
                        <div class="stat-item">
                            <strong>${a.agent_count}</strong>
                            <span>Agents</span>
                        </div>
                        <div class="stat-item">
                            <strong>${rating}</strong>
                            <span>Rating</span>
                        </div>
                    </div>
                    <div class="agency-actions">
                        <a href="agencies/${a.slug}" class="btn-view-agency">View Profile</a>
                        ${a.phone ? `<button class="btn-agency-contact" title="Call Agency" onclick="window.location.href='tel:${a.phone}'"><i class="fa-solid fa-phone"></i></button>` : ''}
                    </div>
                </div>
            </div>
        `;
    });
    grid.innerHTML = html;
}

async function loadAgencyFilters() {
    try {
        const response = await fetch(window.BASE_PATH + 'includes/api/website/home_data.php');
        const result = await response.json();
        
        if (result.success) {
            const cityCheckboxes = document.getElementById('cityCheckboxes');
            
            cityCheckboxes.innerHTML = '';
            result.data.cities.forEach(city => {
                // Add to Checkboxes (Sidebar)
                const label = document.createElement('label');
                label.className = 'check-container';
                label.innerHTML = `<input type="radio" name="city_filter" value="${city.slug}"> <span>${city.name}</span>`;
                
                label.querySelector('input').onchange = (e) => {
                    if (e.target.checked) {
                        selectedAgencyCity = e.target.value;
                        currentAgencyPage = 1;
                        fetchAgencies();
                    }
                };
                cityCheckboxes.appendChild(label);
            });
        }
    } catch (e) {
        console.error("Filter load failed", e);
    }
}

function renderAgencyPagination(meta) {
    const container = document.getElementById('agenciesPagination');
    if (meta.total_pages <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = '';
    for (let i = 1; i <= meta.total_pages; i++) {
        html += `<button class="page-btn ${i === meta.page ? 'active' : ''}" onclick="changeAgencyPage(${i})">${i}</button>`;
    }
    container.innerHTML = html;
}

function changeAgencyPage(p) {
    currentAgencyPage = p;
    fetchAgencies();
    window.scrollTo({ top: 300, behavior: 'smooth' });
}

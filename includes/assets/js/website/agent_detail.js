/**
 * Landsfy Agent Detail Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    let agentId = urlParams.get('id');
    let slug = '';

    // SEO Friendly URL support
    if (!agentId) {
        const pathParts = window.location.pathname.split('/');
        if (pathParts.includes('agents')) {
            slug = pathParts[pathParts.indexOf('agents') + 1];
        }
    }

    if (agentId || slug) {
        fetchAgentDetail(agentId, slug);
    } else {
        window.location.href = window.BASE_PATH + 'agents';
    }
});

async function fetchAgentDetail(id, slug = '') {
    try {
        const url = slug 
            ? `includes/api/website/agent_detail_data.php?slug=${slug}`
            : `includes/api/website/agent_detail_data.php?id=${id}`;
            
        const response = await fetch(url);
        const result = await response.json();

        if (result.success) {
            renderAgentProfile(result.data);
        } else {
            console.error("Agent fetch failed", result.message);
        }
    } catch (error) {
        console.error("Connection error", error);
    }
}

function renderAgentProfile(data) {
    const agent = data.agent;
    const props = data.properties;
    const stats = data.stats;

    // 1. Header Info
    document.getElementById('agentName').textContent = agent.full_name;
    document.getElementById('agentFirstName').textContent = agent.full_name.split(' ')[0];
    document.getElementById('agentSpecialty').textContent = agent.specialization || 'Real Estate Expert';
    document.getElementById('agentLocation').textContent = agent.agency_address || 'Pakistan';
    document.getElementById('agentAgencyLink').innerHTML = `Associated with <strong>${agent.agency_name || 'Independent'}</strong>`;
    
    const avatarImg = agent.avatar_url ? `<img src="${agent.avatar_url}" alt="${agent.full_name}">` : `<i class="fa-solid fa-user"></i>`;
    document.getElementById('agentAvatar').innerHTML = `${avatarImg}<span class="v-badge"><i class="fa-solid fa-circle-check"></i></span>`;

    // 2. Stats
    document.getElementById('statExp').textContent = agent.experience_years ? agent.experience_years + '+' : '0';
    document.getElementById('statActive').textContent = stats.active_listings;
    
    // 3. Properties Grid
    const propsGrid = document.getElementById('agentPropertiesGrid');
    propsGrid.innerHTML = '';
    if (props.length === 0) {
        propsGrid.innerHTML = '<div class="no-data">No active listings from this agent.</div>';
    } else {
        props.forEach(p => {
            const priceLabel = Landsfy.formatPrice(p.price);
            propsGrid.innerHTML += `
                <div class="property-card" onclick="window.location.href='${window.BASE_PATH}properties/${p.slug}'">
                    <div class="property-img-wrapper">
                        <img src="${p.thumbnail || 'includes/assets/images/website/placeholder-property.jpg'}" class="property-img">
                        <div class="property-badge">${p.purpose === 'sell' ? 'For Sale' : 'For Rent'}</div>
                    </div>
                    <div class="property-content">
                        <h3 class="property-title">${p.title}</h3>
                        <div class="property-location"><i class="fa-solid fa-location-dot"></i> ${p.location_name}, ${p.city_name}</div>
                        <div class="property-price">${priceLabel}</div>
                        <div class="property-specs">
                            <div class="spec-item"><i class="fa-solid fa-bed"></i> ${p.beds || 0} Bed</div>
                            <div class="spec-item"><i class="fa-solid fa-bath"></i> ${p.baths || 0} Bath</div>
                            <div class="spec-item"><i class="fa-solid fa-vector-square"></i> ${p.area_size} ${p.area_unit}</div>
                        </div>
                    </div>
                </div>
            `;
        });
        if (stats.active_listings > 6) {
            document.getElementById('btnViewAllProps').style.display = 'block';
            document.getElementById('btnViewAllProps').onclick = () => {
                window.location.href = `${window.BASE_PATH}properties?agent_id=${agent.id}`;
            };
        }
    }

    // 4. Bio
    document.getElementById('agentBio').innerHTML = `<p>${agent.bio || 'No biography provided yet.'}</p>`;

    // 5. Sidebar Agency
    if (agent.agency_id) {
        const agencyLogo = agent.agency_logo ? `<img src="${agent.agency_logo}" alt="Agency">` : `<i class="fa-solid fa-building"></i>`;
        document.getElementById('sideAgencyLogo').innerHTML = agencyLogo;
        document.getElementById('sideAgencyName').textContent = agent.agency_name;
        document.getElementById('sideAgencyDesc').textContent = `Professional Real Estate services in ${agent.city_name || 'Pakistan'}.`;
        document.getElementById('btnVisitAgency').href = `agencies/${agent.agency_slug}`;
    }

    // 6. Contact Actions
    if (agent.phone) {
        const phone = agent.phone;
        const whatsappLink = `https://wa.me/${phone.replace(/\+/g, '')}`;

        document.getElementById('btnAgentCall').onclick = () => window.location.href = `tel:${phone}`;
        document.getElementById('btnAgentWhatsapp').onclick = () => window.location.href = whatsappLink;
        document.getElementById('sideCallBtn').href = `tel:${phone}`;
        document.getElementById('sideWhatsappBtn').href = whatsappLink;
    } else {
        document.getElementById('btnAgentCall').style.display = 'none';
        document.getElementById('btnAgentWhatsapp').style.display = 'none';
        document.getElementById('sideCallBtn').style.display = 'none';
        document.getElementById('sideWhatsappBtn').style.display = 'none';
    }
}


/**
 * Landsfy Agency Detail Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    let agencyId = urlParams.get('id');
    let slug = '';

    // SEO Friendly URL support
    if (!agencyId) {
        const pathParts = window.location.pathname.split('/');
        if (pathParts.includes('agencies')) {
            slug = pathParts[pathParts.indexOf('agencies') + 1];
        }
    }

    if (agencyId || slug) {
        fetchAgencyDetail(agencyId, slug);
    } else {
        window.location.href = window.BASE_PATH + 'agencies';
    }
});

async function fetchAgencyDetail(id, slug = '') {
    try {
        const url = slug 
            ? `includes/api/website/agency_detail_data.php?slug=${slug}`
            : `includes/api/website/agency_detail_data.php?id=${id}`;
            
        const response = await fetch(url);
        const result = await response.json();

        if (result.success) {
            renderAgencyProfile(result.data);
        } else {
            console.error("Agency fetch failed", result.message);
        }
    } catch (error) {
        console.error("Connection error", error);
    }
}

function renderAgencyProfile(data) {
    const agency = data.agency;
    const stats = data.stats;
    const props = data.properties;
    const agents = data.agents;

    // 1. Header & Meta
    if(document.getElementById('agencyName')) document.getElementById('agencyName').textContent = agency.name;
    if(document.getElementById('agencyLocation')) document.getElementById('agencyLocation').innerHTML = `<i class="fa-solid fa-location-dot"></i> ${agency.address || 'Pakistan'}`;
    
    const logoHtml = agency.logo_url ? `<img src="${agency.logo_url}" alt="${agency.name}">` : `<i class="fa-solid fa-building"></i>`;
    if(document.getElementById('agencyLogo')) document.getElementById('agencyLogo').innerHTML = `${logoHtml} ${agency.is_verified == 1 ? '<span class="verified-badge"><i class="fa-solid fa-circle-check"></i></span>' : ''}`;

    if(document.getElementById('agencyBadges')) {
        document.getElementById('agencyBadges').innerHTML = `
            <span class="badge-status-active">Active</span>
            ${agency.is_premium == 1 ? '<span class="badge-premium"><i class="fa-solid fa-crown"></i> Premium Agency</span>' : ''}
        `;
    }

    // 2. Stats (Only if elements exist)
    if(document.getElementById('statListings')) document.getElementById('statListings').textContent = stats.total_listings;
    if(document.getElementById('statAgents')) document.getElementById('statAgents').textContent = stats.total_agents;
    if(document.getElementById('listingsCount')) document.getElementById('listingsCount').textContent = stats.total_listings;

    // 3. Properties Grid
    const propsGrid = document.getElementById('agencyPropertiesGrid');
    if (propsGrid) {
        propsGrid.innerHTML = '';
        if (props.length === 0) {
            propsGrid.innerHTML = '<div class="no-data">No active listings for this agency yet.</div>';
        } else {
            props.forEach(p => {
                const priceLabel = Landsfy.formatPrice(p.price);
                propsGrid.innerHTML += `
                    <div class="property-card" onclick="window.location.href='${window.BASE_PATH}properties/${p.slug}'">
                        <div class="property-img-wrapper">
                            <img src="${p.thumbnail || 'includes/assets/images/website/placeholder-property.jpg'}" class="property-img">
                            <span class="property-badge">${p.purpose === 'sell' ? 'For Sale' : 'For Rent'}</span>
                        </div>
                        <div class="property-content">
                            <h3 class="property-title">${p.title}</h3>
                            <p class="property-location"><i class="fa-solid fa-location-dot"></i> ${p.location_name}, ${p.city_name}</p>
                            <p class="property-price">PKR ${priceLabel}</p>
                            <div class="property-specs">
                                <div class="spec-item"><i class="fa-solid fa-bed"></i> ${p.beds || 0} Beds</div>
                                <div class="spec-item"><i class="fa-solid fa-bath"></i> ${p.baths || 0} Baths</div>
                                <div class="spec-item"><i class="fa-solid fa-vector-square"></i> ${p.area_size} ${p.area_unit}</div>
                            </div>
                        </div>
                    </div>
                `;
            });
        }
    }

    // 4. Agents Grid
    const agentsGrid = document.getElementById('agencyAgentsGrid');
    if (agentsGrid) {
        agentsGrid.innerHTML = '';
        if (agents.length === 0) {
            agentsGrid.innerHTML = '<div class="no-data">No agents listed for this agency.</div>';
        } else {
            agents.forEach(a => {
                const avatar = a.avatar_url || 'includes/assets/images/website/placeholder-avatar.jpg';
                agentsGrid.innerHTML += `
                    <div class="agency-agent-card">
                        <div class="agent-card-left">
                            <div class="agent-photo-mini">
                                <img src="${avatar}" alt="${a.full_name}" onerror="this.src='includes/assets/images/website/placeholder-avatar.jpg'">
                            </div>
                        </div>
                        <div class="agent-card-right">
                            <h4>${a.full_name}</h4>
                            <p>${a.specialization || 'Real Estate Expert'}</p>
                            <a href="agents/${a.slug}" class="btn-view-agent-mini">View Profile <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </div>
                `;
            });
        }
    }

    // 5. About & Sidebar
    if(document.getElementById('agencyDescription')) {
        document.getElementById('agencyDescription').innerHTML = `<p>${agency.description || 'No description provided by the agency.'}</p>`;
    }
    
    // Sidebar Contacts
    if (agency.phone) {
        if(document.getElementById('sidePhone')) {
            document.getElementById('sidePhone').href = `tel:${agency.phone}`;
            document.getElementById('sidePhone').style.display = 'flex';
        }
        if(document.getElementById('textPhone')) document.getElementById('textPhone').textContent = agency.phone;
        
        if(document.getElementById('sideWhatsapp')) {
            const waPhone = agency.phone.replace(/\+/g, '');
            document.getElementById('sideWhatsapp').href = `https://wa.me/${waPhone}`;
            document.getElementById('sideWhatsapp').style.display = 'flex';
        }
        if(document.getElementById('btnContactAgencyTop')) {
            document.getElementById('btnContactAgencyTop').onclick = () => window.location.href = `tel:${agency.phone}`;
        }
    } else {
        if(document.getElementById('sidePhone')) document.getElementById('sidePhone').style.display = 'none';
        if(document.getElementById('sideWhatsapp')) document.getElementById('sideWhatsapp').style.display = 'none';
        if(document.getElementById('btnContactAgencyTop')) document.getElementById('btnContactAgencyTop').style.display = 'none';
    }
    
    if (agency.email) {
        if(document.getElementById('sideEmail')) {
            document.getElementById('sideEmail').href = `mailto:${agency.email}`;
            document.getElementById('sideEmail').style.display = 'flex';
        }
        if(document.getElementById('textEmail')) document.getElementById('textEmail').textContent = agency.email;
    } else {
        if(document.getElementById('sideEmail')) document.getElementById('sideEmail').style.display = 'none';
    }
    
    if (agency.address) {
        if(document.getElementById('sideAddress')) {
            document.getElementById('sideAddress').textContent = agency.address;
            document.getElementById('sideAddress').parentElement.style.display = 'block';
        }
    } else if (document.getElementById('sideAddress')) {
        document.getElementById('sideAddress').parentElement.style.display = 'none';
    }

    if (agency.website) {
        if(document.getElementById('sideWebsite')) {
            document.getElementById('sideWebsite').textContent = agency.website;
            document.getElementById('sideWebsite').parentElement.style.display = 'block';
        }
    } else if (document.getElementById('sideWebsite')) {
        document.getElementById('sideWebsite').parentElement.style.display = 'none';
    }
}


function copyAgencyLink() {
    navigator.clipboard.writeText(window.location.href);
    Landsfy.showToast("Agency profile link copied to clipboard!", "success");
}

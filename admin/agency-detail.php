<?php
include 'header.php';
?>
<style>
    .agency-banner {
        height: 200px;
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        border-radius: 24px;
        margin-bottom: -50px;
        overflow: hidden;
    }

    .agency-banner img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.6;
    }

    .agency-header-container {
        display: flex;
        align-items: flex-end;
        gap: 25px;
        padding: 0 40px;
        margin-bottom: 30px;
    }

    .agency-logo-large {
        width: 120px;
        height: 120px;
        background: var(--surface-bg);
        border: 4px solid var(--border-color);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .agency-logo-large img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .agency-title-area {
        flex: 1;
        padding-bottom: 5px;
    }

    .agency-name-h1 {
        font-size: 32px;
        font-weight: 800;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .agency-v-badge {
        font-size: 13px;
        padding: 4px 12px;
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
        border-radius: 8px;
        font-weight: 600;
        display: none;
        /* Shown via JS */
    }

    .agency-meta-list {
        display: flex;
        gap: 24px;
        color: var(--text-secondary);
        font-size: 14px;
    }

    .agency-meta-list span {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .metrics-overview {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }

    .tab-pane {
        display: none;
        animation: fadeIn 0.3s ease;
    }

    .tab-pane.active {
        display: block;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
<main class="main-content">
    <header class="header glass" style="margin-bottom: 30px;">
        <div class="header-left">
            <div class="breadcrumb" style="font-size: 13px; color: var(--text-secondary);">Agencies / Detail</div>
            <div class="page-title" id="headerAgencyName">Agency Profile</div>
        </div>
        <div class="header-actions">
            <button class="btn-primary" id="editAgencyBtn"><i class="fa-solid fa-pencil"></i> Edit Profile</button>
            <button class="btn-primary" id="verifyAgencyBtn" style="background: var(--success); display: none;"><i
                    class="fa-solid fa-circle-check"></i> Verify Agency</button>
        </div>
    </header>

    <div class="view-container">
        <div class="agency-banner" id="agencyBanner">
            <!-- Banner Image via JS -->
        </div>
        <div class="agency-header-container">
            <div class="agency-logo-large">
                <img src="../includes/assets/images/agency-placeholder.png" alt="Logo" id="agencyLogo">
            </div>
            <div class="agency-title-area">
                <h1 class="agency-name-h1">
                    <span id="agencyName">Loading...</span>
                    <span class="agency-v-badge" id="verifiedBadge"><i class="fa-solid fa-circle-check"></i>
                        Verified</span>
                </h1>
                <div class="agency-meta-list">
                    <span><i class="fa-solid fa-map-pin"></i> <span id="agencyLocation">--</span></span>
                    <span><i class="fa-solid fa-calendar"></i> Joined <span id="agencyJoined">--</span></span>
                    <span><i class="fa-solid fa-users-three"></i> <span id="agencyAgentCount">0</span> Agents</span>
                </div>
            </div>
        </div>

        <div class="metrics-overview">
            <div class="metric-card glass">
                <span class="stat-label">Total Listings</span>
                <span class="stat-value" id="statListings">0</span>
            </div>
            <div class="metric-card glass">
                <span class="stat-label">Properties Sold</span>
                <span class="stat-value" id="statSold">0</span>
            </div>
            <div class="metric-card glass">
                <span class="stat-label">Active Leads</span>
                <span class="stat-value" id="statLeads">0</span>
            </div>
            <div class="metric-card glass">
                <span class="stat-label">Rating</span>
                <span class="stat-value" id="statRating">4.8/5.0</span>
            </div>
        </div>

        <div class="tab-section">
            <div class="agency-tabs">
                <div class="agency-tab active" data-target="team-pane">Our Team</div>
                <div class="agency-tab" data-target="properties-pane" id="propsTabTitle">Properties (0)</div>
                <div class="agency-tab" data-target="reviews-pane">Reviews</div>
                <div class="agency-tab" data-target="docs-pane">Documents</div>
            </div>

            <div class="tab-panes">
                <div class="tab-pane active" id="team-pane">
                    <div class="agent-team-grid" id="teamGrid">
                        <div
                            style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--text-secondary);">
                            Loading team members...</div>
                    </div>
                </div>

                <div class="tab-pane" id="properties-pane">
                    <div class="property-grid" id="agencyPropertyGrid">
                        <div
                            style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--text-secondary);">
                            Loading properties...</div>
                    </div>
                </div>

                <div class="tab-pane" id="reviews-pane">
                    <div class="glass"
                        style="padding: 40px; text-align: center; color: var(--text-secondary); border-radius: 20px;">
                        No reviews found for this agency.
                    </div>
                </div>

                <div class="tab-pane" id="docs-pane">
                    <div class="card-panel glass" id="docsContainer" style="padding: 24px;">
                        <div style="text-align: center; color: var(--text-secondary);">No documents uploaded.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../includes/assets/js/script.js"></script>
<script src="../includes/assets/js/admin/agency-detail.js"></script>
</body>

</html>
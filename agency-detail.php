<?php 
require_once 'includes/database/db.php';
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT slug FROM agencies WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $slug = $stmt->fetchColumn();
    if ($slug) {
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: agencies/" . $slug);
        exit;
    }
}
include 'header.php'; 
?>

<main class="agency-detail-page">
    <!-- Agency Hero Section -->
    <section class="agency-hero-minimal">
        <div class="container">
            <div class="agency-profile-header" id="agencyProfileHeader">
                <div class="agency-brand-info">
                    <div class="detail-agency-logo" id="agencyLogo">
                        <!-- Dynamic Logo -->
                    </div>
                    <div class="agency-main-meta">
                        <h1 class="agency-name" id="agencyName">---</h1>
                        <p class="agency-location" id="agencyLocation"><i class="fa-solid fa-location-dot"></i> ---</p>
                        <div class="agency-badges" id="agencyBadges">
                            <!-- Dynamic Badges -->
                        </div>
                    </div>
                </div>
                <div class="agency-header-actions">
                    <button class="btn-share-agency" onclick="copyAgencyLink()"><i class="fa-solid fa-share-nodes"></i> Share Profile</button>
                    <button class="btn-contact-agency-main" id="btnContactAgencyTop"><i class="fa-solid fa-phone"></i> Contact Agency</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Bar -->
    <section class="agency-stats-bar">
        <div class="container">
            <div class="stats-grid" id="agencyStatsGrid" style="grid-template-columns: repeat(2, 1fr);">
                <div class="stat-item">
                    <strong id="statListings">0</strong>
                    <span>Total Listings</span>
                </div>
                <div class="stat-item">
                    <strong id="statAgents">0</strong>
                    <span>Expert Agents</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Navigation Tabs -->
    <nav class="agency-tabs-nav">
        <div class="container">
            <ul class="tabs-list">
                <li class="active"><a href="#properties">Active Listings</a></li>
                <li><a href="#agents">Our Agents</a></li>
                <li><a href="#about">About Agency</a></li>
            </ul>
        </div>
    </nav>

    <div class="container detail-content-layout">
        <!-- Main Content Area -->
        <div class="agency-main-column">
            
            <!-- Properties Section -->
            <section id="properties" class="content-section">
                <div class="section-header-flex">
                    <h2 class="section-title">Active Listings (<span id="listingsCount">0</span>)</h2>
                </div>
                
                <div class="agency-properties-grid" id="agencyPropertiesGrid">
                    <!-- Dynamic Properties -->
                    <div class="loading-state">Loading properties...</div>
                </div>
                <button class="btn-load-more" id="btnLoadMoreProps" style="display:none;">View All Properties</button>
            </section>

            <!-- Agents Section -->
            <section id="agents" class="content-section">
                <h2 class="section-title">Our Expert Agents</h2>
                <div class="agents-grid" id="agencyAgentsGrid">
                    <!-- Dynamic Agents -->
                </div>
            </section>

            <!-- About Section -->
            <section id="about" class="content-section">
                <h2 class="section-title">About Our Agency</h2>
                <div class="agency-description-box" id="agencyDescription">
                    <p>Loading description...</p>
                </div>
            </section>
        </div>

        <!-- Sidebar Area -->
        <aside class="agency-sidebar">
            <div class="sidebar-card contact-card">
                <h3>Contact Agency</h3>
                <div class="contact-links">
                    <a href="#" id="sidePhone" class="contact-btn phone">
                        <i class="fa-solid fa-phone"></i>
                        <span id="textPhone">---</span>
                    </a>
                    <a href="#" id="sideWhatsapp" class="contact-btn whatsapp">
                        <i class="fa-brands fa-whatsapp"></i>
                        <span>WhatsApp Chat</span>
                    </a>
                    <a href="#" id="sideEmail" class="contact-btn email">
                        <i class="fa-solid fa-envelope"></i>
                        <span id="textEmail">---</span>
                    </a>
                </div>
            </div>

            <div class="sidebar-card info-card">
                <h3>Office Information</h3>
                <ul class="info-list">
                    <li><i class="fa-solid fa-clock"></i> 09:00 AM - 06:00 PM</li>
                    <li><i class="fa-solid fa-location-dot"></i> <span id="sideAddress">---</span></li>
                    <li><i class="fa-solid fa-globe"></i> <span id="sideWebsite">---</span></li>
                </ul>
                <div class="social-links-grid" id="agencySocialLinks">
                    <!-- Dynamic Socials -->
                </div>
            </div>
        </aside>
    </div>
</main>

<?php include 'footer.php'; ?>
<script src="<?php echo $base_path; ?>includes/assets/js/website/agency_detail.js"></script>

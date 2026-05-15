<?php 
require_once 'includes/database/db.php';
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT slug FROM agents WHERE user_id = ?");
    $stmt->execute([$_GET['id']]);
    $slug = $stmt->fetchColumn();
    if ($slug) {
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: agents/" . $slug);
        exit;
    }
}
include 'header.php'; 
?>

<main class="agent-detail-page">
    <!-- Agent Hero Section -->
    <section class="agent-hero-header">
        <div class="container">
            <div class="agent-profile-wrap">
                <div class="agent-profile-main">
                    <div class="agent-avatar-large" id="agentAvatar">
                        <!-- Dynamic Avatar -->
                    </div>
                    <div class="agent-intro-text">
                        <div class="agent-meta-top">
                            <span class="a-specialty-tag" id="agentSpecialty">---</span>
                            <div class="a-rating-mini">
                                <i class="fa-solid fa-star"></i>
                                <span id="agentRatingText">0.0 (0 Reviews)</span>
                            </div>
                        </div>
                        <h1 class="a-name" id="agentName">---</h1>
                        <p class="a-agency-name" id="agentAgencyLink">Associated with ---</p>
                        <div class="a-location-line">
                            <i class="fa-solid fa-location-dot"></i>
                            <span id="agentLocation">---</span>
                        </div>
                    </div>
                </div>
                <div class="agent-header-actions">
                    <button class="btn-a-call" id="btnAgentCall"><i class="fa-solid fa-phone"></i> Call Agent</button>
                    <button class="btn-a-whatsapp" id="btnAgentWhatsapp"><i class="fa-brands fa-whatsapp"></i> WhatsApp</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Agent Stats Bar -->
    <div class="agent-stats-bar">
        <div class="container">
            <div class="a-stats-grid">
                <div class="a-stat-item">
                    <strong id="statExp">0</strong>
                    <span>Years Experience</span>
                </div>
                <div class="a-stat-item">
                    <strong id="statActive">0</strong>
                    <span>Active Listings</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Layout -->
    <section class="agent-content-section">
        <div class="container">
            <div class="agent-detail-layout">
                
                <!-- Left Content: Tabs & Listings -->
                <div class="agent-main-content">
                    <div class="agent-tabs-nav">
                        <ul class="tabs-list">
                            <li class="active"><a href="#listings">Active Listings</a></li>
                            <li><a href="#about">About Me</a></li>
                        </ul>
                    </div>

                    <div class="agent-tab-content">
                        <!-- Active Listings Grid -->
                        <div id="listings" class="tab-pane active">
                            <div class="agent-properties-grid" id="agentPropertiesGrid">
                                <!-- Dynamic Properties -->
                                <div class="loading-state">Loading properties...</div>
                            </div>
                            <button class="btn-load-more-a" id="btnViewAllProps" style="display:none;">View All Listings</button>
                        </div>

                        <!-- About Section -->
                        <div id="about" class="tab-pane">
                            <div class="a-about-text">
                                <h2 class="section-title">Professional Biography</h2>
                                <div id="agentBio">
                                    <p>Loading agent bio...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar: Agency Info -->
                <div class="agent-sidebar">
                    <div class="a-agency-card-fixed">
                        <div class="agency-badge">Official Partner</div>
                        <div class="sidebar-agency-logo" id="sideAgencyLogo">
                            <!-- Dynamic Agency Logo -->
                        </div>
                        <h3 id="sideAgencyName">---</h3>
                        <p id="sideAgencyDesc">---</p>
                        
                        <a href="#" id="btnVisitAgency" class="btn-sidebar-agency-visit">View Agency Profile</a>
                    </div>

                    <div class="quick-contact-sidebar">
                        <h4>Need Assistance?</h4>
                        <p><span id="agentFirstName">---</span> is available for a quick consultation regarding any property inquiries.</p>
                        <div class="sidebar-contact-btns">
                            <a href="#" id="sideCallBtn" class="btn-s-call"><i class="fa-solid fa-phone"></i> Call Now</a>
                            <a href="#" id="sideWhatsappBtn" class="btn-s-whatsapp"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>
<script src="<?php echo $base_path; ?>includes/assets/js/website/agent_detail.js"></script>

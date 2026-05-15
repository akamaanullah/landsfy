<?php 
require_once 'includes/database/db.php';
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT slug FROM properties WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $slug = $stmt->fetchColumn();
    if ($slug) {
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: properties/" . $slug);
        exit;
    }
}
include 'header.php'; 
?>

<main class="property-detail-page">
    <!-- Breadcrumb & Title Section -->
    <style>
        /* Definitive Mobile Fix for Property Detail */
        @media (max-width: 767px) {
            html, body { 
                overflow-x: hidden !important; 
                width: 100% !important; 
                position: relative !important;
            }
            .container { 
                width: 100% !important; 
                max-width: 100vw !important; 
                padding: 0 15px !important;
                margin: 0 !important;
                box-sizing: border-box !important;
            }
            .detail-layout { 
                display: block !important; 
                width: 100% !important; 
            }
            .detail-content-area {
                width: 100% !important;
                max-width: 100% !important;
                overflow: hidden !important;
            }
            .detail-header-section { padding: 20px 0 !important; width: 100% !important; }
            .detail-breadcrumb { 
                display: flex !important; 
                flex-wrap: wrap !important; 
                gap: 5px 8px !important; 
                margin-bottom: 10px !important;
            }
            .detail-title { 
                font-size: 19px !important; 
                line-height: 1.4 !important;
                word-wrap: break-word !important; 
                overflow-wrap: break-word !important;
                display: block !important;
                width: 100% !important;
            }
            
            /* Slider Force Fix */
            .detail-gallery-wrapper { 
                width: 100% !important; 
                padding: 0 !important; 
                margin-bottom: 20px !important;
            }
            .main-gallery-slider { 
                width: 100% !important; 
                max-width: 100vw !important; 
                aspect-ratio: 4/3 !important; 
                position: relative !important;
                overflow: hidden !important;
            }
            .slider-container { 
                width: 100% !important; 
                height: 100% !important;
                overflow: hidden !important; 
            }
            .slider-track { 
                display: flex !important; 
                width: 100% !important; 
                height: 100% !important;
                transition: transform 0.4s ease-out !important;
            }
            .slide { 
                flex: 0 0 100% !important; 
                width: 100% !important; 
                max-width: 100% !important; 
                min-width: 100% !important;
                height: 100% !important;
            }
            .slide img, .generic-img-placeholder { 
                width: 100% !important; 
                height: 100% !important; 
                object-fit: cover !important; 
            }
            
            .gallery-thumbnails {
                display: flex !important;
                overflow-x: auto !important;
                gap: 8px !important;
                padding: 10px 0 !important;
                -webkit-overflow-scrolling: touch !important;
            }
            .thumb-item {
                flex: 0 0 80px !important;
                height: 60px !important;
            }
            .desc-content p, .desc-list-item {
                margin-bottom: 12px !important;
                line-height: 1.7 !important;
                color: #334155 !important;
                font-size: 14px !important;
            }
            .desc-list-item {
                padding-left: 5px !important;
                display: block !important;
            }
            .desc-content p:last-child, .desc-list-item:last-child { margin-bottom: 0 !important; }
            
            /* Add extra gap before list sections */
            .desc-list-item + p { margin-top: 20px !important; }
            p + .desc-list-item { margin-top: 10px !important; }
        }

        /* Similar Properties Slider Styles */
        .similar-properties-section { padding: 80px 0; background: #fff; }
        .similar-slider-wrapper { overflow: hidden; position: relative; margin-top: 30px; }
        .similar-slider-container { 
            display: flex; 
            gap: 24px; 
            transition: transform 0.4s ease; 
            scroll-behavior: smooth;
            padding-bottom: 10px;
        }
        .similar-slider-container .property-card { 
            flex: 0 0 calc(33.333% - 16px); 
            min-width: 300px; 
        }
        .similar-slider-controls { display: flex; gap: 12px; }
        .similar-nav {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 1px solid #e2e8f0;
            background: white;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 18px;
        }
        .similar-nav:hover { background: var(--primary); color: white; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.2); }
        .section-header { display: flex; justify-content: space-between; align-items: flex-end; }

        @media (max-width: 991px) {
            .similar-slider-container .property-card { flex: 0 0 calc(50% - 12px); }
        }
        @media (max-width: 767px) {
            .similar-properties-section { padding: 40px 0; }
            .similar-slider-container { gap: 16px; overflow-x: auto; -webkit-overflow-scrolling: touch; padding: 0 5px 15px; }
            .similar-slider-container .property-card { flex: 0 0 280px; }
            .similar-slider-controls { display: none; }
        }
    </style>
    <section class="detail-header-section">
        <div class="container">
            <nav class="detail-breadcrumb" id="detailBreadcrumb">
                <a href="<?php echo $base_path; ?>index">Home</a> <i class="fa-solid fa-chevron-right"></i>
                <a href="<?php echo $base_path; ?>properties">Properties</a> <i class="fa-solid fa-chevron-right"></i>
                <span id="breadcrumbTitle">Loading...</span>
            </nav>
            
            <div class="detail-title-wrapper">
                <div class="dt-left">
                    <h1 class="detail-title" id="propTitle">Loading Property...</h1>
                    <p class="detail-location" id="propLocation"><i class="fa-solid fa-location-dot"></i> ---</p>
                    <div class="detail-badges" id="propBadges">
                        <!-- Dynamic Badges -->
                    </div>
                </div>
                <div class="dt-right">
                    <div class="detail-price-box">
                        <span class="price-label">Asking Price</span>
                        <h2 class="detail-price" id="propPrice">---</h2>
                    </div>
                    <div class="detail-actions" id="detailSaveAction">
                        <!-- Dynamic Save Button -->
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Media & Sidebar Content -->
    <section class="detail-main-content">
        <div class="container">
            <div class="detail-layout">
                
                <!-- Left: Media & Description -->
                <div class="detail-content-area">
                    <!-- Gallery Slider -->
                    <div class="detail-gallery-wrapper">
                        <div class="main-gallery-slider">
                            <div class="slider-container" id="propertySlider">
                                <div class="slider-track" id="galleryTrack">
                                    <!-- Dynamic Slides -->
                                </div>
                            </div>
                            <!-- Navigation Arrows -->
                            <button class="slider-nav prev" id="sliderPrev"><i class="fa-solid fa-chevron-left"></i></button>
                            <button class="slider-nav next" id="sliderNext"><i class="fa-solid fa-chevron-right"></i></button>
                        </div>
                        <div class="gallery-thumbnails" id="sliderThumbs">
                            <!-- Dynamic Thumbnails -->
                        </div>
                    </div>

                    <!-- Quick Specs -->
                    <div class="detail-card specs-grid-card">
                        <div class="specs-grid" id="quickSpecsGrid">
                            <!-- Dynamic Specs -->
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="detail-card desc-card">
                        <h3 class="card-title">Property Description</h3>
                        <div class="desc-content" id="propDescription">
                            <p>Fetching detailed description...</p>
                        </div>
                    </div>

                    <!-- Amenities -->
                    <div class="detail-card amenities-card">
                        <h3 class="card-title">Amenities & Features</h3>
                        <div class="amenities-grid" id="amenitiesGrid">
                            <!-- Dynamic Amenities -->
                        </div>
                    </div>
                </div>

                <!-- Right: Agent Sidebar -->
                <aside class="detail-sidebar">
                    <div class="agent-card-wrapper" id="agentCard">
                        <div class="agent-card-inner">
                            <div class="agent-header">
                                <div class="agent-avatar" id="agentAvatar">
                                    <i class="fa-solid fa-circle-user"></i>
                                </div>
                                <div class="agent-meta">
                                    <h4 id="agentName">---</h4>
                                    <span id="agentRole">Verified Partner</span>
                                </div>
                            </div>
                            <div class="agent-actions" id="agentContactActions">
                                <button class="btn-agent-contact btn-primary-agent" id="btnCall">
                                    <i class="fa-solid fa-phone"></i> Call Agent
                                </button>
                                <button class="btn-agent-contact btn-whatsapp-agent" id="btnWhatsapp">
                                    <i class="fa-brands fa-whatsapp"></i> WhatsApp
                                </button>
                            </div>
                            <div class="agent-sidebar-note">
                                <p><i class="fa-solid fa-circle-info"></i> Mention Landsfy when calling to get the best response from the agent.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar Safety Tip -->
                    <div class="safety-tip-card">
                        <div class="tip-icon"><i class="fa-solid fa-shield-halved"></i></div>
                        <div class="tip-text">
                            <strong>Safety Tip</strong>
                            <p>Never pay in advance for a property you haven't visited. Beware of fake listings.</p>
                        </div>
                    </div>
                </aside>

            </div>
        </div>
    </section>

    <!-- Similar Properties Section -->
    <section class="similar-properties-section">
        <div class="container">
            <div class="section-header">
                <div class="sh-left">
                    <h2 class="section-title">Similar Properties</h2>
                    <p class="section-subtitle">You might also be interested in these properties in the same area.</p>
                </div>
                <div class="similar-slider-controls">
                    <button class="similar-nav prev" id="similarPrev"><i class="fa-solid fa-chevron-left"></i></button>
                    <button class="similar-nav next" id="similarNext"><i class="fa-solid fa-chevron-right"></i></button>
                </div>
            </div>
            <div class="similar-slider-wrapper">
                <div class="similar-slider-container" id="similarPropertiesGrid">
                    <!-- Dynamic Content -->
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>
<script src="<?php echo $base_path; ?>includes/assets/js/website/property_detail.js"></script>

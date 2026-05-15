<?php
include "header.php";
?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-slider" style="background: #111;">
            <div class="hero-slide active" style="background-image: url('https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1920&q=80'); opacity: 1; visibility: visible;"></div>
            <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?auto=format&fit=crop&w=1920&q=80')"></div>
            <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1600566753190-17f0bb2a6c3e?auto=format&fit=crop&w=1920&q=80')"></div>
        </div>
        
        <div class="hero-content">
                      <h1 class="hero-title animate-fade-in">Find Your Favorite Home at Landsfy.com</h1>

            <div class="search-container">
                <div class="search-tabs">
                    <div class="search-tab active">Buy</div>
                    <div class="search-tab">Rent</div>
                </div>
                
                <div class="search-panel">
                    <div class="search-main">
                        <div class="search-input-wrapper">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" class="search-input" placeholder="Enter Location...">
                        </div>
                        <div class="search-input-wrapper">
                            <i class="fa-solid fa-location-dot"></i>
                            <select class="search-input">
                                <option value="">Select City</option>
                                <option value="karachi">Karachi</option>
                                <option value="lahore">Lahore</option>
                                <option value="islamabad">Islamabad</option>
                            </select>
                        </div>
                        <button class="btn btn-search">Search</button>
                    </div>

                    <div class="advanced-toggle" id="advancedToggle">
                        Advanced <i class="fa-solid fa-chevron-down"></i>
                    </div>

                    <div class="advanced-filters" id="advancedFilters">
                        <div class="filter-group">
                            <label>Property Category</label>
                            <select class="search-input" id="filterCategory">
                                <option value="">-- Select --</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Property Type</label>
                            <select class="search-input" id="filterSubtype">
                                <option value="">-- Select --</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Bedrooms</label>
                            <select class="search-input">
                                <option>-- Select --</option>
                                <option>1</option>
                                <option>2</option>
                                <option>3+</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Price from (PKR)</label>
                            <input type="text" class="search-input" placeholder="From">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="browse-section">
        <div class="container">
            <div class="section-header" style="text-align: left; margin-bottom: 50px;">
                <h2 class="section-title" style="font-size: 32px; letter-spacing: -1px;">Browse Properties</h2>
            </div>

            <!-- Mobile Category Tabs (Visible only on mobile) -->
            <div class="mobile-category-tabs">
                <div class="m-cat-tab active" data-cat="homes"><i class="fa-solid fa-house"></i> Homes</div>
                <div class="m-cat-tab" data-cat="plots"><i class="fa-solid fa-location-dot"></i> Plots</div>
                <div class="m-cat-tab" data-cat="commercial"><i class="fa-solid fa-city"></i> Commercial</div>
            </div>

            <div class="browse-grid">
                <!-- Homes Column -->
                <div class="category-card" id="cat-home">
                    <div class="category-header">
                        <div class="category-icon"><i class="fa-solid fa-house"></i></div>
                        <h3 class="category-title">Homes</h3>
                    </div>
                    <div class="inner-tabs" data-category="home">
                        <div class="inner-tab active" data-target="popular-home">Popular</div>
                        <div class="inner-tab" data-target="type-home">Type</div>
                        <div class="inner-tab" data-target="area-home">Area Size</div>
                    </div>
                    
                    <!-- Popular Homes -->
                    <div class="link-grid link-grid-content active" id="popular-home"></div>
                    <div class="link-grid link-grid-content" id="type-home"></div>
                    <div class="link-grid link-grid-content" id="area-home"></div>
                </div>

                <!-- Plots Column -->
                <div class="category-card" id="cat-plots">
                    <div class="category-header">
                        <div class="category-icon"><i class="fa-solid fa-location-dot"></i></div>
                        <h3 class="category-title">Plots</h3>
                    </div>
                    <div class="inner-tabs" data-category="plots">
                        <div class="inner-tab active" data-target="popular-plots">Popular</div>
                        <div class="inner-tab" data-target="type-plots">Type</div>
                        <div class="inner-tab" data-target="area-plots">Area Size</div>
                    </div>
                    
                    <!-- Popular Plots -->
                    <div class="link-grid link-grid-content active" id="popular-plots"></div>
                    <div class="link-grid link-grid-content" id="type-plots"></div>
                    <div class="link-grid link-grid-content" id="area-plots"></div>
                </div>

                <!-- Commercial Column -->
                <div class="category-card" id="cat-commercial">
                    <div class="category-header">
                        <div class="category-icon"><i class="fa-solid fa-city"></i></div>
                        <h3 class="category-title">Commercial</h3>
                    </div>
                    <div class="inner-tabs" data-category="commercial">
                        <div class="inner-tab active" data-target="popular-commercial">Popular</div>
                        <div class="inner-tab" data-target="type-commercial">Type</div>
                        <div class="inner-tab" data-target="area-commercial">Area Size</div>
                    </div>
                    
                    <!-- Popular Commercial -->
                    <div class="link-grid link-grid-content active" id="popular-commercial"></div>
                    <div class="link-grid link-grid-content" id="type-commercial"></div>
                    <div class="link-grid link-grid-content" id="area-commercial"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Properties Section -->
    <section class="featured-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Featured Properties</h2>
                <p class="section-subtitle">We make the best choices with the hottest and most prestigious projects, please visit the details below to find out more.</p>
            </div>

            <div class="property-grid" id="featuredPropertiesGrid">
                <!-- Dynamic Content Injected via home.js -->
                <div class="property-loading">
                    <p>Loading premier listings...</p>
                </div>
            </div>

            <!-- Browse More Button -->
            <div class="browse-more-wrapper">
                <a href="properties" class="btn-browse-more">
                    Browse More Properties <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Recently Viewed Section (Dynamic) -->
    <style>
        .rv-slider-container {
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: nowrap !important;
            gap: 20px !important;
            overflow-x: auto !important;
            scroll-behavior: smooth;
            padding: 15px !important;
            -ms-overflow-style: none;
            scrollbar-width: none;
            width: 100%;
        }
        .rv-slider-container::-webkit-scrollbar {
            display: none;
        }
        .rv-slider-container .property-card {
            flex: 0 0 320px !important;
            width: 320px !important;
            min-width: 320px !important;
            margin: 0 !important;
        }
        .recently-viewed-wrapper {
            position: relative;
            width: 100%;
            overflow: hidden;
        }
        .recent-nav {
            width: 40px !important;
            height: 40px !important;
            border-radius: 12px !important;
            border: 1px solid rgba(0,0,0,0.1) !important;
            background: #ffffff !important;
            color: #333 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05) !important;
            padding: 0 !important;
        }
        .recent-nav:hover {
            background: #9C4BFF !important;
            color: #ffffff !important;
            border-color: #9C4BFF !important;
            transform: translateY(-2px);
        }
        .recent-nav i {
            font-size: 14px !important;
        }
    </style>
    <section class="recently-viewed-section" id="recentlyViewedSection" style="display: none; padding-top: 60px; padding-bottom: 20px;">
        <div class="container">
            <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <div class="sh-left">
                    <h2 class="section-title" style="margin-bottom: 4px;">Recently Viewed</h2>
                    <p class="section-subtitle" style="margin: 0; font-size: 14px;">The properties you've explored recently.</p>
                </div>
                <div class="sh-right" style="display: flex; align-items: center; gap: 15px;">
                    <button class="btn btn-clear-recent" id="btnClearRecent" style="padding: 8px 16px; border-radius: 10px; font-size: 13px; background: rgba(239, 68, 68, 0.05); color: #EF4444; border: 1px solid rgba(239, 68, 68, 0.1); font-weight: 600; cursor: pointer;">
                        <i class="fa-solid fa-trash-can"></i> Clear
                    </button>
                    <div class="slider-nav-btns" style="display: flex; gap: 8px;">
                        <button class="recent-nav prev" id="recentPrev"><i class="fa-solid fa-chevron-left"></i></button>
                        <button class="recent-nav next" id="recentNext"><i class="fa-solid fa-chevron-right"></i></button>
                    </div>
                </div>
            </div>
            <div class="recently-viewed-wrapper">
                <div class="rv-slider-container" id="recentlyViewedGrid">
                    <!-- Dynamic Content -->
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Banner -->
    <section class="cta-banner">
        <div class="cta-bg-shapes">
            <div class="cta-shape cta-shape-1"></div>
            <div class="cta-shape cta-shape-2"></div>
            <div class="cta-shape cta-shape-3"></div>
        </div>
        <div class="container">
            <div class="cta-inner">
                <!-- Feature Badges -->
                <div class="cta-features">
                    <div class="cta-feature-badge">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>Free Property Listing</span>
                    </div>
                    <div class="cta-feature-badge">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>Verified Agencies Only</span>
                    </div>
                    <div class="cta-feature-badge">
                        <i class="fa-solid fa-circle-user"></i>
                        <span>Direct Owner Contact</span>
                    </div>
                    <div class="cta-feature-badge">
                        <i class="fa-solid fa-bell"></i>
                        <span>Instant Property Alerts</span>
                    </div>
                </div>

                <!-- Headline -->
                <div class="cta-content">
                    <h2 class="cta-title">Ready to Find Your<br><span class="cta-highlight">Dream Property?</span></h2>
                    <p class="cta-subtitle">Pakistan's fastest growing real estate platform. List your property for free and reach thousands of verified buyers instantly.</p>
                    <div class="cta-actions">
                        <?php if ($show_add): ?>
                        <a href="<?php echo $add_url; ?>" class="cta-btn cta-btn-primary">
                            <i class="fa-solid fa-circle-plus"></i> Post Your Property
                        </a>
                        <?php else: ?>
                        <a href="properties" class="cta-btn cta-btn-primary">
                            <i class="fa-solid fa-magnifying-glass"></i> Find Your Dream Home
                        </a>
                        <?php endif; ?>
                        <a href="properties" class="cta-btn cta-btn-outline">
                            <i class="fa-solid fa-binoculars"></i> Browse All Properties
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Titanium Agencies Section -->
    <section class="agency-section">
        <div class="container">
            <div class="section-header" style="text-align: left; margin-bottom: 40px;">
                <h2 class="section-title" style="font-size: 28px;">Titanium Agencies</h2>
            </div>

            <div class="agency-container">
                <div class="agency-nav prev"><i class="fa-solid fa-chevron-left"></i></div>
                <div class="agency-nav next"><i class="fa-solid fa-chevron-right"></i></div>

                <div class="agency-grid" id="premiumAgenciesGrid">
                    <!-- Dynamic Content Injected via home.js -->
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Locations Section -->
    <section class="popular-locations-section">
        <div class="container">
            <div class="section-header" style="text-align:left; margin-bottom:30px;">
                <h2 class="section-title" style="font-size:28px;">Popular Locations</h2>
            </div>

            <div class="locations-tabs">
                <div class="location-tab active" data-loc-target="loc-sale">For Sale</div>
                <div class="location-tab" data-loc-target="loc-rent">To Rent</div>
            </div>

            <!-- For Sale -->
            <div class="locations-tab-content active" id="loc-sale">
                <div id="popularLocationsSaleGrid">
                    <div class="property-loading">
                        <p>Fetching popular areas...</p>
                    </div>
                </div>
            </div>

            <!-- To Rent -->
            <div class="locations-tab-content" id="loc-rent">
                <div id="popularLocationsRentGrid">
                    <div class="property-loading">
                        <p>Fetching rental hotspots...</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Blog Section -->
    <section class="home-blog-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Latest News & Insights</h2>
                <p class="section-subtitle">Stay updated with the latest trends and guides from the Pakistan real estate market.</p>
            </div>
            <div class="blog-grid" id="homeBlogGrid">
                <!-- Dynamic Content Injected via home.js -->
            </div>
            <div class="browse-more-wrapper">
                <a href="blog" class="btn-browse-more">
                    Read All Articles <i class="fa-solid fa-newspaper"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- App Download Banner - Compact -->
    <section class="app-banner-section">
        <div class="container">
            <div class="app-banner-inner">

                <!-- Left: Text -->
                <div class="app-banner-text">
                    <h2 class="app-banner-title">Get the Landsfy App</h2>
                    <p class="app-banner-sub">Buy and Rent Property faster and better using our app.</p>
                    <div class="app-store-btns">
                        <a href="#" class="app-store-badge-link">
                            <img src="https://developer.apple.com/assets/elements/badges/download-on-the-app-store.svg"
                                 alt="Download on the App Store"
                                 class="app-store-badge-img" loading="lazy">
                        </a>
                        <a href="#" class="app-store-badge-link">
                            <img src="https://play.google.com/intl/en_us/badges/static/images/badges/en_badge_web_generic.png"
                                 alt="Get it on Google Play"
                                 class="app-store-badge-img google-badge" loading="lazy">
                        </a>
                    </div>
                </div>

                <!-- Center: Phone Mockup -->
                <div class="app-banner-phone">
                    <div class="app-phone-glow"></div>
                    <img src="includes/assets/images/phone-mockup.png"
                         alt="Landsfy Mobile App"
                         class="app-phone-img">
                </div>

                <!-- Right: QR Code -->
                <div class="app-banner-qr-side">
                    <p class="qr-label">Scan the QR code to<br>get the app</p>
                    <div class="qr-box">
                        <div class="qr-grid">
                            <?php for($i=0;$i<16;$i++): ?>
                            <div class="qr-cell <?= (in_array($i,[0,1,4,5,8,10,11,14,15,2,7,9,13])) ? 'filled' : '' ?>"></div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

<?php
include "footer.php";
?>
<script src="includes/assets/js/website/home.js"></script>

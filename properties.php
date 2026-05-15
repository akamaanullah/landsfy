<?php include 'header.php'; ?>

<!-- Properties Search & Filter Header -->
<section class="properties-hero-section">
    <div class="container">
        <div class="search-breadcrumb">
            <a href="index.php">Home</a> <i class="fa-solid fa-chevron-right"></i> <span>Properties for Sale</span>
        </div>
        <h1 class="properties-page-title">Properties for Sale in <span class="text-primary">Pakistan</span></h1>
        <p class="properties-seo-text">Explore the best real estate opportunities in Pakistan. From luxury villas in DHA and Bahria Town to affordable apartments and residential plots in Karachi, Lahore, and Islamabad. Landsfy connects you with verified agents and premium listings to make your property search seamless and secure.</p>
        <p class="properties-count">Showing 1,240 verified results</p>
    </div>
</section>

<!-- Main Listing Section -->
<section class="listing-main-section">
    <div class="container">
        <div class="listing-layout">
            
            <!-- Sidebar Filters (Desktop Only) -->
            <aside class="filter-sidebar" id="filterSidebar">
                <div class="filter-mobile-header">
                    <h3>Filters</h3>
                    <button id="close-filters-mobile"><i class="fa-solid fa-xmarkmark"></i></button>
                </div>
                <div class="filter-card">
                    <h3 class="filter-card-title">Filters</h3>
                    
                    <div class="filter-group">
                        <label>Property Purpose</label>
                        <select class="filter-select" id="filterPurpose">
                            <option value="">Any Purpose</option>
                            <option value="sell">For Sale</option>
                            <option value="rent">For Rent</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Location</label>
                        <div class="filter-input-box">
                            <i class="fa-solid fa-location-dot"></i>
                            <input type="text" id="filterSearch" placeholder="Enter City or Locality">
                        </div>
                    </div>

                    <div class="filter-group">
                        <label>Property Category</label>
                        <select class="filter-select" id="filterCategory">
                            <option value="">All Categories</option>
                            <!-- Dynamic Categories -->
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Property Type</label>
                        <select class="filter-select" id="filterType">
                            <option value="">All Types</option>
                            <!-- Dynamic Types -->
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Price Range (PKR)</label>
                        <div class="range-inputs">
                            <input type="number" id="minPrice" placeholder="Min">
                            <input type="number" id="maxPrice" placeholder="Max">
                        </div>
                    </div>

                    <div class="filter-group">
                        <label>Area Size</label>
                        <div class="range-inputs">
                            <input type="number" id="areaSize" placeholder="Size">
                            <select id="areaUnit">
                                <option value="Marla">Marla</option>
                                <option value="Kanal">Kanal</option>
                                <option value="Sq.Yd">Sq.Yd</option>
                            </select>
                        </div>
                    </div>

                    <div class="filter-group">
                        <label>Beds</label>
                        <div class="pill-filters" id="bedFilters">
                            <div class="pill-item active" data-value="">Any</div>
                            <div class="pill-item" data-value="1">1+</div>
                            <div class="pill-item" data-value="2">2+</div>
                            <div class="pill-item" data-value="3">3+</div>
                            <div class="pill-item" data-value="4">4+</div>
                        </div>
                    </div>

                    <button class="btn-apply-filters" id="applyFilters">Apply Filters</button>
                    <button class="btn-reset-filters" id="resetFilters">Reset All</button>
                </div>
            </aside>

            <!-- Listing Results -->
            <div class="listing-content">
                
                <!-- Sorting & View Options -->
                <div class="listing-controls">
                    <div class="sort-box">
                        <span>Sort by:</span>
                        <select id="sortResults">
                            <option value="newest">Newest First</option>
                            <option value="price_low">Price: Low to High</option>
                            <option value="price_high">Price: High to Low</option>
                        </select>
                    </div>
                    <div class="view-toggles">
                        <button class="view-btn btn-grid-view active" title="Grid View"><i class="fa-solid fa-table-cells"></i></button>
                        <button class="view-btn btn-list-view" title="List View"><i class="fa-solid fa-list"></i></button>
                    </div>
                </div>

                <!-- Active Filters Tags -->
                <div class="active-filters-bar" id="activeFiltersBar" style="display:none;">
                    <div class="active-tags-list" id="activeTagsList">
                        <!-- Dynamic Tags -->
                    </div>
                    <button class="clear-all-tags" onclick="resetAllFilters()">Clear All</button>
                </div>

                <!-- Grid -->
                <div class="properties-grid" id="listing-grid">
                    <!-- Dynamic Content Injected via properties.js -->
                    <div class="property-loading">
                        <p>Loading verified properties...</p>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="pagination" id="pagination-container">
                    <!-- Dynamic Pagination -->
                </div>

            </div>
        </div>
    </div>
</section>

<!-- Rest of the sections remain same -->
<section class="properties-seo-info">
    <div class="container">
        <div class="seo-info-grid">
            <div class="seo-text-col">
                <h2 class="seo-section-title">Investing in Pakistan Real Estate</h2>
                <p>Pakistan's real estate market offers a diverse range of investment opportunities across its major cities. Whether you're looking for luxury living in the coastal city of Karachi, the cultural heart of Lahore, or the modern infrastructure of Islamabad, Landsfy provides a platform to explore verified properties with ease. Our listings cover popular localities including DHA (Defence Housing Authority), Bahria Town, Gulberg, and Clifton, ensuring that you find a home or investment that meets your specific requirements.</p>
                <p>When searching for property for sale, it's essential to consider factors like location, proximity to amenities, and market trends. Landsfy simplifies this process by providing detailed property specifications, high-quality images, and direct contact with verified agents.</p>
            </div>
            <div class="seo-faq-col">
                <h3 class="seo-sub-title">Frequently Asked Questions</h3>
                <div class="seo-faq-item">
                    <div class="faq-q">How do I verify a property listing on Landsfy?</div>
                    <div class="faq-a">Every property listed as "Verified" has undergone a strict documentation check by our team to ensure authenticity and peace of mind for buyers.</div>
                </div>
                <div class="seo-faq-item">
                    <div class="faq-q">What are the best areas for property investment?</div>
                    <div class="faq-a">Currently, DHA Phase 8 (Karachi), Bahria Town (Lahore), and Gulberg (Islamabad) are among the most sought-after areas for high ROI.</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="listing-cta-section">
    <div class="container">
        <div class="listing-cta-inner">
            <div class="cta-content">
                <h2 class="cta-title">Want to Sell or Rent<br><span class="cta-highlight">Your Property?</span></h2>
                <p class="cta-subtitle">List your property on Pakistan's fastest growing portal and get connected with thousands of verified buyers and tenants today.</p>
                <div class="cta-actions">
                    <?php if ($show_add): ?>
                    <a href="<?php echo $add_url; ?>" class="cta-btn cta-btn-primary">
                        <i class="fa-solid fa-circle-plus"></i> List Property Free
                    </a>
                    <?php else: ?>
                    <a href="properties.php" class="cta-btn cta-btn-primary">
                        <i class="fa-solid fa-magnifying-glass"></i> Find Your Dream Home
                    </a>
                    <?php endif; ?>
                    <a href="contact.php" class="cta-btn cta-btn-outline">
                        Contact Support
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="mobile-filter-trigger">
    <button id="open-filters-mobile">
        <i class="fa-solid fa-filter"></i> Filters & Sorting
    </button>
</div>

<?php include 'footer.php'; ?>
<script src="<?php echo $base_path; ?>includes/assets/js/website/properties.js"></script>

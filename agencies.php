<?php include 'header.php'; ?>

<main class="agencies-page">
    <!-- Hero / Search Section -->
    <section class="agencies-hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Find Trusted Real Estate Agencies</h1>
                <p class="hero-subtitle">Connect with verified experts to find your dream property.</p>
                
                <div class="agency-search-box">
                    <div class="search-input">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="agencyNameSearch" placeholder="Search by Agency Name (e.g. Silver Estates)...">
                    </div>
                    <button class="btn-search-agencies" id="btnSearchAgencies">Search Agencies</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Agencies Listing -->
    <section class="agencies-listing">
        <div class="container">
            <div class="agencies-layout">
                
                <!-- Sidebar Filters -->
                <aside class="agencies-sidebar">
                    <div class="filter-card">
                        <h3 class="filter-title">Filter by City</h3>
                        <div class="filter-options" id="cityCheckboxes">
                            <!-- Dynamic City Checkboxes -->
                            <div class="loading-small">Loading cities...</div>
                        </div>
                        <button class="btn-clear-agencies" id="btnClearAgencies">
                            <i class="fa-solid fa-trash-can"></i> Clear All Filters
                        </button>
                    </div>

                    <div class="filter-card">
                        <h3 class="filter-title">Platform Statistics</h3>
                        <div class="stats-sidebar-info">
                            <p>Browse through hundreds of verified agencies across Pakistan.</p>
                        </div>
                    </div>
                </aside>

                <!-- Agencies Grid -->
                <div class="agencies-content">
                    <div class="agencies-header-meta">
                        <span id="agenciesCountText">Showing --- agencies</span>
                    </div>

                    <div class="agencies-grid" id="agenciesGrid">
                        <!-- Dynamic Content -->
                        <div class="loading-state">
                            <div class="spinner"></div>
                            <p>Fetching top agencies...</p>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-container" id="agenciesPagination">
                        <!-- Dynamic Pagination -->
                    </div>
                </div>

            </div>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>
<script src="includes/assets/js/website/agencies.js"></script>

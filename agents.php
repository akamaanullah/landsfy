<?php include 'header.php'; ?>

<main class="agents-directory-page">
    <!-- Agents Hero Search -->
    <section class="agents-hero">
        <div class="container">
            <div class="agents-hero-content">
                <h1>Find Your Expert Real Estate Agent</h1>
                <p>Connect with verified professionals who specialize in your area and property type.</p>
                <div class="agent-search-bar">
                    <div class="search-input-group">
                        <i class="fa-solid fa-user-tie"></i>
                        <input type="text" id="agentSearchInput" placeholder="Search by agent name or specialization...">
                    </div>
                    <div class="search-input-group">
                        <i class="fa-solid fa-location-dot"></i>
                        <select id="agentCityFilter">
                            <option value="">All Locations</option>
                            <!-- Dynamic Cities -->
                        </select>
                    </div>
                    <button class="btn-search-agents" id="btnSearchAgents">Search Agents</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Filters & Sorting Bar -->
    <div class="agents-filters-bar">
        <div class="container">
            <div class="filters-inner">
                <div class="filter-results-text">
                    <span id="agentsCountText">Showing --- agents</span>
                </div>
                <div class="sort-agents">
                    <span>Sort by:</span>
                    <select id="agentSortOrder">
                        <option value="most_listings">Most Listings</option>
                        <option value="top_rated">Top Rated</option>
                        <option value="experience">Experience</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Agents Grid Section -->
    <section class="agents-grid-section">
        <div class="container">
            <div class="agents-directory-grid" id="agentsGrid">
                <!-- Dynamic Agents -->
                <div class="loading-state">
                    <div class="spinner"></div>
                    <p>Loading professionals...</p>
                </div>
            </div>

            <!-- Pagination -->
            <div class="landsfy-pagination" id="agentsPagination">
                <!-- Dynamic Pagination -->
            </div>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>
<script src="includes/assets/js/website/agents.js"></script>

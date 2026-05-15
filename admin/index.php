<?php
include 'header.php';
?>

        <!-- Main Content Area -->
        <main class="main-content">
            
            <!-- Top Header -->
            <header class="header glass">
                <div class="page-title">Platform Overview</div>
                
                <div class="header-actions">
                    <div class="search-bar">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="globalSearchInput" placeholder="Search properties, projects, users...">
                        <div id="search-results-overlay" class="glass">
                            <!-- Results Injected Here -->
                        </div>
                    </div>
                    
                    <button class="icon-btn" id="themeToggle" title="Toggle Light/Dark Mode">
                        <i class="fa-solid fa-moon" id="themeIcon"></i>
                    </button>
                    
                    <button class="icon-btn" onclick="window.location.href='approvals.php'">
                        <i class="fa-solid fa-bell"></i>
                    </button>
                    
                    <a href="add-property.php" class="btn-primary" style="margin-left: 12px;">
                        <i class="fa-solid fa-plus"></i> New Property
                    </a>
                </div>
            </header>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card glass">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fa-solid fa-house-chimney"></i></div>
                        <div class="stat-trend trend-up"><i class="fa-solid fa-arrow-trend-up"></i> Live</div>
                    </div>
                    <div>
                        <div class="stat-value">...</div>
                        <div class="stat-label">Total Properties</div>
                    </div>
                </div>
                
                <div class="stat-card glass">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
                        <div class="stat-trend trend-up"><i class="fa-solid fa-arrow-trend-up"></i> Active</div>
                    </div>
                    <div>
                        <div class="stat-value">...</div>
                        <div class="stat-label">Published Listings</div>
                    </div>
                </div>
                
                <div class="stat-card glass">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                        <div class="stat-trend trend-up">
                            <i class="fa-solid fa-heart-pulse"></i> Optimal
                        </div>
                    </div>
                    <div>
                        <div class="stat-value">...</div>
                        <div class="stat-label">Pending Review</div>
                    </div>
                </div>

                <div class="stat-card glass">
                    <div class="stat-header">
                        <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
                        <div class="stat-trend trend-up"><i class="fa-solid fa-arrow-trend-up"></i> Growth</div>
                    </div>
                    <div>
                        <div class="stat-value">...</div>
                        <div class="stat-label">Total Users</div>
                    </div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="content-grid">
                
                <!-- Recent Properties -->
                <div class="card-panel glass">
                    <div class="section-header">
                        <div class="section-title">Recent Submissions</div>
                        
                        <div class="tabs-container" id="propertyTabs">
                            <div class="tab-indicator" id="tabIndicator"></div>
                            <button class="tab-btn active" data-target="all">All</button>
                            <button class="tab-btn" data-target="sale">For Sale</button>
                            <button class="tab-btn" data-target="rent">For Rent</button>
                        </div>
                    </div>

                    <div class="property-list" id="recentPropertiesList">
                        <!-- Populated by JS -->
                        <div style="padding: 40px; text-align: center; opacity: 0.5;">
                            <i class="fa-solid fa-circle-notch fa-spin" style="font-size: 32px;"></i>
                            <p>Loading listings...</p>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar -->
                <div style="display: flex; flex-direction: column; gap: 24px;">
                    <!-- Chart Panel -->
                    <div class="card-panel glass">
                        <div class="section-title" style="margin-bottom: 24px;">Inventory Split</div>
                        <div class="chart-container">
                        <div class="chart-container">
                            <div class="doughnut" id="dashboardDoughnut"></div>
                            
                            <div class="chart-legend" id="dashboardLegend">
                                <!-- Populated Dynamically by JS -->
                            </div>
                        </div>
                        </div>
                    </div>

                    <!-- Mini User List -->
                    <div class="card-panel glass">
                        <div class="section-header" style="margin-bottom: 16px;">
                            <div class="section-title">New Users</div>
                            <a href="user-management.php" style="color: var(--primary); font-size: 14px; font-weight: 600; text-decoration: none;">View All</a>
                        </div>
                        <div class="property-list" id="newUsersList" style="gap: 12px;">
                            <!-- Populated by JS -->
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- External JS -->
    <script src="../includes/assets/js/utils.js"></script>
    <script src="../includes/assets/js/script.js"></script>
    <script src="../includes/assets/js/admin/dashboard.js"></script>
</body>
</html>

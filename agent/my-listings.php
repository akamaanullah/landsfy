<?php 
include 'header.php';
?>

        <!-- Main Content -->
        <main class="main-content">
            <header class="header glass">
                <div class="header-left">
                    <div class="page-title">My Properties</div>
                    <div class="breadcrumb">Manage your assigned listings</div>
                </div>
                
                <div class="header-actions">
                    <button class="icon-btn" id="themeToggle">
                        <i class="fa-solid fa-moon" id="themeIcon"></i>
                    </button>
                    <a href="notifications.php" class="icon-btn">
                        <i class="fa-solid fa-bell"></i>
                    </a>
                    <a href="add-property.php" class="btn-primary">
                        <i class="fa-solid fa-plus"></i> New Listing
                    </a>
                </div>
            </header>

            <div class="view-container">
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card glass active" data-filter="all">
                        <div class="stat-icon" style="background: rgba(107,0,182,0.1); color: var(--primary);">
                            <i class="fa-solid fa-buildings"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">My Total</span>
                            <h2 class="stat-value">--</h2>
                        </div>
                    </div>
                    <div class="stat-card glass" data-filter="active">
                        <div class="stat-icon" style="background: rgba(16,185,129,0.1); color: var(--success);">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Published</span>
                            <h2 class="stat-value">--</h2>
                        </div>
                    </div>
                    <div class="stat-card glass" data-filter="pending">
                        <div class="stat-icon" style="background: rgba(245,158,11,0.1); color: var(--warning);">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Reviewing</span>
                            <h2 class="stat-value">--</h2>
                        </div>
                    </div>
                    <div class="stat-card glass" data-filter="sold">
                        <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: var(--danger);">
                            <i class="fa-solid fa-tag"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Sold</span>
                            <h2 class="stat-value">--</h2>
                        </div>
                    </div>
                </div>

                <div class="management-bar glass">
                    <div class="search-box">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" placeholder="Search my inventory..." id="propertySearch">
                    </div>
                    <div class="filter-group">
                        <div class="custom-dropdown glass" id="statusFilter">
                            <div class="dropdown-trigger">
                                <span class="selected-text">All Status</span>
                                <i class="fa-solid fa-chevron-down"></i>
                            </div>
                            <div class="dropdown-menu glass">
                                <div class="dropdown-item active" data-value="all">All Status</div>
                                <div class="dropdown-item" data-value="active">Active</div>
                                <div class="dropdown-item" data-value="pending">Pending</div>
                                <div class="dropdown-item" data-value="sold">Sold</div>
                            </div>
                            <input type="hidden" name="status" value="all">
                        </div>
                    </div>
                </div>

                <!-- Property Grid -->
                <div class="property-grid" id="propertyGrid">
                    <!-- Cards will render here asynchronously -->
                    <div style="text-align: center; width: 100%; padding: 40px; grid-column: 1/-1;">
                        <i class="fa-solid fa-circle-notch fa-spin" style="font-size: 32px; color: var(--primary);"></i>
                    </div>
                </div>
            </div>
        </main>
    </div>


    <!-- External JS -->
    <script src="../includes/assets/js/script.js"></script>
    <script src="../includes/assets/js/agent/listings.js"></script>
</body>
</html>

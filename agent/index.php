<?php 
include 'header.php';
?>
        <!-- Main Content -->
        <main class="main-content">
            <header class="header glass">
                <div class="header-left">
                    <div class="page-title">Personal Overview</div>
                    <div class="breadcrumb">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>! 👋</div>
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
                <!-- Agent Stats Grid -->
                <div class="stats-summary-grid">
                    <div class="stat-card glass">
                        <div class="stat-icon" style="background: rgba(107, 0, 182, 0.1); color: var(--primary);">
                            <i class="fa-solid fa-house-chimney"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">My Listings</span>
                            <span class="stat-value" id="statListings">0</span>
                        </div>
                    </div>
                    <div class="stat-card glass">
                        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--success);">
                            <i class="fa-brands fa-whatsapp"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">WhatsApp Clicks</span>
                            <span class="stat-value" id="statWhatsapp">0</span>
                        </div>
                    </div>
                    <div class="stat-card glass">
                        <div class="stat-icon" style="background: rgba(107, 0, 182, 0.1); color: var(--primary);">
                            <i class="fa-solid fa-phone"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Call Inquiries</span>
                            <span class="stat-value" id="statCalls">0</span>
                        </div>
                    </div>
                    <div class="stat-card glass">
                        <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: var(--warning);">
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Agent Rating</span>
                            <span class="stat-value" id="statRating">0</span>
                        </div>
                    </div>
                </div>

                <div class="adb-grid">
                    <!-- Hot Listings (Engagement Tracker) -->
                    <div class="card-panel glass">
                        <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                            <h3 class="panel-title" style="font-size: 18px; font-weight: 700;">Hot Listings (Most Clicks)</h3>
                            <a href="my-listings.php" class="view-all" style="color: var(--primary); font-size: 13px; font-weight: 600; text-decoration: none;">View All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="data-table" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th>Property</th>
                                        <th style="text-align: center;"><i class="fa-solid fa-eye" style="margin-right: 4px;"></i> Views</th>
                                        <th style="text-align: center;"><i class="fa-brands fa-whatsapp" style="margin-right: 4px; color: #25D366;"></i> WhatsApp</th>
                                        <th style="text-align: center;"><i class="fa-solid fa-phone" style="margin-right: 4px; color: var(--primary);"></i> Calls</th>
                                        <th style="text-align: center;">Engagement</th>
                                    </tr>
                                </thead>
                                <tbody id="hotListingsTbody">
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 20px;">
                                            <i class="fa-solid fa-circle-notch fa-spin" style="font-size: 24px; color: var(--primary);"></i> Loading hot listings...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Recent Inquiries -->
                    <div class="card-panel glass">
                        <div class="panel-header" style="margin-bottom: 20px;">
                            <h3 class="panel-title" style="font-size: 18px; font-weight: 700;">Direct Inquiries</h3>
                        </div>
                        <div class="adb-timeline" id="recentTimeline">
                            <div style="text-align: center; padding: 20px;">
                                <i class="fa-solid fa-circle-notch fa-spin" style="font-size: 24px; color: var(--primary);"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- External JS -->
    <script src="../includes/assets/js/utils.js"></script>
    <script src="../includes/assets/js/script.js"></script>
    <script src="../includes/assets/js/agent/dashboard.js"></script>
</body>
</html>

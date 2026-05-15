<?php 
$page_title = "Buyer Dashboard";
include 'header.php'; 
?>
        <!-- Main Content -->
        <main class="main-content">
            <header class="header glass">
                <div class="header-left">
                    <div class="page-title">Personal Overview</div>
                    <div class="breadcrumb">Welcome back, <?php echo htmlspecialchars($username); ?>! 👋</div>
                </div>
                
                <div class="header-actions">
                    <button class="icon-btn" id="themeToggle">
                        <i class="fa-solid <?php echo $user_theme == 'light' ? 'fa-moon' : 'fa-sun'; ?>" id="themeIcon"></i>
                    </button>
                    <div class="notification-wrapper" style="position: relative;">
                        <button class="icon-btn" id="notifBell">
                            <i class="fa-solid fa-bell"></i>
                            <span class="pulse-dot" style="position: absolute; top: 8px; right: 8px; width: 8px; height: 8px; background: var(--danger); border-radius: 50%; border: 2px solid var(--glass-bg); display: none;"></span>
                        </button>
                        <div class="dropdown-menu glass" id="notifDropdown" style="display: none; position: absolute; top: 100%; right: 0; margin-top: 15px; width: 320px; padding: 20px; z-index: 1000; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                <h4 style="font-size: 16px; font-weight: 800; margin: 0;">Notifications</h4>
                                <span style="font-size: 11px; font-weight: 700; color: var(--primary); cursor: pointer;">Mark all as read</span>
                            </div>
                            <div id="notifContainer">
                                <div style="text-align: center; padding: 32px 0;">
                                    <i class="fa-solid fa-bell-slash" style="font-size: 24px; opacity: 0.2; margin-bottom: 12px; display: block;"></i>
                                    <p style="font-size: 13px; color: var(--text-secondary); margin: 0;">No notifications yet.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <a href="saved-properties.php" class="btn-primary">
                        <i class="fa-solid fa-magnifying-glass-bold"></i> Search Now
                    </a>
                </div>
            </header>

            <div class="view-container">
                <!-- Buyer Stats Grid -->
                <div class="stats-summary-grid">
                    <div class="stat-card glass stat-purple">
                        <div class="stat-icon">
                            <i class="fa-regular fa-heart"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Total Saved</span>
                            <span class="stat-value" id="statSaved">...</span>
                        </div>
                    </div>
                    <div class="stat-card glass stat-green">
                        <div class="stat-icon">
                            <i class="fa-solid fa-chat-circle-text"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">My Inquiries</span>
                            <span class="stat-value" id="statInquiries">...</span>
                        </div>
                    </div>
                    <div class="stat-card glass stat-orange">
                        <div class="stat-icon">
                            <i class="fa-solid fa-eye"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Viewed Today</span>
                            <span class="stat-value" id="statViews">...</span>
                        </div>
                    </div>
                </div>

                <div class="adb-grid">
                    <!-- Recently Saved -->
                    <div class="card-panel glass">
                        <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                            <h3 class="panel-title" style="font-size: 18px; font-weight: 700;">Recently Saved Properties</h3>
                            <a href="saved-properties.php" class="view-all" style="color: var(--primary); font-size: 13px; font-weight: 600; text-decoration: none;">View All</a>
                        </div>
                        
                        <div class="property-list" style="gap: 16px;">
                            <div style="text-align: center; padding: 20px;">
                                <i class="fa-solid fa-circle-notch fa-spin" style="font-size: 24px; color: var(--primary);"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Inquiry Timeline -->
                    <div class="card-panel glass">
                        <div class="panel-header" style="margin-bottom: 20px;">
                            <h3 class="panel-title" style="font-size: 18px; font-weight: 700;">Recent Engagement</h3>
                        </div>
                        <div class="adb-timeline" id="recentTimeline">
                            <!-- Populated by JS snippet or same inquiries API -->
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
    <script src="../includes/assets/js/script.js"></script>
    <script src="../includes/assets/js/buyer/dashboard.js"></script>
    <script src="../includes/assets/js/buyer/notif-checker.js"></script>
</body>
</html>

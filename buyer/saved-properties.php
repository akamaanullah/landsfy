<?php 
$page_title = "Saved Properties";
include 'header.php'; 
?>
        <!-- Main Content -->
        <main class="main-content">
            <header class="header glass">
                <div class="header-left">
                    <div class="page-title">Saved Properties</div>
                    <div class="breadcrumb">Your favorite listings and watchlisted assets</div>
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
                    <a href="index.php" class="btn-primary">
                        <i class="fa-solid fa-magnifying-glass"></i> Find More
                    </a>
                </div>
            </header>

            <div class="view-container">
                <!-- Management/Filter Bar -->
                <div class="management-bar glass">
                    <div class="filter-group">
                        <div class="search-box" style="flex: initial; width: 300px;">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" placeholder="Search saved..." id="savedSearchInput">
                        </div>
                    </div>

                    <div class="view-toggle">
                        <button class="icon-btn active"><i class="fa-solid fa-grid-four"></i></button>
                    </div>
                </div>

                <!-- Property Grid -->
                <div class="property-grid" style="margin-top: 24px; display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px;">
                    <div style="grid-column: 1/-1; text-align: center; padding: 100px 0;">
                        <i class="fa-solid fa-circle-notch fa-spin" style="font-size: 48px; color: var(--primary);"></i>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- External JS -->
    <script src="../includes/assets/js/script.js"></script>
    <script src="../includes/assets/js/buyer/saved_properties.js"></script>
    <script src="../includes/assets/js/buyer/notif-checker.js"></script>
</body>
</html>

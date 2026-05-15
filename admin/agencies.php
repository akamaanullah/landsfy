<?php
include 'header.php';
?>

        <main class="main-content">
            <header class="header glass">
                <div class="page-title">Agency Management</div>
                <div class="header-actions">
                    <div class="search-bar">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" placeholder="Search agencies by name or ID...">
                    </div>
                    <button class="btn-primary" id="addAgencyBtn" style="margin-right: 12px;"><i class="fa-solid fa-circle-plus"></i> Add Agency</button>
                    <button class="icon-btn" id="themeToggle"><i class="fa-solid fa-moon" id="themeIcon"></i></button>
                </div>
            </header>

            <div class="view-container">
                <!-- Summary Stats -->
                <div class="stats-grid" style="margin-bottom: 24px;">
                    <div class="stat-card glass">
                        <div class="stat-icon" style="background: rgba(107,0,182,0.1); color: var(--primary);">
                            <i class="fa-solid fa-buildings"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Total Agencies</span>
                            <h2 class="stat-value" id="statTotal">...</h2>
                        </div>
                    </div>
                    <div class="stat-card glass">
                        <div class="stat-icon" style="background: rgba(16,185,129,0.1); color: var(--success);">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Verified</span>
                            <h2 class="stat-value" id="statVerified">...</h2>
                        </div>
                    </div>
                    <div class="stat-card glass">
                        <div class="stat-icon" style="background: rgba(245,158,11,0.1); color: var(--warning);">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Pending Approval</span>
                            <h2 class="stat-value" id="statPending">...</h2>
                        </div>
                    </div>
                </div>

                <!-- Agency Grid Outer -->
                <div id="agenciesContainer" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
                    <!-- Populated by JS -->
                </div>
            </div>
        </main>
    </div>

    <script src="../includes/assets/js/utils.js"></script>
    <script src="../includes/assets/js/script.js"></script>
    <script src="../includes/assets/js/admin/agencies.js"></script>
</body>
</html>

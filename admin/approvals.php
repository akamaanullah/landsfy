<?php
include 'header.php';
?>

        <main class="main-content">
            <header class="header glass">
                <div class="page-title">Approval Queue</div>
                <div class="header-actions">
                    <button class="icon-btn" id="themeToggle"><i class="fa-solid fa-moon" id="themeIcon"></i></button>
                    <button class="icon-btn"><i class="fa-solid fa-bell"></i></button>
                </div>
            </header>

            <div class="view-container">
                <!-- Summary Stats -->
                <div class="stats-grid" style="margin-bottom: 24px;">
                    <div class="stat-card glass" style="border-left: 3px solid var(--warning);">
                        <div class="stat-icon" style="background: rgba(245,158,11,0.1); color: var(--warning);">
                            <i class="fa-solid fa-clock-countdown"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Pending Review</span>
                            <h2 class="stat-value" id="statPending">...</h2>
                        </div>
                    </div>
                    <div class="stat-card glass" style="border-left: 3px solid var(--success);">
                        <div class="stat-icon" style="background: rgba(16,185,129,0.1); color: var(--success);">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Approved Today</span>
                            <h2 class="stat-value" id="statApproved">...</h2>
                        </div>
                    </div>
                    <div class="stat-card glass" style="border-left: 3px solid var(--danger);">
                        <div class="stat-icon" style="background: rgba(239,68,68,0.1); color: var(--danger);">
                            <i class="fa-solid fa-xmark-circle"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Rejected Today</span>
                            <h2 class="stat-value" id="statRejected">...</h2>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="approval-tabs">
                    <button class="approval-tab-btn active" data-tab="listings">
                        <i class="fa-solid fa-house"></i> Listings <span class="tab-count" id="listingsCount">...</span>
                    </button>
                    <button class="approval-tab-btn" data-tab="agencies">
                        <i class="fa-solid fa-building"></i> Agencies <span class="tab-count" id="agenciesCount">...</span>
                    </button>
                    <button class="approval-tab-btn" data-tab="history">
                        <i class="fa-solid fa-clock-rotate-left"></i> History
                    </button>
                </div>

                <!-- Pending Approvals Queue Shelf -->
                <div class="approval-queue" id="pendingQueue">
                    <!-- Populated by JS -->
                </div>

            </div>
        </main>
    </div>

    <script src="../includes/assets/js/script.js"></script>
    <script src="../includes/assets/js/admin/approvals.js"></script>
</body>
</html>

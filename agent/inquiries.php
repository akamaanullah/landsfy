<?php 
include 'header.php';
?>
        <!-- Main Content -->
        <main class="main-content">
            <header class="header glass">
                <div class="header-left">
                    <div class="page-title">Inquiry Tracker</div>
                    <div class="breadcrumb">Track WhatsApp and Call engagement</div>
                </div>
                
                <div class="header-actions">
                    <button class="icon-btn" id="themeToggle">
                        <i class="fa-solid fa-moon" id="themeIcon"></i>
                    </button>
                    <a href="notifications.php" class="icon-btn">
                        <i class="fa-solid fa-bell"></i>
                    </a>
                    <button class="btn-ghost" style="border: 1px solid var(--glass-border);">
                        <i class="fa-solid fa-export"></i> Export CSV
                    </button>
                </div>
            </header>

            <div class="view-container">
                <div class="management-bar glass" style="flex-wrap: wrap; gap: 16px;">
                    <div class="search-box" style="flex: 1; min-width: 300px;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" placeholder="Search by property or user..." id="inquirySearch">
                    </div>
                    
                    <div class="filter-group" style="display: flex; gap: 12px;">
                        <div class="custom-dropdown glass" style="min-width: 160px;" id="inquiryActionFilter">
                            <div class="dropdown-trigger">
                                <span class="selected-text">Action: All</span>
                                <i class="fa-solid fa-chevron-down"></i>
                            </div>
                            <div class="dropdown-menu glass">
                                <div class="dropdown-item active" data-value="all">All Actions</div>
                                <div class="dropdown-item" data-value="whatsapp">WhatsApp</div>
                                <div class="dropdown-item" data-value="call">Call Button</div>
                            </div>
                            <input type="hidden" name="action" value="all">
                        </div>
                    </div>
                </div>

                <!-- Inquiry List -->
                <div class="card-panel glass">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>User Identity</th>
                                    <th>Property Source</th>
                                    <th>Action Type</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 40px;">
                                        <i class="fa-solid fa-circle-notch fa-spin" style="font-size: 32px; color: var(--primary);"></i>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>


    <!-- External JS -->
    <script src="../includes/assets/js/script.js"></script>
    <script src="../includes/assets/js/agent/inquiries.js"></script>
</body>
</html>

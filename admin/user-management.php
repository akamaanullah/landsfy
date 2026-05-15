<?php
include 'header.php';
?>

        <main class="main-content">
            <header class="header glass">
                <div class="page-title">User Management</div>
                <div class="header-actions">
                    <div class="search-bar">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" placeholder="Search by name, email or role...">
                    </div>
                    <button class="icon-btn" id="themeToggle"><i class="fa-solid fa-moon" id="themeIcon"></i></button>
                    <button class="btn-primary" id="addUserBtn"><i class="fa-solid fa-user-plus"></i> Add User</button>
                </div>
            </header>

            <div class="view-container">
                <!-- User Stats -->
                <div class="stats-grid" style="margin-bottom: 24px;">
                    <div class="stat-card glass">
                        <div class="stat-icon" style="background: rgba(108, 93, 211, 0.1); color: var(--primary);">
                            <i class="fa-solid fa-identification-card"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Total Agents</span>
                            <h2 class="stat-value">...</h2>
                        </div>
                    </div>
                    <div class="stat-card glass">
                        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--success);">
                            <i class="fa-solid fa-shopping-bag-open"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Buyers</span>
                            <h2 class="stat-value">...</h2>
                        </div>
                    </div>
                    <div class="stat-card glass">
                        <div class="stat-icon" style="background: rgba(59, 130, 246, 0.1); color: var(--info);">
                            <i class="fa-solid fa-tag"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Sellers</span>
                            <h2 class="stat-value">...</h2>
                        </div>
                    </div>
                </div>

                <!-- Users Table Shelf -->
                <div class="card-panel glass" style="padding: 0; overflow: hidden;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead style="background: rgba(0,0,0,0.02); border-bottom: 1px solid var(--border-color);">
                            <tr style="text-align: left; font-size: 13px; color: var(--text-secondary);">
                                <th style="padding: 16px 24px;">User / Info</th>
                                <th style="padding: 16px 24px;">Role</th>
                                <th style="padding: 16px 24px;">Status</th>
                                <th style="padding: 16px 24px;">Joined</th>
                                <th style="padding: 16px 24px; text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            <!-- Populated by JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="../includes/assets/js/script.js"></script>
    <script src="../includes/assets/js/admin/users.js"></script>
</body>
</html>

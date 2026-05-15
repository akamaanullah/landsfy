<?php
include 'header.php';

// Fetch Agencies for Filter (Static list for initial load is fine, but we'll use API for data)
$agencies = $pdo->query("SELECT id, name FROM agencies ORDER BY name ASC")->fetchAll();
$categories = $pdo->query("SELECT id, name FROM property_categories ORDER BY sort_order ASC")->fetchAll();
?>

        <main class="main-content">
            <header class="header glass">
                <div class="page-title">Global Inventory</div>
                <div class="header-actions">
                    <button class="icon-btn" id="themeToggle"><i class="fa-solid fa-moon" id="themeIcon"></i></button>
                    <a href="add-property.php" class="btn-primary">
                        <i class="fa-solid fa-plus"></i> New Property
                    </a>
                </div>
            </header>

            <div class="view-container">
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card glass active">
                        <div class="stat-icon" style="background: rgba(107,0,182,0.1); color: var(--primary);">
                            <i class="fa-solid fa-buildings"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Total Inventory</span>
                            <h2 class="stat-value" id="statTotal">...</h2>
                        </div>
                    </div>
                    <div class="stat-card glass">
                        <div class="stat-icon" style="background: rgba(16,185,129,0.1); color: var(--success);">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Published</span>
                            <h2 class="stat-value" id="statPublished">...</h2>
                        </div>
                    </div>
                    <div class="stat-card glass">
                        <div class="stat-icon" style="background: rgba(245,158,11,0.1); color: var(--warning);">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Under Review</span>
                            <h2 class="stat-value" id="statReview">...</h2>
                        </div>
                    </div>
                    <div class="stat-card glass">
                        <div class="stat-icon" style="background: rgba(107,114,128,0.1); color: #6b7280;">
                            <i class="fa-solid fa-tag"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Sold / Archived</span>
                            <h2 class="stat-value" id="statSold">...</h2>
                        </div>
                    </div>
                </div>

                <!-- Filter Bar -->
                <div class="management-bar glass all-props-bar" style="padding: 24px;">
                    <form id="filterForm" style="width: 100%;">
                        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 12px; align-items: end;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Search</label>
                                <div class="input-wrapper">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                    <input type="text" name="search" class="glass-input" placeholder="Keyword...">
                                </div>
                            </div>
                            
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Agency</label>
                                <select name="agency" class="glass-input">
                                    <option value="all">All Agencies</option>
                                    <?php foreach($agencies as $ag): ?>
                                        <option value="<?php echo $ag->id; ?>"><?php echo htmlspecialchars($ag->name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Category</label>
                                <select name="category" class="glass-input">
                                    <option value="all">All Categories</option>
                                    <?php foreach($categories as $cat): ?>
                                        <option value="<?php echo $cat->id; ?>"><?php echo htmlspecialchars($cat->name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Purpose</label>
                                <select name="purpose" class="glass-input">
                                    <option value="all">Any</option>
                                    <option value="sell">For Sale</option>
                                    <option value="rent">For Rent</option>
                                </select>
                            </div>

                            <button type="submit" class="btn-primary" style="height: 48px;"><i class="fa-solid fa-funnel"></i></button>
                        </div>
                    </form>
                </div>

                <!-- Property Grid Outer -->
                <div class="property-grid" id="propertyGrid">
                    <!-- Populated by JS -->
                </div>

                <!-- Load More Button -->
                <div id="loadMoreContainer" style="display: flex; justify-content: center; margin-top: 40px; margin-bottom: 60px;">
                    <button id="loadMoreBtn" class="btn-preview" style="display: none; padding: 12px 32px; border: 1px solid var(--border-color); font-weight: 600;">
                        Load More Properties
                    </button>
                </div>
            </div>
        </main>
    </div>

    <script src="../includes/assets/js/script.js"></script>
    <script src="../includes/assets/js/admin/all-properties.js"></script>
</body>
</html>

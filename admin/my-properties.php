<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Properties | Landsfy Admin</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <!-- External CSS -->
    <link rel="stylesheet" href="../includes/assets/css/style.css">
</head>
<body>
    <!-- Background Blurs -->
    <div class="bg-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
    </div>

    <div class="app-layout">
        <!-- Sidebar -->
        <aside class="sidebar glass">
            <a href="index.html" class="brand">
                <div class="brand-icon" style="background: transparent; box-shadow: none;">
                    <img src="../includes/assets/images/favicon.png" alt="Landsfy" style="width: 40px; height: 40px; object-fit: contain;">
                </div>
                <div class="brand-text">LANDSFY</div>
            </a>

            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="index.html"><i class="fa-solid fa-table-cells-large"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="add-property.html"><i class="fa-solid fa-circle-plus"></i> Add Property</a>
                </li>
                <li class="nav-item active">
                    <a href="my-properties.html"><i class="fa-solid fa-house-chimney"></i> My Properties</a>
                </li>
                <li class="nav-item">
                    <a href="all-properties.html"><i class="fa-solid fa-list-ul"></i> All Property</a>
                </li>
                <li class="nav-item">
                    <a href="approvals.html"><i class="fa-solid fa-circle-check"></i> Approvals <span class="nav-badge">3</span></a>
                </li>
                <li class="nav-item">
                    <a href="user-management.html"><i class="fa-solid fa-users"></i> User Management</a>
                </li>
                <li class="nav-item">
                    <a href="agencies.html"><i class="fa-solid fa-building"></i> Agencies</a>
                </li>
                <li class="nav-item">
                    <a href="#"><i class="fa-solid fa-folders"></i> Categories</a>
                </li>
            </ul>

            <div class="user-card">
                <img src="https://i.pravatar.cc/150?img=11" alt="admin" class="user-avatar">
                <div class="user-info">
                    <div class="user-name">Ishaq Ali</div>
                    <div class="user-role">Main Admin</div>
                </div>
                <i class="fa-solid fa-chevron-right" style="color: var(--text-secondary)"></i>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            
            <!-- Top Header -->
            <header class="header glass">
                <div class="page-title">My Properties</div>
                
                <div class="header-actions">
                    <button class="icon-btn" id="themeToggle" title="Toggle Light/Dark Mode">
                        <i class="fa-solid fa-moon" id="themeIcon"></i>
                    </button>
                    <button class="icon-btn">
                        <i class="fa-solid fa-bell"></i>
                    </button>
                    <a href="add-property.html" class="btn-primary" style="padding: 10px 20px; font-size: 14px;">
                        <i class="fa-solid fa-plus"></i> Add New
                    </a>
                </div>
            </header>

            <div class="view-container">
                
                <!-- Stats Dashboard -->
                <div class="stats-summary-grid">
                    <div class="stat-card glass active" data-filter="all">
                        <div class="stat-icon" style="background: rgba(107, 0, 182, 0.1); color: var(--primary);">
                            <i class="fa-solid fa-rows"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">All Listings</span>
                            <h2 class="stat-value">24</h2>
                        </div>
                    </div>
                    <div class="stat-card glass" data-filter="active">
                        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--success);">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Active</span>
                            <h2 class="stat-value">18</h2>
                        </div>
                    </div>
                    <div class="stat-card glass" data-filter="pending">
                        <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: var(--warning);">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Pending</span>
                            <h2 class="stat-value">3</h2>
                        </div>
                    </div>
                    <div class="stat-card glass" data-filter="sold">
                        <div class="stat-icon" style="background: rgba(139, 92, 246, 0.1); color: #8B5CF6;">
                            <i class="fa-solid fa-handshake"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Sold/Rented</span>
                            <h2 class="stat-value">2</h2>
                        </div>
                    </div>
                    <div class="stat-card glass" data-filter="expired">
                        <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: var(--danger);">
                            <i class="fa-solid fa-prohibit"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Expired</span>
                            <h2 class="stat-value">1</h2>
                        </div>
                    </div>
                </div>

                <!-- Search & Filters -->
                <div class="management-bar glass">
                    <div class="search-box">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" placeholder="Search by Title, ID or Location..." id="propertySearch">
                    </div>
                    
                    <div class="filter-group">
                        <!-- Custom Dropdown: Type -->
                        <div class="custom-dropdown" id="typeDropdown">
                            <div class="dropdown-trigger">
                                <i class="fa-solid fa-house"></i>
                                <span class="selected-text">All Types</span>
                                <i class="fa-solid fa-chevron-down caret"></i>
                            </div>
                            <div class="dropdown-menu glass">
                                <div class="dropdown-item active" data-value="all">All Types</div>
                                <div class="dropdown-item" data-value="home">Home</div>
                                <div class="dropdown-item" data-value="plot">Plots</div>
                                <div class="dropdown-item" data-value="commercial">Commercial</div>
                            </div>
                            <input type="hidden" id="typeFilter" value="all">
                        </div>

                        <!-- Custom Dropdown: Purpose -->
                        <div class="custom-dropdown" id="purposeDropdown">
                            <div class="dropdown-trigger">
                                <i class="fa-solid fa-tag"></i>
                                <span class="selected-text">Any Purpose</span>
                                <i class="fa-solid fa-chevron-down caret"></i>
                            </div>
                            <div class="dropdown-menu glass">
                                <div class="dropdown-item active" data-value="all">Any Purpose</div>
                                <div class="dropdown-item" data-value="sale">For Sale</div>
                                <div class="dropdown-item" data-value="rent">For Rent</div>
                            </div>
                            <input type="hidden" id="purposeFilter" value="all">
                        </div>

                        <!-- Custom Dropdown: Sort -->
                        <div class="custom-dropdown" id="sortDropdown">
                            <div class="dropdown-trigger">
                                <i class="fa-solid fa-sort-ascending"></i>
                                <span class="selected-text">Newest First</span>
                                <i class="fa-solid fa-chevron-down caret"></i>
                            </div>
                            <div class="dropdown-menu glass">
                                <div class="dropdown-item active" data-value="newest">Newest First</div>
                                <div class="dropdown-item" data-value="price-high">Price: High to Low</div>
                                <div class="dropdown-item" data-value="price-low">Price: Low to High</div>
                                <div class="dropdown-item" data-value="views">Most Viewed</div>
                            </div>
                            <input type="hidden" id="sortFilter" value="newest">
                        </div>
                    </div>
                </div>

                <!-- Property Grid -->
                <div class="property-grid" id="propertyGrid">
                    
                    <!-- Card 1: Active -->
                    <div class="property-card glass" data-status="active" data-type="home">
                        <div class="card-image-wrapper" onclick="location.href='property-detail.html'">
                            <div class="card-badge badge-active">Active</div>
                            <img src="https://images.unsplash.com/photo-1600585154340-be6199f7d009?auto=format&fit=crop&w=500&q=80" alt="Property" class="card-image"
                                 onerror="this.onerror=null;this.outerHTML='<div class=\'card-img-placeholder\'><i class=\'fa-solid fa-house\'></i></div>'">

                        </div>
                        <div class="card-content" onclick="location.href='property-detail.html'">
                            <div class="card-price">PKR 4.5 Crore</div>
                            <h4 class="card-title">Modern 500 Sq. Yd Luxury Villa for Sale in DHA Phase 6</h4>
                            <div class="card-location">
                                <i class="fa-solid fa-location-dot"></i> DHA Phase 6, Karachi
                            </div>
                            <div class="card-features">
                                <div class="feature-item"><i class="fa-solid fa-bed"></i> 5</div>
                                <div class="feature-item"><i class="fa-solid fa-bath"></i> 6</div>
                                <div class="feature-item"><i class="fa-solid fa-vector-square"></i> 500 Sq.Yd</div>
                            </div>
                        </div>
                        <div class="card-actions">
                            <div class="card-meta">
                                <i class="fa-solid fa-eye"></i> 1,240 Views
                            </div>
                            <div class="action-btns">
                                <button class="card-action-btn" title="Edit"><i class="fa-solid fa-pencil"></i></button>
                                <button class="card-action-btn action-delete" title="Delete"><i class="fa-solid fa-trash-can"></i></button>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Pending -->
                    <div class="property-card glass" data-status="pending" data-type="plot">
                        <div class="card-image-wrapper" onclick="location.href='property-detail.html'">
                            <div class="card-badge badge-pending">Under Review</div>
                            <img src="https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=500&q=80" alt="Property" class="card-image"
                                 onerror="this.onerror=null;this.outerHTML='<div class=\\'card-img-placeholder\\'><i class=\\'fa-solid fa-house\\'></i></div>'">

                        </div>
                        <div class="card-content" onclick="location.href='property-detail.html'">
                            <div class="card-price">PKR 1.2 Crore</div>
                            <h4 class="card-title">Residential Plot for Sale - Prime Location Block C</h4>
                            <div class="card-location">
                                <i class="fa-solid fa-location-dot"></i> Bahria Town, Lahore
                            </div>
                            <div class="card-features">
                                <div class="feature-item"><i class="fa-solid fa-vector-square"></i> 1 Kanal</div>
                                <div class="feature-item"><i class="fa-solid fa-road-horizon"></i> 40ft Road</div>
                            </div>
                        </div>
                        <div class="card-actions">
                            <div class="card-meta">
                                <i class="fa-solid fa-eye"></i> 450 Views
                            </div>
                            <div class="action-btns">
                                <button class="card-action-btn" title="Edit"><i class="fa-solid fa-pencil"></i></button>
                                <button class="card-action-btn action-delete" title="Delete"><i class="fa-solid fa-trash-can"></i></button>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Sold -->
                    <div class="property-card glass" data-status="sold" data-type="home">
                        <div class="card-image-wrapper" onclick="location.href='property-detail.html'">
                            <div class="card-badge badge-sold">Sold</div>
                            <img src="https://images.unsplash.com/photo-1564013799919-ab600027ffc6?auto=format&fit=crop&w=500&q=80" alt="Property" class="card-image"
                                 onerror="this.onerror=null;this.outerHTML='<div class=\\'card-img-placeholder\\'><i class=\\'fa-solid fa-house\\'></i></div>'">

                        </div>
                        <div class="card-content" onclick="location.href='property-detail.html'">
                            <div class="card-price">PKR 2.8 Crore</div>
                            <h4 class="card-title">Elegant 10 Marla House in Sector F-11</h4>
                            <div class="card-location">
                                <i class="fa-solid fa-location-dot"></i> Sector F-11, Islamabad
                            </div>
                            <div class="card-features">
                                <div class="feature-item"><i class="fa-solid fa-bed"></i> 4</div>
                                <div class="feature-item"><i class="fa-solid fa-bath"></i> 4</div>
                                <div class="feature-item"><i class="fa-solid fa-vector-square"></i> 10 Marla</div>
                            </div>
                        </div>
                        <div class="card-actions">
                            <div class="card-meta">
                                <i class="fa-solid fa-eye"></i> 3,890 Views
                            </div>
                            <div class="action-btns">
                                <button class="card-action-btn" title="Edit"><i class="fa-solid fa-pencil"></i></button>
                                <button class="card-action-btn action-delete" title="Delete"><i class="fa-solid fa-trash-can"></i></button>
                            </div>
                        </div>
                    </div>

                    <!-- Card 4: Active Commercial -->
                    <div class="property-card glass" data-status="active" data-type="commercial">
                        <div class="card-image-wrapper" onclick="location.href='property-detail.html'">
                            <div class="card-badge badge-active">Active</div>
                            <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=500&q=80" alt="Property" class="card-image"
                                 onerror="this.onerror=null;this.outerHTML='<div class=\\'card-img-placeholder\\'><i class=\\'fa-solid fa-house\\'></i></div>'">

                        </div>
                        <div class="card-content" onclick="location.href='property-detail.html'">
                            <div class="card-price">PKR 15,000 / mo</div>
                            <h4 class="card-title">Shared Office Space in Blue Area - Business Hub</h4>
                            <div class="card-location">
                                <i class="fa-solid fa-location-dot"></i> Blue Area, Islamabad
                            </div>
                            <div class="card-features">
                                <div class="feature-item"><i class="fa-solid fa-building"></i> Office</div>
                                <div class="feature-item"><i class="fa-solid fa-vector-square"></i> 200 Sq.Ft</div>
                            </div>
                        </div>
                        <div class="card-actions">
                            <div class="card-meta">
                                <i class="fa-solid fa-eye"></i> 890 Views
                            </div>
                            <div class="action-btns">
                                <button class="card-action-btn" title="Edit"><i class="fa-solid fa-pencil"></i></button>
                                <button class="card-action-btn action-delete" title="Delete"><i class="fa-solid fa-trash-can"></i></button>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </main>
    </div>

    <!-- External JS -->
    <script src="../includes/assets/js/script.js"></script>
</body>
</html>

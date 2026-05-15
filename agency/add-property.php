<?php
require_once '../includes/auth_check.php';
require_once '../includes/database/db.php';

if ($_SESSION['role_name'] !== 'agency_owner' && $_SESSION['role_name'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch basic agency info
$stmt = $pdo->prepare("SELECT a.* FROM agencies a WHERE a.owner_id = ?");
$stmt->execute([$user_id]);
$agency = $stmt->fetch();

$agency_name = $agency ? $agency->name : 'My Agency';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Property | Landsfy Agency</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <!-- External CSS -->
    <link rel="stylesheet" href="../includes/assets/css/style.css">
    <link rel="stylesheet" href="../includes/assets/css/agency-style.css">
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
            <a href="index.php" class="brand">
                <div class="brand-icon" style="background: transparent; box-shadow: none;">
                    <img src="../includes/assets/images/favicon.png" alt="Landsfy" style="width: 40px; height: 40px; object-fit: contain;">
                </div>
                <div class="brand-text">LANDSFY</div>
            </a>

            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="index.php"><i class="fa-solid fa-table-cells-large"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="agency-listings.php"><i class="fa-solid fa-house-chimney"></i> Agency Inventory</a>
                </li>
                <li class="nav-item">
                    <a href="my-agents.php"><i class="fa-solid fa-users"></i> Our Team</a>
                </li>
                <li class="nav-item">
                    <a href="settings.php"><i class="fa-solid fa-gear"></i> Agency Profile</a>
                </li>
                <li class="nav-item">
                    <a href="notifications.php">
                        <i class="fa-solid fa-bell"></i> Notifications
                        <span class="nav-badge" style="display: none;"></span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-bottom">
                <div class="agency-badge-card">
                    <div class="badge-icon"><i class="fa-solid fa-circle-check"></i></div>
                    <div class="badge-title">Verified Agency</div>
                    <div class="badge-desc">Premium Member</div>
                </div>

                <div class="user-card">
                    <img src="<?php echo !empty($_SESSION['avatar_url']) ? '../' . $_SESSION['avatar_url'] : 'https://i.pravatar.cc/150?img=32'; ?>" alt="Agency Admin" class="user-avatar">
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($agency_name); ?></div>
                        <div class="user-role">Agency Admin</div>
                    </div>
                </div>
                
                <a href="../logout.php" class="nav-item-logout" style="margin-top: 16px; display: flex; align-items: center; gap: 12px; padding: 12px; color: #ef4444; text-decoration: none; font-size: 14px; font-weight: 600; transition: all 0.3s ease;">
                    <i class="fa-solid fa-right-from-bracket" style="font-size: 20px;"></i> Logout
                </a>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            
            <!-- Top Header -->
            <header class="header glass">
                <div class="header-left">
                    <div class="page-title">List New Property</div>
                    <div class="breadcrumb"><?php echo htmlspecialchars($agency_name); ?> / Inventory / New</div>
                </div>
                
                <div class="header-actions">
                    <button class="icon-btn" id="themeToggle" title="Toggle Light/Dark Mode">
                        <i class="fa-solid fa-moon" id="themeIcon"></i>
                    </button>
                </div>
            </header>

            <!-- Form Content container -->
            <form id="addPropertyForm" class="property-form-container">
                
                <div class="form-layout-grid">
                    <!-- Left Column (Main Form Details) -->
                    <div class="form-main-col">
                        
                        <!-- Section 1: Property Purpose & Type -->
                        <div class="card-panel glass form-section">
                            <div class="section-badge"><i class="fa-solid fa-target"></i></div>
                            <h3 class="section-title">Property Purpose & Type</h3>
                            <p class="section-desc" style="margin-top: -12px; margin-bottom: 24px; opacity: 0.7;">Define the core category of your listing.</p>
                            
                            <div class="form-group">
                                <label class="form-label">Purpose</label>
                                <div class="pill-group" id="purposeGroup">
                                    <button type="button" class="pill-btn active" data-value="sell">Sell</button>
                                    <button type="button" class="pill-btn" data-value="rent">Rent</button>
                                </div>
                                <input type="hidden" name="property_purpose" id="propertyPurpose" value="sell">
                            </div>

                            <div class="form-group">
                                <label class="form-label mb-1">Select Property Type</label>
                                <div class="type-tabs" id="typeGroupMain">
                                    <button type="button" class="type-tab-btn active" data-value="home">Home</button>
                                    <button type="button" class="type-tab-btn" data-value="plot">Plots</button>
                                    <button type="button" class="type-tab-btn" data-value="commercial">Commercial</button>
                                </div>
                            </div>
                            
                            <!-- Sub Type selection directly below -->
                            <div class="form-group hidden-sub-type" id="subTypeHome" style="display: block;">
                                <div class="chip-group wrap-chips">
                                    <button type="button" class="chip-btn active"><i class="fa-solid fa-house"></i> House</button>
                                    <button type="button" class="chip-btn"><i class="fa-solid fa-building"></i> Flat</button>
                                    <button type="button" class="chip-btn"><i class="fa-solid fa-house-chimney"></i> Upper Portion</button>
                                    <button type="button" class="chip-btn"><i class="fa-solid fa-house-chimney"></i> Lower Portion</button>
                                    <button type="button" class="chip-btn"><i class="fa-solid fa-tree"></i> Farm House</button>
                                    <button type="button" class="chip-btn"><i class="fa-solid fa-door"></i> Room</button>
                                    <button type="button" class="chip-btn"><i class="fa-solid fa-building"></i> Penthouse</button>
                                </div>
                            </div>

                            <div class="form-group hidden-sub-type" id="subTypePlot" style="display: none;">
                                <div class="chip-group wrap-chips">
                                    <button type="button" class="chip-btn active"><i class="fa-solid fa-table-cells-large"></i> Residential Plot</button>
                                    <button type="button" class="chip-btn"><i class="fa-solid fa-building"></i> Commercial Plot</button>
                                    <button type="button" class="chip-btn"><i class="fa-solid fa-plant"></i> Agricultural Land</button>
                                    <button type="button" class="chip-btn"><i class="fa-solid fa-factory"></i> Industrial Land</button>
                                    <button type="button" class="chip-btn"><i class="fa-solid fa-file-text"></i> Plot File</button>
                                    <button type="button" class="chip-btn"><i class="fa-solid fa-file-dashed"></i> Plot Form</button>
                                </div>
                            </div>

                            <div class="form-group hidden-sub-type" id="subTypeCommercial" style="display: none;">
                                <div class="chip-group wrap-chips">
                                    <button type="button" class="chip-btn active"><i class="fa-solid fa-briefcase"></i> Office</button>
                                    <button type="button" class="chip-btn"><i class="fa-solid fa-storefront"></i> Shop</button>
                                    <button type="button" class="chip-btn"><i class="fa-solid fa-warehouse"></i> Warehouse</button>
                                    <button type="button" class="chip-btn"><i class="fa-solid fa-factory"></i> Factory</button>
                                    <button type="button" class="chip-btn"><i class="fa-solid fa-building"></i> Building</button>
                                    <button type="button" class="chip-btn"><i class="fa-solid fa-circles-three"></i> Other</button>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Location and Area -->
                        <div class="card-panel glass form-section">
                            <div class="section-badge"><i class="fa-solid fa-map-pin"></i></div>
                            <h3 class="section-title">Location Details</h3>
                            <p class="section-desc" style="margin-top: -12px; margin-bottom: 24px; opacity: 0.7;">Pinpoint the exact location of your property.</p>
                            
                            <div class="form-row">
                                <div class="form-group half">
                                    <label class="form-label">City</label>
                                    <div class="input-wrapper">
                                        <i class="fa-solid fa-building"></i>
                                        <select name="city_id" class="glass-input custom-select" required>
                                            <option value="" disabled selected>Select City</option>
                                            <?php
                                            $cities = $pdo->query("SELECT id, name FROM cities ORDER BY name ASC")->fetchAll();
                                            foreach ($cities as $city) {
                                                echo "<option value='{$city->id}'>{$city->name}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group half">
                                    <label class="form-label">Location / Society</label>
                                    <div class="input-wrapper">
                                        <i class="fa-solid fa-map-trifold"></i>
                                        <input type="text" name="location_name" class="glass-input" placeholder="e.g. DHA Phase 8" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Price and Details -->
                        <div class="card-panel glass form-section">
                            <div class="section-badge"><i class="fa-solid fa-currency-circle-dollar"></i></div>
                            <h3 class="section-title">Price & Area Specifications</h3>
                            <p class="section-desc" style="margin-top: -12px; margin-bottom: 24px; opacity: 0.7;">Define the financial and physical property specs.</p>
                            
                            <div class="form-row">
                                <div class="form-group half">
                                    <label class="form-label">Land Area Size</label>
                                    <div class="input-group">
                                        <input type="number" name="area_size" class="glass-input input-group-text" placeholder="Size" required>
                                        <select name="area_unit" class="glass-input input-group-append">
                                            <option value="marla">Marla</option>
                                            <option value="sqyrd">Sq. Yd.</option>
                                            <option value="sqft">Sq. Ft.</option>
                                            <option value="kanal">Kanal</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-group half">
                                    <label class="form-label">Total Price (PKR)</label>
                                    <div class="input-wrapper">
                                        <i class="fa-solid fa-money"></i>
                                        <input type="number" name="price" class="glass-input" placeholder="e.g. 15000000" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row" style="margin-top: 16px;">
                                <div class="form-group flex-row half">
                                    <div class="toggle-text">
                                        <div class="toggle-title">Installment Available</div>
                                        <div class="toggle-desc">Can this be paid in installments?</div>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                
                                <div class="form-group flex-row half">
                                    <div class="toggle-text">
                                        <div class="toggle-title">Ready for Possession</div>
                                        <div class="toggle-desc">Is the property ready to move in?</div>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" checked>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Features & Amenities -->
                        <div class="card-panel glass form-section">
                            <div class="section-badge"><i class="fa-solid fa-list-star"></i></div>
                            <h3 class="section-title">Features & Amenities</h3>
                            <p class="section-desc" style="margin-top: -12px; margin-bottom: 24px; opacity: 0.7;">Highlight the key features that make this listing stand out.</p>
                            
                            <!-- Beds and Baths Chips -->
                            <div class="form-row" id="bedsBathsSection">
                                <div class="form-group half" style="flex: 1;">
                                    <label class="form-label">Bedrooms</label>
                                    <div class="chip-group circle-chips" style="flex-wrap: wrap; gap: 8px;">
                                        <button type="button" class="chip-btn circle">1</button>
                                        <button type="button" class="chip-btn circle">2</button>
                                        <button type="button" class="chip-btn circle active">3</button>
                                        <button type="button" class="chip-btn circle">4</button>
                                        <button type="button" class="chip-btn circle">5</button>
                                        <button type="button" class="chip-btn circle">6</button>
                                        <button type="button" class="chip-btn circle">7</button>
                                        <button type="button" class="chip-btn circle">8</button>
                                        <button type="button" class="chip-btn circle">9</button>
                                        <button type="button" class="chip-btn circle">10+</button>
                                    </div>
                                </div>
                                <div class="form-group half" style="flex: 1;">
                                    <label class="form-label">Bathrooms</label>
                                    <div class="chip-group circle-chips" style="flex-wrap: wrap; gap: 8px;">
                                        <button type="button" class="chip-btn circle">1</button>
                                        <button type="button" class="chip-btn circle active">2</button>
                                        <button type="button" class="chip-btn circle">3</button>
                                        <button type="button" class="chip-btn circle">4</button>
                                        <button type="button" class="chip-btn circle">5</button>
                                        <button type="button" class="chip-btn circle">6</button>
                                        <button type="button" class="chip-btn circle">7+</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Amenities Modal Trigger & Selected Display -->
                            <div class="form-group">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                    <label class="form-label" style="margin-bottom: 0;">Additional Amenities</label>
                                    <button type="button" class="btn-primary" id="openAmenitiesModalBtn" style="padding: 8px 16px; font-size: 13px; border-radius: 20px;">
                                        <i class="fa-solid fa-plus"></i> Add Amenities
                                    </button>
                                </div>
                                
                                <div class="chip-group" id="selectedAmenitiesDisplay">
                                    <div class="empty-state-text" style="color: var(--text-secondary); font-size: 14px; font-style: italic;">No additional amenities selected.</div>
                                </div>
                                
                                <input type="hidden" name="property_amenities" id="propertyAmenitiesInput" value="[]">
                            </div>

                            <!-- Quality Tip -->
                            <div class="quality-tip">
                                <i class="fa-solid fa-circle-check tip-icon"></i>
                                <div class="tip-content">
                                    <div class="tip-title">Quality Tip</div>
                                    <p class="tip-desc">Add at least 5 amenities to help your listing stand out and attract more leads.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Section 5: Property Documents -->
                        <div class="card-panel glass form-section">
                            <div class="section-badge"><i class="fa-solid fa-file-pdf"></i></div>
                            <h3 class="section-title">Property Documents (Optional)</h3>
                            <p class="section-desc" style="margin-top: -12px; margin-bottom: 24px; opacity: 0.7;">Upload NOC, Map, or Ownership documents.</p>
                            
                            <div class="form-group" style="margin-top: 16px;">
                                <div class="upload-zone" style="height: 120px; border-style: dashed; position: relative;">
                                    <i class="fa-solid fa-file-pdf" style="font-size: 32px; opacity: 0.5;"></i>
                                    <p>Drop PDF documents here or click to upload</p>
                                    <input type="file" name="documents[]" multiple style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;">
                                </div>
                                <div style="font-size: 11px; color: var(--text-secondary); margin-top: 8px;">Max file size: 5MB per document.</div>
                            </div>
                        </div>

                        <!-- Section 6: Listing Description -->
                        <div class="card-panel glass form-section">
                            <div class="section-badge"><i class="fa-solid fa-text-aa"></i></div>
                            <h3 class="section-title">Listing Description</h3>
                            <p class="section-desc" style="margin-top: -12px; margin-bottom: 24px; opacity: 0.7;">Write a compelling title and description for your ad.</p>

                            <div class="form-group">
                                <label class="form-label">Property Title</label>
                                <input type="text" name="title" class="glass-input" placeholder="e.g. Modern Villa by Skyline Realty" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="glass-input" rows="6" placeholder="Describe the property's unique selling points..." required></textarea>
                            </div>
                        </div>

                        <!-- Section 6: Assign to Agent (Agency Specific) -->
                        <div class="card-panel glass form-section">
                            <div class="section-badge"><i class="fa-solid fa-circle-user-plus"></i></div>
                            <h3 class="section-title">Assign to Agent</h3>
                            <p class="section-desc" style="margin-top: -12px; margin-bottom: 24px; opacity: 0.7;">Select which team member will handle this listing.</p>

                            <div class="form-group" style="margin-top: 16px;">
                                <div class="chip-group wrap-chips" id="agentSelectionGroup">
                                    <?php
                                    $agents = $pdo->prepare("SELECT u.id, u.full_name, u.avatar_url FROM agents a JOIN users u ON a.user_id = u.id WHERE a.agency_id = ?");
                                    $agents->execute([$agency->id]);
                                    $agency_agents = $agents->fetchAll();
                                    
                                    foreach ($agency_agents as $index => $agent) {
                                        $active_class = ($index === 0) ? 'active' : '';
                                        $avatar = $agent->avatar_url ? '../' . $agent->avatar_url : 'https://i.pravatar.cc/150?img=' . (20 + $agent->id);
                                        echo "<button type='button' class='chip-btn {$active_class}' data-value='{$agent->id}'>
                                                <img src='{$avatar}' class='mini-avatar'> {$agent->full_name}
                                              </button>";
                                    }
                                    ?>
                                </div>
                                <input type="hidden" name="agent_id" id="assignedAgentId" value="<?php echo $agency_agents[0]->id ?? ''; ?>">
                            </div>
                        </div>

                        <!-- Section 7: Platform Distribution -->
                        <div class="card-panel glass form-section" style="margin-bottom: 40px;">
                            <div class="section-badge"><i class="fa-solid fa-layout"></i></div>
                            <h3 class="section-title">Platform Distribution</h3>
                            <p class="section-desc" style="margin-top: -12px; margin-bottom: 24px; opacity: 0.7;">Publishing synchronously across platforms.</p>

                            <div class="platform-collab-card">
                                <div class="collab-badge">Partner Synergy</div>
                                
                                <!-- Side 1: Landsfy -->
                                <div class="collab-side">
                                    <img src="../includes/assets/images/favicon.png" alt="Landsfy" class="collab-logo">
                                    <span class="collab-name">landsfy.com</span>
                                </div>

                                <!-- Divider: X -->
                                <div class="collab-divider">X</div>

                                <!-- Side 2: Agency -->
                                <div class="collab-side">
                                    <div class="collab-logo" style="background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 24px; box-shadow: var(--glass-shadow);">S</div>
                                    <span class="collab-name">Skyline Realty</span>
                                </div>
                            </div>
                        </div>

                    </div>
                    
                    <!-- Right Column (Media) -->
                    <div class="form-sidebar-col">
                        
                        <div class="card-panel glass form-section upload-section" style="position: sticky; top: 124px;">
                            <div class="section-badge"><i class="fa-solid fa-images"></i></div>
                            <h3 class="section-title">Property Media</h3>
                            <p class="section-desc" style="margin-top: -12px; margin-bottom: 24px; opacity: 0.7;">High-quality images get 5x more leads.</p>

                            <div class="dropzone" id="imageDropzone">
                                <div class="dropzone-content">
                                    <i class="fa-solid fa-upload upload-icon"></i>
                                    <h4>Upload Images</h4>
                                    <p>Drag & Drop here</p>
                                </div>
                                <input type="file" id="fileInput" multiple accept="image/*" hidden>
                            </div>

                            <button type="button" class="btn-primary w-100" id="submitPropertyBtn" style="margin-top: 24px; justify-content: center; padding: 16px; font-size: 16px;">
                                <i class="fa-solid fa-paper-plane"></i> Post Property Listing
                            </button>
                        </div>

                    </div>
                </div>

            </form>
        </main>
    </div>

    <!-- External JS -->
    <script src="../includes/assets/js/script.js"></script>
    <script src="../includes/assets/js/agency/add-property.js"></script>
    <script src="../includes/assets/js/agency/notif-checker.js"></script>
</body>
</html>

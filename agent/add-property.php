<?php 
include 'header.php';

// Edit Mode Detection
$edit_mode = false;
$edit_property = null;
$edit_images = [];
$edit_amenities = [];

// Fetch User Email for contact info (Default for all modes)
try {
    $user_stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $user_stmt->execute([$_SESSION['user_id']]);
    $user_email = $user_stmt->fetchColumn();
} catch (PDOException $e) {
    $user_email = "";
}

// Fetch Agent Quota
try {
    $quota_stmt = $pdo->prepare("SELECT platinum_quota, platinum_used, diamond_quota, diamond_used FROM agents WHERE user_id = ?");
    $quota_stmt->execute([$_SESSION['user_id']]);
    $agent_quota = $quota_stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $agent_quota = null;
}

$plat_available = $agent_quota ? ($agent_quota['platinum_quota'] - $agent_quota['platinum_used']) : 0;
$diam_available = $agent_quota ? ($agent_quota['diamond_quota'] - $agent_quota['diamond_used']) : 0;

if (isset($_GET['id'])) {
    $edit_id = intval($_GET['id']);
    try {
        $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ? AND author_id = ?");
        $stmt->execute([$edit_id, $_SESSION['user_id']]);
        $edit_property = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($edit_property) {
            $edit_mode = true;
            
            // Fetch Images
            $img_stmt = $pdo->prepare("SELECT id, image_url, is_main FROM property_images WHERE property_id = ? ORDER BY sort_order ASC");
            $img_stmt->execute([$edit_id]);
            $edit_images = $img_stmt->fetchAll(PDO::FETCH_ASSOC);

            // Fetch Amenities
            $amen_stmt = $pdo->prepare("SELECT amenity_field_id, value FROM property_amenity_values WHERE property_id = ?");
            $amen_stmt->execute([$edit_id]);
            $amen_rows = $amen_stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($amen_rows as $row) {
                $edit_amenities[$row['amenity_field_id']] = $row['value'];
            }

            // Fetch Contacts
            $contact_stmt = $pdo->prepare("SELECT id, phone_number, label FROM property_contacts WHERE property_id = ?");
            $contact_stmt->execute([$edit_id]);
            $edit_contacts = $contact_stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        // Handle error quietly or show message
    }
}

// Fetch Cities
$cities_stmt = $pdo->query("SELECT id, name, slug FROM cities ORDER BY name ASC");
$cities = $cities_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Categories
$categories_stmt = $pdo->query("SELECT id, name, slug, icon_class FROM property_categories ORDER BY sort_order ASC");
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Subtypes
$subtypes_stmt = $pdo->query("SELECT id, category_id, name, slug, icon_class FROM property_subtypes ORDER BY sort_order ASC");
$subtypes = $subtypes_stmt->fetchAll(PDO::FETCH_ASSOC);

// Group subtypes by category
$subtypes_by_category = [];
foreach ($subtypes as $st) {
    $subtypes_by_category[$st['category_id']][] = $st;
}

// Fetch Amenity Groups
$groups_stmt = $pdo->query("SELECT id, name, icon_class FROM amenity_groups ORDER BY sort_order ASC");
$amenity_groups = $groups_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Amenity Fields
$fields_stmt = $pdo->query("SELECT id, group_id, label, field_type, options, icon_class, context FROM amenity_fields ORDER BY sort_order ASC");
$amen_fields = $fields_stmt->fetchAll(PDO::FETCH_ASSOC);

// Group fields by group ID
$fields_by_group = [];
foreach ($amen_fields as $f) {
    $fields_by_group[$f['group_id']][] = $f;
}

?>
<style>
    .gallery-grid { 
        display: flex; 
        overflow-x: auto; 
        gap: 12px; 
        width: 100%; 
        margin-top: 15px; 
        padding-bottom: 12px;
        scrollbar-width: thin;
        scrollbar-color: var(--primary-color) transparent;
    }
    .gallery-grid::-webkit-scrollbar { height: 6px; }
    .gallery-grid::-webkit-scrollbar-track { background: transparent; }
    .gallery-grid::-webkit-scrollbar-thumb { background: rgba(var(--primary-rgb), 0.3); border-radius: 10px; }
    .gallery-grid::-webkit-scrollbar-thumb:hover { background: var(--primary-color); }

    .gallery-item { 
        flex: 0 0 120px;
        position: relative; 
        border-radius: 12px; 
        overflow: hidden; 
        aspect-ratio: 1; 
        border: 1px solid var(--border-color); 
        background: var(--surface-bg); 
    }
    .gallery-item img { width: 100%; height: 100%; object-fit: cover; }
    .remove-img-btn { position: absolute; top: 5px; right: 5px; background: rgba(255, 75, 75, 0.9); border: none; color: white; border-radius: 50%; width: 22px; height: 22px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 14px; backdrop-filter: blur(4px); transition: all 0.2s; z-index: 10; }
    .remove-img-btn:hover { background: rgba(255, 0, 0, 1); transform: scale(1.1); }
    .btn-primary.loading { opacity: 0.8; cursor: not-allowed; pointer-events: none; }
    .spinner { animation: spin 1s linear infinite; margin-right: 8px; }
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

    /* Suggestion List Styling */
    .suggestions-wrapper { position: relative; }
    .suggestions-list {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        border-top: none;
        border-bottom-left-radius: 12px;
        border-bottom-right-radius: 12px;
        z-index: 1000;
        max-height: 250px;
        overflow-y: auto;
        box-shadow: var(--glass-shadow);
        display: none;
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
    }
    .suggestion-item {
        padding: 12px 16px;
        color: var(--text-primary);
        cursor: pointer;
        font-size: 14px;
        transition: all 0.2s;
        border-bottom: 1px solid var(--glass-border);
    }
    .suggestion-item:last-child { border-bottom: none; }
    .suggestion-item:hover, .suggestion-item.active {
        background: var(--primary);
        color: #fff;
    }
    .suggestion-item i { margin-right: 8px; color: var(--text-secondary); }

    /* Listing Tier Styling */
    .tier-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
        margin-top: 12px;
    }
    .tier-card {
        padding: 20px;
        border-radius: 20px;
        border: 2px solid var(--glass-border);
        background: rgba(255, 255, 255, 0.02);
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 12px;
        position: relative;
        overflow: hidden;
    }
    .tier-card:hover {
        transform: translateY(-5px);
        background: rgba(255, 255, 255, 0.05);
        border-color: var(--primary-light);
    }
    .tier-card.active {
        border-color: var(--primary);
        background: rgba(107, 0, 182, 0.08);
        box-shadow: 0 10px 25px rgba(107, 0, 182, 0.15);
    }
    .tier-card.active::before {
        content: '\f22e';
        font-family: "Phosphor";
        position: absolute;
        top: 12px;
        right: 12px;
        color: var(--primary);
        font-size: 20px;
    }
    .tier-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        background: rgba(255, 255, 255, 0.05);
    }
    .tier-name { font-weight: 800; font-size: 16px; color: var(--text-primary); }
    .tier-price { font-size: 14px; font-weight: 600; color: var(--text-secondary); }
    .tier-badge {
        position: absolute;
        top: 0;
        right: 0;
        background: var(--primary-gradient);
        color: white;
        font-size: 10px;
        font-weight: 800;
        padding: 4px 12px;
        border-bottom-left-radius: 12px;
        text-transform: uppercase;
    }
    .tier-simple .tier-icon { color: var(--text-secondary); }
    .tier-platinum .tier-icon { color: #10B981; background: rgba(16, 185, 129, 0.1); }
    .tier-diamond .tier-icon { color: #3B82F6; background: rgba(59, 130, 246, 0.1); }
    .tier-card.active.tier-platinum { border-color: #10B981; background: rgba(16, 185, 129, 0.05); }
    .tier-card.active.tier-diamond { border-color: #3B82F6; background: rgba(59, 130, 246, 0.05); }
</style>

        <!-- Main Content Area -->
        <main class="main-content">
            
            <!-- Top Header -->
            <header class="header glass">
                <div class="header-left">
                    <div class="page-title"><?php echo $edit_mode ? 'Edit Listing' : 'Create New Listing'; ?></div>
                    <div class="breadcrumb"><?php echo htmlspecialchars($_SESSION['username']); ?> / <?php echo $edit_mode ? 'Edit Property' : 'New Property'; ?></div>
                </div>
                
                <div class="header-actions">
                    <button class="icon-btn" id="themeToggle" title="Toggle Light/Dark Mode">
                        <i class="fa-solid fa-moon" id="themeIcon"></i>
                    </button>
                    <a href="notifications.php" class="icon-btn">
                        <i class="fa-solid fa-bell"></i>
                    </a>
                </div>
            </header>

            <!-- Form Content container -->
            <form id="addPropertyForm" class="property-form-container">
                <?php if ($edit_mode): ?>
                    <input type="hidden" name="property_id" value="<?php echo $edit_property['id']; ?>">
                <?php endif; ?>
                
                <div class="property-form-full">
                    <div class="form-main-col">
                        
                        <!-- Section 1: Property Purpose & Type -->
                        <div class="card-panel glass form-section">
                            <div class="section-badge"><i class="fa-solid fa-bullseye"></i></div>
                            <h3 class="section-title">Property Purpose & Type</h3>
                            <p class="section-desc" style="margin-top: -12px; margin-bottom: 24px; opacity: 0.7;">Define the core category of your listing.</p>
                            
                            <div class="form-group">
                                <label class="form-label">Purpose</label>
                                <div class="pill-group" id="purposeGroup">
                                    <button type="button" class="pill-btn <?php echo ($edit_property['purpose'] ?? 'sell') === 'sell' ? 'active' : ''; ?>" data-value="sell">Sell</button>
                                    <button type="button" class="pill-btn <?php echo ($edit_property['purpose'] ?? '') === 'rent' ? 'active' : ''; ?>" data-value="rent">Rent</button>
                                </div>
                                <input type="hidden" name="property_purpose" id="propertyPurpose" value="<?php echo htmlspecialchars($edit_property['purpose'] ?? 'sell'); ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label mb-1">Select Property Type</label>
                                <div class="type-tabs" id="typeGroupMain">
                                    <?php foreach ($categories as $index => $cat): ?>
                                        <button type="button" class="type-tab-btn <?php echo $index === 0 ? 'active' : ''; ?>" 
                                                data-id="<?php echo $cat['id']; ?>"
                                                data-value="<?php echo htmlspecialchars($cat['slug']); ?>">
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <!-- Sub Type selection directly below -->
                            <?php foreach ($categories as $index => $cat): ?>
                                <div class="form-group hidden-sub-type" id="subType<?php echo ucfirst($cat['slug']); ?>" 
                                     style="display: <?php echo $index === 0 ? 'block' : 'none'; ?>;">
                                    <div class="chip-group wrap-chips">
                                        <?php if (isset($subtypes_by_category[$cat['id']])): ?>
                                            <?php foreach ($subtypes_by_category[$cat['id']] as $stIndex => $st): ?>
                                                <button type="button" class="chip-btn <?php echo $stIndex === 0 ? 'active' : ''; ?>"
                                                        data-id="<?php echo $st['id']; ?>">
                                                    <i class="fa-solid <?php echo htmlspecialchars($st['icon_class']); ?>"></i> 
                                                    <?php echo htmlspecialchars($st['name']); ?>
                                                </button>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <input type="hidden" name="category_id" id="categoryIdInput" value="<?php echo $categories[0]['id']; ?>">
                                <input type="hidden" name="subtype_id" id="subtypeIdInput" value="<?php echo $subtypes_by_category[$categories[0]['id']][0]['id']; ?>">
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
                                        <select class="glass-input custom-select" name="city_id">
                                            <option value="" disabled <?php echo !$edit_mode ? 'selected' : ''; ?>>Select City</option>
                                            <?php foreach ($cities as $city): ?>
                                                <option value="<?php echo htmlspecialchars($city['id']); ?>" <?php echo ($edit_property['city_id'] ?? '') == $city['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($city['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group half">
                                    <label class="form-label">Location / Society</label>
                                    <div class="suggestions-wrapper">
                                        <div class="input-wrapper">
                                            <i class="fa-solid fa-map-location-dot"></i>
                                            <input type="text" class="glass-input" id="locationInput" name="location_name" autocomplete="off" placeholder="e.g. DHA Phase 8" value="<?php echo htmlspecialchars($edit_property['location_name'] ?? ''); ?>">
                                        </div>
                                        <div class="suggestions-list" id="locationSuggestions"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Price and Details -->
                        <div class="card-panel glass form-section">
                            <div class="section-badge"><i class="fa-solid fa-circle-dollar-to-slot"></i></div>
                            <h3 class="section-title">Price & Area Specifications</h3>
                            <p class="section-desc" style="margin-top: -12px; margin-bottom: 24px; opacity: 0.7;">Define the financial and physical property specs.</p>
                            
                            <div class="form-row">
                                <div class="form-group half">
                                    <label class="form-label">Land Area Size</label>
                                    <div class="input-group">
                                        <input type="number" class="glass-input input-group-text" name="area_size" placeholder="Size" value="<?php echo htmlspecialchars($edit_property['area_size'] ?? ''); ?>">
                                        <select class="glass-input input-group-append" name="area_unit">
                                            <option value="marla" <?php echo ($edit_property['area_unit'] ?? '') === 'marla' ? 'selected' : ''; ?>>Marla</option>
                                            <option value="sqyrd" <?php echo ($edit_property['area_unit'] ?? '') === 'sqyrd' ? 'selected' : ''; ?>>Sq. Yd.</option>
                                            <option value="sqft" <?php echo ($edit_property['area_unit'] ?? '') === 'sqft' ? 'selected' : ''; ?>>Sq. Ft.</option>
                                            <option value="kanal" <?php echo ($edit_property['area_unit'] ?? '') === 'kanal' ? 'selected' : ''; ?>>Kanal</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-group half">
                                    <label class="form-label">Total Price (PKR)</label>
                                    <div class="input-wrapper">
                                        <i class="fa-solid fa-money-bill-wave"></i>
                                        <input type="number" class="glass-input" name="property_price" id="propertyPriceInput" step="1" min="0" max="999999999999" placeholder="e.g. 15000000" value="<?php echo htmlspecialchars($edit_property['price'] ?? ''); ?>">
                                    </div>
                                    <div id="priceInWords" class="price-in-words"></div>
                                </div>
                            </div>

                            <div class="form-row" style="margin-top: 16px;">
                                <div class="form-group flex-row half">
                                    <div class="toggle-text">
                                        <div class="toggle-title">Installment Available</div>
                                        <div class="toggle-desc">Can this be paid in installments?</div>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="is_installment_available" value="1" <?php echo ($edit_property['is_installment_available'] ?? 0) ? 'checked' : ''; ?>>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                
                                <div class="form-group flex-row half">
                                    <div class="toggle-text">
                                        <div class="toggle-title">Ready for Possession</div>
                                        <div class="toggle-desc">Is the property ready to move in?</div>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="is_ready_for_possession" value="1" <?php echo ($edit_mode ? ($edit_property['is_ready_for_possession'] ?? 0) : 1) ? 'checked' : ''; ?>>
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
                                    <input type="hidden" name="bedrooms" id="bedsInput" value="3">
                                    <input type="hidden" name="bathrooms" id="bathsInput" value="2">
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

                        <!-- Section 5: Description -->
                        <div class="card-panel glass form-section">
                            <div class="section-badge"><i class="fa-solid fa-text-aa"></i></div>
                            <h3 class="section-title">Listing Description</h3>
                            <p class="section-desc" style="margin-top: -12px; margin-bottom: 24px; opacity: 0.7;">Write a compelling title and description for your ad.</p>

                             <div class="form-group">
                                 <label class="form-label">Property Title</label>
                                 <input type="text" class="glass-input" name="property_title" placeholder="e.g. Modern Villa by Skyline Realty" value="<?php echo htmlspecialchars($edit_property['title'] ?? ''); ?>">
                             </div>
                             
                             <div class="form-group">
                                 <label class="form-label">Description</label>
                                 <textarea class="glass-input" name="property_description" rows="6" placeholder="Describe the property's unique selling points..."><?php echo htmlspecialchars($edit_property['description'] ?? ''); ?></textarea>
                             </div>
                        </div>

                        <!-- Section 6: Property Media -->
                        <div class="card-panel glass form-section upload-section">
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

                            <!-- Video Dropzone -->
                            <div class="dropzone" id="videoDropzone" style="margin-top: 24px; padding: 24px 16px;">
                                <div class="dropzone-content">
                                    <i class="fa-solid fa-video upload-icon" style="font-size: 32px; margin-bottom: 8px;"></i>
                                    <h4 style="font-size: 14px;">Upload Video (Optional)</h4>
                                </div>
                                <input type="file" id="videoInput" accept="video/mp4,video/x-m4v,video/*" hidden>
                            </div>
                        </div>

                        <!-- Section 7: Contact Information -->
                        <div class="card-panel glass form-section contact-section" style="margin-bottom: 24px;">
                            <div class="section-badge"><i class="fa-solid fa-address-book"></i></div>
                            
                            <div class="contact-header-row">
                                <h3 class="section-title" style="margin-bottom: 24px; padding-left: 0;">Contact Information</h3>
                            </div>

                            <div class="contact-fields-container">
                                <!-- Email Row -->
                                <div class="contact-field-row">
                                    <div class="field-icon"><i class="fa-solid fa-envelope"></i></div>
                                    <div class="field-content">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="glass-input" name="property_email" placeholder="e.g. agent@landsfy.com" value="<?php echo htmlspecialchars($edit_property['contact_email'] ?? ($user_email ?: ($_SESSION['email'] ?? ''))); ?>">
                                    </div>
                                </div>

                                <!-- Mobile Row -->
                                <div class="contact-field-row" id="mobileFieldsContainer">
                                    <div class="field-icon"><i class="fa-solid fa-mobile-screen-button"></i></div>
                                    <div class="field-content">
                                        <label class="form-label">Mobile</label>
                                        <div class="phone-input-group">
                                            <div class="country-prefix">
                                                <img src="https://flagcdn.com/w20/pk.png" alt="PK">
                                                <i class="fa-solid fa-chevron-down"></i>
                                            </div>
                                            <input type="tel" class="glass-input" name="property_contacts[]" placeholder="+92">
                                            <button type="button" class="add-phone-btn" id="addPhoneBtn">
                                                <i class="fa-solid fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Landline Row -->
                                <div class="contact-field-row">
                                    <div class="field-icon"><i class="fa-solid fa-phone"></i></div>
                                    <div class="field-content">
                                        <label class="form-label">Landline</label>
                                        <div class="phone-input-group">
                                            <div class="country-prefix">
                                                <img src="https://flagcdn.com/w20/pk.png" alt="PK">
                                                <i class="fa-solid fa-chevron-down"></i>
                                            </div>
                                            <input type="tel" class="glass-input" name="property_contacts[]" placeholder="+92">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 7: Listing Tier Selection -->
                        <div class="card-panel glass form-section" style="margin-bottom: 24px;">
                            <div class="section-badge"><i class="fa-solid fa-crown"></i></div>
                            <h3 class="section-title">Listing Exposure</h3>
                            <p class="section-desc" style="margin-top: -12px; margin-bottom: 24px; opacity: 0.7;">Select a tier to increase your property's visibility.</p>
                            
                            <div class="tier-cards" id="listingTierGroup">
                                <div class="tier-card tier-simple active" data-value="none">
                                    <div class="tier-icon"><i class="fa-solid fa-house"></i></div>
                                    <div class="tier-info">
                                        <div class="tier-name">Simple</div>
                                        <div class="tier-price">FREE</div>
                                    </div>
                                </div>
                                <div class="tier-card tier-platinum" data-value="platinum">
                                    <div class="tier-badge">Most Popular</div>
                                    <div class="tier-icon"><i class="fa-solid fa-sketch-logo"></i></div>
                                    <div class="tier-info">
                                        <div class="tier-name">Platinum</div>
                                        <div class="tier-price" style="font-weight: 800; color: #10B981;">
                                            <?php echo $plat_available > 0 ? "Use Credit ($plat_available Available)" : "PKR 4,999"; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="tier-card tier-diamond" data-value="diamond">
                                    <div class="tier-badge">Max Exposure</div>
                                    <div class="tier-icon"><i class="fa-solid fa-diamond"></i></div>
                                    <div class="tier-info">
                                        <div class="tier-name">Diamond</div>
                                        <div class="tier-price" style="font-weight: 800; color: #3B82F6;">
                                            <?php echo $diam_available > 0 ? "Use Credit ($diam_available Available)" : "PKR 12,999"; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                                </div>
                            </div>
                            <input type="hidden" name="premium_type" id="premiumTypeInput" value="none">

                             <button type="submit" id="submitPropertyBtn" class="btn-primary" style="margin-top: 24px; justify-content: center; padding: 18px 40px; font-size: 18px; font-weight: 800; width: auto; min-width: 300px; margin-left: auto; display: flex;">
                                 <i class="fa-solid fa-paper-plane"></i> <?php echo $edit_mode ? 'Update Property' : 'Submit Property'; ?>
                             </button>
                        </div>

                    </div>
                </div>

            </form>
        </main>
    </div>

    <!-- Amenities Modal -->
    <div class="modal-overlay" id="amenitiesModal">
        <div class="modal-content glass">
            <div class="modal-header">
                <h3 class="modal-title">Feature and Amenities</h3>
                <button type="button" class="close-modal-btn" id="closeAmenitiesModalBtn"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="modal-body">
                <div class="modal-tabs">
                    <?php foreach ($amenity_groups as $index => $group): ?>
                        <button class="modal-tab-btn <?php echo $index === 0 ? 'active' : ''; ?>" 
                                data-target="tab-<?php echo $group['id']; ?>" 
                                data-context="all">
                            <?php echo htmlspecialchars($group['name']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <div class="modal-tab-content">
                    <?php foreach ($amenity_groups as $index => $group): ?>
                        <div class="tab-pane <?php echo $index === 0 ? 'active' : ''; ?>" id="tab-<?php echo $group['id']; ?>">
                            <div class="amenities-grid">
                                <?php if (isset($fields_by_group[$group['id']])): ?>
                                    <?php foreach ($fields_by_group[$group['id']] as $f): ?>
                                        <div class="amenity-item <?php echo $f['field_type'] === 'switch' ? 'toggle-style' : ''; ?>" 
                                             data-context="<?php echo htmlspecialchars($f['context']); ?>">
                                            <span class="amenity-name"><?php echo htmlspecialchars($f['label']); ?></span>
                                            
                                            <?php if ($f['field_type'] === 'switch'): ?>
                                                <label class="toggle-switch small">
                                                    <input type="checkbox" class="amenity-checkbox" value="<?php echo htmlspecialchars($f['label']); ?>" data-id="<?php echo $f['id']; ?>">
                                                    <span class="slider"></span>
                                                </label>    
                                            <?php elseif ($f['field_type'] === 'number' || $f['field_type'] === 'number_group'): ?>
                                                <input type="number" class="glass-input amenity-input" data-id="<?php echo $f['id']; ?>">
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="cancelAmenitiesBtn">Cancel</button>
                <button type="button" class="btn-primary" id="saveAmenitiesBtn">Add Amenities</button>
            </div>
        </div>
    </div>

    <!-- External JS -->
    <script>
        // Inject Edit Mode Data
        window.editModeData = {
            isEdit: <?php echo $edit_mode ? 'true' : 'false'; ?>,
            property: <?php echo json_encode($edit_property); ?>,
            images: <?php echo json_encode($edit_images); ?>,
            amenities: <?php echo json_encode($edit_amenities); ?>,
            contacts: <?php echo json_encode($edit_contacts ?? []); ?>
        };
    </script>
    <script src="../includes/assets/js/utils.js"></script>
    <script src="../includes/assets/js/agent/add-property.js"></script>
    <script src="../includes/assets/js/script.js"></script>
</body>
</html>

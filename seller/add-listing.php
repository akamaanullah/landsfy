<?php
require_once '../includes/auth_check.php';
require_once '../includes/database/db.php';

if ($_SESSION['role_name'] != 'seller' && $_SESSION['role_name'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

// Edit Mode Detection
$edit_mode = false;
$edit_property = null;
$edit_images = [];
$edit_amenities = [];
$edit_contacts = [];

// Fetch User Email for contact info (Default for all modes)
try {
    $user_stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $user_stmt->execute([$_SESSION['user_id']]);
    $user_email = $user_stmt->fetchColumn();
} catch (PDOException $e) {
    $user_email = "";
}

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
    } catch (PDOException $e) {}
}

// Fetch Cities
$cities = $pdo->query("SELECT id, name FROM cities ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch Categories & Subtypes
$categories = $pdo->query("SELECT id, name, slug FROM property_categories ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);
$subtypes = $pdo->query("SELECT id, category_id, name, slug, icon_class FROM property_subtypes ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);
$subtypes_by_category = [];
foreach ($subtypes as $st) { $subtypes_by_category[$st['category_id']][] = $st; }

// Fetch Amenity Groups & Fields
$amenity_groups = $pdo->query("SELECT id, name FROM amenity_groups ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);
$amen_fields = $pdo->query("SELECT id, group_id, label, field_type, context FROM amenity_fields ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);
$fields_by_group = [];
foreach ($amen_fields as $f) { $fields_by_group[$f['group_id']][] = $f; }
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $edit_mode ? 'Edit Listing' : 'Post New Listing'; ?> | Landsfy Seller</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- External CSS -->
    <link rel="stylesheet" href="../includes/assets/css/style.css">
    <link rel="stylesheet" href="../includes/assets/css/agency-style.css">
    <style>
        .gallery-grid { display: flex; overflow-x: auto; gap: 12px; width: 100%; margin-top: 15px; padding-bottom: 12px; scrollbar-width: thin; scrollbar-color: var(--primary) transparent; }
        .gallery-grid::-webkit-scrollbar { height: 6px; }
        .gallery-grid::-webkit-scrollbar-thumb { background: rgba(107, 0, 182, 0.3); border-radius: 10px; }
        .gallery-item { flex: 0 0 120px; position: relative; border-radius: 12px; overflow: hidden; aspect-ratio: 1; border: 1px solid var(--glass-border); background: var(--glass-bg); }
        .gallery-item img { width: 100%; height: 100%; object-fit: cover; }
        .remove-img-btn { position: absolute; top: 5px; right: 5px; background: rgba(239, 68, 68, 0.9); border: none; color: white; border-radius: 50%; width: 22px; height: 22px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 14px; backdrop-filter: blur(4px); transition: all 0.2s; z-index: 10; }
        
        .suggestions-wrapper { position: relative; }
        .suggestions-list { position: absolute; top: 100%; left: 0; right: 0; background: var(--glass-bg); border: 1px solid var(--glass-border); border-top: none; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px; z-index: 1000; max-height: 250px; overflow-y: auto; box-shadow: var(--glass-shadow); display: none; backdrop-filter: blur(20px); }
        .suggestion-item { padding: 12px 16px; color: var(--text-primary); cursor: pointer; font-size: 14px; transition: all 0.2s; border-bottom: 1px solid var(--glass-border); }
        .suggestion-item:hover { background: var(--primary); color: #fff; }
    </style>
</head>
<body>
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
                <li class="nav-item"><a href="index.php"><i class="fa-solid fa-table-cells-large"></i> Dashboard</a></li>
                <li class="nav-item"><a href="my-listings.php"><i class="fa-solid fa-house-chimney"></i> My Listings</a></li>
                <li class="nav-item"><a href="leads.php"><i class="fa-solid fa-users"></i> Buyer Leads</a></li>
                <li class="nav-item active"><a href="add-listing.php"><i class="fa-solid fa-circle-plus"></i> Post New</a></li>
                <li class="nav-item"><a href="profile.php"><i class="fa-solid fa-circle-user"></i> Profile</a></li>
                <li class="nav-item logout-nav-item" style="margin-top: auto;"><a href="../logout.php" style="color: #ff4757;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="header glass">
                <div class="header-left">
                    <div class="page-title"><?php echo $edit_mode ? 'Edit Listing' : 'Post New Listing'; ?></div>
                    <div class="breadcrumb"><?php echo htmlspecialchars($_SESSION['username']); ?> / Property Ad</div>
                </div>
                <div class="header-actions">
                    <button class="icon-btn" id="themeToggle"><i class="fa-solid fa-moon" id="themeIcon"></i></button>
                    <button class="icon-btn"><i class="fa-solid fa-bell"></i></button>
                </div>
            </header>

            <form id="addPropertyForm" class="property-form-container">
                <?php if($edit_mode): ?><input type="hidden" name="property_id" value="<?php echo $edit_property['id']; ?>"><?php endif; ?>
                
                <div class="property-form-full">
                    <div class="form-main-col">
                        <!-- Section 1: Purpose & Type -->
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
                                        <button type="button" class="type-tab-btn <?php echo $index === 0 ? 'active' : ''; ?>" data-id="<?php echo $cat['id']; ?>" data-value="<?php echo htmlspecialchars($cat['slug']); ?>">
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <?php foreach ($categories as $index => $cat): ?>
                                <div class="form-group hidden-sub-type" id="subType<?php echo ucfirst($cat['slug']); ?>" style="display: <?php echo $index === 0 ? 'block' : 'none'; ?>;">
                                    <div class="chip-group wrap-chips">
                                        <?php if (isset($subtypes_by_category[$cat['id']])): ?>
                                            <?php foreach ($subtypes_by_category[$cat['id']] as $stIndex => $st): ?>
                                                <button type="button" class="chip-btn <?php echo $stIndex === 0 ? 'active' : ''; ?>" data-id="<?php echo $st['id']; ?>">
                                                    <i class="fa-solid <?php echo htmlspecialchars($st['icon_class']); ?>"></i> <?php echo htmlspecialchars($st['name']); ?>
                                                </button>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <input type="hidden" name="category_id" id="categoryIdInput" value="<?php echo $categories[0]['id']; ?>">
                            <input type="hidden" name="subtype_id" id="subtypeIdInput" value="<?php echo $subtypes_by_category[$categories[0]['id']][0]['id']; ?>">
                            <input type="hidden" name="premium_type" id="premiumTypeInput" value="none">
                        </div>

                        <!-- Section 2: Location -->
                        <div class="card-panel glass form-section">
                            <div class="section-badge"><i class="fa-solid fa-map-pin"></i></div>
                            <h3 class="section-title">Location Details</h3>
                            <div class="form-row">
                                <div class="form-group half">
                                    <label class="form-label">City</label>
                                    <div class="input-wrapper">
                                        <i class="fa-solid fa-building"></i>
                                        <select class="glass-input custom-select" name="city_id">
                                            <option value="" disabled <?php echo !$edit_mode ? 'selected' : ''; ?>>Select City</option>
                                            <?php foreach ($cities as $city): ?>
                                                <option value="<?php echo $city['id']; ?>" <?php echo ($edit_property['city_id'] ?? '') == $city['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($city['name']); ?></option>
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
                                            <input type="hidden" name="location_id" id="locationIdInput" value="<?php echo $edit_property['location_id'] ?? ''; ?>">
                                        </div>
                                        <div class="suggestions-list" id="locationSuggestions"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Price & Area -->
                        <div class="card-panel glass form-section">
                            <div class="section-badge"><i class="fa-solid fa-circle-dollar-to-slot"></i></div>
                            <h3 class="section-title">Price & Area Specifications</h3>
                            <div class="form-row">
                                <div class="form-group half">
                                    <label class="form-label">Land Area Size</label>
                                    <div class="input-group">
                                        <input type="number" class="glass-input input-group-text" name="area_size" placeholder="Size" value="<?php echo htmlspecialchars($edit_property['area_size'] ?? ''); ?>">
                                        <select class="glass-input input-group-append" name="area_unit">
                                            <option value="marla" <?php echo ($edit_property['area_unit'] ?? '') === 'marla' ? 'selected' : ''; ?>>Marla</option>
                                            <option value="sqyrd" <?php echo ($edit_property['area_unit'] ?? '') === 'sqyrd' ? 'selected' : ''; ?>>Sq. Yd.</option>
                                            <option value="kanal" <?php echo ($edit_property['area_unit'] ?? '') === 'kanal' ? 'selected' : ''; ?>>Kanal</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group half">
                                    <label class="form-label">Total Price (PKR)</label>
                                    <div class="input-wrapper">
                                        <i class="fa-solid fa-money-bill-wave"></i>
                                        <input type="number" class="glass-input" name="property_price" id="propertyPriceInput" placeholder="e.g. 15000000" value="<?php echo htmlspecialchars($edit_property['price'] ?? ''); ?>">
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
                                        <div class="toggle-desc">Is it ready to move in?</div>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="is_ready_for_possession" value="1" <?php echo ($edit_property['is_ready_for_possession'] ?? 1) ? 'checked' : ''; ?>>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Features & Amenities -->
                        <div class="card-panel glass form-section">
                            <div class="section-badge"><i class="fa-solid fa-list-star"></i></div>
                            <h3 class="section-title">Features & Amenities</h3>
                            <div class="form-row" id="bedsBathsSection">
                                <input type="hidden" name="bedrooms" id="bedsInput" value="<?php echo $edit_property['bedrooms'] ?? '3'; ?>">
                                <input type="hidden" name="bathrooms" id="bathsInput" value="<?php echo $edit_property['bathrooms'] ?? '2'; ?>">
                                <div class="form-group half" style="flex: 1;">
                                    <label class="form-label">Bedrooms</label>
                                    <div class="chip-group circle-chips">
                                        <?php for($i=1; $i<=10; $i++): ?>
                                            <button type="button" class="chip-btn circle <?php echo ($edit_property['bedrooms'] ?? 3) == $i || ($i==10 && ($edit_property['bedrooms'] ?? 0) >= 10) ? 'active' : ''; ?>"><?php echo $i == 10 ? '10+' : $i; ?></button>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="form-group half" style="flex: 1;">
                                    <label class="form-label">Bathrooms</label>
                                    <div class="chip-group circle-chips">
                                        <?php for($i=1; $i<=7; $i++): ?>
                                            <button type="button" class="chip-btn circle <?php echo ($edit_property['bathrooms'] ?? 2) == $i || ($i==7 && ($edit_property['bathrooms'] ?? 0) >= 7) ? 'active' : ''; ?>"><?php echo $i == 7 ? '7+' : $i; ?></button>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group" style="margin-top: 24px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                    <label class="form-label" style="margin-bottom:0;">Additional Amenities</label>
                                    <button type="button" class="btn-primary" id="openAmenitiesModalBtn" style="padding: 8px 16px; font-size: 13px; border-radius: 20px;">
                                        <i class="fa-solid fa-plus"></i> Add Amenities
                                    </button>
                                </div>
                                <div class="chip-group" id="selectedAmenitiesDisplay"></div>
                                <input type="hidden" name="property_amenities" id="propertyAmenitiesInput" value="[]">
                            </div>
                            <div class="quality-tip">
                                <i class="fa-solid fa-circle-check tip-icon"></i>
                                <div class="tip-content">
                                    <div class="tip-title">Quality Tip</div>
                                    <p class="tip-desc">Add at least 5 amenities to attract more leads.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Section 5: Description -->
                        <div class="card-panel glass form-section">
                            <div class="section-badge"><i class="fa-solid fa-text-aa"></i></div>
                            <h3 class="section-title">Listing Description</h3>
                            <div class="form-group">
                                <label class="form-label">Property Title</label>
                                <input type="text" class="glass-input" name="property_title" placeholder="e.g. Modern Villa" value="<?php echo htmlspecialchars($edit_property['title'] ?? ''); ?>">
                            </div>
                            <div class="form-group" style="margin-top: 24px;">
                                <label class="form-label">Description</label>
                                <textarea class="glass-input" name="property_description" rows="6"><?php echo htmlspecialchars($edit_property['description'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        </div>

                        <!-- Section 6: Property Media -->
                        <div class="card-panel glass form-section upload-section">
                            <div class="section-badge"><i class="fa-solid fa-images"></i></div>
                            <h3 class="section-title">Property Media</h3>
                            <div class="dropzone" id="imageDropzone">
                                <div class="dropzone-content">
                                    <i class="fa-solid fa-upload upload-icon"></i>
                                    <h4>Upload Images</h4>
                                    <input type="file" id="fileInput" multiple accept="image/*" hidden>
                                </div>
                            </div>
                            <div class="gallery-grid" id="imageGallery" style="margin-top: 15px;"></div>
                            <input type="hidden" name="deleted_images" id="deletedImagesInput" value="[]">
                            
                            <div class="dropzone" id="videoDropzone" style="margin-top: 24px; padding: 24px 16px;">
                                <div class="dropzone-content">
                                    <i class="fa-solid fa-video upload-icon" style="font-size: 32px; margin-bottom: 8px;"></i>
                                    <h4 style="font-size: 14px;">Upload Video (Optional)</h4>
                                </div>
                                <input type="file" id="videoInput" accept="video/mp4,video/x-m4v,video/*" hidden>
                            </div>
                        </div>

                        <!-- Section 7: Contact Information -->
                        <div class="card-panel glass form-section contact-section">
                            <div class="section-badge"><i class="fa-solid fa-address-book"></i></div>
                            <h3 class="section-title" style="margin-bottom: 24px;">Contact Information</h3>
                            <div class="contact-fields-container">
                                <div class="contact-field-row">
                                    <div class="field-icon"><i class="fa-solid fa-envelope"></i></div>
                                    <div class="field-content">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="glass-input" name="property_email" value="<?php echo htmlspecialchars($edit_property['contact_email'] ?? $user_email); ?>">
                                    </div>
                                </div>
                                <div class="contact-field-row" id="mobileFieldsContainer">
                                    <div class="field-icon"><i class="fa-solid fa-mobile-screen-button"></i></div>
                                    <div class="field-content">
                                        <label class="form-label">Mobile</label>
                                        <div class="phone-input-group">
                                            <input type="tel" class="glass-input" name="property_contacts[]" placeholder="+92">
                                            <button type="button" class="add-phone-btn" id="addPhoneBtn"><i class="fa-solid fa-plus"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                                </div>
                            </div>

                            <button type="submit" id="submitPropertyBtn" class="btn-primary" style="margin-top: 24px; justify-content: center; padding: 18px 40px; font-size: 18px; font-weight: 800; width: auto; min-width: 300px; margin-left: auto; display: flex;">
                                <i class="fa-solid fa-paper-plane"></i> <?php echo $edit_mode ? 'UPDATE AD' : 'POST AD'; ?>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>

    <!-- Amenities Modal (Cloned from Agent) -->
    <div class="modal-overlay" id="amenitiesModal">
        <div class="modal-content glass">
            <div class="modal-header">
                <h3 class="modal-title">Feature and Amenities</h3>
                <button type="button" class="close-modal-btn" id="closeAmenitiesModalBtn"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <div class="modal-tabs">
                    <?php foreach ($amenity_groups as $index => $group): ?>
                        <button type="button" class="modal-tab-btn <?php echo $index === 0 ? 'active' : ''; ?>" 
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
                                                <input type="number" class="glass-input amenity-input" placeholder="e.g. 2023" data-id="<?php echo $f['id']; ?>">
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

    <script>
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

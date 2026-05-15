<?php
require_once '../includes/auth_check.php';
require_once '../includes/database/db.php';

if ($_SESSION['role_name'] !== 'agency_owner' && $_SESSION['role_name'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$edit_id = $_GET['id'] ?? null;

if (!$edit_id) {
    header('Location: agency-listings.php');
    exit;
}

// Fetch agency info
$stmt = $pdo->prepare("SELECT a.* FROM agencies a WHERE a.owner_id = ?");
$stmt->execute([$user_id]);
$agency = $stmt->fetch();
$agency_id = $agency->id;

// Fetch Property Details (Ensure it belongs to this agency)
$stmt = $pdo->prepare("
    SELECT p.*, c.name as city_name, cat.slug as category_slug, st.slug as subtype_slug
    FROM properties p
    JOIN cities c ON p.city_id = c.id
    JOIN property_categories cat ON p.category_id = cat.id
    JOIN property_subtypes st ON p.subtype_id = st.id
    WHERE p.id = ? AND p.agency_id = ?
");
$stmt->execute([$edit_id, $agency_id]);
$property = $stmt->fetch();

if (!$property) {
    echo "Property not found or access denied.";
    exit;
}

// Fetch Images
$img_stmt = $pdo->prepare("SELECT id, image_url, is_main FROM property_images WHERE property_id = ? ORDER BY sort_order ASC");
$img_stmt->execute([$edit_id]);
$images = $img_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Amenities
$amen_stmt = $pdo->prepare("SELECT amenity_field_id, value FROM property_amenity_values WHERE property_id = ?");
$amen_stmt->execute([$edit_id]);
$amen_rows = $amen_stmt->fetchAll(PDO::FETCH_ASSOC);
$amenities = [];
foreach ($amen_rows as $row) {
    $amenities[$row['amenity_field_id']] = $row['value'];
}

// Fetch Documents
$doc_stmt = $pdo->prepare("SELECT id, document_type, document_url FROM property_documents WHERE property_id = ?");
$doc_stmt->execute([$edit_id]);
$documents = $doc_stmt->fetchAll(PDO::FETCH_ASSOC);

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

$agency_name = $agency ? $agency->name : 'My Agency';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Property | <?php echo htmlspecialchars($property->title); ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="../includes/assets/css/style.css">
    <link rel="stylesheet" href="../includes/assets/css/agency-style.css">
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
                <li class="nav-item"><a href="agency-listings.php"><i class="fa-solid fa-house-chimney"></i> Agency Inventory</a></li>
                <li class="nav-item"><a href="my-agents.php"><i class="fa-solid fa-users"></i> Our Team</a></li>
                <li class="nav-item"><a href="settings.php"><i class="fa-solid fa-gear"></i> Agency Profile</a></li>
                <li class="nav-item">
                    <a href="notifications.php">
                        <i class="fa-solid fa-bell"></i> Notifications
                        <span class="nav-badge" style="display: none;"></span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-bottom">
                 <div class="user-card">
                    <img src="<?php echo !empty($_SESSION['avatar_url']) ? '../' . $_SESSION['avatar_url'] : 'https://i.pravatar.cc/150?img=32'; ?>" alt="Admin" class="user-avatar">
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($agency_name); ?></div>
                        <div class="user-role">Agency Admin</div>
                    </div>
                </div>
                <a href="../logout.php" class="nav-item-logout" style="margin-top: 16px; display: flex; align-items: center; gap: 12px; padding: 12px; color: #ef4444; text-decoration: none; font-size: 14px; font-weight: 600;">
                    <i class="fa-solid fa-right-from-bracket" style="font-size: 20px;"></i> Logout
                </a>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <header class="header glass">
                <div class="header-left">
                    <div class="page-title">Edit Property</div>
                    <div class="breadcrumb"><?php echo htmlspecialchars($agency_name); ?> / Inventory / Edit</div>
                </div>
                <div class="header-actions">
                    <button class="icon-btn" id="themeToggle" title="Toggle Light/Dark Mode"><i class="fa-solid fa-moon" id="themeIcon"></i></button>
                </div>
            </header>

            <!-- Form Content -->
            <form id="editPropertyForm" class="property-form-container" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $edit_id; ?>">
                
                <div class="form-layout-grid">
                    <div class="form-main-col">
                        <!-- Section 1: Purpose & Type -->
                        <div class="card-panel glass form-section">
                            <div class="section-badge"><i class="fa-solid fa-target"></i></div>
                            <h3 class="section-title">Property Purpose & Type</h3>
                            
                            <div class="form-group">
                                <label class="form-label">Purpose</label>
                                <div class="pill-group" id="purposeGroup">
                                    <button type="button" class="pill-btn <?php echo $property->purpose === 'sell' ? 'active' : ''; ?>" data-value="sell">Sell</button>
                                    <button type="button" class="pill-btn <?php echo $property->purpose === 'rent' ? 'active' : ''; ?>" data-value="rent">Rent</button>
                                </div>
                                <input type="hidden" name="purpose" id="propertyPurpose" value="<?php echo htmlspecialchars($property->purpose); ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Property Type</label>
                                <div class="type-tabs" id="typeGroupMain">
                                    <button type="button" class="type-tab-btn <?php echo $property->category_slug === 'home' ? 'active' : ''; ?>" data-value="home">Home</button>
                                    <button type="button" class="type-tab-btn <?php echo $property->category_slug === 'plot' ? 'active' : ''; ?>" data-value="plot">Plots</button>
                                    <button type="button" class="type-tab-btn <?php echo $property->category_slug === 'commercial' ? 'active' : ''; ?>" data-value="commercial">Commercial</button>
                                </div>
                                <input type="hidden" name="category_id" id="categoryIdInput" value="<?php echo $property->category_id; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Sub Type</label>
                                <div class="chip-group wrap-chips" id="subTypeGroup">
                                    <?php
                                    $subtypes = $pdo->prepare("SELECT id, name, slug, icon_class FROM property_subtypes WHERE category_id = ?");
                                    $subtypes->execute([$property->category_id]);
                                    while ($st = $subtypes->fetch()) {
                                        $active = ($st->id == $property->subtype_id) ? 'active' : '';
                                        echo "<button type='button' class='chip-btn {$active}' data-value='{$st->id}'><i class='ph {$st->icon_class}'></i> {$st->name}</button>";
                                    }
                                    ?>
                                </div>
                                <input type="hidden" name="subtype_id" id="subtypeIdInput" value="<?php echo $property->subtype_id; ?>">
                            </div>
                        </div>

                        <!-- Section 2: Location -->
                        <div class="card-panel glass form-section">
                            <div class="section-badge"><i class="fa-solid fa-map-pin"></i></div>
                            <h3 class="section-title">Location Details</h3>
                            <div class="form-row">
                                <div class="form-group half">
                                    <label class="form-label">City</label>
                                    <select name="city_id" class="glass-input custom-select" required>
                                        <?php
                                        $cities = $pdo->query("SELECT id, name FROM cities ORDER BY name ASC")->fetchAll();
                                        foreach ($cities as $city) {
                                            $selected = ($city->id == $property->city_id) ? 'selected' : '';
                                            echo "<option value='{$city->id}' {$selected}>{$city->name}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group half">
                                    <label class="form-label">Location / Society</label>
                                    <input type="text" name="location_name" class="glass-input" value="<?php echo htmlspecialchars($property->location_name); ?>" required>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Price & Area -->
                        <div class="card-panel glass form-section">
                            <div class="section-badge"><i class="fa-solid fa-currency-circle-dollar"></i></div>
                            <h3 class="section-title">Price & Area Specifications</h3>
                            <div class="form-row">
                                <div class="form-group half">
                                    <label class="form-label">Land Area Size</label>
                                    <div class="input-group">
                                        <input type="number" name="area_size" class="glass-input input-group-text" value="<?php echo htmlspecialchars($property->area_size); ?>" required>
                                        <select name="area_unit" class="glass-input input-group-append">
                                            <option value="marla" <?php echo $property->area_unit === 'marla' ? 'selected' : ''; ?>>Marla</option>
                                            <option value="sqyrd" <?php echo $property->area_unit === 'sqyrd' ? 'selected' : ''; ?>>Sq. Yd.</option>
                                            <option value="sqft" <?php echo $property->area_unit === 'sqft' ? 'selected' : ''; ?>>Sq. Ft.</option>
                                            <option value="kanal" <?php echo $property->area_unit === 'kanal' ? 'selected' : ''; ?>>Kanal</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group half">
                                    <label class="form-label">Total Price (PKR)</label>
                                    <input type="number" name="price" class="glass-input" value="<?php echo htmlspecialchars($property->price); ?>" required>
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Features & Amenities -->
                        <div class="card-panel glass form-section">
                            <div class="section-badge"><i class="fa-solid fa-list-star"></i></div>
                            <h3 class="section-title">Features & Amenities</h3>
                            <p class="section-desc" style="margin-top: -12px; margin-bottom: 24px; opacity: 0.7;">Highlight the key features that make this listing stand out.</p>
                            
                            <!-- Beds and Baths Chips -->
                            <div class="form-row" id="bedsBathsSection" style="margin-bottom: 24px;">
                                <div class="form-group half" style="flex: 1;">
                                    <label class="form-label">Bedrooms</label>
                                    <div class="chip-group circle-chips" id="bedsChipGroup" style="flex-wrap: wrap; gap: 8px;">
                                        <?php 
                                        $beds_options = ["1", "2", "3", "4", "5", "6", "7", "8", "9", "10+"];
                                        $current_beds = $amenities[3] ?? "3"; // ID 3 is Bedrooms
                                        foreach($beds_options as $opt): 
                                            $active = ($current_beds == $opt) ? 'active' : '';
                                        ?>
                                            <button type="button" class="chip-btn circle <?php echo $active; ?>" data-value="<?php echo $opt; ?>"><?php echo $opt; ?></button>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="form-group half" style="flex: 1;">
                                    <label class="form-label">Bathrooms</label>
                                    <div class="chip-group circle-chips" id="bathsChipGroup" style="flex-wrap: wrap; gap: 8px;">
                                        <?php 
                                        $baths_options = ["1", "2", "3", "4", "5", "6", "7+"];
                                        $current_baths = $amenities[4] ?? "2"; // ID 4 is Bathrooms
                                        foreach($baths_options as $opt): 
                                            $active = ($current_baths == $opt) ? 'active' : '';
                                        ?>
                                            <button type="button" class="chip-btn circle <?php echo $active; ?>" data-value="<?php echo $opt; ?>"><?php echo $opt; ?></button>
                                        <?php endforeach; ?>
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
                                
                                <div class="chip-group wrap-chips" id="selectedAmenitiesDisplay">
                                    <!-- Dynamic Chips -->
                                </div>
                                
                                <input type="hidden" name="property_amenities" id="propertyAmenitiesInput" value='<?php echo json_encode($amenities); ?>'>
                            </div>

                            <!-- Quality Tip -->
                            <div class="quality-tip" style="background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); padding: 16px; border-radius: 16px; display: flex; align-items: flex-start; gap: 12px; margin-top: 24px;">
                                <i class="fa-solid fa-circle-check" style="color: var(--primary); font-size: 24px;"></i>
                                <div class="tip-content">
                                    <div class="tip-title" style="font-weight: 700; font-size: 14px; margin-bottom: 4px;">Quality Tip</div>
                                    <p class="tip-desc" style="font-size: 13px; opacity: 0.7; margin: 0;">Add at least 5 amenities to help your listing stand out.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Section 5: Assign Agent -->
                        <div class="card-panel glass form-section">
                            <div class="section-badge"><i class="fa-solid fa-circle-user-plus"></i></div>
                            <h3 class="section-title">Assign to Agent</h3>
                            <div class="chip-group wrap-chips" id="agentSelectionGroup">
                                <?php
                                $agents = $pdo->prepare("SELECT u.id, u.full_name, u.avatar_url FROM agents a JOIN users u ON a.user_id = u.id WHERE a.agency_id = ?");
                                $agents->execute([$agency_id]);
                                while ($agent = $agents->fetch()) {
                                    $active = ($agent->id == $property->author_id) ? 'active' : '';
                                    $avatar = $agent->avatar_url ? '../' . $agent->avatar_url : 'https://i.pravatar.cc/150?img=' . (20 + $agent->id);
                                    echo "<button type='button' class='chip-btn {$active}' data-value='{$agent->id}'>
                                            <img src='{$avatar}' class='mini-avatar'> {$agent->full_name}
                                          </button>";
                                }
                                ?>
                            </div>
                            <input type="hidden" name="agent_id" id="assignedAgentId" value="<?php echo $property->author_id; ?>">
                        </div>

                        <!-- Description -->
                        <div class="card-panel glass form-section">
                            <div class="section-badge"><i class="fa-solid fa-text-aa"></i></div>
                            <h3 class="section-title">Listing Description</h3>
                            <div class="form-group">
                                <label class="form-label">Property Title</label>
                                <input type="text" name="title" class="glass-input" value="<?php echo htmlspecialchars($property->title); ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="glass-input" rows="6" required><?php echo htmlspecialchars($property->description); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Media -->
                    <div class="form-sidebar-col">
                        <div class="card-panel glass form-section" style="position: sticky; top: 124px;">
                            <div class="section-badge"><i class="fa-solid fa-images"></i></div>
                            <h3 class="section-title">Property Media</h3>
                            
                            <!-- Existing Images Preview -->
                            <div class="existing-images" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 20px;">
                                <?php foreach ($images as $img): ?>
                                    <div class="image-preview-item" data-id="<?php echo $img['id']; ?>" style="position: relative; aspect-ratio: 1; border-radius: 8px; overflow: hidden; border: 1px solid var(--glass-border);">
                                        <img src="../<?php echo $img['image_url']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                        <button type="button" class="remove-existing-img" style="position: absolute; top: 4px; right: 4px; background: rgba(239, 68, 68, 0.8); border: none; color: white; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 12px;"><i class="fa-solid fa-xmark"></i></button>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="dropzone" id="imageDropzone">
                                <div class="dropzone-content">
                                    <i class="fa-solid fa-upload upload-icon"></i>
                                    <p>Upload New Images</p>
                                </div>
                                <input type="file" id="fileInput" multiple accept="image/*" hidden>
                            </div>

                            <button type="button" class="btn-primary w-100" id="updatePropertyBtn" style="margin-top: 24px; padding: 16px;">
                                <i class="fa-solid fa-floppy-disk"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>

    <!-- Hidden input to track removed images -->
    <input type="hidden" name="removed_images" id="removedImagesInput" value="[]" form="editPropertyForm">

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
                                data-target="tab-<?php echo $group['id']; ?>">
                            <?php echo htmlspecialchars($group['name']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <div class="modal-tab-content">
                    <?php foreach ($amenity_groups as $index => $group): ?>
                        <div class="tab-pane <?php echo $index === 0 ? 'active' : ''; ?>" id="tab-<?php echo $group['id']; ?>">
                            <div class="amenities-grid">
                                <?php if (isset($fields_by_group[$group['id']])): ?>
                                    <?php foreach ($fields_by_group[$group['id']] as $f): 
                                        // Skip Bedrooms and Bathrooms as they are handled in the main form
                                        if ($f['id'] == 3 || $f['id'] == 4) continue;
                                    ?>
                                        <div class="amenity-item <?php echo $f['field_type'] === 'switch' ? 'toggle-style' : ''; ?>">
                                            <span class="amenity-name"><?php echo htmlspecialchars($f['label']); ?></span>
                                            
                                            <?php if ($f['field_type'] === 'switch'): ?>
                                                <label class="toggle-switch small">
                                                    <input type="checkbox" class="amenity-checkbox" 
                                                           value="<?php echo htmlspecialchars($f['label']); ?>" 
                                                           data-id="<?php echo $f['id']; ?>"
                                                           <?php echo isset($amenities[$f['id']]) ? 'checked' : ''; ?>>
                                                    <span class="slider"></span>
                                                </label>
                                            <?php elseif ($f['field_type'] === 'number' || $f['field_type'] === 'number_group'): ?>
                                                <input type="number" class="glass-input amenity-input" 
                                                       placeholder="Value..." 
                                                       data-id="<?php echo $f['id']; ?>"
                                                       value="<?php echo htmlspecialchars($amenities[$f['id']] ?? ''); ?>">
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
                <button type="button" class="btn-primary" id="saveAmenitiesBtn">Save Selection</button>
            </div>
        </div>
    </div>

    <script src="../includes/assets/js/script.js"></script>
    <script src="../includes/assets/js/agency/edit-property.js"></script>
    <script src="../includes/assets/js/agency/notif-checker.js"></script>
</body>
</html>

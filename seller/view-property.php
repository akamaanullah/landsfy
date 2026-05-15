<?php 
require_once '../includes/auth_check.php';
require_once '../includes/database/db.php';

if ($_SESSION['role_name'] != 'seller' && $_SESSION['role_name'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

$property_id = $_GET['id'] ?? null;
if (!$property_id) {
    header("Location: my-listings.php");
    exit;
}

try {
    // 1. Fetch Property details with Category, Subtype, and City
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as city_name, cat.name as category_name, sub.name as subtype_name, u.email as author_email
        FROM properties p
        LEFT JOIN cities c ON p.city_id = c.id
        LEFT JOIN property_categories cat ON p.category_id = cat.id
        LEFT JOIN property_subtypes sub ON p.subtype_id = sub.id
        LEFT JOIN users u ON p.author_id = u.id
        WHERE p.id = ? AND p.author_id = ?
    ");
    $stmt->execute([$property_id, $_SESSION['user_id']]);
    $property = $stmt->fetch();

    if (!$property) {
        echo "<div class='main-content'><div class='view-container'><div class='glass card-panel' style='text-align:center;'>Property not found or access denied.</div></div></div>";
        exit;
    }

    // 2. Fetch Images
    $img_stmt = $pdo->prepare("SELECT * FROM property_images WHERE property_id = ? ORDER BY is_main DESC, sort_order ASC");
    $img_stmt->execute([$property_id]);
    $images = $img_stmt->fetchAll();

    // 3. Fetch Amenities
    $amen_stmt = $pdo->prepare("
        SELECT af.id, af.label, af.icon_class, av.value 
        FROM property_amenity_values av
        JOIN amenity_fields af ON av.amenity_field_id = af.id
        WHERE av.property_id = ?
    ");
    $amen_stmt->execute([$property_id]);
    $amenities = $amen_stmt->fetchAll();

    // 4. Fetch Contacts
    $contact_stmt = $pdo->prepare("SELECT * FROM property_contacts WHERE property_id = ?");
    $contact_stmt->execute([$property_id]);
    $contacts = $contact_stmt->fetchAll();

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listing Preview | Landsfy Seller</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <!-- External CSS -->
    <link rel="stylesheet" href="../includes/assets/css/style.css">
    <link rel="stylesheet" href="../includes/assets/css/agency-style.css">
    <style>
        .view-container { padding: 10px 0; }
        .property-detail-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 32px; margin-top: 15px; align-items: start; }
        .hero-gallery { width: 100%; border-radius: 24px; overflow: hidden; background: var(--surface-bg); border: 1px solid var(--glass-border); position: relative; display: flex; flex-direction: column; }
        .bg-blur { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; filter: blur(30px) brightness(0.7); opacity: 0.6; z-index: 0; transform: scale(1.1); }
        .main-image { width: 100%; height: 480px; object-fit: contain; position: relative; z-index: 1; backdrop-filter: blur(5px); }
        .thumbnails-slider { display: flex; overflow-x: auto; gap: 12px; padding: 16px; background: rgba(0,0,0,0.08); scrollbar-width: none; }
        .thumbnails-slider::-webkit-scrollbar { display: none; }
        .thumb-item { flex: 0 0 90px; aspect-ratio: 1; border-radius: 12px; overflow: hidden; border: 2px solid transparent; cursor: pointer; transition: 0.2s; }
        .thumb-item.active { border-color: var(--primary); transform: scale(0.95); }
        .thumb-item img { width: 100%; height: 100%; object-fit: cover; }

        .detail-card { margin-bottom: 24px; padding: 32px !important; border-radius: 24px; }
        .section-title { margin-bottom: 20px !important; font-size: 20px !important; font-weight: 800 !important; color: var(--text-primary); }
        .price-tag { font-size: 32px; font-weight: 800; color: var(--primary); margin-bottom: 8px; font-family: 'Outfit'; }
        .spec-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 24px; }
        .spec-item { background: rgba(107,0,182,0.05); padding: 16px; border-radius: 16px; text-align: center; border: 1px solid rgba(107,0,182,0.1); }
        .spec-value { display: block; font-weight: 800; font-size: 18px; color: var(--text-primary); }
        .spec-label { font-size: 11px; color: var(--text-secondary); text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; }

        .amenities-list { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 10px; }
        .amenity-chip { background: rgba(255,255,255,0.03); padding: 10px 18px; border-radius: 50px; border: 1px solid var(--glass-border); display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 600; color: var(--text-primary); transition: 0.3s; }
        .amenity-chip:hover { background: rgba(107,0,182,0.05); border-color: var(--primary); }
        .amenity-chip i { color: var(--primary); font-size: 18px; }

        @media (max-width: 992px) {
            .property-detail-grid { grid-template-columns: 1fr; }
        }
    </style>
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
                <li class="nav-item active">
                    <a href="my-listings.php"><i class="fa-solid fa-house-chimney"></i> My Listings</a>
                </li>
                <li class="nav-item">
                    <a href="leads.php"><i class="fa-solid fa-users"></i> Buyer Leads</a>
                </li>
                <li class="nav-item">
                    <a href="add-listing.php"><i class="fa-solid fa-circle-plus"></i> Post New</a>
                </li>
                <li class="nav-item">
                    <a href="profile.php"><i class="fa-solid fa-circle-user"></i> Profile</a>
                </li>
                <li class="nav-item logout-nav-item" style="margin-top: auto;">
                    <a href="../logout.php" style="color: #ff4757;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                </li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="header glass">
                <div class="header-left">
                    <div class="page-title">Listing Preview</div>
                    <div class="breadcrumb"><a href="my-listings.php">My Listings</a> / <?php echo htmlspecialchars($property->title); ?></div>
                </div>
                <div class="header-actions">
                    <a href="my-listings.php" class="icon-btn" title="Back">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                    <a href="add-listing.php?id=<?php echo $property->id; ?>" class="btn-primary" style="background: var(--primary); color: white; border: none; gap: 10px;">
                        <i class="fa-solid fa-pencil-bold"></i> Edit Listing
                    </a>
                </div>
            </header>

            <div class="view-container">
                <div class="property-detail-grid">
                    <!-- Left Column: Gallery & Description -->
                    <div class="detail-main">
                        <div class="hero-gallery glass">
                            <?php if (count($images) > 0): ?>
                                <img id="bgBlurImg" src="../<?php echo $images[0]->image_url; ?>" class="bg-blur">
                                <img id="mainDisplayImg" src="../<?php echo $images[0]->image_url; ?>" class="main-image">
                                <div class="thumbnails-slider" style="position: relative; z-index: 2;">
                                    <?php foreach ($images as $index => $img): ?>
                                        <div class="thumb-item <?php echo $index === 0 ? 'active' : ''; ?>" 
                                             onclick="changeMainImage(this, '../<?php echo $img->image_url; ?>')">
                                            <img src="../<?php echo $img->image_url; ?>">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div style="aspect-ratio: 16/9; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.05);">
                                    <i class="fa-solid fa-image-square" style="font-size: 64px; color: var(--text-secondary); opacity: 0.2;"></i>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="card-panel glass detail-card" style="margin-top: 24px;">
                            <h3 class="section-title">Description</h3>
                            <div style="line-height: 1.8; color: var(--text-secondary); font-size: 15px; white-space: pre-line;">
                                <?php echo !empty($property->description) ? htmlspecialchars($property->description) : "No description provided for this listing."; ?>
                            </div>
                        </div>

                        <div class="card-panel glass detail-card">
                            <h3 class="section-title">Amenities & Features</h3>
                            <div class="amenities-list">
                                <?php 
                                $has_amenities = false;
                                foreach ($amenities as $am): 
                                    if (in_array($am->id, [3, 4])) continue; // Skip beds/baths here as they are in specs
                                    $has_amenities = true;
                                    $display_label = htmlspecialchars($am->label);
                                    if ($am->value && $am->value !== '1' && $am->value !== 'on') {
                                        $display_label .= ': ' . htmlspecialchars($am->value);
                                    }
                                ?>
                                    <div class="amenity-chip">
                                        <i class="fa-solid <?php echo htmlspecialchars($am->icon_class ?: 'fa-circle-check'); ?>"></i>
                                        <?php echo $display_label; ?>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (!$has_amenities): ?>
                                    <p style="color: var(--text-secondary)">No specific amenities listed.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Info & Stats -->
                    <div class="detail-sidebar">
                        <div class="card-panel glass detail-card">
                            <div class="status-badge" style="display: inline-block; padding: 6px 14px; border-radius: 50px; background: rgba(107,0,182,0.1); color: var(--primary); font-size: 11px; font-weight: 800; text-transform: uppercase; margin-bottom: 20px; letter-spacing: 0.5px;">
                                <?php echo str_replace('_', ' ', $property->status); ?>
                            </div>
                            <div class="price-tag">PKR <?php echo number_format($property->price); ?></div>
                            <h2 style="font-size: 24px; font-weight: 800; color: var(--text-primary); margin-bottom: 12px; font-family: 'Outfit';"><?php echo htmlspecialchars($property->title); ?></h2>
                            <div style="color: var(--text-secondary); font-size: 15px; display: flex; align-items: center; gap: 8px;">
                                <i class="fa-solid fa-map-pin" style="color: var(--primary);"></i> 
                                <?php echo ($property->location_name ? htmlspecialchars($property->location_name) . ', ' : '') . htmlspecialchars($property->city_name); ?>
                            </div>
                            
                            <div class="spec-grid">
                                <div class="spec-item">
                                    <span class="spec-value"><?php echo htmlspecialchars($property->area_size); ?></span>
                                    <span class="spec-label"><?php echo strtoupper($property->area_unit); ?></span>
                                </div>
                                <?php 
                                $beds = 0; $baths = 0;
                                foreach($amenities as $am) {
                                    if($am->id == 3) $beds = $am->value;
                                    if($am->id == 4) $baths = $am->value;
                                }
                                ?>
                                <?php if($beds): ?>
                                <div class="spec-item">
                                    <span class="spec-value"><?php echo $beds; ?></span>
                                    <span class="spec-label">Beds</span>
                                </div>
                                <?php endif; ?>
                                <?php if($baths): ?>
                                <div class="spec-item">
                                    <span class="spec-value"><?php echo $baths; ?></span>
                                    <span class="spec-label">Baths</span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="card-panel glass detail-card">
                            <h3 class="section-title" style="font-size: 18px;">Contact Leads</h3>
                            <div style="background: rgba(107,0,182,0.05); padding: 20px; border-radius: 16px; text-align: center; margin-bottom: 24px;">
                                <div style="font-size: 32px; font-weight: 800; color: var(--primary);"><?php echo $property->total_clicks ?: 0; ?></div>
                                <div style="font-size: 12px; color: var(--text-secondary); text-transform: uppercase; font-weight: 700;">Total Inquiries</div>
                            </div>
                            <ul style="list-style: none; padding: 0;">
                                <li style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--glass-border);">
                                    <span style="color: var(--text-secondary); font-size: 14px;">Contact Email</span>
                                    <span style="font-weight: 700; color: var(--text-primary); font-size: 14px;"><?php echo htmlspecialchars($property->contact_email ?: $property->author_email); ?></span>
                                </li>
                                <?php foreach($contacts as $c): ?>
                                <li style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--glass-border);">
                                    <span style="color: var(--text-secondary); font-size: 14px;"><?php echo htmlspecialchars($c->label); ?></span>
                                    <span style="font-weight: 700; color: var(--text-primary); font-size: 14px;"><?php echo htmlspecialchars($c->phone_number); ?></span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div class="card-panel glass detail-card">
                            <h3 class="section-title" style="font-size: 18px;">Listing Details</h3>
                            <ul style="list-style: none; padding: 0;">
                                <li style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--glass-border);">
                                    <span style="color: var(--text-secondary); font-size: 14px;">Posted On</span>
                                    <span style="font-weight: 700; font-size: 14px;"><?php echo date('M d, Y', strtotime($property->created_at)); ?></span>
                                </li>
                                <li style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--glass-border);">
                                    <span style="color: var(--text-secondary); font-size: 14px;">Type</span>
                                    <span style="font-weight: 700; font-size: 14px;"><?php echo htmlspecialchars($property->category_name); ?></span>
                                </li>
                                <li style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--glass-border);">
                                    <span style="color: var(--text-secondary); font-size: 14px;">Purpose</span>
                                    <span style="font-weight: 700; font-size: 14px; text-transform: capitalize;"><?php echo htmlspecialchars($property->purpose); ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
    function changeMainImage(thumb, url) {
        document.getElementById('mainDisplayImg').src = url;
        document.getElementById('bgBlurImg').src = url;
        document.querySelectorAll('.thumb-item').forEach(t => t.classList.remove('active'));
        thumb.classList.add('active');
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../includes/assets/js/script.js"></script>
    <script src="../includes/assets/js/seller/notif-checker.js"></script>
</body>
</html>

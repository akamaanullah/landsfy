<?php 
include 'header.php';

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

<style>
    .view-container { padding: 10px 0; }
    .property-detail-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-top: 15px; align-items: start; }
    .hero-gallery { width: 100%; border-radius: 20px; overflow: hidden; background: var(--surface-bg); border: 1px solid var(--border-color); position: relative; display: flex; flex-direction: column; }
    .bg-blur { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; filter: blur(30px) brightness(0.7); opacity: 0.6; z-index: 0; transform: scale(1.1); }
    .main-image { width: 100%; height: 450px; object-fit: contain; position: relative; z-index: 1; backdrop-filter: blur(5px); }
    .thumbnails-slider { display: flex; overflow-x: auto; gap: 10px; padding: 12px; background: rgba(0,0,0,0.08); scrollbar-width: none; }
    .thumbnails-slider::-webkit-scrollbar { display: none; }
    .thumb-item { flex: 0 0 80px; aspect-ratio: 1; border-radius: 10px; overflow: hidden; border: 2px solid transparent; cursor: pointer; transition: 0.2s; }
    .thumb-item.active { border-color: var(--primary); }
    .thumb-item img { width: 100%; height: 100%; object-fit: cover; }

    .detail-card { margin-bottom: 20px; padding: 24px !important; }
    .section-title { margin-bottom: 15px !important; padding-left: 0 !important; }
    .price-tag { font-size: 28px; font-weight: 800; color: var(--primary); margin-bottom: 5px; }
    .spec-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-top: 15px; }
    .spec-item { background: rgba(107,0,182,0.05); padding: 12px; border-radius: 12px; text-align: center; }
    .spec-value { display: block; font-weight: 700; font-size: 16px; color: var(--text-main); }
    .spec-label { font-size: 10px; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px; }

    .amenities-list { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; }
    .amenity-chip { background: rgba(0,0,0,0.02); padding: 8px 14px; border-radius: 50px; border: 1px solid var(--border-color); display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 500; }
    .amenity-chip i { color: var(--primary); font-size: 16px; }

    @media (max-width: 992px) {
        .property-detail-grid { grid-template-columns: 1fr; }
    }
</style>

<main class="main-content">
    <header class="header glass">
        <div class="header-left">
            <div class="page-title">Listing Preview</div>
            <div class="breadcrumb"><a href="my-listings.php">My Listings</a> / <?php echo htmlspecialchars($property->title); ?></div>
        </div>
        <div class="header-actions">
            <a href="my-listings.php" class="btn-primary" style="background: rgba(0,0,0,0.05); color: var(--text-main); border: 1px solid var(--border-color);">
                <i class="fa-solid fa-arrow-left"></i> Back to List
            </a>
            <a href="add-property.php?id=<?php echo $property->id; ?>" class="btn-primary" style="background: var(--primary); color: white; border: none;">
                <i class="fa-solid fa-pencil"></i> Edit Listing
            </a>
        </div>
    </header>

    <div class="view-container">
        <div class="property-detail-grid">
            <!-- Left Column: Gallery & Description -->
            <div class="detail-main">
                <div class="hero-gallery glass">
                    <?php if (count($images) > 0): ?>
                        <img id="bgBlurImg" src="<?php echo getImageUrl($images[0]->image_url); ?>" class="bg-blur" onerror="handleImageError(this, 'property')">
                        <img id="mainDisplayImg" src="<?php echo getImageUrl($images[0]->image_url); ?>" class="main-image" onerror="handleImageError(this, 'property')">
                        <div class="thumbnails-slider" style="position: relative; z-index: 2;">
                            <?php foreach ($images as $index => $img): ?>
                                <div class="thumb-item <?php echo $index === 0 ? 'active' : ''; ?>" 
                                     onclick="changeMainImage(this, '<?php echo getImageUrl($img->image_url); ?>')">
                                    <img src="<?php echo getImageUrl($img->image_url); ?>" onerror="handleImageError(this, 'property')">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div style="aspect-ratio: 16/9; display: flex; align-items: center; justify-content: center; background: #eee;">
                            <i class="fa-solid fa-image-square" style="font-size: 64px; color: #ccc;"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card-panel glass detail-card" style="margin-top: 24px;">
                    <h3 class="section-title">Description</h3>
                    <div style="line-height: 1.8; color: var(--text-secondary); white-space: pre-line;">
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
                            if ($am->value && $am->value !== '1') {
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
                    <div class="status-badge" style="display: inline-block; padding: 4px 12px; border-radius: 50px; background: rgba(107,0,182,0.1); color: var(--primary); font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 15px;">
                        <?php echo strtoupper(str_replace('_', ' ', $property->status)); ?>
                    </div>
                    <div class="price-tag">PKR <?php echo number_format($property->price); ?></div>
                    <h2 style="font-size: 20px; font-weight: 700; margin-bottom: 8px;"><?php echo htmlspecialchars($property->title); ?></h2>
                    <div style="color: var(--text-secondary); font-size: 14px;">
                        <i class="fa-solid fa-location-dot"></i> 
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
                    <h3 class="section-title" style="font-size: 16px;">Contact Information</h3>
                    <ul style="list-style: none; padding: 0; margin-top: 15px;">
                        <li style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border-color);">
                            <span style="color: var(--text-secondary)">Email</span>
                            <span style="font-weight: 600; color: var(--primary);"><?php echo htmlspecialchars($property->contact_email ?: $property->author_email); ?></span>
                        </li>
                        <?php foreach($contacts as $c): ?>
                        <li style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border-color);">
                            <span style="color: var(--text-secondary)"><?php echo htmlspecialchars($c->label); ?></span>
                            <span style="font-weight: 600; color: var(--primary);"><?php echo htmlspecialchars($c->phone_number); ?></span>
                        </li>
                        <?php endforeach; ?>
                        <?php if(empty($contacts)): ?>
                            <li style="color: var(--text-secondary)">No contact details found.</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="card-panel glass detail-card">
                    <h3 class="section-title" style="font-size: 16px;">Listing Information</h3>
                    <ul style="list-style: none; padding: 0; margin-top: 15px;">
                        <li style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border-color);">
                            <span style="color: var(--text-secondary)">Posted On</span>
                            <span style="font-weight: 600;"><?php echo date('M d, Y', strtotime($property->created_at)); ?></span>
                        </li>
                        <li style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border-color);">
                            <span style="color: var(--text-secondary)">Property Type</span>
                            <span style="font-weight: 600;"><?php echo htmlspecialchars($property->category_name); ?></span>
                        </li>
                        <li style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border-color);">
                            <span style="color: var(--text-secondary)">Subtype</span>
                            <span style="font-weight: 600;"><?php echo htmlspecialchars($property->subtype_name); ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function changeMainImage(thumb, url) {
    document.getElementById('mainDisplayImg').src = url;
    document.getElementById('bgBlurImg').src = url;
    document.querySelectorAll('.thumb-item').forEach(t => t.classList.remove('active'));
    thumb.classList.add('active');
}
</script>

    </div> <!-- Close app-layout -->
    <script src="../includes/assets/js/script.js"></script>
</body>
</html>

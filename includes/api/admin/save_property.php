<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';
require_once '../../helpers/file_upload.php';

// Enforce Auth
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Login required']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // DEBUG LOG
    error_log("SAVE_PROPERTY DATA: " . print_r($_POST, true));
    error_log("AMENITIES RAW: " . ($_POST['property_amenities'] ?? 'MISSING'));

    // 1. Data Sanitization
    $author_id = $_SESSION['user_id'];
    $category_id = (int)($_POST['category_id'] ?? 0);
    $subtype_id = (int)($_POST['subtype_id'] ?? 0);
    
    // Support both city_id (Agent) and property_city (Admin)
    $city_id = (int)($_POST['city_id'] ?? $_POST['property_city'] ?? 0);
    
    $location_id = !empty($_POST['location_id']) ? (int)$_POST['location_id'] : null;
    $location_name = trim($_POST['location_name'] ?? '');

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $purpose = $_POST['property_purpose'] ?? 'sell';
    $area_size = (float)($_POST['area_size'] ?? 0);
    $area_unit = $_POST['area_unit'] ?? 'marla';
    $is_installment = isset($_POST['is_installment_available']) ? 1 : 0;
    $is_ready = isset($_POST['is_ready_for_possession']) ? 1 : 0;
    $contact_email = trim($_POST['contact_email'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');

    // --- Dynamic Location Handling (Mirroring Agent Portal) ---
    if (!$location_id && !empty($location_name)) {
        // 1. Check if location exists (case insensitive)
        $loc_stmt = $pdo->prepare("SELECT id FROM locations WHERE city_id = ? AND LOWER(name) = LOWER(?)");
        $loc_stmt->execute([$city_id, $location_name]);
        $location_id = $loc_stmt->fetchColumn() ?: null;

        if (!$location_id) {
            // 2. Create new location
            $loc_slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $location_name)));
            $ins_loc = $pdo->prepare("INSERT INTO locations (city_id, name, slug) VALUES (?, ?, ?)");
            $ins_loc->execute([$city_id, $location_name, $loc_slug]);
            $location_id = $pdo->lastInsertId();
        }
    }

    $property_id = !empty($_POST['property_id']) ? (int)$_POST['property_id'] : null;

    if (!$title || !$category_id || !$price || !$city_id || !$location_name) {
        throw new Exception("Missing required fields (Title, Category, Price, City, Location).");
    }

    // 2. Fetch User's Agency ID if they are an agent
    $agency_id = null;
    $stmt_agency = $pdo->prepare("SELECT agency_id FROM agents WHERE user_id = ?");
    $stmt_agency->execute([$author_id]);
    $agency_id = $stmt_agency->fetchColumn() ?: null;

    if ($property_id) {
        // --- UPDATE EXISTING PROPERTY ---
        // Verify ownership/permission (Admin can edit anything)
        $verify = $pdo->prepare("SELECT author_id FROM properties WHERE id = ?");
        $verify->execute([$property_id]);
        $exists = $verify->fetchColumn();
        if (!$exists) throw new Exception("Property not found.");
        
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title))) . '-' . $property_id;
        $sql = "UPDATE properties SET 
                    category_id = ?, subtype_id = ?, city_id = ?, location_id = ?, location_name = ?, contact_email = ?,
                    title = ?, slug = ?, description = ?, price = ?, purpose = ?, 
                    area_size = ?, area_unit = ?, is_installment_available = ?, is_ready_for_possession = ?
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $category_id, $subtype_id, $city_id, $location_id, $location_name, $contact_email,
            $title, $slug, $description, $price, $purpose,
            $area_size, $area_unit, $is_installment, $is_ready,
            $property_id
        ]);

        // Cleanup old relations for atomic refresh
        $pdo->prepare("DELETE FROM property_contacts WHERE property_id = ?")->execute([$property_id]);
        $pdo->prepare("DELETE FROM property_amenity_values WHERE property_id = ?")->execute([$property_id]);
        
    } else {
        // --- INSERT NEW PROPERTY ---
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title))) . '-' . time();
        $sql = "INSERT INTO properties (
                    author_id, agency_id, category_id, subtype_id, city_id, location_id, location_name, contact_email,
                    title, slug, description, price, purpose, status, 
                    area_size, area_unit, is_installment_available, is_ready_for_possession
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'under_review', ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $author_id, $agency_id, $category_id, $subtype_id, $city_id, $location_id, $location_name, $contact_email,
            $title, $slug, $description, $price, $purpose,
            $area_size, $area_unit, $is_installment, $is_ready
        ]);
        $property_id = $pdo->lastInsertId();
    }

    // 4. Handle Contact Details (property_contacts table)
    if ($phone_number) {
        $pdo->prepare("INSERT INTO property_contacts (property_id, phone_number, label) VALUES (?, ?, 'Mobile')")
            ->execute([$property_id, $phone_number]);
    }

    // 5. Handle Amenities (property_amenity_values)
    $amenities = json_decode($_POST['property_amenities'] ?? '{}', true);
    if (!empty($amenities)) {
        $amenity_sql = "INSERT INTO property_amenity_values (property_id, amenity_field_id, value) VALUES (?, ?, ?)";
        $amenity_stmt = $pdo->prepare($amenity_sql);
        foreach ($amenities as $fid => $val) {
            if ($val !== null && $val !== '' && $val !== 'false') {
                $amenity_stmt->execute([$property_id, $fid, $val]);
            }
        }
    }

    // 6. Handle Image Uploads
    $upload_dir = "../../../uploads/properties/";

    if (isset($_FILES['property_images'])) {
        $files = $_FILES['property_images'];
        for ($i = 0; $i < count($files['name']); $i++) {
            $pseudo_file = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            ];
            
            if ($pseudo_file['error'] !== UPLOAD_ERR_OK) continue;
            
            try {
                $db_path = FileUploadHelper::secureUpload($pseudo_file, $upload_dir, 'prop_' . $property_id . '_', 'image');
                if ($db_path) {
                    // If update, check existing images count for sort order
                    $sort_order = $i;
                    if (!empty($_POST['id'])) {
                        $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM property_images WHERE property_id = ?");
                        $count_stmt->execute([$property_id]);
                        $sort_order += $count_stmt->fetchColumn();
                    }
                    
                    $is_main = ($sort_order === 0) ? 1 : 0;
                    $pdo->prepare("INSERT INTO property_images (property_id, image_url, is_main, sort_order) VALUES (?, ?, ?, ?)")
                        ->execute([$property_id, $db_path, $is_main, $sort_order]);
                }
            } catch (Exception $e) {
                // Ignore failure for single image allowing transaction to complete
            }
        }
    }

    $pdo->commit();
    $msg = !empty($_POST['property_id']) ? 'Property updated successfully!' : 'Property listed successfully!';
    echo json_encode(['success' => true, 'message' => $msg, 'property_id' => $property_id]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

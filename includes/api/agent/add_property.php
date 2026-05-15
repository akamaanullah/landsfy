<?php
require_once '../../auth_check.php';
require_once '../../database/db.php';
require_once '../../helpers/file_upload.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    // 1. Validate Basic Data
    $title = $_POST['property_title'] ?? '';
    $price = $_POST['property_price'] ?? 0;
    
    if (empty($title) || empty($price)) {
        throw new Exception("Title and Price are mandatory.");
    }

    $pdo->beginTransaction();

    // 2. Prepare Property Data
    $author_id = $_SESSION['user_id'];
    
    // Fetch agency_id for this agent
    $agent_stmt = $pdo->prepare("SELECT agency_id FROM agents WHERE user_id = ?");
    $agent_stmt->execute([$author_id]);
    $agent = $agent_stmt->fetch(PDO::FETCH_ASSOC);
    $agency_id = $agent ? $agent['agency_id'] : null;

    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title))) . '-' . time();
    $category_id = $_POST['category_id'] ?? 1;
    $subtype_id = $_POST['subtype_id'] ?? 1;
    $city_id = $_POST['city_id'] ?? 1; // Corrected from property_city to city_id
    $location_name = $_POST['location_name'] ?? '';
    $description = $_POST['property_description'] ?? '';
    $purpose = $_POST['property_purpose'] ?? 'sell';
    $area_size = $_POST['area_size'] ?? 0;
    $area_unit = $_POST['area_unit'] ?? 'marla';
    $installment = isset($_POST['is_installment_available']) ? 1 : 0;
    $possession = isset($_POST['is_ready_for_possession']) ? 1 : 0;
    $contact_email = $_POST['property_email'] ?? '';
    
    // Premium Data
    $premium_type = $_POST['premium_type'] ?? 'none';
    $premium_status = ($premium_type !== 'none') ? 'pending' : 'none';

    // --- Dynamic Location Handling ---
    $location_id = null;
    if (!empty($location_name)) {
        // 1. Check if location already exists for this city
        $loc_stmt = $pdo->prepare("SELECT id FROM locations WHERE city_id = ? AND LOWER(name) = LOWER(?)");
        $loc_stmt->execute([$city_id, trim($location_name)]);
        $location_id = $loc_stmt->fetchColumn();

        if (!$location_id) {
            // 2. Create new location if it doesn't exist
            $loc_slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $location_name)));
            $ins_loc = $pdo->prepare("INSERT INTO locations (city_id, name, slug) VALUES (?, ?, ?)");
            $ins_loc->execute([$city_id, trim($location_name), $loc_slug]);
            $location_id = $pdo->lastInsertId();
        }
    }

    require_once '../../helpers/quota_helper.php';
    $edit_property_id = $_POST['property_id'] ?? null;
    $should_deduct = false;

    if ($premium_type !== 'none') {
        if ($edit_property_id) {
            // Check if it was already premium
            $check_stmt = $pdo->prepare("SELECT premium_type FROM properties WHERE id = ?");
            $check_stmt->execute([$edit_property_id]);
            $old_type = $check_stmt->fetchColumn();
            if ($old_type === 'none') $should_deduct = true;
        } else {
            $should_deduct = true;
        }

        if ($should_deduct && !hasSufficientQuota($pdo, $author_id, $premium_type)) {
            throw new Exception("Insufficient {$premium_type} quota. Please purchase more credits.");
        }
    }

    if ($edit_property_id) {
        // Verify ownership before update
        $verify_stmt = $pdo->prepare("SELECT id, premium_type, premium_status FROM properties WHERE id = ? AND author_id = ?");
        $verify_stmt->execute([$edit_property_id, $author_id]);
        $existing = $verify_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$existing) {
            throw new Exception("Unauthorized to edit this property.");
        }

        // If tier changed, reset premium_status to pending
        if ($existing['premium_type'] !== $premium_type && $premium_type !== 'none') {
            $premium_status = 'pending';
        } else {
            $premium_status = $existing['premium_status'];
        }

        // 3. Update Existing Property
        $sql = "UPDATE properties SET
                    category_id = ?, subtype_id = ?, city_id = ?, location_id = ?, location_name = ?, contact_email = ?,
                    title = ?, slug = ?, description = ?, price = ?, purpose = ?, 
                    area_size = ?, area_unit = ?, is_installment_available = ?, is_ready_for_possession = ?,
                    premium_type = ?, premium_status = ?
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $category_id, $subtype_id, $city_id, $location_id, $location_name, $contact_email,
            $title, $slug, $description, $price, $purpose,
            $area_size, $area_unit, $installment, $possession,
            $premium_type, $premium_status,
            $edit_property_id
        ]);
        
        $property_id = $edit_property_id;
    } else {
        // 3. Insert New Property
        $sql = "INSERT INTO properties (
                    author_id, agency_id, category_id, subtype_id, city_id, location_id, location_name, contact_email,
                    title, slug, description, price, purpose, 
                    area_size, area_unit, is_installment_available, is_ready_for_possession, status,
                    premium_type, premium_status
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, 
                    ?, ?, ?, ?, 'under_review',
                    ?, ?
                )";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $author_id, $agency_id, $category_id, $subtype_id, $city_id, $location_id, $location_name, $contact_email,
            $title, $slug, $description, $price, $purpose,
            $area_size, $area_unit, $installment, $possession,
            $premium_type, $premium_status
        ]);
        
        $property_id = $pdo->lastInsertId();
    }

    // Deduct Quota if needed
    if ($should_deduct) {
        deductAgentQuota($pdo, $author_id, $premium_type);
        // Log it
        $pdo->prepare("INSERT INTO activity_logs (user_id, action_type, description, target_id) VALUES (?, ?, ?, ?)")
            ->execute([$author_id, 'quota_deducted', "1 {$premium_type} credit deducted for property ID: {$property_id}", $property_id]);
    }
    
    // 4a. Handle Deleted Images
    if ($edit_property_id && isset($_POST['deleted_images'])) {
        $deleted_ids = json_decode($_POST['deleted_images'], true);
        if (is_array($deleted_ids) && count($deleted_ids) > 0) {
            foreach ($deleted_ids as $img_id) {
                // Get path to delete from disk
                $path_stmt = $pdo->prepare("SELECT image_url FROM property_images WHERE id = ? AND property_id = ?");
                $path_stmt->execute([$img_id, $edit_property_id]);
                $img_path = $path_stmt->fetchColumn();
                
                if ($img_path) {
                    $full_path = '../../../' . $img_path;
                    if (file_exists($full_path)) @unlink($full_path);
                    
                    $pdo->prepare("DELETE FROM property_images WHERE id = ?")->execute([$img_id]);
                }
            }
        }
    }

    // 4. Handle New Images
    $upload_dir = '../../../uploads/properties/';

    if (isset($_FILES['property_images'])) {
        foreach ($_FILES['property_images']['tmp_name'] as $key => $tmp_name) {
            // Reconstruct array for the helper
            $pseudo_file = [
                'name' => $_FILES['property_images']['name'][$key],
                'type' => $_FILES['property_images']['type'][$key],
                'tmp_name' => $tmp_name,
                'error' => $_FILES['property_images']['error'][$key],
                'size' => $_FILES['property_images']['size'][$key]
            ];
            
            if ($pseudo_file['error'] !== UPLOAD_ERR_OK) continue;

            try {
                $db_path = FileUploadHelper::secureUpload($pseudo_file, $upload_dir, 'prop_' . $property_id . '_', 'image');
                if ($db_path) {
                    $img_sql = "INSERT INTO property_images (property_id, image_url, is_main, sort_order) VALUES (?, ?, ?, ?)";
                    $img_stmt = $pdo->prepare($img_sql);
                    $img_stmt->execute([$property_id, $db_path, ($key === 0 ? 1 : 0), $key]);
                }
            } catch (Exception $e) {
                // Log or ignore single failed image
            }
        }
    }

    // 5. Handle Video
    if (isset($_FILES['property_video']) && $_FILES['property_video']['error'] === UPLOAD_ERR_OK) {
        $video_dir = '../../../uploads/videos/';
        try {
            $db_path = FileUploadHelper::secureUpload($_FILES['property_video'], $video_dir, 'vid_' . $property_id . '_', 'video');
            if ($db_path) {
                $upd_v = $pdo->prepare("UPDATE properties SET video_url = ? WHERE id = ?");
                $upd_v->execute([$db_path, $property_id]);
            }
        } catch (Exception $e) {
            // Ignore video failure
        }
    }

    // 6. Handle Amenities
    if ($edit_property_id) {
        $pdo->prepare("DELETE FROM property_amenity_values WHERE property_id = ?")->execute([$property_id]);
    }

    // Save explicit Bedrooms/Bathrooms
    if (isset($_POST['bedrooms'])) {
        $pdo->prepare("INSERT INTO property_amenity_values (property_id, amenity_field_id, value) VALUES (?, ?, ?)")
            ->execute([$property_id, 3, $_POST['bedrooms']]);
    }
    if (isset($_POST['bathrooms'])) {
        $pdo->prepare("INSERT INTO property_amenity_values (property_id, amenity_field_id, value) VALUES (?, ?, ?)")
            ->execute([$property_id, 4, $_POST['bathrooms']]);
    }

    if (isset($_POST['property_amenities'])) {
        $amenities = json_decode($_POST['property_amenities'], true);
        if (is_array($amenities)) {
            foreach ($amenities as $am_data) {
                if (isset($am_data['id'])) {
                    $am_id = $am_data['id'];
                    $am_val = $am_data['value'] ?? '1';
                    
                    if ($am_id && !in_array($am_id, [3, 4])) {
                        $val_sql = "INSERT INTO property_amenity_values (property_id, amenity_field_id, value) VALUES (?, ?, ?)";
                        $pdo->prepare($val_sql)->execute([$property_id, $am_id, $am_val]);
                    }
                }
            }
        }
    }

    // 7. Handle Contacts
    if ($edit_property_id) {
        $pdo->prepare("DELETE FROM property_contacts WHERE property_id = ?")->execute([$property_id]);
    }

    if (isset($_POST['property_contacts']) && is_array($_POST['property_contacts'])) {
        $contact_sql = "INSERT INTO property_contacts (property_id, phone_number, label) VALUES (?, ?, ?)";
        $contact_stmt = $pdo->prepare($contact_sql);
        foreach ($_POST['property_contacts'] as $index => $phone) {
            if (!empty(trim($phone))) {
                $label = ($index === 0) ? 'Primary' : 'Secondary';
                $contact_stmt->execute([$property_id, trim($phone), $label]);
            }
        }
    }

    $pdo->commit();
    $msg = $edit_property_id ? 'Property updated successfully!' : 'Property listed successfully!';
    echo json_encode(['success' => true, 'message' => $msg, 'id' => $property_id]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

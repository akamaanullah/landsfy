<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

// Allow Agency Owners, Admins, and Agents
if (!in_array($_SESSION['role_name'], ['agency_owner', 'admin', 'agent'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role_name'];

try {
    $agency_id = null;
    $owner_id = null;

    if ($role === 'agency_owner') {
        $stmt = $pdo->prepare("SELECT id, owner_id FROM agencies WHERE owner_id = ?");
        $stmt->execute([$user_id]);
        $agency = $stmt->fetch();
        if ($agency) {
            $agency_id = $agency->id;
            $owner_id = $agency->owner_id;
        }
    } elseif ($role === 'agent') {
        $stmt = $pdo->prepare("
            SELECT a.agency_id, ag.owner_id 
            FROM agents a 
            JOIN agencies ag ON a.agency_id = ag.id 
            WHERE a.user_id = ?
        ");
        $stmt->execute([$user_id]);
        $res = $stmt->fetch();
        if ($res) {
            $agency_id = $res->agency_id;
            $owner_id = $res->owner_id;
        }
    }

    if (!$agency_id) {
        throw new Exception("Agency association not found.");
    }

    // 2. Extract Data
    $property_id = $_POST['id'] ?? null;
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $location_name = $_POST['location_name'] ?? '';
    $city_id = $_POST['city_id'] ?? null;
    $purpose = $_POST['purpose'] ?? 'sell';
    $area_size = $_POST['area_size'] ?? 0;
    $area_unit = $_POST['area_unit'] ?? 'sqyrd';
    $category_id = $_POST['category_id'] ?? null;
    $subtype_id = $_POST['subtype_id'] ?? null;
    $assigned_agent_id = $_POST['agent_id'] ?? null;
    
    $removed_images = json_decode($_POST['removed_images'] ?? '[]', true);
    $amenities = json_decode($_POST['property_amenities'] ?? '[]', true);

    if (!$property_id) {
        throw new Exception("Property ID is missing.");
    }

    // 3. Verify Ownership & Authorization
    // Property must belong to the agency.
    $check_stmt = $pdo->prepare("SELECT id, title FROM properties WHERE id = ? AND agency_id = ?");
    $check_stmt->execute([$property_id, $agency_id]);
    $prop_data = $check_stmt->fetch();
    if (!$prop_data) {
        throw new Exception("Unauthorized access to this property.");
    }

    $pdo->beginTransaction();

    // 4. Update Core Property Data
    $update_stmt = $pdo->prepare("
        UPDATE properties 
        SET title = ?, description = ?, price = ?, location_name = ?, city_id = ?, 
            purpose = ?, area_size = ?, area_unit = ?, category_id = ?, subtype_id = ?, 
            author_id = ?, updated_at = NOW()
        WHERE id = ?
    ");
    
    // For agents, author_id remains the agent. For owners, it can be assigned.
    $target_author_id = ($role === 'agent') ? $user_id : ($assigned_agent_id ?: $user_id);

    $update_stmt->execute([
        $title, $description, $price, $location_name, $city_id,
        $purpose, $area_size, $area_unit, $category_id, $subtype_id,
        $target_author_id, $property_id
    ]);

    // 5. Handle Removed Images
    if (!empty($removed_images)) {
        foreach ($removed_images as $img_id) {
            $img_path_stmt = $pdo->prepare("SELECT image_url FROM property_images WHERE id = ?");
            $img_path_stmt->execute([$img_id]);
            $img_path = $img_path_stmt->fetchColumn();
            
            if ($img_path && file_exists('../../../' . $img_path)) {
                @unlink('../../../' . $img_path);
            }
            
            $del_img_stmt = $pdo->prepare("DELETE FROM property_images WHERE id = ?");
            $del_img_stmt->execute([$img_id]);
        }
    }

    // 6. Handle New Images
    if (!empty($_FILES['images'])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $file_name = time() . '_' . $_FILES['images']['name'][$key];
                $upload_path = 'uploads/properties/' . $file_name;
                
                if (move_uploaded_file($tmp_name, '../../../' . $upload_path)) {
                    $img_ins_stmt = $pdo->prepare("INSERT INTO property_images (property_id, image_url) VALUES (?, ?)");
                    $img_ins_stmt->execute([$property_id, $upload_path]);
                }
            }
        }
    }

    // 7. Sync Amenities
    $del_amen_stmt = $pdo->prepare("DELETE FROM property_amenity_values WHERE property_id = ?");
    $del_amen_stmt->execute([$property_id]);

    if (!empty($amenities)) {
        $ins_amen_stmt = $pdo->prepare("INSERT INTO property_amenity_values (property_id, amenity_field_id, value) VALUES (?, ?, ?)");
        foreach ($amenities as $field_id => $value) {
            if ($value !== '' && $value !== null) {
                $ins_amen_stmt->execute([$property_id, $field_id, $value]);
            }
        }
    }

    // 8. Create Notification for Owner (if an agent updated it)
    if ($role === 'agent' && $owner_id) {
        $notif_stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, sender_id, title, message, type, reference_id, reference_type)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $msg = "Agent " . $_SESSION['full_name'] . " updated property: " . $title;
        $notif_stmt->execute([
            $owner_id, 
            $user_id, 
            "Property Updated", 
            $msg, 
            "property_updated", 
            $property_id, 
            "property"
        ]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Property updated successfully']);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

$property_id = $_GET['id'] ?? null;

if (!$property_id) {
    echo json_encode(['success' => false, 'message' => 'Property ID required']);
    exit;
}

try {
    // 1. Fetch Main Property Details with Joins
    $sql = "SELECT p.*, 
                   c.name as city_name, 
                   l.name as location_name_ref,
                   cat.name as category_name,
                   sub.name as subtype_name,
                   u.full_name as author_name,
                   u.email as author_email,
                   u.avatar_url as author_avatar,
                   a.name as agency_name,
                   ps.views_total,
                   ps.leads_total
            FROM properties p
            LEFT JOIN cities c ON p.city_id = c.id
            LEFT JOIN locations l ON p.location_id = l.id
            LEFT JOIN property_categories cat ON p.category_id = cat.id
            LEFT JOIN property_subtypes sub ON p.subtype_id = sub.id
            LEFT JOIN users u ON p.author_id = u.id
            LEFT JOIN agencies a ON p.agency_id = a.id
            LEFT JOIN property_stats ps ON p.id = ps.property_id
            WHERE p.id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$property_id]);
    $property = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$property) {
        throw new Exception("Property not found");
    }

    // 2. Fetch Images
    $img_stmt = $pdo->prepare("SELECT id, image_url, is_main, sort_order FROM property_images WHERE property_id = ? ORDER BY sort_order ASC");
    $img_stmt->execute([$property_id]);
    $images = $img_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Fetch Contacts
    $contact_stmt = $pdo->prepare("SELECT id, phone_number, label FROM property_contacts WHERE property_id = ?");
    $contact_stmt->execute([$property_id]);
    $contacts = $contact_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Fetch Amenities with Field Labels
    $amenity_sql = "SELECT v.value, f.id, f.label, f.icon_class, f.field_type
                    FROM property_amenity_values v
                    JOIN amenity_fields f ON v.amenity_field_id = f.id
                    WHERE v.property_id = ?";
    $amenity_stmt = $pdo->prepare($amenity_sql);
    $amenity_stmt->execute([$property_id]);
    $amenities = $amenity_stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => [
            'property' => $property,
            'images' => $images,
            'contacts' => $contacts,
            'amenities' => $amenities
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

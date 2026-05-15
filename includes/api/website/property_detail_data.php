<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../database/db.php';

$response = [
    'success' => false,
    'data' => null,
    'similar' => []
];

try {
    $slug = $_GET['slug'] ?? '';
    if (empty($slug)) throw new Exception("Property slug is required");

    // 1. Fetch Property Details (Optimized Selection)
    $stmt = $pdo->prepare("
        SELECT p.id, p.title, p.slug, p.description, p.price, p.purpose, p.status, p.is_featured,
               p.area_size, p.area_unit, p.created_at, p.city_id, p.category_id,
               c.name as city_name, l.name as location_name, 
               ps.name as subtype_name, pc.name as category_name,
               u.full_name as owner_name, u.email as owner_email, u.phone as owner_phone, u.avatar_url as owner_avatar,
               a.name as agency_name, a.logo_url as agency_logo, a.id as agency_id,
               COALESCE(pst.views_total, 0) as views_total,
               (SELECT COUNT(*) FROM saved_properties WHERE property_id = p.id AND user_id = ?) as is_saved
        FROM properties p
        LEFT JOIN cities c ON p.city_id = c.id
        LEFT JOIN locations l ON p.location_id = l.id
        LEFT JOIN property_subtypes ps ON p.subtype_id = ps.id
        LEFT JOIN property_categories pc ON p.category_id = pc.id
        LEFT JOIN users u ON p.author_id = u.id
        LEFT JOIN agencies a ON p.agency_id = a.id
        LEFT JOIN property_stats pst ON p.id = pst.property_id
        WHERE p.slug = ? AND p.status = 'active'
    ");
    if (session_status() === PHP_SESSION_NONE) session_start();
    $user_id = $_SESSION['user_id'] ?? 0;
    $stmt->execute([$user_id, $slug]);
    $property = $stmt->fetch();

    if (!$property) throw new Exception("Property not found");

    // 2. Fetch Images
    $stmt_images = $pdo->prepare("SELECT image_url, is_main FROM property_images WHERE property_id = ? ORDER BY is_main DESC, id ASC");
    $stmt_images->execute([$property->id]);
    $property->images = $stmt_images->fetchAll();

    // 3. Fetch Amenities (Categorized or flattened)
    $stmt_amenities = $pdo->prepare("
        SELECT af.label, af.icon_class, pav.value 
        FROM property_amenity_values pav
        JOIN amenity_fields af ON pav.amenity_field_id = af.id
        WHERE pav.property_id = ?
    ");
    $stmt_amenities->execute([$property->id]);
    $property->amenities = $stmt_amenities->fetchAll();

    // 4. Fetch Similar Properties (Same city, same category, excluding current)
    $stmt_similar = $pdo->prepare("
        SELECT p.*, c.name as city_name, l.name as location_name, ps.name as subtype_name,
               (SELECT image_url FROM property_images WHERE property_id = p.id AND is_main = 1 LIMIT 1) as thumbnail
        FROM properties p
        LEFT JOIN cities c ON p.city_id = c.id
        LEFT JOIN locations l ON p.location_id = l.id
        LEFT JOIN property_subtypes ps ON p.subtype_id = ps.id
        WHERE p.city_id = ? AND p.category_id = ? AND p.id != ? AND p.status = 'active'
        ORDER BY p.created_at DESC
        LIMIT 4
    ");
    $stmt_similar->execute([$property->city_id, $property->category_id, $property->id]);
    $response['similar'] = $stmt_similar->fetchAll();

    // --- Interaction Tracking (View) ---
    try {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $user_id = !empty($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $prop_id = $property->id;

        // 1. Record View in property_interactions
        $interaction_stmt = $pdo->prepare("INSERT INTO property_interactions (property_id, user_id, interaction_type) VALUES (:prop_id, :user_id, 'view')");
        $interaction_stmt->bindValue(':prop_id', $prop_id, PDO::PARAM_INT);
        if ($user_id === null) {
            $interaction_stmt->bindValue(':user_id', null, PDO::PARAM_NULL);
        } else {
            $interaction_stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        }
        $interaction_stmt->execute();

        // 2. Increment Aggregate Stats in property_stats
        $stats_stmt = $pdo->prepare("
            INSERT INTO property_stats (property_id, views_total, last_viewed_at) 
            VALUES (?, 1, CURRENT_TIMESTAMP)
            ON DUPLICATE KEY UPDATE 
                views_total = views_total + 1, 
                last_viewed_at = CURRENT_TIMESTAMP
        ");
        $stats_stmt->execute([$prop_id]);
    } catch (Exception $e_track) {
        // Silently fail interaction tracking so property detail still loads
        error_log("Tracking Error: " . $e_track->getMessage());
    }

    $response['data'] = $property;
    $response['success'] = true;

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);

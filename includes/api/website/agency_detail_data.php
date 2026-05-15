<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../database/db.php';

try {
    $agency_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $slug = $_GET['slug'] ?? '';

    if (!$agency_id && !$slug) {
        throw new Exception("Agency ID or Slug is required");
    }

    // 1. Fetch Agency Info
    $query = "SELECT a.*, u.full_name as owner_name, u.avatar_url as owner_avatar
              FROM agencies a
              LEFT JOIN users u ON a.owner_id = u.id
              WHERE a.status = 'active' AND ";
    
    if ($slug) {
        $stmt_agency = $pdo->prepare($query . "a.slug = ?");
        $stmt_agency->execute([$slug]);
    } else {
        $stmt_agency = $pdo->prepare($query . "a.id = ?");
        $stmt_agency->execute([$agency_id]);
    }
    
    $agency = $stmt_agency->fetch();

    if (!$agency) {
        throw new Exception("Agency not found");
    }

    $actual_id = $agency->id;

    // 2. Fetch Agency Stats
    $stmt_stats = $pdo->prepare("
        SELECT 
            (SELECT COUNT(*) FROM properties WHERE agency_id = ? AND status = 'active') as total_listings,
            (SELECT COUNT(*) FROM agents WHERE agency_id = ?) as total_agents
    ");
    $stmt_stats->execute([$actual_id, $actual_id]);
    $stats = $stmt_stats->fetch();

    // 3. Fetch Active Properties (First 6)
    $stmt_props = $pdo->prepare("
        SELECT p.id, p.title, p.slug, p.price, p.area_size, p.area_unit, p.purpose,
               (SELECT image_url FROM property_images WHERE property_id = p.id AND is_main = 1 LIMIT 1) as thumbnail,
               c.name as city_name, l.name as location_name,
               (SELECT value FROM property_amenity_values WHERE property_id = p.id AND amenity_field_id = (SELECT id FROM amenity_fields WHERE label = 'Bedrooms' LIMIT 1)) as beds,
               (SELECT value FROM property_amenity_values WHERE property_id = p.id AND amenity_field_id = (SELECT id FROM amenity_fields WHERE label = 'Bathrooms' LIMIT 1)) as baths
        FROM properties p
        LEFT JOIN cities c ON p.city_id = c.id
        LEFT JOIN locations l ON p.location_id = l.id
        WHERE p.agency_id = ? AND p.status = 'active'
        ORDER BY p.created_at DESC
        LIMIT 6
    ");
    $stmt_props->execute([$actual_id]);
    $properties = $stmt_props->fetchAll();

    // 4. Fetch Agency Agents
    $stmt_agents = $pdo->prepare("
        SELECT u.id, u.full_name, u.avatar_url, ag.specialization, ag.slug
        FROM agents ag
        JOIN users u ON ag.user_id = u.id
        WHERE ag.agency_id = ? AND u.status = 'active'
    ");
    $stmt_agents->execute([$actual_id]);
    $agents = $stmt_agents->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => [
            'agency' => $agency,
            'stats' => $stats,
            'properties' => $properties,
            'agents' => $agents
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

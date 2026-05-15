<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../database/db.php';

try {
    $agent_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $slug = $_GET['slug'] ?? '';

    if (!$agent_id && !$slug) {
        throw new Exception("Agent ID or Slug is required");
    }

    // 1. Fetch Agent Profile
    $query = "SELECT u.id, u.full_name, u.avatar_url, u.email, u.phone,
                     ag.slug, ag.bio, ag.specialization, ag.experience_years, ag.agency_id,
                     a.name as agency_name, a.slug as agency_slug, a.logo_url as agency_logo, a.address as agency_address
              FROM agents ag
              JOIN users u ON ag.user_id = u.id
              LEFT JOIN agencies a ON ag.agency_id = a.id
              WHERE u.status = 'active' AND ";

    if ($slug) {
        $stmt_agent = $pdo->prepare($query . "ag.slug = ?");
        $stmt_agent->execute([$slug]);
    } else {
        $stmt_agent = $pdo->prepare($query . "u.id = ?");
        $stmt_agent->execute([$agent_id]);
    }
    
    $agent = $stmt_agent->fetch();

    if (!$agent) {
        throw new Exception("Agent not found");
    }

    $actual_user_id = $agent->id;

    // 2. Fetch Agent's Active Listings (First 6)
    $stmt_props = $pdo->prepare("
        SELECT p.id, p.title, p.slug, p.price, p.area_size, p.area_unit, p.purpose,
               (SELECT image_url FROM property_images WHERE property_id = p.id AND is_main = 1 LIMIT 1) as thumbnail,
               c.name as city_name, l.name as location_name,
               (SELECT value FROM property_amenity_values WHERE property_id = p.id AND amenity_field_id = (SELECT id FROM amenity_fields WHERE label = 'Bedrooms' LIMIT 1)) as beds,
               (SELECT value FROM property_amenity_values WHERE property_id = p.id AND amenity_field_id = (SELECT id FROM amenity_fields WHERE label = 'Bathrooms' LIMIT 1)) as baths
        FROM properties p
        LEFT JOIN cities c ON p.city_id = c.id
        LEFT JOIN locations l ON p.location_id = l.id
        WHERE p.author_id = ? AND p.status = 'active'
        ORDER BY p.created_at DESC
        LIMIT 6
    ");
    $stmt_props->execute([$actual_user_id]);
    $properties = $stmt_props->fetchAll();

    // 3. Stats
    $stmt_stats = $pdo->prepare("
        SELECT COUNT(*) as active_listings FROM properties WHERE author_id = ? AND status = 'active'
    ");
    $stmt_stats->execute([$actual_user_id]);
    $stats = $stmt_stats->fetch();

    echo json_encode([
        'success' => true,
        'data' => [
            'agent' => $agent,
            'properties' => $properties,
            'stats' => $stats
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

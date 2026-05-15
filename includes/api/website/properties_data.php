<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../database/db.php';

$response = [
    'success' => false,
    'data' => [],
    'meta' => [
        'total' => 0,
        'page' => 1,
        'limit' => 12
    ]
];

try {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $user_id = $_SESSION['user_id'] ?? 0;
    $params = $_GET;
    $where = ["p.status = 'active'"];
    $bindings = [];

    // 1. Search Query (City or Location)
    $q = $params['q'] ?? $params['search'] ?? '';
    if (!empty($q)) {
        $where[] = "(c.name LIKE ? OR l.name LIKE ? OR p.title LIKE ?)";
        $searchVal = "%{$q}%";
        $bindings[] = $searchVal;
        $bindings[] = $searchVal;
        $bindings[] = $searchVal;
    }

    // 2. City Filter
    if (!empty($params['city'])) {
        $where[] = "c.slug = ?";
        $bindings[] = $params['city'];
    }

    // 3. Location Filter
    if (!empty($params['location'])) {
        $where[] = "l.slug = ?";
        $bindings[] = $params['location'];
    }

    // 4. Purpose Filter (sale/rent)
    if (!empty($params['purpose'])) {
        $purpose_val = strtolower($params['purpose']);
        if ($purpose_val === 'buy' || $purpose_val === 'sale') $purpose_val = 'sell';
        
        $where[] = "p.purpose = ?";
        $bindings[] = $purpose_val;
    }

    // 5. Category Filter (Homes, Plots, Commercial)
    if (!empty($params['cat_id'])) {
        $where[] = "p.category_id = ?";
        $bindings[] = $params['cat_id'];
    }

    // 6. Subtype Filter (House, Flat, etc.)
    if (!empty($params['type_id'])) {
        $where[] = "p.subtype_id = ?";
        $bindings[] = $params['type_id'];
    }

    // Special mapping for slugs (e.g. type=home or type=house)
    if (!empty($params['type'])) {
        // We check if it's a category slug OR a subtype slug
        $where[] = "(pc.slug = ? OR ps.slug = ?)";
        $bindings[] = $params['type'];
        $bindings[] = $params['type'];
    }

    // 6. Price Range
    if (!empty($params['min_price'])) {
        $where[] = "p.price >= ?";
        $bindings[] = (float)$params['min_price'];
    }
    if (!empty($params['max_price'])) {
        $where[] = "p.price <= ?";
        $bindings[] = (float)$params['max_price'];
    }

    // 7. Area Size (Rough mapping for now)
    if (!empty($params['size'])) {
        // e.g. "5-marla"
        if (preg_match('/(\d+)-(marla|kanal)/i', $params['size'], $matches)) {
            $where[] = "p.area_size = ? AND p.area_unit = ?";
            $bindings[] = $matches[1];
            $bindings[] = ucfirst(strtolower($matches[2]));
        }
    }

    // 8. Sorting (Premium Priority + Recency)
    $orderBy = "
        CASE 
            WHEN p.premium_type = 'diamond' AND p.premium_status = 'active' THEN 1
            WHEN p.premium_type = 'platinum' AND p.premium_status = 'active' THEN 2
            ELSE 3 
        END ASC,
        p.created_at DESC
    ";

    if (!empty($params['sort'])) {
        switch($params['sort']) {
            case 'price_low': $orderBy = "p.price ASC, p.created_at DESC"; break;
            case 'price_high': $orderBy = "p.price DESC, p.created_at DESC"; break;
            case 'oldest': $orderBy = "p.created_at ASC"; break;
        }
    }

    // Construct Query
    $whereSql = implode(" AND ", $where);
    
    // Count Total
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM properties p
        LEFT JOIN cities c ON p.city_id = c.id
        LEFT JOIN locations l ON p.location_id = l.id
        LEFT JOIN property_subtypes ps ON p.subtype_id = ps.id
        LEFT JOIN property_categories pc ON p.category_id = pc.id
        WHERE $whereSql
    ");
    $countStmt->execute($bindings);
    $response['meta']['total'] = (int)$countStmt->fetchColumn();

    // Pagination
    $page = isset($params['page']) ? (int)$params['page'] : 1;
    $limit = 12;
    $offset = ($page - 1) * $limit;
    $response['meta']['page'] = $page;

    // Fetch Results (Optimized Selection)
    $stmt = $pdo->prepare("
        SELECT p.id, p.title, p.slug, p.price, p.purpose, p.area_size, p.area_unit, p.is_featured,
               p.premium_type, p.premium_status,
               c.name as city_name, l.name as location_name, ps.name as subtype_name, pc.name as category_name,
               (SELECT image_url FROM property_images WHERE property_id = p.id AND is_main = 1 LIMIT 1) as thumbnail,
               (SELECT COUNT(*) FROM saved_properties WHERE property_id = p.id AND user_id = ?) as is_saved
        FROM properties p
        LEFT JOIN cities c ON p.city_id = c.id
        LEFT JOIN locations l ON p.location_id = l.id
        LEFT JOIN property_subtypes ps ON p.subtype_id = ps.id
        LEFT JOIN property_categories pc ON p.category_id = pc.id
        WHERE $whereSql
        ORDER BY $orderBy
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute(array_merge([$user_id], $bindings));
    $results = $stmt->fetchAll();

    // Get beds/baths for results
    foreach ($results as &$prop) {
        $stmt_specs = $pdo->prepare("
            SELECT af.label, pav.value 
            FROM property_amenity_values pav
            JOIN amenity_fields af ON pav.amenity_field_id = af.id
            WHERE pav.property_id = ? AND af.label IN ('Bedrooms', 'Bathrooms')
        ");
        $stmt_specs->execute([$prop->id]);
        $specs = $stmt_specs->fetchAll();
        
        $prop->beds = 0;
        $prop->baths = 0;
        foreach ($specs as $s) {
            if ($s->label == 'Bedrooms') $prop->beds = $s->value;
            if ($s->label == 'Bathrooms') $prop->baths = $s->value;
        }
    }

    $response['data'] = $results;
    $response['success'] = true;

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);

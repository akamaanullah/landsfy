<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../database/db.php';

$response = [
    'success' => false,
    'data' => [
        'cities' => [],
        'featured_properties' => [],
        'premium_agencies' => [],
        'latest_blogs' => [],
        'categories' => [],
        'subtypes' => [],
        'popular_locations' => [],
        'counts' => [
            'homes' => 0,
            'plots' => 0,
            'commercial' => 0
        ]
    ]
];

try {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $user_id = $_SESSION['user_id'] ?? 0;
    // 1. Get Cities
    $stmt = $pdo->query("SELECT id, name, slug FROM cities ORDER BY sort_order ASC, name ASC");
    $response['data']['cities'] = $stmt->fetchAll();

    // 2. Get Featured Properties (Optimized Selection)
    $stmt = $pdo->prepare("
        SELECT p.id, p.title, p.slug, p.price, p.purpose, p.area_size, p.area_unit, p.is_featured,
               p.premium_type, p.premium_status,
               c.name as city_name, l.name as location_name, ps.name as subtype_name,
               (SELECT image_url FROM property_images WHERE property_id = p.id AND is_main = 1 LIMIT 1) as thumbnail,
               (SELECT COUNT(*) FROM saved_properties WHERE property_id = p.id AND user_id = ?) as is_saved
        FROM properties p
        LEFT JOIN cities c ON p.city_id = c.id
        LEFT JOIN locations l ON p.location_id = l.id
        LEFT JOIN property_subtypes ps ON p.subtype_id = ps.id
        WHERE p.is_featured = 1 AND p.status = 'active'
        ORDER BY 
            CASE 
                WHEN p.premium_type = 'diamond' AND p.premium_status = 'active' THEN 1
                WHEN p.premium_type = 'platinum' AND p.premium_status = 'active' THEN 2
                ELSE 3 
            END ASC,
            p.created_at DESC
        LIMIT 6
    ");
    $stmt->execute([$user_id]);
    $featured = $stmt->fetchAll();

    // Get specs for featured properties
    foreach ($featured as &$prop) {
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
    $response['data']['featured_properties'] = $featured;

    // 3. Get Premium Agencies
    $stmt = $pdo->query("
        SELECT a.id, a.name, a.slug, a.logo_url, c.name as city_name 
        FROM agencies a
        LEFT JOIN users u ON a.owner_id = u.id
        LEFT JOIN properties p ON p.agency_id = a.id
        LEFT JOIN cities c ON p.city_id = c.id
        WHERE a.is_premium = 1 AND a.status = 'active'
        GROUP BY a.id
        LIMIT 8
    ");
    $response['data']['premium_agencies'] = $stmt->fetchAll();

    // 4. Fetch Latest Blogs
    $blogs_stmt = $pdo->query("SELECT title, slug, excerpt, category, image_url, created_at FROM blogs WHERE status = 'published' ORDER BY created_at DESC LIMIT 3");
    $response['data']['latest_blogs'] = $blogs_stmt->fetchAll();

    // 5. Fetch Browse Section Data (Quick Filters Matched to Screenshots)
    $browse_data = [
        'home' => [
            'popular' => [
                ['name' => '5 Marla', 'subtext' => 'Houses', 'query' => 'size=5-marla'],
                ['name' => '10 Marla', 'subtext' => 'Houses', 'query' => 'size=10-marla'],
                ['name' => '3 Marla', 'subtext' => 'Houses', 'query' => 'size=3-marla'],
                ['name' => 'New', 'subtext' => 'Houses', 'query' => 'status=new'],
                ['name' => 'Low Price', 'subtext' => 'All Homes', 'query' => 'sort=price_low'],
                ['name' => 'Small', 'subtext' => 'Houses', 'query' => 'size=3-marla']
            ],
            'types' => [
                ['id' => 1, 'name' => 'Houses', 'subtext' => 'Home'],
                ['id' => 2, 'name' => 'Flats', 'subtext' => 'Home'],
                ['id' => 3, 'name' => 'Upper Portion', 'subtext' => 'Home'],
                ['id' => 4, 'name' => 'Lower Portion', 'subtext' => 'Home'],
                ['id' => 5, 'name' => 'Farmhouse', 'subtext' => 'Home'],
                ['id' => 7, 'name' => 'Penthouse', 'subtext' => 'Home']
            ],
            'sizes' => [
                ['name' => '5 Marla', 'subtext' => 'Homes', 'query' => 'size=5-marla'],
                ['name' => '3 Marla', 'subtext' => 'Homes', 'query' => 'size=3-marla'],
                ['name' => '7 Marla', 'subtext' => 'Homes', 'query' => 'size=7-marla'],
                ['name' => '8 Marla', 'subtext' => 'Homes', 'query' => 'size=8-marla'],
                ['name' => '10 Marla', 'subtext' => 'Homes', 'query' => 'size=10-marla'],
                ['name' => '1 Kanal', 'subtext' => 'Homes', 'query' => 'size=1-kanal']
            ]
        ],
        'plots' => [
            'popular' => [
                ['name' => '5 Marla', 'subtext' => 'Residential Plots', 'query' => 'category_id=2&size=5-marla'],
                ['name' => '10 Marla', 'subtext' => 'Residential Plots', 'query' => 'category_id=2&size=10-marla'],
                ['name' => '7 Marla', 'subtext' => 'Residential Plots', 'query' => 'category_id=2&size=7-marla'],
                ['name' => '3 Marla', 'subtext' => 'Residential Plots', 'query' => 'category_id=2&size=3-marla'],
                ['name' => 'On Instalments', 'subtext' => 'Residential Plots', 'query' => 'category_id=2&installments=1'],
                ['name' => 'On Instalments', 'subtext' => 'Commercial Plots', 'query' => 'category_id=2&subtype_id=9&installments=1']
            ],
            'types' => [
                ['id' => 8, 'name' => 'Residential Plot', 'subtext' => 'Plots'],
                ['id' => 9, 'name' => 'Commercial Plot', 'subtext' => 'Plots'],
                ['id' => 16, 'name' => 'Plot File', 'subtext' => 'Plots'],
                ['id' => 16, 'name' => 'Plot Form', 'subtext' => 'Plots'],
                ['id' => 10, 'name' => 'Agricultural Land', 'subtext' => 'Plots'],
                ['id' => 11, 'name' => 'Industrial Land', 'subtext' => 'Plots']
            ],
            'sizes' => [
                ['name' => '3 Marla', 'subtext' => 'Plots', 'query' => 'category_id=2&size=3-marla'],
                ['name' => '5 Marla', 'subtext' => 'Plots', 'query' => 'category_id=2&size=5-marla'],
                ['name' => '7 Marla', 'subtext' => 'Plots', 'query' => 'category_id=2&size=7-marla'],
                ['name' => '8 Marla', 'subtext' => 'Plots', 'query' => 'category_id=2&size=8-marla'],
                ['name' => '10 Marla', 'subtext' => 'Plots', 'query' => 'category_id=2&size=10-marla'],
                ['name' => '1 Kanal', 'subtext' => 'Plots', 'query' => 'category_id=2&size=1-kanal']
            ]
        ],
        'commercial' => [
            'popular' => [
                ['name' => 'Small Offices', 'subtext' => 'Offices', 'query' => 'subtype_id=12&size_max=500'],
                ['name' => 'New Offices', 'subtext' => 'Offices', 'query' => 'subtype_id=12&status=new'],
                ['name' => 'On Instalments', 'subtext' => 'Shops', 'query' => 'subtype_id=13&installments=1'],
                ['name' => 'Small Shops', 'subtext' => 'Shops', 'query' => 'subtype_id=13&size_max=200'],
                ['name' => 'New Shops', 'subtext' => 'Shops', 'query' => 'subtype_id=13&status=new'],
                ['name' => 'Running Shops', 'subtext' => 'Shops', 'query' => 'subtype_id=13&status=running']
            ],
            'types' => [
                ['id' => 12, 'name' => 'Office', 'subtext' => 'Commercial'],
                ['id' => 13, 'name' => 'Shop', 'subtext' => 'Commercial'],
                ['id' => 15, 'name' => 'Building', 'subtext' => 'Commercial'],
                ['id' => 14, 'name' => 'Warehouse', 'subtext' => 'Commercial'],
                ['id' => 16, 'name' => 'Factory', 'subtext' => 'Commercial'],
                ['id' => 0, 'name' => 'Others', 'subtext' => 'Commercial']
            ],
            'sizes' => [
                ['name' => 'Less than 100 sqft', 'subtext' => 'Commercial', 'query' => 'size_max=100'],
                ['name' => '100-200 sqft', 'subtext' => 'Commercial', 'query' => 'size_min=100&size_max=200'],
                ['name' => '200-300 sqft', 'subtext' => 'Commercial', 'query' => 'size_min=200&size_max=300'],
                ['name' => '300-400 sqft', 'subtext' => 'Commercial', 'query' => 'size_min=300&size_max=400'],
                ['name' => '400-500 sqft', 'subtext' => 'Commercial', 'query' => 'size_min=400&size_max=500'],
                ['name' => 'More than 500 sqft', 'subtext' => 'Commercial', 'query' => 'size_min=500']
            ]
        ]
    ];

    $response['data']['browse_data'] = $browse_data;
    
    // 5. Get Category Counts
    $stmt = $pdo->query("SELECT category_id, COUNT(*) as count FROM properties WHERE status = 'active' GROUP BY category_id");
    $counts = $stmt->fetchAll();
    foreach ($counts as $c) {
        if ($c->category_id == 1) $response['data']['counts']['homes'] = $c->count;
        if ($c->category_id == 2) $response['data']['counts']['plots'] = $c->count;
        if ($c->category_id == 3) $response['data']['counts']['commercial'] = $c->count;
    }

    // 6. Get Categories & Subtypes for Filters
    $stmt = $pdo->query("SELECT id, name FROM property_categories ORDER BY name ASC");
    $response['data']['categories'] = $stmt->fetchAll();

    $stmt = $pdo->query("SELECT id, category_id, name FROM property_subtypes ORDER BY name ASC");
    $response['data']['subtypes'] = $stmt->fetchAll();

    // 7. Get Popular Locations
    $stmt = $pdo->query("
        SELECT c.name as city_name, l.name as location_name, l.slug as location_slug, 
               pc.name as category_name, pc.id as category_id, COUNT(p.id) as prop_count
        FROM properties p
        JOIN cities c ON p.city_id = c.id
        JOIN locations l ON p.location_id = l.id
        JOIN property_categories pc ON p.category_id = pc.id
        WHERE p.status = 'active'
        GROUP BY c.id, l.id, pc.id
        ORDER BY prop_count DESC
        LIMIT 24
    ");
    $response['data']['popular_locations'] = $stmt->fetchAll();

    $response['success'] = true;

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);

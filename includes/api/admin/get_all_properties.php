<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

if ($_SESSION['role_name'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
session_write_close();

try {
    // 1. Filter Parameters
    $where = ["p.status != 'deleted'"];
    $params = [];

    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $where[] = "(p.title LIKE :s1 OR u.username LIKE :s2 OR loc.name LIKE :s3 OR city.name LIKE :s4)";
        $params['s1'] = "%" . $_GET['search'] . "%";
        $params['s2'] = "%" . $_GET['search'] . "%";
        $params['s3'] = "%" . $_GET['search'] . "%";
        $params['s4'] = "%" . $_GET['search'] . "%";
    }

    if (isset($_GET['agency']) && !empty($_GET['agency']) && $_GET['agency'] !== 'all') {
        $where[] = "p.agency_id = :agency";
        $params['agency'] = $_GET['agency'];
    }

    if (isset($_GET['category']) && !empty($_GET['category']) && $_GET['category'] !== 'all') {
        $where[] = "p.category_id = :cat";
        $params['cat'] = $_GET['category'];
    }

    if (isset($_GET['purpose']) && !empty($_GET['purpose']) && $_GET['purpose'] !== 'all') {
        $where[] = "p.purpose = :purpose";
        $params['purpose'] = $_GET['purpose'];
    }

    $where_clause = implode(" AND ", $where);

    // 2. Fetch Filtered Stats (Reflecting active filters)
    $stats_query = "
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN p.status = 'active' THEN 1 ELSE 0 END) as published,
            SUM(CASE WHEN p.status = 'under_review' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN p.status = 'sold' THEN 1 ELSE 0 END) as sold
        FROM properties p
        JOIN users u ON p.author_id = u.id
        LEFT JOIN locations loc ON p.location_id = loc.id
        LEFT JOIN cities city ON p.city_id = city.id
        WHERE $where_clause
    ";
    $stats_stmt = $pdo->prepare($stats_query);
    $stats_stmt->execute($params);
    $stats_row = $stats_stmt->fetch();

    // 3. Pagination Setup
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;
    $offset = ($page - 1) * $limit;

    // 4. Properties List
    $query = "
        SELECT p.*, u.username as author_name, c.name as cat_name, 
               city.name as city_name, loc.name as location_name,
               (SELECT image_url FROM property_images WHERE property_id = p.id AND is_main = 1 LIMIT 1) as featured_image
        FROM properties p
        JOIN users u ON p.author_id = u.id
        JOIN property_categories c ON p.category_id = c.id
        LEFT JOIN locations loc ON p.location_id = loc.id
        LEFT JOIN cities city ON p.city_id = city.id
        WHERE $where_clause
        ORDER BY 
            CASE 
                WHEN p.premium_type = 'diamond' AND p.premium_status = 'active' THEN 1
                WHEN p.premium_type = 'platinum' AND p.premium_status = 'active' THEN 2
                ELSE 3 
            END ASC,
            p.created_at DESC
        LIMIT :limit OFFSET :offset
    ";
    
    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $listings = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => [
            'stats' => [
                'total' => (int)$stats_row->total,
                'published' => (int)$stats_row->published,
                'pending' => (int)$stats_row->pending,
                'sold' => (int)$stats_row->sold
            ],
            'page' => $page,
            'limit' => $limit,
            'listings' => $listings
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_name'], ['seller', 'admin'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

require_once '../../database/db.php';

$user_id = $_SESSION['user_id'];
session_write_close();

// Extract filters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? 'all';
$type = $_GET['type'] ?? 'all';
$purpose = $_GET['purpose'] ?? 'all';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = max(1, intval($_GET['limit'] ?? 24));
$offset = ($page - 1) * $limit;

try {
    $sql = "
        SELECT 
            p.id, p.title, p.price, p.purpose, p.status, p.created_at,
            c.name as city_name,
            COALESCE(ps.views_total, 0) as views_total,
            SUM(CASE WHEN pi.interaction_type IN ('whatsapp_click', 'call_reveal') THEN 1 ELSE 0 END) AS total_clicks,
            (SELECT image_url FROM property_images WHERE property_id = p.id AND is_main = 1 LIMIT 1) as main_image,
            (SELECT image_url FROM property_images WHERE property_id = p.id LIMIT 1) as fallback_image
        FROM properties p
        LEFT JOIN cities c ON p.city_id = c.id
        LEFT JOIN property_stats ps ON p.id = ps.property_id
        LEFT JOIN property_interactions pi ON p.id = pi.property_id
        WHERE p.author_id = :author_id AND p.status != 'deleted'
    ";
    
    $params = [':author_id' => $user_id];

    if (!empty($search)) {
        $sql .= " AND (p.title LIKE :search OR c.name LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }

    if ($status !== 'all') {
        if ($status === 'under_review') {
            $sql .= " AND p.status = 'under_review'";
        } else {
            $sql .= " AND p.status = :status";
            $params[':status'] = $status;
        }
    }

    if ($type !== 'all') {
        $sql .= " AND p.category_id = :category_id";
        $params[':category_id'] = $type;
    }

    if ($purpose !== 'all') {
        $sql .= " AND p.purpose = :purpose";
        $params[':purpose'] = $purpose;
    }

    // Calculate total count properly
    $count_sql = "SELECT COUNT(p.id) FROM properties p LEFT JOIN cities c ON p.city_id = c.id WHERE p.author_id = :author_id AND p.status != 'deleted'";
    $where_clauses = "";
    if (!empty($search)) $where_clauses .= " AND (p.title LIKE :search OR c.name LIKE :search)";
    if ($status !== 'all') {
        if ($status === 'under_review') $where_clauses .= " AND p.status = 'under_review'";
        else $where_clauses .= " AND p.status = :status";
    }
    if ($type !== 'all') $where_clauses .= " AND p.category_id = :category_id";
    if ($purpose !== 'all') $where_clauses .= " AND p.purpose = :purpose";
    
    $count_stmt = $pdo->prepare($count_sql . $where_clauses);
    $count_stmt->execute($params);
    $total_items = (int)$count_stmt->fetchColumn();
    $total_pages = ceil($total_items / $limit);

    $sql .= " GROUP BY p.id ORDER BY p.created_at DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $formatted = array_map(function($row) {
        $img = $row['main_image'] ?: $row['fallback_image'];
        $row['image_url'] = $img ? '../' . $img : 'https://images.unsplash.com/photo-1560518883-ce09059eeffa'; 
        return $row;
    }, $listings);

    echo json_encode(['success' => true, 'data' => $formatted, 'meta' => [
        'current_page' => $page,
        'per_page' => $limit,
        'total_items' => $total_items,
        'total_pages' => $total_pages
    ]]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}

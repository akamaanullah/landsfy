<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_name'], ['agent', 'admin', 'agency_owner'])) {
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
            p.premium_type, p.premium_status, p.premium_expiry,
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

    // Search Clause
    if (!empty($search)) {
        $sql .= " AND (p.title LIKE :search OR c.name LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }

    // Status Clause
    if ($status !== 'all') {
        if ($status === 'pending') {
            $sql .= " AND p.status = 'under_review'";
        } else {
            $sql .= " AND p.status = :status";
            $params[':status'] = $status;
        }
    }

    // Type (Category) Clause
    if ($type !== 'all') {
        if ($type === 'residential') {
            $sql .= " AND p.category_id = 1";
        } elseif ($type === 'plots') {
            $sql .= " AND p.category_id = 2";
        } elseif ($type === 'commercial') {
            $sql .= " AND p.category_id = 3";
        }
    }

    // Purpose Clause
    if ($purpose !== 'all') {
        if ($purpose === 'sale') {
            $sql .= " AND p.purpose = 'sell'";
        } else {
            $sql .= " AND p.purpose = :purpose";
            $params[':purpose'] = $purpose;
        }
    }

    // Calculate total count properly
    $where_clauses = " p.author_id = :author_id AND p.status != 'deleted' ";
    if (!empty($search)) $where_clauses .= " AND (p.title LIKE :search OR c.name LIKE :search) ";
    if ($status !== 'all') {
        if ($status === 'pending') $where_clauses .= " AND p.status = 'under_review' ";
        else $where_clauses .= " AND p.status = :status ";
    }
    if ($type !== 'all') {
        if ($type === 'residential') $where_clauses .= " AND p.category_id = 1 ";
        elseif ($type === 'plots') $where_clauses .= " AND p.category_id = 2 ";
        elseif ($type === 'commercial') $where_clauses .= " AND p.category_id = 3 ";
    }
    if ($purpose !== 'all') {
        if ($purpose === 'sale') $where_clauses .= " AND p.purpose = 'sell' ";
        else $where_clauses .= " AND p.purpose = :purpose ";
    }

    $count_stmt = $pdo->prepare("SELECT COUNT(p.id) FROM properties p LEFT JOIN cities c ON p.city_id = c.id WHERE " . $where_clauses);
    $count_stmt->execute($params);
    $total_items = (int)$count_stmt->fetchColumn();
    $total_pages = ceil($total_items / $limit);

    $sql .= " GROUP BY p.id ORDER BY p.created_at DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format output
    $formatted = array_map(function($row) {
        $img = $row['main_image'] ?: $row['fallback_image'];
        $row['image_url'] = $img ?: 'https://images.unsplash.com/photo-1560518883-ce09059eeffa';
        
        // Handle Expiry Formatting
        if ($row['premium_expiry']) {
            $row['expiry_human'] = date('M d, Y', strtotime($row['premium_expiry']));
            $days_left = ceil((strtotime($row['premium_expiry']) - time()) / (60 * 60 * 24));
            $row['days_left'] = max(0, $days_left);
        } else {
            $row['expiry_human'] = null;
            $row['days_left'] = null;
        }
        
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
    echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
}
?>

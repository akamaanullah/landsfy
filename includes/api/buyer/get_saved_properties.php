<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

if ($_SESSION['role_name'] !== 'buyer' && $_SESSION['role_name'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
session_write_close();
$page = max(1, intval($_GET['page'] ?? 1));
$limit = max(1, intval($_GET['limit'] ?? 24));
$offset = ($page - 1) * $limit;

try {
    $sql = "SELECT p.*, c.name as city_name, l.name as location_name,
            (SELECT image_url FROM property_images WHERE property_id = p.id AND is_main = 1 LIMIT 1) as main_image
            FROM saved_properties sp
            JOIN properties p ON sp.property_id = p.id
            LEFT JOIN cities c ON p.city_id = c.id
            LEFT JOIN locations l ON p.location_id = l.id
            WHERE sp.user_id = ?
            ORDER BY sp.saved_at DESC
            LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
    
    // Total calculation
    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM saved_properties WHERE user_id = ?");
    $count_stmt->execute([$user_id]);
    $total_items = (int)$count_stmt->fetchColumn();

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'properties' => $properties,
        'meta' => [
            'current_page' => $page,
            'per_page' => $limit,
            'total_items' => $total_items,
            'total_pages' => ceil($total_items / $limit)
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

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

try {
    $stmt = $pdo->prepare("
        SELECT 
            p.id, p.title, p.slug, p.status, p.created_at,
            c.name as city_name,
            COALESCE(ps.views_total, 0) as views_total,
            SUM(CASE WHEN pi.interaction_type = 'whatsapp_click' THEN 1 ELSE 0 END) AS whatsapp_clicks,
            SUM(CASE WHEN pi.interaction_type = 'call_reveal' THEN 1 ELSE 0 END) AS call_inquiries
        FROM properties p
        LEFT JOIN cities c ON p.city_id = c.id
        LEFT JOIN property_stats ps ON p.id = ps.property_id
        LEFT JOIN property_interactions pi ON p.id = pi.property_id
        WHERE p.author_id = ? AND p.status != 'deleted'
        GROUP BY p.id
        ORDER BY views_total DESC, whatsapp_clicks DESC
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $listings]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred.', 'error' => $e->getMessage()]);
}
?>

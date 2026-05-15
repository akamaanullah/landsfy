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
    // Recent Interactions (Timeline)
    $stmt = $pdo->prepare("
        SELECT 
            pi.id, pi.interaction_type, pi.created_at,
            p.title as property_title,
            u.full_name as user_name
        FROM property_interactions pi
        JOIN properties p ON pi.property_id = p.id
        LEFT JOIN users u ON pi.user_id = u.id
        WHERE p.author_id = ?
        ORDER BY pi.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $timeline = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $timeline]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
}
?>

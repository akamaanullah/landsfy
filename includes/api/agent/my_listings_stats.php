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
            COUNT(id) as total_listings,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as published_count,
            SUM(CASE WHEN status = 'under_review' THEN 1 ELSE 0 END) as pending_count,
            SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) as sold_count
        FROM properties 
        WHERE author_id = ? AND status != 'deleted'
    ");
    $stmt->execute([$user_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => [
            'total' => (int)($stats['total_listings'] ?? 0),
            'published' => (int)($stats['published_count'] ?? 0),
            'pending' => (int)($stats['pending_count'] ?? 0),
            'sold' => (int)($stats['sold_count'] ?? 0)
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
}
?>

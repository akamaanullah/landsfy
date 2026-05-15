<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

if ($_SESSION['role_name'] !== 'seller' && $_SESSION['role_name'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Fetch notifications for the seller
    $stmt = $pdo->prepare("
        SELECT n.*, u.full_name as sender_name, u.avatar_url as sender_avatar
        FROM notifications n
        LEFT JOIN users u ON n.sender_id = u.id
        WHERE n.user_id = ?
        ORDER BY n.created_at DESC
        LIMIT 50
    ");
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'notifications' => $notifications
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

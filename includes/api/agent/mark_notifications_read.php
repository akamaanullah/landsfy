<?php
require_once '../../auth_check.php';
require_once '../../database/db.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    if (isset($input['action']) && $input['action'] === 'mark_all') {
        $sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);
    } elseif (isset($input['id'])) {
        $sql = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$input['id'], $user_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing notification ID.']);
        exit;
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

<?php
require_once '../../auth_check.php';
require_once '../../database/db.php';

header('Content-Type: application/json');

if ($_SESSION['role_name'] != 'seller' && $_SESSION['role_name'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Update user status and clear session
        $stmt = $pdo->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
        $stmt->execute([$user_id]);

        // Destroy session to log out the user
        session_destroy();

        echo json_encode([
            'success' => true, 
            'message' => 'Account deactivated successfully.'
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to deactivate account.'
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

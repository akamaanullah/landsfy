<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

if ($_SESSION['role_name'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$key = $_POST['setting_key'] ?? '';
$val = $_POST['setting_value'] ?? '';

if (!$key) {
    echo json_encode(['success' => false, 'message' => 'Key required']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO user_settings (user_id, setting_key, setting_value) 
        VALUES (?, ?, ?) 
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
    ");
    $stmt->execute([$user_id, $key, $val]);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
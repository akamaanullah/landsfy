<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../../database/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Login required']);
    exit;
}

$user_id = $_SESSION['user_id'];
$setting_key = $_POST['key'] ?? '';
$setting_value = $_POST['value'] ?? $_POST['theme'] ?? '';

if (empty($setting_key)) {
    echo json_encode(['success' => false, 'message' => 'Missing setting key']);
    exit;
}

// Sanitize key (allow alphanumeric and underscores)
$setting_key = preg_replace('/[^a-zA-Z0-9_]/', '', $setting_key);

try {
    // Upsert user setting
    $stmt = $pdo->prepare("INSERT INTO user_settings (user_id, setting_key, setting_value) 
                           VALUES (?, ?, ?) 
                           ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->execute([$user_id, $setting_key, $setting_value, $setting_value]);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>

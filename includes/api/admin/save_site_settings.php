<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

// Enforce Admin
if ($_SESSION['role_name'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $pdo->beginTransaction();

    foreach ($_POST as $key => $value) {
        // Sanitize key
        $key = preg_replace('/[^a-zA-Z0-9_]/', '', $key);
        
        // Upsert setting
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) 
                               VALUES (?, ?) 
                               ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$key, $value, $value]);
    }

    $pdo->commit();

    // Log the action
    $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action_type, target_type, target_id, details) 
                               VALUES (?, 'update', 'settings', 0, 'Updated global site settings')");
    $log_stmt->execute([$_SESSION['user_id']]);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

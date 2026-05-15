<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

if ($_SESSION['role_name'] !== 'buyer' && $_SESSION['role_name'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$property_id = $_POST['property_id'] ?? null;

if (!$property_id) {
    echo json_encode(['success' => false, 'message' => 'Property ID is required']);
    exit;
}

try {
    // Check if already saved
    $stmt = $pdo->prepare("SELECT id FROM saved_properties WHERE user_id = ? AND property_id = ?");
    $stmt->execute([$user_id, $property_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Unsave
        $stmt = $pdo->prepare("DELETE FROM saved_properties WHERE user_id = ? AND property_id = ?");
        $stmt->execute([$user_id, $property_id]);
        $status = 'unsaved';
    } else {
        // Save
        $stmt = $pdo->prepare("INSERT INTO saved_properties (user_id, property_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $property_id]);
        $status = 'saved';
    }

    echo json_encode(['success' => true, 'status' => $status]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

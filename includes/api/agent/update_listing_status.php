<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_name'], ['agent', 'admin', 'agency_owner', 'seller'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

require_once '../../database/db.php';

$user_id = $_SESSION['user_id'];
$property_id = $_POST['property_id'] ?? null;
$action = $_POST['action'] ?? null;

if (!$property_id || !$action) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
    exit;
}

try {
    // 1. Verify that this property actually belongs to the logged-in agent 
    // Do not let them edit someone else's properties!
    $stmt = $pdo->prepare("SELECT id FROM properties WHERE id = ? AND author_id = ?");
    $stmt->execute([$property_id, $user_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Property not found or access denied.']);
        exit;
    }

    $new_status = null;
    if ($action === 'delete') {
        $new_status = 'deleted'; // Soft delete
    } elseif ($action === 'sold') {
        $new_status = 'sold';
    } elseif ($action === 'pause') {
        $new_status = 'inactive';
    } else {
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
        exit;
    }

    $updateStmt = $pdo->prepare("UPDATE properties SET status = ? WHERE id = ?");
    $updateStmt->execute([$new_status, $property_id]);

    echo json_encode(['success' => true, 'message' => 'Property updated successfully.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
}
?>

<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

if ($_SESSION['role_name'] !== 'agency_owner' && $_SESSION['role_name'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$owner_id = $_SESSION['user_id'];

try {
    $agent_id = $_POST['agent_id'] ?? null;

    if (!$agent_id) {
        throw new Exception("Agent ID is required.");
    }

    // 1. Fetch Agency ID to ensure ownership
    $agency_stmt = $pdo->prepare("SELECT id FROM agencies WHERE owner_id = ?");
    $agency_stmt->execute([$owner_id]);
    $agency = $agency_stmt->fetch();

    if (!$agency) {
        throw new Exception("Agency not found.");
    }
    $agency_id = $agency->id;

    // 2. Delete from agents table (ensure it belongs to this agency)
    $stmt = $pdo->prepare("DELETE FROM agents WHERE id = ? AND agency_id = ?");
    $stmt->execute([$agent_id, $agency_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Agent removed from team.']);
    } else {
        throw new Exception("Agent not found or does not belong to your agency.");
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

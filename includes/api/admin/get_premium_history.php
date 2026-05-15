<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

if ($_SESSION['role_name'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Missing User ID']);
    exit;
}

try {
    // Fetch logs related to this user
    // 1. Quota updates (manual)
    // 2. Premium requests
    // 3. Quota deductions (listing activations)
    
    $query = "
        (SELECT 'quota_update' as log_type, description, created_at, 'system' as detail
         FROM activity_logs 
         WHERE (action_type = 'manual_quota_update' AND target_id = :uid1)
            OR (action_type = 'premium_request_approved' AND target_id IN (SELECT id FROM premium_requests WHERE user_id = :uid2)))
        UNION ALL
        (SELECT 'listing_activation' as log_type, description, created_at, 'property' as detail
         FROM activity_logs 
         WHERE action_type = 'property_approved' AND target_id IN (SELECT id FROM properties WHERE author_id = :uid3 AND premium_type != 'none'))
        ORDER BY created_at DESC
        LIMIT 50
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute(['uid1' => $user_id, 'uid2' => $user_id, 'uid3' => $user_id]);
    $history = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => [
            'history' => $history
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

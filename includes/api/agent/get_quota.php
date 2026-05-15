<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';
require_once '../../helpers/quota_helper.php';

if ($_SESSION['role_name'] !== 'agent') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$quota = getAgentQuota($pdo, $_SESSION['user_id']);

if ($quota) {
    echo json_encode([
        'success' => true,
        'data' => [
            'platinum' => [
                'total' => (int)$quota['platinum_quota'],
                'used' => (int)$quota['platinum_used'],
                'available' => (int)$quota['platinum_quota'] - (int)$quota['platinum_used']
            ],
            'diamond' => [
                'total' => (int)$quota['diamond_quota'],
                'used' => (int)$quota['diamond_used'],
                'available' => (int)$quota['diamond_quota'] - (int)$quota['diamond_used']
            ]
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch quota']);
}
?>

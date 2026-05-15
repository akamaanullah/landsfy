<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

if ($_SESSION['role_name'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // 1. Stats
    $total_agencies = $pdo->query("SELECT COUNT(*) FROM agencies")->fetchColumn();
    $verified_agencies = $pdo->query("SELECT COUNT(*) FROM agencies WHERE is_verified = 1")->fetchColumn();
    $pending_agencies = $pdo->query("SELECT COUNT(*) FROM agencies WHERE status = 'under_review'")->fetchColumn();

    // 2. Details
    $stmt = $pdo->query("
        SELECT a.*, u.username as owner_name, u.avatar_url as owner_avatar,
        (SELECT COUNT(*) FROM properties WHERE agency_id = a.id) as listing_count,
        (SELECT COUNT(*) FROM agents WHERE agency_id = a.id) as agent_count
        FROM agencies a
        JOIN users u ON a.owner_id = u.id
        ORDER BY a.created_at DESC
    ");
    $agencies = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => [
            'stats' => [
                'total' => (int)$total_agencies,
                'verified' => (int)$verified_agencies,
                'pending' => (int)$pending_agencies
            ],
            'agencies' => $agencies
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

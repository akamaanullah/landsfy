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
    $stats = $pdo->query("
        SELECT r.role_name, COUNT(*) as count 
        FROM users u 
        JOIN user_roles ur ON u.id = ur.user_id 
        JOIN roles r ON ur.role_id = r.id 
        GROUP BY r.role_name
    ")->fetchAll(PDO::FETCH_KEY_PAIR);

    // 2. All Users (Excluding Admins)
    $stmt = $pdo->query("
        SELECT u.id, u.username, u.full_name, u.email, u.avatar_url, u.status, u.created_at, r.role_name,
               a.agency_id, ag.name as agency_name,
               a.platinum_quota, a.platinum_used, a.diamond_quota, a.diamond_used
        FROM users u 
        LEFT JOIN user_roles ur ON u.id = ur.user_id 
        LEFT JOIN roles r ON ur.role_id = r.id 
        LEFT JOIN agents a ON u.id = a.user_id
        LEFT JOIN agencies ag ON a.agency_id = ag.id
        WHERE r.role_name != 'admin' OR r.role_name IS NULL
        ORDER BY u.created_at DESC
    ");
    $users = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => [
            'stats' => [
                'agent' => (int)($stats['agent'] ?? 0),
                'buyer' => (int)($stats['buyer'] ?? 0),
                'seller' => (int)($stats['seller'] ?? 0)
            ],
            'users' => $users
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

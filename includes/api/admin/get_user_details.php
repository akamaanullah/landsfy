<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

if ($_SESSION['role_name'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit;
}

try {
    // 1. Basic User Info
    $user_stmt = $pdo->prepare("
        SELECT u.id, u.username, u.full_name, u.email, u.avatar_url, u.status, u.created_at, r.role_name, u.phone,
               a.platinum_quota, a.platinum_used, a.diamond_quota, a.diamond_used
        FROM users u 
        LEFT JOIN user_roles ur ON u.id = ur.user_id 
        LEFT JOIN roles r ON ur.role_id = r.id 
        LEFT JOIN agents a ON u.id = a.user_id
        WHERE u.id = ?
    ");
    $user_stmt->execute([$id]);
    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("User not found");
    }

    // 2. Stats (Property Count)
    $prop_count_stmt = $pdo->prepare("SELECT COUNT(*) FROM properties WHERE author_id = ?");
    $prop_count_stmt->execute([$id]);
    $property_count = $prop_count_stmt->fetchColumn();

    // 3. Activity Logs (Last 10)
    $logs_stmt = $pdo->prepare("SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
    $logs_stmt->execute([$id]);
    $logs = $logs_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Properties (Last 6)
    $props_stmt = $pdo->prepare("
        SELECT p.id, p.title, p.price, p.location_name, p.status,
               sub.name as property_type,
               img.image_url
        FROM properties p
        LEFT JOIN property_subtypes sub ON p.subtype_id = sub.id
        LEFT JOIN property_images img ON (p.id = img.property_id AND img.is_main = 1)
        WHERE p.author_id = ? 
        ORDER BY p.created_at DESC 
        LIMIT 6
    ");
    $props_stmt->execute([$id]);
    $properties = $props_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Reviews (If Agent)
    $reviews = [];
    if ($user['role_name'] === 'agent') {
        $rev_stmt = $pdo->prepare("
            SELECT r.*, u.full_name as reviewer_name, u.avatar_url as reviewer_avatar
            FROM agent_reviews r
            JOIN users u ON r.reviewer_id = u.id
            WHERE r.agent_id = ?
            ORDER BY r.created_at DESC
            LIMIT 10
        ");
        $rev_stmt->execute([$id]);
        $reviews = $rev_stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'user' => $user,
            'stats' => [
                'property_count' => (int)$property_count
            ],
            'activity' => $logs,
            'properties' => $properties,
            'reviews' => $reviews,
            'quota' => [
                'platinum' => [
                    'total' => (int)($user['platinum_quota'] ?? 0),
                    'used' => (int)($user['platinum_used'] ?? 0)
                ],
                'diamond' => [
                    'total' => (int)($user['diamond_quota'] ?? 0),
                    'used' => (int)($user['diamond_used'] ?? 0)
                ]
            ]
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

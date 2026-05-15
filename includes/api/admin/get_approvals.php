<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

if ($_SESSION['role_name'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // 1. Pending Approvals
    $stmt = $pdo->prepare("
        SELECT p.*, u.username as agent_name, u.avatar_url as agent_avatar,
        c.name as category_name,
        (SELECT image_url FROM property_images WHERE property_id = p.id AND is_main = 1 LIMIT 1) as featured_image
        FROM properties p
        JOIN users u ON p.author_id = u.id
        JOIN property_categories c ON p.category_id = c.id
        WHERE p.status = 'under_review'
        ORDER BY p.created_at ASC
    ");
    $stmt->execute();
    $pending_listings = $stmt->fetchAll();

    // 2. Stats
    $today = date('Y-m-d');
    $approved_today = $pdo->query("SELECT COUNT(*) FROM activity_logs WHERE action_type = 'property_approved' AND DATE(created_at) = '$today'")->fetchColumn();
    $rejected_today = $pdo->query("SELECT COUNT(*) FROM activity_logs WHERE action_type = 'property_rejected' AND DATE(created_at) = '$today'")->fetchColumn();

    // 3. Recent History (Last 20)
    $history_stmt = $pdo->prepare("
        SELECT l.*, p.title as property_title, u.username as actor_name
        FROM activity_logs l
        LEFT JOIN properties p ON l.target_id = p.id
        LEFT JOIN users u ON l.user_id = u.id
        WHERE l.action_type IN ('property_approved', 'property_rejected')
        ORDER BY l.created_at DESC
        LIMIT 20
    ");
    $history_stmt->execute();
    $history = $history_stmt->fetchAll();

    // 4. Pending Agencies
    $agency_stmt = $pdo->prepare("
        SELECT a.*, u.full_name as owner_name, u.avatar_url as owner_avatar
        FROM agencies a
        JOIN users u ON a.owner_id = u.id
        WHERE a.status = 'under_review'
        ORDER BY a.created_at ASC
    ");
    $agency_stmt->execute();
    $pending_agencies = $agency_stmt->fetchAll();
 
    echo json_encode([
        'success' => true,
        'data' => [
            'pending_listings' => $pending_listings,
            'pending_agencies' => $pending_agencies,
            'recent_actions' => $history,
            'stats' => [
                'pending' => count($pending_listings) + count($pending_agencies),
                'pending_listings' => count($pending_listings),
                'pending_agencies' => count($pending_agencies),
                'approved_today' => (int)$approved_today,
                'rejected_today' => (int)$rejected_today
            ]
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

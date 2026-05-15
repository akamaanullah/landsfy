<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

// Enforce Admin Only
if ($_SESSION['role_name'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
session_write_close();

try {
    // 1. Stats Cards
    $total_props = $pdo->query("SELECT COUNT(*) FROM properties WHERE status != 'deleted'")->fetchColumn();
    $active_props = $pdo->query("SELECT COUNT(*) FROM properties WHERE status = 'active'")->fetchColumn();
    $pending_props = $pdo->query("SELECT COUNT(*) FROM properties WHERE status = 'under_review'")->fetchColumn();
    $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

    // 2. Recent Submissions
    $recent_stmt = $pdo->query("
        SELECT p.*, u.username as author_name,
               (SELECT image_url FROM property_images WHERE property_id = p.id AND is_main = 1 LIMIT 1) as featured_image
        FROM properties p 
        JOIN users u ON p.author_id = u.id 
        WHERE p.status != 'deleted'
        ORDER BY p.created_at DESC 
        LIMIT 5
    ");
    $recent_properties = $recent_stmt->fetchAll();

    // 3. New Users
    $new_users_stmt = $pdo->query("SELECT id, username, avatar_url, created_at FROM users ORDER BY created_at DESC LIMIT 4");
    $new_users = $new_users_stmt->fetchAll();

    // 4. Property Types for Chart
    $type_stats = $pdo->query("
        SELECT c.name as category, COUNT(*) as count 
        FROM properties p 
        JOIN property_categories c ON p.category_id = c.id 
        GROUP BY c.name
    ")->fetchAll();

    $chart_data = [];
    foreach($type_stats as $row) {
        if(!empty($row->category)) {
            $chart_data[strtolower($row->category)] = (int)$row->count;
        }
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'stats' => [
                'total' => (int)$total_props,
                'active' => (int)$active_props,
                'pending' => (int)$pending_props,
                'users' => (int)$total_users
            ],
            'recent_properties' => $recent_properties,
            'new_users' => $new_users,
            'chart_data' => $chart_data
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

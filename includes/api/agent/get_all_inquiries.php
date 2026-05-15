<?php
session_start();
header('Content-Type: application/json');

// 1. Auth check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_name'], ['agent', 'admin', 'agency_owner'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

require_once '../../database/db.php';

$current_agent_id = $_SESSION['user_id'];
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$action = isset($_GET['action']) ? trim($_GET['action']) : 'all';

try {
    // Base Query
    $query = "
        SELECT 
            pi.id, 
            pi.interaction_type, 
            pi.created_at,
            pi.user_id,
            p.title AS property_title,
            p.id AS property_id,
            u.username AS user_name,
            u.full_name AS user_full_name,
            u.avatar_url
        FROM property_interactions pi
        JOIN properties p ON pi.property_id = p.id
        LEFT JOIN users u ON pi.user_id = u.id
        WHERE p.author_id = :agent_id
        AND pi.interaction_type IN ('whatsapp_click', 'call_reveal')
    ";

    $params = [':agent_id' => $current_agent_id];

    // Filter by Action Type
    if ($action === 'whatsapp') {
        $query .= " AND pi.interaction_type = 'whatsapp_click'";
    } elseif ($action === 'call') {
        $query .= " AND pi.interaction_type = 'call_reveal'";
    }

    // Filter by Search (Property Title or Username)
    if (!empty($search)) {
        $query .= " AND (p.title LIKE :search OR u.username LIKE :search OR u.full_name LIKE :search)";
        $params[':search'] = "%$search%";
    }

    $query .= " ORDER BY pi.created_at DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $results
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>

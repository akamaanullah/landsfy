<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_name'], ['seller', 'admin'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

require_once '../../database/db.php';

$user_id = $_SESSION['user_id'];
$status = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

try {
    $sql = "
        SELECT 
            pi.id, pi.interaction_type, pi.created_at,
            p.title as property_title, p.price, p.location_name,
            u.full_name as buyer_name, u.email as buyer_email, u.phone as buyer_phone, u.avatar_url as buyer_avatar
        FROM property_interactions pi
        JOIN properties p ON pi.property_id = p.id
        LEFT JOIN users u ON pi.user_id = u.id
        WHERE p.author_id = :author_id AND pi.interaction_type IN ('whatsapp_click', 'call_reveal')
    ";
    
    $params = [':author_id' => $user_id];

    if (!empty($search)) {
        $sql .= " AND (u.full_name LIKE :search OR p.title LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }

    $sql .= " ORDER BY pi.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $leads]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}

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
session_write_close();

try {
    // 1. Get Total Properties
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM properties WHERE author_id = ? AND status != 'deleted'");
    $stmt->execute([$user_id]);
    $total_properties = $stmt->fetchColumn();

    // 2. Get Total Views
    $stmt = $pdo->prepare("SELECT SUM(views_total) FROM property_stats ps JOIN properties p ON ps.property_id = p.id WHERE p.author_id = ?");
    $stmt->execute([$user_id]);
    $total_views = $stmt->fetchColumn() ?: 0;

    // 3. Get Total Inquiries (Leads)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM property_interactions pi 
        JOIN properties p ON pi.property_id = p.id 
        WHERE p.author_id = ? AND pi.interaction_type IN ('whatsapp_click', 'call_reveal')
    ");
    $stmt->execute([$user_id]);
    $total_leads = $stmt->fetchColumn();

    // 4. Get Sold Properties
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM properties WHERE author_id = ? AND status = 'sold'");
    $stmt->execute([$user_id]);
    $total_sold = $stmt->fetchColumn();

    // 5. Get Recent Leads
    $stmt = $pdo->prepare("
        SELECT 
            pi.id, pi.interaction_type, pi.created_at,
            p.title as property_title, p.price,
            u.full_name as buyer_name, u.avatar_url as buyer_avatar
        FROM property_interactions pi
        JOIN properties p ON pi.property_id = p.id
        LEFT JOIN users u ON pi.user_id = u.id
        WHERE p.author_id = ? AND pi.interaction_type IN ('whatsapp_click', 'call_reveal')
        ORDER BY pi.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $recent_leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 6. Get Views Share (Top Properties by Views)
    $stmt = $pdo->prepare("
        SELECT p.title, ps.views_total 
        FROM property_stats ps 
        JOIN properties p ON ps.property_id = p.id 
        WHERE p.author_id = ? AND p.status != 'deleted'
        ORDER BY ps.views_total DESC 
        LIMIT 3
    ");
    $stmt->execute([$user_id]);
    $views_share = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'stats' => [
            'total_properties' => $total_properties,
            'total_views' => $total_views,
            'total_leads' => $total_leads,
            'total_sold' => $total_sold
        ],
        'recent_leads' => $recent_leads,
        'views_share' => $views_share
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}

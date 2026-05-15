<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

if ($_SESSION['role_name'] !== 'buyer' && $_SESSION['role_name'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Fetch Leads (Messages)
    $leads_sql = "SELECT l.id, l.message, l.status, l.created_at, 'lead' as type,
                  p.title as property_title, p.slug as property_slug,
                  u.full_name as author_name, u.avatar_url as author_avatar
                  FROM leads l
                  JOIN properties p ON l.property_id = p.id
                  JOIN users u ON p.author_id = u.id
                  WHERE l.buyer_id = ?
                  ORDER BY l.created_at DESC";
    
    $stmt = $pdo->prepare($leads_sql);
    $stmt->execute([$user_id]);
    $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Interactions (WhatsApp/Call)
    $int_sql = "SELECT i.id, i.interaction_type as message, 'active' as status, i.created_at, 'interaction' as type,
                p.title as property_title, p.slug as property_slug,
                u.full_name as author_name, u.avatar_url as author_avatar
                FROM property_interactions i
                JOIN properties p ON i.property_id = p.id
                JOIN users u ON p.author_id = u.id
                WHERE i.user_id = ? AND i.interaction_type IN ('whatsapp_click', 'call_reveal')
                ORDER BY i.created_at DESC";
    
    $stmt = $pdo->prepare($int_sql);
    $stmt->execute([$user_id]);
    $interactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Merge and Sort
    $all_inquiries = array_merge($leads, $interactions);
    usort($all_inquiries, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

    echo json_encode([
        'success' => true,
        'inquiries' => $all_inquiries
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

if ($_SESSION['role_name'] !== 'buyer' && $_SESSION['role_name'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
session_write_close();

try {
    // 1. Total Saved Properties
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM saved_properties WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $total_saved = $stmt->fetchColumn();

    // 2. My Inquiries (Leads sent + Interaction clicks)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM leads WHERE buyer_id = ?");
    $stmt->execute([$user_id]);
    $total_leads = $stmt->fetchColumn();

    // 3. Interactions (WhatsApp/Calls)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM property_interactions WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $total_interactions = $stmt->fetchColumn();

    // 4. Viewed Today
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM property_interactions WHERE user_id = ? AND interaction_type = 'view' AND DATE(created_at) = CURDATE()");
    $stmt->execute([$user_id]);
    $viewed_today = $stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'stats' => [
            'total_saved' => $total_saved,
            'total_inquiries' => $total_leads + $total_interactions,
            'viewed_today' => $viewed_today
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

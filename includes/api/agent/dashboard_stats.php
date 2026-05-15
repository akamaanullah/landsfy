<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_name'], ['agent', 'admin', 'agency_owner'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

require_once '../../database/db.php';

$user_id = $_SESSION['user_id'];

try {
    // 1. My Listings Count
    $stmt = $pdo->prepare("SELECT COUNT(id) AS total_listings FROM properties WHERE author_id = ? AND status != 'deleted'");
    $stmt->execute([$user_id]);
    $listings_count = $stmt->fetch()->total_listings;

    // 2. WhatsApp Clicks & Calls
    $stmt = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN pi.interaction_type = 'whatsapp_click' THEN 1 ELSE 0 END) AS whatsapp_clicks,
            SUM(CASE WHEN pi.interaction_type = 'call_reveal' THEN 1 ELSE 0 END) AS call_inquiries
        FROM property_interactions pi
        JOIN properties p ON pi.property_id = p.id
        WHERE p.author_id = ? AND p.status != 'deleted'
    ");
    $stmt->execute([$user_id]);
    $engagements = $stmt->fetch();

    $whatsapp = $engagements->whatsapp_clicks ?? 0;
    $calls = $engagements->call_inquiries ?? 0;

    // 3. Agent Rating
    $stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating FROM agent_reviews WHERE agent_id = ?");
    $stmt->execute([$user_id]);
    $rating_res = $stmt->fetch();
    $rating = $rating_res->avg_rating ? number_format((float)$rating_res->avg_rating, 1) : "New";

    echo json_encode([
        'success' => true,
        'data' => [
            'total_listings' => (int)$listings_count,
            'whatsapp_clicks' => (int)$whatsapp,
            'call_inquiries' => (int)$calls,
            'agent_rating' => $rating
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
}
?>

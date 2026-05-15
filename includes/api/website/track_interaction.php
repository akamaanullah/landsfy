<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../database/db.php';

$response = ['success' => false];

try {
    if (session_status() === PHP_SESSION_NONE) session_start();
    
    $property_id = $_POST['property_id'] ?? 0;
    $type = $_POST['type'] ?? ''; // 'call_reveal', 'whatsapp_click'
    $user_id = $_SESSION['user_id'] ?? null;

    if (!$property_id || !in_array($type, ['call_reveal', 'whatsapp_click', 'share'])) {
        throw new Exception("Invalid parameters");
    }

    // 1. Record in interactions
    $stmt = $pdo->prepare("INSERT INTO property_interactions (property_id, user_id, interaction_type) VALUES (:prop_id, :user_id, :type)");
    $stmt->bindValue(':prop_id', $property_id, PDO::PARAM_INT);
    $stmt->bindValue(':type', $type, PDO::PARAM_STR);
    
    $user_id = !empty($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    if ($user_id === null) {
        $stmt->bindValue(':user_id', null, PDO::PARAM_NULL);
    } else {
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    }
    $stmt->execute();

    // 2. Increment leads count in stats (optional but good for tracking engagement)
    $stats_stmt = $pdo->prepare("
        INSERT INTO property_stats (property_id, leads_total) 
        VALUES (?, 1)
        ON DUPLICATE KEY UPDATE leads_total = leads_total + 1
    ");
    $stats_stmt->execute([$property_id]);

    $response['success'] = true;

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>

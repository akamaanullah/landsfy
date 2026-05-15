<?php
require_once '../../database/db.php';
require_once '../../helpers/notification_helper.php';

header('Content-Type: application/json');

$property_id = $_POST['property_id'] ?? null;
$user_id = $_POST['user_id'] ?? null; // Logged in user who clicks (if any)
$type = $_POST['type'] ?? 'view'; // view, whatsapp_click, call_reveal

if (!$property_id) {
    echo json_encode(['success' => false, 'message' => 'Property ID is required.']);
    exit;
}

try {
    // 1. Log the interaction
    $stmt = $pdo->prepare("INSERT INTO property_interactions (property_id, user_id, interaction_type) VALUES (?, ?, ?)");
    $stmt->execute([$property_id, $user_id, $type]);
    $interaction_id = $pdo->lastInsertId();

    // 2. Find the Property Author (Agent)
    $prop_stmt = $pdo->prepare("SELECT author_id, title FROM properties WHERE id = ?");
    $prop_stmt->execute([$property_id]);
    $property = $prop_stmt->fetch(PDO::FETCH_ASSOC);

    if ($property && $type !== 'view') {
        $agent_id = $property['author_id'];
        $is_whatsapp = ($type === 'whatsapp_click');
        $action_label = $is_whatsapp ? "WhatsApp" : "Call Reveal";
        
        $title = $action_label . " Inquiry: " . $property['title'];
        $message = "Someone just engaged with your property listing via " . $action_label . ". Track this activity in your Inquiry Tracker.";
        $link = "inquiries.php"; // Redirect to the tracker for better lead management

        // Create the notification for the agent
        createNotification($agent_id, $title, $message, 'inquiry', $property_id, 'property', $link);
    }

    echo json_encode(['success' => true, 'id' => $interaction_id]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../../database/db.php';

$user_id = $_SESSION['user_id'];
$tier = $_POST['tier'] ?? '';
$amount = $_POST['amount'] ?? 0;

if (!in_array($tier, ['platinum', 'diamond'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid tier selected']);
    exit;
}

if (!isset($_FILES['screenshot'])) {
    echo json_encode(['success' => false, 'message' => 'Payment screenshot is required']);
    exit;
}

try {
    // 1. Upload Screenshot
    $upload_dir = '../../../uploads/premium_proofs/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    $file_ext = pathinfo($_FILES['screenshot']['name'], PATHINFO_EXTENSION);
    $filename = 'proof_' . $user_id . '_' . time() . '.' . $file_ext;
    $target_path = $upload_dir . $filename;
    $db_path = 'uploads/premium_proofs/' . $filename;

    if (!move_uploaded_file($_FILES['screenshot']['tmp_name'], $target_path)) {
        throw new Exception("Failed to upload screenshot.");
    }

    // 2. Insert Request
    $stmt = $pdo->prepare("
        INSERT INTO premium_requests (user_id, request_type, amount_paid, payment_screenshot, status) 
        VALUES (?, ?, ?, ?, 'pending')
    ");
    
    $request_type = ($tier === 'platinum') ? 'platinum_credit' : 'diamond_credit';
    $stmt->execute([$user_id, $request_type, $amount, $db_path]);

    // 3. Log Activity
    $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action_type, description) VALUES (?, ?, ?)");
    $log_stmt->execute([$user_id, 'premium_request_submitted', "Requested $tier credit for PKR $amount"]);

    echo json_encode(['success' => true, 'message' => 'Your request has been submitted. It will be verified shortly.']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';
require_once '../../helpers/quota_helper.php';

// Enforce Admin Only
if ($_SESSION['role_name'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id = $_POST['id'] ?? null;
$status = $_POST['status'] ?? null;
$reason = $_POST['rejection_reason'] ?? null;

if (!$id || !$status) {
    echo json_encode(['success' => false, 'message' => 'Missing ID or Status']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Fetch Property Info
    $prop_stmt = $pdo->prepare("SELECT author_id, title, premium_type, premium_status FROM properties WHERE id = ?");
    $prop_stmt->execute([$id]);
    $prop = $prop_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$prop) throw new Exception("Property not found.");

    // 2. Handle Quota Deduction and Expiry on Approval
    if ($status === 'approved' && $prop['premium_type'] !== 'none') {
        // Only deduct if not already active
        if ($prop['premium_status'] !== 'active') {
            if (!hasSufficientQuota($pdo, $prop['author_id'], $prop['premium_type'])) {
                throw new Exception("Agent has insufficient quota for a " . ucfirst($prop['premium_type']) . " listing.");
            }
            
            // Deduct Quota
            if (!deductAgentQuota($pdo, $prop['author_id'], $prop['premium_type'])) {
                throw new Exception("Failed to deduct quota.");
            }
            
            // Mark Premium as Active and set Expiry (1 Month)
            $expiry = date('Y-m-d H:i:s', strtotime('+1 month'));
            $pdo->prepare("UPDATE properties SET premium_status = 'active', premium_expiry = ? WHERE id = ?")
                ->execute([$expiry, $id]);
        }
    }

    // 3. Update Property Status
    if ($status === 'rejected') {
        $stmt = $pdo->prepare("UPDATE properties SET status = ?, rejection_reason = ? WHERE id = ?");
        $stmt->execute([$status, $reason, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE properties SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
    }

    // 4. Trigger Notification
    $notif_title = $status === 'approved' ? 'Listing Approved!' : 'Listing Rejected';
    $notif_msg = "Your property listing '{$prop['title']}' has been " . ($status === 'approved' ? 'successfully approved and is now live.' : 'rejected by the admin.');
    if ($status === 'approved' && $prop['premium_type'] !== 'none') {
        $notif_msg .= " It is now active as a " . ucfirst($prop['premium_type']) . " listing for 1 month.";
    }
    if ($reason) $notif_msg .= " Reason: $reason";
    $notif_type = "property_" . $status;

    $notif_sql = "INSERT INTO notifications (user_id, sender_id, title, message, type, is_read, created_at) 
                  VALUES (?, ?, ?, ?, ?, 0, CURRENT_TIMESTAMP)";
    $pdo->prepare($notif_sql)->execute([
        $prop['author_id'],
        $_SESSION['user_id'],
        $notif_title,
        $notif_msg,
        $notif_type
    ]);

    // 5. Log Activity
    $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action_type, target_id, description) VALUES (?, ?, ?, ?)");
    $desc = "Property #$id status updated to $status";
    $log_stmt->execute([$_SESSION['user_id'], 'property_status_update', $id, $desc]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Status updated successfully']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

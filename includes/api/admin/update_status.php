<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

if ($_SESSION['role_name'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';
$id = $_POST['id'] ?? null;

if (!$id && $action !== 'bulk_approve') {
    echo json_encode(['success' => false, 'message' => 'ID required']);
    exit;
}

try {
    switch ($action) {
        case 'agency_verify':
            $stmt = $pdo->prepare("UPDATE agencies SET is_verified = 1, status = 'active' WHERE id = ?");
            $stmt->execute([$id]);
            $pdo->prepare("INSERT INTO activity_logs (user_id, action_type, description, target_id) VALUES (?, ?, ?, ?)")
                ->execute([$_SESSION['user_id'], 'agency_verified', "Agency ID $id verified and activated.", $id]);
            break;

        case 'agency_delete':
            $pdo->prepare("DELETE FROM agencies WHERE id = ?")->execute([$id]);
            $pdo->prepare("INSERT INTO activity_logs (user_id, action_type, description, target_id) VALUES (?, ?, ?, ?)")
                ->execute([$_SESSION['user_id'], 'agency_deleted', "Agency ID $id permanently deleted.", $id]);
            break;

        case 'user_suspend':
            $pdo->prepare("UPDATE users SET status = 'suspended' WHERE id = ?")->execute([$id]);
            $pdo->prepare("INSERT INTO activity_logs (user_id, action_type, description, target_id) VALUES (?, ?, ?, ?)")
                ->execute([$_SESSION['user_id'], 'user_suspended', "User ID $id account suspended.", $id]);
            break;

        case 'user_activate':
            $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?")->execute([$id]);
            $pdo->prepare("INSERT INTO activity_logs (user_id, action_type, description, target_id) VALUES (?, ?, ?, ?)")
                ->execute([$_SESSION['user_id'], 'user_activated', "User ID $id account reactivated.", $id]);
            break;

        case 'listing_approve':
            // 1. Fetch property details
            $prop_stmt = $pdo->prepare("SELECT author_id, premium_type, premium_status FROM properties WHERE id = ?");
            $prop_stmt->execute([$id]);
            $property = $prop_stmt->fetch(PDO::FETCH_ASSOC);

            if ($property) {
                // 2. Approve the property
                $pdo->prepare("UPDATE properties SET status = 'active' WHERE id = ?")->execute([$id]);

                // 3. Update Premium Status (Deduction already happened during listing)
                if ($property['premium_type'] !== 'none' && $property['premium_status'] === 'pending') {
                    // Set expiry (e.g., 30 days)
                    $pdo->prepare("UPDATE properties SET premium_status = 'active', premium_expiry = DATE_ADD(NOW(), INTERVAL 30 DAY) WHERE id = ?")->execute([$id]);
                }

                // 4. Log action
                $pdo->prepare("INSERT INTO activity_logs (user_id, action_type, description, target_id) VALUES (?, ?, ?, ?)")
                    ->execute([$_SESSION['user_id'], 'property_approved', "Property ID $id approved and visibility activated.", $id]);
            }
            break;

        case 'listing_reject':
            $reason = trim($_POST['reason'] ?? 'Policy violation');
            
            // 1. Fetch details for refund
            $prop_stmt = $pdo->prepare("SELECT author_id, premium_type, premium_status FROM properties WHERE id = ?");
            $prop_stmt->execute([$id]);
            $property = $prop_stmt->fetch(PDO::FETCH_ASSOC);

            // 2. Reject the property
            $pdo->prepare("UPDATE properties SET status = 'rejected' WHERE id = ?")->execute([$id]);

            // 3. Handle Refund
            if ($property && $property['premium_type'] !== 'none' && $property['premium_status'] === 'pending') {
                require_once '../../helpers/quota_helper.php';
                if (refundAgentQuota($pdo, $property['author_id'], $property['premium_type'])) {
                    // Update premium status to indicate it was refunded/rejected
                    $pdo->prepare("UPDATE properties SET premium_status = 'rejected' WHERE id = ?")->execute([$id]);
                    
                    // Log the refund
                    $pdo->prepare("INSERT INTO activity_logs (user_id, action_type, description, target_id) VALUES (?, ?, ?, ?)")
                        ->execute([$_SESSION['user_id'], 'quota_refunded', "1 {$property['premium_type']} credit refunded for rejected property ID: $id", $id]);
                }
            }

            // 4. Log rejection
            $pdo->prepare("INSERT INTO activity_logs (user_id, action_type, description, target_id) VALUES (?, ?, ?, ?)")
                ->execute([$_SESSION['user_id'], 'property_rejected', "Property ID $id rejected. Reason: $reason", $id]);
            break;

        case 'listing_delete':
            $pdo->prepare("UPDATE properties SET status = 'deleted' WHERE id = ?")->execute([$id]);
            $pdo->prepare("INSERT INTO activity_logs (user_id, action_type, description, target_id) VALUES (?, ?, ?, ?)")
                ->execute([$_SESSION['user_id'], 'property_deleted', "Property ID $id marked as deleted.", $id]);
            break;

        default:
            throw new Exception("Invalid status action: $action");
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

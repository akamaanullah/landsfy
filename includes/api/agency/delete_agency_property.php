<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

// Allow Agency Owners, Admins, and Agents
if (!in_array($_SESSION['role_name'], ['agency_owner', 'admin', 'agent'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role_name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $property_id = $_POST['id'] ?? null;

    try {
        if (!$property_id) {
            throw new Exception("Property ID is required.");
        }

        $agency_id = null;
        $owner_id = null;

        // Fetch Agency ID and Owner ID
        if ($role === 'agency_owner') {
            $stmt = $pdo->prepare("SELECT id, owner_id FROM agencies WHERE owner_id = ?");
            $stmt->execute([$user_id]);
            $agency = $stmt->fetch();
            if ($agency) {
                $agency_id = $agency->id;
                $owner_id = $agency->owner_id;
            }
        } elseif ($role === 'agent') {
            $stmt = $pdo->prepare("
                SELECT a.agency_id, ag.owner_id 
                FROM agents a 
                JOIN agencies ag ON a.agency_id = ag.id 
                WHERE a.user_id = ?
            ");
            $stmt->execute([$user_id]);
            $res = $stmt->fetch();
            if ($res) {
                $agency_id = $res->agency_id;
                $owner_id = $res->owner_id;
            }
        }

        if (!$agency_id) {
            throw new Exception("Agency not found or unauthorized.");
        }

        // Fetch property title for notification before deletion
        $prop_stmt = $pdo->prepare("SELECT title FROM properties WHERE id = ? AND agency_id = ?");
        $prop_stmt->execute([$property_id, $agency_id]);
        $prop_title = $prop_stmt->fetchColumn();

        if (!$prop_title) {
            throw new Exception("Property not found or not authorized to delete.");
        }

        $pdo->beginTransaction();

        // 2. Delete Property (ensure it belongs to this agency)
        // Note: You might want to soft-delete (status='deleted') instead of hard delete
        $delete_stmt = $pdo->prepare("UPDATE properties SET status = 'deleted', updated_at = NOW() WHERE id = ? AND agency_id = ?");
        $delete_stmt->execute([$property_id, $agency_id]);

        if ($delete_stmt->rowCount() > 0) {
            // 3. Create Notification for Owner (if an agent deleted it)
            if ($role === 'agent' && $owner_id) {
                $notif_stmt = $pdo->prepare("
                    INSERT INTO notifications (user_id, sender_id, title, message, type, reference_id, reference_type)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $msg = "Agent " . $_SESSION['full_name'] . " deleted property: " . $prop_title;
                $notif_stmt->execute([
                    $owner_id, 
                    $user_id, 
                    "Property Deleted", 
                    $msg, 
                    "property_deleted", 
                    $property_id, 
                    "property"
                ]);
            }

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Property deleted successfully']);
        } else {
            throw new Exception("Failed to delete property.");
        }

    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

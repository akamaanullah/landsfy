<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

if ($_SESSION['role_name'] !== 'agency_owner' && $_SESSION['role_name'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $description = $_POST['description'] ?? '';
    $address = $_POST['address'] ?? '';

    try {
        // Fetch Agency ID
        $stmt = $pdo->prepare("SELECT id FROM agencies WHERE owner_id = ?");
        $stmt->execute([$user_id]);
        $agency = $stmt->fetch();

        if (!$agency) {
            throw new Exception("Agency not found.");
        }

        $pdo->beginTransaction();

        // 1. Update Agency Info
        $stmt = $pdo->prepare("
            UPDATE agencies 
            SET name = ?, phone = ?, email = ?, description = ?, address = ? 
            WHERE id = ?
        ");
        $stmt->execute([$name, $phone, $email, $description, $address, $agency->id]);

        // 2. Handle Password Change (Optional)
        if (!empty($_POST['new_password'])) {
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if ($new_password !== $confirm_password) {
                throw new Exception("New passwords do not match.");
            }

            // Fetch current password hash from users table
            $user_stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
            $user_stmt->execute([$user_id]);
            $user = $user_stmt->fetch();

            if (!$user || !password_verify($current_password, $user->password_hash)) {
                throw new Exception("Current password is incorrect.");
            }

            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_pass = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $update_pass->execute([$hashed_password, $user_id]);
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

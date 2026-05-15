<?php
require_once '../../auth_check.php';
require_once '../../database/db.php';

header('Content-Type: application/json');

if ($_SESSION['role_name'] != 'seller' && $_SESSION['role_name'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $curr_pass = $_POST['current_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $conf_pass = $_POST['confirm_password'] ?? '';

    try {
        if (empty($curr_pass) || empty($new_pass) || empty($conf_pass)) {
            throw new Exception("All password fields are required.");
        }

        if ($new_pass !== $conf_pass) {
            throw new Exception("New passwords do not match.");
        }

        if (strlen($new_pass) < 6) {
            throw new Exception("New password must be at least 6 characters.");
        }

        // 1. Fetch current password hash
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!$user) {
            throw new Exception("User not found.");
        }

        // 2. Verify current password
        if (!password_verify($curr_pass, $user->password_hash)) {
            throw new Exception("Incorrect current password.");
        }

        // 3. Update with new hash
        $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $upd = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $upd->execute([$new_hash, $user_id]);

        echo json_encode([
            'success' => true, 
            'message' => 'Password updated successfully.'
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

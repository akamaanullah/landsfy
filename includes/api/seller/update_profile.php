<?php
session_start();
header('Content-Type: application/json');

// 1. Auth check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_name'], ['seller', 'admin'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

require_once '../../database/db.php';
require_once '../../helpers/file_upload.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 2. Collect & Validate Basic Info
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $cnic = trim($_POST['cnic'] ?? '');

    if (empty($full_name) || empty($email)) {
        throw new Exception("Full Name and Email are required.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format.");
    }

    // 3. Update User Table
    $user_sql = "UPDATE users SET full_name = ?, email = ?, phone = ?, updated_at = NOW() WHERE id = ?";
    $user_stmt = $pdo->prepare($user_sql);
    $user_stmt->execute([$full_name, $email, $phone, $user_id]);

    // 4. Handle Avatar Upload
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
        $upload_dir = '../../../uploads/avatars/';
        $prefix = 'avatar_seller_' . $user_id . '_';
        
        $db_path = FileUploadHelper::secureUpload($_FILES['avatar'], $upload_dir, $prefix, 'image');
        if ($db_path) {
            // Update DB
            $upd_avatar = $pdo->prepare("UPDATE users SET avatar_url = ? WHERE id = ?");
            $upd_avatar->execute([$db_path, $user_id]);
            
            // Update Session
            $_SESSION['avatar_url'] = $db_path; 
        }
    }

    $pdo->commit();
    
    // Update basic session info
    $_SESSION['username'] = $full_name; // Syncing display name

    echo json_encode([
        'success' => true, 
        'message' => 'Profile updated successfully!',
        'avatar_url' => $_SESSION['avatar_url'] ?? null,
        'full_name' => $full_name
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

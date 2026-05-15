<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';
require_once '../../helpers/file_upload.php';

if ($_SESSION['role_name'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$new_password = $_POST['new_password'] ?? '';

if (!$full_name || !$email) {
    echo json_encode(['success' => false, 'message' => 'Required fields missing']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Handle Avatar Upload
    $avatar_url = $_SESSION['avatar_url'] ?? null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../../uploads/avatars/';
        $prefix = 'admin_' . $user_id . '_';
        
        $db_path = FileUploadHelper::secureUpload($_FILES['avatar'], $upload_dir, $prefix, 'image');
        if ($db_path) {
            $avatar_url = $db_path;
        }
    }

    // 2. Base Info Update
    $update_query = "UPDATE users SET full_name = ?, email = ?, phone = ?, avatar_url = ? WHERE id = ?";
    $params = [$full_name, $email, $phone, $avatar_url, $user_id];
    
    $pdo->prepare($update_query)->execute($params);

    // 3. Password Update if provided
    if (!empty($new_password)) {
        if (strlen($new_password) < 8) {
            throw new Exception("Password must be at least 8 characters");
        }
        $hashed_pass = password_hash($new_password, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$hashed_pass, $user_id]);
    }

    $pdo->commit();

    // Update Session
    $_SESSION['full_name'] = $full_name;
    $_SESSION['email'] = $email;
    $_SESSION['phone'] = $phone;
    $_SESSION['avatar_url'] = $avatar_url;

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

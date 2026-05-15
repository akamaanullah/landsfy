<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';
require_once '../../helpers/file_upload.php';

if ($_SESSION['role_name'] !== 'buyer' && $_SESSION['role_name'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

try {
    if ($action === 'update_profile') {
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (!$full_name || !$email) {
            throw new Exception("Full Name and Email are required.");
        }

        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->execute([$full_name, $email, $phone, $user_id]);

        // Update session
        $_SESSION['full_name'] = $full_name;
        $_SESSION['email'] = $email;

        echo json_encode(['success' => true]);
    } 
    else if ($action === 'update_avatar') {
        if (!isset($_FILES['avatar'])) {
            throw new Exception("No image uploaded.");
        }

        $target_dir = "../../../uploads/avatars/";
        $prefix = "avatar_" . $user_id . "_";
        
        // Use secure upload
        $db_path = FileUploadHelper::secureUpload($_FILES['avatar'], $target_dir, $prefix, 'image');

        if ($db_path) {
            $stmt = $pdo->prepare("UPDATE users SET avatar_url = ? WHERE id = ?");
            $stmt->execute([$db_path, $user_id]);
            
            $_SESSION['avatar_url'] = $db_path;
            echo json_encode(['success' => true, 'avatar_url' => '../' . $db_path]);
        } else {
            throw new Exception("Failed to save image.");
        }
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

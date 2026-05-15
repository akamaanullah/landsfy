<?php
session_start();
header('Content-Type: application/json');

// 1. Auth check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_name'], ['agent', 'admin', 'agency_owner'])) {
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
    $bio = trim($_POST['bio'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $experience_years = intval($_POST['experience_years'] ?? 0);

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

    // 4. Update Agent Table
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $full_name))) . '-' . $user_id;

    // Check if agent record exists (it should, but safety first)
    $check_stmt = $pdo->prepare("SELECT id FROM agents WHERE user_id = ?");
    $check_stmt->execute([$user_id]);
    if ($check_stmt->fetch()) {
        $agent_sql = "UPDATE agents SET bio = ?, specialization = ?, experience_years = ?, slug = ? WHERE user_id = ?";
        $agent_stmt = $pdo->prepare($agent_sql);
        $agent_stmt->execute([$bio, $specialization, $experience_years, $slug, $user_id]);
    } else {
        $agent_sql = "INSERT INTO agents (user_id, bio, specialization, experience_years, slug) VALUES (?, ?, ?, ?, ?)";
        $agent_stmt = $pdo->prepare($agent_sql);
        $agent_stmt->execute([$user_id, $bio, $specialization, $experience_years, $slug]);
    }

    // 5. Handle Avatar Upload
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
        $upload_dir = '../../../uploads/avatars/';
        $prefix = 'avatar_' . $user_id . '_';
        
        $db_path = FileUploadHelper::secureUpload($_FILES['avatar'], $upload_dir, $prefix, 'image');
        if ($db_path) {
            // Update DB
            $upd_avatar = $pdo->prepare("UPDATE users SET avatar_url = ? WHERE id = ?");
            $upd_avatar->execute([$db_path, $user_id]);
            
            // Update Session
            $_SESSION['avatar_url'] = $db_path; 
        }
    }

    // 6. Handle Password Change
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';
    $curr_pass = $_POST['current_password'] ?? '';

    if (!empty($new_pass)) {
        if (empty($curr_pass)) {
            throw new Exception("Current password is required to set a new one.");
        }
        if ($new_pass !== $confirm_pass) {
            throw new Exception("New passwords do not match.");
        }
        if (strlen($new_pass) < 6) {
            throw new Exception("New password must be at least 6 characters.");
        }

        // Verify current password
        $pass_stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
        $pass_stmt->execute([$user_id]);
        $stored_hash = $pass_stmt->fetchColumn();

        if (!password_verify($curr_pass, $stored_hash)) {
            throw new Exception("Incorrect current password.");
        }

        // Hash and Update
        $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $upd_pass = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $upd_pass->execute([$new_hash, $user_id]);
    }

    $pdo->commit();
    
    // Update basic session info
    $_SESSION['username'] = $full_name; // Or keep login username, usually better to update full_name in session if used for greeting

    echo json_encode([
        'success' => true, 
        'message' => 'Profile updated successfully!',
        'avatar_url' => $_SESSION['avatar_url'] ?? null
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

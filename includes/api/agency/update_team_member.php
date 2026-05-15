<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

if ($_SESSION['role_name'] !== 'agency_owner' && $_SESSION['role_name'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$owner_id = $_SESSION['user_id'];

try {
    $user_id = $_POST['user_id'] ?? null;
    $full_name = $_POST['full_name'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!$user_id || empty($full_name) || empty($username) || empty($email)) {
        throw new Exception("Missing required fields.");
    }

    // 1. Fetch Agency ID to verify if this user is an agent of this agency
    $owner_agency_stmt = $pdo->prepare("SELECT id FROM agencies WHERE owner_id = ?");
    $owner_agency_stmt->execute([$owner_id]);
    $agency = $owner_agency_stmt->fetch();
    
    if (!$agency) throw new Exception("Agency not found.");
    $agency_id = $agency->id;

    // 2. Check if agent belongs to this agency
    $check_agent = $pdo->prepare("SELECT id FROM agents WHERE user_id = ? AND agency_id = ?");
    $check_agent->execute([$user_id, $agency_id]);
    if (!$check_agent->fetch()) {
        throw new Exception("Unauthorized: Agent does not belong to your agency.");
    }

    // 3. Check for unique email/username (excluding current user)
    $check_unique = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $check_unique->execute([$username, $email, $user_id]);
    if ($check_unique->fetch()) {
        throw new Exception("Username or Email already taken by another user.");
    }

    $pdo->beginTransaction();

    // 4. Update core info
    $update = $pdo->prepare("
        UPDATE users 
        SET full_name = ?, username = ?, email = ?, phone = ? 
        WHERE id = ?
    ");
    $update->execute([$full_name, $username, $email, $phone, $user_id]);

    // 5. Update password if provided
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update_pass = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $update_pass->execute([$hashed_password, $user_id]);
    }

    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Team member updated successfully.']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

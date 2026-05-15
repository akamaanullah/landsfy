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
    // 1. Fetch Agency ID
    $stmt = $pdo->prepare("SELECT id FROM agencies WHERE owner_id = ?");
    $stmt->execute([$owner_id]);
    $agency = $stmt->fetch();

    if (!$agency) {
        throw new Exception("Agency not found.");
    }
    $agency_id = $agency->id;

    // 2. Validate Input
    $full_name = $_POST['full_name'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    $role_id = 3; // Role ID 3 is 'agent'

    if (empty($full_name) || empty($username) || empty($email) || empty($password)) {
        throw new Exception("All fields are required.");
    }

    // 3. Check if username or email exists
    $check = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $check->execute([$username, $email]);
    if ($check->fetch()) {
        throw new Exception("Username or Email already exists.");
    }

    $pdo->beginTransaction();

    // 4. Create User
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $create_user = $pdo->prepare("
        INSERT INTO users (full_name, username, email, phone, password_hash, status) 
        VALUES (?, ?, ?, ?, ?, 'active')
    ");
    $create_user->execute([$full_name, $username, $email, $phone, $hashed_password]);
    $new_user_id = $pdo->lastInsertId();

    // 5. Assign Role (user_roles table)
    $assign_role = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
    $assign_role->execute([$new_user_id, $role_id]);

    // 6. Create Agent Entry
    $create_agent = $pdo->prepare("
        INSERT INTO agents (user_id, agency_id) 
        VALUES (?, ?)
    ");
    $create_agent->execute([$new_user_id, $agency_id]);

    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Team member added successfully.']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

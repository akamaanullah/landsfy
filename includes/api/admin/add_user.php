<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

if ($_SESSION['role_name'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$full_name = trim($_POST['full_name'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role_name = $_POST['role'] ?? 'agent';

if (!$full_name || !$username || !$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Check if username or email exists
    $check = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $check->execute([$username, $email]);
    if ($check->fetch()) {
        throw new Exception("Username or Email already exists");
    }

    // 2. Hash Password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 3. Insert User
    $stmt = $pdo->prepare("INSERT INTO users (full_name, username, email, password_hash, status, created_at) VALUES (?, ?, ?, ?, 'active', NOW())");
    $stmt->execute([$full_name, $username, $email, $hashed_password]);
    $user_id = $pdo->lastInsertId();

    // 4. Assign Role
    $rol_stmt = $pdo->prepare("SELECT id FROM roles WHERE role_name = ?");
    $rol_stmt->execute([$role_name]);
    $role_id = $rol_stmt->fetchColumn();

    if (!$role_id) {
        throw new Exception("Invalid role selected");
    }

    $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)")->execute([$user_id, $role_id]);
    
    // 5. If Agent, require agency_id and insert into agents table
    if ($role_name === 'agent') {
        $agency_id = $_POST['agency_id'] ?? null;
        if (!$agency_id) {
            throw new Exception("Agency Selection is required for Agents");
        }
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $full_name))) . '-' . $user_id;
        $pdo->prepare("INSERT INTO agents (user_id, agency_id, slug, created_at) VALUES (?, ?, ?, NOW())")
            ->execute([$user_id, $agency_id, $slug]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'User created successfully']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

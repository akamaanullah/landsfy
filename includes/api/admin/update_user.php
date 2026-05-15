<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

if ($_SESSION['role_name'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$role_name = $_POST['role'] ?? '';
$status = $_POST['status'] ?? 'active';
$password = $_POST['password'] ?? '';

if (!$id || !$full_name || !$email) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Update User Basic Info
    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, status = ? WHERE id = ?");
    $stmt->execute([$full_name, $email, $status, $id]);

    // 2. Update Password if provided
    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$hashed, $id]);
    }

    // 3. Update Role if provided
    if ($role_name) {
        // Get Role ID
        $rol_stmt = $pdo->prepare("SELECT id FROM roles WHERE role_name = ?");
        $rol_stmt->execute([$role_name]);
        $role_id = $rol_stmt->fetchColumn();

        if ($role_id) {
            // Delete old roles
            $pdo->prepare("DELETE FROM user_roles WHERE user_id = ?")->execute([$id]);
            // Insert new role
            $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)")->execute([$id, $role_id]);

            // Handle Agent specific table
            if ($role_name === 'agent') {
                $agency_id = $_POST['agency_id'] ?? null;
                if (!$agency_id) {
                    throw new Exception("Agency Selection is required for Agents");
                }
                
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $full_name))) . '-' . $id;

                // Upsert into agents table
                $check_agent = $pdo->prepare("SELECT user_id FROM agents WHERE user_id = ?");
                $check_agent->execute([$id]);
                if ($check_agent->fetch()) {
                    $pdo->prepare("UPDATE agents SET agency_id = ?, slug = ? WHERE user_id = ?")->execute([$agency_id, $slug, $id]);
                } else {
                    $pdo->prepare("INSERT INTO agents (user_id, agency_id, slug, created_at) VALUES (?, ?, ?, NOW())")->execute([$id, $agency_id, $slug]);
                }
            } else {
                // Remove from agents table if not an agent anymore
                $pdo->prepare("DELETE FROM agents WHERE user_id = ?")->execute([$id]);
            }
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'User updated successfully']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

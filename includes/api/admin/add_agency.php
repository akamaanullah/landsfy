<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

if ($_SESSION['role_name'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$owner_id = (int)($_POST['owner_id'] ?? 0);
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$address = trim($_POST['address'] ?? '');

if (!$name || !$owner_id) {
    echo json_encode(['success' => false, 'message' => 'Agency Name and Owner are required']);
    exit;
}

try {
    // 1. Check if user exists and is an agency_owner
    $check_stmt = $pdo->prepare("
        SELECT COUNT(*) FROM user_roles ur 
        JOIN roles r ON ur.role_id = r.id 
        WHERE ur.user_id = ? AND r.role_name = 'agency_owner'
    ");
    $check_stmt->execute([$owner_id]);
    if ($check_stmt->fetchColumn() == 0) {
        throw new Exception("The selected user must have the 'Agency Owner' role.");
    }

    // 2. Check if agency name already exists
    $name_check = $pdo->prepare("SELECT COUNT(*) FROM agencies WHERE name = ?");
    $name_check->execute([$name]);
    if ($name_check->fetchColumn() > 0) {
        throw new Exception("An agency with this name already exists.");
    }

    // 3. Generate Slug
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name))) . '-' . time();

    // 4. Insert Agency
    $stmt = $pdo->prepare("
        INSERT INTO agencies (owner_id, name, slug, phone, email, address, status) 
        VALUES (?, ?, ?, ?, ?, ?, 'under_review')
    ");
    $stmt->execute([$owner_id, $name, $slug, $phone, $email, $address]);

    echo json_encode(['success' => true, 'message' => 'Agency created successfully']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

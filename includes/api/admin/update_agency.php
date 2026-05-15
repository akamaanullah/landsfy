<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

if ($_SESSION['role_name'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$website = trim($_POST['website'] ?? '');
$address = trim($_POST['address'] ?? '');
$bio = trim($_POST['bio'] ?? '');

if (!$id || !$name) {
    echo json_encode(['success' => false, 'message' => 'Agency ID and Name are required']);
    exit;
}

try {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name))) . '-' . $id;

    $stmt = $pdo->prepare("
        UPDATE agencies 
        SET name = ?, slug = ?, email = ?, phone = ?, website = ?, address = ?, bio = ?
        WHERE id = ?
    ");
    $stmt->execute([$name, $slug, $email, $phone, $website, $address, $bio, $id]);

    if ($stmt->rowCount() >= 0) {
        echo json_encode(['success' => true, 'message' => 'Agency updated successfully']);
    } else {
        throw new Exception("No changes made or agency not found");
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

$city_id = $_GET['city_id'] ?? null;
$search = $_GET['search'] ?? null;

if (!$city_id) {
    echo json_encode(['success' => false, 'message' => 'City ID required']);
    exit;
}

try {
    if ($search) {
        $stmt = $pdo->prepare("SELECT id, name, slug FROM locations WHERE city_id = ? AND name LIKE ? ORDER BY name ASC LIMIT 10");
        $stmt->execute([$city_id, "%$search%"]);
    } else {
        $stmt = $pdo->prepare("SELECT id, name, slug FROM locations WHERE city_id = ? ORDER BY name ASC");
        $stmt->execute([$city_id]);
    }
    $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $locations
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

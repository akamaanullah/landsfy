<?php
require_once '../../auth_check.php';
require_once '../../database/db.php';

header('Content-Type: application/json');

$city_id = $_GET['city_id'] ?? null;
$search = $_GET['search'] ?? '';

if (!$city_id) {
    echo json_encode(['success' => false, 'message' => 'City ID is required.']);
    exit;
}

try {
    $sql = "SELECT id, name FROM locations WHERE city_id = ? AND name LIKE ? ORDER BY name ASC LIMIT 10";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$city_id, "%$search%"]);
    $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $locations]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}

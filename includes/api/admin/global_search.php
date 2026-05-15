<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

if ($_SESSION['role_name'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$query = trim($_GET['q'] ?? '');
if (strlen($query) < 2) {
    echo json_encode(['success' => true, 'data' => []]);
    exit;
}

try {
    $results = [
        'properties' => [],
        'users' => [],
        'agencies' => []
    ];

    $search_term = "%$query%";

    // 1. Search Properties
    $prop_stmt = $pdo->prepare("
        SELECT p.id, p.title, p.price, p.status, 'property' as type 
        FROM properties p
        LEFT JOIN cities c ON p.city_id = c.id
        LEFT JOIN locations l ON p.location_id = l.id
        WHERE p.title LIKE ? OR l.name LIKE ? OR c.name LIKE ? OR p.description LIKE ?
        LIMIT 5
    ");
    $prop_stmt->execute([$search_term, $search_term, $search_term, $search_term]);
    $results['properties'] = $prop_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Search Users
    $user_stmt = $pdo->prepare("
        SELECT id, full_name, username, email, 'user' as type 
        FROM users 
        WHERE full_name LIKE ? OR username LIKE ? OR email LIKE ?
        LIMIT 5
    ");
    $user_stmt->execute([$search_term, $search_term, $search_term]);
    $results['users'] = $user_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Search Agencies
    $agency_stmt = $pdo->prepare("
        SELECT id, name, address as location_name, 'agency' as type 
        FROM agencies 
        WHERE name LIKE ? OR address LIKE ? OR email LIKE ?
        LIMIT 5
    ");
    $agency_stmt->execute([$search_term, $search_term, $search_term]);
    $results['agencies'] = $agency_stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $results]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

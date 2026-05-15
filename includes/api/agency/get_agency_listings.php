<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

if ($_SESSION['role_name'] !== 'agency_owner' && $_SESSION['role_name'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // 1. Fetch Agency ID
    $stmt = $pdo->prepare("SELECT id FROM agencies WHERE owner_id = ?");
    $stmt->execute([$user_id]);
    $agency = $stmt->fetch();

    if (!$agency) {
        throw new Exception("Agency not found.");
    }
    $agency_id = $agency->id;

    // 2. Fetch Listings
    $query = "SELECT p.*, pi.image_url, u.full_name as agent_name 
              FROM properties p 
              LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_main = 1
              LEFT JOIN users u ON p.author_id = u.id
              WHERE p.agency_id = ? AND p.status != 'deleted'";
    
    $params = [$agency_id];

    // Optional Filters
    if (!empty($_GET['search'])) {
        $search = '%' . $_GET['search'] . '%';
        $query .= " AND (p.title LIKE ? OR p.location_name LIKE ? OR u.full_name LIKE ?)";
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
    }
    if (!empty($_GET['type'])) {
        $query .= " AND p.category_id = ?";
        $params[] = $_GET['type'];
    }
    if (!empty($_GET['purpose'])) {
        $query .= " AND p.purpose = ?";
        $params[] = $_GET['purpose'];
    }
    if (!empty($_GET['status'])) {
        $query .= " AND p.status = ?";
        $params[] = $_GET['status'];
    }

    $query .= " ORDER BY p.created_at DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $listings = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'listings' => $listings
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

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

    // 2. Fetch Team Members
    $query = "
        SELECT 
            a.id as agent_id, 
            u.id as user_id,
            u.full_name,
            u.username,
            u.email, 
            u.phone, 
            u.avatar_url, 
            u.status,
            a.specialization,
            a.experience_years,
            (SELECT COUNT(*) FROM properties WHERE author_id = u.id AND status != 'deleted') as listing_count
        FROM agents a
        JOIN users u ON a.user_id = u.id
        WHERE a.agency_id = ?
    ";
    
    $params = [$agency_id];

    // Optional Search
    if (!empty($_GET['search'])) {
        $search = '%' . $_GET['search'] . '%';
        $query .= " AND (u.full_name LIKE ? OR u.email LIKE ?)";
        $params[] = $search;
        $params[] = $search;
    }

    $query .= " ORDER BY listing_count DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $team = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'team' => $team
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../database/db.php';

try {
    $search = $_GET['search'] ?? '';
    $city = $_GET['city'] ?? '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 12;
    $offset = ($page - 1) * $limit;

    $params = [];
    $whereClauses = ["a.status = 'active'"];

    if (!empty($search)) {
        $whereClauses[] = "a.name LIKE ?";
        $params[] = "%$search%";
    }

    if (!empty($city)) {
        $whereClauses[] = "c.slug = ?";
        $params[] = $city;
    }

    $whereSql = implode(" AND ", $whereClauses);

    // 1. Get Total Count for Pagination
    $countStmt = $pdo->prepare("
        SELECT COUNT(DISTINCT a.id) 
        FROM agencies a
        LEFT JOIN users u ON a.owner_id = u.id
        LEFT JOIN properties p ON p.agency_id = a.id
        LEFT JOIN cities c ON p.city_id = c.id
        WHERE $whereSql
    ");
    $countStmt->execute($params);
    $totalAgencies = $countStmt->fetchColumn();

    // 2. Fetch Agencies with Stats (Optimized)
    $stmt = $pdo->prepare("
        SELECT a.id, a.name, a.slug, a.logo_url, a.is_verified, a.address, a.phone,
               (SELECT COUNT(*) FROM properties WHERE agency_id = a.id AND status = 'active') as property_count,
               (SELECT COUNT(*) FROM agents WHERE agency_id = a.id) as agent_count,
               COALESCE((SELECT AVG(rating) FROM agent_reviews ar JOIN agents ag ON ar.agent_id = ag.user_id WHERE ag.agency_id = a.id), 0) as avg_rating
        FROM agencies a
        LEFT JOIN cities c ON EXISTS (SELECT 1 FROM properties WHERE agency_id = a.id AND city_id = c.id)
        WHERE $whereSql
        GROUP BY a.id
        ORDER BY a.is_verified DESC, a.created_at DESC
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute($params);
    $agencies = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => $agencies,
        'meta' => [
            'total' => (int)$totalAgencies,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($totalAgencies / $limit)
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

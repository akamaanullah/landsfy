<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../database/db.php';

try {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $city_id = isset($_GET['city']) ? (int)$_GET['city'] : 0;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 12;
    $offset = ($page - 1) * $limit;

    $where_clauses = ["u.status = 'active'"];
    $params = [];

    if ($search) {
        $where_clauses[] = "(u.full_name LIKE ? OR ag.specialization LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if ($city_id) {
        $where_clauses[] = "u.city_id = ?"; // Assuming users have city_id or we join with agencies
        $params[] = $city_id;
    }

    $where_sql = implode(" AND ", $where_clauses);

    // Main Query
    $query = "
        SELECT u.id, u.full_name, u.avatar_url, u.phone,
               ag.slug, ag.specialization, ag.experience_years,
               a.name as agency_name, a.id as agency_id,
               (SELECT COUNT(*) FROM properties WHERE author_id = u.id AND status = 'active') as property_count,
               (SELECT AVG(rating) FROM agent_reviews WHERE agent_id = u.id) as avg_rating
        FROM agents ag
        JOIN users u ON ag.user_id = u.id
        LEFT JOIN agencies a ON ag.agency_id = a.id
        WHERE $where_sql
        ORDER BY property_count DESC
        LIMIT $limit OFFSET $offset
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $agents = $stmt->fetchAll();

    // Total Count for Pagination
    $count_query = "
        SELECT COUNT(*) 
        FROM agents ag
        JOIN users u ON ag.user_id = u.id
        WHERE $where_sql
    ";
    $count_stmt = $pdo->prepare($count_query);
    $count_stmt->execute($params);
    $total_items = $count_stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'data' => $agents,
        'meta' => [
            'total' => (int)$total_items,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total_items / $limit)
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

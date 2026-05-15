<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../database/db.php';

try {
    $category = isset($_GET['category']) ? trim($_GET['category']) : '';
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 6;
    $offset = ($page - 1) * $limit;

    $where_clauses = ["status = 'published'"];
    $params = [];

    if ($category && $category !== 'All Articles') {
        $where_clauses[] = "category = ?";
        $params[] = $category;
    }

    if ($search) {
        $where_clauses[] = "(title LIKE ? OR excerpt LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $where_sql = implode(" AND ", $where_clauses);

    // Fetch Posts
    $query = "SELECT id, title, slug, excerpt, category, image_url, read_time, created_at 
              FROM blogs 
              WHERE $where_sql 
              ORDER BY created_at DESC 
              LIMIT $limit OFFSET $offset";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $posts = $stmt->fetchAll();

    // Fetch Total for Pagination
    $count_query = "SELECT COUNT(*) FROM blogs WHERE $where_sql";
    $count_stmt = $pdo->prepare($count_query);
    $count_stmt->execute($params);
    $total_items = $count_stmt->fetchColumn();

    // Fetch Categories for Filter
    $cat_query = "SELECT category, COUNT(*) as count FROM blogs WHERE status = 'published' GROUP BY category";
    $categories = $pdo->query($cat_query)->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => [
            'posts' => $posts,
            'categories' => $categories
        ],
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

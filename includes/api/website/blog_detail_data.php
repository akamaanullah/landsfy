<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../database/db.php';

try {
    $slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

    if (!$slug) {
        throw new Exception("Blog slug is required.");
    }

    // Fetch Main Post
    $stmt = $pdo->prepare("SELECT b.*, u.full_name as author_name, u.avatar_url as author_img 
                           FROM blogs b 
                           LEFT JOIN users u ON b.author_id = u.id 
                           WHERE b.slug = ? AND b.status = 'published'");
    $stmt->execute([$slug]);
    $post = $stmt->fetch();

    if (!$post) {
        throw new Exception("Article not found.");
    }

    // Fetch Related Posts
    $related_stmt = $pdo->prepare("SELECT id, title, slug, image_url, created_at 
                                  FROM blogs 
                                  WHERE category = ? AND id != ? AND status = 'published' 
                                  ORDER BY created_at DESC LIMIT 3");
    $related_stmt->execute([$post->category, $post->id]);
    $related = $related_stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => [
            'post' => $post,
            'related' => $related
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

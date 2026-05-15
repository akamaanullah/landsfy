<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

if ($_SESSION['role_name'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // 1. Fetch Categories
    $categories = $pdo->query("SELECT * FROM property_categories ORDER BY sort_order ASC")->fetchAll();

    // 2. Fetch Subtypes
    $subtypes_raw = $pdo->query("SELECT * FROM property_subtypes ORDER BY sort_order ASC")->fetchAll();
    $subtypes = [];
    foreach ($subtypes_raw as $st) {
        $subtypes[$st->category_id][] = $st;
    }

    // 3. Fetch Amenity Fields
    $amenities_raw = $pdo->query("SELECT * FROM amenity_fields ORDER BY sort_order ASC")->fetchAll();
    $amenities = [];
    foreach ($amenities_raw as $af) {
        // Map 'context' to category slug or 'all'
        $amenities[$af->context][] = $af;
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'categories' => $categories,
            'subtypes' => $subtypes,
            'amenities' => $amenities
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

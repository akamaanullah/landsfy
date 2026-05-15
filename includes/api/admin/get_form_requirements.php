<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

try {
    // 1. Fetch Categories
    $cat_stmt = $pdo->query("SELECT id, name, slug, icon_class FROM property_categories ORDER BY sort_order");
    $categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Fetch Subtypes
    $sub_stmt = $pdo->query("SELECT id, category_id, name, slug, icon_class FROM property_subtypes ORDER BY sort_order");
    $subtypes = [];
    while ($row = $sub_stmt->fetch(PDO::FETCH_ASSOC)) {
        $subtypes[$row['category_id']][] = $row;
    }

    // 3. Fetch Cities
    $city_stmt = $pdo->query("SELECT id, name, slug FROM cities ORDER BY name ASC");
    $cities = $city_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Fetch Amenity Groups
    $group_stmt = $pdo->query("SELECT id, name, icon_class FROM amenity_groups ORDER BY sort_order");
    $groups = $group_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Fetch All Amenity Fields
    $field_stmt = $pdo->query("SELECT * FROM amenity_fields WHERE status = 'active' ORDER BY sort_order");
    $fields = [];
    while ($row = $field_stmt->fetch(PDO::FETCH_ASSOC)) {
        $fields[] = $row;
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'categories' => $categories,
            'subtypes' => $subtypes,
            'cities' => $cities,
            'amenity_groups' => $groups,
            'amenity_fields' => $fields
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

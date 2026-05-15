<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

// Enforce Admin
if ($_SESSION['role_name'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'add_subtype':
        case 'edit_subtype':
            $id = $_POST['id'] ?? null;
            $cat_id = $_POST['category_id'] ?? null;
            $name = trim($_POST['name'] ?? '');
            $icon = trim($_POST['icon'] ?? 'ph-house');
            $slug = strtolower(str_replace(' ', '-', $name));

            if (!$cat_id || !$name) throw new Exception("Category and Name are required.");

            if ($id) {
                $stmt = $pdo->prepare("UPDATE property_subtypes SET name = ?, slug = ?, icon_class = ? WHERE id = ?");
                $stmt->execute([$name, $slug, $icon, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO property_subtypes (category_id, name, slug, icon_class) VALUES (?, ?, ?, ?)");
                $stmt->execute([$cat_id, $name, $slug, $icon]);
            }
            break;

        case 'delete_subtype':
            $id = $_POST['id'] ?? null;
            if (!$id) throw new Exception("ID required.");
            $pdo->prepare("DELETE FROM property_subtypes WHERE id = ?")->execute([$id]);
            break;

        case 'add_category':
        case 'edit_category':
            $id = $_POST['id'] ?? null;
            $name = trim($_POST['name'] ?? '');
            $icon = trim($_POST['icon'] ?? 'ph-house');
            $slug = strtolower(str_replace(' ', '-', $name));

            if (!$name) throw new Exception("Category Name is required.");

            if ($id) {
                $stmt = $pdo->prepare("UPDATE property_categories SET name = ?, slug = ?, icon_class = ? WHERE id = ?");
                $stmt->execute([$name, $slug, $icon, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO property_categories (name, slug, icon_class) VALUES (?, ?, ?)");
                $stmt->execute([$name, $slug, $icon]);
            }
            break;

        case 'delete_category':
            $id = $_POST['id'] ?? null;
            if (!$id) throw new Exception("ID required.");
            $pdo->prepare("DELETE FROM property_categories WHERE id = ?")->execute([$id]);
            break;

        case 'add_amenity':
        case 'edit_amenity':
            $id = $_POST['id'] ?? null;
            $group_id = $_POST['group_id'] ?? 1;
            $context = $_POST['context'] ?? 'all';
            $label = trim($_POST['label'] ?? '');
            $field_type = $_POST['field_type'] ?? 'switch';
            $options = trim($_POST['options'] ?? '');
            $is_required = isset($_POST['is_required']) && $_POST['is_required'] === 'true' ? 1 : 0;
            $icon = trim($_POST['icon'] ?? 'ph-dot');

            if (!$label) throw new Exception("Label is required.");

            $options_json = null;
            if (!empty($options)) {
                $opts_array = array_map('trim', explode(',', $options));
                if (!empty($opts_array[0])) {
                    $options_json = json_encode($opts_array);
                }
            }

            if ($id) {
                $stmt = $pdo->prepare("UPDATE amenity_fields SET group_id = ?, label = ?, field_type = ?, options = ?, is_required = ?, context = ?, icon_class = ? WHERE id = ?");
                $stmt->execute([$group_id, $label, $field_type, $options_json, $is_required, $context, $icon, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO amenity_fields (group_id, label, field_type, options, is_required, context, icon_class) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$group_id, $label, $field_type, $options_json, $is_required, $context, $icon]);
            }
            break;

        case 'delete_amenity':
            $id = $_POST['id'] ?? null;
            if (!$id) throw new Exception("ID required.");
            $pdo->prepare("DELETE FROM amenity_fields WHERE id = ?")->execute([$id]);
            break;

        default:
            throw new Exception("Invalid action: $action");
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

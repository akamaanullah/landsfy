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
        case 'add_city':
        case 'edit_city':
            $id = $_POST['id'] ?? null;
            $name = trim($_POST['name'] ?? '');
            $popular = $_POST['is_popular'] ?? 0;
            $slug = strtolower(str_replace(' ', '-', $name));

            if (!$name) throw new Exception("Name is required.");

            if ($id) {
                $stmt = $pdo->prepare("UPDATE cities SET name = ?, slug = ?, is_popular = ? WHERE id = ?");
                $stmt->execute([$name, $slug, $popular, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO cities (name, slug, is_popular) VALUES (?, ?, ?)");
                $stmt->execute([$name, $slug, $popular]);
            }
            break;

        case 'delete_city':
            $id = $_POST['id'] ?? null;
            if (!$id) throw new Exception("ID required.");
            $pdo->prepare("DELETE FROM cities WHERE id = ?")->execute([$id]);
            break;

        case 'add_location':
        case 'edit_location':
            $id = $_POST['id'] ?? null;
            $city_id = $_POST['city_id'] ?? null;
            $name = trim($_POST['name'] ?? '');
            $slug = strtolower(str_replace(' ', '-', $name));

            if (!$city_id || !$name) throw new Exception("City and Name are required.");

            if ($id) {
                $stmt = $pdo->prepare("UPDATE locations SET city_id = ?, name = ?, slug = ? WHERE id = ?");
                $stmt->execute([$city_id, $name, $slug, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO locations (city_id, name, slug) VALUES (?, ?, ?)");
                $stmt->execute([$city_id, $name, $slug]);
            }
            break;

        case 'delete_location':
            $id = $_POST['id'] ?? null;
            if (!$id) throw new Exception("ID required.");
            $pdo->prepare("DELETE FROM locations WHERE id = ?")->execute([$id]);
            break;

        default:
            throw new Exception("Invalid action: $action");
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

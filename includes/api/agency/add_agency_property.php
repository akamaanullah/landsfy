<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

// Allow Agency Owners, Admins, and Agents
if (!in_array($_SESSION['role_name'], ['agency_owner', 'admin', 'agent'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role_name'];

try {
    $agency_id = null;
    $owner_id = null;

    if ($role === 'agency_owner') {
        $stmt = $pdo->prepare("SELECT id, owner_id FROM agencies WHERE owner_id = ?");
        $stmt->execute([$user_id]);
        $agency = $stmt->fetch();
        if ($agency) {
            $agency_id = $agency->id;
            $owner_id = $agency->owner_id;
        }
    } elseif ($role === 'agent') {
        $stmt = $pdo->prepare("
            SELECT a.agency_id, ag.owner_id 
            FROM agents a 
            JOIN agencies ag ON a.agency_id = ag.id 
            WHERE a.user_id = ?
        ");
        $stmt->execute([$user_id]);
        $res = $stmt->fetch();
        if ($res) {
            $agency_id = $res->agency_id;
            $owner_id = $res->owner_id;
        }
    }

    if (!$agency_id) {
        throw new Exception("Agency association not found.");
    }

    // 2. Validate Input
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $location_name = $_POST['location_name'] ?? '';
    $city_id = $_POST['city_id'] ?? null;
    $purpose = $_POST['property_purpose'] ?? 'sell';
    $area_size = $_POST['area_size'] ?? 0;
    $area_unit = $_POST['area_unit'] ?? 'sqyrd';
    $assigned_agent_id = $_POST['agent_id'] ?? null; // For owner assigning to agent

    if (empty($title) || empty($location_name)) {
        throw new Exception("Title and Location are required.");
    }

    // 3. Insert Property
    $stmt = $pdo->prepare("
        INSERT INTO properties (
            title, description, price, location_name, city_id, 
            purpose, area_size, area_unit, agency_id, author_id, 
            status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())
    ");
    
    // If agent is adding, author_id is the agent. If owner is adding, it could be assigned to an agent or themselves.
    $author_id = ($role === 'agent') ? $user_id : ($assigned_agent_id ?: $user_id);

    $stmt->execute([
        $title, $description, $price, $location_name, $city_id,
        $purpose, $area_size, $area_unit, $agency_id, $author_id
    ]);

    $property_id = $pdo->lastInsertId();

    // 4. Create Notification for Owner (if an agent added it)
    if ($role === 'agent' && $owner_id) {
        $notif_stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, sender_id, title, message, type, reference_id, reference_type)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $msg = "Agent " . $_SESSION['full_name'] . " added a new property: " . $title;
        $notif_stmt->execute([
            $owner_id, 
            $user_id, 
            "New Property Added", 
            $msg, 
            "property_added", 
            $property_id, 
            "property"
        ]);
    }

    echo json_encode([
        'success' => true, 
        'message' => 'Property added successfully',
        'property_id' => $property_id
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

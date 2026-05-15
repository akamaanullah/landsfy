<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

if ($_SESSION['role_name'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Agency ID is required']);
    exit;
}

try {
    // 1. Basic Agency Info with Owner Details
    $agency_stmt = $pdo->prepare("
        SELECT a.*, u.full_name as owner_name, u.email as owner_email, u.avatar_url as owner_avatar
        FROM agencies a 
        JOIN users u ON a.owner_id = u.id 
        WHERE a.id = ?
    ");
    $agency_stmt->execute([$id]);
    $agency = $agency_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$agency) {
        throw new Exception("Agency not found");
    }

    // 2. Metrics / Stats
    // - Agent Count
    $agent_count_stmt = $pdo->prepare("SELECT COUNT(*) FROM agents WHERE agency_id = ?");
    $agent_count_stmt->execute([$id]);
    $agent_count = (int)$agent_count_stmt->fetchColumn();
    
    // - Properties Count
    $prop_count_stmt = $pdo->prepare("SELECT COUNT(*) FROM properties WHERE agency_id = ?");
    $prop_count_stmt->execute([$id]);
    $property_count = (int)$prop_count_stmt->fetchColumn();

    // - Sold Count
    $sold_count_stmt = $pdo->prepare("SELECT COUNT(*) FROM properties WHERE agency_id = ? AND status = 'sold'");
    $sold_count_stmt->execute([$id]);
    $sold_count = (int)$sold_count_stmt->fetchColumn();

    // - Active Leads (Interactions: whatsapp_click + call_reveal)
    $leads_stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM property_interactions i
        JOIN properties p ON i.property_id = p.id
        WHERE p.agency_id = ? AND i.interaction_type IN ('whatsapp_click', 'call_reveal')
    ");
    $leads_stmt->execute([$id]);
    $leads_count = (int)$leads_stmt->fetchColumn();

    // - Overall Rating (Aggregate of all agent reviews for this agency)
    $rating_stmt = $pdo->prepare("
        SELECT AVG(r.rating) 
        FROM agent_reviews r
        JOIN agents a ON r.agent_id = a.user_id
        WHERE a.agency_id = ?
    ");
    $rating_stmt->execute([$id]);
    $avg_rating = $rating_stmt->fetchColumn();
    $formatted_rating = $avg_rating ? number_format((float)$avg_rating, 1) : '0.0';

    // 3. Team Members (Agents)
    $team_stmt = $pdo->prepare("
        SELECT ag.id as agent_id, u.id as user_id, u.full_name, u.email, u.phone, u.avatar_url, ag.specialization
        FROM agents ag
        JOIN users u ON ag.user_id = u.id
        WHERE ag.agency_id = ?
    ");
    $team_stmt->execute([$id]);
    $team = $team_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Properties (Last 12)
    $props_stmt = $pdo->prepare("
        SELECT p.id, p.title, p.price, p.status, p.area_size, p.area_unit,
               c.name as city_name, l.name as location_name,
               sub.name as property_type,
               img.image_url
        FROM properties p
        LEFT JOIN cities c ON p.city_id = c.id
        LEFT JOIN locations l ON p.location_id = l.id
        LEFT JOIN property_subtypes sub ON p.subtype_id = sub.id
        LEFT JOIN property_images img ON (p.id = img.property_id AND img.is_main = 1)
        WHERE p.agency_id = ?
        ORDER BY p.created_at DESC
        LIMIT 12
    ");
    $props_stmt->execute([$id]);
    $properties = $props_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Documents
    $docs_stmt = $pdo->prepare("SELECT * FROM agency_documents WHERE agency_id = ?");
    $docs_stmt->execute([$id]);
    $documents = $docs_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 6. Recent Reviews
    $reviews_stmt = $pdo->prepare("
        SELECT r.*, u.full_name as reviewer_name, u.avatar_url as reviewer_avatar, ag_u.full_name as agent_name
        FROM agent_reviews r
        JOIN users u ON r.reviewer_id = u.id
        JOIN agents ag ON r.agent_id = ag.user_id
        JOIN users ag_u ON ag.user_id = ag_u.id
        WHERE ag.agency_id = ?
        ORDER BY r.created_at DESC
        LIMIT 10
    ");
    $reviews_stmt->execute([$id]);
    $reviews = $reviews_stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => [
            'agency' => $agency,
            'stats' => [
                'agent_count' => $agent_count,
                'property_count' => $property_count,
                'sold_count' => $sold_count,
                'leads_count' => $leads_count,
                'rating' => $formatted_rating
            ],
            'team' => $team,
            'properties' => $properties,
            'documents' => $documents,
            'reviews' => $reviews
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

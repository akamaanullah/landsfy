<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

if ($_SESSION['role_name'] !== 'agency_owner' && $_SESSION['role_name'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // 1. Fetch Agency ID for this owner
    $stmt = $pdo->prepare("SELECT id FROM agencies WHERE owner_id = ?");
    $stmt->execute([$user_id]);
    $agency = $stmt->fetch();

    if (!$agency) {
        throw new Exception("Agency not found for this user.");
    }
    $agency_id = $agency->id;

    // 2. Fetch Stats (Consolidating from the other API logic)
    $listings_count = $pdo->prepare("SELECT COUNT(*) FROM properties WHERE agency_id = ? AND status != 'deleted'");
    $listings_count->execute([$agency_id]);
    
    $agents_count = $pdo->prepare("SELECT COUNT(*) FROM agents WHERE agency_id = ?");
    $agents_count->execute([$agency_id]);
    
    $leads_count = $pdo->prepare("SELECT COUNT(l.id) FROM leads l JOIN properties p ON l.property_id = p.id WHERE p.agency_id = ? AND l.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $leads_count->execute([$agency_id]);
    
    $rating_avg = $pdo->prepare("SELECT AVG(ar.rating) FROM agent_reviews ar JOIN agents a ON ar.agent_id = a.user_id WHERE a.agency_id = ?");
    $rating_avg->execute([$agency_id]);

    // 3. Fetch Top Agents (Simple logic: sort by number of listings for now)
    $top_agents_stmt = $pdo->prepare("
        SELECT u.full_name, u.avatar_url, 
               (SELECT COUNT(*) FROM properties WHERE author_id = u.id) as listings_count,
               (SELECT COUNT(*) FROM leads l JOIN properties p ON l.property_id = p.id WHERE p.author_id = u.id) as leads_count
        FROM agents a
        JOIN users u ON a.user_id = u.id
        WHERE a.agency_id = ?
        ORDER BY listings_count DESC LIMIT 5
    ");
    $top_agents_stmt->execute([$agency_id]);
    $top_agents = $top_agents_stmt->fetchAll();

    // 4. Fetch Recent Activity (New Leads)
    $recent_leads_stmt = $pdo->prepare("
        SELECT l.sender_name, p.title as property_title, l.created_at, u.full_name as agent_name
        FROM leads l
        JOIN properties p ON l.property_id = p.id
        LEFT JOIN users u ON p.author_id = u.id
        WHERE p.agency_id = ?
        ORDER BY l.created_at DESC LIMIT 5
    ");
    $recent_leads_stmt->execute([$agency_id]);
    $recent_leads = $recent_leads_stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'stats' => [
            'total_listings' => $listings_count->fetchColumn(),
            'total_agents' => $agents_count->fetchColumn(),
            'monthly_leads' => $leads_count->fetchColumn(),
            'avg_rating' => round($rating_avg->fetchColumn() ?: 0.0, 1)
        ],
        'top_agents' => $top_agents,
        'recent_leads' => $recent_leads
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

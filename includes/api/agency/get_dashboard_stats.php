<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

if ($_SESSION['role_name'] !== 'agency_owner' && $_SESSION['role_name'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
session_write_close();

try {
    // 1. Fetch Agency ID for this owner
    $stmt = $pdo->prepare("SELECT id FROM agencies WHERE owner_id = ?");
    $stmt->execute([$user_id]);
    $agency = $stmt->fetch();

    if (!$agency) {
        throw new Exception("Agency not found for this user.");
    }
    $agency_id = $agency->id;

    // 2. Total Listings
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM properties WHERE agency_id = ? AND status != 'deleted'");
    $stmt->execute([$agency_id]);
    $total_listings = $stmt->fetchColumn();

    // 3. Active Agents
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM agents WHERE agency_id = ?");
    $stmt->execute([$agency_id]);
    $total_agents = $stmt->fetchColumn();

    // 4. Monthly Leads (Leads for properties belonging to this agency in the last 30 days)
    $stmt = $pdo->prepare("SELECT COUNT(l.id) FROM leads l 
                           JOIN properties p ON l.property_id = p.id 
                           WHERE p.agency_id = ? AND l.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stmt->execute([$agency_id]);
    $monthly_leads = $stmt->fetchColumn();

    // 5. Customer Rating (Average rating of all agents in this agency)
    $stmt = $pdo->prepare("SELECT AVG(ar.rating) FROM agent_reviews ar 
                           JOIN agents a ON ar.agent_id = a.user_id 
                           WHERE a.agency_id = ?");
    $stmt->execute([$agency_id]);
    $avg_rating = $stmt->fetchColumn() ?: 0.0;

    echo json_encode([
        'success' => true,
        'stats' => [
            'total_listings' => $total_listings,
            'total_agents' => $total_agents,
            'monthly_leads' => $monthly_leads,
            'avg_rating' => round($avg_rating, 1)
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

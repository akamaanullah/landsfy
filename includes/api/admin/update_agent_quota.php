<?php
header('Content-Type: application/json');
require_once '../../auth_check.php';
require_once '../../database/db.php';

if ($_SESSION['role_name'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$agent_id = $_POST['user_id'] ?? null;
$plat = $_POST['platinum_quota'] ?? 0;
$diam = $_POST['diamond_quota'] ?? 0;

if (!$agent_id) {
    echo json_encode(['success' => false, 'message' => 'Missing Agent ID']);
    exit;
}

try {
    $mode = $_POST['mode'] ?? 'add'; // 'add' or 'set'

    $pdo->beginTransaction();

    if ($mode === 'add') {
        $stmt = $pdo->prepare("UPDATE agents SET platinum_quota = platinum_quota + ?, diamond_quota = diamond_quota + ? WHERE user_id = ?");
        $stmt->execute([$plat, $diam, $agent_id]);
        $desc = "Manually added quota to user #$agent_id: Platinum (+$plat), Diamond (+$diam)";
    } else {
        $stmt = $pdo->prepare("UPDATE agents SET platinum_quota = ?, diamond_quota = ? WHERE user_id = ?");
        $stmt->execute([$plat, $diam, $agent_id]);
        $desc = "Manually set user #$agent_id quota: Platinum ($plat), Diamond ($diam)";
    }

    // 2. Log Action
    $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action_type, description, target_id) VALUES (?, ?, ?, ?)");
    $log_stmt->execute([
        $_SESSION['user_id'], 
        'manual_quota_update', 
        $desc,
        $agent_id
    ]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Quota updated successfully']);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

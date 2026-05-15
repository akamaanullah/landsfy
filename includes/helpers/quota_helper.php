<?php
/**
 * Quota Helper - Manages Platinum and Diamond listing credits
 */

/**
 * Get current quota for an agent
 */
function getAgentQuota($pdo, $userId) {
    try {
        $stmt = $pdo->prepare("SELECT platinum_quota, platinum_used, diamond_quota, diamond_used FROM agents WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Check if agent has enough quota for a specific type
 */
function hasSufficientQuota($pdo, $userId, $type) {
    $quota = getAgentQuota($pdo, $userId);
    if (!$quota) return false;

    if ($type === 'platinum') {
        return ($quota['platinum_quota'] - $quota['platinum_used']) > 0;
    } elseif ($type === 'diamond') {
        return ($quota['diamond_quota'] - $quota['diamond_used']) > 0;
    }
    
    return true; // Simple listings
}

/**
 * Deduct quota after admin approval
 */
function deductAgentQuota($pdo, $userId, $type) {
    if ($type === 'none' || empty($type)) return true;

    $column = ($type === 'platinum') ? 'platinum_used' : (($type === 'diamond') ? 'diamond_used' : null);
    if (!$column) return true;

    try {
        $stmt = $pdo->prepare("UPDATE agents SET $column = $column + 1 WHERE user_id = ?");
        return $stmt->execute([$userId]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Refund quota (if listing is rejected or deleted)
 */
function refundAgentQuota($pdo, $userId, $type) {
    if ($type === 'none' || empty($type)) return true;

    $column = ($type === 'platinum') ? 'platinum_used' : (($type === 'diamond') ? 'diamond_used' : null);
    if (!$column) return true;

    try {
        $stmt = $pdo->prepare("UPDATE agents SET $column = GREATEST(0, $column - 1) WHERE user_id = ?");
        return $stmt->execute([$userId]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Add quota (used by Admin)
 */
function addAgentQuota($pdo, $userId, $type, $amount) {
    $column = ($type === 'platinum') ? 'platinum_quota' : (($type === 'diamond') ? 'diamond_quota' : null);
    if (!$column) return false;

    try {
        $stmt = $pdo->prepare("UPDATE agents SET $column = $column + ? WHERE user_id = ?");
        return $stmt->execute([$amount, $userId]);
    } catch (PDOException $e) {
        return false;
    }
}
?>

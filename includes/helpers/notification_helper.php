<?php

/**
 * Global Helper to create notifications for any user
 * 
 * @param int $userId ID of the recipient
 * @param string $title Short title
 * @param string $message Detailed message
 * @param string $type Category (inquiry, system, approval, review, etc.)
 * @param int $refId ID of the related object (property_id, etc.)
 * @param string $refType Type of the related object ('property', 'user', 'inquiry')
 * @param string $link Optional link to redirect the user
 * @return bool
 */
function createNotification($userId, $title, $message, $type = 'system', $refId = null, $refType = null, $link = null) {
    global $pdo;
    
    // Ensure we have a database connection
    if (!$pdo) {
        require_once dirname(__DIR__) . '/database/db.php';
    }

    try {
        $sql = "INSERT INTO notifications (user_id, title, message, type, reference_id, reference_type, link) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$userId, $title, $message, $type, $refId, $refType, $link]);
    } catch (PDOException $e) {
        error_log("Notification Error: " . $e->getMessage());
        return false;
    }
}

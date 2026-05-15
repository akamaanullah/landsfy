<?php
/**
 * User Meta/Settings Helper
 * Handles dynamic user attributes stored in the user_settings table.
 */

if (!function_exists('get_user_setting')) {
    function get_user_setting($user_id, $key, $default = null) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("SELECT setting_value FROM user_settings WHERE user_id = ? AND setting_key = ?");
            $stmt->execute([$user_id, $key]);
            $value = $stmt->fetchColumn();
            
            return ($value !== false) ? $value : $default;
        } catch (PDOException $e) {
            return $default;
        }
    }
}

if (!function_exists('set_user_setting')) {
    function set_user_setting($user_id, $key, $value) {
        global $pdo;
        
        try {
            // Check if exists
            $stmt = $pdo->prepare("SELECT id FROM user_settings WHERE user_id = ? AND setting_key = ?");
            $stmt->execute([$user_id, $key]);
            $exists = $stmt->fetchColumn();
            
            if ($exists) {
                $stmt = $pdo->prepare("UPDATE user_settings SET setting_value = ? WHERE id = ?");
                $stmt->execute([$value, $exists]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO user_settings (user_id, setting_key, setting_value) VALUES (?, ?, ?)");
                $stmt->execute([$user_id, $key, $value]);
            }
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}

/**
 * Check if the current user is verified
 */
function is_user_verified($user_id) {
    return get_user_setting($user_id, 'is_verified', '0') === '1';
}

/**
 * Check if the current user has premium status
 */
function is_user_premium($user_id) {
    return get_user_setting($user_id, 'is_premium', '0') === '1';
}
?>

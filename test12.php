<?php
require 'includes/database/db.php';

try {
    // Add missing columns if they don't exist
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS google_id VARCHAR(255) DEFAULT NULL AFTER id");
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS oauth_provider VARCHAR(50) DEFAULT NULL AFTER google_id");
    
    // Add unique index if not exists (handling potential error if already exists)
    try {
        $pdo->exec("ALTER TABLE users ADD UNIQUE KEY (google_id)");
    } catch (Exception $e) {
        // Index might already exist
    }

    echo "Google OAuth columns added successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

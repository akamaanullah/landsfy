<?php
// includes/database/db.php

// Database Configuration (Ideally move to .env or a secure config)
$host = 'localhost';
$dbname = 'landsfy_nre';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Set PDO error mode
    // Use ERRMODE_SILENT or handle carefully in production
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
} catch(PDOException $e) {
    // In production, log error instead of die() with message
    // error_log($e->getMessage());
    die("Error: We are experiencing some technical difficulties. Please try again later.");
}

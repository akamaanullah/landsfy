<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';

// Load Database Connection
require_once __DIR__ . '/database/db.php';

// Google Credentials (Use .env or config file in production)
$google_client_id = 'YOUR_GOOGLE_CLIENT_ID';
$google_client_secret = 'YOUR_GOOGLE_CLIENT_SECRET';

// Dynamically detect host and directory for redirect URI
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$directory = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$google_redirect_uri = $protocol . $host . $directory . '/google-callback.php';

// Initialize Google Client
$google_client = new Google_Client();
$google_client->setClientId($google_client_id);
$google_client->setClientSecret($google_client_secret);
$google_client->setRedirectUri($google_redirect_uri);

// Add requested scopes
$google_client->addScope('email');
$google_client->addScope('profile');
?>

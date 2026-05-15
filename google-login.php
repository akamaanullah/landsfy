<?php
require_once 'includes/google-config.php';

// Capture role for new signups
if (isset($_GET['role'])) {
    $role = $_GET['role'];
    if (in_array($role, ['buyer', 'seller', 'agency_owner'])) {
        $_SESSION['google_reg_role'] = $role;
    }
}

// Prepare the Google login URL
$auth_url = $google_client->createAuthUrl();

// Redirect to Google's OAuth server
header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
exit();
?>

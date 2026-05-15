<?php
session_start();

// Enforce login status
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Ensure the user actually has permission to view agent areas
if (!isset($_SESSION['role_name']) || !in_array($_SESSION['role_name'], ['agent', 'admin', 'agency_owner', 'seller', 'buyer'])) {
    header("Location: ../index.php");
    exit;
}

// Deep Security: Enforce Agency association for Agents
if ($_SESSION['role_name'] === 'agent' && empty($_SESSION['agency_id'])) {
    session_destroy();
    header("Location: ../login.php?error=unauthorized_agent");
    exit;
}
?>

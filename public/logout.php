<?php
// public/logout.php - Simple Logout
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
unset($_SESSION['user']);
$_SESSION['flash_success'] = "You have been signed out.";
header("Location: /index.php");
exit;

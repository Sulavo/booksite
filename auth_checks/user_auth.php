<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Optionally, if you want to allow only users with role = 1 (non-admins)
if ($_SESSION['user']['role'] != 1) {
    header('Location: unauthorized.php'); // or redirect to homepage
    exit();
}
?>
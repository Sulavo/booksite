<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user']['id'];

// Delete user and cascade (assumes foreign key constraints are setup)
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    session_destroy();
    header('Location: index.php');
    exit();
} else {
    echo "Failed to delete account.";
}
?>
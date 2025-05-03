<?php
// admin_auth.php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 0) {
    header('Location: ../login.php');
    exit();

}
?>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') {
    header('Location: ' . BASE_URL . 'admin/index.php'); // Redirect admins to their dashboard
    exit();
}
?>
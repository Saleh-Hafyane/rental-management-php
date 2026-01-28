<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../config/db.php"; // Ensure BASE_URL is defined here
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentNow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= BASE_URL ?>index.php">Rent<span class="text-primary">Now</span></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>index.php">Home</a>
                </li>
                <?php if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>reservations/select_bien.php">Rent</a>
                </li>
                <?php endif; ?>
                <?php if (isset($_SESSION['user'])): ?>
                    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>biens/create.php">Add Biens</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>admin/history.php">Reservation History</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>admin/stats.php">Statistics</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>biens/index.php">Manage Properties</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>reservations/my_reservations.php">My Reservations</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>auth/logout.php" class="btn btn-outline-primary ms-lg-2">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>auth/login.php" class="btn btn-primary ms-lg-2">Login</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>auth/register.php" class="btn btn-secondary ms-lg-2">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container mt-4">

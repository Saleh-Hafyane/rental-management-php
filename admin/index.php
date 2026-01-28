<?php
require "guard.php";
require "../config/db.php";
include "../partials/header.php";

$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalBiens = $pdo->query("SELECT COUNT(*) FROM biens")->fetchColumn();
$totalReservations = $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
$totalRevenue = $pdo->query("SELECT SUM(total_price) FROM reservations WHERE status = 'confirmed'")->fetchColumn();
?>

<div class="container">
    <h2>Admin Dashboard</h2>

    <div class="row">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-header">Total Users</div>
                <div class="card-body">
                    <h5 class="card-title"><?= $totalUsers ?></h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-header">Total Properties</div>
                <div class="card-body">
                    <h5 class="card-title"><?= $totalBiens ?></h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info mb-3">
                <div class="card-header">Total Reservations</div>
                <div class="card-body">
                    <h5 class="card-title"><?= $totalReservations ?></h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-dark mb-3">
                <div class="card-header">Total Revenue</div>
                <div class="card-body">
                    <h5 class="card-title">$<?= number_format($totalRevenue, 2) ?></h5>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <h4>Admin Actions</h4>
        <a href="<?= BASE_URL ?>admin/history.php" class="btn btn-secondary">View Reservation History</a>
        <a href="<?= BASE_URL ?>admin/stats.php" class="btn btn-secondary">View Statistics</a>
        <a href="<?= BASE_URL ?>biens/index.php" class="btn btn-secondary">Manage Properties</a>
    </div>
</div>

<?php include "../partials/footer.php"; ?>
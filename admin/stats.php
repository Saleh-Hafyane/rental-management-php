<?php
require "guard.php";
require "../config/db.php";
include "../partials/header.php";

// Monthly Revenue
$revenue_data = $pdo->query("
    SELECT DATE_FORMAT(r.created_at, '%Y-%m') AS month, SUM(r.total_price) AS total
    FROM reservations r
    WHERE r.status = 'confirmed'
    GROUP BY month
    ORDER BY month
")->fetchAll(PDO::FETCH_ASSOC);

$revenue_labels = array_column($revenue_data, 'month');
$revenue_totals = array_column($revenue_data, 'total');

// Reservations per month
$reservations_data = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS count
    FROM reservations
    GROUP BY month
    ORDER BY month
")->fetchAll(PDO::FETCH_ASSOC);

$reservation_labels = array_column($reservations_data, 'month');
$reservation_counts = array_column($reservations_data, 'count');

// Property type distribution
$property_types_data = $pdo->query("
    SELECT type, COUNT(*) as count
    FROM biens
    GROUP BY type
")->fetchAll(PDO::FETCH_ASSOC);

$property_type_labels = array_column($property_types_data, 'type');
$property_type_counts = array_column($property_types_data, 'count');
?>

<div class="container">
    <h2>Statistics</h2>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Monthly Revenue</div>
                <div class="card-body">
                    <canvas id="revenue-chart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Reservations per Month</div>
                <div class="card-body">
                    <canvas id="reservations-chart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Property Type Distribution</div>
                <div class="card-body">
                    <canvas id="property-types-chart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Revenue Chart
    new Chart(document.getElementById('revenue-chart'), {
        type: 'line',
        data: {
            labels: <?= json_encode($revenue_labels) ?>,
            datasets: [{
                label: 'Revenue ($)',
                data: <?= json_encode($revenue_totals) ?>,
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                fill: true,
            }]
        }
    });

    // Reservations Chart
    new Chart(document.getElementById('reservations-chart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($reservation_labels) ?>,
            datasets: [{
                label: 'Number of Reservations',
                data: <?= json_encode($reservation_counts) ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
            }]
        }
    });

    // Property Types Chart
    new Chart(document.getElementById('property-types-chart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($property_type_labels) ?>,
            datasets: [{
                data: <?= json_encode($property_type_counts) ?>,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                ],
            }]
        }
    });
</script>

<?php include "../partials/footer.php"; ?>

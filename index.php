<?php
require_once __DIR__ . "/config/db.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') {
    header('Location: ' . BASE_URL . 'admin/index.php');
    exit();
}
include "partials/header.php"; ?>

<section class="hero">
  <h1>Rent Cars & Apartments Easily</h1>
  <p>Fast, secure, and transparent rental platform.</p>
  <a href="<?= BASE_URL ?>reservations/select_bien.php" class="cta">Start Renting</a>
</section>

<section class="features">
  <div class="feature">
    <h3>🚗 Cars & Apartments</h3>
    <p>Wide selection of quality rentals.</p>
  </div>
  <div class="feature">
    <h3>📅 Smart Booking</h3>
    <p>Real-time availability & pricing.</p>
  </div>
  <div class="feature">
    <h3>📝 Contracts</h3>
    <p>Automatic printable contracts.</p>
  </div>
</section>

<?php include "partials/footer.php"; ?>

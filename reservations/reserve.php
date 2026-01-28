<?php
// Start session and connect to database
session_start();
require "../config/db.php";

// Ensure user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

// Get property ID from URL and fetch details
$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM biens WHERE id=?");
$stmt->execute([$id]);
$bien = $stmt->fetch();

$error = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $bien) {
    $start = $_POST['start'];
    $end   = $_POST['end'];

    // Validate date range
    if ($end <= $start) {
        $error = "Invalid dates";
    } else {
        // Availability check
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM reservations
            WHERE bien_id = ?
            AND (
                start_date < ? AND end_date > ?
            )
        ");
        $stmt->execute([$id, $end, $start]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $error = "This bien is not available for these dates.";
        } else {
            // Calculate duration and total price
            $days = (strtotime($end) - strtotime($start)) / 86400;
            $total = $days * $bien['price_per_day'];

            // Save reservation to database
            $stmt = $pdo->prepare("
              INSERT INTO reservations(user_id,bien_id,start_date,end_date,total_price)
              VALUES (?,?,?,?,?)
            ");
            $stmt->execute([
                $_SESSION['user']['id'],
                $id,
                $start,
                $end,
                $total
            ]);

            // Redirect to reservations list
            header("Location: " . BASE_URL . "reservations/my_reservations.php");
            exit;
        }
    }
}

include "../partials/header.php";
?>

<div class="container">
    <?php if (!$bien): ?>
        <div class="alert alert-danger" role="alert">
            Property not found.
        </div>
        <a href="<?= BASE_URL ?>reservations/select_bien.php" class="btn btn-secondary">Back to Properties</a>
    <?php else: ?>
        <h2>Reserve: <?= htmlspecialchars($bien['name']) ?></h2>
        <p class="lead">Price per day: $<?= htmlspecialchars($bien['price_per_day']) ?></p>

        <?php if($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <div class="card mt-3">
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label for="start" class="form-label">Start date</label>
                        <input type="date" class="form-control" id="start" name="start" required>
                    </div>
                    <div class="mb-3">
                        <label for="end" class="form-label">End date</label>
                        <input type="date" class="form-control" id="end" name="end" required>
                    </div>
                    <button type="submit" class="btn btn-success">Confirm Reservation</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include "../partials/footer.php"; ?>

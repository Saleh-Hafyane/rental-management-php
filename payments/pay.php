<?php
include "../partials/header.php"; // Handles session and DB connection

if (!isset($_SESSION['user'])) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: " . BASE_URL . "reservations/my_reservations.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT r.*, b.name as bien_name
    FROM reservations r
    JOIN biens b ON r.bien_id = b.id
    WHERE r.id = ? AND r.user_id = ?
");
$stmt->execute([$id, $_SESSION['user']['id']]);
$reservation = $stmt->fetch();

if (!$reservation) {
    header("Location: " . BASE_URL . "reservations/my_reservations.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'];

    // In a real application, you would integrate with a payment gateway here.
    // For this example, we'll just simulate a successful payment.

    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare("
            INSERT INTO payments (reservation_id, amount, payment_method, status)
            VALUES (?, ?, ?, 'completed')
        ");
        $stmt->execute([$id, $reservation['total_price'], $payment_method]);
        $payment_id = $pdo->lastInsertId();

        $stmt = $pdo->prepare("UPDATE reservations SET status = 'confirmed' WHERE id = ?");
        $stmt->execute([$id]);
        
        // Here you could also update the bien status to 'rented' if the business logic requires it
        // $stmt = $pdo->prepare("UPDATE biens SET status = 'rented' WHERE id = ?");
        // $stmt->execute([$reservation['bien_id']]);

        $pdo->commit();

        header("Location: " . BASE_URL . "reservations/my_reservations.php");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        // In a real app, you would log this error and show a user-friendly message.
        die("Payment processing failed. Please try again. Error: " . $e->getMessage());
    }
}
?>

<div class="container">
    <h2>Complete Your Payment</h2>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Reservation for: <?= htmlspecialchars($reservation['bien_name']) ?></h5>
            <p class="card-text">
                <strong>Total Amount:</strong> $<?= htmlspecialchars($reservation['total_price']) ?>
            </p>
            <hr>
            <form method="post">
                <div class="mb-3">
                    <label for="payment_method" class="form-label">Select Payment Method</label>
                    <select class="form-control" id="payment_method" name="payment_method">
                        <option value="credit_card">Credit Card (Simulated)</option>
                        <option value="paypal">PayPal (Simulated)</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Pay Now</button>
                <a href="<?= BASE_URL ?>reservations/my_reservations.php" class="btn btn-secondary">Cancel</a>
            </form>
            <p class="text-muted small mt-3">Note: This is a simulated payment process. No real transaction will occur.</p>
        </div>
    </div>
</div>

<?php include "../partials/footer.php"; ?>
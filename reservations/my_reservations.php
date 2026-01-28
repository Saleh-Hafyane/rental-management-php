<?php
session_start();
require "../config/db.php";
include "../partials/header.php";

if (!isset($_SESSION['user'])) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT r.*, b.name as bien_name, b.type as bien_type
    FROM reservations r
    JOIN biens b ON r.bien_id = b.id
    WHERE r.user_id = ?
    ORDER BY r.start_date DESC
");
$stmt->execute([$_SESSION['user']['id']]);
$reservations = $stmt->fetchAll();
?>

<div class="container">
    <h2>My Reservations</h2>

    <?php if (empty($reservations)): ?>
        <p>You have no reservations.</p>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($reservations as $r): ?>
                <div class="list-group-item list-group-item-action flex-column align-items-start">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1"><?= htmlspecialchars($r['bien_name']) ?></h5>
                        <small><?= date('M d, Y', strtotime($r['start_date'])) ?> to <?= date('M d, Y', strtotime($r['end_date'])) ?></small>
                    </div>
                    <p class="mb-1">
                        <strong>Total Price:</strong> $<?= htmlspecialchars($r['total_price']) ?><br>
                        <strong>Status:</strong> <span class="badge bg-<?= 
                            ($r['status'] === 'confirmed') ? 'success' : 
                            (($r['status'] === 'pending') ? 'warning' : 'secondary') 
                        ?>"><?= ucfirst($r['status']) ?></span>
                    </p>
                    <div class="mt-2">
                        <?php if ($r['status'] === 'pending'): ?>
                            <a href="<?= BASE_URL ?>payments/pay.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-success">Pay Now</a>
                        <?php elseif ($r['status'] === 'confirmed'): ?>
                            <a href="<?= BASE_URL ?>contracts/contract.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-info" target="_blank">View Contract</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include "../partials/footer.php"; ?>

<?php
session_start();
require_once __DIR__ . "/../config/db.php";
include_once __DIR__ . "/../partials/header.php";

$bien_id = $_GET['id'] ?? null;

if (!$bien_id) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>Bien ID not provided.</div></div>";
    include_once __DIR__ . "/../partials/footer.php";
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM biens WHERE id = ?");
    $stmt->execute([$bien_id]);
    $bien = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$bien) {
        echo "<div class='container mt-4'><div class='alert alert-danger'>Bien not found.</div></div>";
        include_once __DIR__ . "/../partials/footer.php";
        exit();
    }

    // Fetch all images for the bien
    $stmt_images = $pdo->prepare("SELECT image_path FROM bien_images WHERE bien_id = ?");
    $stmt_images->execute([$bien_id]);
    $images = $stmt_images->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>Database error: " . $e->getMessage() . "</div></div>";
    include_once __DIR__ . "/../partials/footer.php";
    exit();
}
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h2><?= htmlspecialchars($bien['name']) ?></h2>
        </div>
        <div class="card-body">
            <h5 class="card-title">Type: <?= ucfirst(htmlspecialchars($bien['type'])) ?></h5>
            <p class="card-text"><strong>Description:</strong> <?= htmlspecialchars($bien['description']) ?></p>
            <p class="card-text"><strong>Price per day:</strong> $<?= htmlspecialchars(number_format($bien['price_per_day'], 2)) ?></p>
            <p class="card-text"><strong>Status:</strong> <?= ucfirst(htmlspecialchars($bien['status'])) ?></p>

            <div class="row mt-4">
                <?php if (!empty($images)): ?>
                    <?php foreach ($images as $image): ?>
                        <div class="col-md-4 mb-3">
                            <img src="<?= BASE_URL ?>uploads/biens/<?= htmlspecialchars($image['image_path']) ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($bien['name']) ?>">
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12"><p>No images available for this property.</p></div>
                <?php endif; ?>
            </div>

            <div class="mt-3">
                <?php if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin'): ?>
                    <a href="<?= BASE_URL ?>reservations/reserve.php?id=<?= $bien['id'] ?>" class="btn btn-primary">Reserve Now</a>
                <?php endif; ?>
                <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                    <a href="<?= BASE_URL ?>biens/edit.php?id=<?= $bien['id'] ?>" class="btn btn-warning">Edit Bien</a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>reservations/select_bien.php" class="btn btn-secondary">Back to List</a>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . "/../partials/footer.php"; ?>

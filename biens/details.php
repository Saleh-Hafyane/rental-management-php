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
        <style>
            .carousel-item {
                height: 400px; /* Fixed height for carousel items */
                overflow: hidden; /* Hide overflowing parts of the image */
            }
            .carousel-item img {
                height: 100%; /* Make image fill the fixed height of the carousel item */
                object-fit: cover; /* Cover the area, cropping if necessary */
            }
        </style>
        <?php if (!empty($images)): ?>
            <div class="row justify-content-center mb-3">
                <div class="col-md-8">
                    <div id="bienImageCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-indicators">
                            <?php foreach ($images as $index => $image): ?>
                                <button type="button" data-bs-target="#bienImageCarousel" data-bs-slide-to="<?= $index ?>" class="<?= $index === 0 ? 'active' : '' ?>" aria-current="<?= $index === 0 ? 'true' : 'false' ?>" aria-label="Slide <?= $index + 1 ?>"></button>
                            <?php endforeach; ?>
                        </div>
                        <div class="carousel-inner">
                            <?php foreach ($images as $index => $image): ?>
                                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                    <img src="<?= BASE_URL ?>uploads/biens/<?= htmlspecialchars($image['image_path']) ?>" class="d-block w-100" alt="<?= htmlspecialchars($bien['name']) ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#bienImageCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#bienImageCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="row justify-content-center mb-3">
                <div class="col-md-8">
                    <div class="text-center p-3 border rounded">
                        <img src="https://via.placeholder.com/400x250?text=No+Image+Available" class="img-fluid rounded" alt="No Image">
                        <p class="mt-2 text-muted">No images available for this property.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <div class="card-body">
            <div class="mb-3">
                <h5 class="card-title">Description</h5>
                <p class="card-text"><?= htmlspecialchars($bien['description']) ?></p>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <strong>Type:</strong> <?= ucfirst(htmlspecialchars($bien['type'])) ?>
                </div>
                <div class="col-md-4">
                    <strong>Price per day:</strong> <span class="text-success fs-5">$<?= htmlspecialchars(number_format($bien['price_per_day'], 2)) ?></span>
                </div>
                <div class="col-md-4">
                    <strong>Status:</strong> <span class="badge bg-<?= $bien['status'] === 'available' ? 'success' : 'warning' ?> fs-6"><?= ucfirst(htmlspecialchars($bien['status'])) ?></span>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top">
                <?php if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin'): ?>
                    <a href="<?= BASE_URL ?>reservations/reserve.php?id=<?= $bien['id'] ?>" class="btn btn-primary me-2">Reserve Now</a>
                <?php endif; ?>
                <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                    <a href="<?= BASE_URL ?>biens/edit.php?id=<?= $bien['id'] ?>" class="btn btn-warning me-2">Edit Bien</a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>biens/index.php" class="btn btn-secondary">Back to List</a>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . "/../partials/footer.php"; ?>

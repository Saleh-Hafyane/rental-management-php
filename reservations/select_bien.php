<?php
require "guard.php";
require "../config/db.php";
include "../partials/header.php";

$type_filter = $_GET['type'] ?? '';
$search_term = $_GET['search'] ?? '';

$sql = "SELECT b.*, (SELECT image_path FROM bien_images WHERE bien_id = b.id ORDER BY id ASC LIMIT 1) as image FROM biens b WHERE b.status = 'available'";
$params = [];

if ($type_filter) {
    $sql .= " AND type = ?";
    $params[] = $type_filter;
}

if ($search_term) {
    $sql .= " AND name LIKE ?";
    $params[] = '%' . $search_term . '%';
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$biens = $stmt->fetchAll();
?>

<div class="container">
    <h2>Choose a Property to Reserve</h2>

    <form method="get" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search by name..." value="<?= htmlspecialchars($search_term) ?>">
            </div>
            <div class="col-md-3">
                <select name="type" class="form-control">
                    <option value="">All Types</option>
                    <option value="car" <?= $type_filter === 'car' ? 'selected' : '' ?>>Car</option>
                    <option value="apartment" <?= $type_filter === 'apartment' ? 'selected' : '' ?>>Apartment</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary">Filter</button>
            </div>
        </div>
    </form>

    <div class="row">
        <?php if (empty($biens)): ?>
            <div class="col">
                <p>No properties available for reservation.</p>
            </div>
        <?php else: ?>
            <?php foreach ($biens as $b): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <?php if ($b['image']): ?>
                            <img src="<?= BASE_URL ?>uploads/biens/<?= htmlspecialchars($b['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($b['name']) ?>" style="height: 200px; object-fit: cover;">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($b['name']) ?></h5>
                            <h6 class="card-subtitle mb-2 text-muted"><?= ucfirst($b['type']) ?></h6>
                            <p class="card-text">
                                <strong>Price:</strong> $<?= htmlspecialchars($b['price_per_day']) ?> / day
                            </p>
                            <a href="reserve.php?id=<?= $b['id'] ?>" class="btn btn-primary">Reserve</a>
                            <a href="<?= BASE_URL ?>biens/details.php?id=<?= $b['id'] ?>" class="btn btn-info">Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include "../partials/footer.php"; ?>

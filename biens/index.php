<?php
require "guard.php";
require "../config/db.php";
include "../partials/header.php";

$type_filter = $_GET['type'] ?? '';
$search_term = $_GET['search'] ?? '';

$sql = "SELECT * FROM biens WHERE 1=1";
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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Properties</h2>
        <a href="<?= BASE_URL ?>biens/create.php" class="btn btn-primary">+ Add Property</a>
    </div>

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
        <?php foreach ($biens as $b): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($b['name']) ?></h5>
                        <h6 class="card-subtitle mb-2 text-muted"><?= ucfirst($b['type']) ?></h6>
                        <p class="card-text">
                            <strong>Price:</strong> $<?= htmlspecialchars($b['price_per_day']) ?> / day<br>
                            <strong>Status:</strong> <span class="badge bg-<?= $b['status'] === 'available' ? 'success' : 'warning' ?>"><?= ucfirst($b['status']) ?></span>
                        </p>
                        <a href="<?= BASE_URL ?>biens/edit.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                        <a href="<?= BASE_URL ?>biens/details.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-info">Details</a>
                        <a href="<?= BASE_URL ?>biens/delete.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this property?')">Delete</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include "../partials/footer.php"; ?>
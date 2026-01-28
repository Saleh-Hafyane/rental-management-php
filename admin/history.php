<?php
require "guard.php";
require "../config/db.php";
include "../partials/header.php";

$search_term = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

$sql = "
    SELECT r.*, u.name AS user_name, b.name AS bien_name
    FROM reservations r
    JOIN users u ON r.user_id = u.id
    JOIN biens b ON r.bien_id = b.id
    WHERE 1=1
";
$params = [];

if ($search_term) {
    $sql .= " AND (u.name LIKE ? OR b.name LIKE ?)";
    $params[] = '%' . $search_term . '%';
    $params[] = '%' . $search_term . '%';
}

if ($status_filter) {
    $sql .= " AND r.status = ?";
    $params[] = $status_filter;
}

$sql .= " ORDER BY r.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reservations = $stmt->fetchAll();
?>

<div class="container">
    <h2>Reservation History</h2>

    <form method="get" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search by client or property..." value="<?= htmlspecialchars($search_term) ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="confirmed" <?= $status_filter === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                    <option value="canceled" <?= $status_filter === 'canceled' ? 'selected' : '' ?>>Canceled</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary">Filter</button>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Client</th>
                    <th>Property</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Total Price</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservations as $r): ?>
                    <tr>
                        <td><?= $r['id'] ?></td>
                        <td><?= htmlspecialchars($r['user_name']) ?></td>
                        <td><?= htmlspecialchars($r['bien_name']) ?></td>
                        <td><?= date('M d, Y', strtotime($r['start_date'])) ?></td>
                        <td><?= date('M d, Y', strtotime($r['end_date'])) ?></td>
                        <td>$<?= htmlspecialchars($r['total_price']) ?></td>
                        <td>
                            <span class="badge bg-<?= 
                                ($r['status'] === 'confirmed') ? 'success' : 
                                (($r['status'] === 'pending') ? 'warning' : 'secondary') 
                            ?>"><?= ucfirst($r['status']) ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include "../partials/footer.php"; ?>

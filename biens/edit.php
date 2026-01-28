<?php
require "guard.php";
require "../config/db.php";
include "../partials/header.php";

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM biens WHERE id = ?");
$stmt->execute([$id]);
$bien = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bien) {
    header("Location: index.php");
    exit;
}

// Fetch existing images
$stmt_images = $pdo->prepare("SELECT * FROM bien_images WHERE bien_id = ?");
$stmt_images->execute([$id]);
$images = $stmt_images->fetchAll(PDO::FETCH_ASSOC);

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $name = $_POST['name'];
    $description = $_POST['description']; // New: Get description
    $price = $_POST['price'];
    $status = $_POST['status'];

    if (empty($type) || empty($name) || empty($price) || empty($status)) {
        $error = "Please fill in all required fields.";
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("UPDATE biens SET type = ?, name = ?, description = ?, price_per_day = ?, status = ? WHERE id = ?");
            $stmt->execute([$type, $name, $description, $price, $status, $id]);

            // Handle new image uploads
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                $upload_dir = __DIR__ . "/../uploads/biens/";
                $allowed_types = ['jpg', 'jpeg', 'png', 'webp'];

                foreach ($_FILES['images']['name'] as $key => $image_name) {
                    $file_tmp = $_FILES['images']['tmp_name'][$key];
                    $file_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

                    if (in_array($file_ext, $allowed_types)) {
                        $new_file_name = uniqid('bien_', true) . '.' . $file_ext;
                        $destination = $upload_dir . $new_file_name;

                        if (move_uploaded_file($file_tmp, $destination)) {
                            $stmt = $pdo->prepare("INSERT INTO bien_images(bien_id, image_path) VALUES (?, ?)");
                            $stmt->execute([$id, $new_file_name]); // Store only filename
                        }
                    }
                }
            }

            $pdo->commit();
            header("Location: index.php");
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "An error occurred: " . $e->getMessage();
        }
    }
}
?>

<div class="container">
    <h2>Edit Property</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="form-group mb-3">
            <label for="type">Type</label>
            <select class="form-control" id="type" name="type">
                <option value="car" <?= $bien['type'] === 'car' ? 'selected' : '' ?>>Car</option>
                <option value="apartment" <?= $bien['type'] === 'apartment' ? 'selected' : '' ?>>Apartment</option>
            </select>
        </div>
        <div class="form-group mb-3">
            <label for="name">Name</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($bien['name']) ?>" required>
        </div>
        <div class="form-group mb-3">
            <label for="description">Description</label>
            <textarea class="form-control" id="description" name="description" rows="5"><?= htmlspecialchars($bien['description']) ?></textarea>
        </div>
        <div class="form-group mb-3">
            <label for="price">Price per day</label>
            <input type="number" class="form-control" id="price" name="price" step="0.01" value="<?= htmlspecialchars($bien['price_per_day']) ?>" required>
        </div>
        <div class="form-group mb-3">
            <label for="status">Status</label>
            <select class="form-control" id="status" name="status">
                <option value="available" <?= $bien['status'] === 'available' ? 'selected' : '' ?>>Available</option>
                <option value="rented" <?= $bien['status'] === 'rented' ? 'selected' : '' ?>>Rented</option>
                <option value="maintenance" <?= $bien['status'] === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
            </select>
        </div>

        <div class="form-group mb-3">
            <label>Current Images:</label>
            <div class="row">
                <?php if (!empty($images)): ?>
                    <?php foreach ($images as $image): ?>
                        <div class="col-md-3 mb-2">
                            <img src="<?= BASE_URL ?>uploads/biens/<?= htmlspecialchars($image['image_path']) ?>" class="img-thumbnail" alt="Bien Image">
                            <a href="<?= BASE_URL ?>biens/delete_image.php?image_id=<?= $image['id'] ?>&bien_id=<?= $bien['id'] ?>" class="btn btn-danger btn-sm mt-1" onclick="return confirm('Are you sure you want to delete this image?')">Delete</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12"><p>No images uploaded yet.</p></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-group mb-3">
            <label for="images">Upload New Images (optional)</label>
            <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/jpeg, image/png, image/webp">
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>

<?php include "../partials/footer.php"; ?>

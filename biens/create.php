<?php
require "guard.php";
require "../config/db.php";
include "../partials/header.php";

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

            $stmt = $pdo->prepare("INSERT INTO biens(type, name, description, price_per_day, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$type, $name, $description, $price, $status]);
            $bien_id = $pdo->lastInsertId();

            // Handle image uploads
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
                            $stmt->execute([$bien_id, $new_file_name]); // Store only filename
                        }
                    }
                }
            }

            $pdo->commit();
            header("Location: " . BASE_URL . "biens/index.php");
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "An error occurred: " . $e->getMessage();
        }
    }
}
?>

<div class="container">
    <h2>Add Property</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="form-group mb-3">
            <label for="type">Type</label>
            <select class="form-control" id="type" name="type">
                <option value="car">Car</option>
                <option value="apartment">Apartment</option>
            </select>
        </div>
        <div class="form-group mb-3">
            <label for="name">Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="form-group mb-3">
            <label for="description">Description</label>
            <textarea class="form-control" id="description" name="description" rows="5"></textarea>
        </div>
        <div class="form-group mb-3">
            <label for="price">Price per day</label>
            <input type="number" class="form-control" id="price" name="price" step="0.01" required>
        </div>
        <div class="form-group mb-3">
            <label for="status">Status</label>
            <select class="form-control" id="status" name="status">
                <option value="available">Available</option>
                <option value="rented">Rented</option>
                <option value="maintenance">Maintenance</option>
            </select>
        </div>
        <div class="form-group mb-3">
            <label for="images">Property Images</label>
            <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/jpeg, image/png, image/webp">
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</div>

<?php include "../partials/footer.php"; ?>

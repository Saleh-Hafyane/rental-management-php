<?php
require "guard.php";
require "../config/db.php";

$image_id = $_GET['image_id'] ?? null;
$bien_id = $_GET['bien_id'] ?? null;

if (!$image_id || !$bien_id) {
    header("Location: " . BASE_URL . "biens/index.php");
    exit;
}

try {
    $pdo->beginTransaction();

    // Get image path
    $stmt = $pdo->prepare("SELECT image_path FROM bien_images WHERE id = ?");
    $stmt->execute([$image_id]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($image) {
        $file_path = __DIR__ . "/../uploads/biens/" . $image['image_path'];

        // Delete file from server
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Delete record from database
        $stmt = $pdo->prepare("DELETE FROM bien_images WHERE id = ?");
        $stmt->execute([$image_id]);
    }

    $pdo->commit();
    header("Location: " . BASE_URL . "biens/edit.php?id=" . $bien_id);
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    // Log error or display a user-friendly message
    die("Error deleting image: " . $e->getMessage());
}
?>
<?php
require "guard.php";
require "../config/db.php";

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM biens WHERE id = ?");
$stmt->execute([$id]);
$bien = $stmt->fetch();

if ($bien) {
    $stmt = $pdo->prepare("DELETE FROM biens WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: index.php");
exit;

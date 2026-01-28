<?php
session_start();
require_once "../config/db.php"; // Include config to access BASE_URL
session_destroy();
header("Location: " . BASE_URL . "index.php");
exit;

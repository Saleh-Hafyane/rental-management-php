<?php
require_once "../config/db.php"; // Include config to access BASE_URL
header("Location: " . BASE_URL . "auth/login.php");
exit;

<?php
// ===========================================
// Journal Management System
// Database Configuration
// config.php
// ===========================================

// Database settings
$host = "localhost";
$dbname = "journal";
$username = "root";
$password = "";

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// UTF-8 support
$conn->set_charset("utf8mb4");

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Website settings
$site_name = "Academia Institute";
$site_url = "http://localhost/NII";

// Upload folder
$upload_dir = "uploads/";

// Default timezone
date_default_timezone_set("Asia/Almaty");
?>
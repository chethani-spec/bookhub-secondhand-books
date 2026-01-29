<?php
// Database Configuration
define('DB_HOST', 'sql201.infinityfree.com');
define('DB_USER', 'if0_40734059');
define('DB_PASS', 'BookHub2025');
define('DB_NAME', 'if0_40734059_bookhub');

// Create connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to utf8
mysqli_set_charset($conn, "utf8");

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Base URL 
define('BASE_URL', 'http://bookmarket2025.gt.tc/');

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

// Function to redirect
function redirect($page) {
    header("Location: " . BASE_URL . $page);
    exit();
}

// Function to sanitize input
function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}
?>
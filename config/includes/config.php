<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'dagang_in');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

// Define base paths
// Determine BASE_URL dynamically so assets load correctly on different hosts
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
// dirname(dirname($_SERVER['SCRIPT_NAME'])) => e.g. '/dagang.in' when running '/dagang.in/public/index.php'
$baseDir = rtrim(dirname(dirname(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '/')), '/\\');
define('BASE_URL', $protocol . '://' . $host . $baseDir);
define('BASE_PATH', dirname(dirname(dirname(__FILE__))));
define('PUBLIC_PATH', BASE_PATH . '/public');
define('ADMIN_PATH', BASE_PATH . '/admin');
define('ASSETS_PATH', BASE_PATH . '/assets');
define('CONFIG_PATH', BASE_PATH . '/config');
define('DATABASE_PATH', BASE_PATH . '/database');
define('TESTS_PATH', BASE_PATH . '/tests');


// Store configuration
define('STORE_NAME', 'Dagang.in');
define('STORE_DESCRIPTION', 'Platform Toko Online Terpercaya');

// Super Admin Email (untuk validasi penting, referensi ke depan)
define('SUPER_ADMIN_EMAIL', 'admin@dagang.in');

// CDN Assets - untuk konsistensi di semua template
define('BOOTSTRAP_CSS', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css');
define('BOOTSTRAP_JS', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js');
define('FONTAWESOME_CSS', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
?>
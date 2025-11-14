<?php
require_once '../config/includes/config.php';
require_once '../config/includes/auth.php';

// Test product display: Check if products are fetched without user_id filter
$products_query = $conn->query('SELECT COUNT(*) as count FROM products');
$result = $products_query->fetch_assoc();
echo 'Total products in database: ' . $result['count'] . PHP_EOL;

// Test login function with known credentials
$login_result = login('joko', '123');
echo 'Login result for joko/123: ' . ($login_result ? 'Success' : 'Failed') . PHP_EOL;

// Test isLoggedIn after login
echo 'Is logged in after login: ' . (isLoggedIn() ? 'Yes' : 'No') . PHP_EOL;

// Test logout (simulate by calling logout, but in CLI it won't redirect)
// logout();
// echo 'After logout, is logged in: ' . (isLoggedIn() ? 'Yes' : 'No') . PHP_EOL;
?>
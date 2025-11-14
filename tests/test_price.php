<?php
require_once '../config/includes/config.php';

// Test price parsing
$test_prices = ['10000', '10.000', '100,000', '1.000.000'];

foreach ($test_prices as $test_price) {
    $clean_price = str_replace(['.', ','], '', $test_price);
    $price = (float) $clean_price;
    echo "Input: $test_price -> Clean: $clean_price -> Float: $price\n";
}

// Test database insertion
$user_id = 3; // joko's user_id
$name = 'Test Product';
$description = 'Test description';
$price = 10000.00;
$stock = 5;
$image = '';

$stmt = $conn->prepare("INSERT INTO products (user_id, name, description, price, stock, image) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issdis", $user_id, $name, $description, $price, $stock, $image);

if ($stmt->execute()) {
    echo "Product inserted successfully with price: $price\n";
} else {
    echo "Failed to insert product\n";
}

// Check the inserted product
$result = $conn->query("SELECT name, price FROM products WHERE name = 'Test Product'");
if ($result && $row = $result->fetch_assoc()) {
    echo "Retrieved price: " . $row['price'] . "\n";
}
?>
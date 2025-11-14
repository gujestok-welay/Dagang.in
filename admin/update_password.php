<?php
require_once '../config/includes/config.php';

$username = 'joko';
$password = '123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
$stmt->bind_param("ss", $hashed_password, $username);
$result = $stmt->execute();

if ($result) {
    echo "Password updated successfully for user $username\n";
    echo "New hash: $hashed_password\n";
} else {
    echo "Failed to update password\n";
}
?>
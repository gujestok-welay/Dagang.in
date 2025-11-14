<?php
session_start();
require_once 'config.php';

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function login($username, $password)
{
    global $conn;

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            return true;
        }
    }
    return false;
}

function register($username, $email, $password, $store_name, $phone, $address)
{
    global $conn;

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, email, password, store_name, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $username, $email, $hashed_password, $store_name, $phone, $address);

    return $stmt->execute();
}

function logout()
{
    session_destroy();
    header("Location: ../public/index.php");
    exit();
}
?>
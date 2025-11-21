<?php
// Memuat file konfigurasi untuk koneksi database dan BASE_URL
require_once __DIR__ . '/../../config/includes/config.php';

// Untuk landing page publik, gunakan nama dari config atau default
// Jika ingin multi-store, bisa tambahkan logika berdasarkan domain/subdomain
$store_name = defined('STORE_NAME') ? STORE_NAME : 'Dagang.in';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Dagang.in - Platform e-commerce untuk UMKM Indonesia. Temukan produk berkualitas dari pengusaha lokal terpercaya dengan harga terjangkau.">
    <meta name="keywords" content="UMKM, toko online, produk lokal, e-commerce Indonesia, belanja online">
    <meta name="author" content="Dagang.in">
    <meta name="robots" content="index, follow">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($store_name); ?> - Platform UMKM Indonesia">
    <meta property="og:description" content="Temukan produk berkualitas dari UMKM lokal terpercaya">
    <meta property="og:type" content="website">
    <meta property="og:image" content="<?php echo BASE_URL; ?>/assets/images/log.png">

    <title><?php echo htmlspecialchars($store_name); ?> - Platform UMKM Indonesia</title>

    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">

    <!-- Stylesheets -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/images/log.png">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>/public/index.php">
                <!-- Gunakan BASE_URL untuk path yang konsisten -->
                <img src="<?php echo BASE_URL; ?>/assets/images/log.png" alt="Logo Toko">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#products">Produk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Kontak</a>
                    </li>
                </ul>
                <a href="<?php echo BASE_URL; ?>/login.php" class="btn btn-accent">Login Admin</a>
            </div>
        </div>
    </nav>
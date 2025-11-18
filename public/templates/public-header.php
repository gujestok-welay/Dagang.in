<?php
// Memuat file konfigurasi untuk koneksi database dan BASE_URL
require_once __DIR__ . '/../../config/includes/config.php';

// Ambil data user/toko untuk ditampilkan di header (misal: judul halaman)
// Logika ini mungkin perlu disesuaikan jika Anda memiliki banyak toko
$user_query = $conn->query("SELECT * FROM users LIMIT 1");
$user = $user_query->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['store_name'] ?? 'Dagang.in'); ?> - Toko Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Gunakan BASE_URL untuk path yang konsisten -->
    <link href="<?php echo BASE_URL; ?>/assets/css/style.css" rel="stylesheet">
    <!-- Anda mungkin perlu menambahkan FontAwesome di sini jika ikon tidak muncul -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-complementary ">
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
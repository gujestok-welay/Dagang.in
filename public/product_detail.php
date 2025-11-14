<?php
require_once '../config/includes/config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$product_id = $_GET['id'];

// Get product with store name
$product_query = $conn->prepare("SELECT products.*, users.store_name, users.phone, users.email, users.address FROM products JOIN users ON products.user_id = users.id WHERE products.id = ?");
$product_query->bind_param("i", $product_id);
$product_query->execute();
$product = $product_query->get_result()->fetch_assoc();

if (!$product) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Detail Produk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-brand">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <strong>Dagang.in</strong>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#products">Produk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#contact">Kontak</a>
                    </li>
                </ul>
                <a href="../login.php" class="btn btn-accent">Login Admin</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-6">
                <?php if ($product['image']): ?>
                    <img src="../assets/uploads/<?php echo htmlspecialchars($product['image']); ?>"
                        class="img-fluid rounded" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <?php else: ?>
                    <div class="bg-light d-flex align-items-center justify-content-center rounded" style="height: 400px;">
                        <span class="text-muted">No Image</span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <h1>
                    <?php echo htmlspecialchars($product['name']); ?>
                </h1>
                <p class="text-muted">Toko: <?php echo htmlspecialchars($product['store_name']); ?></p>
                <h3 class="text-accent">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                </h3>
                <p><strong>Stok:</strong>
                    <?php echo $product['stock']; ?>
                </p>
                <p><strong>Deskripsi:</strong></p>
                <p>
                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                </p>

                <div class="mt-4">
                    <a href="https://wa.me/<?php echo $product['phone'] ?? '628123456789'; ?>?text=Halo,%20saya%20tertarik%20dengan%20produk%20<?php echo urlencode($product['name']); ?>%20dengan%20harga%20Rp%20<?php echo number_format($product['price'], 0, ',', '.'); ?>"
                        class="btn whatsapp-btn btn-lg" target="_blank">
                        <i class="fab fa-whatsapp"></i> Hubungi via WhatsApp
                    </a>
                    <a href="index.php" class="btn btn-secondary btn-lg ms-2">Kembali ke Produk</a>
                </div>

                <div class="mt-4">
                    <h5>Informasi Toko</h5>
                    <p><strong>
                            <?php echo htmlspecialchars($product['store_name']); ?></strong></p>
                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($product['phone'] ?? '08123456789'); ?>
                    </p>
                    <p><i class="fas fa-envelope"></i>
                        <?php echo htmlspecialchars($product['email'] ?? 'info@dagang.in'); ?>
                    </p>
                    <p><i class="fas fa-map-marker-alt"></i>
                        <?php echo htmlspecialchars($product['address'] ?? 'Jl. Contoh No. 1, Kota'); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</body>

</html>
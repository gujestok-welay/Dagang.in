<?php
require_once '../config/includes/auth.php';
require_once '../config/includes/config.php';

if (isset($_GET['logout'])) {
    logout();
}

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle delete
if (isset($_GET['delete'])) {
    $product_id = $_GET['delete'];
    $delete_stmt = $conn->prepare("DELETE FROM products WHERE id = ? AND user_id = ?");
    $delete_stmt->bind_param("ii", $product_id, $user_id);
    $delete_stmt->execute();
    header("Location: products.php");
    exit();
}

// Get products
$products_query = $conn->prepare("SELECT * FROM products WHERE user_id = ? ORDER BY created_at DESC");
$products_query->bind_param("i", $user_id);
$products_query->execute();
$products = $products_query->get_result();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Produk - Dagang.in</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-brand">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Dagang.in</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="products.php">Produk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">Pesanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="customers.php">Pelanggan</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="?logout=1">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manajemen Produk</h2>
            <a href="add_product.php" class="btn btn-primary">Tambah Produk</a>
        </div>

        <div class="row">
            <?php while ($product = $products->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card product-card h-100">
                        <?php if ($product['image']): ?>
                            <img src="../assets/uploads/<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top"
                                alt="<?php echo htmlspecialchars($product['name']); ?>"
                                style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                                style="height: 200px;">
                                <span class="text-muted">No Image</span>
                            </div>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class=" card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="card-text">
                                <?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...
                            </p>
                            <p class="card-text"><strong>Rp
                                    <?php echo number_format($product['price'], 0, ',', '.'); ?></strong>
                            </p>
                            <p class="card-text">Stok:
                                <?php echo $product['stock']; ?>
                            </p>
                            <div class="mt-auto">
                                <a href="edit_product.php?id=<?php echo $product['id']; ?>"
                                    class="btn btn-warning btn-sm">Edit</a>
                                <a href=" ?delete=<?php echo $product['id']; ?>" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Yakin hapus produk ini?')">Hapus</a> <a
                                    href="https://wa.me/?text=Halo, saya tertarik dengan produk <?php echo urlencode($product['name']); ?>"
                                    class="btn whatsapp-btn btn-sm" target="_blank">WhatsApp</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
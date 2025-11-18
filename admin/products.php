<?php
require_once '../config/includes/auth.php';
require_once '../config/includes/config.php';

$currentPage = 'products';
$pageTitle = 'Manajemen Produk';
require_once 'templates/header.php';
// 1. Tentukan halaman saat ini, ini akan memberitahu header.php link mana yang harus diberi kelas 'active'
$currentPage = 'products';
$pageTitle = 'Manajemen Produk'; // Judul untuk tag <title>

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

require_once 'templates/header.php';
?>



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
                            alt="<?php echo htmlspecialchars($product['name']); ?>" style="height: 200px; object-fit: cover;">
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
                                href="https://wa.me/6282197771318?text=Halo, saya tertarik dengan produk <?php echo urlencode($product['name']); ?>"
                                class="btn whatsapp-btn btn-sm" target="_blank">WhatsApp</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>
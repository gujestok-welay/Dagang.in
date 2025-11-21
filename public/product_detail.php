<?php
require_once __DIR__ . '/templates/public-header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$product_id = (int) $_GET['id'];

// Get product with store name
$product_query = $conn->prepare("SELECT products.*, users.store_name, users.phone, users.email, users.address FROM products JOIN users ON products.user_id = users.id WHERE products.id = ?");
$product_query->bind_param("i", $product_id);
$product_query->execute();
$product = $product_query->get_result()->fetch_assoc();

if (!$product) {
    header("Location: index.php");
    exit();
}

// Get related products from the same store (exclude current product)
$related_query = $conn->prepare("SELECT products.*, users.store_name FROM products JOIN users ON products.user_id = users.id WHERE products.user_id = ? AND products.id != ? LIMIT 4");
$related_query->bind_param("ii", $product['user_id'], $product_id);
$related_query->execute();
$related_products = $related_query->get_result()->fetch_all(MYSQLI_ASSOC);

// Sanitize phone number for WhatsApp (remove non-numeric characters)
$whatsapp_phone = preg_replace('/[^0-9]/', '', $product['phone'] ?? '6282197771318');
// Ensure it starts with country code
if (substr($whatsapp_phone, 0, 1) === '0') {
    $whatsapp_phone = '62' . substr($whatsapp_phone, 1);
} elseif (substr($whatsapp_phone, 0, 2) !== '62') {
    $whatsapp_phone = '62' . $whatsapp_phone;
}
?>

<!-- Breadcrumb Navigation -->
<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/public/index.php">Beranda</a></li>
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/public/index.php#products">Produk</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['name']); ?>
            </li>
        </ol>
    </nav>
</div>

<div class="container mb-5">
    <div class="row">
        <div class="col-md-6 mb-4">
            <?php if ($product['image']): ?>
                <img src="<?php echo BASE_URL; ?>/assets/uploads/<?php echo htmlspecialchars($product['image']); ?>"
                    class="img-fluid rounded shadow" style="max-height: 500px; width: 100%; object-fit: cover;"
                    alt="<?php echo htmlspecialchars($product['name']); ?>" loading="lazy">
            <?php else: ?>
                <div class="bg-light d-flex align-items-center justify-content-center rounded shadow"
                    style="height: 500px;">
                    <span class="text-muted"><i class="fas fa-image fa-3x"></i><br>No Image</span>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-md-6">
            <h1 class="mb-3">
                <?php echo htmlspecialchars($product['name']); ?>
            </h1>
            <p class="text-muted mb-3">
                <i class="fas fa-store me-2"></i>
                <strong>Toko:</strong>
                <a href="<?php echo BASE_URL; ?>/public/index.php?search=&store=<?php echo urlencode($product['store_name']); ?>"
                    class="text-decoration-none">
                    <?php echo htmlspecialchars($product['store_name']); ?>
                </a>
            </p>
            <h3 class="text-accent mb-3">
                <strong>Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></strong>
            </h3>
            <p class="mb-3">
                <strong>Stok:</strong>
                <?php if ($product['stock'] > 10): ?>
                    <span class="badge bg-success"><?php echo $product['stock']; ?> tersedia</span>
                <?php elseif ($product['stock'] > 0): ?>
                    <span class="badge bg-warning text-dark"><?php echo $product['stock']; ?> tersisa</span>
                <?php else: ?>
                    <span class="badge bg-danger">Habis</span>
                <?php endif; ?>
            </p>

            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Deskripsi Produk</h5>
                    <p class="card-text">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </p>
                </div>
            </div>

            <div class="d-grid gap-2 mb-4">
                <a href="https://wa.me/<?php echo $whatsapp_phone; ?>?text=Halo,%20saya%20tertarik%20dengan%20produk%20<?php echo urlencode($product['name']); ?>%20dengan%20harga%20Rp%20<?php echo number_format($product['price'], 0, ',', '.'); ?>"
                    class="btn whatsapp-btn btn-lg" target="_blank" rel="noopener noreferrer">
                    <i class="fab fa-whatsapp me-2"></i> Hubungi via WhatsApp
                </a>
                <a href="<?php echo BASE_URL; ?>/public/index.php" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-arrow-left me-2"></i> Kembali ke Produk
                </a>
            </div>

            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-store me-2"></i>Informasi Toko</h5>
                </div>
                <div class="card-body">
                    <h6 class="card-title fw-bold"><?php echo htmlspecialchars($product['store_name']); ?></h6>
                    <hr>
                    <p class="mb-2">
                        <i class="fas fa-phone text-primary me-2"></i>
                        <a href="tel:<?php echo htmlspecialchars($product['phone'] ?? '082197771318'); ?>"
                            class="text-decoration-none">
                            <?php echo htmlspecialchars($product['phone'] ?? '082197771318'); ?>
                        </a>
                    </p>
                    <p class="mb-2">
                        <i class="fas fa-envelope text-primary me-2"></i>
                        <a href="mailto:<?php echo htmlspecialchars($product['email'] ?? 'gujestokjondrywelay@gmail.com'); ?>"
                            class="text-decoration-none">
                            <?php echo htmlspecialchars($product['email'] ?? 'gujestokjondrywelay@gmail.com'); ?>
                        </a>
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-map-marker-alt text-primary me-2"></i>
                        <?php echo htmlspecialchars($product['address'] ?? 'UKIM, Makassar'); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products Section -->
    <?php if (!empty($related_products)): ?>
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="mb-4">
                    <i class="fas fa-box-open me-2"></i>Produk Lainnya dari Toko Ini
                </h3>
            </div>
            <?php foreach ($related_products as $related): ?>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card product-card h-100 shadow-sm">
                        <?php if ($related['image']): ?>
                            <img src="<?php echo BASE_URL; ?>/assets/uploads/<?php echo htmlspecialchars($related['image']); ?>"
                                class="card-img-top" style="height: 200px; object-fit: cover;"
                                alt="<?php echo htmlspecialchars($related['name']); ?>" loading="lazy">
                        <?php else: ?>
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                                style="height: 200px;">
                                <span class="text-muted"><i class="fas fa-image fa-2x"></i></span>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title text-truncate" title="<?php echo htmlspecialchars($related['name']); ?>">
                                <?php echo htmlspecialchars($related['name']); ?>
                            </h5>
                            <p class="text-muted small mb-2">
                                <i class="fas fa-store me-1"></i><?php echo htmlspecialchars($related['store_name']); ?>
                            </p>
                            <p class="text-accent fw-bold mb-3">
                                Rp <?php echo number_format($related['price'], 0, ',', '.'); ?>
                            </p>
                            <a href="<?php echo BASE_URL; ?>/public/product_detail.php?id=<?php echo $related['id']; ?>"
                                class="btn btn-primary w-100">
                                <i class="fas fa-eye me-2"></i>Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/templates/public-footer.php'; ?>
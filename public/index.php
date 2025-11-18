<?php
require_once __DIR__ . '/templates/public-header.php';
// Get products with store name
$products_query = $conn->query("SELECT products.*, users.store_name FROM products JOIN users ON products.user_id = users.id ORDER BY products.created_at DESC");
?>


<section id="home" class="bg-brand text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1>Selamat Datang di <?php echo htmlspecialchars($user['store_name'] ?? 'Dagang.in'); ?></h1>
                <p class="lead">Temukan produk berkualitas untuk kebutuhan Anda. Kami siap melayani dengan sepenuh
                    hati.</p>

            </div>
            <div class="col-md-6">
                <img src="../assets/images/store.jpg" alt="Toko" class="img-fluid rounded"
                    onerror="this.style.display='none'">
            </div>
        </div>
    </div>
</section>

<section id="products" class="py-5">
    <div class="container">
        <h2 class="text-center mb-4">Produk Kami</h2>
        <div class="row">
            <?php while ($product = $products_query->fetch_assoc()): ?>
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
                            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="card-text">
                                <?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...
                            </p>
                            <p class="card-text"><strong>Rp
                                    <?php echo number_format($product['price'], 0, ',', '.'); ?></strong></p>
                            <p class="card-text">Stok: <?php echo $product['stock']; ?></p>
                            <p class="card-text"><small class="text-muted">Toko:
                                    <?php echo htmlspecialchars($product['store_name']); ?></small></p>
                            <div class="mt-auto">
                                <a href="product_detail.php?id=<?php echo $product['id']; ?>"
                                    class="btn btn-primary btn-sm">Lihat Detail</a>
                                <a href="https://wa.me/<?php echo $user['phone'] ?? '628123456789'; ?>?text=Halo,%20saya%20tertarik%20dengan%20produk%20<?php echo urlencode($product['name']); ?>%20dengan%20harga%20Rp%20<?php echo number_format($product['price'], 0, ',', '.'); ?>"
                                    class="btn whatsapp-btn btn-sm" target="_blank">
                                    <i class="fab fa-whatsapp"></i> Hubungi via WhatsApp
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<section id="contact" class="bg-neutral py-5">
    <div class="container">
        <h2 class="text-center mb-4">Kontak Kami</h2>
        <div class="row">
            <div class="col-md-6">
                <h5>Informasi Kontak</h5>
            
                <p><i class="fas fa-phone"></i> 082197771318
                </p>
                <p><i class="fas fa-envelope"></i> gujestokjondrywelay@gmail.com
                </p>
                <p><i class="fas fa-map-marker-alt"></i> UKIM
                </p>
            </div>
            <div class="col-md-6">
                <h5>Hubungi Kami</h5>
                <a href="https://wa.me/<?php echo $user['phone'] ?? '628123456789'; ?>?text=Halo,%20saya%20ingin%20bertanya%20tentang%20produk%20Anda"
                    class="btn whatsapp-btn btn-lg" target="_blank">
                    <i class="fab fa-whatsapp"></i> Chat WhatsApp
                </a>
            </div>
        </div>
    </div>
</section>

<?php
// 3. Muat footer
require_once __DIR__ . '/templates/public-footer.php';
?>
<?php
require_once __DIR__ . '/templates/public-header.php';
require_once __DIR__ . '/../config/utils/Pagination.php';

// Get search and filter parameters
$search = trim($_GET['search'] ?? '');
$min_price = isset($_GET['min_price']) ? max(0, (float) $_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? max(0, (float) $_GET['max_price']) : 999999999;
$stock_filter = $_GET['stock'] ?? ''; // 'in_stock', 'low_stock', 'all'
$current_page = max(1, (int) ($_GET['page'] ?? 1));

// Build WHERE clause for filtering
$where_conditions = ['products.is_deleted = 0'];
$params = [];
$param_types = '';

// Search by product name or description
if (!empty($search)) {
    $search_term = '%' . $search . '%';
    $where_conditions[] = "(products.name LIKE ? OR products.description LIKE ?)";
    $params[] = $search_term;
    $params[] = $search_term;
    $param_types .= 'ss';
}

// Price filter
if ($min_price > 0 || $max_price < 999999999) {
    $where_conditions[] = "products.price BETWEEN ? AND ?";
    $params[] = $min_price;
    $params[] = $max_price;
    $param_types .= 'dd';
}

// Stock filter
if ($stock_filter === 'in_stock') {
    $where_conditions[] = "products.stock > 0";
} elseif ($stock_filter === 'low_stock') {
    $where_conditions[] = "products.stock > 0 AND products.stock <= 10";
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Count total products matching filters
$count_query_sql = "SELECT COUNT(*) as total FROM products JOIN users ON products.user_id = users.id $where_clause";
$count_query = $conn->prepare($count_query_sql);

if ($count_query && !empty($params)) {
    $count_query->bind_param($param_types, ...$params);
}

if ($count_query) {
    $count_query->execute();
    $count_result = $count_query->get_result();
    $count_row = $count_result->fetch_assoc();
    $total_products = $count_row['total'];
    $count_query->close();
} else {
    $total_products = 0;
}

// Pagination settings
$items_per_page = 12;
$pagination = new Pagination($total_products, $items_per_page, $current_page);

// Get products with pagination
$products_query_sql = "SELECT products.*, users.store_name, users.phone as seller_phone FROM products JOIN users ON products.user_id = users.id $where_clause 
ORDER BY products.created_at DESC 
LIMIT ? OFFSET ?";

$products_query = $conn->prepare($products_query_sql);

if ($products_query) {
    // Add limit and offset parameters
    $offset = $pagination->getOffset();
    $limit = $pagination->getLimit();
    $all_params = $params;
    $all_params[] = $limit;
    $all_params[] = $offset;
    $all_param_types = $param_types . 'ii';

    if (!empty($all_params)) {
        $products_query->bind_param($all_param_types, ...$all_params);
    }

    $products_query->execute();
    $products = $products_query->get_result();
} else {
    $products = null;
}
?>

<!-- Hero Section -->
<section id="home" class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 col-md-12">
                <div class="hero-content">
                    <h1 class="hero-title">
                        Berkembang Bersama <span class="text-gradient"
                            style="-webkit-text-fill-color: #ffd460 !important;">UMKM Lokal</span>
                    </h1>
                    <p class="hero-subtitle">
                        Temukan produk berkualitas dari UMKM Indonesia. Dukung ekonomi lokal dengan berbelanja produk
                        pilihan dari para pengusaha terbaik di seluruh nusantara.
                    </p>
                    <a href="#products" class="hero-btn">
                        <i class="fas fa-shopping-bag me-2"></i> Jelajahi Produk
                    </a>
                </div>
            </div>
            <div class="col-lg-6 col-md-12 mt-5 mt-lg-0">
                <div class="hero-image-wrapper text-center">
                    <img src="../assets/images/store.jpg" alt="Toko UMKM" class="img-fluid rounded"
                        style="border-radius: 30px !important; box-shadow: 0 20px 60px rgba(0,0,0,0.3);"
                        onerror="this.src='https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=600&h=400&fit=crop'">

                    <!-- Floating Badges -->
                    <div class="hero-badge hero-badge-1">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle text-success fs-3 me-2"></i>
                            <div class="text-start">
                                <small class="text-muted d-block">Produk</small>
                                <strong class="text-dark">Terjamin</strong>
                            </div>
                        </div>
                    </div>

                    <div class="hero-badge hero-badge-2">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-shipping-fast text-primary fs-3 me-2"></i>
                            <div class="text-start">
                                <small class="text-muted d-block">Pengiriman</small>
                                <strong class="text-dark">Cepat</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Mengapa Memilih Kami?</h2>
            <p class="section-subtitle">Dapatkan pengalaman belanja terbaik dengan berbagai keunggulan kami</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="feature-title">100% Terpercaya</h3>
                    <p class="feature-description">Semua produk telah melewati verifikasi ketat untuk menjamin kualitas
                        terbaik</p>
                </div>
            </div>
           
            <div class="col-lg-3 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3 class="feature-title">Layanan 24/7</h3>
                    <p class="feature-description">Tim customer service kami siap membantu Anda kapan saja</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3 class="feature-title">Dukung UMKM</h3>
                    <p class="feature-description">Setiap pembelian membantu mengembangkan ekonomi lokal Indonesia</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                <div class="stat-card">
                    <span class="stat-number"><?php echo number_format($total_products); ?>+</span>
                    <span class="stat-label">Produk Tersedia</span>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                <div class="stat-card">
                    <span class="stat-number">500+</span>
                    <span class="stat-label">Pelanggan Puas</span>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                <div class="stat-card">
                    <span class="stat-number">50+</span>
                    <span class="stat-label">UMKM Partner</span>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <span class="stat-number">100%</span>
                    <span class="stat-label">Produk Original</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Products Section -->
<section id="products" class="products-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Produk Pilihan Kami</h2>
            <p class="section-subtitle">Jelajahi koleksi produk berkualitas dari UMKM terpercaya</p>
        </div>

        <!-- Search and Filter Form -->
        <div class="filter-card">
            <form method="GET" action="index.php" id="filter-form" class="row g-3 align-items-end">
                <div class="col-lg-4 col-md-6">
                    <label for="search-input" class="form-label fw-semibold">
                        <i class="fas fa-search me-2"></i>Cari Produk
                    </label>
                    <input type="text" class="form-control" id="search-input" name="search"
                        placeholder="Ketik nama produk..." value="<?php echo htmlspecialchars($search); ?>"
                        aria-label="Cari produk">
                </div>
                <div class="col-lg-2 col-md-3">
                    <label for="min-price-input" class="form-label fw-semibold">
                        <i class="fas fa-money-bill-wave me-2"></i>Harga Min
                    </label>
                    <input type="number" class="form-control" id="min-price-input" name="min_price" placeholder="Rp 0"
                        min="0" step="1000" value="<?php echo $min_price > 0 ? $min_price : ''; ?>"
                        aria-label="Harga minimum">
                </div>
                <div class="col-lg-2 col-md-3">
                    <label for="max-price-input" class="form-label fw-semibold">
                        <i class="fas fa-money-bill-wave me-2"></i>Harga Maks
                    </label>
                    <input type="number" class="form-control" id="max-price-input" name="max_price" placeholder="Rp Max"
                        min="0" step="1000" value="<?php echo $max_price < 999999999 ? $max_price : ''; ?>"
                        aria-label="Harga maksimum">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label for="stock-filter" class="form-label fw-semibold">
                        <i class="fas fa-box me-2"></i>Stok
                    </label>
                    <select class="form-select" id="stock-filter" name="stock" aria-label="Filter stok produk">
                        <option value="">Semua Stok</option>
                        <option value="in_stock" <?php echo $stock_filter === 'in_stock' ? 'selected' : ''; ?>>Ada Stok
                        </option>
                        <option value="low_stock" <?php echo $stock_filter === 'low_stock' ? 'selected' : ''; ?>>Stok
                            Terbatas</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>Filter
                    </button>
                </div>
            </form>
            <?php if (!empty($search) || $min_price > 0 || $max_price < 999999999 || !empty($stock_filter)): ?>
                <div class="mt-3 text-center" id="reset-filter-container">
                    <button type="button" id="reset-filter-btn" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-redo me-2"></i>Reset Filter
                    </button>
                </div>
            <?php else: ?>
                <div class="mt-3 text-center" id="reset-filter-container" style="display: none;">
                    <button type="button" id="reset-filter-btn" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-redo me-2"></i>Reset Filter
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($total_products > 0): ?>
            <div class="text-center mb-4">
                <span id="product-count-badge" class="badge bg-primary"
                    style="font-size: 1rem; padding: 0.75rem 1.5rem; border-radius: 50px;">
                    Menampilkan <?php echo $pagination->getStartItem(); ?> - <?php echo $pagination->getEndItem(); ?> dari
                    <strong><?php echo $total_products; ?></strong> produk
                </span>
            </div>
        <?php else: ?>
            <div class="text-center mb-4">
                <span id="product-count-badge" class="badge bg-primary"
                    style="font-size: 1rem; padding: 0.75rem 1.5rem; border-radius: 50px; display: none;">
                </span>
            </div>
        <?php endif; ?>

        <div class="row g-4" id="products-container">
            <?php if ($products && $products->num_rows > 0): ?>
                <?php while ($product = $products->fetch_assoc()): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card product-card h-100">
                            <?php if ($product['stock'] > 0 && $product['stock'] <= 10): ?>
                                <span class="badge-new">Stok Terbatas!</span>
                            <?php endif; ?>

                            <?php
                            // Determine image to display with fallback chain
                            $image_src = '../assets/images/placeholder-product.svg'; // Default placeholder
                    
                            if (!empty($product['image'])) {
                                // Check if thumbnail exists
                                $thumb_path = '../assets/uploads/thumbs/' . $product['image'];
                                $original_path = '../assets/uploads/' . $product['image'];

                                if (file_exists(__DIR__ . '/' . $thumb_path)) {
                                    $image_src = $thumb_path;
                                } elseif (file_exists(__DIR__ . '/' . $original_path)) {
                                    $image_src = $original_path;
                                }
                            }
                            ?>
                            <img src="<?php echo htmlspecialchars($image_src); ?>" class="card-img-top"
                                alt="<?php echo htmlspecialchars($product['name']); ?>"
                                style="height: 260px; object-fit: cover;"
                                onerror="this.onerror=null; this.src='../assets/images/placeholder-product.svg';">

                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($product['name']); ?></h5>
                                </div>

                                <p class="card-text text-muted" style="font-size: 0.9rem;">
                                    <?php echo htmlspecialchars(substr($product['description'], 0, 85)); ?>...
                                </p>

                                <span class="price-tag">
                                    Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                                </span>

                                <div class="mb-3">
                                    <span class="stock-badge">
                                        <i class="fas fa-box me-1"></i>
                                        Stok: <?php echo $product['stock']; ?>
                                    </span>
                                </div>

                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-store text-primary me-2"></i>
                                    <small class="text-muted"><?php echo htmlspecialchars($product['store_name']); ?></small>
                                </div>

                                <div class="mt-auto d-flex gap-2">
                                    <a href="product_detail.php?id=<?php echo $product['id']; ?>"
                                        class="btn btn-primary flex-grow-1">
                                        <i class="fas fa-eye me-2"></i>Detail
                                    </a>
                                    <?php
                                    // Format phone number for WhatsApp (remove leading 0, add 62)
                                    $whatsapp_phone = !empty($product['seller_phone']) ? $product['seller_phone'] : '628123456789';
                                    if (substr($whatsapp_phone, 0, 1) === '0') {
                                        $whatsapp_phone = '62' . substr($whatsapp_phone, 1);
                                    }
                                    $whatsapp_message = "Halo, saya tertarik dengan produk *" . $product['name'] . "* dengan harga Rp " . number_format($product['price'], 0, ',', '.');
                                    ?>
                                    <a href="https://wa.me/<?php echo htmlspecialchars($whatsapp_phone); ?>?text=<?php echo urlencode($whatsapp_message); ?>"
                                        class="btn whatsapp-btn" target="_blank" rel="noopener noreferrer"
                                        title="Chat via WhatsApp">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-box-open" style="font-size: 5rem; color: var(--text-gray); opacity: 0.3;"></i>
                        <h3 class="mt-4 text-muted">Produk Tidak Ditemukan</h3>
                        <p class="text-muted">Coba ubah filter pencarian Anda</p>
                        <a href="index.php" class="btn btn-primary mt-3">
                            <i class="fas fa-redo me-2"></i>Tampilkan Semua Produk
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($products && $pagination->getTotalPages() > 1): ?>
            <div class="mt-4" id="pagination-container">
                <?php
                $extra_params = [];
                if (!empty($search))
                    $extra_params['search'] = $search;
                if ($min_price > 0)
                    $extra_params['min_price'] = $min_price;
                if ($max_price < 999999999)
                    $extra_params['max_price'] = $max_price;
                if (!empty($stock_filter))
                    $extra_params['stock'] = $stock_filter;
                echo $pagination->render('index.php', 'page', $extra_params);
                ?>
            </div>
        <?php else: ?>
            <div class="mt-4" id="pagination-container"></div>
        <?php endif; ?>
    </div>
</section>

<!-- Contact Section -->
<section id="contact" class="contact-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Hubungi Kami</h2>
            <p class="section-subtitle">Kami siap membantu Anda dengan sepenuh hati</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="contact-info-card">
                    <h4 class="fw-bold mb-4 text-center" style="color: var(--primary-color);">
                        <i class="fas fa-info-circle me-2"></i>Informasi Kontak
                    </h4>

                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div>
                            <small class="d-block text-muted">Telepon</small>
                            <strong style="font-size: 1.1rem;">082197771318</strong>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <small class="d-block text-muted">Email</small>
                            <strong style="font-size: 1rem;">DagangIN@gmail.com</strong>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <small class="d-block text-muted">Alamat</small>
                            <strong style="font-size: 1.1rem;">UKIM, Ambon</strong>
                        </div>
                    </div>

                    <div class="mt-4 text-center">
                        <p class="text-muted mb-3">Ikuti kami di:</p>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="#" class="btn btn-outline-primary"
                                style="width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="btn btn-outline-danger"
                                style="width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="btn btn-outline-info"
                                style="width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fab fa-twitter"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// 3. Muat footer
require_once __DIR__ . '/templates/public-footer.php';
?>
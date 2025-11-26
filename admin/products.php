<?php
$currentPage = 'products';
$pageTitle = 'Manajemen Produk';

require_once 'templates/header.php';
require_once '../config/utils/Pagination.php';

// CSRF helpers (copy dari categories.php jika belum ada)
if (!function_exists('get_csrf_token')) {
    function get_csrf_token()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
if (!function_exists('verify_csrf_token')) {
    function verify_csrf_token($token)
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = 'danger';

// Handle bulk actions (Soft Delete)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['bulk_action'] ?? '';
    $selected_ids = $_POST['selected_products'] ?? [];

    if (!empty($selected_ids) && is_array($selected_ids)) {
        $selected_ids = array_map('intval', $selected_ids);

        if ($action === 'delete') {
            try {
                // Soft delete: update is_deleted = 1
                $ids_str = implode(',', $selected_ids);
                $update_stmt = $conn->prepare("UPDATE products SET is_deleted = 1 WHERE id IN (" . str_repeat('?,', count($selected_ids) - 1) . "?) AND user_id = ?");
                $params = array_merge($selected_ids, [$user_id]);
                $param_types = str_repeat('i', count($selected_ids) + 1);
                $update_stmt->bind_param($param_types, ...$params);

                if ($update_stmt->execute()) {
                    $message = "Produk berhasil diarsipkan (Soft Delete).";
                    $message_type = 'success';
                } else {
                    $message = "Gagal mengarsipkan produk.";
                }
                $update_stmt->close();
            } catch (Exception $e) {
                $message = "Error: " . $e->getMessage();
            }
        }
    } else {
        $message = "Pilih produk terlebih dahulu.";
    }
}

// Handle single delete (Soft Delete, tanpa hapus file)
if (isset($_GET['delete'])) {
    $product_id = (int) $_GET['delete'];
    $token = $_GET['token'] ?? '';
    if (!verify_csrf_token($token)) {
        die('Token CSRF tidak valid.');
    }

    // Soft delete: update is_deleted = 1
    $update_stmt = $conn->prepare("UPDATE products SET is_deleted = 1 WHERE id = ? AND user_id = ?");
    $update_success = false;
    if ($update_stmt) {
        $update_stmt->bind_param("ii", $product_id, $user_id);
        $update_success = $update_stmt->execute();
        $update_stmt->close();
    }

    if ($update_success) {
        // Redirect dengan pesan sukses
        header("Location: products.php?msg=softdeleted");
        exit();
    } else {
        $message = "Gagal mengarsipkan produk. Kemungkinan produk sudah memiliki riwayat transaksi/pesanan.";
        $message_type = 'danger';
    }
}

// Pagination settings
$items_per_page = 12;
$current_page = max(1, (int) ($_GET['page'] ?? 1));

// Get search and filter parameters
$search = trim($_GET['search'] ?? '');
$min_price = isset($_GET['min_price']) ? max(0, (float) $_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? max(0, (float) $_GET['max_price']) : 999999999;
$stock_filter = $_GET['stock'] ?? ''; // 'in_stock', 'low_stock', 'all'
$category_filter = isset($_GET['category_id']) ? (int) $_GET['category_id'] : 0;

// Build WHERE clause for filtering
$where_conditions = ["user_id = ?", "is_deleted = 0"];
$params = [$user_id];
$param_types = 'i';

// Search by product name or description
if (!empty($search)) {
    $search_term = '%' . $search . '%';
    $where_conditions[] = "(name LIKE ? OR description LIKE ?)";
    $params[] = $search_term;
    $params[] = $search_term;
    $param_types .= 'ss';
}

// Price filter
if ($min_price > 0 || $max_price < 999999999) {
    $where_conditions[] = "price BETWEEN ? AND ?";
    $params[] = $min_price;
    $params[] = $max_price;
    $param_types .= 'dd';
}

// Stock filter
if ($stock_filter === 'in_stock') {
    $where_conditions[] = "stock > 0";
} elseif ($stock_filter === 'low_stock') {
    $where_conditions[] = "stock > 0 AND stock <= 10";
}

// Category filter
if ($category_filter > 0) {
    $where_conditions[] = "category_id = ?";
    $params[] = $category_filter;
    $param_types .= 'i';
}

$where_clause = implode(' AND ', $where_conditions);

// Count total products
// Tambahkan filter is_deleted = 0 ke count
$count_query = $conn->prepare("SELECT COUNT(*) as total FROM products WHERE $where_clause");
if (!$count_query) {
    die("Database error: " . $conn->error);
}

$count_query->bind_param($param_types, ...$params);
$count_query->execute();
$count_result = $count_query->get_result();
$count_row = $count_result->fetch_assoc();
$total_products = $count_row['total'];
$count_query->close();

// Initialize pagination
$pagination = new Pagination($total_products, $items_per_page, $current_page);

// Get products with pagination
$offset = $pagination->getOffset();
$limit = $pagination->getLimit();

$query_params = $params;
$query_params[] = $limit;
$query_params[] = $offset;
$query_param_types = $param_types . 'ii';

$products_query = $conn->prepare("SELECT * FROM products WHERE $where_clause ORDER BY created_at DESC LIMIT ? OFFSET ?");
if (!$products_query) {
    die("Database error: " . $conn->error);
}

$products_query->bind_param($query_param_types, ...$query_params);
$products_query->execute();
$products = $products_query->get_result();
$products_query->close();

// Get categories for filter dropdown
$categories_for_filter = null;
$categories_query = $conn->prepare("SELECT id, name FROM categories WHERE user_id = ? ORDER BY name");
if ($categories_query) {
    $categories_query->bind_param("i", $user_id);
    $categories_query->execute();
    $categories_for_filter = $categories_query->get_result();
}
?>



<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manajemen Produk</h2>
        <a href="export_products.php?format=csv" class="btn btn-success">
            <i class="fas fa-file-csv"></i> Export Data (CSV)
        </a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Search and Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" placeholder="Cari produk..."
                        value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-2">
                    <input type="number" class="form-control" name="min_price" placeholder="Harga min" min="0"
                        value="<?php echo $min_price > 0 ? $min_price : ''; ?>">
                </div>
                <div class="col-md-2">
                    <input type="number" class="form-control" name="max_price" placeholder="Harga maks" min="0"
                        value="<?php echo $max_price < 999999999 ? $max_price : ''; ?>">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="stock">
                        <option value="">Semua Stock</option>
                        <option value="in_stock" <?php echo $stock_filter === 'in_stock' ? 'selected' : ''; ?>>Ada Stock
                        </option>
                        <option value="low_stock" <?php echo $stock_filter === 'low_stock' ? 'selected' : ''; ?>>Stock
                            Terbatas</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="category_id">
                        <option value="">Semua Kategori</option>
                        <?php if ($categories_for_filter && $categories_for_filter->num_rows > 0): ?>
                            <?php while ($cat = $categories_for_filter->fetch_assoc()): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($category_filter == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">Cari</button>
                </div>
            </form>
            <?php if (!empty($search) || $min_price > 0 || $max_price < 999999999 || !empty($stock_filter) || $category_filter > 0): ?>
                <div class="mt-2">
                    <a href="products.php" class="btn btn-sm btn-outline-secondary">Reset Filter</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($total_products > 0): ?>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="text-muted">
                Menampilkan <?php echo $pagination->getStartItem(); ?> - <?php echo $pagination->getEndItem(); ?> dari
                <?php echo $total_products; ?> produk
            </div>
            <form method="POST" id="bulk-form" class="d-none">
                <input type="hidden" name="bulk_action" id="bulk-action">
                <div id="selected-ids"></div>
            </form>
        </div>

        <!-- Bulk Actions Bar -->
        <div class="card mb-3" id="bulk-actions-bar" style="display: none;">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <span id="selected-count">0</span> produk dipilih
                    </div>
                    <div class="col-md-6 text-end">
                        <button type="button" class="btn btn-sm btn-danger" onclick="executeBulkAction('delete')">
                            <i class="fas fa-trash"></i> Hapus Pilihan
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="clearSelection()">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <form method="POST" id="products-form">

        <div class="row">
            <?php if ($products->num_rows > 0): ?>
                <?php while ($product = $products->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card product-card h-100 position-relative">
                            <div class="position-absolute top-0 end-0 p-2">
                                <input type="checkbox" class="form-check-input product-checkbox"
                                    value="<?php echo $product['id']; ?>" onchange="updateBulkSelection()">
                            </div>
                            <?php
                            $placeholder = '../assets/images/placeholder-product.svg';
                            $uploads_dir = '../assets/uploads/';
                            $thumbs_dir = $uploads_dir . 'thumbs/';
                            $finalPath = $placeholder;
                            if (!empty($product['image'])) {
                                $imgName = $product['image'];
                                $thumbCandidate = $thumbs_dir . $imgName;
                                $origCandidate = $uploads_dir . $imgName;
                                if (file_exists($thumbCandidate)) {
                                    $finalPath = $thumbCandidate;
                                } elseif (file_exists($origCandidate)) {
                                    $finalPath = $origCandidate;
                                }
                            }
                            ?>
                            <img src="<?php echo htmlspecialchars($finalPath); ?>" class="card-img-top"
                                alt="<?php echo htmlspecialchars($product['name']); ?>"
                                style="height: 200px; object-fit: cover;">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
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
                                    <a href="?delete=<?php echo $product['id']; ?>&token=<?php echo urlencode(get_csrf_token()); ?>"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Yakin hapus produk ini?')">Hapus</a> <a
                                        href="https://wa.me/6282197771318?text=Halo, saya tertarik dengan produk <?php echo urlencode($product['name']); ?>"
                                        class="btn whatsapp-btn btn-sm" target="_blank">WhatsApp</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        Tidak ada produk. <a href="add_product.php">Tambah produk sekarang</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($pagination->getTotalPages() > 1): ?>
            <div class="mt-4">
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
                if ($category_filter > 0)
                    $extra_params['category_id'] = $category_filter;
                echo $pagination->render('products.php', 'page', $extra_params);
                ?>
            </div>
        <?php endif; ?>
    </form>
</div>

<script>
    function updateBulkSelection() {
        const checkboxes = document.querySelectorAll('.product-checkbox:checked');
        const bulkActionsBar = document.getElementById('bulk-actions-bar');
        const selectedCount = document.getElementById('selected-count');

        selectedCount.textContent = checkboxes.length;

        if (checkboxes.length > 0) {
            bulkActionsBar.style.display = 'block';
        } else {
            bulkActionsBar.style.display = 'none';
        }
    }

    function executeBulkAction(action) {
        const checkboxes = document.querySelectorAll('.product-checkbox:checked');
        const selectedIds = Array.from(checkboxes).map(cb => cb.value);

        if (selectedIds.length === 0) {
            alert('Pilih produk terlebih dahulu!');
            return;
        }

        if (action === 'delete') {
            if (!confirm(`Hapus ${selectedIds.length} produk? Tindakan ini tidak dapat dibatalkan.`)) {
                return;
            }
        }

        const form = document.getElementById('products-form');

        // Clear previous inputs
        document.querySelectorAll('input[name="selected_products[]"]').forEach(el => el.remove());
        document.getElementById('bulk-action').remove();

        // Add selected IDs
        selectedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_products[]';
            input.value = id;
            form.appendChild(input);
        });

        // Add action
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'bulk_action';
        actionInput.id = 'bulk-action';
        actionInput.value = action;
        form.appendChild(actionInput);

        form.submit();
    }

    function clearSelection() {
        document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = false);
        updateBulkSelection();
    }
</script>

<?php
require_once 'templates/footer.php';
?>
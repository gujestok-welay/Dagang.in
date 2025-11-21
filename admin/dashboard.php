<?php
$currentPage = 'dashboard';
$pageTitle = 'Dashboard';
require_once 'templates/header.php';

$user_id = $_SESSION['user_id'];

// Get user info
$user_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();

// Get dashboard stats
$stats = [];

// Total products
$product_count = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE user_id = ?");
$product_count->bind_param("i", $user_id);
$product_count->execute();
$stats['products'] = $product_count->get_result()->fetch_assoc()['count'];

// Products by stock status
$in_stock = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE user_id = ? AND stock > 0");
$in_stock->bind_param("i", $user_id);
$in_stock->execute();
$stats['in_stock'] = $in_stock->get_result()->fetch_assoc()['count'];

$low_stock = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE user_id = ? AND stock > 0 AND stock <= 5");
$low_stock->bind_param("i", $user_id);
$low_stock->execute();
$stats['low_stock'] = $low_stock->get_result()->fetch_assoc()['count'];

// Total orders
$order_count = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
$order_count->bind_param("i", $user_id);
$order_count->execute();
$stats['orders'] = $order_count->get_result()->fetch_assoc()['count'];

// Pending orders
$pending_orders = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ? AND status IN ('pending', 'processing')");
$pending_orders->bind_param("i", $user_id);
$pending_orders->execute();
$stats['pending_orders'] = $pending_orders->get_result()->fetch_assoc()['count'];

// Total revenue - all completed orders
$revenue_completed = $conn->prepare("SELECT SUM(total) as total FROM orders WHERE user_id = ? AND status IN ('delivered', 'completed')");
$revenue_completed->bind_param("i", $user_id);
$revenue_completed->execute();
$stats['revenue'] = $revenue_completed->get_result()->fetch_assoc()['total'] ?? 0;

// Revenue this month
$this_month_revenue = $conn->prepare("SELECT SUM(total) as total FROM orders WHERE user_id = ? AND status IN ('delivered', 'completed') AND MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())");
$this_month_revenue->bind_param("i", $user_id);
$this_month_revenue->execute();
$stats['revenue_month'] = $this_month_revenue->get_result()->fetch_assoc()['total'] ?? 0;

// Unique customers
$customers = $conn->prepare("SELECT COUNT(DISTINCT customer_id) as count FROM orders WHERE user_id = ?");
$customers->bind_param("i", $user_id);
$customers->execute();
$stats['customers'] = $customers->get_result()->fetch_assoc()['count'];

// Recent orders
$recent_orders = $conn->prepare("SELECT o.id, c.name as customer_name, o.total, o.status, o.created_at FROM orders o JOIN customers c ON o.customer_id = c.id WHERE o.user_id = ? ORDER BY o.created_at DESC LIMIT 5");
$recent_orders->bind_param("i", $user_id);
$recent_orders->execute();
$orders_result = $recent_orders->get_result();

// Top products - best sellers
$top_products = $conn->prepare("
    SELECT p.id, p.name, COUNT(oi.product_id) as sold_count, SUM(oi.quantity) as total_quantity
    FROM products p
    LEFT JOIN order_items oi ON p.id = oi.product_id
    WHERE p.user_id = ?
    GROUP BY p.id
    ORDER BY total_quantity DESC
    LIMIT 5
");
$top_products->bind_param("i", $user_id);
$top_products->execute();
$top_products_result = $top_products->get_result();

// Orders by status
$orders_by_status = [];
$statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
foreach ($statuses as $status) {
    $status_query = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ? AND status = ?");
    $status_query->bind_param("is", $user_id, $status);
    $status_query->execute();
    $orders_by_status[$status] = $status_query->get_result()->fetch_assoc()['count'];
}
?>


<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Selamat Datang, <?php echo htmlspecialchars($user['store_name']); ?>!</h2>
            <p class="text-muted">Berikut adalah ringkasan bisnis Anda</p>
        </div>
    </div>

    <!-- Main Stats -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card shadow h-100 py-2" style="border-left: 4px solid var(--secondary-color);">
                <div class="card-body">
                    <div class="text-uppercase mb-1 font-weight-bold" style="font-size: 0.8rem; color: var(--secondary-color);">
                        <i class="fas fa-box"></i> Total Produk
                    </div>
                    <div class="h3 mb-0"><?php echo $stats['products']; ?></div>
                    <small class="text-success"><?php echo $stats['in_stock']; ?> tersedia</small>
                    <?php if ($stats['low_stock'] > 0): ?>
                        <br><small class="text-warning"><?php echo $stats['low_stock']; ?> stok terbatas</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card shadow h-100 py-2" style="border-left: 4px solid var(--accent-color);">
                <div class="card-body">
                    <div class="text-uppercase mb-1 font-weight-bold" style="font-size: 0.8rem; color: var(--accent-color);">
                        <i class="fas fa-shopping-bag"></i> Total Pesanan
                    </div>
                    <div class="h3 mb-0"><?php echo $stats['orders']; ?></div>
                    <small class="text-danger"><?php echo $stats['pending_orders']; ?> menunggu proses</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card shadow h-100 py-2" style="border-left: 4px solid var(--secondary-color);">
                <div class="card-body">
                    <div class="text-uppercase mb-1 font-weight-bold" style="font-size: 0.8rem; color: var(--secondary-color);">
                        <i class="fas fa-chart-line"></i> Pendapatan Total
                    </div>
                    <div class="h3 mb-0">Rp <?php echo number_format($stats['revenue'], 0, ',', '.'); ?></div>
                    <small class="text-info">Bulan ini: Rp
                        <?php echo number_format($stats['revenue_month'], 0, ',', '.'); ?></small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card shadow h-100 py-2" style="border-left: 4px solid var(--accent-color);">
                <div class="card-body">
                    <div class="text-uppercase mb-1 font-weight-bold" style="font-size: 0.8rem; color: var(--accent-color);">
                        <i class="fas fa-users"></i> Pelanggan
                    </div>
                    <div class="h3 mb-0"><?php echo $stats['customers']; ?></div>
                    <small class="text-muted">Pelanggan unik</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Status Pesanan</h5>
                </div>
                <div class="card-body">
                    <canvas id="orderStatusChart"></canvas>
                    <div class="mt-3">
                        <small class="text-muted">
                            <span class="badge bg-warning">Pending:
                                <?php echo $orders_by_status['pending'] ?? 0; ?></span>
                            <span class="badge bg-info">Diproses:
                                <?php echo $orders_by_status['processing'] ?? 0; ?></span>
                            <span class="badge bg-primary">Dikirim:
                                <?php echo $orders_by_status['shipped'] ?? 0; ?></span>
                            <span class="badge bg-success">Terkirim:
                                <?php echo $orders_by_status['delivered'] ?? 0; ?></span>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Status Stok Produk</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-6">
                            <h3 class="text-success"><?php echo $stats['in_stock']; ?></h3>
                            <p class="text-muted">Produk Tersedia</p>
                        </div>
                        <div class="col-md-6">
                            <h3 class="text-warning"><?php echo $stats['low_stock']; ?></h3>
                            <p class="text-muted">Stok Terbatas</p>
                        </div>
                    </div>
                    <div class="progress mt-3">
                        <div class="progress-bar bg-success"
                            style="width: <?php echo ($stats['in_stock'] / max(1, $stats['products'])) * 100; ?>%">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders & Top Products -->
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Pesanan Terbaru</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Pelanggan</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($orders_result && $orders_result->num_rows > 0): ?>
                                    <?php while ($order = $orders_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $order['id']; ?></td>
                                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                            <td>Rp <?php echo number_format($order['total'], 0, ',', '.'); ?></td>
                                            <td>
                                                <span class="badge bg-<?php
                                                echo match ($order['status']) {
                                                    'delivered' => 'success',
                                                    'shipped' => 'info',
                                                    'processing' => 'primary',
                                                    'pending' => 'warning',
                                                    'cancelled' => 'danger',
                                                    default => 'secondary'
                                                };
                                                ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Belum ada pesanan</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="orders.php" class="btn btn-sm btn-outline-primary mt-2">Lihat Semua Pesanan</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow mb-3">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0"><i class="fas fa-star"></i> Produk Terlaris</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php if ($top_products_result && $top_products_result->num_rows > 0): ?>
                            <?php while ($product = $top_products_result->fetch_assoc()): ?>
                                <a href="edit_product.php?id=<?php echo $product['id']; ?>"
                                    class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($product['name']); ?></h6>
                                            <small class="text-muted">Terjual: <?php echo $product['total_quantity'] ?? 0; ?>
                                                unit</small>
                                        </div>
                                        <span class="badge bg-success"><?php echo $product['sold_count'] ?? 0; ?>x</span>
                                    </div>
                                </a>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted text-center mb-0">Belum ada penjualan</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-link"></i> Akses Cepat</h5>
                </div>
                <div class="card-body">
                    <a href="add_product.php" class="btn btn-outline-primary w-100 mb-2 btn-sm">
                        <i class="fas fa-plus"></i> Tambah Produk
                    </a>
                    <a href="products.php" class="btn btn-outline-primary w-100 mb-2 btn-sm">
                        <i class="fas fa-box"></i> Kelola Produk
                    </a>
                    <a href="add_order.php" class="btn btn-outline-success w-100 mb-2 btn-sm">
                        <i class="fas fa-plus"></i> Tambah Pesanan
                    </a>
                    <a href="orders.php" class="btn btn-outline-success w-100 mb-2 btn-sm">
                        <i class="fas fa-shopping-bag"></i> Kelola Pesanan
                    </a>
                    <a href="categories.php" class="btn btn-outline-warning w-100 mb-2 btn-sm">
                        <i class="fas fa-tag"></i> Kategori
                    </a>
                    <a href="../public/index.php" class="btn btn-outline-info w-100 btn-sm" target="_blank">
                        <i class="fas fa-eye"></i> Lihat Toko
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    // Order Status Chart
    const ctxStatus = document.getElementById('orderStatusChart');
    if (ctxStatus) {
        const statusData = {
            labels: ['Pending', 'Diproses', 'Dikirim', 'Terkirim', 'Dibatalkan'],
            datasets: [{
                label: 'Jumlah Pesanan',
                data: [
                    <?php echo $orders_by_status['pending'] ?? 0; ?>,
                    <?php echo $orders_by_status['processing'] ?? 0; ?>,
                    <?php echo $orders_by_status['shipped'] ?? 0; ?>,
                    <?php echo $orders_by_status['delivered'] ?? 0; ?>,
                    <?php echo $orders_by_status['cancelled'] ?? 0; ?>
                ],
                backgroundColor: [
                    '#ffc107',
                    '#17a2b8',
                    '#007bff',
                    '#28a745',
                    '#dc3545'
                ],
                borderColor: [
                    '#ff9800',
                    '#0097a7',
                    '#0056b3',
                    '#1e7e34',
                    '#c82333'
                ],
                borderWidth: 1
            }]
        };

        new Chart(ctxStatus, {
            type: 'doughnut',
            data: statusData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }
</script>

<?php
require_once 'templates/footer.php';
?>
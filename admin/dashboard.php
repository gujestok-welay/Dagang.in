<?php
// Tentukan halaman saat ini dan judulnya
$currentPage = 'dashboard';
$pageTitle = 'Dashboard';
require_once 'templates/header.php';

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
$product_result = $product_count->get_result();
$stats['products'] = $product_result->fetch_assoc()['count'];

// Total orders
$order_count = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
$order_count->bind_param("i", $user_id);
$order_count->execute();
$order_result = $order_count->get_result();
$stats['orders'] = $order_result->fetch_assoc()['count'];

// Total revenue
$revenue = $conn->prepare("SELECT SUM(total) as total FROM orders WHERE user_id = ? AND status = 'completed'");
$revenue->bind_param("i", $user_id);
$revenue->execute();
$revenue_result = $revenue->get_result();
$stats['revenue'] = $revenue_result->fetch_assoc()['total'] ?? 0;

// Recent orders
$recent_orders = $conn->prepare("SELECT o.id, c.name as customer_name, o.total, o.status, o.created_at FROM orders o JOIN customers c ON o.customer_id = c.id WHERE o.user_id = ? ORDER BY o.created_at DESC LIMIT 5");
$recent_orders->bind_param("i", $user_id);
$recent_orders->execute();
$orders_result = $recent_orders->get_result();
?>


<div class="container mt-4">
    <h2 class="g-4 p-4 text-center">Selamat Datang,
        <?php echo htmlspecialchars($user['store_name']); ?>!
    </h2>

    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card dashboard-card ">
                <div class="card-body">
                    <h5 class="card-title">Total Produk</h5>
                    <h2>
                        <?php echo $stats['products']; ?>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card ">
                <div class="card-body">
                    <h5 class="card-title">Total Pesanan</h5>
                    <h2>
                        <?php echo $stats['orders']; ?>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card ">
                <div class="card-body">
                    <h5 class="card-title">Pendapatan</h5>
                    <h2>Rp
                        <?php echo number_format($stats['revenue'], 0, ',', '.'); ?>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card ">
                <div class="card-body">
                    <h5 class="card-title">Pelanggan</h5>
                    <h2>
                        <?php echo $stats['orders']; // Approximate, can be improved ?>
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Pesanan Terbaru</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
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
                                <?php while ($order = $orders_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $order['id']; ?></td>
                                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                        <td>Rp
                                            <?php echo number_format($order['total'], 0, ',', '.'); ?>
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-<?php echo $order['status'] == 'completed' ? 'success' : ($order['status'] == 'pending' ? 'warning' : 'secondary'); ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <a href="add_product.php" class="btn btn-primary w-100 mb-2">Tambah Produk</a>
                    <a href="add_order.php" class="btn btn-accent w-100 mb-2">Tambah Pesanan</a>
                    <a href="../public/index.php" class="btn btn-info w-100" target="_blank">Lihat Toko</a>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
require_once 'templates/footer.php';
?>
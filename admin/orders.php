<?php
require_once '../config/includes/auth.php';
require_once '../config/includes/config.php';

$currentPage = 'orders';
$pageTitle = 'Manajemen Pesanan';
require_once 'templates/header.php';
if (isset($_GET['logout'])) {
    logout();
}

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle status update
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    $update_stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ? AND user_id = ?");
    $update_stmt->bind_param("sii", $status, $order_id, $user_id);
    $update_stmt->execute();
    header("Location: orders.php");
    exit();
}

// Get orders with customer info
$orders_query = $conn->prepare("
    SELECT o.*, c.name as customer_name, c.phone, c.email
    FROM orders o
    JOIN customers c ON o.customer_id = c.id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
");
$orders_query->bind_param("i", $user_id);
$orders_query->execute();
$orders = $orders_query->get_result();

require_once 'templates/header.php';
?>



<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manajemen Pesanan</h2>
        <a href="add_order.php" class="btn btn-primary">Tambah Pesanan</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pelanggan</th>
                            <th>Kontak</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody> <?php while ($order = $orders->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $order['id']; ?></td>
                                <td>
                                    <?php echo htmlspecialchars($order['customer_name']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($order['phone']); ?><br>
                                    <small><?php echo htmlspecialchars($order['email']); ?></small>
                                </td>
                                <td>Rp
                                    <?php echo number_format($order['total'], 0, ',', '.'); ?>
                                </td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="status" class="form-select form-select-sm d-inline w-auto"
                                            onchange="this.form.submit()">
                                            <option value=" pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Diproses</option>
                                            <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Selesai</option>
                                            <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                </td>
                                <td>
                                    <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                </td>
                                <td>
                                    <a href="view_order.php?id=<?php echo $order['id']; ?>"
                                        class="btn btn-info btn-sm">Detail</a>
                                    <a href=" https://wa.me/<?php echo $order['phone']; ?>?text=Halo%20
                                        <?php echo urlencode($order['customer_name']); ?>%2C%20status%20pesanan%20Anda%3A%20
                                        <?php echo urlencode(ucfirst($order['status'])); ?>"
                                        class="btn whatsapp-btn btn-sm" target="_blank">WA
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>
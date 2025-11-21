<?php
$currentPage = 'orders';
$pageTitle = 'Detail Pesanan';
require_once 'templates/header.php';

$user_id = $_SESSION['user_id'];

// Get order ID
if (!isset($_GET['id'])) {
    header("Location: orders.php");
    exit();
}

$order_id = $_GET['id'];

// Get order with customer info
$order_query = $conn->prepare("
    SELECT o.*, c.name as customer_name, c.phone, c.email, c.address
    FROM orders o
    JOIN customers c ON o.customer_id = c.id
    WHERE o.id = ? AND o.user_id = ?
");
$order_query->bind_param("ii", $order_id, $user_id);
$order_query->execute();
$order_result = $order_query->get_result();

if ($order_result->num_rows == 0) {
    header("Location: orders.php");
    exit();
}

$order = $order_result->fetch_assoc();

// Get order items
$items_query = $conn->prepare("
    SELECT oi.*, p.name as product_name, p.image
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$items_query->bind_param("i", $order_id);
$items_query->execute();
$items_result = $items_query->get_result();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $update_stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ? AND user_id = ?");
    $update_stmt->bind_param("sii", $new_status, $order_id, $user_id);

    if ($update_stmt->execute()) {
        $order['status'] = $new_status;
        $message = "Status pesanan berhasil diperbarui!";
    }
}

// Status badge colors
$status_colors = [
    'pending' => 'warning',
    'processing' => 'info',
    'completed' => 'success',
    'cancelled' => 'danger'
];

$status_labels = [
    'pending' => 'Menunggu',
    'processing' => 'Diproses',
    'completed' => 'Selesai',
    'cancelled' => 'Dibatalkan'
];
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Detail Pesanan #<?php echo $order['id']; ?></h2>
        <a href="orders.php" class="btn btn-secondary">Kembali</a>
    </div>

    <?php if (isset($message)): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <!-- Order Items -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Produk yang Dipesan</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Harga</th>
                                    <th>Jumlah</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($item = $items_result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if ($item['image']): ?>
                                                    <img src="../assets/uploads/<?php echo htmlspecialchars($item['image']); ?>"
                                                        alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                                        style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px;">
                                                <?php endif; ?>
                                                <span><?php echo htmlspecialchars($item['product_name']); ?></span>
                                            </div>
                                        </td>
                                        <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td><strong>Rp <?php echo number_format($order['total'], 0, ',', '.'); ?></strong>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Order Notes -->
            <?php if ($order['notes']): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Catatan Pesanan</h5>
                    </div>
                    <div class="card-body">
                        <p><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <!-- Order Status -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Status Pesanan</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="badge bg-<?php echo $status_colors[$order['status']]; ?> fs-6">
                            <?php echo $status_labels[$order['status']]; ?>
                        </span>
                    </div>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="status" class="form-label">Ubah Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>
                                    Menunggu</option>
                                <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Diproses</option>
                                <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Selesai</option>
                                <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
                            </select>
                        </div>
                        <button type="submit" name="update_status" class="btn btn-primary w-100">Update Status</button>
                    </form>
                </div>
            </div>

            <!-- Customer Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Informasi Pelanggan</h5>
                </div>
                <div class="card-body">
                    <p><strong>Nama:</strong><br><?php echo htmlspecialchars($order['customer_name']); ?></p>
                    <p><strong>Telepon:</strong><br>
                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $order['phone']); ?>?text=Halo%20<?php echo urlencode($order['customer_name']); ?>,%20mengenai%20pesanan%20%23<?php echo $order['id']; ?>"
                            class="btn btn-sm whatsapp-btn" target="_blank">
                            <i class="fab fa-whatsapp"></i> <?php echo htmlspecialchars($order['phone']); ?>
                        </a>
                    </p>
                    <?php if ($order['email']): ?>
                        <p><strong>Email:</strong><br><?php echo htmlspecialchars($order['email']); ?></p>
                    <?php endif; ?>
                    <?php if ($order['address']): ?>
                        <p><strong>Alamat:</strong><br><?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Payment Info -->
            <div class="card">
                <div class="card-header">
                    <h5>Informasi Pembayaran</h5>
                </div>
                <div class="card-body">
                    <p><strong>Metode:</strong><br><?php echo htmlspecialchars($order['payment_method']); ?></p>
                    <p><strong>Tanggal
                            Order:</strong><br><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
<?php
$currentPage = 'orders';
$pageTitle = 'Manajemen Pesanan';
require_once 'templates/header.php';
require_once '../config/utils/Pagination.php';

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = 'danger';

// Pagination & filter defaults (dibutuhkan sebelum POST)
$items_per_page = 15;
$current_page = max(1, (int) ($_GET['page'] ?? 1));
$search = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? '';

// Handle status update
if (isset($_POST['update_status'])) {
    try {
        $order_id = (int) $_POST['order_id'];
        $status = trim($_POST['status']);
        $allowed_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

        if (!in_array($status, $allowed_statuses)) {
            $message = "Status tidak valid.";
        } else {
            $update_stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ? AND user_id = ?");
            if (!$update_stmt) {
                $message = "Database error: " . $conn->error;
            } else {
                $update_stmt->bind_param("sii", $status, $order_id, $user_id);
                if ($update_stmt->execute()) {
                    $message = "Status pesanan berhasil diperbarui!";
                    $message_type = 'success';

                    // Preserve filter parameters from POST
                    if (!empty($_POST['search'])) {
                        $search = trim($_POST['search']);
                    }
                    if (!empty($_POST['status_filter'])) {
                        $status_filter = $_POST['status_filter'];
                    }
                    if (!empty($_POST['page'])) {
                        $current_page = (int) $_POST['page'];
                    }
                } else {
                    $message = "Gagal memperbarui status.";
                }
                $update_stmt->close();
            }
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Build WHERE clause for filtering
$where_conditions = ["o.user_id = ?"];
$params = [$user_id];
$param_types = 'i';

// Search by customer name or phone
if (!empty($search)) {
    $search_term = '%' . $search . '%';
    $where_conditions[] = "(c.name LIKE ? OR c.phone LIKE ?)";
    $params[] = $search_term;
    $params[] = $search_term;
    $param_types .= 'ss';
}

// Status filter
if (!empty($status_filter)) {
    $where_conditions[] = "o.status = ?";
    $params[] = $status_filter;
    $param_types .= 's';
}

$where_clause = implode(' AND ', $where_conditions);

// Count total orders
$count_query = $conn->prepare("
    SELECT COUNT(*) as total
    FROM orders o
    JOIN customers c ON o.customer_id = c.id
    WHERE $where_clause
");

if (!$count_query) {
    die("Database error: " . $conn->error);
}

$count_query->bind_param($param_types, ...$params);
$count_query->execute();
$count_result = $count_query->get_result();
$count_row = $count_result->fetch_assoc();
$total_orders = $count_row['total'];
$count_query->close();

// Initialize pagination
$pagination = new Pagination($total_orders, $items_per_page, $current_page);

// Get orders with pagination
$offset = $pagination->getOffset();
$limit = $pagination->getLimit();

$query_params = $params;
$query_params[] = $limit;
$query_params[] = $offset;
$query_param_types = $param_types . 'ii';

$orders_query = $conn->prepare("
    SELECT o.*, c.name as customer_name, c.phone, c.email
    FROM orders o
    JOIN customers c ON o.customer_id = c.id
    WHERE $where_clause
    ORDER BY o.created_at DESC
    LIMIT ? OFFSET ?
");

if (!$orders_query) {
    die("Database error: " . $conn->error);
}

$orders_query->bind_param($query_param_types, ...$query_params);
$orders_query->execute();
$orders = $orders_query->get_result();
$orders_query->close();
?>



<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manajemen Pesanan</h2>
        <div class="btn-group" role="group">
            <a href="add_order.php" class="btn btn-primary">Tambah Pesanan</a>
            <div class="btn-group" role="group">
                <button id="exportOrdersBtn" type="button" class="btn btn-success dropdown-toggle"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-download"></i> Export
                </button>
                <ul class="dropdown-menu" aria-labelledby="exportOrdersBtn">
                    <li><a class="dropdown-item" href="export_orders.php?format=csv">Export ke CSV</a></li>
                    <li><a class="dropdown-item" href="export_orders.php?format=excel">Export ke Excel</a></li>
                </ul>
            </div>
        </div>
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
                <div class="col-md-5">
                    <input type="text" class="form-control" name="search"
                        placeholder="Cari nama atau nomor pelanggan..."
                        value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="status">
                        <option value="">Semua Status</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending
                        </option>
                        <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>
                            Diproses</option>
                        <option value="shipped" <?php echo $status_filter === 'shipped' ? 'selected' : ''; ?>>Dikirim
                        </option>
                        <option value="delivered" <?php echo $status_filter === 'delivered' ? 'selected' : ''; ?>>Terkirim
                        </option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>
                            Dibatalkan</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Cari</button>
                </div>
            </form>
            <?php if (!empty($search) || !empty($status_filter)): ?>
                <div class="mt-2">
                    <a href="orders.php" class="btn btn-sm btn-outline-secondary">Reset Filter</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($total_orders > 0): ?>
        <div class="text-muted mb-3">
            Menampilkan <?php echo $pagination->getStartItem(); ?> - <?php echo $pagination->getEndItem(); ?> dari
            <?php echo $total_orders; ?> pesanan
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <?php if ($orders->num_rows > 0): ?>
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
                        <tbody>
                            <?php while ($order = $orders->fetch_assoc()): ?>
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
                                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                                            <input type="hidden" name="status_filter"
                                                value="<?php echo htmlspecialchars($status_filter); ?>">
                                            <input type="hidden" name="page" value="<?php echo $current_page; ?>">
                                            <select name="status" class="form-select form-select-sm d-inline w-auto"
                                                onchange="this.form.submit()">
                                                <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Diproses</option>
                                                <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Dikirim</option>
                                                <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Terkirim</option>
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
                                        <a href="https://wa.me/<?php echo $order['phone']; ?>?text=Halo%20
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
            <?php else: ?>
                <div class="alert alert-info text-center mb-0">
                    Tidak ada pesanan.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($pagination->getTotalPages() > 1): ?>
        <div class="mt-4">
            <?php
            $extra_params = [];
            if (!empty($search))
                $extra_params['search'] = $search;
            if (!empty($status_filter))
                $extra_params['status'] = $status_filter;
            echo $pagination->render('orders.php', 'page', $extra_params);
            ?>
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'templates/footer.php';
?>
<?php
$currentPage = 'customers';
$pageTitle = 'Manajemen Pelanggan';
require_once 'templates/header.php';

$user_id = $_SESSION['user_id'];

// Handle delete
if (isset($_GET['delete'])) {
    $customer_id = $_GET['delete'];
    $delete_stmt = $conn->prepare("DELETE FROM customers WHERE id = ? AND user_id = ?");
    $delete_stmt->bind_param("ii", $customer_id, $user_id);
    $delete_stmt->execute();
    header("Location: customers.php");
    exit();
}

// Get search parameter
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Get customers with order count
if ($search) {
    $customers_query = $conn->prepare("
        SELECT c.*, COUNT(o.id) as order_count, SUM(o.total) as total_spent
        FROM customers c
        LEFT JOIN orders o ON c.id = o.customer_id
        WHERE c.user_id = ? AND (c.name LIKE ? OR c.phone LIKE ? OR c.email LIKE ?)
        GROUP BY c.id
        ORDER BY c.created_at DESC
    ");
    $search_param = "%$search%";
    $customers_query->bind_param("isss", $user_id, $search_param, $search_param, $search_param);
} else {
    $customers_query = $conn->prepare("
        SELECT c.*, COUNT(o.id) as order_count, SUM(o.total) as total_spent
        FROM customers c
        LEFT JOIN orders o ON c.id = o.customer_id
        WHERE c.user_id = ?
        GROUP BY c.id
        ORDER BY c.created_at DESC
    ");
    $customers_query->bind_param("i", $user_id);
}

$customers_query->execute();
$customers = $customers_query->get_result();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manajemen Pelanggan</h2>
    </div>

    <!-- Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-10">
                    <input type="text" class="form-control" name="search"
                        placeholder="Cari berdasarkan nama, telepon, atau email..."
                        value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Cari</button>
                </div>
            </form>
            <?php if ($search): ?>
                <a href="customers.php" class="btn btn-secondary btn-sm mt-2">Reset Pencarian</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Kontak</th>
                            <th>Alamat</th>
                            <th>Total Pesanan</th>
                            <th>Total Belanja</th>
                            <th>Tanggal Daftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($customers->num_rows > 0): ?>
                            <?php while ($customer = $customers->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $customer['id']; ?></td>
                                    <td><?php echo htmlspecialchars($customer['name']); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($customer['phone']); ?><br>
                                        <small
                                            class="text-muted"><?php echo htmlspecialchars($customer['email'] ?? '-'); ?></small>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars(substr($customer['address'] ?? '-', 0, 50)); ?><?php echo strlen($customer['address'] ?? '') > 50 ? '...' : ''; ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo $customer['order_count']; ?> pesanan</span>
                                    </td>
                                    <td>Rp <?php echo number_format($customer['total_spent'] ?? 0, 0, ',', '.'); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($customer['created_at'])); ?></td>
                                    <td>
                                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $customer['phone']); ?>?text=Halo%20<?php echo urlencode($customer['name']); ?>"
                                            class="btn btn-sm whatsapp-btn" target="_blank" title="WhatsApp">
                                            <i class="fab fa-whatsapp"></i>
                                        </a>
                                        <a href="?delete=<?php echo $customer['id']; ?>" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Yakin hapus pelanggan ini? Semua pesanan terkait juga akan terhapus.')"
                                            title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">
                                    <?php if ($search): ?>
                                        Tidak ada pelanggan yang sesuai dengan pencarian
                                        "<?php echo htmlspecialchars($search); ?>"
                                    <?php else: ?>
                                        Belum ada data pelanggan
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <p class="text-muted">
            <i class="fas fa-info-circle"></i>
            Pelanggan akan otomatis terdaftar saat Anda membuat pesanan baru.
        </p>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
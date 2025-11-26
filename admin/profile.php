<?php
$currentPage = 'profile';
$pageTitle = 'Profil Toko';

require_once 'templates/header.php';

// CSRF helpers (copy dari categories.php)
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

// Get user data
$user_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die("Akses ilegal: Token CSRF tidak valid.");
    }
    if (isset($_POST['update_profile'])) {
        $store_name = trim($_POST['store_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Format email tidak valid.";
        } else {
            // Check if email already used by another user
            $check_email = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $check_email->bind_param("si", $email, $user_id);
            $check_email->execute();

            if ($check_email->get_result()->num_rows > 0) {
                $message = "Email sudah digunakan oleh pengguna lain.";
            } else {
                $update_stmt = $conn->prepare("UPDATE users SET store_name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
                $update_stmt->bind_param("ssssi", $store_name, $email, $phone, $address, $user_id);

                if ($update_stmt->execute()) {
                    $message = "Profil berhasil diperbarui!";
                    // Refresh user data
                    $user_query->execute();
                    $user = $user_query->get_result()->fetch_assoc();
                } else {
                    $message = "Gagal memperbarui profil.";
                }
            }
        }
    } elseif (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Verify current password
        if (!password_verify($current_password, $user['password'])) {
            $message = "Password saat ini tidak sesuai.";
        } elseif (strlen($new_password) < 6) {
            $message = "Password baru minimal 6 karakter.";
        } elseif ($new_password !== $confirm_password) {
            $message = "Konfirmasi password tidak cocok.";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_pass = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_pass->bind_param("si", $hashed_password, $user_id);

            if ($update_pass->execute()) {
                $message = "Password berhasil diubah!";
            } else {
                $message = "Gagal mengubah password.";
            }
        }
    }
}

// Get statistics
$stats_query = $conn->prepare("
    SELECT 
        (SELECT COUNT(*) FROM products WHERE user_id = ?) as total_products,
        (SELECT COUNT(*) FROM orders WHERE user_id = ?) as total_orders,
        (SELECT SUM(total) FROM orders WHERE user_id = ? AND status = 'completed') as total_revenue,
        (SELECT COUNT(DISTINCT customer_id) FROM orders WHERE user_id = ?) as total_customers
");
$stats_query->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
$stats_query->execute();
$stats = $stats_query->get_result()->fetch_assoc();
?>

<div class="container mt-4">
    <h2 class="mb-4">Profil Toko</h2>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo strpos($message, 'berhasil') !== false ? 'success' : 'danger'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Statistics -->
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5>Statistik Toko</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="p-3">
                                <h3 class="text-primary"><?php echo $stats['total_products'] ?? 0; ?></h3>
                                <p class="text-muted">Total Produk</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3">
                                <h3 class="text-info"><?php echo $stats['total_orders'] ?? 0; ?></h3>
                                <p class="text-muted">Total Pesanan</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3">
                                <h3 class="text-success">Rp
                                    <?php echo number_format($stats['total_revenue'] ?? 0, 0, ',', '.'); ?>
                                </h3>
                                <p class="text-muted">Total Pendapatan</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3">
                                <h3 class="text-warning"><?php echo $stats['total_customers'] ?? 0; ?></h3>
                                <p class="text-muted">Total Pelanggan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Information -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Informasi Profil</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token"
                            value="<?php echo htmlspecialchars(get_csrf_token()); ?>">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username"
                                value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                            <small class="text-muted">Username tidak dapat diubah</small>
                        </div>
                        <div class="mb-3">
                            <label for="store_name" class="form-label">Nama Toko</label>
                            <input type="text" class="form-control" id="store_name" name="store_name"
                                value="<?php echo htmlspecialchars($user['store_name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Telepon/WhatsApp</label>
                            <input type="text" class="form-control" id="phone" name="phone"
                                value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                placeholder="Contoh: 628123456789">
                            <small class="text-muted">Format: 628xxx (untuk tombol WhatsApp)</small>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Alamat</label>
                            <textarea class="form-control" id="address" name="address"
                                rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary">Update Profil</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Change Password -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Ubah Password</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token"
                            value="<?php echo htmlspecialchars(get_csrf_token()); ?>">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Password Saat Ini</label>
                            <input type="password" class="form-control" id="current_password" name="current_password"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Password Baru</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required
                                minlength="6">
                            <small class="text-muted">Minimal 6 karakter</small>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                required minlength="6">
                        </div>
                        <button type="submit" name="change_password" class="btn btn-warning">Ubah Password</button>
                    </form>
                </div>
            </div>

            <!-- Account Info -->
            <div class="card">
                <div class="card-header">
                    <h5>Informasi Akun</h5>
                </div>
                <div class="card-body">
                    <p><strong>Tanggal Daftar:</strong><br>
                        <?php echo date('d F Y', strtotime($user['created_at'])); ?>
                    </p>
                    <p><strong>Status:</strong><br>
                        <span class="badge bg-success">Aktif</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
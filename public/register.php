<?php
require_once '../config/includes/auth.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate input
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $store_name = trim($_POST['store_name']);
    $phone = preg_replace('/[^0-9]/', '', $_POST['phone']); // Remove non-numeric
    $address = trim($_POST['address']);

    // Validation
    $errors = [];

    // Username validation
    if (strlen($username) < 3) {
        $errors[] = "Username minimal 3 karakter";
    }

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }

    // Password validation
    if (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter";
    }

    // Password match
    if ($password !== $confirm_password) {
        $errors[] = "Password tidak cocok";
    }

    // Phone validation
    if (strlen($phone) < 10 || strlen($phone) > 15) {
        $errors[] = "Nomor telepon harus 10-15 digit";
    }

    if (empty($errors)) {
        if (register($username, $email, $password, $store_name, $phone, $address)) {
            $message = "Registrasi berhasil! Anda akan dialihkan ke halaman login...";
            $message_type = 'success';
        } else {
            $message = "Registrasi gagal. Silakan coba lagi atau gunakan data yang berbeda.";
            $message_type = 'error';
        }
    } else {
        $message = implode(', ', $errors);
        $message_type = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Dagang.in</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/register.css">
</head>

<body>
    <div class="split-card mx-auto">
        <div class="split-left">
            <h3>Sudah Punya Akun?</h3>
            <p>Masuk ke akun Anda untuk mengelola toko dan mulai berjualan di Dagang.in.</p>
            <a href="login.php" class="btn btn-outline-white">MASUK DISINI</a>
        </div>
        <div class="split-right">
            <div class="register-title text-center mb-2">Daftar Akun Baru</div>
            <div class="social-login mb-2">
                <button class="social-btn-login" disabled title="Segera Hadir"><i class="fab fa-google"></i></button>
                <button class="social-btn-login" disabled title="Segera Hadir"><i class="fab fa-facebook"></i></button>
            </div>
            <div class="divider-regis"><span>ATAU GUNAKAN EMAIL DAN DETAIL ANDA</span></div>
            <?php if ($message): ?>
                <div class="alert <?php echo $message_type === 'success' ? 'alert-success' : 'alert-danger'; ?> text-center"
                    id="message-alert">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            <form method="POST" id="registerForm" autocomplete="off">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="store_name" class="form-label">Nama Toko</label>
                        <input type="text" class="form-control form-control-lg" id="store_name" name="store_name"
                            required
                            value="<?php echo isset($_POST['store_name']) ? htmlspecialchars($_POST['store_name']) : ''; ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label">No. Telepon</label>
                        <input type="tel" class="form-control form-control-lg" id="phone" name="phone"
                            pattern="[0-9]{10,15}" placeholder="08123456789" required
                            value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                        <small class="text-muted">Format: 08xxxxxxxxxx</small>
                    </div>
                </div>
                <div class="mt-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control form-control-lg" id="username" name="username" minlength="3"
                        required
                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    <small class="text-muted">Min. 3 karakter</small>
                </div>
                <div class="mt-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control form-control-lg" id="email" name="email" required
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control form-control-lg" id="password" name="password"
                            minlength="6" required autocomplete="new-password">
                        <small class="text-muted">Min. 6 karakter</small>
                    </div>
                    <div class="col-md-6">
                        <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                        <input type="password" class="form-control form-control-lg" id="confirm_password"
                            name="confirm_password" minlength="6" required autocomplete="new-password">
                        <small id="password-match" class="text-muted"></small>
                    </div>
                </div>
                <div class="mt-3 mb-2">
                    <label for="address" class="form-label">Alamat</label>
                    <textarea class="form-control form-control-lg" id="address" name="address" rows="2"
                        required><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                </div>
                <button type="submit" class="btn btn-register btn-lg w-100 mt-2" id="submitBtn">DAFTAR SEKARANG</button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/register.js"></script>
</body>

</html>
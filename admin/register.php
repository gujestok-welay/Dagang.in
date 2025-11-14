<?php
require_once '../config/includes/auth.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $store_name = $_POST['store_name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    if (register($username, $email, $password, $store_name, $phone, $address)) {
        $message = "Registrasi berhasil! Silakan login.";
    } else {
        $message = "Registrasi gagal. Username atau email mungkin sudah digunakan.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Dagang.in</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/login.css" rel="stylesheet">
</head>

<body class="login-bg">
    <div class="container-fluid d-flex align-items-center justify-content-center min-vh-100">
        <div class="row justify-content-center w-100">
            <div class="col-lg-10 col-xl-8">
                <div class="login-card">
                    <div class="row g-0">
                        <div class="col-md-7 login-left">
                            <div class="login-body">
                                <h3 class="login-title">DAFTAR</h3>
                                <div class="social-login">
                                    <button class="social-btn"><i class="fab fa-google"></i></button>
                                    <button class="social-btn"><i class="fab fa-github"></i></button>
                                    <button class="social-btn"><i class="fab fa-linkedin"></i></button>
                                    <button class="social-btn"><i class="fab fa-facebook"></i></button>
                                </div>
                                <div class="divider">
                                    <span>ATAU GUNAKAN EMAIL DAN DETAIL ANDA</span>
                                </div>
                                <?php if ($message): ?>
                                    <div class="alert alert-info">
                                        <?php echo $message; ?>
                                    </div>
                                <?php endif; ?>
                                <form method="POST">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="username" class="form-label login-label">Username</label>
                                            <input type="text" class="form-control login-input" id="username"
                                                name="username" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label login-label">Email</label>
                                            <input type="email" class="form-control login-input" id="email" name="email"
                                                required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label login-label">Password</label>
                                        <input type="password" class="form-control login-input" id="password"
                                            name="password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="store_name" class="form-label login-label">Nama Toko</label>
                                        <input type="text" class="form-control login-input" id="store_name"
                                            name="store_name" required>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="phone" class="form-label login-label">No. Telepon</label>
                                            <input type="text" class="form-control login-input" id="phone" name="phone"
                                                required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="address" class="form-label login-label">Alamat</label>
                                            <textarea class="form-control login-input" id="address" name="address"
                                                rows="1" required></textarea>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn login-btn w-100">DAFTAR</button>
                                </form>
                            </div>
                        </div>
                        <div class="col-md-5 login-right">
                            <div class="login-cta">
                                <h3 class="login-cta-title">HALO, SAHABAT!</h3>
                                <p class="login-cta-text">Masuk dengan akun Anda untuk terhubung dengan kami</p>
                                <a href="../login.php" class="btn login-cta-btn">MASUK</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
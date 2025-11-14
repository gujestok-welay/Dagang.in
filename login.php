<?php
require_once 'config/includes/auth.php';

if (isLoggedIn()) {
    header("Location: admin/dashboard.php");
    exit();
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (login($username, $password)) {
        header("Location: admin/dashboard.php");
        exit();
    } else {
        $message = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dagang.in</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/login.css" rel="stylesheet">
</head>

<body class="login-bg">
    <div class="container-fluid d-flex align-items-center justify-content-center min-vh-100">
        <div class="row justify-content-center w-100">
            <div class="col-lg-8 col-xl-6">
                <div class="login-card">
                    <div class="row g-0">
                        <div class="col-md-6 login-left">
                            <div class="login-body">
                                <h3 class="login-title">MASUK</h3>
                                <div class="social-login">
                                    <button class="social-btn"><i class="fab fa-google"></i></button>
                                    <button class="social-btn"><i class="fab fa-github"></i></button>
                                    <button class="social-btn"><i class="fab fa-linkedin"></i></button>
                                    <button class="social-btn"><i class="fab fa-facebook"></i></button>
                                </div>
                                <div class="divider">
                                    <span>ATAU GUNAKAN EMAIL DAN KATA SANDI ANDA</span>
                                </div>
                                <?php if ($message): ?>
                                    <div class="alert alert-danger"><?php echo $message; ?></div>
                                <?php endif; ?>
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="username" class="form-label login-label">Username</label>
                                        <input type="text" class="form-control login-input" id="username"
                                            name="username" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label login-label">Password</label>
                                        <input type="password" class="form-control login-input" id="password"
                                            name="password" required>
                                    </div>
                                    <div class="forgot-password">
                                        <a href="#" class="forgot-link">Lupa Kata Sandi?</a>
                                    </div>
                                    <button type="submit" class="btn login-btn w-100">MASUK</button>
                                </form>
                            </div>
                        </div>
                        <div class="col-md-6 login-right">
                            <div class="login-cta">
                                <h3 class="login-cta-title">HALO, SAHABAT!</h3>
                                <p class="login-cta-text">Daftar dengan detail pribadi Anda untuk terhubung dengan kami
                                </p>
                                <a href="admin/register.php" class="btn login-cta-btn">DAFTAR</a>
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
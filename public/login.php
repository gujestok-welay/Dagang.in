<?php
require_once '../config/includes/auth.php';

if (isLoggedIn()) {
    header("Location: ../admin/dashboard.php");
    exit();
}

$message = '';
$login_attempts = 0;

// Simple rate limiting using session
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = time();
}

// Reset attempts after 15 minutes
if (time() - $_SESSION['last_attempt_time'] > 900) {
    $_SESSION['login_attempts'] = 0;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check rate limiting
    if ($_SESSION['login_attempts'] >= 5) {
        $wait_time = 900 - (time() - $_SESSION['last_attempt_time']);
        $message = "Terlalu banyak percobaan login. Silakan coba lagi dalam " . ceil($wait_time / 60) . " menit.";
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        if (login($username, $password)) {
            // Reset attempts on successful login
            $_SESSION['login_attempts'] = 0;
            header("Location: ../admin/dashboard.php");
            exit();
        } else {
            $_SESSION['login_attempts']++;
            $_SESSION['last_attempt_time'] = time();
            // Generic error message for security
            $message = "Login gagal. Silakan periksa kredensial Anda.";
        }
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/login.css" rel="stylesheet">
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
                                <div class="social-login justify-content-center">
                                    <button class="social-btn-login" disabled title="Segera Hadir"><i
                                            class="fab fa-google"></i></button>
                                    <button class="social-btn-login" disabled title="Segera Hadir"><i
                                            class="fab fa-facebook"></i></button>
                                </div>
                                <div class="divider-login">
                                    <span>ATAU GUNAKAN EMAIL DAN KATA SANDI ANDA</span>
                                </div>
                                <?php if ($message): ?>
                                    <div class="alert alert-danger" role="alert">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        <?php echo htmlspecialchars($message); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] > 0): ?>
                                    <div class="alert alert-warning alert-sm" role="alert">
                                        <small>Percobaan login: <?php echo $_SESSION['login_attempts']; ?>/5</small>
                                    </div>
                                <?php endif; ?>
                                <form method="POST" autocomplete="on">
                                    <div class="mb-3">
                                        <label for="username" class="form-label login-label">Username</label>
                                        <input type="text" class="form-control login-input" id="username"
                                            name="username" autocomplete="username"
                                            value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                            required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label login-label">Password</label>
                                        <input type="password" class="form-control login-input" id="password"
                                            name="password" autocomplete="current-password" required>
                                    </div>
                                    <div class="forgot-password">
                                        <a class="forgot-link"
                                            href="https://wa.me/6282197771318?text=Halo+Admin+Dagang.in%2C+saya+lupa+password+akun+saya"
                                            target="_blank" rel="noopener">Lupa Kata Sandi?</a>
                                    </div>
                                    <button type="submit" class="btn login-btn w-100">MASUK</button>
                                </form>
                            </div>
                        </div>
                        <div class="col-md-6 login-right">
                            <div class="login-cta">
                                <h3 class="login-cta-title">Siap Berkembang Bersama?</h3>
                                <p class="login-cta-text">Jangan ketinggalan! Bergabunglah dengan ribuan sahabat UMKM
                                    lainnya yang telah berkembang bersama kami.
                                </p>
                                <a href="register.php" class="btn login-cta-btn">BERGABUNG 🙌</a>
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
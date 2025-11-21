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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/register.css" rel="stylesheet">
</head>

<body class="regis-bg">
    <div class="container-fluid d-flex align-items-center justify-content-center min-vh-100">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <div class="regis-card">
                    <div class="row g-0">
                        <div class="col-md-7 regis-left">
                            <div class="regis-body">
                                <h3 class="regis-title">DAFTAR</h3>
                                <div class="social-regis">
                                    <button class="social-btn-regis" disabled title="Segera Hadir"><i
                                            class="fab fa-google"></i></button>
                                    <button class="social-btn-regis" disabled title="Segera Hadir"><i
                                            class="fab fa-github"></i></button>
                                    <button class="social-btn-regis" disabled title="Segera Hadir"><i
                                            class="fab fa-linkedin"></i></button>
                                    <button class="social-btn-regis" disabled title="Segera Hadir"><i
                                            class="fab fa-facebook"></i></button>
                                </div>
                                <div class="divider-regis">
                                    <span>ATAU GUNAKAN EMAIL DAN DETAIL ANDA</span>
                                </div>
                                <?php if ($message): ?>
                                    <div class="alert <?php echo $message_type === 'success' ? 'alert-success' : 'alert-danger'; ?>"
                                        id="message-alert">
                                        <?php echo htmlspecialchars($message); ?>
                                    </div>
                                <?php endif; ?>
                                <form method="POST" id="registerForm">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="username" class="form-label regis-label">Username</label>
                                            <input type="text" class="form-control regis-input" id="username"
                                                name="username" minlength="3" required>
                                            <small class="text-light">Min. 3 karakter</small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label regis-label">Email</label>
                                            <input type="email" class="form-control regis-input" id="email" name="email"
                                                required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label regis-label">Password</label>
                                        <input type="password" class="form-control regis-input" id="password"
                                            name="password" minlength="6" required>
                                        <small class="text-light">Min. 6 karakter</small>
                                        <div id="password-strength" class="mt-2" style="display: none;">
                                            <div class="progress" style="height: 5px;">
                                                <div id="strength-bar" class="progress-bar" role="progressbar"></div>
                                            </div>
                                            <small id="strength-text" class="text-light"></small>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label regis-label">Konfirmasi
                                            Password</label>
                                        <input type="password" class="form-control regis-input" id="confirm_password"
                                            name="confirm_password" minlength="6" required>
                                        <small id="password-match" class="text-light"></small>
                                    </div>
                                    <div class="mb-3">
                                        <label for="store_name" class="form-label regis-label">Nama Toko</label>
                                        <input type="text" class="form-control regis-input" id="store_name"
                                            name="store_name" required>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="phone" class="form-label regis-label">No.
                                                Telepon</label>
                                            <input type="tel" class="form-control regis-input" id="phone" name="phone"
                                                pattern="[0-9]{10,15}" placeholder="08123456789" required>
                                            <small class="text-light">Format: 08xxxxxxxxxx</small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="address" class="form-label regis-label">Alamat</label>
                                            <textarea class="form-control regis-input" id="address" name="address"
                                                rows="2" required></textarea>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn regis-btn w-100" id="submitBtn">BUAT AKUN
                                        SAYA</button>
                                </form>
                            </div>
                        </div>
                        <div class="col-md-5 regis-right">
                            <div class="regis-cta">
                                <h3 class="regis-cta-title">Sudah Punya Akun?</h3>
                                <p class="regis-cta-text">Masuk di sini untuk mengakses dasbor dan semua fitur Anda.
                                </p>
                                <a href="../login.php" class="btn regis-cta-btn">MASUK 🙌</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password strength checker
        const password = document.getElementById('password');
        const strengthBar = document.getElementById('strength-bar');
        const strengthText = document.getElementById('strength-text');
        const strengthContainer = document.getElementById('password-strength');
        const confirmPassword = document.getElementById('confirm_password');
        const passwordMatch = document.getElementById('password-match');
        const form = document.getElementById('registerForm');
        const submitBtn = document.getElementById('submitBtn');

        password.addEventListener('input', function () {
            const val = this.value;
            if (val.length === 0) {
                strengthContainer.style.display = 'none';
                return;
            }

            strengthContainer.style.display = 'block';
            let strength = 0;

            // Length check
            if (val.length >= 6) strength += 20;
            if (val.length >= 8) strength += 20;

            // Character variety
            if (/[a-z]/.test(val)) strength += 20;
            if (/[A-Z]/.test(val)) strength += 20;
            if (/[0-9]/.test(val)) strength += 10;
            if (/[^a-zA-Z0-9]/.test(val)) strength += 10;

            strengthBar.style.width = strength + '%';

            if (strength < 40) {
                strengthBar.className = 'progress-bar bg-danger';
                strengthText.textContent = 'Lemah';
            } else if (strength < 70) {
                strengthBar.className = 'progress-bar bg-warning';
                strengthText.textContent = 'Sedang';
            } else {
                strengthBar.className = 'progress-bar bg-success';
                strengthText.textContent = 'Kuat';
            }
        });

        // Password match checker
        function checkPasswordMatch() {
            if (confirmPassword.value.length === 0) {
                passwordMatch.textContent = '';
                return false;
            }

            if (password.value === confirmPassword.value) {
                passwordMatch.textContent = '✓ Password cocok';
                passwordMatch.className = 'text-success';
                return true;
            } else {
                passwordMatch.textContent = '✗ Password tidak cocok';
                passwordMatch.className = 'text-danger';
                return false;
            }
        }

        confirmPassword.addEventListener('input', checkPasswordMatch);
        password.addEventListener('input', function () {
            if (confirmPassword.value.length > 0) {
                checkPasswordMatch();
            }
        });

        // Form validation
        form.addEventListener('submit', function (e) {
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Password dan Konfirmasi Password tidak cocok!');
                return false;
            }
        });

        // Auto-redirect after successful registration
        <?php if ($message_type === 'success'): ?>
            setTimeout(function () {
                window.location.href = '../login.php';
            }, 3000);
        <?php endif; ?>
    </script>
</body>

</html>
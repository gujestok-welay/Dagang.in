<?php
// Database migration runner
session_start();

// Cek apakah user sudah login dan admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/login.php');
    exit;
}

// Include config
require_once '../config/includes/config.php';

// Hanya admin yang bisa jalankan migration
$admin_emails = ['admin@dagang.in', 'developer@dagang.in'];
$query = "SELECT email FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!in_array($user['email'], $admin_emails)) {
    die('Unauthorized access');
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migration Runner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .migration-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }

        .migration-title {
            color: #667eea;
            margin-bottom: 30px;
            font-weight: bold;
            text-align: center;
        }

        .migration-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
        }

        .migration-item h6 {
            color: #333;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .migration-item p {
            color: #666;
            font-size: 14px;
            margin-bottom: 0;
        }

        .status-success {
            color: #28a745;
            font-weight: bold;
        }

        .status-error {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="migration-container">
        <h2 class="migration-title">🗄️ Database Migration Runner</h2>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_migration'])) {
            $migration_file = '../database/migration_add_categories.sql';

            if (!file_exists($migration_file)) {
                echo '<div class="alert alert-danger">
                    <strong>Error:</strong> Migration file not found!
                </div>';
            } else {
                // Baca file migration
                $sql = file_get_contents($migration_file);

                // Split queries by semicolon
                $queries = array_filter(array_map('trim', explode(';', $sql)), function ($query) {
                    return !empty($query) && !str_starts_with(trim($query), '--');
                });

                $success_count = 0;
                $error_count = 0;
                $errors = [];

                foreach ($queries as $query) {
                    if (trim($query)) {
                        if ($conn->query($query) === TRUE) {
                            $success_count++;
                        } else {
                            $error_count++;
                            $errors[] = $conn->error;
                        }
                    }
                }

                if ($error_count === 0) {
                    echo '<div class="alert alert-success">
                        <h5><i class="fas fa-check-circle"></i> Migration Berhasil!</h5>
                        <p>Semua ' . $success_count . ' query berhasil dijalankan.</p>
                    </div>';
                    echo '<div class="migration-item">
                        <h6>✅ Tasks Completed:</h6>
                        <ul style="margin-bottom: 0; padding-left: 20px;">
                            <li>Categories table created</li>
                            <li>Added category_id column to products table</li>
                            <li>Foreign key constraint established</li>
                            <li>Default category inserted</li>
                            <li>Indexes created for performance</li>
                        </ul>
                    </div>';
                } else {
                    echo '<div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-triangle"></i> Migration Partially Completed</h5>
                        <p>Success: ' . $success_count . ' | Errors: ' . $error_count . '</p>
                    </div>';

                    if (!empty($errors)) {
                        echo '<div class="alert alert-danger">';
                        echo '<strong>Errors:</strong><ul>';
                        foreach ($errors as $error) {
                            echo '<li>' . htmlspecialchars($error) . '</li>';
                        }
                        echo '</ul></div>';
                    }
                }
            }
        }
        ?>

        <div class="migration-item">
            <h6>📋 Migration Details:</h6>
            <p><strong>File:</strong> database/migration_add_categories.sql</p>
            <p><strong>Changes:</strong></p>
            <ul style="margin-bottom: 0; padding-left: 20px; font-size: 13px;">
                <li>Create <code>categories</code> table</li>
                <li>Add <code>category_id</code> column to <code>products</code></li>
                <li>Setup foreign key relationships</li>
                <li>Create performance indexes</li>
            </ul>
        </div>

        <form method="POST" style="margin-top: 30px;">
            <button type="submit" name="run_migration" class="btn btn-primary w-100"
                onclick="return confirm('Jalankan migration? Ini akan mengubah struktur database.');">
                <i class="fas fa-database"></i> Run Migration
            </button>
        </form>

        <a href="dashboard.php" class="btn btn-secondary w-100" style="margin-top: 10px;">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
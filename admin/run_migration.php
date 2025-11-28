<?php
// Database migration runner - AUTOMATED VERSION
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/login.php');
    exit;
}

// Include config
require_once '../config/includes/config.php';

// --- FUNGSI HELPER ---

/**
 * Membuat tabel migrations jika belum ada.
 * Tabel ini berfungsi sebagai "buku catatan" migrasi yang sudah dijalankan.
 */
function create_migrations_table_if_not_exists($conn)
{
    $query = "CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        migration_file VARCHAR(255) NOT NULL,
        executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY (migration_file)
    )";
    if (!$conn->query($query)) {
        // Jika gagal buat tabel, hentikan proses dengan pesan error
        die("Fatal Error: Could not create migrations table. Error: " . $conn->error);
    }
}

/**
 * Mengambil daftar file migrasi yang sudah pernah dijalankan dari database.
 */
function get_executed_migrations($conn)
{
    $result = $conn->query("SELECT migration_file FROM migrations");
    $executed_files = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $executed_files[] = $row['migration_file'];
        }
    }
    return $executed_files;
}

/**
 * Menjalankan query dari sebuah file migrasi.
 */
function run_migration_file($conn, $filepath)
{
    $sql = file_get_contents($filepath);
    
    // PERBAIKAN: Hapus komentar SQL (baris -- dan blok /* */) terlebih dahulu
    $sql_clean = preg_replace('/(--[^\n\r]*)|(\/\*[\s\S]*?\*\/)/', '', $sql);

    // Memecah file SQL yang sudah bersih menjadi query individu berdasarkan semicolon (;)
    $queries = array_filter(array_map('trim', explode(';', $sql_clean)));

    $results = ['success' => 0, 'errors' => []];
    
    if (empty($queries)) {
        // Jika file hanya berisi komentar, anggap sukses tapi beri catatan.
        $results['success'] = 1; 
        return $results;
    }

    foreach ($queries as $query) {
        if ($conn->query($query)) {
            $results['success']++;
        } else {
            // Tambahkan query yang gagal ke pesan error agar lebih mudah di-debug
            $results['errors'][] = "Error: " . $conn->error . " | Query: " . substr($query, 0, 100) . "...";
        }
    }
    return $results;
}

/**
 * Mencatat file migrasi yang berhasil dijalankan ke dalam tabel 'migrations'.
 */
function record_migration($conn, $filename)
{
    $stmt = $conn->prepare("INSERT INTO migrations (migration_file) VALUES (?)");
    $stmt->bind_param('s', $filename);
    $stmt->execute();
    $stmt->close();
}


// --- LOGIKA UTAMA ---

// 1. Pastikan tabel 'migrations' ada
create_migrations_table_if_not_exists($conn);

// 2. Dapatkan semua file migrasi yang sudah dieksekusi dari DB
$executed_migrations = get_executed_migrations($conn);

// 3. Pindai (scan) semua file .sql di folder database
$migration_files_path = '../database/';
$all_migration_files = glob($migration_files_path . '*.sql');

// 4. Filter untuk mendapatkan migrasi yang BARU (belum dieksekusi)
$pending_migrations = [];
foreach ($all_migration_files as $file) {
    $filename = basename($file);
    if (!in_array($filename, $executed_migrations)) {
        // Abaikan file schema utama
        if ($filename !== 'database_schema.sql') {
            $pending_migrations[] = $filename;
        }
    }
}
// Urutkan file agar dieksekusi sesuai urutan nama file (misal berdasarkan tanggal)
sort($pending_migrations);

// Variabel untuk menyimpan hasil eksekusi
$execution_results = [];

// 5. Proses jika ada request POST untuk menjalankan migrasi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_migrations'])) {
    if (!empty($pending_migrations)) {
        foreach ($pending_migrations as $migration_to_run) {
            $filepath = $migration_files_path . $migration_to_run;
            $result = run_migration_file($conn, $filepath);

            // Jika tidak ada error, catat ke database
            if (empty($result['errors'])) {
                record_migration($conn, $migration_to_run);
                $execution_results[$migration_to_run] = ['status' => 'success', 'details' => $result];
            } else {
                $execution_results[$migration_to_run] = ['status' => 'error', 'details' => $result];
                // Hentikan proses jika ada satu migrasi yang gagal
                break;
            }
        }
        // Refresh halaman untuk melihat status terbaru
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migration Runner (Otomatis)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f4f7f9;
        }

        .migration-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 40px;
            max-width: 800px;
            margin: 50px auto;
        }

        .migration-title {
            color: #343a40;
            margin-bottom: 30px;
            font-weight: 700;
            text-align: center;
        }

        .status-box {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
        }

        .status-box.success {
            background-color: #e9f7ef;
            border-left: 5px solid #28a745;
            color: #155724;
        }

        .status-box.warning {
            background-color: #fff3cd;
            border-left: 5px solid #ffc107;
            color: #856404;
        }

        .migration-list .card {
            margin-bottom: 15px;
            border: 1px solid #e9ecef;
            transition: all 0.2s ease-in-out;
        }

        .migration-list .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .migration-list .card-body {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .migration-list .file-icon {
            font-size: 1.5rem;
            margin-right: 15px;
            color: #6c757d;
        }

        .migration-list .status-badge {
            font-size: 0.8rem;
            padding: 5px 10px;
            border-radius: 50px;
        }
    </style>
</head>

<body>
    <div class="migration-container">
        <h2 class="migration-title"><i class="fas fa-database"></i> Database Migration Runner</h2>

        <?php if (!empty($pending_migrations)) : ?>
            <div class="status-box warning">
                <h5><i class="fas fa-exclamation-triangle"></i> Pending Migrations Found!</h5>
                <p class="mb-0">Terdapat <strong><?= count($pending_migrations) ?></strong> file migrasi baru yang perlu dijalankan.</p>
            </div>
        <?php else : ?>
            <div class="status-box success">
                <h5><i class="fas fa-check-circle"></i> Database is Up-to-Date</h5>
                <p class="mb-0">Struktur database Anda sudah sinkron dengan versi kode terbaru.</p>
            </div>
        <?php endif; ?>

        <div class="migration-list">
            <h5 class="mb-3">Daftar Migrasi:</h5>

            <?php if (empty($all_migration_files)) : ?>
                <p class="text-muted">Tidak ada file migrasi (.sql) yang ditemukan di folder <code>/database</code>.</p>
            <?php else : ?>
                <?php foreach ($all_migration_files as $file) :
                    $filename = basename($file);
                    if ($filename === 'database_schema.sql') continue; // Skip file schema
                    $is_executed = in_array($filename, $executed_migrations);
                ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-file-code file-icon <?= $is_executed ? 'text-success' : 'text-warning' ?>"></i>
                                <span><?= htmlspecialchars($filename) ?></span>
                            </div>
                            <?php if ($is_executed) : ?>
                                <span class="badge bg-success status-badge">Executed</span>
                            <?php else : ?>
                                <span class="badge bg-warning text-dark status-badge">Pending</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <form method="POST" class="mt-4">
            <?php if (!empty($pending_migrations)) : ?>
                <button type="submit" name="run_migrations" class="btn btn-primary w-100 btn-lg" onclick="return confirm('Anda yakin ingin menjalankan <?= count($pending_migrations) ?> migrasi yang pending? Ini akan mengubah struktur database.');">
                    <i class="fas fa-play-circle"></i> Jalankan Migrasi Pending
                </button>
            <?php else : ?>
                <button type="button" class="btn btn-secondary w-100 btn-lg" disabled>
                    <i class="fas fa-check-circle"></i> Semua Migrasi Sudah Dijalankan
                </button>
            <?php endif; ?>
        </form>

        <a href="dashboard.php" class="btn btn-outline-secondary w-100 mt-3">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
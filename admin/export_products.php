<?php
// Mulai session
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Include config
require_once '../config/includes/config.php';

// Tentukan format export
$format = isset($_GET['format']) ? $_GET['format'] : 'csv';

if ($format === 'csv') {
    exportToCSV();
} elseif ($format === 'excel') {
    exportToExcel();
} else {
    header('Location: products.php');
    exit;
}

/**
 * Export produk ke format CSV
 */
function exportToCSV()
{
    global $conn;

    $user_id = $_SESSION['user_id'];

    // Query untuk mendapatkan semua produk user
    $query = "SELECT 
                p.id,
                p.name,
                c.name as category,
                p.description,
                p.price,
                p.stock,
                p.created_at,
                p.updated_at
              FROM products p
              LEFT JOIN categories c ON p.category_id = c.id
              WHERE p.user_id = ?
              ORDER BY p.created_at DESC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Set header untuk download CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="produk_' . date('Y-m-d_H-i-s') . '.csv"');

    // Output CSV header
    $output = fopen('php://output', 'w');

    // Write BOM untuk UTF-8 compatibility
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // Tulis header kolom
    fputcsv($output, ['ID', 'Nama Produk', 'Kategori', 'Deskripsi', 'Harga', 'Stok', 'Dibuat', 'Diupdate'], ',');

    // Tulis data produk
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['name'],
            $row['category'] ?? 'Tidak Dikategorikan',
            $row['description'],
            number_format($row['price'], 2, ',', '.'),
            $row['stock'],
            date('d-m-Y H:i', strtotime($row['created_at'])),
            date('d-m-Y H:i', strtotime($row['updated_at']))
        ], ',');
    }

    fclose($output);
    $stmt->close();
    exit;
}

/**
 * Export produk ke format Excel (XLSX)
 */
function exportToExcel()
{
    global $conn;

    $user_id = $_SESSION['user_id'];

    // Query untuk mendapatkan semua produk user
    $query = "SELECT 
                p.id,
                p.name,
                c.name as category,
                p.description,
                p.price,
                p.stock,
                p.created_at,
                p.updated_at
              FROM products p
              LEFT JOIN categories c ON p.category_id = c.id
              WHERE p.user_id = ?
              ORDER BY p.created_at DESC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Collect data
    $data = [];
    $data[] = ['ID', 'Nama Produk', 'Kategori', 'Deskripsi', 'Harga', 'Stok', 'Dibuat', 'Diupdate'];

    while ($row = $result->fetch_assoc()) {
        $data[] = [
            $row['id'],
            $row['name'],
            $row['category'] ?? 'Tidak Dikategorikan',
            $row['description'],
            $row['price'],
            $row['stock'],
            date('d-m-Y H:i', strtotime($row['created_at'])),
            date('d-m-Y H:i', strtotime($row['updated_at']))
        ];
    }

    // Generate HTML untuk ditampilkan sebagai Excel
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="produk_' . date('Y-m-d_H-i-s') . '.xls"');

    // Output BOM untuk UTF-8
    echo "\xEF\xBB\xBF";

    // Mulai tabel HTML
    echo '<table border="1">';
    echo '<tr>';
    foreach ($data[0] as $header) {
        echo '<td style="background-color: #4CAF50; color: white; font-weight: bold;">' . htmlspecialchars($header) . '</td>';
    }
    echo '</tr>';

    // Output data rows
    for ($i = 1; $i < count($data); $i++) {
        echo '<tr>';
        foreach ($data[$i] as $cell) {
            echo '<td>' . htmlspecialchars($cell) . '</td>';
        }
        echo '</tr>';
    }

    echo '</table>';

    $stmt->close();
    exit;
}
?>
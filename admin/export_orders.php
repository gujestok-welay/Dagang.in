<?php
// Mulai session
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/login.php');
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
    header('Location: orders.php');
    exit;
}

/**
 * Export order ke format CSV
 */
function exportToCSV()
{
    global $conn;

    $user_id = $_SESSION['user_id'];

    // Query untuk mendapatkan semua order user
    $query = "SELECT 
                o.id,
                o.customer_name,
                o.customer_email,
                o.customer_phone,
                o.total,
                o.status,
                COUNT(op.id) as total_items,
                SUM(op.quantity) as total_quantity,
                o.created_at,
                o.updated_at
              FROM orders o
              LEFT JOIN order_items op ON o.id = op.order_id
              WHERE o.user_id = ?
              GROUP BY o.id
              ORDER BY o.created_at DESC";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        header('Content-Type: text/plain; charset=utf-8');
        http_response_code(500);
        echo "SQL Prepare failed: " . $conn->error;
        exit;
    }
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Set header untuk download CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="order_' . date('Y-m-d_H-i-s') . '.csv"');

    // Output CSV header
    $output = fopen('php://output', 'w');

    // Write BOM untuk UTF-8 compatibility
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // Tulis header kolom
    fputcsv($output, ['ID', 'Nama Pelanggan', 'Email', 'Telepon', 'Total Item', 'Total Qty', 'Total Harga', 'Status', 'Dibuat', 'Diupdate'], ',');

    // Status mapping untuk tampilan
    $status_labels = [
        'pending' => 'Menunggu',
        'processing' => 'Diproses',
        'shipped' => 'Dikirim',
        'delivered' => 'Terkirim',
        'cancelled' => 'Dibatalkan'
    ];

    // Tulis data order
    while ($row = $result->fetch_assoc()) {
        $status_label = $status_labels[$row['status']] ?? $row['status'];

        fputcsv($output, [
            'ORDER-' . str_pad($row['id'], 5, '0', STR_PAD_LEFT),
            $row['customer_name'],
            $row['customer_email'],
            $row['customer_phone'],
            $row['total_items'],
            $row['total_quantity'],
            'Rp ' . number_format($row['total'], 2, ',', '.'),
            $status_label,
            date('d-m-Y H:i', strtotime($row['created_at'])),
            date('d-m-Y H:i', strtotime($row['updated_at']))
        ], ',');
    }

    fclose($output);
    $stmt->close();
    exit;
}

/**
 * Export order ke format Excel (XLSX)
 */
function exportToExcel()
{
    global $conn;

    $user_id = $_SESSION['user_id'];

    // Query untuk mendapatkan semua order user
    $query = "SELECT 
                                o.id,
                                c.name AS customer_name,
                                c.email AS customer_email,
                                c.phone AS customer_phone,
                                o.total,
                                o.status,
                                COUNT(op.id) as total_items,
                                SUM(op.quantity) as total_quantity,
                                o.created_at
                            FROM orders o
                            LEFT JOIN customers c ON o.customer_id = c.id
                            LEFT JOIN order_items op ON o.id = op.order_id
                            WHERE o.user_id = ?
                            GROUP BY o.id
                            ORDER BY o.created_at DESC";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        header('Content-Type: text/plain; charset=utf-8');
        http_response_code(500);
        echo "SQL Prepare failed: " . $conn->error;
        exit;
    }
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Collect data
    $data = [];
    $data[] = ['ID', 'Nama Pelanggan', 'Email', 'Telepon', 'Total Item', 'Total Qty', 'Total Harga', 'Status', 'Dibuat', 'Diupdate'];

    // Status mapping untuk tampilan
    $status_labels = [
        'pending' => 'Menunggu',
        'processing' => 'Diproses',
        'shipped' => 'Dikirim',
        'delivered' => 'Terkirim',
        'cancelled' => 'Dibatalkan'
    ];

    while ($row = $result->fetch_assoc()) {
        $status_label = $status_labels[$row['status']] ?? $row['status'];

        $data[] = [
            'ORDER-' . str_pad($row['id'], 5, '0', STR_PAD_LEFT),
            $row['customer_name'],
            $row['customer_email'],
            $row['customer_phone'],
            $row['total_items'],
            $row['total_quantity'],
            'Rp ' . number_format($row['total'], 2, ',', '.'),
            $status_label,
            date('d-m-Y H:i', strtotime($row['created_at']))
        ];
    }

    // Generate HTML untuk ditampilkan sebagai Excel
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="order_' . date('Y-m-d_H-i-s') . '.xls"');

    // Output BOM untuk UTF-8
    echo "\xEF\xBB\xBF";

    // Mulai tabel HTML
    echo '<table border="1">';
    echo '<tr>';
    foreach ($data[0] as $header) {
        echo '<td style="background-color: #2196F3; color: white; font-weight: bold;">' . htmlspecialchars($header) . '</td>';
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
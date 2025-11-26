<?php
session_start();
require_once '../config/includes/config.php';

// Cek Login & Admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/login.php');
    exit;
}
$user_id = $_SESSION['user_id'];

// Ambil info Toko untuk Kop Surat
$user_query = $conn->prepare("SELECT store_name, address, phone, email FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$store = $user_query->get_result()->fetch_assoc();

// Filter Logic (Sama seperti di orders.php agar sinkron)
$search = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? '';

$where_conditions = ["o.user_id = ?"];
$params = [$user_id];
$param_types = 'i';

if (!empty($search)) {
    $search_term = '%' . $search . '%';
    $where_conditions[] = "(c.name LIKE ? OR c.phone LIKE ?)";
    $params[] = $search_term;
    $params[] = $search_term;
    $param_types .= 'ss';
}

if (!empty($status_filter)) {
    $where_conditions[] = "o.status = ?";
    $params[] = $status_filter;
    $param_types .= 's';
}

$where_clause = implode(' AND ', $where_conditions);

// Query Data (SUDAH DIPERBAIKI DENGAN JOIN)
$query = "
    SELECT
        o.id, o.created_at, o.total, o.status,
        c.name as customer_name, c.phone,
        (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as total_items
    FROM orders o
    JOIN customers c ON o.customer_id = c.id
    WHERE $where_clause
    ORDER BY o.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Pesanan - <?php echo htmlspecialchars($store['store_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* CSS Khusus Cetak */
        body {
            background: #f5f5f5;
            /* Abu-abu di layar */
            font-family: 'Times New Roman', Times, serif;
            /* Font formal untuk laporan */
        }

        .sheet {
            background: white;
            width: 210mm;
            /* Lebar A4 */
            min-height: 297mm;
            /* Tinggi A4 */
            margin: 20mm auto;
            padding: 20mm;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
            text-align: center;
        }

        .store-name {
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
            text-decoration: underline;
        }

        .table-custom th {
            background-color: #f8f9fa !important;
            border-bottom: 2px solid #000 !important;
        }

        .status-badge {
            font-size: 0.8rem;
            padding: 2px 6px;
            border: 1px solid #333;
            border-radius: 4px;
        }

        /* Sembunyikan elemen saat diprint */
        @media print {
            body {
                background: none;
                margin: 0;
            }

            .sheet {
                width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }

            .no-print {
                display: none !important;
            }

            /* Pastikan background warna tabel tercetak */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>

<body>

    <div class="no-print fixed-top p-3 text-end">
        <button onclick="window.print()" class="btn btn-primary shadow">
            🖨️ Cetak / Simpan PDF
        </button>
        <button onclick="window.close()" class="btn btn-secondary shadow">
            Tutup
        </button>
    </div>

    <div class="sheet">
        <div class="header">
            <div class="store-name"><?php echo htmlspecialchars($store['store_name']); ?></div>
            <div><?php echo htmlspecialchars($store['address'] ?? 'Alamat Toko Belum Diatur'); ?></div>
            <div>Telp: <?php echo htmlspecialchars($store['phone']); ?> | Email:
                <?php echo htmlspecialchars($store['email']); ?>
            </div>
        </div>

        <div class="report-title">LAPORAN DATA PESANAN</div>

        <div class="mb-3">
            <strong>Periode Cetak:</strong> <?php echo date('d F Y H:i'); ?><br>
            <strong>Filter Status:</strong> <?php echo $status_filter ? ucfirst($status_filter) : 'Semua Status'; ?>
        </div>

        <table class="table table-bordered table-custom table-sm">
            <thead>
                <tr class="text-center">
                    <th width="5%">No</th>
                    <th width="15%">Tanggal</th>
                    <th width="25%">Pelanggan</th>
                    <th width="10%">Item</th>
                    <th width="15%">Status</th>
                    <th width="20%">Total (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                $grand_total = 0;
                if ($result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                        $grand_total += $row['total'];
                        ?>
                        <tr>
                            <td class="text-center"><?php echo $no++; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['customer_name']); ?></strong><br>
                                <small><?php echo htmlspecialchars($row['phone']); ?></small>
                            </td>
                            <td class="text-center"><?php echo $row['total_items']; ?></td>
                            <td class="text-center">
                                <span class="status-badge">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                            </td>
                            <td class="text-end"><?php echo number_format($row['total'], 0, ',', '.'); ?></td>
                        </tr>
                        <?php
                    endwhile;
                else:
                    ?>
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada data pesanan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-end fw-bold">TOTAL PENDAPATAN</td>
                    <td class="text-end fw-bold" style="background-color: #eee;">
                        Rp <?php echo number_format($grand_total, 0, ',', '.'); ?>
                    </td>
                </tr>
            </tfoot>
        </table>

        <?php
        // Ambil kota dari alamat toko (jika ada)
        $alamat = $store['address'] ?? '';
        $kota = 'Kota Anda'; // Default jika tidak ditemukan
        
        // Coba ekstrak kota dari alamat (asumsi format: "Jalan ... , Kota ...")
        if (!empty($alamat)) {
            // Cari kata "Kota" atau ambil kata terakhir setelah koma
            $parts = explode(',', $alamat);
            $last_part = trim(end($parts));
            if (stripos($last_part, 'kota') !== false) {
                $kota = $last_part;
            } else {
                // Jika tidak ada kata "Kota", ambil kata terakhir saja
                $kota = $last_part;
            }
        }
        ?>
        <div style="margin-top: 50px; float: right; width: 200px; text-align: center;">
            <p><?php echo htmlspecialchars($kota); ?>, <?php echo date('d F Y'); ?></p>
            <p style="margin-top: 60px; font-weight: bold; text-decoration: underline;">
                <?php echo htmlspecialchars($store['store_name']); ?> Owner
            </p>
        </div>
    </div>

    <script>
        // Opsional: Otomatis muncul dialog print saat dibuka
        // window.onload = function() { window.print(); }
    </script>
</body>

</html>
<?php
/**
 * DATABASE SEEDER DAGANG.IN
 * =============================
 * File ini berfungsi untuk mengisi database dengan data demo (toko dan produk).
 * Cukup jalankan file ini sekali melalui browser.
 * AMAN untuk dijalankan berulang kali, tidak akan membuat data duplikat.
 */

// LANGKAH 1: Selalu panggil file koneksi di paling atas!
// Ini akan membuat variabel $conn tersedia untuk seluruh script.
require_once '../config/includes/config.php';

// Data Demo Toko dan Produk UMKM
$toko_demo = [
    [
        'user' => [
            'username' => 'batikjaya',
            'email' => 'kontak@batikjaya.com',
            'password' => 'password123',
            'store_name' => 'Batik Jaya Abadi',
            'phone' => '081234567890',
            'address' => 'Jl. Malioboro No. 10, Yogyakarta'
        ],
        'products' => [
            ['name' => 'Kemeja Batik Pria Lengan Panjang', 'price' => 250000, 'stock' => 50, 'description' => 'Kemeja batik katun primisima, adem dan nyaman dipakai.'],
            ['name' => 'Dress Batik Wanita Modern', 'price' => 320000, 'stock' => 35, 'description' => 'Dress modern dengan motif parang kontemporer.'],
            ['name' => 'Kain Batik Tulis Madura', 'price' => 750000, 'stock' => 15, 'description' => 'Kain batik tulis asli dari pengrajin Madura, cocok untuk koleksi.'],
        ]
    ],
    [
        'user' => [
            'username' => 'kopisenja',
            'email' => 'order@kopisenja.id',
            'password' => 'password123',
            'store_name' => 'Kopi Senja Nusantara',
            'phone' => '085678901234',
            'address' => 'Jl. Braga No. 25, Bandung'
        ],
        'products' => [
            ['name' => 'Kopi Arabika Gayo 250g', 'price' => 85000, 'stock' => 120, 'description' => 'Biji kopi pilihan dari dataran tinggi Gayo, Aceh. Aroma fruity.'],
            ['name' => 'Kopi Robusta Lampung 250g', 'price' => 60000, 'stock' => 200, 'description' => 'Kopi robusta dengan body tebal dan rasa coklat yang kuat.'],
            ['name' => 'Paket Cold Brew Siap Minum', 'price' => 125000, 'stock' => 40, 'description' => '5 botol cold brew rasa original, hazelnut, dan caramel.'],
            ['name' => 'V60 Dripper Set Keramik', 'price' => 180000, 'stock' => 30, 'description' => 'Set lengkap untuk menyeduh kopi manual di rumah.'],
        ]
    ],
    [
        'user' => [
            'username' => 'kerajinankulit',
            'email' => 'info@kerajinankulit.com',
            'password' => 'password123',
            'store_name' => 'Garut Kulit Asli',
            'phone' => '087811223344',
            'address' => 'Jl. Cimanuk No. 50, Garut'
        ],
        'products' => [
            ['name' => 'Jaket Kulit Domba Pria', 'price' => 1200000, 'stock' => 20, 'description' => 'Jaket kulit domba asli Garut, model biker. Lentur dan tahan lama.'],
            ['name' => 'Tas Selempang Kulit Sapi Wanita', 'price' => 850000, 'stock' => 25, 'description' => 'Tas elegan dari kulit sapi pull-up, warna tan.'],
            ['name' => 'Dompet Kulit Pria', 'price' => 350000, 'stock' => 80, 'description' => 'Dompet minimalis dengan banyak slot kartu, jahitan tangan rapi.'],
        ]
    ],
];

echo "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><title>Database Seeder</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'></head>";
echo "<body class='bg-light'><div class='container p-5'><h1 class='mb-4'>Dagang.in Database Seeder</h1>";
echo "<div class='alert alert-info'>Script ini akan mengisi data demo ke database. Script ini aman dijalankan berulang kali.</div>";
echo "<ul class='list-group'>";

try {
    // LANGKAH 2: Sekarang variabel $conn sudah ada, kita bisa mulai transaksi
    $conn->begin_transaction();

    foreach ($toko_demo as $toko) {
        // --- Cek & Masukkan data User (Toko) ---
        $userData = $toko['user'];
        $stmtCheck = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmtCheck->bind_param("ss", $userData['username'], $userData['email']);
        $stmtCheck->execute();
        $result = $stmtCheck->get_result();
        $existingUser = $result->fetch_assoc();
        $userId = null;

        if ($existingUser) {
            $userId = $existingUser['id'];
            echo "<li class='list-group-item list-group-item-warning'>Toko '{$userData['store_name']}' sudah ada, proses insert user dilewati.</li>";
        } else {
            $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
            $stmtUser = $conn->prepare("INSERT INTO users (username, email, password, store_name, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtUser->bind_param("ssssss", $userData['username'], $userData['email'], $hashedPassword, $userData['store_name'], $userData['phone'], $userData['address']);
            $stmtUser->execute();
            $userId = $conn->insert_id;
            echo "<li class='list-group-item list-group-item-success'>Toko '{$userData['store_name']}' berhasil dibuat.</li>";
        }

        // --- Masukkan data Produk untuk user ini ---
        if ($userId) {
            foreach ($toko['products'] as $productData) {
                $stmtCheckProduct = $conn->prepare("SELECT id FROM products WHERE name = ? AND user_id = ?");
                $stmtCheckProduct->bind_param("si", $productData['name'], $userId);
                $stmtCheckProduct->execute();
                $resultProd = $stmtCheckProduct->get_result();

                if ($resultProd->fetch_assoc()) {
                    echo "<li class='list-group-item list-group-item-light ps-5'>&hookrightarrow; Produk '{$productData['name']}' sudah ada, dilewati.</li>";
                } else {
                    $stmtProduct = $conn->prepare("INSERT INTO products (user_id, name, description, price, stock) VALUES (?, ?, ?, ?, ?)");
                    $stmtProduct->bind_param("issdi", $userId, $productData['name'], $productData['description'], $productData['price'], $productData['stock']);
                    $stmtProduct->execute();
                    echo "<li class='list-group-item ps-5'>&hookrightarrow; Produk '{$productData['name']}' berhasil ditambahkan.</li>";
                }
            }
        }
    }

    $conn->commit();
    echo "</ul><div class='alert alert-success mt-4'><strong>Selesai!</strong> Semua data demo berhasil diproses.</div>";

} catch (Exception $e) {
    $conn->rollback();
    echo "</ul><div class='alert alert-danger mt-4'><strong>ERROR:</strong> Terjadi kesalahan. Semua perubahan dibatalkan. Pesan: " . $e->getMessage() . "</div>";
}

echo "</div></body></html>";
?>
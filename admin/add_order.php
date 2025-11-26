<?php
$currentPage = 'orders';
$pageTitle = 'Tambah Pesanan';
require_once 'templates/header.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$user_id = $_SESSION['user_id'];
$message = '';

// Get products for selection (hanya produk aktif, tidak di-soft delete)
$products_query = $conn->prepare("SELECT id, name, price, stock FROM products WHERE user_id = ? AND stock > 0 AND is_deleted = 0");
$products_query->bind_param("i", $user_id);
$products_query->execute();
$products = $products_query->get_result();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_name = $_POST['customer_name'];
    $customer_phone = $_POST['customer_phone'];
    $customer_email = $_POST['customer_email'];
    $customer_address = $_POST['customer_address'];
    $payment_method = $_POST['payment_method'];
    $notes = $_POST['notes'];
    $order_items = $_POST['order_items'];

    // Validasi dasar
    if (empty($_POST['order_items'])) {
        $message = 'Tidak ada item produk.';
    } else {
        $conn->begin_transaction();
        try {
            $customer_stmt = $conn->prepare("INSERT INTO customers (user_id, name, phone, email, address) VALUES (?, ?, ?, ?, ?)");
            $customer_stmt->bind_param("issss", $user_id, $customer_name, $customer_phone, $customer_email, $customer_address);
            $customer_stmt->execute();
            $customer_id = $conn->insert_id;

            $total = 0;
            foreach ($order_items as $item) {
                $product_id = (int) $item['product_id'];
                $quantity = (int) $item['quantity'];
                $product_query = $conn->prepare("SELECT price FROM products WHERE id = ? AND user_id = ?");
                $product_query->bind_param("ii", $product_id, $user_id);
                $product_query->execute();
                $product = $product_query->get_result()->fetch_assoc();
                if (!$product) {
                    throw new Exception("Produk tidak ditemukan atau bukan milik user.");
                }
                $total += $product['price'] * $quantity;
            }

            // Insert order (tambahkan status + created_at)
            $status = 'pending';
            $order_stmt = $conn->prepare("
                INSERT INTO orders (customer_id, user_id, total, payment_method, notes, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            // Tipe parameter: customer_id (i), user_id (i), total (d), payment_method (s), notes (s), status (s)
            $order_stmt->bind_param("iidsss", $customer_id, $user_id, $total, $payment_method, $notes, $status);
            $order_stmt->execute();
            $order_id = $conn->insert_id;

            foreach ($order_items as $item) {
                $product_id = (int) $item['product_id'];
                $quantity = (int) $item['quantity'];
                $product_query = $conn->prepare("SELECT price, stock FROM products WHERE id = ? AND user_id = ?");
                $product_query->bind_param("ii", $product_id, $user_id);
                $product_query->execute();
                $product = $product_query->get_result()->fetch_assoc();
                if (!$product) {
                    throw new Exception("Produk tidak ditemukan.");
                }
                if ($product['stock'] < $quantity) {
                    throw new Exception("Stok produk kurang.");
                }

                $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $item_stmt->bind_param("iiid", $order_id, $product_id, $quantity, $product['price']);
                $item_stmt->execute();

                $update_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $update_stock->bind_param("ii", $quantity, $product_id);
                $update_stock->execute();
            }

            $conn->commit();
            // Redirect agar terlihat di daftar
            header("Location: orders.php?added=1");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Gagal menambah pesanan: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h3>Tambah Pesanan Baru</h3>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-success">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST">
                        <h5>Data Pelanggan</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="customer_name" class="form-label">Nama Pelanggan</label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name"
                                    required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="customer_phone" class="form-label">No. Telepon</label>
                                <input type="text" class="form-control" id="customer_phone" name="customer_phone"
                                    required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="customer_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="customer_email" name="customer_email">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="payment_method" class="form-label">Metode Pembayaran</label>
                                <select class="form-control" id="payment_method" name="payment_method" required>
                                    <option value="cash">Tunai</option>
                                    <option value="transfer">Transfer Bank</option>
                                    <option value="ewallet">E-Wallet</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="customer_address" class="form-label">Alamat</label>
                            <textarea class="form-control" id="customer_address" name="customer_address" rows="2"
                                required></textarea>
                        </div>


                        <h5 class="mt-4">Produk Pesanan</h5>
                        <div id="order-items">
                            <div class="order-item border rounded p-3 mb-3 position-relative">
                                <button type="button"
                                    class="btn-close position-absolute top-0 end-0 m-2 remove-item d-md-none"
                                    aria-label="Hapus"></button>
                                <div class="row g-2 align-items-end">
                                    <div class="col-12 col-md-5">
                                        <label class="form-label">Produk</label>
                                        <select class="form-control product-select" name="order_items[0][product_id]"
                                            required>
                                            <option value="">Pilih Produk</option>
                                            <?php
                                            // Perlu query ulang karena $products sudah habis di atas
                                            $products_query = $conn->prepare("SELECT id, name, price, stock FROM products WHERE user_id = ? AND stock > 0 AND is_deleted = 0");
                                            $products_query->bind_param("i", $user_id);
                                            $products_query->execute();
                                            $products = $products_query->get_result();
                                            while ($product = $products->fetch_assoc()): ?>
                                                <option value="<?php echo $product['id']; ?>"
                                                    data-price="<?php echo $product['price']; ?>"
                                                    data-stock="<?php echo $product['stock']; ?>">
                                                    <?php echo htmlspecialchars($product['name']); ?> - Rp
                                                    <?php echo number_format($product['price'], 0, ',', '.'); ?> (Stok:
                                                    <?php echo $product['stock']; ?>)
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label">Jumlah</label>
                                        <input type="number" class="form-control quantity-input"
                                            name="order_items[0][quantity]" min="1" required>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label">Subtotal</label>
                                        <input type="text" class="form-control subtotal" readonly>
                                    </div>
                                    <div class="col-12 col-md-1 d-flex align-items-end justify-content-end">
                                        <button type="button"
                                            class="btn btn-danger remove-item d-none d-md-block w-100">X</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="add-item" class="btn btn-secondary mb-3">Tambah Item</button>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Catatan</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                        </div>

                        <div class="mb-3">
                            <strong>Total: Rp <span id="total-amount">0</span></strong>
                        </div>

                        <button type="submit" class="btn btn-primary">Simpan Pesanan</button>
                        <a href="orders.php" class="btn btn-secondary">Kembali</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let itemCount = 1;

    // Fungsi untuk generate HTML order-item baru
    function getOrderItemHtml(idx) {
        // Option produk harus diambil ulang dari DOM (karena PHP tidak bisa di JS)
        const productOptions = document.querySelector('.product-select').innerHTML;
        return `
                            <div class="order-item border rounded p-3 mb-3 position-relative">
                                <button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-item d-md-none" aria-label="Hapus"></button>
                                <div class="row g-2 align-items-end">
                                    <div class="col-12 col-md-5">
                                        <label class="form-label">Produk</label>
                                        <select class="form-control product-select" name="order_items[${idx}][product_id]" required>
                                            ${productOptions}
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label">Jumlah</label>
                                        <input type="number" class="form-control quantity-input" name="order_items[${idx}][quantity]" min="1" required>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label">Subtotal</label>
                                        <input type="text" class="form-control subtotal" readonly>
                                    </div>
                                    <div class="col-12 col-md-1 d-flex align-items-end justify-content-end">
                                        <button type="button" class="btn btn-danger remove-item d-none d-md-block w-100">X</button>
                                    </div>
                                </div>
                            </div>
                            `;
    }

    document.getElementById('add-item').addEventListener('click', function () {
        const orderItems = document.getElementById('order-items');
        orderItems.insertAdjacentHTML('beforeend', getOrderItemHtml(itemCount));
        itemCount++;
        updateTotal();
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-item')) {
            // Pastikan minimal 1 item tersisa
            if (document.querySelectorAll('.order-item').length > 1) {
                e.target.closest('.order-item').remove();
                updateTotal();
            }
        }
    });

    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('product-select') || e.target.classList.contains('quantity-input')) {
            updateTotal();
        }
    });

    function updateTotal() {
        let total = 0;
        document.querySelectorAll('.order-item').forEach(item => {
            const select = item.querySelector('.product-select');
            const quantity = item.querySelector('.quantity-input').value;
            const subtotal = item.querySelector('.subtotal');

            if (select.value && quantity) {
                const price = select.options[select.selectedIndex].getAttribute('data-price');
                const sub = price * quantity;
                subtotal.value = 'Rp ' + Number(sub).toLocaleString('id-ID');
                total += Number(sub);
            } else {
                subtotal.value = '';
            }
        });
        document.getElementById('total-amount').textContent = total.toLocaleString('id-ID');
    }

    // Inisialisasi total saat load
    updateTotal();
</script>

<?php require_once 'templates/footer.php'; ?>
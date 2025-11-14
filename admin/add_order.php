<?php
require_once '../config/includes/auth.php';
require_once '../config/includes/config.php';

if (isset($_GET['logout'])) {
    logout();
}

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// Get products for selection
$products_query = $conn->prepare("SELECT id, name, price, stock FROM products WHERE user_id = ? AND stock > 0");
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

    // Insert customer
    $customer_stmt = $conn->prepare("INSERT INTO customers (name, phone, email, address) VALUES (?, ?, ?, ?)");
    $customer_stmt->bind_param("ssss", $customer_name, $customer_phone, $customer_email, $customer_address);
    $customer_stmt->execute();
    $customer_id = $conn->insert_id;

    // Calculate total
    $total = 0;
    foreach ($order_items as $item) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];

        $product_query = $conn->prepare("SELECT price FROM products WHERE id = ?");
        $product_query->bind_param("i", $product_id);
        $product_query->execute();
        $product_result = $product_query->get_result();
        $product = $product_result->fetch_assoc();

        $total += $product['price'] * $quantity;
    }

    // Insert order
    $order_stmt = $conn->prepare("INSERT INTO orders (customer_id, user_id, total, payment_method, notes) VALUES (?, ?, ?, ?, ?)");
    $order_stmt->bind_param("iisss", $customer_id, $user_id, $total, $payment_method, $notes);
    $order_stmt->execute();
    $order_id = $conn->insert_id;

    // Insert order items and update stock
    foreach ($order_items as $item) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];

        $product_query = $conn->prepare("SELECT price FROM products WHERE id = ?");
        $product_query->bind_param("i", $product_id);
        $product_query->execute();
        $product_result = $product_query->get_result();
        $product = $product_result->fetch_assoc();

        $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $item_stmt->bind_param("iiid", $order_id, $product_id, $quantity, $product['price']);
        $item_stmt->execute();

        // Update stock
        $update_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $update_stock->bind_param("ii", $quantity, $product_id);
        $update_stock->execute();
    }

    $message = "Pesanan berhasil ditambahkan!";
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pesanan - Dagang.in</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-brand">
        <div class="container">
            <a class="navbar-brand" href="#">Dagang.in</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">Produk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="orders.php">Pesanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="customers.php">Pelanggan</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="?logout=1">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

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
                                <div class="order-item row mb-3">
                                    <div class="col-md-5">
                                        <label class="form-label">Produk</label>
                                        <select class="form-control product-select" name="order_items[0][product_id]"
                                            required>
                                            <option value="">Pilih Produk</option>
                                            <?php while ($product = $products->fetch_assoc()): ?>
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
                                    <div class=" col-md-3">
                                        <label class="form-label">Jumlah</label>
                                        <input type="number" class="form-control quantity-input"
                                            name="order_items[0][quantity]" min="1" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Subtotal</label>
                                        <input type="text" class="form-control subtotal" readonly>
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <button type="button" class="btn btn-danger remove-item">X</button>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let itemCount = 1;

        document.getElementById('add-item').addEventListener('click', function () {
            const orderItems = document.getElementById('order-items');
            const newItem = orderItems.querySelector('.order-item').cloneNode(true);
            newItem.querySelectorAll('input, select').forEach(input => {
                input.name = input.name.replace('[0]', '[' + itemCount + ']');
                input.value = '';
            });
            orderItems.appendChild(newItem);
            itemCount++;
        });

        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-item')) {
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
                    subtotal.value = 'Rp ' + sub.toLocaleString('id-ID');
                    total += sub;
                }
            });
            document.getElementById('total-amount').textContent = total.toLocaleString('id-ID');
        }
    </script>
</body>

</html>
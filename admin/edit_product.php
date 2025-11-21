<?php
$currentPage = 'products';
$pageTitle = 'Edit Produk';
require_once 'templates/header.php';
require_once '../config/utils/FileUploadValidator.php';

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = 'danger'; // 'danger' or 'success'

// Get product ID
if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$product_id = (int) $_GET['id'];

// Get product data
$product_query = $conn->prepare("SELECT * FROM products WHERE id = ? AND user_id = ?");
if (!$product_query) {
    die("Database error: " . $conn->error);
}

$product_query->bind_param("ii", $product_id, $user_id);
$product_query->execute();
$product_result = $product_query->get_result();

if ($product_result->num_rows == 0) {
    header("Location: products.php");
    exit();
}

$product = $product_result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = str_replace(['.', ','], '', $_POST['price'] ?? '0');
        $price = (float) $price;
        $stock = (int) ($_POST['stock'] ?? 0);
        $image = $product['image']; // Keep old image by default

        // Validate input fields
        if (empty($name) || empty($description) || $price <= 0 || $stock < 0) {
            $message = "Harap isi semua field dengan benar.";
        } else {
            // Handle image upload with secure validation
            if (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE) {
                $validation_result = FileUploadValidator::validate($_FILES['image']);

                if (!$validation_result['valid']) {
                    $message = $validation_result['message'];
                } else {
                    $target_dir = "../assets/uploads/";
                    $new_filename = $validation_result['data']['filename'];
                    $target_file = $target_dir . $new_filename;

                    $move_result = FileUploadValidator::moveUploadedFile($_FILES['image']['tmp_name'], $target_file);

                    if (!$move_result['success']) {
                        $message = $move_result['message'];
                    } else {
                        // Delete old image if exists and different
                        $old_image_path = $target_dir . $product['image'];
                        if (!empty($product['image']) && file_exists($old_image_path) && $old_image_path !== $target_file) {
                            FileUploadValidator::deleteFile($old_image_path);
                        }
                        $image = $new_filename;
                    }
                }
            }

            // Update product if no errors
            if (empty($message)) {
                $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, image = ? WHERE id = ? AND user_id = ?");

                if (!$stmt) {
                    $message = "Kesalahan database: " . $conn->error;
                } else {
                    $stmt->bind_param("ssdisii", $name, $description, $price, $stock, $image, $product_id, $user_id);

                    if ($stmt->execute()) {
                        $message = "Produk berhasil diperbarui!";
                        $message_type = 'success';
                        // Refresh product data
                        $product_query->execute();
                        $product = $product_query->get_result()->fetch_assoc();
                    } else {
                        $message = "Gagal memperbarui produk: " . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>Edit Produk</h3>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Produk</label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="<?php echo htmlspecialchars($product['name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="description" name="description" rows="3"
                                required><?php echo htmlspecialchars($product['description']); ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Harga (Rp)</label>
                                <input type="text" class="form-control" id="price" name="price"
                                    value="<?php echo number_format($product['price'], 0, ',', '.'); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="stock" class="form-label">Stok</label>
                                <input type="number" class="form-control" id="stock" name="stock"
                                    value="<?php echo $product['stock']; ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Gambar Produk</label>
                            <?php if ($product['image']): ?>
                                <div class="mb-2">
                                    <img src="../assets/uploads/<?php echo htmlspecialchars($product['image']); ?>"
                                        alt="Current Image" style="max-width: 200px; height: auto;">
                                    <p class="text-muted small">Gambar saat ini</p>
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <small class="text-muted">Biarkan kosong jika tidak ingin mengubah gambar. Maksimal
                                5MB.</small>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Produk</button>
                        <a href="products.php" class="btn btn-secondary">Kembali</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
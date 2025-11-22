<?php
$currentPage = 'products';
$pageTitle = 'Tambah Produk';
require_once 'templates/header.php';
require_once '../config/utils/FileUploadValidator.php';
require_once '../config/utils/ImageProcessor.php';

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = 'danger'; // 'danger' or 'success'

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = str_replace(['.', ','], '', $_POST['price'] ?? '0');
        $price = (float) $price;
        $stock = (int) ($_POST['stock'] ?? 0);
        $category_id = isset($_POST['category_id']) && !empty($_POST['category_id']) ? (int) $_POST['category_id'] : NULL;
        $image = '';

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
                        $image = $new_filename;
                        // Generate thumbnail (silent failure allowed)
                        $thumb_dir = "../assets/uploads/thumbs";
                        if (!is_dir($thumb_dir)) {
                            @mkdir($thumb_dir, 0755, true);
                        }
                        $thumb_path = $thumb_dir . '/' . $new_filename;
                        $thumb_result = ImageProcessor::generateSquareThumbnail($target_file, $thumb_path, 400);
                        if (!$thumb_result['success']) {
                            // Append non-blocking warning
                            $message = empty($message) ? 'Thumbnail gagal dibuat: ' . htmlspecialchars($thumb_result['message']) : $message;
                        }
                    }
                }
            }

            // Insert product if no image errors
            if (empty($message)) {
                $stmt = $conn->prepare("INSERT INTO products (user_id, name, description, price, stock, category_id, image) VALUES (?, ?, ?, ?, ?, ?, ?)");

                if (!$stmt) {
                    $message = "Kesalahan database: " . $conn->error;
                } else {
                    // Bind: user_id(i) name(s) description(s) price(d) stock(i) category_id(i/null -> i) image(s)
                    $stmt->bind_param("issdiis", $user_id, $name, $description, $price, $stock, $category_id, $image);

                    if ($stmt->execute()) {
                        $message = "Produk berhasil ditambahkan!";
                        $message_type = 'success';
                        // Clear form
                        $name = $description = $_POST['price'] = $_POST['stock'] = '';
                    } else {
                        $message = "Gagal menambahkan produk: " . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Get categories for select dropdown
$categories = null;
$categories_query = $conn->prepare("SELECT id, name FROM categories WHERE user_id = ? AND is_active = 1 ORDER BY name");
if ($categories_query) {
    $categories_query->bind_param("i", $user_id);
    $categories_query->execute();
    $categories = $categories_query->get_result();
}
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>Tambah Produk Baru</h3>
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
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="description" name="description" rows="3"
                                required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Harga (Rp)</label>
                                <input type="text" class="form-control" id="price" name="price"
                                    placeholder="Contoh: 10000 atau 10.000" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="stock" class="form-label">Stok</label>
                                <input type="number" class="form-control" id="stock" name="stock" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Kategori</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">-- Pilih Kategori --</option>
                                <?php if ($categories && $categories->num_rows > 0): ?>
                                    <?php while ($cat = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                            <small class="text-muted"><a href="categories.php" target="_blank">Kelola
                                    kategori</a></small>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Gambar Produk</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <small class="text-muted">Format: JPG, PNG, GIF. Maksimal 5MB.</small>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan Produk</button>
                        <a href="products.php" class="btn btn-secondary">Kembali</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
<?php
$currentPage = 'categories';
$pageTitle = 'Manajemen Kategori';
require_once 'templates/header.php';

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = 'danger';

// Handle delete
if (isset($_GET['delete'])) {
    try {
        $category_id = (int) $_GET['delete'];
        $delete_stmt = $conn->prepare("DELETE FROM categories WHERE id = ? AND user_id = ?");
        if ($delete_stmt) {
            $delete_stmt->bind_param("ii", $category_id, $user_id);
            $delete_stmt->execute();
            $delete_stmt->close();
        }
        header("Location: categories.php");
        exit();
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Handle add/update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $category_id = isset($_POST['category_id']) ? (int) $_POST['category_id'] : 0;
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $color = trim($_POST['color'] ?? '#007bff');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        // Validate input
        if (empty($name)) {
            $message = "Nama kategori harus diisi.";
        } else {
            // Generate slug
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

            if ($category_id > 0) {
                // Update existing
                $stmt = $conn->prepare("UPDATE categories SET name = ?, slug = ?, description = ?, color = ?, is_active = ? WHERE id = ? AND user_id = ?");
                if ($stmt) {
                    $stmt->bind_param("sssssii", $name, $slug, $description, $color, $is_active, $category_id, $user_id);
                    if ($stmt->execute()) {
                        $message = "Kategori berhasil diperbarui!";
                        $message_type = 'success';
                    } else {
                        $message = "Gagal memperbarui kategori.";
                    }
                    $stmt->close();
                }
            } else {
                // Insert new
                $stmt = $conn->prepare("INSERT INTO categories (user_id, name, slug, description, color, is_active) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("issssi", $user_id, $name, $slug, $description, $color, $is_active);
                    if ($stmt->execute()) {
                        $message = "Kategori berhasil ditambahkan!";
                        $message_type = 'success';
                    } else {
                        $message = "Gagal menambahkan kategori.";
                    }
                    $stmt->close();
                }
            }
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Get categories
$categories_query = $conn->prepare("SELECT * FROM categories WHERE user_id = ? ORDER BY created_at DESC");
if ($categories_query) {
    $categories_query->bind_param("i", $user_id);
    $categories_query->execute();
    $categories = $categories_query->get_result();
} else {
    $categories = null;
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manajemen Kategori</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
            <i class="fas fa-plus"></i> Tambah Kategori
        </button>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <?php if ($categories && $categories->num_rows > 0): ?>
            <?php while ($category = $categories->fetch_assoc()): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header"
                            style="background-color: <?php echo htmlspecialchars($category['color']); ?>; color: white;">
                            <h5 class="mb-0"><?php echo htmlspecialchars($category['name']); ?></h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text"><?php echo htmlspecialchars(substr($category['description'], 0, 100)); ?></p>
                            <p class="text-muted small">Slug: <code><?php echo htmlspecialchars($category['slug']); ?></code>
                            </p>
                            <span class="badge bg-<?php echo $category['is_active'] ? 'success' : 'secondary'; ?>">
                                <?php echo $category['is_active'] ? 'Aktif' : 'Tidak Aktif'; ?>
                            </span>
                        </div>
                        <div class="card-footer bg-light">
                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#categoryModal"
                                onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <a href="?delete=<?php echo $category['id']; ?>" class="btn btn-sm btn-danger"
                                onclick="return confirm('Yakin hapus kategori ini?')">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    Belum ada kategori. <a href="#"
                        onclick="document.querySelector('[data-bs-target=\'#categoryModal\']').click()">Tambah kategori
                        sekarang</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalLabel">Tambah/Edit Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" id="category_id" name="category_id" value="0">

                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Kategori</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="color" class="form-label">Warna</label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color" id="color" name="color"
                                value="#007bff">
                            <span class="input-group-text" id="colorPreview"
                                style="background-color: #007bff; width: 50px;"></span>
                        </div>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                        <label class="form-check-label" for="is_active">
                            Kategori Aktif
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editCategory(category) {
        document.getElementById('category_id').value = category.id;
        document.getElementById('name').value = category.name;
        document.getElementById('description').value = category.description;
        document.getElementById('color').value = category.color;
        document.getElementById('is_active').checked = category.is_active == 1;
        document.getElementById('colorPreview').style.backgroundColor = category.color;
        document.querySelector('#categoryModalLabel').textContent = 'Edit Kategori: ' + category.name;
    }

    // Update color preview
    document.getElementById('color').addEventListener('change', function () {
        document.getElementById('colorPreview').style.backgroundColor = this.value;
    });

    // Reset form when modal is closed
    document.getElementById('categoryModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('category_id').value = 0;
        document.querySelector('form').reset();
        document.getElementById('color').value = '#007bff';
        document.getElementById('colorPreview').style.backgroundColor = '#007bff';
        document.querySelector('#categoryModalLabel').textContent = 'Tambah/Edit Kategori';
    });
</script>

<?php
require_once 'templates/footer.php';
?>
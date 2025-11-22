<?php
// Rebuild bersih file categories.php (memperbaiki korup kode & warning)
$currentPage = 'categories';
$pageTitle = 'Manajemen Kategori';
require_once 'templates/header.php'; // menyediakan $conn dan session

$message = '';
$message_type = 'danger';
$debug = isset($_GET['debug']);

// Flash message retrieval (PRG)
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    $message_type = $_SESSION['flash_type'] ?? 'info';
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
}

// CSRF helpers
function get_csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function verify_csrf_token($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
function flash($msg, $type = 'info')
{
    $_SESSION['flash_message'] = $msg;
    $_SESSION['flash_type'] = $type;
}

// Slug unik global (kolom slug UNIQUE)
function generate_unique_slug($conn, $base_slug, $exclude_id = 0)
{
    $base_slug = $base_slug ?: 'kategori';
    $slug = $base_slug;
    $i = 1;
    $stmt = $conn->prepare("SELECT id FROM categories WHERE slug = ? AND id <> ? LIMIT 1");
    if (!$stmt)
        return $slug;
    while (true) {
        $stmt->bind_param("si", $slug, $exclude_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 0) {
            $stmt->close();
            return $slug;
        }
        $slug = $base_slug . '-' . $i;
        $i++;
    }
}

// Cek eksistensi tabel
$table_exists = false;
$table_check = $conn->query("SHOW TABLES LIKE 'categories'");
if ($table_check && $table_check->num_rows > 0) {
    $table_exists = true;
} else {
    if (!$message) {
        $message = "Tabel categories belum ada. Jalankan migration dulu.";
        $message_type = 'warning';
    }
}

// Handle DELETE
if ($table_exists && isset($_GET['delete'])) {
    $category_id = (int) $_GET['delete'];
    $token = $_GET['token'] ?? '';
    if (!verify_csrf_token($token)) {
        flash('Token tidak valid.', 'danger');
        header('Location: categories.php');
        exit();
    }
    // Cek relasi produk
    $cp = $conn->prepare("SELECT COUNT(*) cnt FROM products WHERE category_id = ? AND user_id = ?");
    if ($cp) {
        $cp->bind_param('ii', $category_id, $_SESSION['user_id']);
        $cp->execute();
        $r = $cp->get_result()->fetch_assoc();
        $cp->close();
        if ($r['cnt'] > 0) {
            flash('Kategori dipakai produk dan tidak dapat dihapus.', 'warning');
            header('Location: categories.php');
            exit();
        }
    }
    $del = $conn->prepare("DELETE FROM categories WHERE id = ? AND user_id = ?");
    if ($del) {
        $del->bind_param('ii', $category_id, $_SESSION['user_id']);
        if ($del->execute())
            flash('Kategori berhasil dihapus.', 'success');
        else
            flash('Gagal menghapus kategori: ' . $del->error, 'danger');
        $del->close();
    }
    header('Location: categories.php');
    exit();
}

// Handle INSERT/UPDATE
if ($table_exists && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = (int) ($_POST['category_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $color = trim($_POST['color'] ?? '#007bff');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // CSRF
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $message = 'Token CSRF tidak valid.';
        $message_type = 'danger';
    }

    // Validasi dasar
    if (!$message) {
        if ($name === '') {
            $message = 'Nama kategori harus diisi.';
        } elseif (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            $message = 'Format warna tidak valid.';
        }
    }

    if (!$message) {
        // Generate slug
        $base_slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $name));
        $slug = generate_unique_slug($conn, $base_slug, $category_id);

        if ($category_id > 0) {
            $stmt = $conn->prepare("UPDATE categories SET name = ?, slug = ?, description = ?, color = ?, is_active = ? WHERE id = ? AND user_id = ?");
            if ($stmt) {
                $stmt->bind_param('ssssiii', $name, $slug, $description, $color, $is_active, $category_id, $_SESSION['user_id']);
                if ($stmt->execute()) {
                    if ($debug) {
                        $message = 'Kategori berhasil diperbarui (debug).';
                        $message_type = 'success';
                    } else {
                        flash('Kategori berhasil diperbarui!', 'success');
                        header('Location: categories.php');
                        exit();
                    }
                } else {
                    $message = 'Gagal memperbarui kategori: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                $message = 'Prepare UPDATE gagal: ' . $conn->error;
            }
        } else {
            $stmt = $conn->prepare("INSERT INTO categories (user_id, name, slug, description, color, is_active) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param('issssi', $_SESSION['user_id'], $name, $slug, $description, $color, $is_active);
                if ($stmt->execute()) {
                    if ($debug) {
                        $message = 'Kategori berhasil ditambahkan (debug).';
                        $message_type = 'success';
                    } else {
                        flash('Kategori berhasil ditambahkan!', 'success');
                        header('Location: categories.php');
                        exit();
                    }
                } else {
                    $message = 'Gagal menambahkan kategori: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                $message = 'Prepare INSERT gagal: ' . $conn->error;
            }
        }
    }
}

// Ambil daftar kategori
$categories = null;
if ($table_exists) {
    $q = $conn->prepare('SELECT * FROM categories WHERE user_id = ? ORDER BY created_at DESC');
    if ($q) {
        $q->bind_param('i', $_SESSION['user_id']);
        if ($q->execute()) {
            $categories = $q->get_result();
        } else if (!$message) {
            $message = 'Query kategori gagal: ' . $q->error;
            $message_type = 'danger';
        }
        $q->close();
    } else if (!$message) {
        $message = 'Prepare query kategori gagal: ' . $conn->error;
        $message_type = 'danger';
    }
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
            <?php if (!$table_exists): ?>
                <div class="mt-2">
                    <a href="run_migration.php" class="btn btn-sm btn-outline-primary">Jalankan Migration Categories</a>
                </div>
            <?php endif; ?>
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
                            <a href="?delete=<?php echo $category['id']; ?>&token=<?php echo urlencode(get_csrf_token()); ?>"
                                class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus kategori ini?')">
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
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(get_csrf_token()); ?>">

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
        const form = document.querySelector('#categoryModal form');
        form.reset();
        document.getElementById('category_id').value = 0;
        document.getElementById('color').value = '#007bff';
        document.getElementById('colorPreview').style.backgroundColor = '#007bff';
        document.querySelector('#categoryModalLabel').textContent = 'Tambah/Edit Kategori';
    });
</script>

<?php
require_once 'templates/footer.php';
?>
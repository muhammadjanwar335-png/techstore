<?php
/**
 * TECHSTORE - Edit Kategori Produk (Admin)
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

check_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . BASE_URL . 'admin/categories/index.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $cat = $stmt->fetch();

    if (!$cat) {
        set_flash('danger', 'Kategori tidak ditemukan.');
        header('Location: ' . BASE_URL . 'admin/categories/index.php');
        exit;
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $icon = sanitize($_POST['icon'] ?? 'bi-tag');
    $description = sanitize($_POST['description'] ?? '');

    if (empty($name)) {
        $error = 'Nama kategori wajib diisi!';
    } else {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        try {
            $stmt_up = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, description = ?, icon = ? WHERE id = ?");
            $stmt_up->execute([$name, $slug, $description, $icon, $id]);

            set_flash('success', "Kategori <strong>{$name}</strong> berhasil diperbarui!");
            header('Location: ' . BASE_URL . 'admin/categories/index.php');
            exit;
        } catch (Exception $e) {
            $error = 'Gagal menyimpan kategori: ' . $e->getMessage();
        }
    }
}

$page_title = 'Edit Kategori ' . $cat['name'] . ' - TechStore Admin';
include __DIR__ . '/../../includes/admin_header.php';
include __DIR__ . '/../../includes/admin_sidebar.php';
include __DIR__ . '/../../includes/admin_topbar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Kategori</h3>
        <p class="text-muted small mb-0">Ubah nama, slug, ikon, dan deskripsi kategori produk.</p>
    </div>
    <a href="<?= BASE_URL ?>admin/categories/index.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger d-flex align-items-center small py-2 mb-4">
        <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
        <div><?= htmlspecialchars($error) ?></div>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 p-4 p-md-5" style="max-width: 650px;">
    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label small fw-semibold text-secondary">Nama Kategori</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($cat['name']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-semibold text-secondary">Icon Bootstrap</label>
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi <?= htmlspecialchars($cat['icon']) ?>" id="previewIcon"></i></span>
                <input type="text" name="icon" class="form-control" value="<?= htmlspecialchars($cat['icon']) ?>" oninput="document.getElementById('previewIcon').className = 'bi ' + this.value" required>
            </div>
            <small class="text-muted" style="font-size: 0.72rem;">Contoh: bi-keyboard, bi-mouse, bi-headphones, bi-display, bi-laptop</small>
        </div>

        <div class="mb-4">
            <label class="form-label small fw-semibold text-secondary">Deskripsi Kategori</label>
            <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($cat['description']) ?></textarea>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="<?= BASE_URL ?>admin/categories/index.php" class="btn btn-light border">Batal</a>
            <button type="submit" class="btn btn-primary fw-bold">
                <i class="bi bi-save me-1"></i>Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/admin_footer.php'; ?>

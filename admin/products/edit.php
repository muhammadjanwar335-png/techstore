<?php
/**
 * TECHSTORE - Edit Produk (Admin)
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

check_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . BASE_URL . 'admin/products/index.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if (!$product) {
        set_flash('danger', 'Produk tidak ditemukan.');
        header('Location: ' . BASE_URL . 'admin/products/index.php');
        exit;
    }

    $categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = (int)($_POST['category_id'] ?? 0);
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $weight = (int)($_POST['weight'] ?? 500);
    $status = sanitize($_POST['status'] ?? 'active');

    if (empty($name) || $category_id <= 0 || $price <= 0 || empty($description)) {
        $error = 'Semua kolom bertanda wajib harus diisi!';
    } else {
        $image_name = $product['image'];
        // Jika ada upload gambar baru
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../assets/uploads/products/';
            $uploadResult = upload_image($_FILES['product_image'], $uploadDir, 'prod_');
            if ($uploadResult['status']) {
                $image_name = $uploadResult['filename'];
            } else {
                $error = 'Gagal upload: ' . $uploadResult['message'];
            }
        }

        if (empty($error)) {
            try {
                $stmt_up = $pdo->prepare("
                    UPDATE products 
                    SET category_id = ?, name = ?, description = ?, price = ?, stock = ?, weight = ?, image = ?, status = ?
                    WHERE id = ?
                ");
                $stmt_up->execute([
                    $category_id, $name, $description, $price, $stock, $weight, $image_name, $status, $id
                ]);

                set_flash('success', "Data produk <strong>{$name}</strong> berhasil diperbarui!");
                header('Location: ' . BASE_URL . 'admin/products/index.php');
                exit;
            } catch (Exception $e) {
                $error = 'Gagal menyimpan perubahan: ' . $e->getMessage();
            }
        }
    }
}

$page_title = 'Edit Produk ' . $product['name'] . ' - TechStore Admin';
include __DIR__ . '/../../includes/admin_header.php';
include __DIR__ . '/../../includes/admin_sidebar.php';
include __DIR__ . '/../../includes/admin_topbar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Data Produk</h3>
        <p class="text-muted small mb-0">Ubah spesifikasi, harga jual, foto, dan persediaan stok barang.</p>
    </div>
    <a href="<?= BASE_URL ?>admin/products/index.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger d-flex align-items-center small py-2 mb-4">
        <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
        <div><?= htmlspecialchars($error) ?></div>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="row g-4">
            <div class="col-md-8">
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-secondary">Nama Produk / Aksesoris</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-secondary">Kategori</label>
                        <select name="category_id" class="form-select" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-secondary">Harga Jual (Rp)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">Rp</span>
                            <input type="number" name="price" class="form-control" value="<?= (int)$product['price'] ?>" min="1000" step="500" required>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-secondary">Jumlah Stok</label>
                        <input type="number" name="stock" class="form-control" value="<?= (int)$product['stock'] ?>" min="0" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-secondary">Berat (gram)</label>
                        <input type="number" name="weight" class="form-control" value="<?= (int)$product['weight'] ?>" min="1" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-secondary">Status Tayang</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= $product['status'] === 'active' ? 'selected' : '' ?>>Aktif (Dijual)</option>
                            <option value="inactive" <?= $product['status'] === 'inactive' ? 'selected' : '' ?>>Nonaktif (Disembunyikan)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small text-secondary">Deskripsi & Spesifikasi Lengkap</label>
                    <textarea name="description" class="form-control" rows="6" required><?= htmlspecialchars($product['description']) ?></textarea>
                </div>
            </div>

            <!-- Upload Gambar Baru -->
            <div class="col-md-4">
                <div class="border rounded-4 p-3 bg-light text-center h-100 d-flex flex-column justify-content-between">
                    <div>
                        <h6 class="fw-bold mb-2">Foto Produk Saat Ini</h6>
                        <div class="mb-3">
                            <img id="productImagePreview" src="<?= get_product_image_url($product['image']) ?>" alt="Foto Produk" class="img-fluid rounded-3 border bg-white" style="max-height: 200px; width: 100%; object-fit: contain;">
                        </div>

                        <label class="form-label small fw-semibold text-secondary">Ganti Foto Baru (Opsional)</label>
                        <input type="file" name="product_image" class="form-control form-control-sm input-image-preview" data-preview-target="productImagePreview" accept="image/jpeg,image/png,image/webp">
                    </div>

                    <div class="mt-4 pt-3 border-top d-grid gap-2">
                        <button type="submit" class="btn btn-primary fw-bold py-2">
                            <i class="bi bi-save me-1"></i>Simpan Perubahan
                        </button>
                        <a href="<?= BASE_URL ?>admin/products/index.php" class="btn btn-light border py-2">Batal</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/admin_footer.php'; ?>

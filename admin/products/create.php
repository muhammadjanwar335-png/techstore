<?php
/**
 * TECHSTORE - Tambah Produk Baru (Admin)
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

check_admin();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
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
        $error = 'Nama produk, kategori, deskripsi, dan harga jual wajib diisi secara lengkap!';
    } else {
        // Buat slug otomatis
        $base_slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $slug = $base_slug . '-' . substr(uniqid(), -4);

        // Upload Gambar jika ada
        $image_name = 'default.jpg';
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../assets/uploads/products/';
            $uploadResult = upload_image($_FILES['product_image'], $uploadDir, 'prod_');
            if ($uploadResult['status']) {
                $image_name = $uploadResult['filename'];
            } else {
                $error = 'Gagal upload gambar: ' . $uploadResult['message'];
            }
        }

        if (empty($error)) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO products (category_id, name, slug, description, price, stock, weight, image, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $category_id, $name, $slug, $description, $price, $stock, $weight, $image_name, $status
                ]);

                set_flash('success', "Produk <strong>{$name}</strong> berhasil ditambahkan ke katalog!");
                header('Location: ' . BASE_URL . 'admin/products/index.php');
                exit;
            } catch (Exception $e) {
                $error = 'Terjadi kesalahan sistem saat menyimpan: ' . $e->getMessage();
            }
        }
    }
}

$page_title = 'Tambah Produk Baru - TechStore Admin';
include __DIR__ . '/../../includes/admin_header.php';
include __DIR__ . '/../../includes/admin_sidebar.php';
include __DIR__ . '/../../includes/admin_topbar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-plus-circle text-primary me-2"></i>Tambah Produk Baru</h3>
        <p class="text-muted small mb-0">Lengkapi formulir untuk mempublikasikan perlengkapan komputer ke katalog.</p>
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
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Mechanical Keyboard RGB Wireless" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-secondary">Kategori</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-secondary">Harga Jual (Rp)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">Rp</span>
                            <input type="number" name="price" class="form-control" placeholder="Contoh: 1450000" min="1000" step="500" required>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-secondary">Jumlah Stok</label>
                        <input type="number" name="stock" class="form-control" value="10" min="0" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-secondary">Berat (gram)</label>
                        <input type="number" name="weight" class="form-control" value="500" min="1" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-secondary">Status Tayang</label>
                        <select name="status" class="form-select">
                            <option value="active">Aktif (Dijual)</option>
                            <option value="inactive">Nonaktif (Disembunyikan)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small text-secondary">Deskripsi & Spesifikasi Lengkap</label>
                    <textarea name="description" class="form-control" rows="6" placeholder="Ketikkan spesifikasi teknis, fitur unggulan, kelengkapan dalam paket, dan ketentuan garansi..." required></textarea>
                </div>
            </div>

            <!-- Upload Gambar Produk -->
            <div class="col-md-4">
                <div class="border rounded-4 p-3 bg-light text-center h-100 d-flex flex-column justify-content-between">
                    <div>
                        <h6 class="fw-bold mb-2">Foto Produk</h6>
                        <p class="small text-muted mb-3">Unggah foto berkualitas format JPG, PNG, atau WebP (Maks. 2MB)</p>
                        
                        <div class="mb-3">
                            <img id="productImagePreview" src="https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=600&auto=format&fit=crop&q=80" alt="Preview Foto" class="img-fluid rounded-3 border bg-white" style="max-height: 200px; width: 100%; object-fit: contain;">
                        </div>

                        <input type="file" name="product_image" class="form-control form-control-sm input-image-preview" data-preview-target="productImagePreview" accept="image/jpeg,image/png,image/webp">
                    </div>

                    <div class="mt-4 pt-3 border-top d-grid gap-2">
                        <button type="submit" class="btn btn-primary fw-bold py-2">
                            <i class="bi bi-save me-1"></i>Simpan Produk
                        </button>
                        <a href="<?= BASE_URL ?>admin/products/index.php" class="btn btn-light border py-2">Batal</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/admin_footer.php'; ?>

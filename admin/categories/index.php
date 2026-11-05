<?php
/**
 * TECHSTORE - Manajemen Kategori Produk (Admin)
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

check_admin();

try {
    $stmt = $pdo->query("
        SELECT c.*, COUNT(p.id) AS total_products
        FROM categories c
        LEFT JOIN products p ON c.id = p.category_id
        GROUP BY c.id
        ORDER BY c.id ASC
    ");
    $categories = $stmt->fetchAll();
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

$page_title = 'Kelola Kategori Produk - TechStore Admin';
include __DIR__ . '/../../includes/admin_header.php';
include __DIR__ . '/../../includes/admin_sidebar.php';
include __DIR__ . '/../../includes/admin_topbar.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-tags text-primary me-2"></i>Kategori Produk</h3>
        <p class="text-muted small mb-0">Kelola kelompok aksesoris komputer (Keyboard, Mouse, Headset, Monitor, dll).</p>
    </div>
    <div>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="bi bi-plus-lg me-1"></i>Tambah Kategori Baru
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-custom align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th style="width: 60px;">Ikon</th>
                    <th>Nama Kategori</th>
                    <th>Slug URL</th>
                    <th>Deskripsi</th>
                    <th>Jumlah Produk</th>
                    <th class="text-end" style="width: 120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td class="text-muted">#<?= $cat['id'] ?></td>
                        <td>
                            <div class="bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; font-size: 1.2rem;">
                                <i class="bi <?= htmlspecialchars($cat['icon']) ?>"></i>
                            </div>
                        </td>
                        <td class="fw-bold text-dark">
                            <?= htmlspecialchars($cat['name']) ?>
                        </td>
                        <td class="font-monospace small text-muted">
                            <?= htmlspecialchars($cat['slug']) ?>
                        </td>
                        <td class="small text-muted text-truncate" style="max-width: 300px;">
                            <?= htmlspecialchars($cat['description']) ?>
                        </td>
                        <td>
                            <span class="badge bg-light text-primary border fw-bold"><?= (int)$cat['total_products'] ?> Produk</span>
                        </td>
                        <td class="text-end">
                            <a href="<?= BASE_URL ?>admin/categories/edit.php?id=<?= $cat['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <a href="<?= BASE_URL ?>admin/categories/delete.php?id=<?= $cat['id'] ?>" class="btn btn-sm btn-outline-danger btn-confirm-delete" data-item-name="kategori <?= htmlspecialchars($cat['name']) ?>" title="Hapus">
                                <i class="bi bi-trash3-fill"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Kategori -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom p-4">
                <h5 class="modal-title fw-bold" id="addCatModalLabel"><i class="bi bi-tag-fill text-primary me-2"></i>Tambah Kategori Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>admin/categories/create.php" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Nama Kategori</label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Graphic Tablet" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Icon Bootstrap</label>
                        <input type="text" name="icon" class="form-control" value="bi-tag" placeholder="Contoh: bi-mouse, bi-keyboard" required>
                        <small class="text-muted" style="font-size: 0.72rem;">Gunakan class ikon dari Bootstrap Icons (cth: bi-keyboard, bi-mouse, bi-laptop).</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Deskripsi Singkat</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Penjelasan singkat kategori..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top p-3">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold">Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/admin_footer.php'; ?>

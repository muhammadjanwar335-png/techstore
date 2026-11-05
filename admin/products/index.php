<?php
/**
 * TECHSTORE - Manajemen Data Produk (Admin)
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

check_admin();

$search = sanitize($_GET['q'] ?? '');
$cat_id = (int)($_GET['category'] ?? 0);
$filter_low = sanitize($_GET['filter'] ?? '');

$where = ["1=1"];
$params = [];

if (!empty($search)) {
    $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if ($cat_id > 0) {
    $where[] = "p.category_id = ?";
    $params[] = $cat_id;
}

if ($filter_low === 'low_stock') {
    $where[] = "p.stock < 5";
}

$sql = "
    SELECT p.*, c.name AS category_name
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE " . implode(' AND ', $where) . "
    ORDER BY p.id DESC
";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    $categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

$page_title = 'Kelola Data Produk - TechStore Admin';
include __DIR__ . '/../../includes/admin_header.php';
include __DIR__ . '/../../includes/admin_sidebar.php';
include __DIR__ . '/../../includes/admin_topbar.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-box-seam text-primary me-2"></i>Manajemen Produk</h3>
        <p class="text-muted small mb-0">Kelola inventaris hardware, aksesoris komputer, stok barang, dan harga jual.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>admin/products/create.php" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Tambah Produk Baru
        </a>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
    <form method="GET" action="" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="q" class="form-control bg-light border-start-0" placeholder="Cari nama produk..." value="<?= htmlspecialchars($search) ?>">
            </div>
        </div>

        <div class="col-md-4">
            <select name="category" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
                <option value="0">-- Semua Kategori --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $cat_id == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm flex-fill">Terapkan</button>
            <?php if (!empty($search) || $cat_id > 0 || !empty($filter_low)): ?>
                <a href="<?= BASE_URL ?>admin/products/index.php" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Product Table -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-custom align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Produk</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Status</th>
                    <th class="text-end" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            Tidak ada produk yang sesuai dengan kriteria filter.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($products as $p): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="<?= get_product_image_url($p['image'], $p['category_name']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="rounded-3 border" style="width: 48px; height: 48px; object-fit: cover;">
                                    <div>
                                        <div class="fw-bold text-dark text-truncate" style="max-width: 280px;"><?= htmlspecialchars($p['name']) ?></div>
                                        <small class="text-muted">Berat: <?= (int)$p['weight'] ?> gram</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($p['category_name']) ?></span>
                            </td>
                            <td class="fw-bold text-primary">
                                <?= format_rupiah($p['price']) ?>
                            </td>
                            <td>
                                <?php if ($p['stock'] <= 0): ?>
                                    <span class="badge bg-danger">Habis (0)</span>
                                <?php elseif ($p['stock'] < 5): ?>
                                    <span class="badge bg-warning text-dark">Sisa <?= $p['stock'] ?> unit</span>
                                <?php else: ?>
                                    <span class="badge bg-success"><?= $p['stock'] ?> unit</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($p['status'] === 'active'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>product-detail.php?id=<?= $p['id'] ?>" target="_blank" class="btn btn-sm btn-light border" title="Lihat di Toko">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                                <a href="<?= BASE_URL ?>admin/products/edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a href="<?= BASE_URL ?>admin/products/delete.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger btn-confirm-delete" data-item-name="<?= htmlspecialchars($p['name']) ?>" title="Hapus">
                                    <i class="bi bi-trash3-fill"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../includes/admin_footer.php'; ?>

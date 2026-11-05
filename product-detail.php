<?php
/**
 * TECHSTORE - Halaman Detail Produk
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: ' . BASE_URL . 'customer/products.php');
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT p.*, c.name AS category_name, c.id AS cat_id
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.id = ? AND p.status = 'active'
        LIMIT 1
    ");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if (!$product) {
        set_flash('danger', 'Produk tidak ditemukan atau telah dinonaktifkan.');
        header('Location: ' . BASE_URL . 'customer/products.php');
        exit;
    }

    // Ambil produk terkait dari kategori yang sama
    $stmt_related = $pdo->prepare("
        SELECT p.*, c.name AS category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.category_id = ? AND p.id != ? AND p.status = 'active'
        LIMIT 4
    ");
    $stmt_related->execute([$product['category_id'], $product['id']]);
    $related_products = $stmt_related->fetchAll();

} catch (Exception $e) {
    die("Terjadi kesalahan: " . $e->getMessage());
}

$page_title = $product['name'] . ' - TechStore';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php" class="text-decoration-none text-muted">Beranda</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>customer/products.php" class="text-decoration-none text-muted">Katalog</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>customer/products.php?category=<?= $product['cat_id'] ?>" class="text-decoration-none text-muted"><?= htmlspecialchars($product['category_name']) ?></a></li>
            <li class="breadcrumb-item active text-truncate" style="max-width: 250px;" aria-current="page"><?= htmlspecialchars($product['name']) ?></li>
        </ol>
    </nav>

    <!-- Product Details Main Grid -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
        <div class="card-body p-4 p-lg-5">
            <div class="row g-5">
                <!-- Kolom Gambar -->
                <div class="col-lg-5">
                    <div class="position-relative bg-light rounded-4 overflow-hidden border p-3 text-center">
                        <img src="<?= get_product_image_url($product['image'], $product['category_name']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="img-fluid rounded-3" style="max-height: 420px; object-fit: contain;">
                        <span class="position-absolute top-0 start-0 m-3 badge bg-dark bg-opacity-75 px-3 py-2 rounded-pill">
                            <?= htmlspecialchars($product['category_name']) ?>
                        </span>
                    </div>
                </div>

                <!-- Kolom Informasi Produk & Form Checkout -->
                <div class="col-lg-7">
                    <div class="mb-2">
                        <span class="text-primary fw-bold text-uppercase small"><i class="bi bi-tag-fill me-1"></i><?= htmlspecialchars($product['category_name']) ?></span>
                    </div>
                    <h2 class="fw-bold mb-3"><?= htmlspecialchars($product['name']) ?></h2>

                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="fs-2 fw-extrabold text-primary">
                            <?= format_rupiah($product['price']) ?>
                        </div>
                        <div>
                            <?php if ($product['stock'] > 5): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">
                                    <i class="bi bi-check-circle-fill me-1"></i>Stok Tersedia (<?= $product['stock'] ?> unit)
                                </span>
                            <?php elseif ($product['stock'] > 0): ?>
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2 rounded-pill">
                                    <i class="bi bi-exclamation-circle-fill me-1"></i>Stok Menipis (Tersisa <?= $product['stock'] ?> unit)
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill">
                                    <i class="bi bi-x-circle-fill me-1"></i>Stok Habis
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr class="my-4 text-muted">

                    <h5 class="fw-bold mb-2">Deskripsi Produk</h5>
                    <div class="text-secondary leading-relaxed mb-4" style="white-space: pre-line;">
                        <?= htmlspecialchars($product['description']) ?>
                    </div>

                    <div class="row g-3 mb-4 text-secondary small">
                        <div class="col-sm-6">
                            <div class="p-2 border rounded bg-light">
                                <i class="bi bi-box-seam text-primary me-2"></i><strong>Berat Pengiriman:</strong> <?= (int)$product['weight'] ?> gram
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-2 border rounded bg-light">
                                <i class="bi bi-shield-check text-primary me-2"></i><strong>Garansi:</strong> 1 Tahun Resmi Distributor
                            </div>
                        </div>
                    </div>

                    <!-- Form Tambah ke Keranjang -->
                    <?php if ($product['stock'] > 0): ?>
                        <form action="<?= BASE_URL ?>customer/cart.php" method="POST" class="mt-4">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            
                            <div class="d-flex align-items-center gap-3 mb-4">
                                <label class="fw-bold text-dark mb-0">Jumlah:</label>
                                <div class="input-group" style="width: 140px;">
                                    <button class="btn btn-outline-secondary" type="button" onclick="let input = document.getElementById('qtyInput'); if(input.value > 1) input.value--;">-</button>
                                    <input type="number" id="qtyInput" name="quantity" class="form-control text-center" value="1" min="1" max="<?= $product['stock'] ?>">
                                    <button class="btn btn-outline-secondary" type="button" onclick="let input = document.getElementById('qtyInput'); if(parseInt(input.value) < <?= $product['stock'] ?>) input.value++;">+</button>
                                </div>
                                <small class="text-muted">Maksimal <?= $product['stock'] ?> unit</small>
                            </div>

                            <div class="d-flex gap-3">
                                <button type="submit" class="btn btn-primary btn-lg px-4 flex-grow-1 shadow-sm">
                                    <i class="bi bi-cart-plus me-2"></i>Tambahkan ke Keranjang
                                </button>
                                <a href="<?= BASE_URL ?>customer/cart.php" class="btn btn-outline-dark btn-lg px-3" title="Lihat Keranjang">
                                    <i class="bi bi-cart3"></i>
                                </a>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-secondary text-center py-3">
                            <i class="bi bi-bell-slash me-2"></i>Maaf, stok barang ini sedang habis. Silakan cek produk lain atau hubungi admin.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($related_products)): ?>
        <div class="mb-5">
            <h4 class="fw-bold mb-4">Produk Terkait Lainnya</h4>
            <div class="row g-4 row-cols-1 row-cols-sm-2 row-cols-lg-4">
                <?php foreach ($related_products as $rel): ?>
                    <div class="col">
                        <div class="product-card">
                            <div class="img-wrapper">
                                <img src="<?= get_product_image_url($rel['image'], $rel['category_name']) ?>" alt="<?= htmlspecialchars($rel['name']) ?>">
                                <span class="category-badge"><?= htmlspecialchars($rel['category_name']) ?></span>
                            </div>
                            <div class="card-body">
                                <a href="<?= BASE_URL ?>product-detail.php?id=<?= $rel['id'] ?>" class="product-title" title="<?= htmlspecialchars($rel['name']) ?>">
                                    <?= htmlspecialchars($rel['name']) ?>
                                </a>
                                <div class="price-tag"><?= format_rupiah($rel['price']) ?></div>
                                <a href="<?= BASE_URL ?>product-detail.php?id=<?= $rel['id'] ?>" class="btn btn-outline-primary btn-sm mt-auto w-100">
                                    Detail Produk
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<?php
/**
 * TECHSTORE - Halaman Katalog Produk (Pencarian & Filter)
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';

// Bangun query dinamis
$where = ["p.status = 'active'"];
$params = [];

if (!empty($search)) {
    $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if ($category_filter > 0) {
    $where[] = "p.category_id = ?";
    $params[] = $category_filter;
}

$orderBy = "p.id DESC";
if ($sort === 'price_asc') {
    $orderBy = "p.price ASC";
} elseif ($sort === 'price_desc') {
    $orderBy = "p.price DESC";
} elseif ($sort === 'name_asc') {
    $orderBy = "p.name ASC";
}

$sql = "
    SELECT p.*, c.name AS category_name
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE " . implode(' AND ', $where) . "
    ORDER BY {$orderBy}
";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    // Ambil daftar seluruh kategori untuk tombol filter
    $stmt_cats = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $stmt_cats->fetchAll();
} catch (Exception $e) {
    die("Query error: " . $e->getMessage());
}

$page_title = 'Katalog Produk Komputer & Laptop - TechStore';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-4">
    <!-- Header Katalog -->
    <div class="bg-dark text-white rounded-4 p-4 p-md-5 mb-4 position-relative overflow-hidden" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
        <div class="row align-items-center">
            <div class="col-md-8">
                <span class="badge bg-primary px-3 py-2 rounded-pill mb-2">Katalog Aksesoris & Hardware</span>
                <h2 class="fw-bold mb-2">Semua Perlengkapan Komputer & Laptop</h2>
                <p class="text-secondary mb-0 small">
                    Pilih kebutuhan setup kerja dan gaming Anda dengan kualitas original serta jaminan garansi resmi.
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="text-white-50 small">Menampilkan <strong><?= count($products) ?></strong> Produk</div>
            </div>
        </div>
    </div>

    <!-- Filter & Sort Bar -->
    <div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
        <form method="GET" action="" class="row g-2 align-items-center">
            <!-- Search Keyword -->
            <div class="col-lg-4 col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control bg-light border-start-0" placeholder="Cari nama barang..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>

            <!-- Kategori Dropdown -->
            <div class="col-lg-3 col-md-3 col-6">
                <select name="category" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
                    <option value="0">-- Semua Kategori --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $category_filter == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Sorting Dropdown -->
            <div class="col-lg-3 col-md-3 col-6">
                <select name="sort" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Terbaru</option>
                    <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Harga: Terendah ke Tertinggi</option>
                    <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Harga: Tertinggi ke Terendah</option>
                    <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Nama: A - Z</option>
                </select>
            </div>

            <!-- Action Button -->
            <div class="col-lg-2 col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">Filter</button>
                <?php if (!empty($search) || $category_filter > 0 || $sort !== 'newest'): ?>
                    <a href="<?= BASE_URL ?>customer/products.php" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Category Pills Quick Scroll -->
    <div class="d-flex gap-2 overflow-x-auto pb-3 mb-4" style="white-space: nowrap;">
        <a href="<?= BASE_URL ?>customer/products.php" class="btn btn-sm <?= $category_filter == 0 ? 'btn-primary' : 'btn-light border' ?> rounded-pill px-3">
            Semua
        </a>
        <?php foreach ($categories as $c): ?>
            <a href="<?= BASE_URL ?>customer/products.php?category=<?= $c['id'] ?>" class="btn btn-sm <?= $category_filter == $c['id'] ? 'btn-primary' : 'btn-light border' ?> rounded-pill px-3">
                <i class="bi <?= htmlspecialchars($c['icon']) ?> me-1"></i><?= htmlspecialchars($c['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Product Grid -->
    <?php if (empty($products)): ?>
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center my-4">
            <div class="display-1 text-muted mb-3"><i class="bi bi-search"></i></div>
            <h4 class="fw-bold">Produk Tidak Ditemukan</h4>
            <p class="text-muted">Tidak ada aksesoris atau peripheral yang sesuai dengan kata kunci atau filter yang Anda pilih.</p>
            <div>
                <a href="<?= BASE_URL ?>customer/products.php" class="btn btn-primary btn-sm px-4">
                    Lihat Semua Produk
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4">
            <?php foreach ($products as $prod): ?>
                <div class="col">
                    <div class="product-card">
                        <div class="img-wrapper">
                            <img src="<?= get_product_image_url($prod['image'], $prod['category_name']) ?>" alt="<?= htmlspecialchars($prod['name']) ?>">
                            <span class="category-badge"><?= htmlspecialchars($prod['category_name']) ?></span>
                            <?php if ($prod['stock'] < 5): ?>
                                <span class="stock-badge bg-danger text-white">Sisa <?= $prod['stock'] ?>!</span>
                            <?php else: ?>
                                <span class="stock-badge bg-success text-white">Ready (<?= $prod['stock'] ?>)</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <a href="<?= BASE_URL ?>product-detail.php?id=<?= $prod['id'] ?>" class="product-title" title="<?= htmlspecialchars($prod['name']) ?>">
                                <?= htmlspecialchars($prod['name']) ?>
                            </a>
                            <div class="price-tag"><?= format_rupiah($prod['price']) ?></div>
                            <div class="mt-auto d-grid gap-2">
                                <a href="<?= BASE_URL ?>product-detail.php?id=<?= $prod['id'] ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-eye me-1"></i>Lihat Detail
                                </a>
                                <form action="<?= BASE_URL ?>customer/cart.php" method="POST" class="d-grid">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="bi bi-cart-plus me-1"></i>+ Keranjang
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

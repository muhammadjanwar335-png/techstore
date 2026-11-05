<?php
/**
 * TECHSTORE - Halaman Beranda (Landing Page)
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';

// Ambil 8 Kategori
try {
    $stmt_cats = $pdo->query("
        SELECT c.*, COUNT(p.id) AS total_products 
        FROM categories c
        LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
        GROUP BY c.id
        ORDER BY c.id ASC
    ");
    $categories = $stmt_cats->fetchAll();
} catch (Exception $e) {
    $categories = [];
}

// Ambil Produk Terbaru & Terlaris (8 Produk)
try {
    $stmt_featured = $pdo->query("
        SELECT p.*, c.name AS category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'active'
        ORDER BY p.id DESC
        LIMIT 8
    ");
    $featured_products = $stmt_featured->fetchAll();
} catch (Exception $e) {
    $featured_products = [];
}

$page_title = 'TechStore – Solusi Aksesoris Komputer & Laptop Terlengkap';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<!-- Hero Banner Section -->
<section class="hero-tech">
    <div class="container position-relative" style="z-index: 2;">
        <div class="row align-items-center g-5">
            <div class="col-lg-7 text-center text-lg-start">
                <span class="badge bg-primary bg-opacity-25 text-info px-3 py-2 rounded-pill mb-3 fw-semibold border border-primary border-opacity-50">
                    <i class="bi bi-patch-check-fill me-1"></i> 100% Produk Original & Bergaransi Resmi
                </span>
                <h1 class="display-4 fw-extrabold text-white mb-3 lh-sm">
                    Upgrade Setup Kerja & Gaming Impianmu di <span class="text-primary">TechStore</span>
                </h1>
                <p class="lead text-secondary mb-4 fs-6">
                    Temukan ribuan peripheral dan accessories komputer, laptop, mechanical keyboard, mouse gaming presisi, hingga penyimpanan super cepat dengan harga terbaik.
                </p>
                <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                    <a href="<?= BASE_URL ?>customer/products.php" class="btn btn-primary btn-lg px-4 py-3 shadow-lg">
                        <i class="bi bi-cart3 me-2"></i>Jelajahi Katalog
                    </a>
                    <a href="#kategori" class="btn btn-outline-light btn-lg px-4 py-3">
                        <i class="bi bi-grid me-2"></i>Lihat Kategori
                    </a>
                </div>

                <!-- Trust Badges -->
                <div class="row mt-5 pt-3 border-top border-secondary border-opacity-25 text-start g-3">
                    <div class="col-4">
                        <div class="fw-bold text-white fs-5">24+</div>
                        <div class="text-secondary small">Produk Unggulan</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold text-white fs-5">8 Kategori</div>
                        <div class="text-secondary small">Hardware & Aksesoris</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold text-white fs-5">100% Aman</div>
                        <div class="text-secondary small">Garansi Uang Kembali</div>
                    </div>
                </div>
            </div>

            <!-- Banner Visual Hero -->
            <div class="col-lg-5 text-center d-none d-lg-block">
                <div class="position-relative">
                    <img src="https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=800&auto=format&fit=crop&q=80" alt="Tech Accessories" class="img-fluid rounded-4 shadow-2xl border border-secondary border-opacity-50" style="max-height: 420px; object-fit: cover; width: 100%;">
                    
                    <!-- Floating Card Promo -->
                    <div class="position-absolute bottom-0 start-0 translate-middle-y bg-dark bg-opacity-90 backdrop-blur p-3 rounded-3 border border-secondary shadow-lg text-start ms-n3" style="max-width: 240px;">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-lightning-charge-fill text-warning fs-5"></i>
                            <span class="text-white fw-bold small">Diskon Spesial Mahasiswa</span>
                        </div>
                        <div class="text-secondary small">Gunakan promo untuk perlengkapan kuliah & skripsi!</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section Kategori Pilihan (Minimal 8 Kategori Sesuai Spesifikasi D) -->
<section class="py-5" id="kategori">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
            <div>
                <span class="text-primary fw-bold text-uppercase small letter-spacing-1">Eksplorasi Perlengkapan</span>
                <h2 class="fw-bold mb-0">8 Kategori Pilihan</h2>
            </div>
            <a href="<?= BASE_URL ?>customer/products.php" class="text-primary fw-semibold mt-2 mt-md-0">
                Lihat Semua Produk &rarr;
            </a>
        </div>

        <div class="row g-3 row-cols-2 row-cols-md-4">
            <?php foreach ($categories as $cat): ?>
                <div class="col">
                    <a href="<?= BASE_URL ?>customer/products.php?category=<?= $cat['id'] ?>" class="category-card h-100">
                        <div class="category-icon mx-auto">
                            <i class="bi <?= htmlspecialchars($cat['icon']) ?>"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($cat['name']) ?></h6>
                        <small class="text-muted"><?= (int)$cat['total_products'] ?> Produk Tersedia</small>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Section Featured Products -->
<section class="py-5 bg-white border-top border-bottom">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
            <div>
                <span class="text-primary fw-bold text-uppercase small letter-spacing-1">Koleksi Terbaru</span>
                <h2 class="fw-bold mb-0">Produk Unggulan & Terpopuler</h2>
            </div>
            <div class="d-flex gap-2 mt-2 mt-md-0">
                <a href="<?= BASE_URL ?>customer/products.php" class="btn btn-outline-primary btn-sm px-3">
                    Buka Seluruh Katalog (24 Produk)
                </a>
            </div>
        </div>

        <div class="row g-4 row-cols-1 row-cols-sm-2 row-cols-lg-4">
            <?php foreach ($featured_products as $prod): ?>
                <div class="col">
                    <div class="product-card">
                        <div class="img-wrapper">
                            <img src="<?= get_product_image_url($prod['image'], $prod['category_name']) ?>" alt="<?= htmlspecialchars($prod['name']) ?>">
                            <span class="category-badge"><?= htmlspecialchars($prod['category_name']) ?></span>
                            <?php if ($prod['stock'] < 5): ?>
                                <span class="stock-badge bg-danger text-white">Sisa <?= $prod['stock'] ?>!</span>
                            <?php else: ?>
                                <span class="stock-badge bg-success text-white">Ready Stok</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <a href="<?= BASE_URL ?>product-detail.php?id=<?= $prod['id'] ?>" class="product-title" title="<?= htmlspecialchars($prod['name']) ?>">
                                <?= htmlspecialchars($prod['name']) ?>
                            </a>
                            <div class="price-tag">
                                <?= format_rupiah($prod['price']) ?>
                            </div>
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
    </div>
</section>

<!-- Section Keunggulan / Value Proposition -->
<section class="py-5">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-3 col-sm-6">
                <div class="p-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; font-size: 1.8rem;">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Garansi Resmi</h5>
                    <p class="text-muted small mb-0">Semua produk dilindungi jaminan garansi distributor resmi hingga 2 tahun.</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="p-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; font-size: 1.8rem;">
                        <i class="bi bi-truck"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Pengiriman Kilat</h5>
                    <p class="text-muted small mb-0">Kerja sama dengan ekspedisi terpercaya JNE, SiCepat, dan J&T Express.</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="p-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; font-size: 1.8rem;">
                        <i class="bi bi-qr-code-scan"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Multi Pembayaran</h5>
                    <p class="text-muted small mb-0">Mendukung QRIS instan, Transfer Bank BCA/Mandiri, E-Wallet, serta COD.</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="p-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; font-size: 1.8rem;">
                        <i class="bi bi-headset"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Bantuan 24/7</h5>
                    <p class="text-muted small mb-0">Tim customer care kami siap melayani pertanyaan spesifikasi dan pemesanan.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>

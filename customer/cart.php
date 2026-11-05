<?php
/**
 * TECHSTORE - Keranjang Belanja Pelanggan
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

// Wajib login untuk mengelola keranjang
check_login();
$user_id = $_SESSION['user_id'];

// Ambil atau buat ID keranjang user
try {
    $stmt_cart = $pdo->prepare("SELECT id FROM carts WHERE user_id = ? LIMIT 1");
    $stmt_cart->execute([$user_id]);
    $cart = $stmt_cart->fetch();

    if (!$cart) {
        $stmt_new_cart = $pdo->prepare("INSERT INTO carts (user_id) VALUES (?)");
        $stmt_new_cart->execute([$user_id]);
        $cart_id = $pdo->lastInsertId();
    } else {
        $cart_id = $cart['id'];
    }
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

// -------------------------------------------------------------
// POST / GET Action Handler
// -------------------------------------------------------------
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Tambah Produk ke Keranjang
    if ($action === 'add') {
        $product_id = (int)($_POST['product_id'] ?? 0);
        $qty = max(1, (int)($_POST['quantity'] ?? 1));

        // Cek stok produk
        $stmt_prod = $pdo->prepare("SELECT id, name, price, stock FROM products WHERE id = ? AND status = 'active' LIMIT 1");
        $stmt_prod->execute([$product_id]);
        $prod = $stmt_prod->fetch();

        if ($prod) {
            if ($prod['stock'] < $qty) {
                set_flash('danger', "Maaf, stok untuk {$prod['name']} tidak mencukupi (Tersisa {$prod['stock']} unit).");
            } else {
                // Cek apakah produk sudah ada di keranjang
                $stmt_check = $pdo->prepare("SELECT id, quantity FROM cart_details WHERE cart_id = ? AND product_id = ?");
                $stmt_check->execute([$cart_id, $product_id]);
                $existing = $stmt_check->fetch();

                if ($existing) {
                    $new_qty = $existing['quantity'] + $qty;
                    if ($new_qty > $prod['stock']) {
                        $new_qty = $prod['stock'];
                        set_flash('warning', "Jumlah di keranjang disesuaikan dengan sisa stok maksimal ({$prod['stock']} unit).");
                    } else {
                        set_flash('success', "Jumlah <strong>{$prod['name']}</strong> di keranjang berhasil diperbarui!");
                    }
                    $stmt_up = $pdo->prepare("UPDATE cart_details SET quantity = ? WHERE id = ?");
                    $stmt_up->execute([$new_qty, $existing['id']]);
                } else {
                    $stmt_in = $pdo->prepare("INSERT INTO cart_details (cart_id, product_id, quantity) VALUES (?, ?, ?)");
                    $stmt_in->execute([$cart_id, $product_id, $qty]);
                    set_flash('success', "<strong>{$prod['name']}</strong> berhasil ditambahkan ke keranjang belanja!");
                }
            }
        } else {
            set_flash('danger', 'Produk tidak ditemukan!');
        }
        header('Location: ' . BASE_URL . 'customer/cart.php');
        exit;
    }

    // 2. Ubah Jumlah Item
    if ($action === 'update') {
        $detail_id = (int)($_POST['detail_id'] ?? 0);
        $qty = (int)($_POST['quantity'] ?? 1);

        if ($qty <= 0) {
            $stmt_del = $pdo->prepare("DELETE FROM cart_details WHERE id = ? AND cart_id = ?");
            $stmt_del->execute([$detail_id, $cart_id]);
            set_flash('info', 'Item dihapus dari keranjang belanja.');
        } else {
            // Validasi stok produk terkait
            $stmt_cd = $pdo->prepare("
                SELECT cd.id, p.stock, p.name 
                FROM cart_details cd
                JOIN products p ON cd.product_id = p.id
                WHERE cd.id = ? AND cd.cart_id = ?
            ");
            $stmt_cd->execute([$detail_id, $cart_id]);
            $item = $stmt_cd->fetch();

            if ($item) {
                if ($qty > $item['stock']) {
                    $qty = $item['stock'];
                    set_flash('warning', "Jumlah disesuaikan dengan stok yang tersedia ({$item['stock']} unit).");
                } else {
                    set_flash('success', 'Jumlah produk berhasil diperbarui.');
                }
                $stmt_up = $pdo->prepare("UPDATE cart_details SET quantity = ? WHERE id = ?");
                $stmt_up->execute([$qty, $detail_id]);
            }
        }
        header('Location: ' . BASE_URL . 'customer/cart.php');
        exit;
    }
}

// 3. Hapus Item Tertentu dari Keranjang (via GET)
if ($action === 'delete') {
    $detail_id = (int)($_GET['id'] ?? 0);
    $stmt_del = $pdo->prepare("DELETE FROM cart_details WHERE id = ? AND cart_id = ?");
    $stmt_del->execute([$detail_id, $cart_id]);
    set_flash('info', 'Produk telah dikeluarkan dari keranjang belanja.');
    header('Location: ' . BASE_URL . 'customer/cart.php');
    exit;
}

// 4. Kosongkan Keranjang
if ($action === 'clear') {
    $stmt_clear = $pdo->prepare("DELETE FROM cart_details WHERE cart_id = ?");
    $stmt_clear->execute([$cart_id]);
    set_flash('info', 'Keranjang belanja Anda telah dikosongkan.');
    header('Location: ' . BASE_URL . 'customer/cart.php');
    exit;
}

// Ambil seluruh item keranjang
try {
    $stmt_items = $pdo->prepare("
        SELECT cd.id AS detail_id, cd.quantity, p.id AS product_id, p.name, p.price, p.stock, p.image, p.weight, c.name AS category_name,
               (cd.quantity * p.price) AS subtotal
        FROM cart_details cd
        JOIN products p ON cd.product_id = p.id
        JOIN categories c ON p.category_id = c.id
        WHERE cd.cart_id = ?
        ORDER BY cd.id DESC
    ");
    $stmt_items->execute([$cart_id]);
    $cart_items = $stmt_items->fetchAll();
} catch (Exception $e) {
    die("Query error: " . $e->getMessage());
}

$subtotal_total = 0;
$total_weight = 0;
foreach ($cart_items as $item) {
    $subtotal_total += $item['subtotal'];
    $total_weight += ($item['weight'] * $item['quantity']);
}

$page_title = 'Keranjang Belanja - TechStore';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php" class="text-decoration-none text-muted">Beranda</a></li>
            <li class="breadcrumb-item active" aria-current="page">Keranjang Belanja</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0"><i class="bi bi-cart3 text-primary me-2"></i>Keranjang Belanja Anda</h2>
        <?php if (!empty($cart_items)): ?>
            <a href="<?= BASE_URL ?>customer/cart.php?action=clear" class="btn btn-outline-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin mengosongkan seluruh keranjang belanja?');">
                <i class="bi bi-trash3 me-1"></i>Kosongkan Keranjang
            </a>
        <?php endif; ?>
    </div>

    <?php if (empty($cart_items)): ?>
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center my-4">
            <div class="display-1 text-muted mb-3"><i class="bi bi-cart-x"></i></div>
            <h4 class="fw-bold mb-2">Keranjang Belanja Masih Kosong</h4>
            <p class="text-muted mb-4">Belum ada aksesoris komputer atau laptop yang Anda pilih. Mari jelajahi koleksi produk terbaik kami!</p>
            <div>
                <a href="<?= BASE_URL ?>customer/products.php" class="btn btn-primary px-4 py-2">
                    <i class="bi bi-grid-fill me-2"></i>Mulai Berbelanja Sekarang
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <!-- Kolom Daftar Item -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Harga Satuan</th>
                                    <th style="width: 140px;">Jumlah</th>
                                    <th>Subtotal</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="<?= get_product_image_url($item['image'], $item['category_name']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="rounded-3 border" style="width: 64px; height: 64px; object-fit: cover;">
                                                <div>
                                                    <a href="<?= BASE_URL ?>product-detail.php?id=<?= $item['product_id'] ?>" class="fw-bold text-dark text-decoration-none d-block">
                                                        <?= htmlspecialchars($item['name']) ?>
                                                    </a>
                                                    <small class="text-muted"><i class="bi bi-tag me-1"></i><?= htmlspecialchars($item['category_name']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="fw-semibold text-secondary">
                                            <?= format_rupiah($item['price']) ?>
                                        </td>
                                        <td>
                                            <form action="<?= BASE_URL ?>customer/cart.php" method="POST" class="d-flex align-items-center">
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="detail_id" value="<?= $item['detail_id'] ?>">
                                                <div class="input-group input-group-sm">
                                                    <button class="btn btn-outline-secondary" type="button" onclick="let inp = this.nextElementSibling; if(inp.value > 1) { inp.value--; inp.form.submit(); }">-</button>
                                                    <input type="number" name="quantity" class="form-control text-center cart-qty-input" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>">
                                                    <button class="btn btn-outline-secondary" type="button" onclick="let inp = this.previousElementSibling; if(parseInt(inp.value) < <?= $item['stock'] ?>) { inp.value++; inp.form.submit(); }">+</button>
                                                </div>
                                            </form>
                                            <small class="text-muted d-block text-center mt-1" style="font-size: 0.72rem;">Stok: <?= $item['stock'] ?></small>
                                        </td>
                                        <td class="fw-bold text-primary">
                                            <?= format_rupiah($item['subtotal']) ?>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?= BASE_URL ?>customer/cart.php?action=delete&id=<?= $item['detail_id'] ?>" class="btn btn-sm text-danger" title="Hapus Item" onclick="return confirm('Hapus barang ini dari keranjang?');">
                                                <i class="bi bi-trash3-fill"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-3">
                    <a href="<?= BASE_URL ?>customer/products.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Lanjutkan Belanja Produk Lain
                    </a>
                </div>
            </div>

            <!-- Kolom Ringkasan Belanja -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 90px;">
                    <h5 class="fw-bold mb-3">Ringkasan Pesanan</h5>
                    
                    <div class="d-flex justify-content-between mb-2 text-secondary small">
                        <span>Total Barang:</span>
                        <span class="fw-semibold text-dark"><?= count($cart_items) ?> macam (<?= array_sum(array_column($cart_items, 'quantity')) ?> unit)</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2 text-secondary small">
                        <span>Total Berat:</span>
                        <span class="fw-semibold text-dark"><?= number_format($total_weight / 1000, 2) ?> kg</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2 text-secondary small">
                        <span>Subtotal Produk:</span>
                        <span class="fw-bold text-dark fs-6"><?= format_rupiah($subtotal_total) ?></span>
                    </div>

                    <hr class="my-3">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="fw-bold fs-6">Estimasi Total:</span>
                        <span class="fw-extrabold text-primary fs-4"><?= format_rupiah($subtotal_total) ?></span>
                    </div>

                    <a href="<?= BASE_URL ?>customer/checkout.php" class="btn btn-primary w-100 py-3 fw-bold shadow-sm d-flex justify-content-center align-items-center gap-2">
                        <span>Lanjut ke Checkout</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>

                    <div class="mt-3 p-3 bg-light rounded-3 text-secondary small text-center">
                        <i class="bi bi-shield-lock-fill text-success me-1"></i> Transaksi terenkripsi aman & bergaransi
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<?php
/**
 * TECHSTORE - Halaman Checkout & Pembuatan Transaksi
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

check_login();
$user_id = $_SESSION['user_id'];

// Ambil info profil user untuk data pengiriman bawaan
try {
    $stmt_user = $pdo->prepare("SELECT name, email, phone, address FROM users WHERE id = ?");
    $stmt_user->execute([$user_id]);
    $user_profile = $stmt_user->fetch();

    // Ambil keranjang user
    $stmt_cart = $pdo->prepare("SELECT id FROM carts WHERE user_id = ? LIMIT 1");
    $stmt_cart->execute([$user_id]);
    $cart = $stmt_cart->fetch();

    if (!$cart) {
        header('Location: ' . BASE_URL . 'customer/cart.php');
        exit;
    }

    $cart_id = $cart['id'];

    // Ambil item keranjang
    $stmt_items = $pdo->prepare("
        SELECT cd.id AS detail_id, cd.quantity, p.id AS product_id, p.name, p.price, p.stock, p.image, p.weight, c.name AS category_name,
               (cd.quantity * p.price) AS subtotal
        FROM cart_details cd
        JOIN products p ON cd.product_id = p.id
        JOIN categories c ON p.category_id = c.id
        WHERE cd.cart_id = ?
    ");
    $stmt_items->execute([$cart_id]);
    $cart_items = $stmt_items->fetchAll();

    if (empty($cart_items)) {
        set_flash('warning', 'Keranjang belanja Anda kosong. Silakan pilih produk terlebih dahulu.');
        header('Location: ' . BASE_URL . 'customer/products.php');
        exit;
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// Hitung subtotal
$subtotal_total = 0;
$total_weight = 0;
foreach ($cart_items as $item) {
    $subtotal_total += $item['subtotal'];
    $total_weight += ($item['weight'] * $item['quantity']);
}

// Daftar Kurir Pengiriman & Ongkos Kirim Flat Demo
$couriers = [
    'JNE Regular'     => 20000,
    'SiCepat BEST'    => 25000,
    'J&T Express'     => 18000,
    'Anteraja Reguler'=> 19000
];

$error = '';

// Proses Checkout Saat Form Disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient_name = sanitize($_POST['recipient_name'] ?? '');
    $recipient_phone = sanitize($_POST['recipient_phone'] ?? '');
    $shipping_address = sanitize($_POST['shipping_address'] ?? '');
    $selected_courier = sanitize($_POST['courier'] ?? 'JNE Regular');
    $payment_method = sanitize($_POST['payment_method'] ?? 'QRIS');
    $notes = sanitize($_POST['notes'] ?? '');

    $shipping_cost = isset($couriers[$selected_courier]) ? $couriers[$selected_courier] : 20000;
    $grand_total = $subtotal_total + $shipping_cost;

    if (empty($recipient_name) || empty($recipient_phone) || empty($shipping_address)) {
        $error = 'Nama penerima, nomor telepon, dan alamat pengiriman lengkap wajib diisi!';
    } else {
        try {
            // Memulai Transaksi Database (ACID Compliance)
            $pdo->beginTransaction();

            // 1. Validasi ulang stok seluruh produk di keranjang
            foreach ($cart_items as $item) {
                $stmt_chk = $pdo->prepare("SELECT stock, name FROM products WHERE id = ? FOR UPDATE");
                $stmt_chk->execute([$item['product_id']]);
                $curr_prod = $stmt_chk->fetch();

                if (!$curr_prod || $curr_prod['stock'] < $item['quantity']) {
                    throw new Exception("Maaf, stok untuk produk '{$curr_prod['name']}' tidak mencukupi atau telah habis!");
                }
            }

            // 2. Buat Nomor Transaksi dan Faktur Otomatis
            $transaction_code = generate_transaction_code();
            $invoice_code = generate_invoice_code();

            // Status awal transaksi: jika COD -> pending / processing, transfer/qris/ewallet -> pending
            $trans_status = 'pending';

            // 3. Simpan ke tabel transactions
            $stmt_trans = $pdo->prepare("
                INSERT INTO transactions (
                    transaction_code, user_id, total_amount, shipping_cost, grand_total,
                    status, recipient_name, recipient_phone, shipping_address, shipping_courier, notes
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt_trans->execute([
                $transaction_code, $user_id, $subtotal_total, $shipping_cost, $grand_total,
                $trans_status, $recipient_name, $recipient_phone, $shipping_address, $selected_courier, $notes
            ]);
            $transaction_id = $pdo->lastInsertId();

            // 4. Simpan ke tabel transaction_details dan kurangi stok produk
            $stmt_td = $pdo->prepare("
                INSERT INTO transaction_details (transaction_id, product_id, product_name, price, quantity, subtotal)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt_dec_stock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

            foreach ($cart_items as $item) {
                $stmt_td->execute([
                    $transaction_id,
                    $item['product_id'],
                    $item['name'],
                    $item['price'],
                    $item['quantity'],
                    $item['subtotal']
                ]);
                // Kurangi stok barang
                $stmt_dec_stock->execute([$item['quantity'], $item['product_id']]);
            }

            // 5. Simpan ke tabel payments
            $stmt_pay = $pdo->prepare("
                INSERT INTO payments (transaction_id, payment_method, payment_status, amount, notes)
                VALUES (?, ?, 'pending', ?, ?)
            ");
            $stmt_pay->execute([
                $transaction_id,
                $payment_method,
                $grand_total,
                "Pemesanan dibuat melalui checkout online oleh {$recipient_name}"
            ]);

            // 6. Simpan ke tabel invoices
            $invoice_date = date('Y-m-d');
            $due_date = date('Y-m-d', strtotime('+1 day'));
            $stmt_inv = $pdo->prepare("
                INSERT INTO invoices (invoice_code, transaction_id, user_id, invoice_date, due_date, total_amount, status)
                VALUES (?, ?, ?, ?, ?, ?, 'unpaid')
            ");
            $stmt_inv->execute([
                $invoice_code,
                $transaction_id,
                $user_id,
                $invoice_date,
                $due_date,
                $grand_total
            ]);

            // 7. Kosongkan keranjang belanja pengguna
            $stmt_del_cart = $pdo->prepare("DELETE FROM cart_details WHERE cart_id = ?");
            $stmt_del_cart->execute([$cart_id]);

            // Commit transaksi database
            $pdo->commit();

            set_flash('success', "Transaksi berhasil dibuat dengan kode <strong>{$transaction_code}</strong>. Silakan selesaikan pembayaran Anda!");
            header('Location: ' . BASE_URL . 'customer/payment.php?trx=' . $transaction_code);
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Gagal memproses pesanan: ' . $e->getMessage();
        }
    }
}

$page_title = 'Checkout Pembelian - TechStore';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php" class="text-decoration-none text-muted">Beranda</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>customer/cart.php" class="text-decoration-none text-muted">Keranjang</a></li>
            <li class="breadcrumb-item active" aria-current="page">Checkout Pesanan</li>
        </ol>
    </nav>

    <h2 class="fw-bold mb-4"><i class="bi bi-shield-check text-primary me-2"></i>Checkout & Pembayaran</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
            <div><?= htmlspecialchars($error) ?></div>
        </div>
    <?php endif; ?>

    <form method="POST" action="" id="checkoutForm">
        <div class="row g-4">
            <!-- Kolom Kiri: Form Data Pengiriman & Metode Bayar -->
            <div class="col-lg-7">
                <!-- 1. Alamat Pengiriman -->
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-geo-alt-fill text-primary"></i> 1. Alamat Pengiriman
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Nama Lengkap Penerima</label>
                            <input type="text" name="recipient_name" class="form-control" value="<?= htmlspecialchars($user_profile['name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Nomor Telepon / WhatsApp</label>
                            <input type="text" name="recipient_phone" class="form-control" value="<?= htmlspecialchars($user_profile['phone'] ?? '') ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Alamat Lengkap</label>
                            <textarea name="shipping_address" class="form-control" rows="3" placeholder="Alamat lengkap, nama jalan, RT/RW, kecamatan, kota, kodepos" required><?= htmlspecialchars($user_profile['address'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Catatan Pesanan (Opsional)</label>
                            <input type="text" name="notes" class="form-control" placeholder="Contoh: Titipkan ke satpam jika penerima tidak di tempat">
                        </div>
                    </div>
                </div>

                <!-- 2. Pilihan Ekspedisi Pengiriman -->
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-truck text-primary"></i> 2. Ekspedisi Pengiriman
                    </h5>
                    <div class="row g-3">
                        <?php $i = 0; foreach ($couriers as $name => $cost): $i++; ?>
                            <div class="col-md-6">
                                <label class="border rounded-3 p-3 d-flex align-items-center justify-content-between cursor-pointer w-100 courier-option" style="cursor: pointer;">
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="radio" name="courier" value="<?= $name ?>" data-cost="<?= $cost ?>" <?= $i === 1 ? 'checked' : '' ?> onchange="updateTotalCost(<?= $cost ?>)">
                                        <div>
                                            <div class="fw-bold small text-dark"><?= $name ?></div>
                                            <div class="text-muted" style="font-size: 0.75rem;">Estimasi 2-3 Hari Kerja</div>
                                        </div>
                                    </div>
                                    <span class="fw-bold text-primary small"><?= format_rupiah($cost) ?></span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 3. Metode Pembayaran Sesuai Spesifikasi F -->
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-wallet2 text-primary"></i> 3. Pilih Metode Pembayaran
                    </h5>
                    <div class="row g-3">
                        <!-- Transfer Bank -->
                        <div class="col-12">
                            <label class="border rounded-3 p-3 d-flex align-items-center justify-content-between w-100" style="cursor: pointer;">
                                <div class="d-flex align-items-center gap-3">
                                    <input type="radio" name="payment_method" value="Transfer Bank - BCA" checked>
                                    <i class="bi bi-bank text-primary fs-4"></i>
                                    <div>
                                        <div class="fw-bold small">Transfer Bank (BCA / Mandiri / BRI)</div>
                                        <div class="text-muted small">Unggah bukti transfer untuk verifikasi admin</div>
                                    </div>
                                </div>
                                <span class="badge bg-light text-dark border">Manual Check</span>
                            </label>
                        </div>

                        <!-- QRIS Simulasi -->
                        <div class="col-12">
                            <label class="border rounded-3 p-3 d-flex align-items-center justify-content-between w-100" style="cursor: pointer;">
                                <div class="d-flex align-items-center gap-3">
                                    <input type="radio" name="payment_method" value="QRIS">
                                    <i class="bi bi-qr-code-scan text-success fs-4"></i>
                                    <div>
                                        <div class="fw-bold small">QRIS (Simulasi Transaksi Instan)</div>
                                        <div class="text-muted small">Scan QR Code dari seluruh e-wallet & m-banking</div>
                                    </div>
                                </div>
                                <span class="badge bg-success">Instan / Otomatis</span>
                            </label>
                        </div>

                        <!-- E-Wallet: DANA, OVO, GoPay -->
                        <div class="col-md-4">
                            <label class="border rounded-3 p-3 d-flex align-items-center gap-2 w-100" style="cursor: pointer;">
                                <input type="radio" name="payment_method" value="DANA">
                                <span class="fw-bold small text-primary">DANA</span>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <label class="border rounded-3 p-3 d-flex align-items-center gap-2 w-100" style="cursor: pointer;">
                                <input type="radio" name="payment_method" value="OVO">
                                <span class="fw-bold small text-info">OVO</span>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <label class="border rounded-3 p-3 d-flex align-items-center gap-2 w-100" style="cursor: pointer;">
                                <input type="radio" name="payment_method" value="GoPay">
                                <span class="fw-bold small text-success">GoPay</span>
                            </label>
                        </div>

                        <!-- COD -->
                        <div class="col-12">
                            <label class="border rounded-3 p-3 d-flex align-items-center justify-content-between w-100" style="cursor: pointer;">
                                <div class="d-flex align-items-center gap-3">
                                    <input type="radio" name="payment_method" value="COD (Cash on Delivery)">
                                    <i class="bi bi-cash-stack text-warning fs-4"></i>
                                    <div>
                                        <div class="fw-bold small">COD (Cash on Delivery)</div>
                                        <div class="text-muted small">Bayar tunai ke kurir saat barang tiba di alamat Anda</div>
                                    </div>
                                </div>
                                <span class="badge bg-secondary">Bayar di Tempat</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Rincian Belanja & Tombol Konfirmasi -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 90px;">
                    <h5 class="fw-bold mb-3">Rincian Barang Dibeli</h5>

                    <div class="border rounded-3 p-3 mb-3 bg-light overflow-y-auto" style="max-height: 250px;">
                        <?php foreach ($cart_items as $it): ?>
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-light-subtle">
                                <div class="text-truncate me-2" style="max-width: 220px;">
                                    <div class="fw-semibold small text-dark text-truncate"><?= htmlspecialchars($it['name']) ?></div>
                                    <small class="text-muted"><?= $it['quantity'] ?>x &bull; <?= format_rupiah($it['price']) ?></small>
                                </div>
                                <div class="fw-bold text-dark small">
                                    <?= format_rupiah($it['subtotal']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="d-flex justify-content-between mb-2 text-secondary small">
                        <span>Subtotal Produk:</span>
                        <span class="fw-bold text-dark" id="displaySubtotal"><?= format_rupiah($subtotal_total) ?></span>
                    </div>

                    <div class="d-flex justify-content-between mb-2 text-secondary small">
                        <span>Biaya Pengiriman:</span>
                        <span class="fw-bold text-dark" id="displayShipping"><?= format_rupiah(20000) ?></span>
                    </div>

                    <hr class="my-3">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="fw-bold">Total Pembayaran:</span>
                        <span class="fw-extrabold text-primary fs-4" id="displayGrandTotal"><?= format_rupiah($subtotal_total + 20000) ?></span>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow-sm d-flex justify-content-center align-items-center gap-2">
                        <i class="bi bi-lock-fill"></i>
                        <span>Proses Pesanan Sekarang</span>
                    </button>

                    <div class="mt-3 text-center small text-muted">
                        Dengan memproses pesanan, Anda menyetujui ketentuan transaksi di TechStore.
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
const baseSubtotal = <?= (float)$subtotal_total ?>;

function updateTotalCost(shippingCost) {
    const grand = baseSubtotal + shippingCost;
    document.getElementById('displayShipping').textContent = formatRupiahJS(shippingCost);
    document.getElementById('displayGrandTotal').textContent = formatRupiahJS(grand);
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

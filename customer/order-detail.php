<?php
/**
 * TECHSTORE - Halaman Rincian Pesanan Pelanggan
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

check_login();
$user_id = $_SESSION['user_id'];

$trx_code = sanitize($_GET['code'] ?? '');
if (empty($trx_code)) {
    header('Location: ' . BASE_URL . 'customer/history.php');
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT t.*, p.payment_method, p.payment_status, p.payment_proof, p.verified_at,
               i.invoice_code, i.invoice_date, i.status AS invoice_status
        FROM transactions t
        LEFT JOIN payments p ON t.id = p.transaction_id
        LEFT JOIN invoices i ON t.id = i.transaction_id
        WHERE t.transaction_code = ? AND t.user_id = ?
        LIMIT 1
    ");
    $stmt->execute([$trx_code, $user_id]);
    $order = $stmt->fetch();

    if (!$order) {
        set_flash('danger', 'Pesanan tidak ditemukan.');
        header('Location: ' . BASE_URL . 'customer/history.php');
        exit;
    }

    // Ambil item produk dalam pesanan
    $stmt_items = $pdo->prepare("
        SELECT td.*, p.image, c.name AS category_name
        FROM transaction_details td
        LEFT JOIN products p ON td.product_id = p.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE td.transaction_id = ?
    ");
    $stmt_items->execute([$order['id']]);
    $items = $stmt_items->fetchAll();

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

$page_title = 'Detail Pesanan ' . $order['transaction_code'] . ' - TechStore';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php" class="text-decoration-none text-muted">Beranda</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>customer/history.php" class="text-decoration-none text-muted">Riwayat Pesanan</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($order['transaction_code']) ?></li>
        </ol>
    </nav>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1">Rincian Pesanan</h2>
            <div class="text-muted small">
                Nomor: <strong><?= htmlspecialchars($order['transaction_code']) ?></strong> &bull; <?= format_date($order['created_at'], true) ?>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>invoice/index.php?code=<?= $order['invoice_code'] ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-file-earmark-pdf me-1"></i>Lihat Invoice Resmi
            </a>
            <a href="<?= BASE_URL ?>invoice/struk.php?code=<?= $order['invoice_code'] ?>" target="_blank" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-printer me-1"></i>Cetak Struk
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Kolom Kiri: Tabel Barang & Ongkir -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-light border-bottom p-3 fw-bold">
                    <i class="bi bi-box-seam text-primary me-2"></i>Daftar Barang yang Dipesan
                </div>
                <div class="table-responsive">
                    <table class="table table-custom align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Jumlah</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $it): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="<?= get_product_image_url($it['image'], $it['category_name']) ?>" alt="<?= htmlspecialchars($it['product_name']) ?>" class="rounded-3 border" style="width: 50px; height: 50px; object-fit: cover;">
                                            <div>
                                                <div class="fw-semibold text-dark"><?= htmlspecialchars($it['product_name']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($it['category_name'] ?? 'Accessories') ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= format_rupiah($it['price']) ?></td>
                                    <td><?= $it['quantity'] ?> unit</td>
                                    <td class="text-end fw-bold text-dark"><?= format_rupiah($it['subtotal']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end fw-semibold">Subtotal Produk:</td>
                                <td class="text-end fw-bold"><?= format_rupiah($order['total_amount']) ?></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end fw-semibold">Biaya Pengiriman (<?= htmlspecialchars($order['shipping_courier']) ?>):</td>
                                <td class="text-end fw-bold"><?= format_rupiah($order['shipping_cost']) ?></td>
                            </tr>
                            <tr class="table-light">
                                <td colspan="3" class="text-end fw-extrabold fs-6">Grand Total:</td>
                                <td class="text-end fw-extrabold text-primary fs-5"><?= format_rupiah($order['grand_total']) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Info Pengiriman & Pembayaran -->
        <div class="col-lg-4">
            <!-- Info Status -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <h6 class="fw-bold mb-3 text-secondary text-uppercase small">Status Transaksi</h6>
                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <span>Status Pesanan:</span>
                    <?= get_status_badge($order['status']) ?>
                </div>
                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <span>Status Bayar:</span>
                    <?= get_payment_badge($order['payment_status']) ?>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span>Metode Bayar:</span>
                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($order['payment_method']) ?></span>
                </div>

                <?php if ($order['payment_status'] === 'pending'): ?>
                    <hr>
                    <a href="<?= BASE_URL ?>customer/payment.php?trx=<?= $order['transaction_code'] ?>" class="btn btn-warning w-100 fw-bold">
                        <i class="bi bi-wallet2 me-1"></i>Selesaikan Pembayaran
                    </a>
                <?php endif; ?>
            </div>

            <!-- Info Penerima -->
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h6 class="fw-bold mb-3 text-secondary text-uppercase small">Informasi Pengiriman</h6>
                <div class="mb-2">
                    <strong>Penerima:</strong><br>
                    <span><?= htmlspecialchars($order['recipient_name']) ?></span> (<?= htmlspecialchars($order['recipient_phone']) ?>)
                </div>
                <div class="mb-3">
                    <strong>Alamat Tujuan:</strong><br>
                    <span class="text-muted small leading-relaxed"><?= htmlspecialchars($order['shipping_address']) ?></span>
                </div>
                <div class="mb-2">
                    <strong>Ekspedisi Kurir:</strong><br>
                    <span><?= htmlspecialchars($order['shipping_courier']) ?></span>
                </div>
                <?php if (!empty($order['notes'])): ?>
                    <div class="mt-2 pt-2 border-top small text-muted">
                        <em>Catatan: <?= htmlspecialchars($order['notes']) ?></em>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

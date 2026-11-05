<?php
/**
 * TECHSTORE - Detail & Manajemen Transaksi (Admin)
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

check_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . BASE_URL . 'admin/transactions/index.php');
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT t.*, u.name AS user_name, u.email AS user_email,
               p.id AS payment_id, p.payment_method, p.payment_status, p.payment_proof, p.amount, p.verified_at,
               i.invoice_code, i.invoice_date, i.status AS invoice_status
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        LEFT JOIN payments p ON t.id = p.transaction_id
        LEFT JOIN invoices i ON t.id = i.transaction_id
        WHERE t.id = ?
        LIMIT 1
    ");
    $stmt->execute([$id]);
    $order = $stmt->fetch();

    if (!$order) {
        set_flash('danger', 'Transaksi tidak ditemukan.');
        header('Location: ' . BASE_URL . 'admin/transactions/index.php');
        exit;
    }

    // Ambil item produk
    $stmt_items = $pdo->prepare("
        SELECT td.*, p.image, c.name AS category_name
        FROM transaction_details td
        LEFT JOIN products p ON td.product_id = p.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE td.transaction_id = ?
    ");
    $stmt_items->execute([$id]);
    $items = $stmt_items->fetchAll();

} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

$page_title = 'Kelola Transaksi ' . $order['transaction_code'] . ' - TechStore Admin';
include __DIR__ . '/../../includes/admin_header.php';
include __DIR__ . '/../../includes/admin_sidebar.php';
include __DIR__ . '/../../includes/admin_topbar.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-receipt text-primary me-2"></i>Kelola Transaksi: <?= htmlspecialchars($order['transaction_code']) ?></h3>
        <p class="text-muted small mb-0">Dipesan pada <?= format_date($order['created_at'], true) ?> oleh <?= htmlspecialchars($order['recipient_name']) ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>admin/transactions/index.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
        <a href="<?= BASE_URL ?>invoice/index.php?code=<?= $order['invoice_code'] ?>" target="_blank" class="btn btn-primary btn-sm">
            <i class="bi bi-receipt me-1"></i>Lihat Invoice
        </a>
        <a href="<?= BASE_URL ?>invoice/struk.php?code=<?= $order['invoice_code'] ?>" target="_blank" class="btn btn-outline-dark btn-sm">
            <i class="bi bi-printer me-1"></i>Cetak Struk
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Tabel Barang & Rincian Keuangan -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white border-bottom p-3 fw-bold">
                <i class="bi bi-box-seam text-primary me-2"></i>Rincian Produk Dipesan
            </div>
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Harga Satuan</th>
                            <th class="text-center">Jumlah</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $it): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?= get_product_image_url($it['image'], $it['category_name']) ?>" alt="<?= htmlspecialchars($it['product_name']) ?>" class="rounded-3 border" style="width: 48px; height: 48px; object-fit: cover;">
                                        <div>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($it['product_name']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($it['category_name'] ?? 'Hardware') ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?= format_rupiah($it['price']) ?></td>
                                <td class="text-center"><?= $it['quantity'] ?> unit</td>
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
                            <td colspan="3" class="text-end fw-semibold">Ongkos Kirim (<?= htmlspecialchars($order['shipping_courier']) ?>):</td>
                            <td class="text-end fw-bold"><?= format_rupiah($order['shipping_cost']) ?></td>
                        </tr>
                        <tr class="table-light">
                            <td colspan="3" class="text-end fw-extrabold fs-6">Grand Total Pembayaran:</td>
                            <td class="text-end fw-extrabold text-primary fs-5"><?= format_rupiah($order['grand_total']) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Info Pengiriman & Catatan -->
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-truck text-primary me-2"></i>Informasi Penerima & Ekspedisi</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="small text-muted d-block">Nama Lengkap Penerima</label>
                    <span class="fw-bold text-dark"><?= htmlspecialchars($order['recipient_name']) ?></span>
                </div>
                <div class="col-md-6">
                    <label class="small text-muted d-block">Kontak / No. Telepon</label>
                    <span class="fw-bold text-dark"><?= htmlspecialchars($order['recipient_phone']) ?></span>
                </div>
                <div class="col-12">
                    <label class="small text-muted d-block">Alamat Tujuan Pengiriman</label>
                    <span class="text-dark"><?= htmlspecialchars($order['shipping_address']) ?></span>
                </div>
                <div class="col-md-6">
                    <label class="small text-muted d-block">Ekspedisi Kurir</label>
                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($order['shipping_courier']) ?></span>
                </div>
                <div class="col-md-6">
                    <label class="small text-muted d-block">Catatan Khusus</label>
                    <span class="text-muted"><?= htmlspecialchars($order['notes'] ?: '-') ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Status Update & Pembayaran -->
    <div class="col-lg-4">
        <!-- Form Ubah Status Transaksi -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-sliders text-primary me-2"></i>Status Transaksi</h5>
            
            <div class="mb-3">
                <label class="small text-muted d-block mb-1">Status Saat Ini:</label>
                <div><?= get_status_badge($order['status']) ?></div>
            </div>

            <form action="<?= BASE_URL ?>admin/transactions/update-status.php" method="POST">
                <input type="hidden" name="transaction_id" value="<?= $order['id'] ?>">
                
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Ubah Status Menjadi:</label>
                    <select name="status" class="form-select">
                        <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending (Menunggu Pembayaran)</option>
                        <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Diproses (Siap Dikirim)</option>
                        <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Dikirim (Dalam Perjalanan)</option>
                        <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Selesai (Barang Diterima)</option>
                        <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Dibatalkan (Stok Dikembalikan)</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-bold">
                    <i class="bi bi-check2-circle me-1"></i>Perbarui Status Pesanan
                </button>
            </form>
        </div>

        <!-- Detail Pembayaran & Verifikasi -->
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-credit-card-2-front text-primary me-2"></i>Status Pembayaran</h5>
            
            <div class="mb-2 d-flex justify-content-between align-items-center">
                <span class="small text-muted">Metode:</span>
                <span class="fw-bold"><?= htmlspecialchars($order['payment_method']) ?></span>
            </div>

            <div class="mb-3 d-flex justify-content-between align-items-center">
                <span class="small text-muted">Status:</span>
                <?= get_payment_badge($order['payment_status']) ?>
            </div>

            <?php if (!empty($order['payment_proof'])): ?>
                <div class="mb-3 text-center border rounded-3 p-2 bg-light">
                    <div class="small fw-semibold text-secondary mb-2">Bukti Transfer Pelanggan:</div>
                    <img src="<?= BASE_URL ?>assets/uploads/payments/<?= htmlspecialchars($order['payment_proof']) ?>" alt="Bukti Transfer" class="img-fluid rounded border mb-2" style="max-height: 180px;">
                    <div>
                        <a href="<?= BASE_URL ?>assets/uploads/payments/<?= htmlspecialchars($order['payment_proof']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrows-fullscreen me-1"></i>Perbesar Bukti
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($order['payment_status'] === 'pending'): ?>
                <a href="<?= BASE_URL ?>admin/payments/index.php" class="btn btn-warning w-100 fw-bold">
                    <i class="bi bi-shield-check me-1"></i>Buka Halaman Verifikasi
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/admin_footer.php'; ?>

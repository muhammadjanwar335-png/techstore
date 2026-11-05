<?php
/**
 * TECHSTORE - Manajemen & Verifikasi Pembayaran (Admin)
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

check_admin();

$status_filter = sanitize($_GET['status'] ?? 'all');
$search = sanitize($_GET['q'] ?? '');

$where = ["1=1"];
$params = [];

if ($status_filter !== 'all' && !empty($status_filter)) {
    $where[] = "p.payment_status = ?";
    $params[] = $status_filter;
}

if (!empty($search)) {
    $where[] = "(t.transaction_code LIKE ? OR u.name LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$sql = "
    SELECT p.*, t.transaction_code, t.recipient_name, t.status AS trans_status,
           u.name AS customer_name, u.email AS customer_email,
           a.name AS admin_verifier_name,
           i.invoice_code
    FROM payments p
    JOIN transactions t ON p.transaction_id = t.id
    JOIN users u ON t.user_id = u.id
    LEFT JOIN users a ON p.verified_by = a.id
    LEFT JOIN invoices i ON t.id = i.transaction_id
    WHERE " . implode(' AND ', $where) . "
    ORDER BY p.id DESC
";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $payments = $stmt->fetchAll();
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

$page_title = 'Verifikasi Pembayaran - TechStore Admin';
include __DIR__ . '/../../includes/admin_header.php';
include __DIR__ . '/../../includes/admin_sidebar.php';
include __DIR__ . '/../../includes/admin_topbar.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-credit-card-2-front text-primary me-2"></i>Verifikasi Pembayaran</h3>
        <p class="text-muted small mb-0">Cek mutasi, bukti transfer pelanggan, dan validasi transaksi.</p>
    </div>
</div>

<!-- Filter Pills -->
<div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>admin/payments/index.php?status=all" class="btn btn-sm <?= $status_filter === 'all' ? 'btn-primary' : 'btn-light border' ?> rounded-pill px-3">
                Semua Pembayaran
            </a>
            <a href="<?= BASE_URL ?>admin/payments/index.php?status=pending" class="btn btn-sm <?= $status_filter === 'pending' ? 'btn-warning text-dark fw-bold' : 'btn-light border' ?> rounded-pill px-3">
                Menunggu Verifikasi (Pending)
            </a>
            <a href="<?= BASE_URL ?>admin/payments/index.php?status=paid" class="btn btn-sm <?= $status_filter === 'paid' ? 'btn-success' : 'btn-light border' ?> rounded-pill px-3">
                Terverifikasi (Paid)
            </a>
            <a href="<?= BASE_URL ?>admin/payments/index.php?status=failed" class="btn btn-sm <?= $status_filter === 'failed' ? 'btn-danger' : 'btn-light border' ?> rounded-pill px-3">
                Gagal / Ditolak (Failed)
            </a>
        </div>

        <form method="GET" action="" class="d-flex gap-2">
            <input type="text" name="q" class="form-control form-control-sm bg-light" placeholder="Cari kode TRX/nama..." value="<?= htmlspecialchars($search) ?>" style="width: 200px;">
            <button type="submit" class="btn btn-primary btn-sm">Cari</button>
        </form>
    </div>
</div>

<!-- Payments Table -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-custom align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Kode Transaksi</th>
                    <th>Pelanggan</th>
                    <th>Metode Pembayaran</th>
                    <th>Nominal</th>
                    <th>Bukti Transfer</th>
                    <th>Status Bayar</th>
                    <th class="text-end" style="width: 140px;">Verifikasi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payments)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Tidak ada data pembayaran yang sesuai kriteria.</td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($payments as $pay): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <span class="fw-bold font-monospace text-dark d-block"><?= htmlspecialchars($pay['transaction_code']) ?></span>
                                <small class="text-muted"><?= format_date($pay['payment_date'], true) ?></small>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($pay['customer_name']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($pay['customer_email']) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($pay['payment_method']) ?></span>
                            </td>
                            <td class="fw-bold text-primary">
                                <?= format_rupiah($pay['amount']) ?>
                            </td>
                            <td>
                                <?php if (!empty($pay['payment_proof'])): ?>
                                    <button type="button" class="btn btn-sm btn-outline-info d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#proofModal<?= $pay['id'] ?>">
                                        <i class="bi bi-image"></i>
                                        <span>Lihat Bukti</span>
                                    </button>

                                    <!-- Modal Preview Bukti -->
                                    <div class="modal fade" id="proofModal<?= $pay['id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-4 border-0">
                                                <div class="modal-header border-bottom">
                                                    <h6 class="modal-title fw-bold">Bukti Transfer - <?= htmlspecialchars($pay['transaction_code']) ?></h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-center p-3">
                                                    <img src="<?= BASE_URL ?>assets/uploads/payments/<?= htmlspecialchars($pay['payment_proof']) ?>" alt="Bukti Transfer" class="img-fluid rounded border" style="max-height: 400px;">
                                                </div>
                                                <div class="modal-footer border-top">
                                                    <a href="<?= BASE_URL ?>assets/uploads/payments/<?= htmlspecialchars($pay['payment_proof']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-arrows-fullscreen me-1"></i>Buka Tab Penuh
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small"><em>(Tanpa lampiran)</em></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= get_payment_badge($pay['payment_status']) ?>
                                <?php if ($pay['verified_by']): ?>
                                    <div class="text-muted" style="font-size: 0.7rem;">Oleh: <?= htmlspecialchars($pay['admin_verifier_name']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#verifyModal<?= $pay['id'] ?>">
                                    <i class="bi bi-shield-check me-1"></i>Aksi
                                </button>
                            </td>
                        </tr>

                        <!-- Modal Verifikasi Pembayaran -->
                        <div class="modal fade" id="verifyModal<?= $pay['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content rounded-4 border-0 shadow-lg">
                                    <div class="modal-header border-bottom p-4">
                                        <h5 class="modal-title fw-bold"><i class="bi bi-shield-check text-primary me-2"></i>Verifikasi Pembayaran</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="<?= BASE_URL ?>admin/payments/verify.php" method="POST">
                                        <input type="hidden" name="payment_id" value="<?= $pay['id'] ?>">
                                        <input type="hidden" name="transaction_id" value="<?= $pay['transaction_id'] ?>">
                                        
                                        <div class="modal-body p-4 text-start">
                                            <div class="mb-3 p-3 bg-light rounded-3 small">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span>Kode Transaksi:</span>
                                                    <strong><?= htmlspecialchars($pay['transaction_code']) ?></strong>
                                                </div>
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span>Pelanggan:</span>
                                                    <strong><?= htmlspecialchars($pay['customer_name']) ?></strong>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span>Total Tagihan:</span>
                                                    <strong class="text-primary fs-6"><?= format_rupiah($pay['amount']) ?></strong>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold text-secondary">Tindakan Verifikasi</label>
                                                <select name="status" class="form-select" required>
                                                    <option value="paid" <?= $pay['payment_status'] === 'paid' ? 'selected' : '' ?>>TERIMA (Tandai Lunas / Paid)</option>
                                                    <option value="pending" <?= $pay['payment_status'] === 'pending' ? 'selected' : '' ?>>PENDING (Tunda / Menunggu)</option>
                                                    <option value="failed" <?= $pay['payment_status'] === 'failed' ? 'selected' : '' ?>>TOLAK (Pembayaran Gagal / Ditolak)</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold text-secondary">Catatan Verifikasi (Opsional)</label>
                                                <textarea name="notes" class="form-control" rows="2" placeholder="Catatan untuk pelanggan atau admin..."><?= htmlspecialchars($pay['notes'] ?? '') ?></textarea>
                                            </div>
                                        </div>

                                        <div class="modal-footer border-top p-3">
                                            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary fw-bold">Simpan Verifikasi</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../includes/admin_footer.php'; ?>

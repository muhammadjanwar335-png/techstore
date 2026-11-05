<?php
/**
 * TECHSTORE - Manajemen Transaksi Penjualan (Admin)
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

check_admin();

$search = sanitize($_GET['q'] ?? '');
$status_filter = sanitize($_GET['status'] ?? 'all');

$where = ["1=1"];
$params = [];

if (!empty($search)) {
    $where[] = "(t.transaction_code LIKE ? OR u.name LIKE ? OR t.recipient_name LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if ($status_filter !== 'all' && !empty($status_filter)) {
    $where[] = "t.status = ?";
    $params[] = $status_filter;
}

$sql = "
    SELECT t.*, u.name AS user_name, u.email AS user_email,
           p.payment_method, p.payment_status,
           i.invoice_code
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    LEFT JOIN payments p ON t.id = p.transaction_id
    LEFT JOIN invoices i ON t.id = i.transaction_id
    WHERE " . implode(' AND ', $where) . "
    ORDER BY t.id DESC
";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $transactions = $stmt->fetchAll();
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

$page_title = 'Semua Transaksi - TechStore Admin';
include __DIR__ . '/../../includes/admin_header.php';
include __DIR__ . '/../../includes/admin_sidebar.php';
include __DIR__ . '/../../includes/admin_topbar.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-receipt-cutoff text-primary me-2"></i>Daftar Seluruh Transaksi</h3>
        <p class="text-muted small mb-0">Kelola dan pantau pesanan pelanggan, ubah status pengiriman, dan kelola faktur.</p>
    </div>
</div>

<!-- Filter Status Bar -->
<div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
    <form method="GET" action="" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="q" class="form-control bg-light border-start-0" placeholder="Cari kode TRX atau nama pelanggan..." value="<?= htmlspecialchars($search) ?>">
            </div>
        </div>

        <div class="col-md-4">
            <select name="status" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
                <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>-- Semua Status Pesanan --</option>
                <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending (Menunggu Pembayaran)</option>
                <option value="processing" <?= $status_filter === 'processing' ? 'selected' : '' ?>>Diproses (Siap Kirim)</option>
                <option value="shipped" <?= $status_filter === 'shipped' ? 'selected' : '' ?>>Dikirim (Dalam Perjalanan)</option>
                <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Selesai</option>
                <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
            </select>
        </div>

        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm flex-fill">Filter</button>
            <?php if (!empty($search) || $status_filter !== 'all'): ?>
                <a href="<?= BASE_URL ?>admin/transactions/index.php" class="btn btn-outline-secondary btn-sm" title="Reset">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Transactions Table -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-custom align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Kode Transaksi</th>
                    <th>Pelanggan</th>
                    <th>Metode Bayar</th>
                    <th>Total Tagihan</th>
                    <th>Status Pesanan</th>
                    <th>Status Bayar</th>
                    <th class="text-end" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Tidak ada transaksi yang cocok dengan filter.</td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($transactions as $tr): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <span class="fw-bold font-monospace text-dark d-block"><?= htmlspecialchars($tr['transaction_code']) ?></span>
                                <small class="text-muted"><?= format_date($tr['created_at']) ?></small>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($tr['recipient_name']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($tr['recipient_phone']) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($tr['payment_method']) ?></span>
                            </td>
                            <td class="fw-bold text-primary">
                                <?= format_rupiah($tr['grand_total']) ?>
                            </td>
                            <td>
                                <?= get_status_badge($tr['status']) ?>
                            </td>
                            <td>
                                <?= get_payment_badge($tr['payment_status']) ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>admin/transactions/detail.php?id=<?= $tr['id'] ?>" class="btn btn-sm btn-outline-primary" title="Kelola Transaksi">
                                    <i class="bi bi-gear-fill me-1"></i>Kelola
                                </a>
                                <a href="<?= BASE_URL ?>invoice/index.php?code=<?= $tr['invoice_code'] ?>" target="_blank" class="btn btn-sm btn-light border" title="Lihat Invoice">
                                    <i class="bi bi-receipt"></i>
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

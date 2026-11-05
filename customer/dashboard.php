<?php
/**
 * TECHSTORE - Dashboard Pelanggan
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

check_customer();
$user_id = $_SESSION['user_id'];

try {
    // 1. Total Transaksi
    $stmt_tot = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE user_id = ?");
    $stmt_tot->execute([$user_id]);
    $total_orders = (int)$stmt_tot->fetchColumn();

    // 2. Pesanan Aktif (Pending, Processing, Shipped)
    $stmt_act = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE user_id = ? AND status IN ('pending', 'processing', 'shipped')");
    $stmt_act->execute([$user_id]);
    $active_orders = (int)$stmt_act->fetchColumn();

    // 3. Total Belanja (Completed)
    $stmt_spend = $pdo->prepare("SELECT COALESCE(SUM(grand_total), 0) FROM transactions WHERE user_id = ? AND status = 'completed'");
    $stmt_spend->execute([$user_id]);
    $total_spent = (float)$stmt_spend->fetchColumn();

    // 4. Riwayat 5 Transaksi Terakhir
    $stmt_recent = $pdo->prepare("
        SELECT t.*, p.payment_method, p.payment_status, i.invoice_code
        FROM transactions t
        LEFT JOIN payments p ON t.id = p.transaction_id
        LEFT JOIN invoices i ON t.id = i.transaction_id
        WHERE t.user_id = ?
        ORDER BY t.id DESC
        LIMIT 5
    ");
    $stmt_recent->execute([$user_id]);
    $recent_orders = $stmt_recent->fetchAll();

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

$page_title = 'Dashboard Pelanggan - TechStore';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-4">
    <!-- Welcome Header -->
    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4 text-white" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
        <div class="row align-items-center">
            <div class="col-md-8">
                <span class="badge bg-primary px-3 py-2 rounded-pill mb-2">Akun Pelanggan Terdaftar</span>
                <h2 class="fw-bold mb-2">Selamat Datang, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h2>
                <p class="text-secondary mb-0 small">
                    Kelola riwayat belanja, lacak ekspedisi pengiriman, dan nikmati berbagai promo aksesoris komputer eksklusif.
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="<?= BASE_URL ?>customer/products.php" class="btn btn-primary px-4 py-2 fw-semibold">
                    <i class="bi bi-cart3 me-2"></i>Mulai Belanja
                </a>
            </div>
        </div>
    </div>

    <!-- Stat Cards Pelanggan -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3 fs-3">
                        <i class="bi bi-bag-check"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Pesanan</div>
                        <div class="fs-4 fw-bold text-dark"><?= $total_orders ?> Transaksi</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3 fs-3">
                        <i class="bi bi-truck"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Pesanan Berjalan</div>
                        <div class="fs-4 fw-bold text-dark"><?= $active_orders ?> Pesanan</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-3 fs-3">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Pembelian Sukses</div>
                        <div class="fs-4 fw-bold text-dark"><?= format_rupiah($total_spent) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0"><i class="bi bi-receipt text-primary me-2"></i>Pesanan Terakhir Anda</h5>
            <a href="<?= BASE_URL ?>customer/history.php" class="text-primary small fw-semibold">
                Lihat Seluruh Riwayat &rarr;
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-custom align-middle mb-0">
                <thead>
                    <tr>
                        <th>Kode Transaksi</th>
                        <th>Tanggal</th>
                        <th>Total Bayar</th>
                        <th>Metode</th>
                        <th>Status Pesanan</th>
                        <th>Pembayaran</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_orders)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                Belum ada riwayat pesanan. Silakan berbelanja di katalog produk kami!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_orders as $ro): ?>
                            <tr>
                                <td class="fw-bold font-monospace"><?= htmlspecialchars($ro['transaction_code']) ?></td>
                                <td><?= format_date($ro['created_at']) ?></td>
                                <td class="fw-bold text-primary"><?= format_rupiah($ro['grand_total']) ?></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($ro['payment_method']) ?></span></td>
                                <td><?= get_status_badge($ro['status']) ?></td>
                                <td><?= get_payment_badge($ro['payment_status']) ?></td>
                                <td class="text-end">
                                    <a href="<?= BASE_URL ?>customer/order-detail.php?code=<?= $ro['transaction_code'] ?>" class="btn btn-outline-primary btn-sm">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
